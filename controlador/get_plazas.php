<?php
require './controlador/conexion.php';

try {
    $query = "SELECT id, nombre FROM plaza WHERE activo = 1 ORDER BY nombre";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $plazas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($plazas);
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 