<?php
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
    $leadId = $_GET['id'] ?? '';
    
    if (empty($leadId)) {
        echo json_encode(['success' => false, 'message' => 'ID de lead requerido']);
        exit();
    }
    
    // Get lead data
    $query = "SELECT id, name, email, phone, campaign_name, created_at, status, plaza, comentarios,
                     asesor, fecha_prospectado, etapa_lead, monto_venta, producto, 
                     fecha_seguimiento, liga_campana, medio_campana
              FROM leads 
              WHERE id = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'lead' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lead no encontrado']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
