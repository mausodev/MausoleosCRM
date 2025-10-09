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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Debug: Log received data
    error_log('POST data received: ' . print_r($_POST, true));
    
    // Validar datos de entrada
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo_aviso = trim($_POST['tipo_aviso'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $destinatarios_input = $_POST['destinatarios'] ?? '';
    $area = trim($_POST['area'] ?? '');
    
    // Debug: Log processed data
    error_log('Processed data - Titulo: ' . $titulo . ', Tipo: ' . $tipo_aviso . ', Destinatarios: ' . $destinatarios_input);
    
    if (empty($titulo) || empty($tipo_aviso) || empty($mensaje) || empty($destinatarios_input)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
        exit;
    }
    
    // Convertir string de destinatarios a array
    $destinatarios = array_filter(explode(',', $destinatarios_input));
    
    if (count($destinatarios) === 0) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos un destinatario']);
        exit;
    }
    
    // Validar que el tipo de aviso sea válido
    $tipos_validos = ['INFORMATIVO', 'URGENTE', 'IT', 'GENERAL'];
    if (!in_array($tipo_aviso, $tipos_validos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de aviso no válido']);
        exit;
    }
    
    // Obtener información del emisor
    $id_emisor = $access_data['id_asesor'];
    $correo_emisor = $access_data['correo'];
    
    // Iniciar transacción
    $con->begin_transaction();
    
    // Insertar el aviso principal
    $sql_aviso = "INSERT INTO aviso_portal (id_emisor, titulo, tipo_aviso, mensaje, correo_emite, area, fecha_envio) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt_aviso = $con->prepare($sql_aviso);
    $stmt_aviso->bind_param("isssss", $id_emisor, $titulo, $tipo_aviso, $mensaje, $correo_emisor, $area);
    
    if (!$stmt_aviso->execute()) {
        throw new Exception('Error al crear el aviso: ' . $stmt_aviso->error);
    }
    
    $id_aviso = $con->insert_id;
    
    // Insertar destinatarios
    $sql_receptores = "INSERT INTO avisos_receptores (id_receptor, id_aviso, leido, fecha_lectura) VALUES (?, ?, 0, NULL)";
    $stmt_receptores = $con->prepare($sql_receptores);
    
    foreach ($destinatarios as $id_receptor) {
        // Validar que el receptor existe y está activo
        $sql_validar = "SELECT id FROM empleado WHERE id = ? AND activo = 1";
        $stmt_validar = $con->prepare($sql_validar);
        $stmt_validar->bind_param("i", $id_receptor);
        $stmt_validar->execute();
        $result_validar = $stmt_validar->get_result();
        
        if ($result_validar->num_rows === 0) {
            continue; // Saltar destinatarios inválidos
        }
        
        $stmt_receptores->bind_param("ii", $id_receptor, $id_aviso);
        if (!$stmt_receptores->execute()) {
            throw new Exception('Error al asignar destinatarios: ' . $stmt_receptores->error);
        }
    }
    
    // Confirmar transacción
    $con->commit();
    
    // Obtener información del aviso creado para respuesta
    $sql_info = "SELECT ap.*, e.nombre as emisor_nombre 
                 FROM aviso_portal ap 
                 JOIN empleado e ON ap.id_emisor = e.id 
                 WHERE ap.id_aviso = ?";
    $stmt_info = $con->prepare($sql_info);
    $stmt_info->bind_param("i", $id_aviso);
    $stmt_info->execute();
    $aviso_info = $stmt_info->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Aviso enviado correctamente',
        'aviso' => [
            'id' => $id_aviso,
            'titulo' => $aviso_info['titulo'],
            'tipo' => $aviso_info['tipo_aviso'],
            'emisor' => $aviso_info['emisor_nombre'],
            'fecha' => $aviso_info['fecha_envio'],
            'destinatarios_count' => count($destinatarios)
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt_aviso)) $stmt_aviso->close();
    if (isset($stmt_receptores)) $stmt_receptores->close();
    if (isset($stmt_info)) $stmt_info->close();
    $con->close();
}
?>
