<?php
// Test endpoint para verificar conexión
header('Content-Type: application/json');

// Deshabilitar errores de PHP
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar output
ob_clean();

try {
    require './conexion.php';
    
    // Test simple de conexión
    $test_query = "SELECT 1 as test";
    $result = $con->query($test_query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Conexión a base de datos exitosa',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error en consulta de prueba: ' . $con->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
