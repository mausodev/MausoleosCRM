<?php
require './controlador/conexion.php';

// Disable error reporting to prevent PHP errors from breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;
    $puesto = $_GET['puesto'] ?? null;

        error_log("get_meta_venta.php - ID: $id, Puesto: $puesto");

    if (!$id || !$puesto) {
        throw new Exception('Missing required parameters');
    }

   
    $id_field = ($puesto === 'ASESOR') ? 'id_asesor' : 'id_cordinador';
    $id_value = $id;

    
    $query = "SELECT * FROM meta_venta WHERE $id_field = ? ORDER BY mes ASC";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $con->error);
    }

    $stmt->bind_param("i", $id_value);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Database result error: ' . $stmt->error);
    }

    $meta_data = [];
    while ($row = $result->fetch_assoc()) {
        $meta_data[] = [
            'mes' => $row['mes'],
            'meta' => $row['meta'],
            'meta_cero' => $row['meta_cero'],
            'nombre_mes' => $row['nombre_mes'],
            'id_asesor' => $row['id_asesor'],
            'id_cordinador' => $row['id_cordinador'],
            'campo' => $row['campo'],
            'plaza' => $row['plaza']
        ];
    }

    error_log("Datos encontrodaos: " . count($meta_data));

    echo json_encode(['success' => true, 'data' => $meta_data]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 