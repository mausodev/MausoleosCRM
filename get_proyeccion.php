<?php
session_start();
require './controlador/conexion.php';

if (!isset($_SESSION['correo'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if (isset($_POST['asesor_id'])) {
    $asesor_id = $_POST['asesor_id'];
    $sucursal = $_SESSION['sucursal'];
    
    // Obtener el mes actual
    $sql_mes = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result_mes = $con->query($sql_mes);
    $mes = '';
    if ($result_mes->num_rows > 0) {
        $row = $result_mes->fetch_assoc();
        $mes = $row['mes'];
    }
    
    // Query para obtener la proyección
    $query = "SELECT 
                c.id as id_cliente,
                CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) as cliente,
                c.etapa,
                c.mes,
                COALESCE(c.venta_embudo, 0) as venta_embudo
              FROM cliente c
              WHERE c.asesor = ?
              AND c.mes = ?
              AND c.etapa IN ('ACTIVAR', 'ESTRECHAR', 'EN PRONOSTICO')
              ORDER BY 
                CASE c.etapa
                    WHEN 'ACTIVAR' THEN 1
                    WHEN 'ESTRECHAR' THEN 2
                    WHEN 'EN PRONOSTICO' THEN 3
                END";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $asesor_id, $mes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $proyeccion = array();
    $total_proyectado = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $proyeccion[] = array(
                'id_cliente' => $row['id_cliente'],
                'cliente' => $row['cliente'],
                'etapa' => $row['etapa'],
                'mes' => $row['mes'],
                'venta_embudo' => $row['venta_embudo']
            );
            $total_proyectado += $row['venta_embudo'];
        }
    }
    
    // Obtener el menor precio de la plaza CUAUHTEMOC
    $sql_precio = "SELECT MIN(precio) as menor_precio FROM precio_servicio WHERE plaza = 'CUAUHTEMOC'";
    $result_precio = $con->query($sql_precio);
    $menor_precio = 0;
    if ($result_precio->num_rows > 0) {
        $row = $result_precio->fetch_assoc();
        $menor_precio = $row['menor_precio'];
    }
    
    // Calcular el número de productos
    $productos = $menor_precio > 0 ? $total_proyectado / $menor_precio : 0;
    
    // Obtener los porcentajes de las etapas desde la tabla embudo_plaza
    $sqlEtapas = "SELECT etapa, porcentaje FROM embudo_plaza WHERE plaza = ? AND activo = 1 ORDER BY 
      CASE etapa
        WHEN 'BASE DE DATOS' THEN 1
        WHEN 'ACTIVAR' THEN 2
        WHEN 'ESTRECHAR' THEN 3
        WHEN 'EN PRONOSTICO' THEN 4
        WHEN 'CERRADO GANADO' THEN 5
        WHEN 'CERRADO PERDIDO' THEN 6
        ELSE 7
      END";

    $stmtEtapas = $con->prepare($sqlEtapas);
    $stmtEtapas->bind_param("s", $sucursal);
    $stmtEtapas->execute();
    $resultEtapas = $stmtEtapas->get_result();

    $porcentajes = [];
    if ($resultEtapas->num_rows > 0) {
        while ($rowEtapa = $resultEtapas->fetch_assoc()) {
            $porcentajes[$rowEtapa['etapa']] = (float)$rowEtapa['porcentaje'];
        }
    } else {
        // Valores por defecto si no se encuentran datos en la base de datos
        $porcentajes = [
            "BASE DE DATOS" => 0,
            "ACTIVAR" => 0,
            "ESTRECHAR" => 0.25,
            "EN PRONOSTICO" => 0.7,
            "CERRADO GANADO" => 1,
            "CERRADO PERDIDO" => 0
        ];
    }
    
    echo json_encode(array(
        'proyeccion' => $proyeccion,
        'productos' => $productos,
        'porcentajes' => $porcentajes
    ));
} else {
    echo json_encode(array(
        'proyeccion' => array(),
        'productos' => 0
    ));
}
?> 