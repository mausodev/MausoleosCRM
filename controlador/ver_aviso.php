<?php
// Deshabilitar errores de PHP para evitar output HTML
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier output previo
ob_clean();

require './conexion.php';
require './access_control.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar acceso
$access_data = verificarAcceso();
/*if (!$access_data['acceso']) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}*/

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id_aviso = $_GET['id'] ?? '';
    
    if (empty($id_aviso) || !is_numeric($id_aviso)) {
        echo json_encode(['success' => false, 'message' => 'ID de aviso no válido']);
        exit;
    }
    
    $id_usuario = $access_data['id_asesor'];
    
    // Verificar que el usuario tiene acceso a este aviso
    $sql_verificar = "SELECT ar.id_aviso, ar.leido 
                      FROM avisos_receptores ar 
                      WHERE ar.id_aviso = ? AND ar.id_receptor = ?";
    
    $stmt_verificar = $con->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $id_aviso, $id_usuario);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a este aviso']);
        exit;
    }
    
    $acceso_aviso = $result_verificar->fetch_assoc();
    
    // Obtener información completa del aviso
    $sql_aviso = "SELECT ap.id_aviso, ap.titulo, ap.tipo_aviso, ap.mensaje, ap.correo_emite, 
                         ap.area, ap.fecha_envio, e.nombre as emisor, e.correo as correo_emisor
                  FROM aviso_portal ap
                  JOIN empleado e ON ap.id_emisor = e.id
                  WHERE ap.id_aviso = ?";
    
    $stmt_aviso = $con->prepare($sql_aviso);
    $stmt_aviso->bind_param("i", $id_aviso);
    $stmt_aviso->execute();
    $result_aviso = $stmt_aviso->get_result();
    
    if ($result_aviso->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Aviso no encontrado']);
        exit;
    }
    
    $aviso = $result_aviso->fetch_assoc();
    
    // Formatear fecha
    $fecha_formateada = date('d/m/Y H:i:s', strtotime($aviso['fecha_envio']));
    
    // Obtener lista de destinatarios
    $sql_destinatarios = "SELECT e.nombre, e.puesto, e.correo, ar.leido, ar.fecha_lectura
                          FROM avisos_receptores ar
                          JOIN empleado e ON ar.id_receptor = e.id
                          WHERE ar.id_aviso = ?
                          ORDER BY e.nombre";
    
    $stmt_destinatarios = $con->prepare($sql_destinatarios);
    $stmt_destinatarios->bind_param("i", $id_aviso);
    $stmt_destinatarios->execute();
    $result_destinatarios = $stmt_destinatarios->get_result();
    $destinatarios = $result_destinatarios->fetch_all(MYSQLI_ASSOC);
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'aviso' => [
            'id_aviso' => $aviso['id_aviso'],
            'titulo' => $aviso['titulo'],
            'tipo_aviso' => $aviso['tipo_aviso'],
            'mensaje' => $aviso['mensaje'],
            'emisor' => $aviso['emisor'],
            'correo_emisor' => $aviso['correo_emite'],
            'area' => $aviso['area'],
            'fecha_envio' => $fecha_formateada,
            'leido' => (bool)$acceso_aviso['leido'],
            'destinatarios' => []
        ]
    ];
    
    // Agregar información de destinatarios
    foreach ($destinatarios as $destinatario) {
        $response['aviso']['destinatarios'][] = [
            'nombre' => $destinatario['nombre'],
            'puesto' => $destinatario['puesto'],
            'correo' => $destinatario['correo'],
            'leido' => (bool)$destinatario['leido'],
            'fecha_lectura' => $destinatario['fecha_lectura'] ? date('d/m/Y H:i:s', strtotime($destinatario['fecha_lectura'])) : null
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener el aviso: ' . $e->getMessage()]);
} finally {
    if (isset($stmt_verificar)) $stmt_verificar->close();
    if (isset($stmt_aviso)) $stmt_aviso->close();
    if (isset($stmt_destinatarios)) $stmt_destinatarios->close();
    $con->close();
}
?>
