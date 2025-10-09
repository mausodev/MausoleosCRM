<?php
session_start();

header('Content-Type: application/json');

// Verifica que existan las variables necesarias
if (!isset($_SESSION['id'], $_SESSION['sucursal'], $_SESSION['puesto'])) {
  echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
  exit;
}

$id_asesor = $_SESSION['id'];
$sucursal = $_SESSION['sucursal'];
$puesto = $_SESSION['puesto'];
/*var_dump($id_asesor, $sucursal, $puesto);
die();*/
// Aquí iría tu lógica para obtener datos de la base
// Ejemplo (modifícalo con tu propia lógica de consulta):
    require './controlador/conexion.php';

    $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $mes = $row['mes']; 
      error_log("Mes obtenido de calendario: " . $mes);
  } else {
    $mes = 'N/A';
    error_log("No se encontró mes en calendario");
  }

if ($puesto == 'ASESOR') {
    // Query para obtener la venta (suma de venta_embudo de clientes CERRADO GANADO)
    $sqlVenta = "SELECT COALESCE(SUM(venta_embudo), 0) as venta_total 
                 FROM cliente 
                 WHERE etapa = 'CERRADO GANADO' 
                 AND asesor = ? 
                 AND mes = ?";
    
    $stmtVenta = $con->prepare($sqlVenta);
    $stmtVenta->bind_param("ss", $id_asesor, $mes);
    $stmtVenta->execute();
    $resultVenta = $stmtVenta->get_result();
    $rowVenta = $resultVenta->fetch_assoc();
    $venta = $rowVenta ? (float)$rowVenta['venta_total'] : 0;

    // Query para obtener la meta de venta
    $sqlMeta = "SELECT COALESCE(meta, 0) as meta 
                FROM meta_venta 
                WHERE id_asesor = ? 
                AND nombre_mes = ?";
    
    $stmtMeta = $con->prepare($sqlMeta);
    $stmtMeta->bind_param("ss", $id_asesor, $mes);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();
    $rowMeta = $resultMeta->fetch_assoc();
    $meta = $rowMeta ? (float)$rowMeta['meta'] : 0;
    $venta_faltante = $meta - $venta;

    // Query para obtener el pronóstico (suma de venta_embudo de clientes EN PRONOSTICO)
    $sqlPronostico = "SELECT COALESCE(SUM(venta_embudo), 0) as pronostico 
                      FROM cliente 
                      WHERE etapa = 'EN PRONOSTICO' 
                      AND asesor = ?
                      AND mes = ?";
    
    error_log("Parámetros para consulta de pronóstico - asesor: " . $id_asesor . ", mes: " . $mes);
    
    $stmtPronostico = $con->prepare($sqlPronostico);
    $stmtPronostico->bind_param("ss", $id_asesor, $mes);
    $stmtPronostico->execute();
    $resultPronostico = $stmtPronostico->get_result();
    $rowPronostico = $resultPronostico->fetch_assoc();
    $pronostico = $rowPronostico ? (float)$rowPronostico['pronostico'] : 0;
    $venta_pronostico =  $pronostico;

    // Calculate proyeccion_asesor (sum of embudo for specific stages / meta * 100)
    $sqlProyeccion = "SELECT COALESCE(SUM(venta_embudo), 0) as total_embudo 
                      FROM cliente 
                      WHERE asesor = ? 
                      AND etapa IN ('CERRADO GANADO', 'ESTRECHAR', 'EN PRONOSTICO') 
                      AND mes = ?";
    $stmtProyeccion = $con->prepare($sqlProyeccion);
    $stmtProyeccion->bind_param("ss", $id_asesor, $mes);
    $stmtProyeccion->execute();
    $resultProyeccion = $stmtProyeccion->get_result();
    $totalEmbudo = $resultProyeccion->fetch_assoc()['total_embudo'] ?? 0;
    
    // Calculate proyeccion as percentage
    $proyeccion_asesor = 0;
    if ($meta > 0) {
        $proyeccion_asesor = ($totalEmbudo / $meta) * 100;
    }

    // Asegurar que todos los valores sean números válidos
    $venta = is_numeric($venta) ? $venta : 0;
    $venta_faltante = is_numeric($venta_faltante) ? $venta_faltante : 0;
    $venta_pronostico = is_numeric($venta_pronostico) ? $venta_pronostico : 0;
    $proyeccion_asesor = is_numeric($proyeccion_asesor) ? $proyeccion_asesor : 0;

    // Debug para verificar los valores
    error_log("Venta: " . $venta);
    error_log("Pronóstico: " . $pronostico);
    error_log("Venta Pronóstico Total: " . $venta_pronostico);
    error_log("Proyección Asesor: " . $proyeccion_asesor);
    error_log("Resultado completo de la consulta de pronóstico: " . print_r($rowPronostico, true));

    echo json_encode([
        'success' => true,
        'venta' => number_format($venta, 2, '.', ''),
        'venta_faltante' => number_format($venta_faltante, 2, '.', ''),
        'venta_pronostico' => number_format($venta_pronostico, 2, '.', ''),
        'proyeccion_asesor' => number_format($proyeccion_asesor, 2, '.', '')
    ]);
} else if ($puesto == 'COORDINADOR') {
    // First get all employees supervised by this coordinator
    $sqlEmpleados = "SELECT id FROM empleado WHERE id_supervisor = ?";
    $stmtEmpleados = $con->prepare($sqlEmpleados);
    $stmtEmpleados->bind_param("s", $id_asesor);
    $stmtEmpleados->execute();
    $resultEmpleados = $stmtEmpleados->get_result();
    
    $venta_total = 0;
    $venta_faltante_total = 0;
    $venta_pronostico_total = 0;
    
    while ($rowEmpleado = $resultEmpleados->fetch_assoc()) {
        $id_empleado = $rowEmpleado['id'];
        
        // Get sales data for each employee
        $sqlVenta = "SELECT COALESCE(SUM(venta_embudo), 0) as venta_total 
                     FROM cliente 
                     WHERE etapa = 'CERRADO GANADO' 
                     AND asesor = ? 
                     AND mes = ?";
        
        $stmtVenta = $con->prepare($sqlVenta);
        $stmtVenta->bind_param("ss", $id_empleado, $mes);
        $stmtVenta->execute();
        $resultVenta = $stmtVenta->get_result();
        $rowVenta = $resultVenta->fetch_assoc();
        $venta = $rowVenta ? (float)$rowVenta['venta_total'] : 0;
        
        // Get sales target for each employee
        $sqlMeta = "SELECT COALESCE(meta, 0) as meta 
                    FROM meta_venta 
                    WHERE id_cordinador = ? 
                    AND nombre_mes = ?";
        
        $stmtMeta = $con->prepare($sqlMeta);
        $stmtMeta->bind_param("ss", $id_empleado, $mes);
        $stmtMeta->execute();
        $resultMeta = $stmtMeta->get_result();
        $rowMeta = $resultMeta->fetch_assoc();
        $meta = $rowMeta ? (float)$rowMeta['meta'] : 0;
        $venta_faltante = $meta - $venta;
        
        // Get forecast for each employee
        $sqlPronostico = "SELECT COALESCE(SUM(venta_embudo), 0) as pronostico 
                          FROM cliente 
                          WHERE etapa = 'EN PRONOSTICO' 
                          AND asesor = ?
                          AND mes = ?";
        
        $stmtPronostico = $con->prepare($sqlPronostico);
        $stmtPronostico->bind_param("ss", $id_empleado, $mes);
        $stmtPronostico->execute();
        $resultPronostico = $stmtPronostico->get_result();
        $rowPronostico = $resultPronostico->fetch_assoc();
        $pronostico = $rowPronostico ? (float)$rowPronostico['pronostico'] : 0;
        
        // Accumulate totals
        $venta_total += $venta;
        $venta_faltante_total += $venta_faltante;
        $venta_pronostico_total += $pronostico;
    }
    
    echo json_encode([
        'success' => true,
        'venta' => number_format($venta_total, 2, '.', ''),
        'venta_faltante' => number_format($venta_faltante_total, 2, '.', ''),
        'venta_pronostico' => number_format($venta_pronostico_total, 2, '.', '')
    ]);
} else if ($puesto == 'GERENTE' || $puesto == 'EJECUTIVO') {
    // Get all advisors in the same branch
    $sqlAsesores = "SELECT id FROM empleado WHERE puesto = 'ASESOR' AND sucursal = ?";
    $stmtAsesores = $con->prepare($sqlAsesores);
    $stmtAsesores->bind_param("s", $sucursal);
    $stmtAsesores->execute();
    $resultAsesores = $stmtAsesores->get_result();
    
    $venta_total = 0;
    $venta_faltante_total = 0;
    $venta_pronostico_total = 0;
    
    // Get total sales from all advisors in the branch
    $sqlVenta = "SELECT COALESCE(SUM(venta_embudo), 0) as venta_total 
                 FROM cliente 
                 WHERE etapa = 'CERRADO GANADO' 
                 AND asesor IN (SELECT id FROM empleado WHERE puesto = 'ASESOR' AND sucursal = ?)
                 AND mes = ?";
    
    $stmtVenta = $con->prepare($sqlVenta);
    $stmtVenta->bind_param("ss", $sucursal, $mes);
    $stmtVenta->execute();
    $resultVenta = $stmtVenta->get_result();
    $rowVenta = $resultVenta->fetch_assoc();
    $venta_total = $rowVenta ? (float)$rowVenta['venta_total'] : 0;
    
    // Get total sales target for the branch
    $sqlMeta = "SELECT COALESCE(SUM(meta), 0) as meta_total 
                FROM meta_venta 
                WHERE plaza = ? 
                AND nombre_mes = ?
                AND id_cordinador != 0";
    
    $stmtMeta = $con->prepare($sqlMeta);
    $stmtMeta->bind_param("ss", $sucursal, $mes);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();
    $rowMeta = $resultMeta->fetch_assoc();
    $meta_total = $rowMeta ? (float)$rowMeta['meta_total'] : 0;
    $venta_faltante_total = $meta_total - $venta_total;
    
    // Get total forecast sales from all advisors in the branch
    $sqlPronostico = "SELECT COALESCE(SUM(venta_embudo), 0) as pronostico_total 
                      FROM cliente 
                      WHERE etapa = 'EN PRONOSTICO' 
                      AND asesor IN (SELECT id FROM empleado WHERE puesto = 'ASESOR' AND sucursal = ?)
                      AND mes = ?";
    
    $stmtPronostico = $con->prepare($sqlPronostico);
    $stmtPronostico->bind_param("ss", $sucursal, $mes);
    $stmtPronostico->execute();
    $resultPronostico = $stmtPronostico->get_result();
    $rowPronostico = $resultPronostico->fetch_assoc();
    $venta_pronostico_total = $rowPronostico ? (float)$rowPronostico['pronostico_total'] : 0;
    
    echo json_encode([
        'success' => true,
        'venta' => number_format($venta_total, 2, '.', ''),
        'venta_faltante' => number_format($venta_faltante_total, 2, '.', ''),
        'venta_pronostico' => number_format($venta_pronostico_total, 2, '.', '')
    ]);
} else {
    // Mantener la lógica original para otros puestos
    $sql = "SELECT 
              venta_total AS venta, 
              venta_objetivo - venta_total AS venta_faltante,
              (venta_total + proyeccion) AS venta_pronostico
            FROM ventas
            WHERE id_asesor = ? AND sucursal = ? AND puesto = ?
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $id_asesor, $sucursal, $puesto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'venta' => $row['venta'],
            'venta_faltante' => $row['venta_faltante'],
            'venta_pronostico' => $row['venta_pronostico']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos']);
    }
}
?>