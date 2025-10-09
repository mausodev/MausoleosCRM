<?php
require_once 'conexion.php';

try {
    $query = "SELECT l.*, e.nombre as nombre_asesor, p.nombre as plaza_nombre 
              FROM control_lead l 
              LEFT JOIN empleados e ON l.id_asesor = e.id 
              LEFT JOIN plazas p ON l.plaza = p.id 
              WHERE l.plaza IN ('CUAUHTEMOC', 'DELICIAS')
              ORDER BY l.fecha DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($leads);
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 