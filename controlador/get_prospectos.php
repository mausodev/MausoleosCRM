<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();

header('Content-Type: application/json');

try {
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
              ORDER BY id DESC";
    
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . mysqli_error($con));
    }
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    // Formato requerido por DataTables
    $response = array(
        "data" => $data
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(array(
        "error" => $e->getMessage()
    ));
}

mysqli_close($con);
?>
