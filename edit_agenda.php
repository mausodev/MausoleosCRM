<?php
require './controlador/conexion.php';

// Handle GET request for fetching activity data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch activity data
    $sql = "SELECT * FROM agenda_personal WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $activityData = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($activityData);
        exit();
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Activity not found']);
        exit();
    }
}

$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
$result = $con->query($sqlCierre);

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $mes = $row['mes']; 
  
  } else {
    $mes = 'N/A';
  }

  // Lógica para manejar reprogramación
  $fecha_reprogramacion = $_POST['fecha_reprogramacion'];
  $estatus = $_POST['estatus'];
  
  // Si se proporciona fecha de reprogramación, cambiar estatus automáticamente a REPROGRAMADA
  if ($fecha_reprogramacion !== "") {
    $estatus = 'REPROGRAMADA';
    
    // Obtener contador de reprogramaciones
    $sqlReprogramacion = "SELECT reprogramacion FROM agenda_personal WHERE id = ?";
    $stmtReprogramacion = $con->prepare($sqlReprogramacion);
    $stmtReprogramacion->bind_param('i', $_POST['id']);
    $stmtReprogramacion->execute();
    $resultReprogramacion = $stmtReprogramacion->get_result();
    
    if ($resultReprogramacion->num_rows > 0) {
      $rowReprogramacion = $resultReprogramacion->fetch_assoc();
      $reprogramacion = $rowReprogramacion['reprogramacion'] + 1;
    } else {
      $reprogramacion = 1;
    }
  } else {
    $reprogramacion = 0;
  }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    // Fetch existing activity data
    $sqlSelect = "SELECT * FROM agenda_personal WHERE id = ?";
    $stmtSelect = $con->prepare($sqlSelect);
    $stmtSelect->bind_param('i', $id);
    $stmtSelect->execute();
    $resultSelect = $stmtSelect->get_result();
    $activityData = $resultSelect->fetch_assoc();

    /*VAR_DUMP($sqlSelect);
    DIE();*/
    $actividad = $_POST['actividad'];
    $cita = $_POST['cita'];
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];
    $disponible = $_POST['disponible'];
    // $estatus ya se definió arriba con la lógica de reprogramación
    // $fecha_reprogramacion ya se definió arriba
    $cumplio = $_POST['cumplio'];
    $fuente_prospeccion = $_POST['fuente_prospeccion'];
    $notas = $_POST['notas'];
    $fechaModificado = $_POST['fecha_modificado'];
    $modificadoPor = $_POST['modificado_por'];

    // Validar que la fecha no sea anterior a hoy
    $fecha_actual = date('Y-m-d H:i:s');
    if ($inicio < $fecha_actual) {
        header("Location: agenda.php?error=past_date");
        exit();
    } else {
        // Validar empalme de horarios si se está editando
        require __DIR__ . '/controlador/validate_schedule.php';
        $hayEmpalme = validarEmpalmeHorarios($_SESSION['id'], $inicio, $fin, $id);
        
        if ($hayEmpalme) {
            header("Location: agenda.php?error=empalme");
            exit();
        } else {
            $sql = "UPDATE agenda_personal SET actividad = ?, cita = ?, fechahora_inicio = ?, fechahora_fin = ?, disponible = ?, completada = ?, fecha_reprogramacion = ?, mes = ?, reprogramacion = ?, cumplio = ?, fuente_prospeccion = ?, notas = ?, fecha_modificado = ?, modificado_por = ? WHERE id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param('ssssssssissssii', $actividad, $cita, $inicio, $fin, $disponible, $estatus, $fecha_reprogramacion, $mes, $reprogramacion, $cumplio, $fuente_prospeccion, $notas, $fechaModificado, $modificadoPor, $id);

            if ($stmt->execute()) {
                header("Location: agenda.php?update=success");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
} else if (isset($_GET['id'])) {
    // If it's a GET request with an ID, fetch the data for editing
    $id = $_GET['id'];
    $sqlSelect = "SELECT * FROM agenda_personal WHERE id = ?";
    $stmtSelect = $con->prepare($sqlSelect);
    $stmtSelect->bind_param('i', $id);
    $stmtSelect->execute();
    $resultSelect = $stmtSelect->get_result();
    $activityData = $resultSelect->fetch_assoc();
    
    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($activityData);
    exit();
}
?>
