<?php
session_start();
require './controlador/conexion.php';

$producto = $_GET['producto'] ?? '';
$plazo = $_GET['plazo'] ?? '';
$sucursal = $_GET['sucursal'] ?? '';



$response = ['success' => false];

if ($producto && $plazo && $sucursal) {
    $sql = "SELECT precio, enganche FROM precio_servicio WHERE nombre = ? AND plazo = ? AND plaza = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sis", $producto, $plazo, $sucursal);
    $stmt->execute();
    $result = $stmt->get_result();
  
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['precio'] = $row['precio'];
        $response['enganche'] = $row['enganche'];
    }
}

echo json_encode($response);
?> 