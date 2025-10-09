<?php
// Suppress error display to prevent HTML errors from breaking JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'conexion.php';

// Check if user is logged in
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save':
            saveLead();
            break;
        case 'update':
            updateLead();
            break;
        case 'delete':
            deleteLead();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function saveLead() {
    global $con;
    
    // Validate required fields
    $required_fields = ['plaza', 'asesor', 'fechaProspectado', 'etapaLead', 'nombre_cliente', 'apellidos', 'fecha_creado'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
            return;
        }
    }
    
    // Get form data and convert to UPPER CASE
    $plaza = strtoupper($_POST['plaza']);
    $asesor = $_POST['asesor'];
    $fechaProspectado = $_POST['fechaProspectado'];
    $etapaLead = strtoupper($_POST['etapaLead']);
    $montoVenta = $_POST['montoVenta'] ?? null;
    $producto = strtoupper($_POST['producto'] ?? '');
    $fechaSeguimiento = $_POST['fechaSeguimiento'] ?? null;
    $nombreCampana = strtoupper($_POST['nombreCampana'] ?? '');
    $medioCampana = strtoupper($_POST['medioCampana'] ?? '');
    $comentarios = strtoupper($_POST['comentarios'] ?? '');
    $nombre_cliente = strtoupper($_POST['nombre_cliente']);
    $apellidos = strtoupper($_POST['apellidos']);
    $fecha_creado = $_POST['fecha_creado'];
    $creado_por = strtoupper($_POST['creado_por'] ?? $_SESSION['correo'] ?? '');
    
    // Use selected campaign name or generate one if not provided
    $campaignName = !empty($nombreCampana) ? $nombreCampana : 'Campaña_' . date('Y-m-d_H-i-s');
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Insert new lead
        $query = "INSERT INTO leads (
            name, email, telefono, campaign_name, created_at, status, plaza, comentarios,
            asesor, fecha_prospectado, etapa_lead, monto_venta, producto, 
            fecha_seguimiento, nombre_campana, medio_campana, nombre_cliente, apellidos, 
            fecha_creado, creado_por
        ) VALUES (?, ?, ?, ?, NOW(), 'Activo', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $con->prepare($query);
        
        // Default values for missing fields
        $name = 'Lead_' . date('YmdHis');
        $email = '';
        $telefono = '';
        
        $stmt->bind_param("ssssssssssssssssss", 
            $name, $email, $telefono, $campaignName, $plaza, $comentarios,
            $asesor, $fechaProspectado, $etapaLead, $montoVenta, $producto,
            $fechaSeguimiento, $nombreCampana, $medioCampana, $nombre_cliente, $apellidos,
            $fecha_creado, $creado_por
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al guardar el lead: ' . $stmt->error);
        }
        
        $lead_id = $con->insert_id;
        $stmt->close();
        
        // Insert into client table
        $clientQuery = "INSERT INTO cliente (
            nombre, apellido_paterno, etapa, notas, origen_cliente, estatus, 
            asesor, fecha_creado, creado_por, descuento
        ) VALUES (?, ?, 'BASE DE DATOS', ?, 'FACEBOOK', 'MKT', ?, ?, ?, 0)";
        
        $clientStmt = $con->prepare($clientQuery);
        $clientStmt->bind_param("ssssss", 
            $nombre_cliente, $apellidos, $comentarios, $asesor, $fecha_creado, $creado_por
        );
        
        if (!$clientStmt->execute()) {
            throw new Exception('Error al guardar el cliente: ' . $clientStmt->error);
        }
        
        $clientStmt->close();
        
        // Commit transaction
        $con->commit();
        echo json_encode(['success' => true, 'message' => 'Lead y cliente guardados exitosamente']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateLead() {
    global $con;
    
    $leadId = $_POST['leadId'] ?? '';
    if (empty($leadId)) {
        echo json_encode(['success' => false, 'message' => 'ID de lead requerido']);
        return;
    }
    
    // Get form data and convert to UPPER CASE
    $plaza = strtoupper($_POST['plaza'] ?? '');
    $asesor = $_POST['asesor'] ?? '';
    $fechaProspectado = $_POST['fechaProspectado'] ?? '';
    $etapaLead = strtoupper($_POST['etapaLead'] ?? '');
    $montoVenta = $_POST['montoVenta'] ?? null;
    $producto = strtoupper($_POST['producto'] ?? '');
    $fechaSeguimiento = $_POST['fechaSeguimiento'] ?? null;
    $nombreCampana = strtoupper($_POST['nombreCampana'] ?? '');
    $medioCampana = strtoupper($_POST['medioCampana'] ?? '');
    $comentarios = strtoupper($_POST['comentarios'] ?? '');
    $nombre_cliente = strtoupper($_POST['nombre_cliente'] ?? '');
    $apellidos = strtoupper($_POST['apellidos'] ?? '');
    $fecha_creado = $_POST['fecha_creado'] ?? '';
    $creado_por = strtoupper($_POST['creado_por'] ?? $_SESSION['correo'] ?? '');
    
    // Update lead
    $query = "UPDATE leads SET 
        plaza = ?, asesor = ?, fecha_prospectado = ?, etapa_lead = ?, monto_venta = ?, 
        producto = ?, fecha_seguimiento = ?, nombre_campana = ?, medio_campana = ?, 
        comentarios = ?, nombre_cliente = ?, apellidos = ?, fecha_creado = ?, 
        creado_por = ?, updated_at = NOW()
        WHERE id = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssssssssssssi", 
        $plaza, $asesor, $fechaProspectado, $etapaLead, $montoVenta, $producto,
        $fechaSeguimiento, $nombreCampana, $medioCampana, $comentarios, $nombre_cliente,
        $apellidos, $fecha_creado, $creado_por, $leadId
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lead actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el lead: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteLead() {
    global $con;
    
    $leadId = $_POST['leadId'] ?? '';
    if (empty($leadId)) {
        echo json_encode(['success' => false, 'message' => 'ID de lead requerido']);
        return;
    }
    
    // Delete lead
    $query = "DELETE FROM leads WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leadId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lead eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el lead: ' . $stmt->error]);
    }
    
    $stmt->close();
}
?>