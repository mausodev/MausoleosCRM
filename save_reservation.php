<?php
// Conexión a la base de datos (asumiendo que ya existe)
require_once './controlador/conexion.php';
var_dump('entre');
die();

// CREATE - Crear nueva reservación
function createReservation($id_vehiculo, $modelo, $fecha_inicio, $fecha_fin, $kilometraje, $usuario, $creado_por) {
    global $conn;
    
    // Verificar si existe una reservación para el mismo vehículo en el mismo rango de fechas
    $check_query = "SELECT id FROM reserva_auto 
                   WHERE id_vehiculo = ? 
                   AND ((fecha_inicio BETWEEN ? AND ?) 
                   OR (fecha_fin BETWEEN ? AND ?))
                   AND estatus = 'ACTIVO'";
                   
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("issss", $id_vehiculo, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ["success" => false, "message" => "Ya existe una reservación para este vehículo en el rango de fechas seleccionado"];
    }
    
    // Insertar nueva reservación
    $query = "INSERT INTO reserva_auto (id_vehiculo, modelo, fecha_inicio, fecha_fin, 
              kilometraje, usuario, estatus, creado_por, fecha_creado) 
              VALUES (?, ?, ?, ?, ?, ?, 'ACTIVO', ?, NOW())";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssiss", $id_vehiculo, $modelo, $fecha_inicio, $fecha_fin, 
                      $kilometraje, $usuario, $creado_por);
    
    if ($stmt->execute()) {
        return ["success" => true, "message" => "Reservación creada exitosamente"];
    }
    return ["success" => false, "message" => "Error al crear la reservación"];
}

// UPDATE - Actualizar reservación
function updateReservation($id, $kilometraje, $estatus, $notas, $modificado_por) {
    global $conn;
    
    $query = "UPDATE reserva_auto 
              SET kilometraje = ?, 
                  estatus = ?, 
                  notas = ?, 
                  modificado_por = ?, 
                  fecha_modificado = NOW() 
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisi", $kilometraje, $estatus, $notas, $modificado_por, $id);
    
    if ($stmt->execute()) {
        return ["success" => true, "message" => "Reservación actualizada exitosamente"];
    }
    return ["success" => false, "message" => "Error al actualizar la reservación"];
}

// DELETE - Eliminar reservación
function deleteReservation($id) {
    global $conn;
    
    $query = "DELETE FROM reserva_auto WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return ["success" => true, "message" => "Reservación eliminada exitosamente"];
    }
    return ["success" => false, "message" => "Error al eliminar la reservación"];
}

// READ - Obtener reservación por ID
function getReservation($id) {
    global $conn;
    
    $query = "SELECT * FROM reserva_auto WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// READ - Obtener todas las reservaciones
function getAllReservations() {
    global $conn;
    
    $query = "SELECT * FROM reserva_auto ORDER BY fecha_inicio DESC";
    $result = $conn->query($query);
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    return $reservations;
}
?>
