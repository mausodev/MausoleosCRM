<?php
require 'conexion.php';

$id = $_POST['id'];
$query = "DELETE FROM vehiculo WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();

echo "success"; 