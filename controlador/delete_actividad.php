<?php
require './conexion.php';
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo_asesor = $_SESSION['correo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $actividad_id = $_POST['actividad_id'] ?? null;

        if (!$actividad_id) {
            throw new Exception('ID de actividad requerido');
        }

        // Verificar que la actividad pertenece al asesor logueado
        $sql = "DELETE FROM agenda_personal WHERE id = ? AND correo_asesor = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("is", $actividad_id, $correo_asesor);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Actividad eliminada exitosamente'
                ]);
            } else {
                throw new Exception('No se encontró la actividad o no tienes permisos para eliminarla');
            }
        } else {
            throw new Exception('Error al eliminar la actividad: ' . $stmt->error);
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
