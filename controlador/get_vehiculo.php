<?php
require_once 'conexion.php';

if(isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Preparar la consulta para evitar inyección SQL
    $stmt = $con->prepare("SELECT * FROM vehiculo WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        // Formatear las fechas para el input datetime-local
        if($row['servicio']) {
            $row['servicio'] = date('Y-m-d\TH:i', strtotime($row['servicio']));
        }
        if($row['proximo_servicio']) {
            $row['proximo_servicio'] = date('Y-m-d\TH:i', strtotime($row['proximo_servicio']));
        }
        if($row['vigencia']) {
            $row['vigencia'] = date('Y-m-d', strtotime($row['vigencia']));
        }
        
        // Devolver los datos en formato JSON
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Vehículo no encontrado']);
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
}

$con->close();
?> 