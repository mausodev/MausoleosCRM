<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }
    
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("ID de prospecto requerido");
    }
    
    $id = intval($_POST['id']);
    
    // Verificar que el prospecto existe
    $check_query = "SELECT id FROM generacion_prospectos WHERE id = ?";
    $check_stmt = mysqli_prepare($con, $check_query);
    
    if (!$check_stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (!mysqli_fetch_assoc($check_result)) {
        mysqli_stmt_close($check_stmt);
        throw new Exception("Prospecto no encontrado");
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Eliminar el prospecto
    $delete_query = "DELETE FROM generacion_prospectos WHERE id = ?";
    $delete_stmt = mysqli_prepare($con, $delete_query);
    
    if (!$delete_stmt) {
        throw new Exception("Error al preparar la consulta de eliminación: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        echo json_encode(array(
            "success" => true,
            "message" => "Prospecto eliminado exitosamente"
        ));
    } else {
        throw new Exception("Error al eliminar el prospecto: " . mysqli_stmt_error($delete_stmt));
    }
    
    mysqli_stmt_close($delete_stmt);
    
} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}

mysqli_close($con);
?>
