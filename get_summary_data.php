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

// Obtener resumen de actividades por tipo
$sql_actividades = "SELECT 
    ap.actividad,
    COUNT(*) as total_actividades,
    SUM(CASE WHEN ap.cumplio = 'SI' THEN 1 ELSE 0 END) as efectivas,
    ap.fuente_prospeccion,
    COUNT(DISTINCT ap.fuente_prospeccion) as fuentes_unicas
    FROM agenda_personal ap
    WHERE ap.correo_asesor = ? 
    AND ap.mes = ?
    GROUP BY ap.actividad, ap.fuente_prospeccion
    ORDER BY total_actividades DESC";

$stmt_actividades = $con->prepare($sql_actividades);
$stmt_actividades->bind_param('ss', $correo, $mes);
$stmt_actividades->execute();
$result_actividades = $stmt_actividades->get_result();

$actividades_resumen = array();
while ($row = $result_actividades->fetch_assoc()) {
    $actividades_resumen[] = $row;
}

// Obtener venta diaria esperada (suma de total_embudo de clientes por día)
$sql_venta_diaria = "SELECT 
    DATE(c.fecha_creado) as fecha,
    SUM(c.total_embudo) as venta_diaria
    FROM cliente c
    INNER JOIN empleado e ON c.asesor = e.id
    WHERE e.correo = ? 
    AND c.mes = ?
    AND e.sucursal = ?
    GROUP BY DATE(c.fecha_creado)
    ORDER BY fecha DESC";

$stmt_venta = $con->prepare($sql_venta_diaria);
$stmt_venta->bind_param('sss', $correo, $mes, $sucursal);
$stmt_venta->execute();
$result_venta = $stmt_venta->get_result();

$venta_diaria = array();
while ($row = $result_venta->fetch_assoc()) {
    $venta_diaria[] = $row;
}

// Calcular totales generales
$total_actividades = array_sum(array_column($actividades_resumen, 'total_actividades'));
$total_efectivas = array_sum(array_column($actividades_resumen, 'efectivas'));
$porcentaje_efectividad = $total_actividades > 0 ? ($total_efectivas / $total_actividades) * 100 : 0;

$total_venta_diaria = array_sum(array_column($venta_diaria, 'venta_diaria'));
$promedio_venta_diaria = count($venta_diaria) > 0 ? $total_venta_diaria / count($venta_diaria) : 0;

$resumen = array(
    'actividades' => $actividades_resumen,
    'venta_diaria' => $venta_diaria,
    'totales' => array(
        'total_actividades' => $total_actividades,
        'total_efectivas' => $total_efectivas,
        'porcentaje_efectividad' => round($porcentaje_efectividad, 2),
        'total_venta_diaria' => $total_venta_diaria,
        'promedio_venta_diaria' => round($promedio_venta_diaria, 2)
    )
);

header('Content-Type: application/json');
echo json_encode($resumen);
?>
