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

// Verificar que el usuario tenga permisos de administrador
if (!in_array($puesto, ['GERENTE', 'DIRECTOR'])) {
    header("Location: clients.php");
    exit();
}
$mensaje = '';

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_porcentajes'])) {
    try {
        $conn->begin_transaction();
        
        foreach ($_POST['porcentajes'] as $etapa => $porcentaje) {
            $porcentaje = (float)$porcentaje;
            
            // Verificar si existe el registro
            $sqlCheck = "SELECT id FROM embudo_plaza WHERE plaza = ? AND etapa = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("ss", $sucursal, $etapa);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows > 0) {
                // Actualizar registro existente
                $sqlUpdate = "UPDATE embudo_plaza SET porcentaje = ? WHERE plaza = ? AND etapa = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("dss", $porcentaje, $sucursal, $etapa);
                $stmtUpdate->execute();
            } else {
                // Insertar nuevo registro
                $sqlInsert = "INSERT INTO embudo_plaza (plaza, etapa, porcentaje) VALUES (?, ?, ?)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param("ssd", $sucursal, $etapa, $porcentaje);
                $stmtInsert->execute();
            }
        }
        
        $conn->commit();
        $mensaje = '<div class="alert alert-success">Porcentajes actualizados correctamente.</div>';
    } catch (Exception $e) {
        $conn->rollback();
        $mensaje = '<div class="alert alert-danger">Error al actualizar: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Obtener los porcentajes actuales
$sqlPorcentajes = "SELECT etapa, porcentaje FROM embudo_plaza WHERE plaza = ? AND activo = 1 ORDER BY 
  CASE etapa
    WHEN 'BASE DE DATOS' THEN 1
    WHEN 'ACTIVAR' THEN 2
    WHEN 'ESTRECHAR' THEN 3
    WHEN 'EN PRONOSTICO' THEN 4
    WHEN 'CERRADO GANADO' THEN 5
    WHEN 'CERRADO PERDIDO' THEN 6
    ELSE 7
  END";

$stmtPorcentajes = $con->prepare($sqlPorcentajes);
$stmtPorcentajes->bind_param("s", $sucursal);
$stmtPorcentajes->execute();
$resultPorcentajes = $stmtPorcentajes->get_result();

$porcentajesActuales = [];
if ($resultPorcentajes->num_rows > 0) {
    while ($row = $resultPorcentajes->fetch_assoc()) {
        $porcentajesActuales[$row['etapa']] = $row['porcentaje'];
    }
}

// Valores por defecto si no hay datos
$etapasDefault = [
    "BASE DE DATOS" => 0,
    "ACTIVAR" => 0,
    "ESTRECHAR" => 0.25,
    "EN PRONOSTICO" => 0.7,
    "CERRADO GANADO" => 1,
    "CERRADO PERDIDO" => 0
];

// Combinar valores por defecto con los actuales
$porcentajesFinales = array_merge($etapasDefault, $porcentajesActuales);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Administración de Porcentajes por Etapa - Portal Mausoleos</title>
    <link rel="shortcut icon" href="assets/images/GrupoMausoleos.png" />
    <link rel="stylesheet" href="assets/fonts/icomoon/style.css" />
    <link rel="stylesheet" href="assets/css/main.min.css" />
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />
</head>

<body>
    <?php if (!$acceso): ?>
    <?php echo generarOverlayAccesoDenegado(); ?>
    <?php endif; ?>
    
    <div class="page-wrapper" <?php echo !$acceso ? 'style="pointer-events: none; opacity: 0.3;"' : ''; ?>>
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
                            </div>
                        </div>
                        <div class="col-md-9 col-10">
                            <div class="header-actions col">
                                <div class="dropdown ms-3">
                                    <a class="dropdown-toggle d-flex py-2 align-items-center text-decoration-none" href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="assets/images/GrupoMausoleos.png" class="rounded-2 img-3x" alt="Bootstrap Gallery" />
                                        <div class="ms-2 text-truncate d-lg-block d-none text-white">
                                            <span class="d-flex opacity-50 small"><?php echo htmlspecialchars($puesto); ?></span>
                                            <span><?php echo htmlspecialchars($_SESSION['correo']); ?></span>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="mx-3 mt-2 d-grid">
                                            <a href="clients.php" class="btn btn-outline-primary">Volver</a>
                                            <a href="login.php" class="btn btn-outline-danger">Salir</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App body starts -->
            <div class="app-body">
                <div class="container">
                    <!-- Breadcrumb start -->
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item">
                            <i class="icon-house_siding lh-1"></i>
                            <a href="clients.php" class="text-decoration-none">Inicio</a>
                        </li>
                        <li class="breadcrumb-item">Administración de Porcentajes</li>
                    </ol>

                    <div class="row gx-3">
                        <div class="col-xxl-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title">Configuración de Porcentajes por Etapa - Plaza: <?php echo htmlspecialchars($sucursal); ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php echo $mensaje; ?>
                                    
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>Etapa</th>
                                                                <th>Porcentaje (%)</th>
                                                                <th>Descripción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($porcentajesFinales as $etapa => $porcentaje): ?>
                                                            <tr>
                                                                <td><strong><?php echo htmlspecialchars($etapa); ?></strong></td>
                                                                <td>
                                                                    <input type="number" 
                                                                           class="form-control" 
                                                                           name="porcentajes[<?php echo htmlspecialchars($etapa); ?>]" 
                                                                           value="<?php echo htmlspecialchars($porcentaje); ?>" 
                                                                           step="0.01" 
                                                                           min="0" 
                                                                           max="1" 
                                                                           style="width: 100px;">
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    $descripciones = [
                                                                        'BASE DE DATOS' => 'Cliente en base de datos sin actividad',
                                                                        'ACTIVAR' => 'Cliente activado, primer contacto',
                                                                        'ESTRECHAR' => 'Cliente en proceso de cierre',
                                                                        'EN PRONOSTICO' => 'Cliente con alta probabilidad de cierre',
                                                                        'CERRADO GANADO' => 'Venta concretada',
                                                                        'CERRADO PERDIDO' => 'Venta perdida'
                                                                    ];
                                                                    echo htmlspecialchars($descripciones[$etapa] ?? '');
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="card-title">Información</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><strong>Nota:</strong> Los porcentajes deben estar entre 0 y 1 (0% a 100%)</p>
                                                        <ul>
                                                            <li><strong>0.00</strong> = 0%</li>
                                                            <li><strong>0.25</strong> = 25%</li>
                                                            <li><strong>0.70</strong> = 70%</li>
                                                            <li><strong>1.00</strong> = 100%</li>
                                                        </ul>
                                                        <p class="text-muted">Estos porcentajes se utilizan para calcular la venta en embudo de cada cliente según su etapa actual.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" name="actualizar_porcentajes" class="btn btn-primary">
                                                    <i class="icon-save"></i> Guardar Cambios
                                                </button>
                                                <a href="clients.php" class="btn btn-secondary">
                                                    <i class="icon-arrow-left"></i> Volver
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App footer start -->
            <div class="app-footer">
                <div class="container">
                    <span>© Portal mausoleos 2025</span>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>
    <script src="assets/js/custom.js"></script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
</body>
</html> 