<?php
require_once 'conexion.php';
require_once 'access_control.php';

// Verificar acceso
$accessData = verificarAcceso();
$usuario = $accessData['inicial'];

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }
    
    // Validar campo requerido
    if (empty($_POST['nombre_prospecto'])) {
        throw new Exception("El nombre del prospecto es requerido");
    }
    
    // Obtener datos del formulario
    $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $mes_seguimiento = $_POST['mes_seguimiento'] ?? null;
    $fecha_captacion = !empty($_POST['fecha_captacion']) ? $_POST['fecha_captacion'] : null;
    $servicio_persona_fallecida = $_POST['servicio_persona_fallecida'] ?? null;
    $fuente_revisar_nota = $_POST['fuente_revisar_nota'] ?? null;
    $canal_repositorio = $_POST['canal_repositorio'] ?? null;
    $nombre_prospecto = $_POST['nombre_prospecto'];
    $telefono = $_POST['telefono'] ?? null;
    $estatus_lead = $_POST['estatus_lead'] ?? null;
    $realizo_llamada_nombre_callcenter = $_POST['realizo_llamada_nombre_callcenter'] ?? null;
    $dia_encuesta = !empty($_POST['dia_encuesta']) ? $_POST['dia_encuesta'] : null;
    $accion_a_realizar = $_POST['accion_a_realizar'] ?? null;
    $producto_interes = $_POST['producto_interes'] ?? null;
    $asesor_guardia = $_POST['asesor_guardia'] ?? null;
    $se_canalizo_asesor = $_POST['se_canalizo_asesor'] ?? null;
    $estatus_venta = $_POST['estatus_venta'] ?? null;
    $numero_contrato = $_POST['numero_contrato'] ?? null;
    $descripcion_venta = $_POST['descripcion_venta'] ?? null;
    $monto = !empty($_POST['monto']) ? floatval($_POST['monto']) : null;
    $comentarios_finales = $_POST['comentarios_finales'] ?? null;
    
    if ($id) {
        // Actualizar registro existente
        $query = "UPDATE generacion_prospectos SET 
                    mes_seguimiento = ?,
                    fecha_captacion = ?,
                    servicio_persona_fallecida = ?,
                    fuente_revisar_nota = ?,
                    canal_repositorio = ?,
                    nombre_prospecto = ?,
                    telefono = ?,
                    estatus_lead = ?,
                    realizo_llamada_nombre_callcenter = ?,
                    dia_encuesta = ?,
                    accion_a_realizar = ?,
                    producto_interes = ?,
                    asesor_guardia = ?,
                    se_canalizo_asesor = ?,
                    estatus_venta = ?,
                    numero_contrato = ?,
                    descripcion_venta = ?,
                    monto = ?,
                    comentarios_finales = ?,
                    modificado_por = ?
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssssssssssssssdssi", 
            $mes_seguimiento,
            $fecha_captacion,
            $servicio_persona_fallecida,
            $fuente_revisar_nota,
            $canal_repositorio,
            $nombre_prospecto,
            $telefono,
            $estatus_lead,
            $realizo_llamada_nombre_callcenter,
            $dia_encuesta,
            $accion_a_realizar,
            $producto_interes,
            $asesor_guardia,
            $se_canalizo_asesor,
            $estatus_venta,
            $numero_contrato,
            $descripcion_venta,
            $monto,
            $comentarios_finales,
            $usuario,
            $id
        );
        
    } else {
        // Crear nuevo registro
        $query = "INSERT INTO generacion_prospectos (
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
                    creado_por
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssssssssssssssdss", 
            $mes_seguimiento,
            $fecha_captacion,
            $servicio_persona_fallecida,
            $fuente_revisar_nota,
            $canal_repositorio,
            $nombre_prospecto,
            $telefono,
            $estatus_lead,
            $realizo_llamada_nombre_callcenter,
            $dia_encuesta,
            $accion_a_realizar,
            $producto_interes,
            $asesor_guardia,
            $se_canalizo_asesor,
            $estatus_venta,
            $numero_contrato,
            $descripcion_venta,
            $monto,
            $comentarios_finales,
            $usuario
        );
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $action = $id ? "actualizado" : "creado";
        echo json_encode(array(
            "success" => true,
            "message" => "Prospecto $action exitosamente",
            "id" => $id ? $id : mysqli_insert_id($con)
        ));
    } else {
        throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
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
