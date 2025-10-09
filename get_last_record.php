<?php
require './controlador/conexion.php';

// Remove or comment out the debugging lines
// var_dump('entre');
// DIE();

$nombre = $_GET['nombre'] ?? '';
$apellidoPaterno = $_GET['apellidoPaterno'] ?? '';
$apellidoMaterno = $_GET['apellidoMaterno'] ?? '';

$sql = "SELECT fecha_nacimiento, telefono, numero_venta FROM cliente WHERE nombre = ? AND apellido_paterno = ? AND apellido_materno = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $apellidoPaterno, $apellidoMaterno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'fechaNacimiento' => $row['fecha_nacimiento'], 'telefono' => $row['telefono'], 'numero_venta' => $row['numero_venta']]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$con->close();
?>
