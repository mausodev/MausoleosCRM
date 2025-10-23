<?php
require './controlador/conexion.php';
require './controlador/access_control.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

session_start();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener los datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['employee_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de empleado no proporcionado']);
    exit;
}

$employee_id = intval($data['employee_id']);

// Verificar acceso
$accessData = verificarAcceso();
$correo = $accessData['correo'];
$sucursal = $accessData['sucursal'];

try {
    // Obtener información del empleado antes de dar de baja
    $query_empleado = "SELECT nombre, apellido_paterno, apellido_materno, correo, puesto 
                       FROM empleado 
                       WHERE id = ? AND sucursal = ?";
    $stmt_empleado = $con->prepare($query_empleado);
    $stmt_empleado->bind_param("is", $employee_id, $sucursal);
    $stmt_empleado->execute();
    $result_empleado = $stmt_empleado->get_result();
    
    if ($result_empleado->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Empleado no encontrado']);
        exit;
    }
    
    $empleado = $result_empleado->fetch_assoc();
    $nombre_completo = $empleado['nombre'] . ' ' . $empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno'];
    $correo_empleado = $empleado['correo'];
    
    // Actualizar estado del empleado a BAJA
    $update_query = "UPDATE empleado 
                     SET estado_empleado = 'BAJA', 
                         activo = 0,
                         fecha_modificado = NOW(),
                         modificado_por = ?
                     WHERE id = ? AND sucursal = ?";
    
    $stmt_update = $con->prepare($update_query);
    $stmt_update->bind_param("sis", $correo, $employee_id, $sucursal);
    
    if (!$stmt_update->execute()) {
        throw new Exception('Error al actualizar el estado del empleado: ' . $stmt_update->error);
    }
    
    // Obtener correos para notificación de baja
    $query_notificaciones = "SELECT correo FROM empleado 
                            WHERE notificacion_baja = 1 
                            AND sucursal = ? 
                            AND (puesto = 'EJECUTIVO' OR puesto = 'COORDINADOR DE TALENTO Y CULTURA' 
                                 OR puesto = 'DIRECTOR' OR puesto = 'GERENTE')
                            AND notificacion_baja = 1";
                            
    $stmt_notificaciones = $con->prepare($query_notificaciones);
    $stmt_notificaciones->bind_param("s", $sucursal);
    $stmt_notificaciones->execute();
    $result_notificaciones = $stmt_notificaciones->get_result();
    
    $notificaciones_baja = [];
    while ($row = $result_notificaciones->fetch_assoc()) {
        $notificaciones_baja[] = $row['correo'];
    }
    
    // Enviar notificación de baja por correo
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'crmmauso@gmail.com';
        $mail->Password = 'fjqa kkqe nmbm cxpy';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        $mail->setFrom('crmmauso@gmail.com', 'Portal Mausoleos');
        $mail->isHTML(true);
        $mail->Subject = 'Notificación de Baja - Portal Mausoleos';
        
        // Agregar destinatarios
        if (!empty($notificaciones_baja)) {
            foreach ($notificaciones_baja as $correo_dest) {
                $mail->addAddress($correo_dest);
            }
        } else {
            $mail->addAddress("notificacionesmle@mle.com.mx");
        }
        
        $mail->Body = "
            <h2>Notificación de Baja</h2>
            <p>El empleado <b>{$nombre_completo}</b> con correo <b>{$correo_empleado}</b> 
            ha sido dado de <span style='color:red;'>BAJA</span> en el Portal Mausoleos.</p>
            <p>Fecha de actualización: " . date('Y-m-d H:i:s') . "</p>
            <p>Realizado por: {$correo}</p>
            <p>Por favor, atender esta notificación.</p>
        ";
        
        $mail->send();
        
    } catch (Exception $e) {
        // Log del error pero no detener el proceso
        error_log("Error al enviar correo de notificación: " . $mail->ErrorInfo);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Empleado dado de baja exitosamente',
        'empleado' => $nombre_completo
    ]);
    
} catch (Exception $e) {
    error_log("Error en dar_baja_empleado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>