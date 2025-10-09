<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();

header('Content-Type: application/json');

try {
    // Obtener solo empleados con puesto = 'ASESOR'
    $query = "SELECT 
                id, 
                CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo,
                correo,
                puesto
              FROM empleado 
              WHERE puesto = 'ASESOR' 
                AND activo = 1 
              ORDER BY nombre_completo";
    
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . mysqli_error($con));
    }
    
    $asesores = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $asesores[] = $row;
    }
    
    echo json_encode(array(
        "success" => true,
        "data" => $asesores
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}

mysqli_close($con);
?>
