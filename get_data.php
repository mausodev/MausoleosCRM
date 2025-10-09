<?php
session_start();
require './controlador/conexion.php';

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

// Get session variables
$id_usuario = $_SESSION['id'];
$sucursal = $_SESSION['sucursal'];
$puesto = $_SESSION['puesto'];

$accion = $_GET['accion'];
$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
$result = $con->query($sqlCierre);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $mes = $row['mes']; 
} else {
    $mes = 'N/A';
}

if ($accion == 'consultar') {
    $where = "1";
    if ($mes != 'N/A') {
        $where .= " AND c.mes = '$mes'";
    }

    // Base query structure
    $query = "
        SELECT
            e_coord.iniciales AS coordinador,
            e_coord.apsi AS apsi_coordinador,
            e_asesor.iniciales AS asesor,
            e_asesor.apsi AS apsi_asesor,
            IFNULL(SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_embudo ELSE 0 END), 0) AS venta,
            IFNULL(SUM(CASE WHEN c.etapa IN ('ACTIVAR', 'ESTRECHAR', 'EN PRONOSTICO') THEN c.venta_embudo ELSE 0 END), 0) AS proyeccion,
            COALESCE(mv_asesor.meta, 1) AS meta,
            NOW() AS fecha,
            c.mes AS mes,
            e_asesor.sucursal AS plaza,
            COALESCE(mv_coord.meta, 0) AS meta_coordinador,
            e_asesor.id AS id_asesor,
            mv_asesor.nombre_mes AS mes_meta
        FROM cliente c
        INNER JOIN empleado e_asesor ON c.asesor = e_asesor.id
        LEFT JOIN empleado e_coord ON e_asesor.id_supervisor = e_coord.id
        LEFT JOIN meta_venta mv_asesor ON mv_asesor.id_asesor = e_asesor.id AND UPPER(mv_asesor.nombre_mes) = UPPER(c.mes)
        LEFT JOIN meta_venta mv_coord ON mv_coord.id_cordinador = e_coord.id AND UPPER(mv_coord.nombre_mes) = UPPER(c.mes)
    ";

    // Add role-specific conditions
    if ($puesto == 'ASESOR') {
        $where .= " AND c.asesor = $id_usuario";
    } elseif ($puesto == 'COORDINADOR') {
        $where .= " AND e_asesor.id_supervisor = $id_usuario";
    } elseif ($puesto == 'GERENTE' || $puesto == 'EJECUTIVO') {
        $where .= " AND e_asesor.sucursal = '$sucursal' AND e_asesor.puesto = 'ASESOR'";
    }
 
    // Add the WHERE clause and grouping
    $query .= " WHERE $where GROUP BY e_asesor.id ORDER BY e_coord.id_supervisor";
    //var_dump($query);
    $res = $con->query($query);
    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    
    // Debug information
    $debug_info = [
        'query' => $query,
        'mes_actual' => $mes,
        'primer_registro' => !empty($datos) ? $datos[0] : null
    ];
    
    echo json_encode([
        'datos' => $datos,
        'total' => count($datos),
        'debug' => $debug_info
    ]);
}
?>