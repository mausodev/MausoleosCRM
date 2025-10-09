<?php
require 'conexion.php';

$id = $_POST['id'] ?? null;
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$anio = $_POST['anio'];
$kilometraje = $_POST['kilometraje'];
$poliza = $_POST['poliza'];
$vigencia = $_POST['vigencia'];
$servicio = $_POST['servicio'] ?? null;
$proximo_servicio = $_POST['proximo_servicio'] ?? null;
$estatus = $_POST['estatus'] ?? 'Activo';

if($id) {
    // Update
    $query = "UPDATE vehiculo SET 
              marca = ?, 
              modelo = ?,
              anio = ?,
              kilometraje = ?,
              poliza = ?,
              vigencia = ?,
              servicio = ?,
              proximo_servicio = ?,
              estatus = ?
              WHERE id = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssiiissssi", $marca, $modelo, $anio, $kilometraje, $poliza, $vigencia, $servicio, $proximo_servicio, $estatus, $id);
} else {
    // Insert
    $query = "INSERT INTO vehiculo (marca, modelo, anio, kilometraje, poliza, vigencia) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssiiss", $marca, $modelo, $anio, $kilometraje, $poliza, $vigencia);
}

$stmt->execute();
header('Location: ../alta_auto.php'); 