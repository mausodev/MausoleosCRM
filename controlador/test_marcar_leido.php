<?php
// Test endpoint para marcar como leído
header('Content-Type: application/json');

// Deshabilitar errores de PHP
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar output
ob_clean();

try {
    require './conexion.php';
    require './access_control.php';
    
    // Iniciar sesión si no está iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar acceso
    $access_data = verificarAcceso();
    
    echo json_encode([
        'success' => true,
        'message' => 'Test marcar leído funcionando',
        'user_id' => $access_data['id_asesor'],
        'access' => $access_data['acceso'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
