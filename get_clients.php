<?php
require './controlador/conexion.php';

$conexion = mysqli_connect('localhost', 'root', 'admin', 'mausoleo_local');

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$asesor_id = $_GET['asesor_id'];

// Asegúrate de que el asesor_id esté definido y sea un número
if (isset($asesor_id) && is_numeric($asesor_id)) {
    $sql = "SELECT CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_cliente, etapa
            FROM cliente
            WHERE asesor = ? 
              AND etapa IN ('ACTIVAR', 'ESTRECHAR', 'EN PRONOSTICO')
              AND DATE(fecha_modificado) = CURDATE()";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $asesor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }

    echo json_encode($clientes);
} else {
    echo json_encode([]);
}
?>
