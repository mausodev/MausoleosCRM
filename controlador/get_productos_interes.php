<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();

header('Content-Type: application/json');

try {
    // Obtener productos únicos de la tabla precio_servicio
    $query = "SELECT DISTINCT nombre 
              FROM precio_servicio 
              WHERE nombre IS NOT NULL 
                AND nombre != '' 
              ORDER BY nombre";
    
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . mysqli_error($con));
    }
    
    $productos = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
    
    echo json_encode(array(
        "success" => true,
        "data" => $productos
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}

mysqli_close($con);
?>
