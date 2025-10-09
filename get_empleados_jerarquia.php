<?php
session_start();
require './controlador/conexion.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Obtener parámetros
$ejecutivo = isset($_GET['ejecutivo']) ? $_GET['ejecutivo'] : '';
$coordinador = isset($_GET['coordinador']) ? $_GET['coordinador'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$sucursal = $_SESSION['sucursal'];

header('Content-Type: application/json');

try {
    $empleados = [];
    
    if ($tipo == 'coordinador' && !empty($ejecutivo)) {
        // Obtener coordinadores bajo un ejecutivo específico
        $sql = "SELECT correo, nombre, iniciales 
                FROM empleado 
                WHERE puesto = 'COORDINADOR' 
                AND supervisor = ? 
                AND sucursal = ? 
                AND activo = 1 
                ORDER BY correo";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $ejecutivo, $sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $empleados[] = [
                'correo' => $row['correo'],
                'nombre' => $row['nombre'],
                'iniciales' => $row['iniciales']
            ];
        }
        
    } elseif ($tipo == 'asesor' && !empty($coordinador)) {
        // Obtener asesores bajo un coordinador específico
        $sql = "SELECT correo, nombre, iniciales 
                FROM empleado 
                WHERE puesto = 'ASESOR' 
                AND supervisor = ? 
                AND sucursal = ? 
                AND activo = 1 
                ORDER BY correo";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $coordinador, $sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $empleados[] = [
                'correo' => $row['correo'],
                'nombre' => $row['nombre'],
                'iniciales' => $row['iniciales']
            ];
        }
        
    } elseif ($tipo == 'asesor' && !empty($ejecutivo)) {
        // Obtener asesores bajo un ejecutivo (a través de coordinadores)
        $sql = "SELECT correo, nombre, iniciales 
                FROM empleado 
                WHERE puesto = 'ASESOR' 
                AND supervisor IN (
                    SELECT correo FROM empleado 
                    WHERE supervisor = ? AND puesto = 'COORDINADOR'
                )
                AND sucursal = ? 
                AND activo = 1 
                ORDER BY correo";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $ejecutivo, $sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $empleados[] = [
                'correo' => $row['correo'],
                'nombre' => $row['nombre'],
                'iniciales' => $row['iniciales']
            ];
        }
    }
    
    echo json_encode($empleados);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
