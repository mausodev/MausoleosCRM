<?php
require './controlador/conexion.php';

$sql = "SELECT 
    ra.id,
    ra.modelo as title,
    ra.fecha_inicio as start,
    ra.fecha_fin as end,
    ra.id_vehiculo,
    ra.kilometraje,
    ra.destino
FROM reserva_auto ra
WHERE ra.estatus = 'ACTIVO'";

$result = $con->query($sql);
$events = array();

if ($result) {
    while($row = $result->fetch_assoc()) {
        $events[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'start' => $row['fecha_inicio'],
            'end' => $row['fecha_fin'],
            'id_vehiculo' => $row['id_vehiculo'],
            'kilometraje' => $row['kilometraje'],
            'destino' => $row['destino']
        );
    }
}

header('Content-Type: application/json');
echo json_encode($events);
?> 