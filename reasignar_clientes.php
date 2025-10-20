<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

require './controlador/conexion.php';
require './controlador/access_control.php';

function respond($data) {
    echo json_encode($data);
    exit;
}

function safe_log($message, $data = null) {
    if ($data === null) {
        error_log($message);
    } else {
        error_log($message . ": " . print_r($data, true));
    }
}

safe_log("═══ INICIO reasignar_clientes.php ═══");

try {
    // Leer datos del POST
    $input = file_get_contents('php://input');
    safe_log("Input recibido", $input);
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        safe_log("Error JSON: " . json_last_error_msg());
        respond([
            'success' => false, 
            'error' => 'Error al decodificar JSON: ' . json_last_error_msg()
        ]);
    }
    
    safe_log("Data decodificada", $data);
    
    // Validar que existan reasignaciones
    if (!isset($data['reasignaciones']) || !is_array($data['reasignaciones'])) {
        safe_log("Error: Datos de reasignación no válidos");
        respond([
            'success' => false, 
            'error' => 'Datos de reasignación no válidos'
        ]);
    }
    
    // Validar que haya al menos una reasignación
    if (empty($data['reasignaciones'])) {
        safe_log("Error: No hay reasignaciones para procesar");
        respond([
            'success' => false, 
            'error' => 'No hay reasignaciones para procesar'
        ]);
    }
    
    safe_log("Total de reasignaciones a procesar", count($data['reasignaciones']));
    
    // Obtener datos del usuario actual
    $accessData = verificarAcceso();
    $correo = $accessData['correo'];
    safe_log("Usuario que realiza la reasignación", $correo);
    
    // Iniciar transacción
    $con->begin_transaction();
    safe_log("Transacción iniciada");
    
    $reasignaciones_exitosas = 0;
    $errores = [];
    
    foreach ($data['reasignaciones'] as $index => $reasignacion) {
        safe_log("Procesando reasignación #" . ($index + 1), $reasignacion);
        
        // Validar que existan los campos necesarios
        if (!isset($reasignacion['cliente_id']) || !isset($reasignacion['nuevo_asesor_id'])) {
            $errores[] = "Reasignación #" . ($index + 1) . ": Datos incompletos";
            safe_log("Error: Datos incompletos en reasignación #" . ($index + 1));
            continue;
        }
        
        $cliente_id = intval($reasignacion['cliente_id']);
        $nuevo_asesor_id = intval($reasignacion['nuevo_asesor_id']);
        
        if ($cliente_id <= 0 || $nuevo_asesor_id <= 0) {
            $errores[] = "Reasignación #" . ($index + 1) . ": IDs inválidos";
            safe_log("Error: IDs inválidos - Cliente: $cliente_id, Asesor: $nuevo_asesor_id");
            continue;
        }

        $alias=0;

        $query_alias_nuevo_asesor = "SELECT iniciales from empleado where id = ?";
        $stmt_alias_na = $con->prepare($query_alias_nuevo_asesor);
        $stmt_alias_na->bind_param("i", $nuevo_asesor_id);
        $stmt_alias_na->execute();
        $result_alias_na = $stmt_alias_na->get_result();

        $tmp_alias = $result_alias_na->fetch_assoc();
        $alias=$tmp_alias['iniciales'];


        
        $query_update = "UPDATE cliente
                        SET asesor = ?,
                            modificado_por = ?,
                            fecha_modificado = NOW(),
                            nombre_asesor = ?
                        WHERE id = ?";
       
        $stmt_update = $con->prepare($query_update);
        
        if (!$stmt_update) {
            $error = $con->error;
            $errores[] = "Reasignación #" . ($index + 1) . ": Error en prepare: $error";
            safe_log("Error en prepare", $error);
            continue;
        }
        
        $stmt_update->bind_param("issi", $nuevo_asesor_id, $correo, $alias,$cliente_id);
       
        if ($stmt_update->execute()) {
            $reasignaciones_exitosas++;
            safe_log("✓ Cliente #$cliente_id reasignado exitosamente a asesor #$nuevo_asesor_id");
        } else {
            $error = $stmt_update->error;
            $errores[] = "Reasignación #" . ($index + 1) . ": Error en execute: $error";
            safe_log("Error en execute", $error);
        }
        
        $stmt_update->close();
    }
    
    // Si hubo algún error, hacer rollback
    if (!empty($errores) && $reasignaciones_exitosas === 0) {
        $con->rollback();
        safe_log("Rollback realizado - Todos los intentos fallaron");
        
        respond([
            'success' => false,
            'error' => 'Error al reasignar clientes',
            'detalles' => $errores
        ]);
    }
    
    // Si hubo al menos una reasignación exitosa, hacer commit
    $con->commit();
    safe_log("Commit realizado - Reasignaciones exitosas: $reasignaciones_exitosas");
    
    $response = [
        'success' => true,
        'message' => "$reasignaciones_exitosas cliente(s) reasignado(s) exitosamente",
        'total_procesados' => $reasignaciones_exitosas,
        'total_solicitados' => count($data['reasignaciones'])
    ];
    
    // Si hubo algunos errores pero también éxitos, incluirlos en la respuesta
    if (!empty($errores)) {
        $response['advertencias'] = $errores;
        safe_log("Algunas reasignaciones tuvieron errores", $errores);
    }
    
    safe_log("═══ FIN EXITOSO ═══");
    respond($response);
   
} catch (Exception $e) {
    if (isset($con) && $con->connect_errno === 0) {
        $con->rollback();
    }
    
    safe_log("═══ ERROR EXCEPTION ═══");
    safe_log("Mensaje: " . $e->getMessage());
    safe_log("Archivo: " . $e->getFile());
    safe_log("Línea: " . $e->getLine());
    
    respond([
        'success' => false,
        'error' => 'Error al reasignar clientes: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>