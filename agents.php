<?php
require './controlador/conexion.php';
require './controlador/access_control.php';

// Verificar acceso y obtener datos de sesión
$accessData = verificarAcceso();
$acceso = $accessData['acceso'];
$id_asesor = $accessData['id_asesor'];
$inicial = $accessData['inicial'];
$supervisor = $accessData['supervisor'];
$correo = $accessData['correo'];
$sucursal = $accessData['sucursal'];
$departamento = $accessData['departamento'];
$puesto = $accessData['puesto'];
$rol_venta = $accessData['rol_venta'];
$id_Rol = $accessData['id_Rol'];

$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
$result = $con->query($sqlCierre);

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $mes = $row['mes']; 
} else {
  $mes = 'N/A';
}

// Obtener el mes seleccionado desde GET o POST, o usar el mes actual por defecto
$mesSeleccionado = isset($_REQUEST['mes_filtro']) ? $_REQUEST['mes_filtro'] : $mes;

// Obtener filtros adicionales
$filtro_coordinador = isset($_REQUEST['filtro_coordinador']) ? $_REQUEST['filtro_coordinador'] : '';
$filtro_asesor = isset($_REQUEST['filtro_asesor']) ? $_REQUEST['filtro_asesor'] : '';
$filtro_fecha_creado_desde = isset($_REQUEST['fecha_creado_desde']) ? $_REQUEST['fecha_creado_desde'] : '';
$filtro_fecha_creado_hasta = isset($_REQUEST['fecha_creado_hasta']) ? $_REQUEST['fecha_creado_hasta'] : '';
$filtro_fecha_compromiso_desde = isset($_REQUEST['fecha_compromiso_desde']) ? $_REQUEST['fecha_compromiso_desde'] : '';
$filtro_fecha_compromiso_hasta = isset($_REQUEST['fecha_compromiso_hasta']) ? $_REQUEST['fecha_compromiso_hasta'] : '';
$filtro_fecha_cierre_desde = isset($_REQUEST['fecha_cierre_desde']) ? $_REQUEST['fecha_cierre_desde'] : '';
$filtro_fecha_cierre_hasta = isset($_REQUEST['fecha_cierre_hasta']) ? $_REQUEST['fecha_cierre_hasta'] : '';
$filtro_tipo = isset($_REQUEST['filtro_tipo']) ? $_REQUEST['filtro_tipo'] : '';
$filtro_articulo = isset($_REQUEST['filtro_articulo']) ? $_REQUEST['filtro_articulo'] : '';
$filtro_opcion = isset($_REQUEST['filtro_opcion']) ? $_REQUEST['filtro_opcion'] : '';

// Construir la consulta base según el puesto
$whereConditions = ["c.mes = ?"];
$params = [$mesSeleccionado];
$paramTypes = "s";

if ($puesto == 'COORDINADOR') {
    $whereConditions[] = "e.supervisor = ?";
    $params[] = $inicial;
    $paramTypes .= "s";
    
    // Si hay filtro de asesor específico
    if (!empty($filtro_asesor)) {
        $whereConditions[] = "e.iniciales = ?";
        $params[] = $filtro_asesor;
        $paramTypes .= "s";
    }
} elseif ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE') {
    $whereConditions[] = "e.sucursal = ?";
    $params[] = $sucursal;
    $paramTypes .= "s";
    
    // Si hay filtro de coordinador
    if (!empty($filtro_coordinador)) {
        $whereConditions[] = "e.supervisor = ?";
        $params[] = $filtro_coordinador;
        $paramTypes .= "s";
    }
    
    // Si hay filtro de asesor específico
    if (!empty($filtro_asesor)) {
        $whereConditions[] = "e.iniciales = ?";
        $params[] = $filtro_asesor;
        $paramTypes .= "s";
    }
} elseif ($puesto == 'ASESOR') {
    $whereConditions[] = "c.asesor = ?";
    $params[] = $id_asesor;
    $paramTypes .= "i";
}

// Agregar filtros dinámicos solo si se ha seleccionado un tipo de filtro
if (!empty($filtro_opcion)) {
    switch($filtro_opcion) {
        case 'fecha_creado':
            if (!empty($filtro_fecha_creado_desde)) {
                $whereConditions[] = "c.fecha_creado >= ?";
                $params[] = $filtro_fecha_creado_desde;
                $paramTypes .= "s";
            }
            if (!empty($filtro_fecha_creado_hasta)) {
                $whereConditions[] = "c.fecha_creado <= ?";
                $params[] = $filtro_fecha_creado_hasta;
                $paramTypes .= "s";
            }
            break;
            
        case 'fecha_compromiso':
            if (!empty($filtro_fecha_compromiso_desde)) {
                $whereConditions[] = "c.fecha_compromiso >= ?";
                $params[] = $filtro_fecha_compromiso_desde;
                $paramTypes .= "s";
            }
            if (!empty($filtro_fecha_compromiso_hasta)) {
                $whereConditions[] = "c.fecha_compromiso <= ?";
                $params[] = $filtro_fecha_compromiso_hasta;
                $paramTypes .= "s";
            }
            break;
            
        case 'fecha_cierre':
            if (!empty($filtro_fecha_cierre_desde)) {
                $whereConditions[] = "c.fecha_cierre >= ?";
                $params[] = $filtro_fecha_cierre_desde;
                $paramTypes .= "s";
            }
            if (!empty($filtro_fecha_cierre_hasta)) {
                $whereConditions[] = "c.fecha_cierre <= ?";
                $params[] = $filtro_fecha_cierre_hasta;
                $paramTypes .= "s";
            }
            break;
            
        case 'tipo':
            if (!empty($filtro_tipo)) {
                $whereConditions[] = "c.tipo = ?";
                $params[] = $filtro_tipo;
                $paramTypes .= "s";
            }
            break;
            
        case 'articulo':
            if (!empty($filtro_articulo)) {
                $whereConditions[] = "c.articulo = ?";
                $params[] = $filtro_articulo;
                $paramTypes .= "s";
            }
            break;
    }
}

$whereClause = implode(" AND ", $whereConditions);

$query = "SELECT c.id, CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_completo, 
                 c.porcentaje, c.etapa, c.fecha_modificado, c.articulo, c.venta_real, c.estado, 
                 e.iniciales, c.venta_embudo, c.fecha_compromiso, c.mes, c.origen_cliente, 
                 c.folio_contrato, c.fecha_cierre, c.fecha_creado, e.supervisor, c.telefono, c.tipo
          FROM cliente c
          INNER JOIN empleado e ON e.id = c.asesor
          WHERE $whereClause
          ORDER BY c.fecha_compromiso DESC, c.fecha_modificado DESC";

$stmt = $con->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener coordinadores para el filtro (solo para EJECUTIVO y GERENTE)
$coordinadores = [];
if ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE') {
    $sqlCoordinadores = "SELECT DISTINCT supervisor FROM empleado WHERE sucursal = ? AND supervisor IS NOT NULL ORDER BY supervisor";
    $stmtCoordinadores = $con->prepare($sqlCoordinadores);
    $stmtCoordinadores->bind_param("s", $sucursal);
    $stmtCoordinadores->execute();
    $resultCoordinadores = $stmtCoordinadores->get_result();
    while ($row = $resultCoordinadores->fetch_assoc()) {
        $coordinadores[] = $row['supervisor'];
    }
}

// Obtener asesores para el filtro
$asesores = [];
if ($puesto == 'COORDINADOR') {
    $sqlAsesores = "SELECT iniciales FROM empleado WHERE supervisor = ? AND activo = 1 ORDER BY iniciales";
    $stmtAsesores = $con->prepare($sqlAsesores);
    $stmtAsesores->bind_param("s", $inicial);
    $stmtAsesores->execute();
    $resultAsesores = $stmtAsesores->get_result();
    while ($row = $resultAsesores->fetch_assoc()) {
        $asesores[] = $row['iniciales'];
    }
} elseif ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE') {
    $sqlAsesores = "SELECT iniciales FROM empleado WHERE sucursal = ? AND activo = 1";
    if (!empty($filtro_coordinador)) {
        $sqlAsesores .= " AND supervisor = ?";
    }
    $sqlAsesores .= " ORDER BY iniciales";
    
    $stmtAsesores = $con->prepare($sqlAsesores);
    if (!empty($filtro_coordinador)) {
        $stmtAsesores->bind_param("ss", $sucursal, $filtro_coordinador);
    } else {
        $stmtAsesores->bind_param("s", $sucursal);
    }
    $stmtAsesores->execute();
    $resultAsesores = $stmtAsesores->get_result();
    while ($row = $resultAsesores->fetch_assoc()) {
        $asesores[] = $row['iniciales'];
    }
}

// Obtener tipos de servicio únicos para el filtro (solo de la plaza del usuario)
$tiposServicio = [];
$sqlTipos = "SELECT DISTINCT c.tipo 
             FROM cliente c 
             INNER JOIN empleado e ON c.asesor = e.id 
             WHERE c.tipo IS NOT NULL AND c.tipo != '' 
             AND e.sucursal = ? 
             ORDER BY c.tipo";
$stmtTipos = $con->prepare($sqlTipos);
$stmtTipos->bind_param("s", $sucursal);
$stmtTipos->execute();
$resultTipos = $stmtTipos->get_result();
while ($row = $resultTipos->fetch_assoc()) {
    $tiposServicio[] = $row['tipo'];
}

// Obtener artículos únicos para el filtro (solo de la plaza del usuario)
$articulos = [];
$sqlArticulos = "SELECT DISTINCT c.articulo 
                 FROM cliente c 
                 INNER JOIN empleado e ON c.asesor = e.id 
                 WHERE c.articulo IS NOT NULL AND c.articulo != '' 
                 AND e.sucursal = ? 
                 ORDER BY c.articulo";
$stmtArticulos = $con->prepare($sqlArticulos);
$stmtArticulos->bind_param("s", $sucursal);
$stmtArticulos->execute();
$resultArticulos = $stmtArticulos->get_result();
while ($row = $resultArticulos->fetch_assoc()) {
    $articulos[] = $row['articulo'];
}

// Obtener proyecciones de la tabla embudo_plaza para la sucursal del usuario
$proyeccionesEtapas = [];
if ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE') {
    $sqlProyecciones = "SELECT etapa, porcentaje FROM embudo_plaza WHERE plaza = ?";
    $stmtProyecciones = $con->prepare($sqlProyecciones);
    $stmtProyecciones->bind_param("s", $sucursal);
    $stmtProyecciones->execute();
    $resultProyecciones = $stmtProyecciones->get_result();
    while ($row = $resultProyecciones->fetch_assoc()) {
        $proyeccionesEtapas[$row['etapa']] = $row['porcentaje'];
    }
} elseif ($puesto == 'COORDINADOR') {
    // Para coordinadores, usar las proyecciones de la sucursal del usuario logueado
    $sqlProyecciones = "SELECT etapa, porcentaje FROM embudo_plaza WHERE plaza = ?";
    $stmtProyecciones = $con->prepare($sqlProyecciones);
    $stmtProyecciones->bind_param("s", $sucursal);
    $stmtProyecciones->execute();
    $resultProyecciones = $stmtProyecciones->get_result();
    while ($row = $resultProyecciones->fetch_assoc()) {
        $proyeccionesEtapas[$row['etapa']] = $row['porcentaje'];
    }
}

// Obtener precio promedio de la plaza del usuario
$sqlPrecioPromedio = "SELECT precio_promedio FROM precio_promedio WHERE plaza = ?";
$stmtPrecioPromedio = $con->prepare($sqlPrecioPromedio);
$stmtPrecioPromedio->bind_param("s", $sucursal);
$stmtPrecioPromedio->execute();
$resultPrecioPromedio = $stmtPrecioPromedio->get_result();
$precioPromedio = $resultPrecioPromedio->fetch_assoc()['precio_promedio'] ?? 0;

// Obtener etapas con valor de proyección (porcentaje > 0)
$etapasConProyeccion = [];
if ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE') {
    $sqlEtapasConProyeccion = "SELECT etapa FROM embudo_plaza WHERE plaza = ? AND porcentaje > 0";
    $stmtEtapasConProyeccion = $con->prepare($sqlEtapasConProyeccion);
    $stmtEtapasConProyeccion->bind_param("s", $sucursal);
    $stmtEtapasConProyeccion->execute();
    $resultEtapasConProyeccion = $stmtEtapasConProyeccion->get_result();
    while ($row = $resultEtapasConProyeccion->fetch_assoc()) {
        $etapasConProyeccion[] = $row['etapa'];
    }
} elseif ($puesto == 'COORDINADOR') {
    // Para coordinadores, obtener etapas con proyección de su área
    $sqlEtapasConProyeccion = "SELECT DISTINCT ep.etapa FROM embudo_plaza ep 
                              INNER JOIN empleado e ON ep.plaza = e.sucursal 
                              WHERE e.supervisor = ? AND  ep.porcentaje BETWEEN 0.1 AND 0.9";
    $stmtEtapasConProyeccion = $con->prepare($sqlEtapasConProyeccion);
    $stmtEtapasConProyeccion->bind_param("s", $inicial);
    $stmtEtapasConProyeccion->execute();
    $resultEtapasConProyeccion = $stmtEtapasConProyeccion->get_result();
    while ($row = $resultEtapasConProyeccion->fetch_assoc()) {
        $etapasConProyeccion[] = $row['etapa'];
    }
} elseif ($puesto == 'ASESOR') {
    // Para asesores, usar las etapas de su sucursal
    $sqlEtapasConProyeccion = "SELECT etapa FROM embudo_plaza WHERE plaza = ? AND porcentaje BETWEEN 0.1 AND 0.9";
    $stmtEtapasConProyeccion = $con->prepare($sqlEtapasConProyeccion);
    $stmtEtapasConProyeccion->bind_param("s", $sucursal);
    $stmtEtapasConProyeccion->execute();
    $resultEtapasConProyeccion = $stmtEtapasConProyeccion->get_result();
    while ($row = $resultEtapasConProyeccion->fetch_assoc()) {
        $etapasConProyeccion[] = $row['etapa'];
    }
}

// Calcular totales
$totalVentas = 0;
$venta_faltante = 0;
$prospectos_mes = 0; // Cambio de venta_pronostico a prospectos_mes
$proyeccion_asesor = 0;
$totalEmbudo = 0;

// Calcular Total Ventas (sumar venta_embudo) por rol, etapa y mes seleccionado
if ($puesto == 'COORDINADOR') {
    $sqlTotalVentas = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       INNER JOIN empleado e ON c.asesor = e.id
                       WHERE e.id_supervisor = ? AND c.etapa = 'CERRADO GANADO' AND c.mes = ?";
    $stmtTotalVentas = $con->prepare($sqlTotalVentas);
    $stmtTotalVentas->bind_param("is", $id_asesor, $mesSeleccionado);
    $stmtTotalVentas->execute();
    $resultTotalVentas = $stmtTotalVentas->get_result();
    $totalVentas = $resultTotalVentas->fetch_assoc()['total'] ?? 0;
} elseif ($puesto == 'ASESOR') {
    $sqlTotalVentas = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       WHERE c.etapa = 'CERRADO GANADO' AND c.mes = ? AND c.asesor = ?";
    $stmtTotalVentas = $con->prepare($sqlTotalVentas);
    $stmtTotalVentas->bind_param("si", $mesSeleccionado, $id_asesor);
    $stmtTotalVentas->execute();
    $resultTotalVentas = $stmtTotalVentas->get_result();
    $totalVentas = $resultTotalVentas->fetch_assoc()['total'] ?? 0;
} else {
    $sqlTotalVentas = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       WHERE c.etapa = 'CERRADO GANADO' AND c.mes = ? AND c.plaza = ?";
    $stmtTotalVentas = $con->prepare($sqlTotalVentas);
    $stmtTotalVentas->bind_param("ss", $mesSeleccionado, $sucursal);
    $stmtTotalVentas->execute();
    $resultTotalVentas = $stmtTotalVentas->get_result();
    $totalVentas = $resultTotalVentas->fetch_assoc()['total'] ?? 0;
}

// Calcular Total Embudo (sumar venta_embudo en etapas ESTRECHAR y EN PRONOSTICO) por rol y mes
if ($puesto == 'COORDINADOR') {
    $sqlTotalEmbudo = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       INNER JOIN empleado e ON c.asesor = e.id
                       WHERE e.id_supervisor = ? AND c.etapa IN ('ESTRECHAR','EN PRONOSTICO') AND c.mes = ?";
    $stmtTotalEmbudo = $con->prepare($sqlTotalEmbudo);
    $stmtTotalEmbudo->bind_param("is", $id_asesor, $mesSeleccionado);
    $stmtTotalEmbudo->execute();
    $resultTotalEmbudo = $stmtTotalEmbudo->get_result();
    $totalEmbudo = $resultTotalEmbudo->fetch_assoc()['total'] ?? 0;
} elseif ($puesto == 'ASESOR') {
    $sqlTotalEmbudo = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       WHERE c.etapa IN ('ESTRECHAR','EN PRONOSTICO') AND c.mes = ? AND c.asesor = ?";
    $stmtTotalEmbudo = $con->prepare($sqlTotalEmbudo);
    $stmtTotalEmbudo->bind_param("si", $mesSeleccionado, $id_asesor);
    $stmtTotalEmbudo->execute();
    $resultTotalEmbudo = $stmtTotalEmbudo->get_result();
    $totalEmbudo = $resultTotalEmbudo->fetch_assoc()['total'] ?? 0;
} else {
    $sqlTotalEmbudo = "SELECT COALESCE(SUM(c.venta_embudo), 0) AS total
                       FROM cliente c
                       WHERE c.etapa IN ('ESTRECHAR','EN PRONOSTICO') AND c.mes = ? AND c.plaza = ?";
    $stmtTotalEmbudo = $con->prepare($sqlTotalEmbudo);
    $stmtTotalEmbudo->bind_param("ss", $mesSeleccionado, $sucursal);
    $stmtTotalEmbudo->execute();
    $resultTotalEmbudo = $stmtTotalEmbudo->get_result();
    $totalEmbudo = $resultTotalEmbudo->fetch_assoc()['total'] ?? 0;
}

foreach ($clientes as $cliente) {
    // Sumar clientes en etapas que tengan valor de proyección
    if (in_array($cliente['etapa'], $etapasConProyeccion)) {
        $prospectos_mes += 1; // Contar clientes, no ventas
    }
}

// Calcular meta y venta_faltante por rol
if ($puesto == 'COORDINADOR') {
    $sqlMeta = "SELECT meta FROM meta_venta WHERE id_cordinador = ? AND nombre_mes = ?";
    $stmtMeta = $con->prepare($sqlMeta);
    $stmtMeta->bind_param("is", $id_asesor, $mesSeleccionado);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();
    $meta = $resultMeta->fetch_assoc()['meta'] ?? 0;
} elseif ($puesto == 'ASESOR') {
    $sqlMeta = "SELECT meta FROM meta_venta WHERE id_asesor = ? AND nombre_mes = ?";
    $stmtMeta = $con->prepare($sqlMeta);
    $stmtMeta->bind_param("is", $id_asesor, $mesSeleccionado);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();
    $meta = $resultMeta->fetch_assoc()['meta'] ?? 0;
} else {
    $sqlMeta = "SELECT meta FROM meta_sucursal WHERE plaza = ? AND mes = ?";
    $stmtMeta = $con->prepare($sqlMeta);
    $stmtMeta->bind_param("ss", $sucursal, $mesSeleccionado);
    $stmtMeta->execute();
    $resultMeta = $stmtMeta->get_result();
    $meta = $resultMeta->fetch_assoc()['meta'] ?? 0;
}

// Meta faltante = totalVentas - meta
$venta_faltante = $totalVentas - $meta;
/*var_dump($mesSeleccionado);
die();*/
// Calcular Prospección Faltante: (total_embudo + total_venta) / precio_promedio * 10 - (meta_asesor / precio_promedio) * 10
$prospeccion_faltante = 0;
if ($precioPromedio > 0) {
    $prospeccion_faltante = (($totalEmbudo + $totalVentas) / $precioPromedio * 10) - (($meta / $precioPromedio) * 10);
}
//var_dump($precioPromedio);
//die();
// Calcular proyeccion_asesor para ASESOR role only
if ($puesto == 'ASESOR') {
    $sqlProyeccion = "SELECT COALESCE(SUM(venta_embudo), 0) as total_embudo 
                      FROM cliente 
                      WHERE asesor = ? 
                      AND etapa IN ('CERRADO GANADO', 'ESTRECHAR', 'EN PRONOSTICO') 
                      AND mes = ?";
    $stmtProyeccion = $con->prepare($sqlProyeccion);
    $stmtProyeccion->bind_param("is", $id_asesor, $mesSeleccionado);
    $stmtProyeccion->execute();
    $resultProyeccion = $stmtProyeccion->get_result();
    $totalEmbudo = $resultProyeccion->fetch_assoc()['total_embudo'] ?? 0;
    
    if ($meta > 0) {
        $proyeccion_asesor = ($totalEmbudo / $meta) * 100;
    } else {
        $proyeccion_asesor = 0;
    }
}

// Agrupar clientes por etapa y fecha de seguimiento
$clientesAgrupados = [];
foreach ($clientes as $cliente) {
    $etapa = $cliente['etapa'] ?? 'Sin Etapa';
    $fechaSeguimiento = $cliente['fecha_compromiso'] ?? 'Sin Fecha';
    
    if (!isset($clientesAgrupados[$etapa])) {
        $clientesAgrupados[$etapa] = [];
    }
    if (!isset($clientesAgrupados[$etapa][$fechaSeguimiento])) {
        $clientesAgrupados[$etapa][$fechaSeguimiento] = [];
    }
    
    $clientesAgrupados[$etapa][$fechaSeguimiento][] = $cliente;
}

// Ordenar las etapas y fechas
foreach ($clientesAgrupados as $etapa => &$fechas) {
    krsort($fechas); // Ordenar fechas de más reciente a más antigua
}

// Definir el orden específico de las etapas
$ordenEtapas = [
    'BASE DE DATOS',
    'ACTIVAR', 
    'ESTRECHAR',
    'EN PRONOSTICO',
    'CERRADO GANADO',
    'CERRADO PERDIDO'
];

// Ordenar las etapas según el orden específico
$clientesAgrupadosOrdenados = [];
foreach ($ordenEtapas as $etapa) {
    if (isset($clientesAgrupados[$etapa])) {
        $clientesAgrupadosOrdenados[$etapa] = $clientesAgrupados[$etapa];
    }
}

// Agregar cualquier etapa que no esté en la lista de orden específico al final
foreach ($clientesAgrupados as $etapa => $fechas) {
    if (!in_array($etapa, $ordenEtapas)) {
        $clientesAgrupadosOrdenados[$etapa] = $fechas;
    }
}

$clientesAgrupados = $clientesAgrupadosOrdenados;

// Calcular totales por etapa
$totalesPorEtapa = [];
foreach ($clientesAgrupados as $etapa => $fechas) {
    $totalVentasEtapa = 0;
    $totalEmbudoEtapa = 0;
    $totalClientesEtapa = 0;
    
    foreach ($fechas as $fecha => $clientesEnFecha) {
        foreach ($clientesEnFecha as $cliente) {
            $totalVentasEtapa += $cliente['venta_real'] ?? 0;
            $totalEmbudoEtapa += $cliente['venta_embudo'] ?? 0;
            $totalClientesEtapa++;
        }
    }
    
    $totalesPorEtapa[$etapa] = [
        'ventas' => $totalVentasEtapa,
        'embudo' => $totalEmbudoEtapa,
        'clientes' => $totalClientesEtapa
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portal Mausoleos - Seguimiento</title>

    <!-- Meta -->
    <meta name="description" content="Canvas de seguimiento de clientes por etapa y fecha" />
    <meta name="author" content="Bootstrap Gallery" />
    <link rel="shortcut icon" href="assets/images/GrupoMausoleos.png" />

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/fonts/icomoon/style.css" />
    <link rel="stylesheet" href="assets/css/main.min.css" />
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />
    
    <style>
        .canvas-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px 0;
        }
        
        .stages-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .stages-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 0;
            min-height: 600px;
        }
        
        .stage-column {
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }
        
        .stage-column:last-child {
            border-right: none;
        }
        
        .stage-header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stage-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stage-stats {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .stage-count {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
            align-self: center;
        }
        
        .stage-totals {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.8rem;
        }
        
        .total-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
        }
        
        .total-label {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }
        
        .total-value {
            color: white;
            font-weight: bold;
        }
        
        .total-item.projection {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 4px;
            margin-top: 2px;
        }
        
        .stage-content {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            max-height: 500px;
        }
        
        .date-section {
            margin-bottom: 15px;
        }
        
        .date-header {
            background: #e9ecef;
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .client-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .client-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .client-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .client-name {
            font-weight: bold;
            font-size: 1rem;
            color: #212529;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        
        .client-essential {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.85rem;
        }
        
        .essential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .essential-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .essential-value {
            color: #212529;
            font-weight: bold;
        }
        
        /* Modal styles */
        .client-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-row {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .detail-value {
            color: #212529;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .filters-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filters-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .venta { color: #28a745; }
        .embudo { color: #007bff; }
        .pronostico { color: #ffc107; }
        .faltante { color: #dc3545; }
        .prospeccion { color: #6f42c1; }
        
        @media (max-width: 768px) {
            .stages-grid {
                grid-template-columns: 1fr;
            }
            
            .stage-column {
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            
            .stage-column:last-child {
                border-bottom: none;
            }
            
            .stage-header {
                padding: 12px;
            }
            
            .stage-title {
                font-size: 1.1rem;
                margin-bottom: 8px;
            }
            
            .stage-totals {
                font-size: 0.75rem;
            }
            
            .client-cards {
                flex-direction: column;
            }
            
            .filters-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .client-details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .stats-cards {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-value {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <?php if (!$acceso): ?>
    <?php echo generarOverlayAccesoDenegado(); ?>
    <?php endif; ?>
    
    <!-- Page wrapper start -->
    <div class="page-wrapper" <?php echo !$acceso ? 'style="pointer-events: none; opacity: 0.3;"' : ''; ?>>
        <!-- App container starts -->
        <div class="app-container">
            <!-- App header starts -->
            <div class="app-header d-flex align-items-center">
                <div class="container">
                    <div class="row gx-3">
                        <div class="col-md-3 col-2">
                            <div class="app-brand">
                                <a href="#" class="d-lg-block d-none">
                                    <img src="assets/images/GrupoMausoleos.png" class="logo" alt="Bootstrap Gallery" />
                                </a>
                                <a href="#" class="d-lg-none d-md-block">
                                    <img src="assets/images/GrupoMausoleos.png" class="logo" alt="Bootstrap Gallery" />
                                </a>
                            </div>
                        </div>
                        <div class="col-md-9 col-10">
                            <div class="header-actions col">
                                <div class="search-container d-none d-lg-block">
                                    <input type="text" id="search" class="form-control" placeholder="Buscar..." />
                                    <i class="icon-search"></i>
                                </div>
                                <div class="dropdown ms-3">
                                    <a id="userSettings" class="dropdown-toggle d-flex py-2 align-items-center text-decoration-none"
                                       href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="assets/images/GrupoMausoleos.png" class="rounded-2 img-3x" alt="Bootstrap Gallery" />
                                        <div class="ms-2 text-truncate d-lg-block d-none text-white">
                                            <span class="d-flex opacity-50 small"><?php echo htmlspecialchars($puesto); ?></span>
                                            <span><?php echo htmlspecialchars($correo); ?></span>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="header-action-links">
                                            <a class="dropdown-item" href="#"><i class="icon-user border border-primary text-primary"></i>Perfil</a>
                                            <a class="dropdown-item" href="#"><i class="icon-settings border border-danger text-danger"></i>Configurar</a>
                                            <a class="dropdown-item" href="#"><i class="icon-box border border-info text-info"></i>Ajustes</a>
                                        </div>
                                        <div class="mx-3 mt-2 d-grid">
                                            <a href="login.php" class="btn btn-outline-danger">Salir</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- App header ends -->

            <!-- App navbar starts -->
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if (tienePermiso($id_Rol, 'clients.php')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="clients.php"><i class="icon-supervised_user_circle"></i> Cliente</a>
                        </li>
                        <?php endif; ?>
                        <?php if (tienePermiso($id_Rol, 'agents.php')): ?>
                        <li class="nav-item active-link">
                            <a class="nav-link" href="agents.php"><i class="icon-support_agent"></i>Seguimiento</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="aviso.php">
                                <i class="icon-notifications"></i>Avisos
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (tienePermiso($id_Rol, 'leds.php') && $puesto !== 'ASESOR'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="leds.php"><i class="fs-3 icon-contacts"></i>Leds Digitales</a>
                        </li>
                        <?php endif; ?>
                        <?php if (tienePermiso($id_Rol, 'account-settings.php') && $puesto !== 'ASESOR'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="account-settings.php"><i class="icon-package"></i>Configuracion</a>
                        </li>
                        <?php endif; ?>
                        <?php if (tienePermiso($id_Rol, 'controlventa.php') && $puesto !== 'ASESOR'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="controlventa.php"><i class="icon-server"></i>Reportes</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-login"></i>Login
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="login.php">
                        <span>Salir</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="maintenance.html>
                        <span>Cambio de password</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="page-not-found.html">
                        <span>Page Not Found</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="maintenance.html">
                        <span>Maintenance</span>
                      </a>
                    </li>
                    </ul>
                </div>
            </nav>
            <!-- App Navbar ends -->

            <!-- App body starts -->
            <div class="app-body">
                <div class="container">
                    <!-- Breadcrumb start -->
                    <div class="row gx-3">
                        <div class="col-12">
                            <ol class="breadcrumb mb-3">
                                <li class="breadcrumb-item">
                                    <i class="icon-house_siding lh-1"></i>
                                    <a href="clients.php" class="text-decoration-none">Inicio</a>
                                </li>
                                <li class="breadcrumb-item">Seguimiento</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="filters-section">
                        <h5 class="mb-3">Filtros</h5>
                        <form method="GET" class="filters-row" id="filtersForm">
                            <div class="filter-group">
                                <label class="filter-label">Mes</label>
                                <select name="mes_filtro" class="form-select" onchange="this.form.submit()">
                                    <?php
                                    $meses = [
                                        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                                    ];
                                    foreach ($meses as $nombreMes) {
                                        $selected = ($mesSeleccionado == $nombreMes) ? 'selected' : '';
                                        echo "<option value=\"$nombreMes\" $selected>$nombreMes</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Tipo de Filtro</label>
                                <select name="filtro_opcion" class="form-select" id="filtro_opcion" onchange="toggleFiltros()">
                                    <option value="">Seleccionar tipo de filtro</option>
                                    <option value="fecha_creado" <?php echo ($filtro_opcion == 'fecha_creado') ? 'selected' : ''; ?>>Fecha de Creación</option>
                                    <option value="fecha_compromiso" <?php echo ($filtro_opcion == 'fecha_compromiso') ? 'selected' : ''; ?>>Fecha de Compromiso</option>
                                    <option value="fecha_cierre" <?php echo ($filtro_opcion == 'fecha_cierre') ? 'selected' : ''; ?>>Fecha de Cierre</option>
                                    <option value="tipo" <?php echo ($filtro_opcion == 'tipo') ? 'selected' : ''; ?>>Tipo de Servicio</option>
                                    <option value="articulo" <?php echo ($filtro_opcion == 'articulo') ? 'selected' : ''; ?>>Artículo de Venta</option>
                                </select>
                            </div>
                            
                            <!-- Filtros de Fecha de Creación -->
                            <div class="filter-group" id="filtro_fecha_creado" style="display: none;">
                                <label class="filter-label">Fecha Creación Desde</label>
                                <input type="date" name="fecha_creado_desde" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_creado_desde); ?>">
                            </div>
                            
                            <div class="filter-group" id="filtro_fecha_creado_hasta" style="display: none;">
                                <label class="filter-label">Fecha Creación Hasta</label>
                                <input type="date" name="fecha_creado_hasta" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_creado_hasta); ?>">
                            </div>
                            
                            <!-- Filtros de Fecha de Compromiso -->
                            <div class="filter-group" id="filtro_fecha_compromiso" style="display: none;">
                                <label class="filter-label">Fecha Compromiso Desde</label>
                                <input type="date" name="fecha_compromiso_desde" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_compromiso_desde); ?>">
                            </div>
                            
                            <div class="filter-group" id="filtro_fecha_compromiso_hasta" style="display: none;">
                                <label class="filter-label">Fecha Compromiso Hasta</label>
                                <input type="date" name="fecha_compromiso_hasta" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_compromiso_hasta); ?>">
                            </div>
                            
                            <!-- Filtros de Fecha de Cierre -->
                            <div class="filter-group" id="filtro_fecha_cierre" style="display: none;">
                                <label class="filter-label">Fecha Cierre Desde</label>
                                <input type="date" name="fecha_cierre_desde" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_cierre_desde); ?>">
                            </div>
                            
                            <div class="filter-group" id="filtro_fecha_cierre_hasta" style="display: none;">
                                <label class="filter-label">Fecha Cierre Hasta</label>
                                <input type="date" name="fecha_cierre_hasta" class="form-control" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_cierre_hasta); ?>">
                            </div>
                            
                            <!-- Filtro de Tipo de Servicio -->
                            <div class="filter-group" id="filtro_tipo" style="display: none;">
                                <label class="filter-label">Tipo de Servicio</label>
                                <select name="filtro_tipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tiposServicio as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo); ?>" 
                                                <?php echo ($filtro_tipo == $tipo) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Filtro de Artículo -->
                            <div class="filter-group" id="filtro_articulo" style="display: none;">
                                <label class="filter-label">Artículo de Venta</label>
                                <select name="filtro_articulo" class="form-select">
                                    <option value="">Todos los artículos</option>
                                    <?php foreach ($articulos as $articulo): ?>
                                        <option value="<?php echo htmlspecialchars($articulo); ?>" 
                                                <?php echo ($filtro_articulo == $articulo) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($articulo); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE'): ?>
                            <div class="filter-group">
                                <label class="filter-label">Coordinador</label>
                                <select name="filtro_coordinador" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos los coordinadores</option>
                                    <?php foreach ($coordinadores as $coord): ?>
                                        <option value="<?php echo htmlspecialchars($coord); ?>" 
                                                <?php echo ($filtro_coordinador == $coord) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($coord); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($puesto == 'COORDINADOR' || $puesto == 'EJECUTIVO' || $puesto == 'GERENTE'): ?>
                            <div class="filter-group">
                                <label class="filter-label">Asesor</label>
                                <select name="filtro_asesor" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos los asesores</option>
                                    <?php foreach ($asesores as $asesor): ?>
                                        <option value="<?php echo htmlspecialchars($asesor); ?>" 
                                                <?php echo ($filtro_asesor == $asesor) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($asesor); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="filter-group">
                                <label class="filter-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                            </div>
                        </form>
                    </div>

                    <!-- Estadísticas -->
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-value venta">$<?php echo number_format($totalVentas, 2, '.', ','); ?></div>
                            <div class="stat-label">Total Ventas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value embudo">$<?php echo number_format($totalEmbudo - $totalVentas, 2, '.', ','); ?></div>
                            <div class="stat-label">Total Embudo</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value faltante">$<?php echo number_format($venta_faltante, 2, '.', ','); ?></div>
                            <div class="stat-label">Meta Faltante</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value pronostico"><?php echo $prospectos_mes; ?></div>
                            <div class="stat-label">Prospectos del mes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value prospeccion"><?php echo number_format($prospeccion_faltante, 0); ?></div>
                            <div class="stat-label">Prospección Faltante</div>
                        </div>
                        <?php if ($puesto == 'ASESOR'): ?>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($proyeccion_asesor, 2); ?>%</div>
                            <div class="stat-label">Proyección Asesor</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Canvas de seguimiento -->
                    <div class="canvas-container">
                        <?php if (empty($clientesAgrupados)): ?>
                            <div class="text-center py-5">
                                <h4>No hay clientes para mostrar con los filtros actuales</h4>
                                <p class="text-muted">Intenta cambiar los filtros o el mes seleccionado</p>
                            </div>
                        <?php else: ?>
                            <div class="stages-table">
                                <div class="stages-header">
                                    <h4 class="mb-0">Pipeline de Ventas</h4>
                                </div>
                                <div class="stages-grid">
                                    <?php foreach ($clientesAgrupados as $etapa => $fechas): ?>
                                        <div class="stage-column">
                                                                                    <div class="stage-header">
                                            <div class="stage-title"><?php echo htmlspecialchars($etapa); ?></div>
                                            <div class="stage-stats">
                                                <div class="stage-count">
                                                    <?php echo $totalesPorEtapa[$etapa]['clientes']; ?>
                                                </div>
                                                <div class="stage-totals">
                                                    <div class="total-item">
                                                        <span class="total-label">Ventas:</span>
                                                        <span class="total-value">$<?php echo number_format($totalesPorEtapa[$etapa]['ventas'], 2, '.', ','); ?></span>
                                                    </div>
                                                    <div class="total-item">
                                                        <span class="total-label">Embudo:</span>
                                                        <span class="total-value">$<?php echo number_format($totalesPorEtapa[$etapa]['embudo'], 2, '.', ','); ?></span>
                                                    </div>
                                                    <?php if (isset($proyeccionesEtapas[$etapa])): ?>
                                                    <div class="total-item projection">
                                                        <span class="total-label">Proyección:</span>
                                                        <span class="total-value"><?php echo number_format($proyeccionesEtapas[$etapa], 2); ?>%</span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                            
                                            <div class="stage-content">
                                                <?php foreach ($fechas as $fechaSeguimiento => $clientesEnFecha): ?>
                                                    <div class="date-section">
                                                        <div class="date-header">
                                                            <i class="icon-calendar me-2"></i>
                                                            <?php echo htmlspecialchars($fechaSeguimiento); ?>
                                                            <span class="badge bg-secondary ms-2"><?php echo count($clientesEnFecha); ?></span>
                                                        </div>
                                                        
                                                        <div class="client-cards">
                                                            <?php foreach ($clientesEnFecha as $cliente): ?>
                                                                <div class="client-card" 
                                                                     data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                                                     data-nombre="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>"
                                                                     data-porcentaje="<?php echo htmlspecialchars($cliente['porcentaje']); ?>"
                                                                     data-etapa="<?php echo htmlspecialchars($cliente['etapa']); ?>"
                                                                     data-fecha-creado="<?php echo htmlspecialchars($cliente['fecha_creado'] ?? ''); ?>"
                                                                     data-fecha-modificado="<?php echo htmlspecialchars($cliente['fecha_modificado'] ?? ''); ?>"
                                                                     data-mes="<?php echo htmlspecialchars($cliente['mes'] ?? ''); ?>"
                                                                     data-origen="<?php echo htmlspecialchars($cliente['origen_cliente'] ?? ''); ?>"
                                                                     data-folio="<?php echo htmlspecialchars($cliente['folio_contrato'] ?? ''); ?>"
                                                                     data-fecha-cierre="<?php echo htmlspecialchars($cliente['fecha_cierre'] ?? ''); ?>"
                                                                     data-fecha-compromiso="<?php echo htmlspecialchars($cliente['fecha_compromiso'] ?? ''); ?>"
                                                                     data-articulo="<?php echo htmlspecialchars($cliente['articulo'] ?? ''); ?>"
                                                                     data-tipo="<?php echo htmlspecialchars($cliente['tipo'] ?? ''); ?>"
                                                                     data-venta-real="<?php echo htmlspecialchars($cliente['venta_real'] ?? '0'); ?>"
                                                                     data-venta-embudo="<?php echo htmlspecialchars($cliente['venta_embudo'] ?? '0'); ?>"
                                                                     data-estado="<?php echo htmlspecialchars($cliente['estado'] ?? ''); ?>"
                                                                     data-iniciales="<?php echo htmlspecialchars($cliente['iniciales'] ?? ''); ?>"
                                                                     data-supervisor="<?php echo htmlspecialchars($cliente['supervisor'] ?? ''); ?>"
                                                                     data-telefono="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                                                                     onclick="showClientDetails(this)">
                                                                    <div class="client-name">
                                                                        <?php echo htmlspecialchars($cliente['nombre_completo']); ?>
                                                                    </div>
                                                                    <div class="client-essential">
                                                                        <div class="essential-item">
                                                                            <span class="essential-label">Asesor:</span>
                                                                            <span class="essential-value"><?php echo htmlspecialchars($cliente['iniciales']); ?></span>
                                                                        </div>
                                                                        <div class="essential-item">
                                                                            <span class="essential-label">Artículo:</span>
                                                                            <span class="essential-value"><?php echo htmlspecialchars($cliente['articulo']); ?></span>
                                                                        </div>
                                                                        <div class="essential-item">
                                                                            <span class="essential-label">Venta:</span>
                                                                            <span class="essential-value">$<?php echo number_format($cliente['venta_real'], 2, '.', ','); ?></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Modal para detalles del cliente -->
                    <div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="clientDetailsModalLabel">Detalles del Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="clientDetailsContent">
                                    <!-- El contenido se llenará dinámicamente -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" id="btnLlamar" onclick="llamarCliente()" style="display: none;">
                                        <i class="icon-phone me-1"></i>Llamar
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Actividades -->
                    <div class="modal fade" id="actividadesModal" tabindex="-1" aria-labelledby="actividadesModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="actividadesModalLabel">
                                        <i class="icon-calendar me-2"></i>Gestionar Actividades del Cliente
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Botón para agregar nueva actividad -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Actividades de Seguimiento</h6>
                                        <button type="button" class="btn btn-success btn-sm" onclick="abrirFormularioActividad()">
                                            <i class="icon-add me-1"></i>Nueva Actividad
                                        </button>
                                    </div>
                                    
                                    <!-- Lista de actividades existentes -->
                                    <div id="listaActividades" class="mb-3">
                                        <!-- Las actividades se cargarán aquí dinámicamente -->
                                    </div>
                                    
                                    <!-- Formulario de actividad (oculto inicialmente) -->
                                    <div id="formularioActividad" style="display: none;">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0" id="tituloFormulario">Nueva Actividad</h6>
                                            </div>
                                            <div class="card-body">
                                                <form id="formActividad">
                                                    <input type="hidden" id="actividad_id" name="actividad_id">
                                                    <input type="hidden" id="cliente_id_actividad" name="cliente_id">
                                                    
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="actividad_tipo" class="form-label fw-semibold">
                                                                <i class="icon-category text-primary me-1"></i>Tipo de Actividad <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-select" id="actividad_tipo" name="actividad_tipo" required>
                                                                <option value="">Seleccionar tipo</option>
                                                                <option value="LLAMADA">📞 Llamada telefónica</option>
                                                                <option value="VISITA">🏠 Visita presencial</option>
                                                                <option value="EMAIL">📧 Envío de email</option>
                                                                <option value="WHATSAPP">💬 WhatsApp</option>
                                                                <option value="CITA">📅 Cita programada</option>
                                                                <option value="SEGUIMIENTO">🔄 Seguimiento</option>
                                                                <option value="COTIZACION">💰 Envío de cotización</option>
                                                                <option value="CONTRATO">📄 Revisión de contrato</option>
                                                                <option value="PAGO">💳 Gestión de pago</option>
                                                                <option value="OTRO">🔧 Otro</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="fuente_prospeccion_actividad" class="form-label fw-semibold">
                                                                <i class="icon-source text-info me-1"></i>Fuente de Prospección
                                                            </label>
                                                            <select class="form-select" id="fuente_prospeccion_actividad" name="fuente_prospeccion_actividad">
                                                                <option value="">Seleccionar fuente</option>
                                                                <option value="ANUNCIO">📢 ANUNCIO</option>
                                                                <option value="CAMBACEO">🚶 CAMBACEO</option>
                                                                <option value="Telemarketing">📞 TELEMARKETING</option>
                                                                <option value="Venta Digital">💻 VENTA DIGITAL</option>
                                                                <option value="FUNERAL">⚰️ FUNERAL</option>
                                                                <option value="CLIENTE META">🎯 CLIENTE META</option>
                                                                <option value="FACEBOOK">📘 FACEBOOK</option>
                                                                <option value="EVENTO">🎪 EVENTO</option>
                                                                <option value="REFERIDO">👥 REFERIDO</option>
                                                                <option value="MERCADO NATURAL">🏪 MERCADO NATURAL</option>
                                                                <option value="TITULOS">📄 TITULOS</option>
                                                                <option value="MODULO">🏢 MODULO</option>
                                                                <option value="DEMOSTRACIONES">🎭 DEMOSTRACIONES</option>
                                                                <option value="PUNTO">📍 PUNTO</option>
                                                                <option value="GUARDIA">🛡️ GUARDIA</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="col-12">
                                                            <label for="cita_descripcion" class="form-label fw-semibold">
                                                                <i class="icon-notes text-success me-1"></i>Descripción de la Actividad <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea class="form-control" id="cita_descripcion" name="cita_descripcion" rows="3" 
                                                                      placeholder="Describe la actividad a realizar..." required></textarea>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="fecha_inicio_actividad" class="form-label fw-semibold">
                                                                <i class="icon-schedule text-warning me-1"></i>Fecha y Hora de Inicio <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="datetime-local" class="form-control" id="fecha_inicio_actividad" name="fecha_inicio_actividad" required>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="fecha_fin_actividad" class="form-label fw-semibold">
                                                                <i class="icon-event text-danger me-1"></i>Fecha y Hora de Fin <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="datetime-local" class="form-control" id="fecha_fin_actividad" name="fecha_fin_actividad" required>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="completada_actividad" class="form-label fw-semibold">
                                                                <i class="icon-check_circle text-success me-1"></i>Estado <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-select" id="completada_actividad" name="completada_actividad" required>
                                                                <option value="PROGRAMADA">⏳ PROGRAMADA</option>
                                                                <option value="COMPLETADA">✅ COMPLETADA</option>
                                                                <option value="CANCELADA">❌ CANCELADA</option>
                                                                <option value="REPROGRAMADA">🔄 REPROGRAMADA</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <label for="notas_actividad" class="form-label fw-semibold">
                                                                <i class="icon-notes text-secondary me-1"></i>Notas Adicionales
                                                            </label>
                                                            <input type="text" class="form-control" id="notas_actividad" name="notas_actividad" 
                                                                   placeholder="Notas adicionales...">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                        <button type="button" class="btn btn-secondary" onclick="cerrarFormularioActividad()">
                                                            <i class="icon-close me-1"></i>Cancelar
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="icon-save me-1"></i>Guardar Actividad
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- App body ends -->

            <!-- App footer start -->
            <div class="app-footer">
                <div class="container">
                    <span>© Portal mausoleos 2025</span>
                </div>
            </div>
            <!-- App footer end -->
        </div>
        <!-- App container ends -->
    </div>
    <!-- Page wrapper end -->

    <!-- JavaScript Files -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>
    <script src="assets/js/custom.js"></script>
    
    <script>
        // Función para mostrar/ocultar filtros dinámicamente
        function toggleFiltros() {
            const filtroOpcion = document.getElementById('filtro_opcion').value;
            
            // Ocultar todos los filtros dinámicos
            const filtrosDinamicos = [
                'filtro_fecha_creado', 'filtro_fecha_creado_hasta',
                'filtro_fecha_compromiso', 'filtro_fecha_compromiso_hasta',
                'filtro_fecha_cierre', 'filtro_fecha_cierre_hasta',
                'filtro_tipo', 'filtro_articulo'
            ];
            
            filtrosDinamicos.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.style.display = 'none';
                    // Limpiar valores cuando se oculta
                    const input = elemento.querySelector('input, select');
                    if (input) {
                        input.value = '';
                    }
                }
            });
            
            // Mostrar los filtros correspondientes según la opción seleccionada
            switch(filtroOpcion) {
                case 'fecha_creado':
                    document.getElementById('filtro_fecha_creado').style.display = 'block';
                    document.getElementById('filtro_fecha_creado_hasta').style.display = 'block';
                    break;
                case 'fecha_compromiso':
                    document.getElementById('filtro_fecha_compromiso').style.display = 'block';
                    document.getElementById('filtro_fecha_compromiso_hasta').style.display = 'block';
                    break;
                case 'fecha_cierre':
                    document.getElementById('filtro_fecha_cierre').style.display = 'block';
                    document.getElementById('filtro_fecha_cierre_hasta').style.display = 'block';
                    break;
                case 'tipo':
                    document.getElementById('filtro_tipo').style.display = 'block';
                    break;
                case 'articulo':
                    document.getElementById('filtro_articulo').style.display = 'block';
                    break;
                default:
                    // Si no hay opción seleccionada, limpiar todos los filtros dinámicos
                    filtrosDinamicos.forEach(id => {
                        const elemento = document.getElementById(id);
                        if (elemento) {
                            const input = elemento.querySelector('input, select');
                            if (input) {
                                input.value = '';
                            }
                        }
                    });
                    break;
            }
        }
        
        // Inicializar filtros al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            toggleFiltros();
        });

        // Función de búsqueda
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.client-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Ocultar secciones vacías
            const dateSections = document.querySelectorAll('.date-section');
            dateSections.forEach(section => {
                const visibleCards = section.querySelectorAll('.client-card[style="display: block"]');
                if (visibleCards.length === 0) {
                    section.style.display = 'none';
                } else {
                    section.style.display = 'block';
                }
            });
            
            const stageSections = document.querySelectorAll('.stage-column');
            stageSections.forEach(section => {
                const visibleDateSections = section.querySelectorAll('.date-section[style="display: block"]');
                if (visibleDateSections.length === 0) {
                    section.style.display = 'none';
                } else {
                    section.style.display = 'block';
                }
            });
        });

        // Función para mostrar detalles del cliente en un modal
        function showClientDetails(element) {
            try {
                console.log('Función showClientDetails llamada');
                
                // Obtener datos de los data attributes
                const clienteId = element.getAttribute('data-id') || 'N/A';
                const nombre = element.getAttribute('data-nombre') || 'N/A';
                const porcentaje = element.getAttribute('data-porcentaje') || '0';
                const etapa = element.getAttribute('data-etapa') || 'N/A';
                const fechaCreado = element.getAttribute('data-fecha-creado') || 'N/A';
                const fechaModificado = element.getAttribute('data-fecha-modificado') || 'N/A';
                const mes = element.getAttribute('data-mes') || 'N/A';
                const origen = element.getAttribute('data-origen') || 'N/A';
                const folio = element.getAttribute('data-folio') || 'N/A';
                const fechaCierre = element.getAttribute('data-fecha-cierre') || 'N/A';
                const fechaCompromiso = element.getAttribute('data-fecha-compromiso') || 'N/A';
                const articulo = element.getAttribute('data-articulo') || 'N/A';
                const tipo = element.getAttribute('data-tipo') || 'N/A';
                const ventaReal = parseFloat(element.getAttribute('data-venta-real')) || 0;
                const ventaEmbudo = parseFloat(element.getAttribute('data-venta-embudo')) || 0;
                const estado = element.getAttribute('data-estado') || 'N/A';
                const iniciales = element.getAttribute('data-iniciales') || 'N/A';
                const supervisor = element.getAttribute('data-supervisor') || 'N/A';
                const telefono = element.getAttribute('data-telefono') || 'N/A';
                
                console.log('Datos extraídos:', {nombre, porcentaje, etapa, fechaCreado});
                
                const modalContent = document.getElementById('clientDetailsContent');
                console.log('Modal content element:', modalContent);
                
                modalContent.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Información del Cliente</h6>
                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirActividadesModal(${clienteId})">
                            <i class="icon-calendar me-1"></i>Gestionar Actividades
                        </button>
                    </div>
                    <div class="client-details-grid">
                        <div class="detail-row">
                            <div class="detail-label">ID Cliente:</div>
                            <div class="detail-value">${clienteId}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Nombre Completo:</div>
                            <div class="detail-value">${nombre}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Porcentaje:</div>
                            <div class="detail-value">${porcentaje}%</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Etapa:</div>
                            <div class="detail-value">${etapa}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fecha Creado:</div>
                            <div class="detail-value">${fechaCreado}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fecha Última Actividad:</div>
                            <div class="detail-value">${fechaModificado}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Mes:</div>
                            <div class="detail-value">${mes}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fuente:</div>
                            <div class="detail-value">${origen}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Folio:</div>
                            <div class="detail-value">${folio}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fecha Compromiso Venta:</div>
                            <div class="detail-value">${fechaCierre}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fecha Seguimiento:</div>
                            <div class="detail-value">${fechaCompromiso}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Artículo:</div>
                            <div class="detail-value">${articulo}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Tipo de Servicio:</div>
                            <div class="detail-value">${tipo}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Venta Real:</div>
                            <div class="detail-value">$ ${ventaReal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Venta Embudo:</div>
                            <div class="detail-value">$ ${ventaEmbudo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Estado:</div>
                            <div class="detail-value">${estado}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Asesor:</div>
                            <div class="detail-value">${iniciales}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Coordinador:</div>
                            <div class="detail-value">${supervisor}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Teléfono:</div>
                            <div class="detail-value">${telefono}</div>
                        </div>
                    </div>
                `;
                
                // Mostrar/ocultar botón de llamar según si hay teléfono
                const btnLlamar = document.getElementById('btnLlamar');
                if (telefono && telefono !== 'N/A' && telefono.trim() !== '') {
                    btnLlamar.style.display = 'inline-block';
                    btnLlamar.setAttribute('data-telefono', telefono);
                } else {
                    btnLlamar.style.display = 'none';
                }
                
                const modalElement = document.getElementById('clientDetailsModal');
                console.log('Modal element:', modalElement);
                
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    console.log('Modal mostrado correctamente');
                } else {
                    console.error('Bootstrap no está disponible');
                    // Fallback: mostrar el modal manualmente
                    modalElement.style.display = 'block';
                    modalElement.classList.add('show');
                    document.body.classList.add('modal-open');
                }
            } catch (error) {
                console.error('Error al mostrar detalles del cliente:', error);
                alert('Error al cargar los detalles del cliente. Por favor, inténtalo de nuevo.');
            }
        }

        // Variables globales para actividades
        let actividadesData = [];
        let actividadEditando = null;

        // Función para abrir el modal de actividades
        function abrirActividadesModal(clienteId) {
            document.getElementById('cliente_id_actividad').value = clienteId;
            cargarActividades();
            const modal = new bootstrap.Modal(document.getElementById('actividadesModal'));
            modal.show();
            
            // Aplicar mayúsculas a los campos del modal cuando se abra
            modal._element.addEventListener('shown.bs.modal', function() {
                if (typeof applyUppercaseToAllTextFields === 'function') {
                    applyUppercaseToAllTextFields();
                }
            });
        }

        // Función para cargar actividades del cliente
        function cargarActividades() {
            const clienteId = document.getElementById('cliente_id_actividad').value;
            if (!clienteId) return;
            
            fetch(`controlador/get_actividades_cliente.php?cliente_id=${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actividadesData = data.actividades;
                        mostrarActividades();
                    } else {
                        console.error('Error al cargar actividades:', data.message);
                        mostrarMensaje('Error al cargar actividades: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Error de conexión al cargar actividades', 'danger');
                });
        }

        // Función para mostrar actividades en la lista
        function mostrarActividades() {
            const listaActividades = document.getElementById('listaActividades');
            
            if (actividadesData.length === 0) {
                listaActividades.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="icon-info-circle me-2"></i>
                        No hay actividades registradas para este cliente.
                    </div>
                `;
                return;
            }
            
            let html = '<div class="row g-3">';
            
            actividadesData.forEach(actividad => {
                const fechaInicio = new Date(actividad.fechahora_inicio);
                const fechaFin = new Date(actividad.fechahora_fin);
                const fechaCreacion = new Date(actividad.fecha_creado);
                
                const estadoClass = {
                    'PROGRAMADA': 'warning',
                    'COMPLETADA': 'success',
                    'CANCELADA': 'danger',
                    'REPROGRAMADA': 'info'
                }[actividad.completada] || 'secondary';
                
                html += `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">${actividad.actividad}</h6>
                                    <span class="badge bg-${estadoClass}">${actividad.completada}</span>
                                </div>
                                <p class="card-text text-muted small mb-2">${actividad.cita}</p>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="icon-schedule me-1"></i>
                                            Inicio: ${fechaInicio.toLocaleString()}
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="icon-event me-1"></i>
                                            Fin: ${fechaFin.toLocaleString()}
                                        </small>
                                    </div>
                                </div>
                                ${actividad.fuente_prospeccion ? `<small class="text-info"><i class="icon-source me-1"></i>${actividad.fuente_prospeccion}</small><br>` : ''}
                                ${actividad.notas ? `<small class="text-secondary"><i class="icon-notes me-1"></i>${actividad.notas}</small><br>` : ''}
                                <small class="text-muted">
                                    <i class="icon-calendar me-1"></i>
                                    Creado: ${fechaCreacion.toLocaleString()}
                                </small>
                                <div class="d-flex justify-content-end gap-1 mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editarActividad(${actividad.id})">
                                        <i class="icon-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarActividad(${actividad.id})">
                                        <i class="icon-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            listaActividades.innerHTML = html;
        }

        // Función para abrir formulario de nueva actividad
        function abrirFormularioActividad() {
            actividadEditando = null;
            document.getElementById('tituloFormulario').textContent = 'Nueva Actividad';
            document.getElementById('formActividad').reset();
            document.getElementById('actividad_id').value = '';
            
            // Establecer fecha y hora actual como predeterminada
            const ahora = new Date();
            const fechaHora = ahora.toISOString().slice(0, 16);
            document.getElementById('fecha_inicio_actividad').value = fechaHora;
            
            // Establecer fecha de fin 1 hora después
            const fechaFin = new Date(ahora.getTime() + 60 * 60 * 1000);
            const fechaHoraFin = fechaFin.toISOString().slice(0, 16);
            document.getElementById('fecha_fin_actividad').value = fechaHoraFin;
            
            document.getElementById('formularioActividad').style.display = 'block';
            document.getElementById('listaActividades').style.display = 'none';
            
            // Aplicar mayúsculas a los campos del formulario
            setTimeout(() => {
                if (typeof applyUppercaseToAllTextFields === 'function') {
                    applyUppercaseToAllTextFields();
                }
            }, 100);
        }

        // Función para cerrar formulario de actividad
        function cerrarFormularioActividad() {
            document.getElementById('formularioActividad').style.display = 'none';
            document.getElementById('listaActividades').style.display = 'block';
            actividadEditando = null;
        }

        // Función para editar actividad
        function editarActividad(id) {
            const actividad = actividadesData.find(a => a.id == id);
            if (!actividad) return;
            
            actividadEditando = actividad;
            document.getElementById('tituloFormulario').textContent = 'Editar Actividad';
            document.getElementById('actividad_id').value = actividad.id;
            document.getElementById('actividad_tipo').value = actividad.actividad;
            document.getElementById('cita_descripcion').value = actividad.cita;
            document.getElementById('fecha_inicio_actividad').value = actividad.fechahora_inicio.slice(0, 16);
            document.getElementById('fecha_fin_actividad').value = actividad.fechahora_fin.slice(0, 16);
            document.getElementById('completada_actividad').value = actividad.completada;
            document.getElementById('fuente_prospeccion_actividad').value = actividad.fuente_prospeccion || '';
            document.getElementById('notas_actividad').value = actividad.notas || '';
            
            document.getElementById('formularioActividad').style.display = 'block';
            document.getElementById('listaActividades').style.display = 'none';
        }

        // Función para eliminar actividad
        function eliminarActividad(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta actividad?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('actividad_id', id);
            
            fetch('controlador/delete_actividad.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cargarActividades();
                } else {
                    mostrarMensaje(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión al eliminar actividad', 'danger');
            });
        }

        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo) {
            // Crear o actualizar mensaje en el modal
            let mensajeDiv = document.getElementById('mensajeActividades');
            if (!mensajeDiv) {
                mensajeDiv = document.createElement('div');
                mensajeDiv.id = 'mensajeActividades';
                mensajeDiv.className = 'alert alert-dismissible fade show';
                document.querySelector('#actividadesModal .modal-body').insertBefore(mensajeDiv, document.querySelector('#actividadesModal .modal-body').firstChild);
            }
            
            mensajeDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
            mensajeDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                if (mensajeDiv) {
                    mensajeDiv.remove();
                }
            }, 5000);
        }

        // Event listener para el formulario de actividades
        document.addEventListener('DOMContentLoaded', function() {
            const formActividad = document.getElementById('formActividad');
            if (formActividad) {
                formActividad.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('controlador/save_actividad.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarMensaje(data.message, 'success');
                            cerrarFormularioActividad();
                            cargarActividades();
                        } else {
                            mostrarMensaje(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarMensaje('Error de conexión al guardar actividad', 'danger');
                    });
                });
            }
        });
        
        // Función para llamar al cliente
        function llamarCliente() {
            const btnLlamar = document.getElementById('btnLlamar');
            const telefono = btnLlamar.getAttribute('data-telefono');
            
            if (telefono && telefono !== 'N/A' && telefono.trim() !== '') {
                // Limpiar el número de teléfono (remover espacios, guiones, etc.)
                const numeroLimpio = telefono.replace(/[\s\-\(\)]/g, '');
                
                // Crear enlace tel: para dispositivos móviles
                const enlaceLlamada = `tel:${numeroLimpio}`;
                
                // Crear elemento temporal para activar la llamada
                const link = document.createElement('a');
                link.href = enlaceLlamada;
                link.click();
                
                console.log('Iniciando llamada a:', numeroLimpio);
            } else {
                alert('No hay número de teléfono disponible para este cliente.');
            }
        }
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
</body>
</html>