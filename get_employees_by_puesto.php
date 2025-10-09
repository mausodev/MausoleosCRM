<?php
session_start();
require './controlador/conexion.php';

// Check if puesto parameter is provided
if (!isset($_GET['puesto'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Puesto parameter is required']);
    exit();
}

$puesto = $_GET['puesto'];
$sucursal = $_GET['sucursal'] ?? '';

// Validate puesto value
if (!in_array($puesto, ['ASESOR', 'COORDINADOR'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid puesto value']);
    exit();
}

// Get employees by puesto and sucursal
$query = "SELECT id, correo FROM empleado WHERE puesto = ? AND activo = 1 AND sucursal = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $puesto, $sucursal);
$stmt->execute();
$result = $stmt->get_result();

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = [
        'id' => $row['id'],
        'correo' => $row['correo']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($employees);
?> 