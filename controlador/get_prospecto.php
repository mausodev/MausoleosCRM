<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID de prospecto requerido");
    }
    
    $id = intval($_GET['id']);
    
    $query = "SELECT 
                id,
                mes_seguimiento,
                fecha_captacion,
                servicio_persona_fallecida,
                fuente_revisar_nota,
                canal_repositorio,
                nombre_prospecto,
                telefono,
                estatus_lead,
                realizo_llamada_nombre_callcenter,
                dia_encuesta,
                accion_a_realizar,
                producto_interes,
                asesor_guardia,
                se_canalizo_asesor,
                estatus_venta,
                numero_contrato,
                descripcion_venta,
                monto,
                comentarios_finales,
                fecha_creado,
                creado_por,
                fecha_modificado,
                modificado_por
              FROM generacion_prospectos 
              WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(array(
            "success" => true,
            "data" => $row
        ));
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Prospecto no encontrado"
        ));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}

mysqli_close($con);
?>
