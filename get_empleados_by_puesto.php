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
$puesto_id = isset($_GET['puesto_id']) ? intval($_GET['puesto_id']) : 0;
$sucursal = isset($_GET['sucursal']) ? $_GET['sucursal'] : '';

if (!$puesto_id || !$sucursal) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Prepare and execute query
$query = "SELECT id, correo 
          FROM empleado 
          WHERE puesto = ? 
          AND sucursal = ? 
          ORDER BY correo ASC";

$stmt = $con->prepare($query);
$stmt->bind_param("is", $puesto_id, $sucursal);
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$empleados = [];
while ($row = $result->fetch_assoc()) {
    $empleados[] = [
        'id' => $row['id'],
        'correo' => $row['correo']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($empleados); 