<?php

require './controlador/conexion.php';
require './controlador/access_control.php';


header('Content-Type: application/json');

if (!isset($_GET['asesor_id']) || empty($_GET['asesor_id'])) {
    echo json_encode(([
        'success' => false,
        'error' => 'ID de asesor no proporcionado'
    ]));
    exit;
}

$asesor_id = intval($_GET['asesor_id']);

if (!$asesor_id) {
    echo json_encode(['success'=>false, 'error'=>'No se recibio el id del asesor']);
    exit;
}

try {

    $accessData = verificarAcceso();
    $sucursal = $accessData['sucursal'];
    $query_clientes = "SELECT id, nombre, etapa 
                      FROM cliente 
                      WHERE asesor = ? 
                      AND etapa != 'CERRADO PERDIDO' 
                      AND etapa != 'CERRADO GANADO'
                      ORDER BY nombre";
    
    $stmt_clientes = $con->prepare($query_clientes);
    if (!$stmt_clientes) {
        throw new Exception("Error al preparar consulta de clientes: " . $con->error);
    }
    $stmt_clientes->bind_param("i", $asesor_id);
    $stmt_clientes->execute();
    $result_clientes = $stmt_clientes->get_result();
    
    $clientes = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = [
            'id'=>$row['id'],
            'nombre'=>$row['nombre'],
            'etapa' => $row['etapa']
        ];
    }

    $stmt_clientes->close();


    $query_asesores = "SELECT id, correo, puesto 
                       FROM empleado 
                       WHERE activo = 1 
                       AND sucursal = ? 
                       AND id != ?
                       AND puesto = 'ASESOR'
                       ORDER BY correo";

    $stmt_asesores = $con->prepare($query_asesores);
     if (!$stmt_asesores) {
        throw new Exception("Error al preparar consulta de asesores: " . $con->error);
    }
    $stmt_asesores->bind_param('si',$sucursal,$asesor_id);
    $stmt_asesores->execute();
    $result_asesores = $stmt_asesores->get_result();
    
    $asesores=[];
    while ($row = $result_asesores->fetch_assoc()) {
        $asesores[] = [
            'id' => $row['id'],
            'correo' => $row['correo'],
            'puesto' => $row['puesto']
        ];
    }
    $stmt_asesores->close();

    echo json_encode([
        'success' => true,
        'clientes' => $clientes,
        'asesores' => $asesores,
        'debug' => [
            'total_clientes' => count($clientes),
            'total_asesores' => count($asesores),
            'sucursal' => $sucursal
        ]
    ]);
    
    
} catch (Exception $e) {

    error_log("Error en get_clientes_asesor.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e
    ]);
}

$con->$close();

?>