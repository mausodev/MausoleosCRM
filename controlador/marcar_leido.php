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

// Establecer Content-Type antes de cualquier output
header('Content-Type: application/json');

// Verificar acceso
$access_data = verificarAcceso();
/*if (!$access_data['acceso']) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id_aviso'])) {
        echo json_encode(['success' => false, 'message' => 'ID de aviso requerido']);
        exit;
    }
    
    $id_aviso = $input['id_aviso'];
    $id_usuario = $access_data['id_asesor'];
    
    // Validar que el ID es numérico
    if (!is_numeric($id_aviso)) {
        echo json_encode(['success' => false, 'message' => 'ID de aviso no válido']);
        exit;
    }
    
    // Verificar que el usuario tiene acceso a este aviso
    $sql_verificar = "SELECT id_aviso, leido 
                      FROM avisos_receptores 
                      WHERE id_aviso = ? AND id_receptor = ?";
    
    $stmt_verificar = $con->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $id_aviso, $id_usuario);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a este aviso']);
        exit;
    }
    
    $aviso_usuario = $result_verificar->fetch_assoc();
    
    // Si ya está leído, no hacer nada
    if ($aviso_usuario['leido']) {
        echo json_encode(['success' => true, 'message' => 'Aviso ya estaba marcado como leído']);
        exit;
    }
    
    // Marcar como leído
    $sql_marcar = "UPDATE avisos_receptores 
                   SET leido = 1, fecha_lectura = NOW() 
                   WHERE id_aviso = ? AND id_receptor = ?";
    
    $stmt_marcar = $con->prepare($sql_marcar);
    $stmt_marcar->bind_param("ii", $id_aviso, $id_usuario);
    
    if (!$stmt_marcar->execute()) {
        throw new Exception('Error al marcar el aviso como leído: ' . $stmt_marcar->error);
    }
    
    if ($stmt_marcar->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado del aviso']);
        exit;
    }
    
    // Obtener información actualizada del aviso
    $sql_info = "SELECT ap.titulo, ap.tipo_aviso, e.nombre as emisor
                 FROM aviso_portal ap
                 JOIN empleado e ON ap.id_emisor = e.id
                 WHERE ap.id_aviso = ?";
    
    $stmt_info = $con->prepare($sql_info);
    $stmt_info->bind_param("i", $id_aviso);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();
    $aviso_info = $result_info->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Aviso marcado como leído correctamente',
        'aviso' => [
            'id' => $id_aviso,
            'titulo' => $aviso_info['titulo'],
            'tipo' => $aviso_info['tipo_aviso'],
            'emisor' => $aviso_info['emisor'],
            'fecha_lectura' => date('d/m/Y H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al marcar el aviso: ' . $e->getMessage()]);
} finally {
    if (isset($stmt_verificar)) $stmt_verificar->close();
    if (isset($stmt_marcar)) $stmt_marcar->close();
    if (isset($stmt_info)) $stmt_info->close();
    $con->close();
}
?>
