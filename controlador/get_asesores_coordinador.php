<?php
// Deshabilitar errores de PHP para evitar output HTML
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier output previo
ob_clean();

require './conexion.php';
require './access_control.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Establecer Content-Type antes de cualquier output
header('Content-Type: application/json');

// Verificar acceso
$access_data = verificarAcceso();
if (!$access_data['acceso']) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $coordinador_id = intval($_POST['coordinador_id'] ?? 0);
    $sucursal_usuario = $access_data['sucursal'];
    
    if ($coordinador_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de coordinador inválido']);
        exit;
    }
    
    // Verificar que el coordinador existe y pertenece a la misma sucursal
    $sql_verificar = "SELECT id, nombre, correo FROM empleado WHERE id = ? AND puesto = 'COORDINADOR' AND sucursal = ? AND activo = 1";
    $stmt_verificar = $con->prepare($sql_verificar);
    $stmt_verificar->bind_param("is", $coordinador_id, $sucursal_usuario);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Coordinador no encontrado o no pertenece a tu sucursal']);
        exit;
    }
    
    $coordinador = $result_verificar->fetch_assoc();
    
    // Obtener asesores de la misma sucursal (por ahora, todos los asesores de la sucursal)
    // En el futuro se puede implementar una relación específica coordinador-asesor
    $sql_asesores = "SELECT id, nombre, correo, puesto, sucursal 
                     FROM empleado 
                     WHERE puesto = 'ASESOR' AND sucursal = ? AND activo = 1 
                     ORDER BY nombre";
    
    $stmt_asesores = $con->prepare($sql_asesores);
    $stmt_asesores->bind_param("s", $sucursal_usuario);
    $stmt_asesores->execute();
    $result_asesores = $stmt_asesores->get_result();
    
    $asesores = [];
    while ($row = $result_asesores->fetch_assoc()) {
        $asesores[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'coordinador' => $coordinador,
        'asesores' => $asesores,
        'total' => count($asesores)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt_verificar)) $stmt_verificar->close();
    if (isset($stmt_asesores)) $stmt_asesores->close();
    $con->close();
}
?>
