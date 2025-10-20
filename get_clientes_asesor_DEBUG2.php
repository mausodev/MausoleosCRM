<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Función helper para responder
function respond($data) {
    echo json_encode($data);
    exit;
}

// Función para loggear arrays/objetos de forma segura
function safe_log($message, $data = null) {
    if ($data === null) {
        error_log($message);
    } else {
        // Convertir objetos mysqli a string descriptivo
        if ($data instanceof mysqli) {
            error_log($message . ": [mysqli connection object]");
        } else if (is_array($data)) {
            // Limpiar objetos mysqli del array antes de var_export
            $clean_data = array_map(function($item) {
                return ($item instanceof mysqli) ? '[mysqli object]' : $item;
            }, $data);
            error_log($message . ": " . print_r($clean_data, true));
        } else {
            error_log($message . ": " . var_export($data, true));
        }
    }
}

safe_log("═══ INICIO get_clientes_asesor.php ═══");

try {
    // =========================================================================
    // CHECKPOINT 1: Inicio
    // =========================================================================
    safe_log("✓ Checkpoint 1: Script iniciado");
    
    // =========================================================================
    // CHECKPOINT 2: Validar ID
    // =========================================================================
    safe_log("✓ Checkpoint 2: Validando ID");
    
    $asesor_id = $_GET['asesor_id'] ?? null;
    safe_log("  - ID recibido", $asesor_id);
    
    if (!$asesor_id) {
        safe_log("✗ ERROR: No se recibió ID");
        respond(['success' => false, 'error' => 'No se recibió el ID del asesor', 'checkpoint' => 2]);
    }
    
    $asesor_id = intval($asesor_id);
    safe_log("  - ID sanitizado", $asesor_id);
    
    if ($asesor_id <= 0) {
        safe_log("✗ ERROR: ID inválido");
        respond(['success' => false, 'error' => 'ID de asesor inválido', 'checkpoint' => 2]);
    }
    
    // =========================================================================
    // CHECKPOINT 3: Cargar archivos
    // =========================================================================
    safe_log("✓ Checkpoint 3: Cargando archivos");
    
    if (!file_exists('./controlador/conexion.php')) {
        safe_log("✗ ERROR: No existe ./controlador/conexion.php");
        respond(['success' => false, 'error' => 'Archivo conexion.php no encontrado', 'checkpoint' => 3]);
    }
    
    if (!file_exists('./controlador/access_control.php')) {
        safe_log("✗ ERROR: No existe ./controlador/access_control.php");
        respond(['success' => false, 'error' => 'Archivo access_control.php no encontrado', 'checkpoint' => 3]);
    }
    
    require './controlador/conexion.php';
    safe_log("  - conexion.php cargado");
    
    require './controlador/access_control.php';
    safe_log("  - access_control.php cargado");
    
    // =========================================================================
    // CHECKPOINT 4: Verificar conexión
    // =========================================================================
    safe_log("✓ Checkpoint 4: Verificando conexión BD");
    
    if (!isset($con)) {
        safe_log("✗ ERROR: Variable $con no definida");
        respond(['success' => false, 'error' => 'Conexión a BD no disponible', 'checkpoint' => 4]);
    }
    
    if (!($con instanceof mysqli)) {
        safe_log("✗ ERROR: $con no es mysqli");
        respond(['success' => false, 'error' => 'Conexión a BD inválida', 'checkpoint' => 4]);
    }
    
    safe_log("  - Conexión BD válida");
    
    // =========================================================================
    // CHECKPOINT 5: Obtener clientes
    // =========================================================================
    safe_log("✓ Checkpoint 5: Obteniendo clientes");
    
    $query_clientes = "SELECT id, nombre, etapa FROM cliente WHERE asesor = ? AND etapa != 'CERRADO PERDIDO' AND etapa != 'CERRADO GANADO' ORDER BY nombre";
    
    $stmt_clientes = $con->prepare($query_clientes);
    
    if (!$stmt_clientes) {
        $error = $con->error;
        safe_log("✗ ERROR en prepare: " . $error);
        respond(['success' => false, 'error' => 'Error en prepare de clientes: ' . $error, 'checkpoint' => 5]);
    }
    
    $stmt_clientes->bind_param("i", $asesor_id);
    
    if (!$stmt_clientes->execute()) {
        $error = $stmt_clientes->error;
        safe_log("✗ ERROR en execute: " . $error);
        respond(['success' => false, 'error' => 'Error en execute de clientes: ' . $error, 'checkpoint' => 5]);
    }
    
    $result_clientes = $stmt_clientes->get_result();
    
    $clientes = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
    $stmt_clientes->close();
    
    safe_log("  - Total clientes: " . count($clientes));
    
    // =========================================================================
    // CHECKPOINT 6: Obtener sucursal
    // =========================================================================
    safe_log("✓ Checkpoint 6: Obteniendo sucursal");
    
    if (!function_exists('verificarAcceso')) {
        safe_log("✗ ERROR: Función verificarAcceso no existe");
        respond(['success' => false, 'error' => 'Función verificarAcceso no disponible', 'checkpoint' => 6]);
    }
    
    $accessData = verificarAcceso();
    
    // Log seguro sin imprimir objetos mysqli
    if (isset($accessData['sucursal'])) {
        safe_log("  - Sucursal obtenida: " . $accessData['sucursal']);
    } else {
        safe_log("  - WARNING: 'sucursal' no está en accessData");
    }
    
    $sucursal = $accessData['sucursal'] ?? '';
    
    if (empty($sucursal)) {
        safe_log("✗ ERROR: Sucursal vacía");
        respond(['success' => false, 'error' => 'No se pudo obtener la sucursal', 'checkpoint' => 6]);
    }
    
    safe_log("  - Sucursal: " . $sucursal);
    
    // =========================================================================
    // CHECKPOINT 7: Obtener asesores
    // =========================================================================
    safe_log("✓ Checkpoint 7: Obteniendo asesores");
    
    $query_asesores = "SELECT id, correo, puesto FROM empleado WHERE activo = 1 AND sucursal = ? AND id != ? AND puesto = 'ASESOR' AND (estado_empleado = 'Activo' OR estado_empleado = '') ORDER BY correo";
    
    $stmt_asesores = $con->prepare($query_asesores);
    
    if (!$stmt_asesores) {
        $error = $con->error;
        safe_log("✗ ERROR en prepare: " . $error);
        respond(['success' => false, 'error' => 'Error en prepare de asesores: ' . $error, 'checkpoint' => 7]);
    }
    
    $stmt_asesores->bind_param('si', $sucursal, $asesor_id);
    
    if (!$stmt_asesores->execute()) {
        $error = $stmt_asesores->error;
        safe_log("✗ ERROR en execute: " . $error);
        respond(['success' => false, 'error' => 'Error en execute de asesores: ' . $error, 'checkpoint' => 7]);
    }
    
    $result_asesores = $stmt_asesores->get_result();
    
    $asesores = [];
    while ($row = $result_asesores->fetch_assoc()) {
        $asesores[] = $row;
    }
    $stmt_asesores->close();
    
    safe_log("  - Total asesores: " . count($asesores));
    
    if (count($asesores) === 0) {
        safe_log("⚠ WARNING: No hay asesores disponibles en sucursal: " . $sucursal);
    }
    
    // =========================================================================
    // CHECKPOINT 8: Respuesta
    // =========================================================================
    safe_log("✓ Checkpoint 8: Generando respuesta");
    
    $response = [
        'success' => true,
        'clientes' => $clientes,
        'asesores' => $asesores,
        'debug' => [
            'checkpoint' => 8,
            'total_clientes' => count($clientes),
            'total_asesores' => count($asesores),
            'sucursal' => $sucursal,
            'asesor_id' => $asesor_id
        ]
    ];
    
    safe_log("═══ FIN EXITOSO ═══");
    
    respond($response);
    
} catch (Exception $e) {
    safe_log("═══ ERROR EXCEPTION ═══");
    safe_log("Mensaje: " . $e->getMessage());
    safe_log("Archivo: " . $e->getFile());
    safe_log("Línea: " . $e->getLine());
    
    respond([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'checkpoint' => 'exception'
    ]);
    
} catch (Error $e) {
    safe_log("═══ ERROR PHP ═══");
    safe_log("Mensaje: " . $e->getMessage());
    safe_log("Archivo: " . $e->getFile());
    safe_log("Línea: " . $e->getLine());
    
    respond([
        'success' => false,
        'error' => 'Error de PHP: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'checkpoint' => 'php_error'
    ]);
}

safe_log("═══ LLEGÓ AL FINAL ═══");
respond(['success' => false, 'error' => 'Script terminó inesperadamente', 'checkpoint' => 0]);