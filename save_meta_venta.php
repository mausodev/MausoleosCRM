<?php
// Verificar si ya hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1); // ← CAMBIADO A 1 para ver errores
ini_set('log_errors', 1);

// Limpiar cualquier salida previa
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Establecer header JSON inmediatamente
header('Content-Type: application/json; charset=utf-8');

// Array para debugging
$debug_info = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'none',
    'input_raw' => file_get_contents('php://input'),
    'session_exists' => isset($_SESSION['correo'])
];

try {
    // Incluir el archivo de conexión usando la ruta relativa correcta
    require_once './controlador/conexion.php';
    
    // Verificar que tenemos conexión a la base de datos
    if (!isset($con) || $con->connect_errno) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Leer los datos JSON enviados
    $input = file_get_contents('php://input');
    if (empty($input)) {
        // Devolver info de debug si no hay datos
        throw new Exception('No se recibieron datos. Debug: ' . json_encode($debug_info));
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }
    
    // Agregar datos recibidos al debug
    $debug_info['data_received'] = $data;
    
    // Validar que tenemos todos los datos necesarios
    if (!isset($data['id']) || !isset($data['puesto']) || !isset($data['metas'])) {
        throw new Exception('Faltan parámetros requeridos. Recibido: ' . json_encode($data));
    }
    
    // Obtener los datos de sesión
    if (!isset($_SESSION['correo']) || !isset($_SESSION['sucursal'])) {
        throw new Exception('No hay datos de sesión disponibles');
    }
    
    $usuario_logueado = $_SESSION['correo'];
    $plaza_usuario = $_SESSION['sucursal'];
    $id = intval($data['id']);
    $puesto = $data['puesto'];
    $metas = $data['metas'];
    
    // Validar el puesto
    if ($puesto !== 'ASESOR' && $puesto !== 'COORDINADOR') {
        throw new Exception('Puesto inválido');
    }
    
    // Obtener el modelo (tipo) del empleado desde la tabla empleado
    $empleado_query = "SELECT tipo FROM empleado WHERE id = ?";
    $empleado_stmt = $con->prepare($empleado_query);
    if (!$empleado_stmt) {
        throw new Exception('Error al preparar consulta de empleado: ' . $con->error);
    }
    
    $empleado_stmt->bind_param("i", $id);
    $empleado_stmt->execute();
    $empleado_result = $empleado_stmt->get_result();
    
    if ($empleado_result->num_rows === 0) {
        throw new Exception('No se encontró el empleado con ID: ' . $id);
    }
    
    $empleado_data = $empleado_result->fetch_assoc();
    $modelo = $empleado_data['tipo'] ?? 'MLE';
    $empleado_stmt->close();
    
    $producto_meta = 0;
    
    // Determinar el campo de ID según el puesto
    $id_field = ($puesto === 'ASESOR') ? 'id_asesor' : 'id_cordinador';
    
    $fecha_actual = date('Y-m-d H:i:s');
    
    // Log para debugging
    $debug_info['id_field'] = $id_field;
    $debug_info['fecha_actual'] = $fecha_actual;
    
    // Iniciar la transacción para asegurar integridad de datos
    $con->begin_transaction();
    
    $registros_procesados = 0;
    
    // Procesar cada meta enviada desde el formulario
    foreach ($metas as $meta) {
        $mes = intval($meta['mes']);
        $meta_valor = floatval($meta['meta']);
        $meta_cero = isset($meta['meta_cero']) ? intval($meta['meta_cero']) : 0;
        $nombre_mes = $meta['nombre_mes'];
        
        $check_sql = "SELECT id FROM meta_venta WHERE mes = ? AND {$id_field} = ?";
        $check_stmt = $con->prepare($check_sql);
        
        if (!$check_stmt) {
            throw new Exception('Error al preparar consulta de verificación: ' . $con->error . ' SQL: ' . $check_sql);
        }
        
        $check_stmt->bind_param("ii", $mes, $id);
        $check_stmt->execute();
        $resultado = $check_stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $update_sql = "UPDATE meta_venta 
                          SET meta = ?, 
                              meta_cero = ?, 
                              campo = ?, 
                              plaza = ?, 
                              modelo = ?,
                              producto_meta = ?,
                              fecha_modificado = ?,
                              modificado_por = ?
                          WHERE mes = ? AND {$id_field} = ?";
            
            $update_stmt = $con->prepare($update_sql);
            if (!$update_stmt) {
                throw new Exception('Error al preparar actualización: ' . $con->error . ' SQL: ' . $update_sql);
            }
            
            $update_stmt->bind_param("disssissii",  // d-i-s-s-s-i-s-s-i-i = 10 tipos
                $meta_valor,         // 1 - d
                $meta_cero,          // 2 - i
                $usuario_logueado,   // 3 - s
                $plaza_usuario,      // 4 - s
                $modelo,             // 5 - s
                $producto_meta,      // 6 - i
                $fecha_actual,       // 7 - s
                $usuario_logueado,   // 8 - s
                $mes,                // 9 - i
                $id                  // 10 - i
            );
            
            if (!$update_stmt->execute()) {
                throw new Exception('Error al actualizar: ' . $update_stmt->error);
            }
            
            $update_stmt->close();
        } else {
            $insert_sql = "INSERT INTO meta_venta 
                          (mes, meta, meta_cero, nombre_mes, {$id_field}, campo, plaza, modelo, producto_meta, fecha_creado, creado_por, fecha_modificado, modificado_por) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
            
            $insert_stmt = $con->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception('Error al preparar inserción: ' . $con->error . ' SQL: ' . $insert_sql);
            }
            
            $insert_stmt->bind_param("idisisssissss",  // i-d-i-s-i-s-s-s-i-s-s = 11 tipos
                $mes,                // 1 - i (integer)
                $meta_valor,         // 2 - d (double)
                $meta_cero,          // 3 - i (integer)
                $nombre_mes,         // 4 - s (string)
                $id,                 // 5 - i (integer) ← ESTE era el problema
                $usuario_logueado,   // 6 - s (string)
                $plaza_usuario,      // 7 - s (string)
                $modelo,             // 8 - s (string)
                $producto_meta,      // 9 - i (integer)
                $fecha_actual,       // 10 - s (string)
                $usuario_logueado,    // 11 - s (string)
                $fecha_actual,       // 12 -  s (string)
                $usuario_logueado    // 13 - s (string)           
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception('Error al insertar: ' . $insert_stmt->error);
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
        $registros_procesados++;
    }
    
    $con->commit();
    
    // Limpiar buffer antes de enviar JSON
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'records_processed' => $registros_procesados,
            'modelo_usado' => $modelo,
            'producto_meta_usado' => $producto_meta
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($con) && !$con->connect_errno) {
        $con->rollback();
    }
    
    // Limpiar buffer antes de enviar JSON de error
    ob_end_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug_info
    ]);
}

// Terminar el script inmediatamente para evitar salida adicional
exit;