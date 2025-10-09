<?php
session_start();
require './controlador/conexion.php';

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$id_asesor = $_SESSION['id'];
$correo = $_SESSION['correo'];
$puesto = $_SESSION['puesto'];
$sucursal = $_SESSION['sucursal'];

// Obtener el mes actual
$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
$result = $con->query($sqlCierre);
$mes = 'N/A';
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mes = $row['mes']; 
}

// Obtener filtros de la URL
$filtro_estatus = isset($_GET['estatus']) ? $_GET['estatus'] : '';
$filtro_asesor = isset($_GET['asesor']) ? $_GET['asesor'] : '';
$filtro_coordinador = isset($_GET['coordinador']) ? $_GET['coordinador'] : '';

// Construir consulta base con filtros
$where_conditions = [];
$params = [];
$param_types = '';

// Filtro por plaza del usuario logueado
$where_conditions[] = "ap.plaza = ?";
$params[] = $sucursal;
$param_types .= 's';

// Filtro por estatus
if (!empty($filtro_estatus)) {
    $where_conditions[] = "ap.completada = ?";
    $params[] = $filtro_estatus;
    $param_types .= 's';
}

// Construir consulta según el rol
if ($puesto == 'COORDINADOR') {
    // Filtro por asesor para coordinadores
    if (!empty($filtro_asesor)) {
        $where_conditions[] = "ap.correo_asesor = ?";
        $params[] = $filtro_asesor;
        $param_types .= 's';
    } else {
        $where_conditions[] = "ap.correo_asesor IN (SELECT correo FROM empleado WHERE id_supervisor = ?)";
        $params[] = $id_asesor;
        $param_types .= 'i';
    }
} elseif ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE' || $puesto == 'DIRECTOR') {
    // Filtros para roles ejecutivos
    if (!empty($filtro_coordinador)) {
        $where_conditions[] = "ap.correo_asesor IN (SELECT correo FROM empleado WHERE supervisor = ?)";
        $params[] = $filtro_coordinador;
        $param_types .= 's';
    } elseif (!empty($filtro_asesor)) {
        $where_conditions[] = "ap.correo_asesor = ?";
        $params[] = $filtro_asesor;
        $param_types .= 's';
    }
    // Si no hay filtros, mostrar todo de la plaza
} elseif ($puesto == 'ASESOR') {
    $where_conditions[] = "ap.correo_asesor = ?";
    $params[] = $correo;
    $param_types .= 's';
}

$sql = "SELECT ap.id, ap.actividad, ap.cita, ap.fechahora_inicio, ap.fechahora_fin, 
       ap.disponible, ap.completada, ap.fecha_reprogramacion, ap.cumplio, 
       ap.fuente_prospeccion, ap.fecha_modificado, ap.correo_asesor,
       e.iniciales as asesor_iniciales, e.fecha_creado,
       CASE 
         WHEN ap.cliente = 0 THEN 'PROSPECCION'
         ELSE CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno)
       END AS cliente_nombre
       FROM agenda_personal ap
       LEFT JOIN cliente c ON ap.cliente = c.id
       LEFT JOIN empleado e ON ap.correo_asesor = e.correo
       WHERE " . implode(' AND ', $where_conditions) . "
       ORDER BY ap.fechahora_inicio ASC";

$stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$agenda = $stmt->get_result();

// Obtener metas de venta para calcular desempeño
$metas_data = array();
if ($puesto == 'COORDINADOR') {
    $sql_metas = "SELECT e.id, e.correo, COALESCE(mv.meta, 0) as meta, 
                  COALESCE(SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_embudo ELSE 0 END), 0) as venta_real
                  FROM empleado e 
                  LEFT JOIN meta_venta mv ON e.id = mv.id_asesor AND mv.nombre_mes = ?
                  LEFT JOIN cliente c ON e.id = c.asesor AND c.mes = ?
                  WHERE e.id_supervisor = ? AND e.activo = 1 AND e.puesto = 'ASESOR'
                  GROUP BY e.id, e.correo, mv.meta";
    $stmt_metas = $con->prepare($sql_metas);
    $stmt_metas->bind_param("ssi", $mes, $mes, $id_asesor);
} else {
    $sql_metas = "SELECT e.id, e.correo, COALESCE(mv.meta, 0) as meta,
                  COALESCE(SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_embudo ELSE 0 END), 0) as venta_real
                  FROM empleado e 
                  LEFT JOIN meta_venta mv ON e.id = mv.id_asesor AND mv.nombre_mes = ?
                  LEFT JOIN cliente c ON e.id = c.asesor AND c.mes = ?
                  WHERE e.correo = ?
                  GROUP BY e.id, e.correo, mv.meta";
    $stmt_metas = $con->prepare($sql_metas);
    $stmt_metas->bind_param("sss", $mes, $mes, $correo);
}

$stmt_metas->execute();
$result_metas = $stmt_metas->get_result();

while ($row_meta = $result_metas->fetch_assoc()) {
    $metas_data[$row_meta['correo']] = array(
        'meta' => $row_meta['meta'],
        'venta_real' => $row_meta['venta_real'],
        'fecha_creado' => $row_meta['fecha_creado'] ?? null
    );
}

// Procesar actividades y agregar información de desempeño
$actividades = array();
while ($row = $agenda->fetch_assoc()) {
    $asesor_correo = $row['correo_asesor'];
    $meta_info = $metas_data[$asesor_correo] ?? array('meta' => 0, 'venta_real' => 0, 'fecha_creado' => null);
    
    // Calcular etiqueta de desempeño
    $etiqueta_desempeno = 'NUEVO INGRESO';
    if ($meta_info['fecha_creado']) {
        $fecha_creacion = new DateTime($meta_info['fecha_creado']);
        $fecha_actual = new DateTime();
        $dias_diferencia = $fecha_actual->diff($fecha_creacion)->days;
        
        if ($dias_diferencia > 90) {
            if ($meta_info['meta'] > 0) {
                $porcentaje_cumplimiento = ($meta_info['venta_real'] / $meta_info['meta']) * 100;
                if ($porcentaje_cumplimiento >= 100) {
                    $etiqueta_desempeno = 'ALTO DESEMPEÑO';
                } else {
                    $etiqueta_desempeno = 'BAJO DESEMPEÑO';
                }
            }
        }
    }
    
    $actividades[] = array(
        'id' => $row['id'],
        'actividad' => $row['actividad'],
        'cita' => $row['cita'],
        'fechahora_inicio' => $row['fechahora_inicio'],
        'fechahora_fin' => $row['fechahora_fin'],
        'disponible' => $row['disponible'],
        'completada' => $row['completada'],
        'fecha_reprogramacion' => $row['fecha_reprogramacion'],
        'cumplio' => $row['cumplio'],
        'fuente_prospeccion' => $row['fuente_prospeccion'],
        'fecha_modificado' => $row['fecha_modificado'],
        'cliente_nombre' => $row['cliente_nombre'],
        'asesor_iniciales' => $row['asesor_iniciales'],
        'etiqueta_desempeno' => $etiqueta_desempeno
    );
}

header('Content-Type: application/json');
echo json_encode($actividades);
?>
