<?php
require './controlador/conexion.php';
function guardarProyeccionAutomatica($con, $idAsesor, $idCoordinador) {
    $fecha  = date("Y-m-d");
    $semana = date("W");
    $anio   = date("Y");
    $mes    = date("n");

    /** ===============================
     *  SECCIÓN ASESOR
     * =============================== */
    // Monto proyectado asesor
    $sqlProy = "SELECT SUM(venta_embudo) AS monto_proyectado
                FROM cliente
                WHERE asesor = ?
                  AND etapa NOT IN ('CERRADO PERDIDO','CERRADO GANADO')
                  AND (DATE(fecha_creado) = CURDATE() OR DATE(fecha_modificado) = CURDATE())";
    $stmt = $con->prepare($sqlProy);
    $stmt->bind_param("i", $idAsesor);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $montoProyectado = $res['monto_proyectado'] ?? 0;

    // Venta asesor
    $sqlVenta = "SELECT SUM(venta_embudo) AS venta
                 FROM cliente
                 WHERE asesor = ?
                   AND etapa = 'CERRADO GANADO'
                   AND (DATE(fecha_creado) = CURDATE() OR DATE(fecha_modificado) = CURDATE())";
    $stmt = $con->prepare($sqlVenta);
    $stmt->bind_param("i", $idAsesor);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $venta = $res['venta'] ?? 0;

    // Meta asesor
    $sqlMeta = "SELECT meta FROM meta_venta WHERE id_asesor = ? AND mes = ?";
    $stmt = $con->prepare($sqlMeta);
    $stmt->bind_param("ii", $idAsesor, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $metaAsesor = $res['meta'] ?? 1;

    // Porcentaje asesor
    $porcentaje = (($montoProyectado + $venta) / $metaAsesor) * 100;

    /** ===============================
     *  SECCIÓN COORDINADOR
     * =============================== */
    // Monto proyectado coordinador
    $sqlProyCoord = "SELECT SUM(c.venta_embudo) AS monto_proyectado_coord
                     FROM cliente c
                     INNER JOIN empleado e ON c.asesor = e.id
                     WHERE e.id_supervisor = ?
                       AND c.etapa IN ('ESTRECHAR','EN PRONOSTICO')
                       AND (DATE(c.fecha_creado) = CURDATE() OR DATE(c.fecha_modificado) = CURDATE())";
    $stmt = $con->prepare($sqlProyCoord);
    $stmt->bind_param("i", $idCoordinador);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $montoProyectadoCoord = $res['monto_proyectado_coord'] ?? 0;

    // Venta coordinador
    $sqlVentaCoord = "SELECT SUM(c.venta_embudo) AS venta_coord
                      FROM cliente c
                      INNER JOIN empleado e ON c.asesor = e.id
                      WHERE e.id_supervisor = ?
                        AND c.etapa = 'CERRADO GANADO'
                        AND (DATE(c.fecha_creado) = CURDATE() OR DATE(c.fecha_modificado) = CURDATE())";
    $stmt = $con->prepare($sqlVentaCoord);
    $stmt->bind_param("i", $idCoordinador);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $ventaCoord = $res['venta_coord'] ?? 0;

    // Meta coordinador
    $sqlMetaCoord = "SELECT meta FROM meta_venta WHERE id_coordinador = ? AND mes = ?";
    $stmt = $con->prepare($sqlMetaCoord);
    $stmt->bind_param("ii", $idCoordinador, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $metaCoordinador = $res['meta'] ?? 1;

    // Porcentaje coordinador
    $porcentajeCoord = (($montoProyectadoCoord + $ventaCoord) / $metaCoordinador) * 100;

    /** ===============================
     *  INSERTAR REGISTRO DIARIO
     * =============================== */
    $sqlInsert = "INSERT INTO proyecciones_diarias 
                  (fecha, id_asesor, monto_proyectado, venta, porcentaje, 
                   id_coordinador, monto_proyectado_coord, venta_coord, porcentaje_coord) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sqlInsert);
    $stmt->bind_param("siddiiddi", 
        $fecha, $idAsesor, $montoProyectado, $venta, $porcentaje,
        $idCoordinador, $montoProyectadoCoord, $ventaCoord, $porcentajeCoord
    );
    $stmt->execute();

    /** ===============================
     *  SI ES SÁBADO → GUARDAR CIERRE SEMANAL
     * =============================== */
    if (date("w") == 6) {
        // Asesor
        $sqlSemanaAsesor = "SELECT SUM(monto_proyectado) AS cierre_proyeccion, SUM(venta) AS cierre_venta
                            FROM proyecciones_diarias
                            WHERE WEEK(fecha, 1) = ? AND YEAR(fecha) = ? AND id_asesor = ?";
        $stmt = $con->prepare($sqlSemanaAsesor);
        $stmt->bind_param("iii", $semana, $anio, $idAsesor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $cierreProyAsesor  = $row['cierre_proyeccion'] ?? 0;
        $cierreVentaAsesor = $row['cierre_venta'] ?? 0;

        $sqlInsertCierre = "INSERT INTO proyecciones_semanales 
                            (semana, anio, id_asesor, id_coordinador, cierre_proyeccion, cierre_venta)
                            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sqlInsertCierre);
        $stmt->bind_param("iiii dd", $semana, $anio, $idAsesor, $idCoordinador, $cierreProyAsesor, $cierreVentaAsesor);
        $stmt->execute();

        // Coordinador
        $sqlSemanaCoord = "SELECT SUM(monto_proyectado_coord) AS cierre_proyeccion, SUM(venta_coord) AS cierre_venta
                           FROM proyecciones_diarias
                           WHERE WEEK(fecha, 1) = ? AND YEAR(fecha) = ? AND id_coordinador = ?";
        $stmt = $con->prepare($sqlSemanaCoord);
        $stmt->bind_param("iii", $semana, $anio, $idCoordinador);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $cierreProyCoord  = $row['cierre_proyeccion'] ?? 0;
        $cierreVentaCoord = $row['cierre_venta'] ?? 0;

        $sqlInsertCierre = "INSERT INTO proyecciones_semanales 
                            (semana, anio, id_asesor, id_coordinador, cierre_proyeccion, cierre_venta)
                            VALUES (?, ?, 0, ?, ?, ?)";
        $stmt = $con->prepare($sqlInsertCierre);
        $stmt->bind_param("iiidd", $semana, $anio, $idCoordinador, $cierreProyCoord, $cierreVentaCoord);
        $stmt->execute();
    }
}
?>
