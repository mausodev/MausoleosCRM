<?php
// Configurar headers ANTES de cualquier output
header('Content-Type: application/json; charset=utf-8');

// Desactivar output buffering que puede interferir
ob_clean();

// Logging seguro sin afectar JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // IMPORTANTE: Cambiar a 0
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

try {
    // Validar ID del GET
    if (!isset($_GET['asesor_id'])) {
        throw new Exception('No se recibió el ID del asesor');
    }

    $asesor_id = intval($_GET['asesor_id']);

    if ($asesor_id <= 0) {
        throw new Exception('ID de asesor inválido');
    }

    // Cargar conexión
    if (!file_exists('./controlador/conexion.php')) {
        throw new Exception('Archivo de conexión no encontrado');
    }

    require './controlador/conexion.php';

    if (!isset($con) || !($con instanceof mysqli)) {
        throw new Exception('Conexión a base de datos no disponible');
    }

    // Cargar control de acceso para obtener sucursal
    if (!file_exists('./controlador/access_control.php')) {
        throw new Exception('Archivo de control de acceso no encontrado');
    }

    require './controlador/access_control.php';

    $accessData = verificarAcceso();
    $sucursal = $accessData['sucursal'] ?? '';

    if (empty($sucursal)) {
        throw new Exception('No se pudo obtener la sucursal del usuario');
    }

    // QUERY 1: Obtener clientes activos del asesor
    $query_clientes = "SELECT id, nombre, etapa FROM cliente 
                       WHERE asesor = ? 
                       AND etapa NOT IN ('CERRADO PERDIDO', 'CERRADO GANADO') 
                       ORDER BY nombre";

    $stmt_clientes = $con->prepare($query_clientes);
    if (!$stmt_clientes) {
        throw new Exception('Error en prepare de clientes: ' . $con->error);
    }

    $stmt_clientes->bind_param("i", $asesor_id);

    if (!$stmt_clientes->execute()) {
        throw new Exception('Error en execute de clientes: ' . $stmt_clientes->error);
    }

    $result_clientes = $stmt_clientes->get_result();
    $clientes = [];

    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }

    $stmt_clientes->close();

    // QUERY 2: Obtener asesores disponibles en la misma sucursal
    $query_asesores = "SELECT id, correo, puesto 
                       FROM empleado 
                       WHERE activo = 1 
                       AND sucursal = ? 
                       AND id != ? 
                       AND puesto = 'ASESOR' 
                       AND (estado_empleado = 'Activo' OR estado_empleado = '') 
                       ORDER BY correo";

    $stmt_asesores = $con->prepare($query_asesores);
    if (!$stmt_asesores) {
        throw new Exception('Error en prepare de asesores: ' . $con->error);
    }

    $stmt_asesores->bind_param('si', $sucursal, $asesor_id);

    if (!$stmt_asesores->execute()) {
        throw new Exception('Error en execute de asesores: ' . $stmt_asesores->error);
    }

    $result_asesores = $stmt_asesores->get_result();
    $asesores = [];

    while ($row = $result_asesores->fetch_assoc()) {
        $asesores[] = $row;
    }

    $stmt_asesores->close();

    // Respuesta exitosa
    $response = [
        'success' => true,
        'clientes' => $clientes,
        'asesores' => $asesores
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Respuesta de error
    $error_response = [
        'success' => false,
        'error' => $e->getMessage()
    ];

    echo json_encode($error_response, JSON_UNESCAPED_UNICODE);
    error_log('Error en get_clientes_asesor.php: ' . $e->getMessage());
}
?>