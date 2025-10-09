<?php
session_start();
require './controlador/conexion.php';

// Check if user is logged in
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get parameters
$supervisor = isset($_GET['supervisor']) ? $_GET['supervisor'] : '';
$sucursal = isset($_GET['sucursal']) ? $_GET['sucursal'] : '';

if (!$supervisor || !$sucursal) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Prepare and execute query
$query = "SELECT id, correo, iniciales 
          FROM empleado 
          WHERE supervisor = ? 
          AND sucursal = ? 
          AND activo = 1 
          ORDER BY iniciales ASC";

$stmt = $con->prepare($query);
$stmt->bind_param("ss", $supervisor, $sucursal);
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$empleados = [];
while ($row = $result->fetch_assoc()) {
    $empleados[] = [
        'id' => $row['id'],
        'correo' => $row['correo'],
        'iniciales' => $row['iniciales']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($empleados);
?>
