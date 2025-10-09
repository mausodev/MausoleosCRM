<?php
/**
 * Validación de horarios para evitar empalmes en la agenda
 */

require __DIR__ . '/conexion.php';

// Función para validar si hay empalme de horarios
function validarEmpalmeHorarios($id_empleado, $fecha_inicio, $fecha_fin, $excluir_id = null) {
    global $con;
    
    // Convertir fechas a formato datetime
    $inicio = date('Y-m-d H:i:s', strtotime($fecha_inicio));
    $fin = date('Y-m-d H:i:s', strtotime($fecha_fin));
    
    // Query para verificar empalmes
    $sql = "SELECT COUNT(*) as empalmes 
            FROM agenda_personal 
            WHERE id_empleado = ? 
            AND completada NOT IN ('CANCELADA', 'REPROGRAMADA')
            AND (
                (fechahora_inicio < ? AND fechahora_fin > ?) OR  -- Nueva actividad empalma con existente
                (fechahora_inicio < ? AND fechahora_fin > ?) OR  -- Actividad existente empalma con nueva
                (fechahora_inicio >= ? AND fechahora_fin <= ?)   -- Nueva actividad está dentro de existente
            )";
    
    $params = [$id_empleado, $fin, $inicio, $inicio, $fin, $inicio, $fin];
    $paramTypes = "issssss";
    
    // Si se está editando, excluir el registro actual
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
        $paramTypes .= "i";
    }
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['empalmes'] > 0;
}

// Función para obtener actividades empalmadas
function obtenerActividadesEmpalmadas($id_empleado, $fecha_inicio, $fecha_fin, $excluir_id = null) {
    global $con;
    
    $inicio = date('Y-m-d H:i:s', strtotime($fecha_inicio));
    $fin = date('Y-m-d H:i:s', strtotime($fecha_fin));
    
    $sql = "SELECT id, actividad, cita, fechahora_inicio, fechahora_fin, completada
            FROM agenda_personal 
            WHERE id_empleado = ? 
            AND completada NOT IN ('CANCELADA', 'REPROGRAMADA')
            AND (
                (fechahora_inicio < ? AND fechahora_fin > ?) OR
                (fechahora_inicio < ? AND fechahora_fin > ?) OR
                (fechahora_inicio >= ? AND fechahora_fin <= ?)
            )";
    
    $params = [$id_empleado, $fin, $inicio, $inicio, $fin, $inicio, $fin];
    $paramTypes = "issssss";
    
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
        $paramTypes .= "i";
    }
    
    $sql .= " ORDER BY fechahora_inicio";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $actividades = [];
    while ($row = $result->fetch_assoc()) {
        $actividades[] = $row;
    }
    
    return $actividades;
}

// API endpoint para validación AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'validar_empalme') {
        $id_empleado = intval($_POST['id_empleado']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $excluir_id = isset($_POST['excluir_id']) ? intval($_POST['excluir_id']) : null;
        
        $hayEmpalme = validarEmpalmeHorarios($id_empleado, $fecha_inicio, $fecha_fin, $excluir_id);
        
        if ($hayEmpalme) {
            $actividadesEmpalmadas = obtenerActividadesEmpalmadas($id_empleado, $fecha_inicio, $fecha_fin, $excluir_id);
            
            echo json_encode([
                'empalme' => true,
                'actividades' => $actividadesEmpalmadas,
                'mensaje' => 'Existe un empalme de horarios con actividades existentes.'
            ]);
        } else {
            echo json_encode([
                'empalme' => false,
                'mensaje' => 'No hay empalmes de horarios.'
            ]);
        }
    }
}
?>
