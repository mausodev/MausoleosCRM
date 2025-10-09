<?php
header('Content-Type: application/json');

// Include the database connection
require './controlador/conexion.php';

if (!$con) {
    echo json_encode(['error' => 'Database connection failed']);  // Handle connection error
    exit;  // Stop further execution
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['id'])) {
    $clienteId = $_GET['id'];
    
    // Realizar la consulta para obtener los datos del cliente
    $sql = "SELECT * FROM cliente WHERE id = '$clienteId'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        echo json_encode($cliente);  // Retorna los datos en formato JSON
    } else {
        echo json_encode([]);  // Si no se encuentra el cliente, retornar un array vacío
    }
}
?>