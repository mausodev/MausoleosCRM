<?php
require './conexion.php';
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$cliente_id = $_GET['cliente_id'] ?? null;
$correo_asesor = $_SESSION['correo'];



if (!$cliente_id) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente requerido']);
    exit();
}

try {
    // Obtener el mes actual
    $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);
    $mes = 'N/A';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mes = $row['mes']; 
    }

    // Consulta para obtener las actividades del cliente
    $sql = "SELECT ap.id, ap.actividad, ap.cita, ap.fechahora_inicio, ap.fechahora_fin, 
                   ap.completada, ap.fuente_prospeccion, ap.fecha_creado, ap.fecha_modificado,
                   ap.notas, c.nombre, c.apellido_paterno, c.apellido_materno
            FROM agenda_personal ap
            LEFT JOIN cliente c ON ap.cliente = c.id
            WHERE ap.cliente = ? AND ap.correo_asesor = ? AND ap.mes = ?
            ORDER BY ap.fechahora_inicio DESC";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("iss", $cliente_id, $correo_asesor, $mes);
    $stmt->execute();
    $result = $stmt->get_result();

    
    $actividades = [];
    while ($row = $result->fetch_assoc()) {
        $actividades[] = [
            'id' => $row['id'],
            'actividad' => $row['actividad'],
            'cita' => $row['cita'],
            'fechahora_inicio' => $row['fechahora_inicio'],
            'fechahora_fin' => $row['fechahora_fin'],
            'completada' => $row['completada'],
            'fuente_prospeccion' => $row['fuente_prospeccion'],
            'fecha_creado' => $row['fecha_creado'],
            'fecha_modificado' => $row['fecha_modificado'],
            'notas' => $row['notas'],
            'cliente_nombre' => $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'actividades' => $actividades,
        'mes' => $mes
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener actividades: ' . $e->getMessage()
    ]);
}
?>
