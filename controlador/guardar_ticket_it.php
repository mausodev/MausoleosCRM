<?php
require './conexion.php';
require './access_control.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar acceso
$access_data = verificarAcceso();
if (!$access_data['acceso']) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar datos de entrada
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo_problema = trim($_POST['tipo_problema'] ?? '');
    $prioridad = trim($_POST['prioridad'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $pasos_reproducir = trim($_POST['pasos_reproducir'] ?? '');
    $info_adicional = trim($_POST['info_adicional'] ?? '');
    
    if (empty($titulo) || empty($tipo_problema) || empty($prioridad) || empty($descripcion)) {
        echo json_encode(['success' => false, 'message' => 'Los campos obligatorios deben ser completados']);
        exit;
    }
    
    // Validar tipos y prioridades
    $tipos_validos = ['SOFTWARE', 'HARDWARE', 'RED', 'EMAIL', 'SISTEMA', 'OTRO'];
    $prioridades_validas = ['BAJA', 'MEDIA', 'ALTA', 'CRITICA'];
    
    if (!in_array($tipo_problema, $tipos_validos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de problema no válido']);
        exit;
    }
    
    if (!in_array($prioridad, $prioridades_validas)) {
        echo json_encode(['success' => false, 'message' => 'Prioridad no válida']);
        exit;
    }
    
    // Obtener información del usuario que crea el ticket
    $id_usuario = $access_data['id_asesor'];
    $correo_usuario = $access_data['correo'];
    
    // Crear el mensaje completo del ticket
    $mensaje_completo = "TICKET IT - {$tipo_problema}\n\n";
    $mensaje_completo .= "Prioridad: {$prioridad}\n";
    $mensaje_completo .= "Descripción del problema:\n{$descripcion}\n\n";
    
    if (!empty($pasos_reproducir)) {
        $mensaje_completo .= "Pasos para reproducir:\n{$pasos_reproducir}\n\n";
    }
    
    if (!empty($info_adicional)) {
        $mensaje_completo .= "Información adicional:\n{$info_adicional}\n\n";
    }
    
    $mensaje_completo .= "---\n";
    $mensaje_completo .= "Ticket creado por: " . $access_data['correo'] . " ({$correo_usuario})\n";
    $mensaje_completo .= "Fecha: " . date('d/m/Y H:i:s');
    
    // Iniciar transacción
    $con->begin_transaction();
    
    // Insertar el ticket como aviso especial
    $sql_ticket = "INSERT INTO aviso_portal (id_emisor, titulo, tipo_aviso, mensaje, correo_emite, area, fecha_envio) 
                   VALUES (?, ?, 'IT', ?, ?, 'IT', NOW())";
    
    $stmt_ticket = $con->prepare($sql_ticket);
    $stmt_ticket->bind_param("isss", $id_usuario, $titulo, $mensaje_completo, $correo_usuario);
    
    if (!$stmt_ticket->execute()) {
        throw new Exception('Error al crear el ticket: ' . $stmt_ticket->error);
    }
    
    $id_ticket = $con->insert_id;
    
    // Obtener empleados del área de IT para asignarles el ticket
    $sql_it = "SELECT id FROM empleado WHERE (puesto LIKE '%IT%' OR puesto LIKE '%SISTEMAS%' OR puesto LIKE '%TECNICO%' OR puesto LIKE '%SOPORTE%') AND activo = 1";
    $result_it = $con->query($sql_it);
    
    $destinatarios_it = [];
    if ($result_it && $result_it->num_rows > 0) {
        while ($row = $result_it->fetch_assoc()) {
            $destinatarios_it[] = $row['id'];
        }
    }
    
    // Si no hay empleados de IT, asignar a gerentes o administradores
    if (empty($destinatarios_it)) {
        $sql_admin = "SELECT id FROM empleado WHERE (puesto = 'GERENTE' OR puesto = 'ADMINISTRADOR') AND activo = 1 LIMIT 3";
        $result_admin = $con->query($sql_admin);
        if ($result_admin && $result_admin->num_rows > 0) {
            while ($row = $result_admin->fetch_assoc()) {
                $destinatarios_it[] = $row['id'];
            }
        }
    }
    
    // Si aún no hay destinatarios, asignar al emisor para que tenga registro
    if (empty($destinatarios_it)) {
        $destinatarios_it[] = $id_usuario;
    }
    
    // Insertar destinatarios del ticket
    $sql_receptores = "INSERT INTO avisos_receptores (id_receptor, id_aviso, leido, fecha_lectura) VALUES (?, ?, 0, NULL)";
    $stmt_receptores = $con->prepare($sql_receptores);
    
    foreach ($destinatarios_it as $id_receptor) {
        $stmt_receptores->bind_param("ii", $id_receptor, $id_ticket);
        if (!$stmt_receptores->execute()) {
            throw new Exception('Error al asignar destinatarios del ticket: ' . $stmt_receptores->error);
        }
    }
    
    // Confirmar transacción
    $con->commit();
    
    // Obtener información del ticket creado
    $sql_info = "SELECT ap.*, e.nombre as emisor_nombre 
                 FROM aviso_portal ap 
                 JOIN empleado e ON ap.id_emisor = e.id 
                 WHERE ap.id_aviso = ?";
    $stmt_info = $con->prepare($sql_info);
    $stmt_info->bind_param("i", $id_ticket);
    $stmt_info->execute();
    $ticket_info = $stmt_info->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Ticket IT creado correctamente',
        'ticket' => [
            'id' => $id_ticket,
            'titulo' => $ticket_info['titulo'],
            'tipo_problema' => $tipo_problema,
            'prioridad' => $prioridad,
            'emisor' => $ticket_info['emisor_nombre'],
            'fecha' => $ticket_info['fecha_envio'],
            'destinatarios_count' => count($destinatarios_it)
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt_ticket)) $stmt_ticket->close();
    if (isset($stmt_receptores)) $stmt_receptores->close();
    if (isset($stmt_info)) $stmt_info->close();
    $con->close();
}
?>
