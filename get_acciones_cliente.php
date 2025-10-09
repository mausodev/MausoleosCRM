<?php
require './controlador/conexion.php';

if (isset($_POST['cliente_id']) && isset($_POST['mes'])) {
    $cliente_id = $_POST['cliente_id'];
    $mes = $_POST['mes'];
    
    /*var_dump($cliente_id);
    die();*/
    // Query para obtener las acciones del cliente
    $query = "SELECT a.actividad, a.cita, a.completada, a.notas, a.fuente_prospeccion, 
                     a.fecha_reprogramacion, a.fecha_creado, a.fecha_modificado,
                     c.id as id_cliente, c.nombre as nombre_cliente
              FROM agenda_personal a
              JOIN cliente c ON a.cliente = c.id
              WHERE a.cliente = ? AND a.mes = ? 
              ORDER BY a.fecha_creado DESC";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $cliente_id, $mes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    
    if ($result->num_rows > 0) {
        $firstRow = true;
        while ($row = $result->fetch_assoc()) {
            if ($firstRow) {
                $html .= "<tr class='table-info'><td colspan='8' class='text-center fw-bold'>Cliente: " . htmlspecialchars($row['nombre_cliente']) . " (ID: " . $row['id_cliente'] . ")</td></tr>";
                $firstRow = false;
            }
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($row['actividad']) . "</td>";
            $html .= "<td>" . htmlspecialchars($row['cita']) . "</td>";
            $html .= "<td>" . ($row['completada'] ? 'Completada' : 'Pendiente') . "</td>";
            $html .= "<td>" . htmlspecialchars($row['fuente_prospeccion']) . "</td>";
            $html .= "<td>" . htmlspecialchars($row['notas']) . "</td>";
            $html .= "<td>" . date('d/m/Y H:i', strtotime($row['fecha_creado'])) . "</td>";
            $html .= "<td>" . date('d/m/Y H:i', strtotime($row['fecha_modificado'])) . "</td>";
            $html .= "<td>" . ($row['fecha_reprogramacion'] ? date('d/m/Y H:i', strtotime($row['fecha_reprogramacion'])) : 'N/A') . "</td>";
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='8' class='text-center'>No hay acciones registradas para este cliente</td></tr>";
    }
    
    echo $html;
} else {
    echo "<tr><td colspan='8' class='text-center'>Error: Datos incompletos</td></tr>";
}
?> 