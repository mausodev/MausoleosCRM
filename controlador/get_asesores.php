<?php
require_once 'conexion.php';

if (!isset($_GET['plaza'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Plaza parameter is required']);
    exit;
}

$plaza = $_GET['plaza'];

// Validate that plaza is either CUAUHTEMOC or DELICIAS
if (!in_array($plaza, ['CUAUHTEMOC', 'DELICIAS'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid plaza. Must be CUAUHTEMOC or DELICIAS']);
    exit;
}

try {
    $query = "SELECT id, correo 
              FROM empleado 
              WHERE puesto = 'ASESOR' 
              AND sucursal = ? 
              AND activo = 1 
              ORDER BY correo";
              
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $con->error);
    }
    
    $stmt->bind_param("s", $plaza);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $asesores = [];
    while ($row = $result->fetch_assoc()) {
        $asesores[] = $row;
    }
    
    // Debug: Log the query and results
    error_log("Query executed for plaza: $plaza, found " . count($asesores) . " asesores");
    
    header('Content-Type: application/json');
    echo json_encode($asesores);
} catch(Exception $e) {
    error_log("Error in get_asesores.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 