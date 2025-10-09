<?php
session_start();
require './controlador/conexion.php';

if (!isset($_SESSION['correo'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$sucursal = $_SESSION['sucursal'];

try {
    // Obtener los porcentajes de las etapas desde la tabla embudo_plaza
    $sqlEtapas = "SELECT etapa, porcentaje FROM embudo_plaza WHERE plaza = ? AND activo = 1 ORDER BY 
      CASE etapa
        WHEN 'BASE DE DATOS' THEN 1
        WHEN 'ACTIVAR' THEN 2
        WHEN 'ESTRECHAR' THEN 3
        WHEN 'EN PRONOSTICO' THEN 4
        WHEN 'CERRADO GANADO' THEN 5
        WHEN 'CERRADO PERDIDO' THEN 6
        ELSE 7
      END";

    $stmtEtapas = $con->prepare($sqlEtapas);
    if (!$stmtEtapas) {
        throw new Exception("Error preparando la consulta: " . $con->error);
    }
    $stmtEtapas->bind_param("s", $sucursal);
    $stmtEtapas->execute();
    $resultEtapas = $stmtEtapas->get_result();

    $etapas = [];
    if ($resultEtapas->num_rows > 0) {
        while ($rowEtapa = $resultEtapas->fetch_assoc()) {
            $etapas[$rowEtapa['etapa']] = (float)$rowEtapa['porcentaje'];
        }
    } else {
        // Valores por defecto si no se encuentran datos en la base de datos
        $etapas = [
            "BASE DE DATOS" => 0,
            "ACTIVAR" => 0,
            "ESTRECHAR" => 0.25,
            "EN PRONOSTICO" => 0.7,
            "CERRADO GANADO" => 1,
            "CERRADO PERDIDO" => 0
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($etapas);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 