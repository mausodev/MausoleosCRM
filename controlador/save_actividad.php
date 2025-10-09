<?php
require './conexion.php';
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo_asesor = strtoupper($_SESSION['correo']);
$plaza_usuario = strtoupper($_SESSION['sucursal']);
$id_empleado = $_SESSION['id'];
$fecha_modificado = date("Y-m-d H:i:s");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $actividad_id = $_POST['actividad_id'] ?? null;
        $cliente_id = $_POST['cliente_id'] ?? null;
        $actividad = strtoupper($_POST['actividad_tipo'] ?? '');
        $cita = strtoupper($_POST['cita_descripcion'] ?? '');
        $fecha_inicio = $_POST['fecha_inicio_actividad'] ?? '';
        $fecha_fin = $_POST['fecha_fin_actividad'] ?? '';
        $completada = strtoupper($_POST['completada_actividad'] ?? 'PENDIENTE');
        $fuente_prospeccion = strtoupper($_POST['fuente_prospeccion_actividad'] ?? '');
        $notas = strtoupper($_POST['notas_actividad'] ?? '');

        // Validaciones básicas
        if (empty($cliente_id) || empty($actividad) || empty($cita) || empty($fecha_inicio) || empty($fecha_fin)) {
            throw new Exception('Todos los campos obligatorios deben ser completados');
        }

        // Obtener el mes actual
        $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
        $result = $con->query($sqlCierre);
        $mes = 'N/A';
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $mes = $row['mes']; 
        }

        if ($actividad_id) {
            // Actualizar actividad existente
            $sql = "UPDATE agenda_personal SET 
                    actividad = ?, 
                    cita = ?, 
                    fechahora_inicio = ?, 
                    fechahora_fin = ?, 
                    completada = ?, 
                    fuente_prospeccion = ?, 
                    notas = ?, 
                    fecha_modificado = ?,
                    correo_asesor = ?,
                    plaza = ?,
                    id_empleado = ?
                    WHERE id = ? AND correo_asesor = ?";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssssssssssiss", $actividad, $cita, $fecha_inicio, $fecha_fin, 
                             $completada, $fuente_prospeccion, $notas, $fecha_modificado, 
                             $correo_asesor, $plaza_usuario, $id_empleado, $actividad_id, $correo_asesor);
            
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Actividad actualizada exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar la actividad: ' . $stmt->error);
            }
        } else {
            // Crear nueva actividad
            $fecha_creado = date("Y-m-d H:i:s");
            
            $sql = "INSERT INTO agenda_personal 
                    (actividad, cita, fechahora_inicio, fechahora_fin, completada, cliente, 
                     correo_asesor, plaza, id_empleado, fuente_prospeccion, fecha_creado, fecha_modificado, 
                     mes, notas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssssissssssss", $actividad, $cita, $fecha_inicio, $fecha_fin, 
                             $completada, $cliente_id, $correo_asesor, $plaza_usuario, $id_empleado, 
                             $fuente_prospeccion, $fecha_creado, $fecha_modificado, $mes, $notas);
            
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Actividad creada exitosamente',
                    'id' => $con->insert_id
                ]);
            } else {
                throw new Exception('Error al crear la actividad: ' . $stmt->error);
            }
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
