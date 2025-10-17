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





$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
       $result = $con->query($sqlCierre);

       if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         $mes = $row['mes']; 
       } else {
         $mes = 'N/A';
       }
       $respuesta='';


?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portal Mausoleos</title>

    <!-- Meta -->
    <meta name="description" content="Portal Mausoleos" />
    <meta name="author" content="Portal Mausoleos" />
    <link rel="canonical" href="https://www.portalmausoleos.com.mx/">
    <meta property="og:url" content="https://www.portalmausoleos.com.mx">
    <meta property="og:title" content="Portal Mausoleos">
    <meta property="og:description" content="Portal Mausoleos">
    <meta property="og:type" content="Website">
    <meta property="og:site_name" content="Portal Mausoleos">
    <link rel="shortcut icon" href="assets/images/GrupoMausoleos.png" />

    <!-- *************
			************ CSS Files *************
		************* -->
    <!-- Icomoon Font Icons css -->
    <link rel="stylesheet" href="assets/fonts/icomoon/style.css" />

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.min.css" />

    <!-- *************
			************ Vendor Css Files *************
		************ -->

    <!-- Scrollbar CSS -->
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />
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

          <!-- Container starts -->
          <div class="container">

            <!-- Row starts -->
            <div class="row gx-3">
              <div class="col-md-3 col-2">

                <!-- App brand starts -->
                <div class="app-brand">
                  <a href="index.html" class="d-lg-block d-none">
                    <img src="assets/images/GrupoMausoleos.png" class="logo" alt="Bootstrap Gallery" />
                  </a>
                  <a href="index.html" class="d-lg-none d-md-block">
                    <img src="assets/images/GrupoMausoleos.png" class="logo" alt="Bootstrap Gallery" />
                  </a>
                </div>
                <!-- App brand ends -->

              </div>

              <div class="col-md-9 col-10">

                <!-- App header actions start -->
                <div class="header-actions col">

                  <!-- Search container start -->
                  <div class="search-container d-none d-lg-block">
                    <input type="text" id="search" class="form-control" placeholder="Search" />
                    <i class="icon-search"></i>
                  </div>
                  <!-- Search container end -->

                  <div class="d-sm-flex d-none align-items-center gap-2">
                    <div class="dropdown">
                      <a class="dropdown-toggle header-action-icon" href="#!" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="icon-warning fs-4 lh-1 text-white"></i>
                        <span class="count">7</span>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end dropdown-menu-md">
                        <h5 class="fw-semibold px-3 py-2 text-primary">
                          Notifications
                        </h5>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-success rounded-circle me-3">
                              <i class="icon-shopping-bag text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Rosalie Deleon</h6>
                              <p class="mb-1 text-secondary">
                                You have new order.
                              </p>
                              <p class="small m-0 text-secondary">
                                30 mins ago
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-danger rounded-circle me-3">
                              <i class="icon-alert-triangle text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Donovan Stuart</h6>
                              <p class="mb-2">Membership has been expired.</p>
                              <p class="small m-0 text-secondary">2 days ago</p>
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-warning rounded-circle me-3">
                              <i class="icon-shopping-cart text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Roscoe Richards</h6>
                              <p class="mb-2">Payment pending. Pay now.</p>
                              <p class="small m-0 text-secondary">3 days ago</p>
                            </div>
                          </div>
                        </div>
                        <div class="d-grid mx-3 my-1">
                          <a href="javascript:void(0)" class="btn btn-outline-primary">View all</a>
                        </div>
                      </div>
                    </div>
                    <div class="dropdown">
                      <a class="dropdown-toggle header-action-icon" href="#!" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="icon-drafts fs-4 lh-1 text-white"></i>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end dropdown-menu-md">
                        <h5 class="fw-semibold px-3 py-2 text-primary">
                          Messages
                        </h5>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <img src="assets/images/user3.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Angelia Payne</h6>
                              <p class="mb-1 text-secondary">
                                Membership has been ended.
                              </p>
                              <p class="small m-0 text-secondary">
                                Today, 07:30pm
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <img src="assets/images/user1.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Clyde Fowler</h6>
                              <p class="mb-1 text-secondary">
                                Congratulate, James for new job.
                              </p>
                              <p class="small m-0 text-secondary">
                                Today, 08:00pm
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <img src="assets/images/user4.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Sophie Michiels</h6>
                              <p class="mb-2 text-secondary">
                                Lewis added new schedule release.
                              </p>
                              <p class="small m-0 text-secondary">
                                Today, 09:30pm
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="d-grid mx-3 my-1">
                          <a href="javascript:void(0)" class="btn btn-outline-primary">View all</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="dropdown ms-3">
                    <a id="userSettings" class="dropdown-toggle d-flex py-2 align-items-center text-decoration-none"
                      href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <img src="assets/images/GrupoMausoleos.png" class="rounded-2 img-3x" alt="Bootstrap Gallery" />
                      <div class="ms-2 text-truncate d-lg-block d-none text-white">
                        <span class="d-flex opacity-50 small"><?php echo htmlspecialchars($puesto); ?></span>
                        <span><?php echo htmlspecialchars($correo); ?><</span>
                      </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                      <div class="header-action-links">
                        <a class="dropdown-item" href="profile.html"><i
                            class="icon-user border border-primary text-primary"></i>Profile</a>
                        <a class="dropdown-item" href="settings.html"><i
                            class="icon-settings border border-danger text-danger"></i>Settings</a>
                        <a class="dropdown-item" href="widgets.html"><i
                            class="icon-box border border-info text-info"></i>Widgets</a>
                      </div>
                      <div class="mx-3 mt-2 d-grid">
                        <a href="login.php" class="btn btn-outline-danger">Logout</a>
                      </div>
                    </div>
                  </div>

                  <!-- Toggle Menu starts -->
                  <button class="btn btn-warning btn-sm ms-3 d-lg-none d-md-block" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#MobileMenu">
                    <i class="icon-menu"></i>
                  </button>
                  <!-- Toggle Menu ends -->

                </div>
                <!-- App header actions end -->

              </div>
            </div>
            <!-- Row ends -->

          </div>
          <!-- Container ends -->

        </div>
        <!-- App header ends -->

        <!-- App navbar starts -->
        <nav class="navbar navbar-expand-lg">
          <div class="container">
            <div class="offcanvas offcanvas-end" id="MobileMenu">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title semibold">Navigation</h5>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="offcanvas">
                  <i class="icon-clear"></i>
                </button>
              </div>
              <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="clients.php" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                    <i class="icon-supervised_user_circle" href="clients.php"></i> Cliente
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="agenda.php">
                        <span>Agenda actividad</span></a>
                    </li>
                  </ul>
                </li>

                <li class="nav-item">
                  <a class="nav-link" href="agents.php">
                    <i class="icon-support_agent"></i>Seguimiento
                  </a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link" href="leds.php">
                    <i class="fs-3 icon-contacts"></i>Leads Digitales
                  </a>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-package"></i>Configuracion
                  </a>
                 <ul class="dropdown-menu dropdown-megamenu">
                    <li>
                      <a class="dropdown-item" href="account-settings.php">
                        <span>Control de usuario</span></a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-server"></i>Reportes
                  </a>
                  <ul class="dropdown-menu dropdown-megamenu">
                    <li>
                      <a class="dropdown-item" href="#">
                        <span>Direccion comercial</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item current-page" href="controlventa.php">
                        <span>Plaza Venta</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="embudo.php">
                        <span>Indicador Asesor</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="indicadordia.php">
                        <span>Actividad diaria</span>
                      </a>
                      </li>
                      <li>
                      <a class="dropdown-item" href="bono-proyec.php">
                        <span>Bono Proyeccion</span>
                      </a>
                      </li>
                  </ul>
                </li>
                
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
                      <a class="dropdown-item" href="#">
                        <span>Cambio de password</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="page-not-found.html">
                        <span>Pagina no encontrada</span>
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>
        <!-- App Navbar ends -->

        <!-- App body starts -->
        <div class="app-body">

          <!-- Container starts -->
          <div class="container">

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12 col-xl-6">
                <!-- Breadcrumb start -->
                <ol class="breadcrumb mb-3">
                  <li class="breadcrumb-item">
                    <i class="icon-house_siding lh-1"></i>
                    <a href="clients.php" class="text-decoration-none">Inicio</a>
                  </li>
                  <li class="breadcrumb-item">Reportes</li>
                  <li class="breadcrumb-item">Indicador Asesor</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-header">
                    <h5 class="card-title">EMBUDO VENTA: </h5>
                  </div>
                  <div class="card-body">
                    <!-- Dropdown selectors -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="coordinador" class="form-label">Coordinador</label>
                            <select class="form-select" id="coordinador" name="coordinador" <?php echo ($puesto === 'COORDINADOR') ? '' : 'disabled'; ?>>
                                <option value="">Seleccione un coordinador</option>
                                <?php
                                // Query para obtener coordinadores
                                $query_coord = "SELECT id, correo FROM empleado 
                                              WHERE puesto = 'COORDINADOR' 
                                              AND sucursal = '$sucursal'";
                                $result_coord = mysqli_query($con, $query_coord);
                                
                                while($row = mysqli_fetch_assoc($result_coord)) {
                                    $selected = ($puesto === 'COORDINADOR' && $row['id'] == $id_asesor) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' " . $selected . ">" . $row['correo'] . "</option>";
                                }
                                ?>
                                
                            </select>
                            <div class="mt-2">
                                <strong>Total Coordinador: </strong>
                                <span id="total_coordinador">0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="asesor" class="form-label">Asesor</label>
                            <select class="form-select" id="asesor" name="asesor">
                                <option value="">Seleccione un asesor</option>
                                <?php
                                $query_asesor = "SELECT id, correo FROM empleado
                                                WHERE puesto = 'ASESOR'              
                                                AND id_supervisor = '$supervisor'";
                                $result_asesor = mysqli_query($con, $query_asesor);
                                
                                while($row = mysqli_fetch_assoc($result_asesor)) {
                                    $selected = ($puesto === 'ASESOR' && $row['id'] == $id_asesor) ? 'selected' : '';
                                    echo "<option value='" . $row['id'] . "' " . $selected . ">" . $row['correo'] . "</option>";
                                }
                                ?>
                            </select>
                            <div class="mt-2">
                                <strong>Total Asesor: </strong>
                                <span id="total_asesor">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de clientes -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaClientes">
                            <thead>
                                <tr>
                                    <th>F.creado</th>
                                    <th>Atraso</th>
                                    <th>F.Seguimiento</th>
                                    <th>F.iniciado</th>
                                    <th>Nombre Cliente</th>
                                    <th>Venta</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                    <td id="total_venta">$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Gráfico de barras -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title">Comparación de Metas y Ventas</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:40vh; width:100%">
                                <canvas id="comparacionChart"></canvas>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

            <!-- Sección de Proyección -->
            <div class="card mt-3">
              <div class="card-header">
                <h5 class="card-title">EMBUDO PROYECCIÓN</h5>
              </div>
              <div class="card-body">
                <!-- Tabla de proyección -->
                <div class="table-responsive">
                  <table class="table table-bordered" id="tablaProyeccion">
                    <thead>
                      <tr>
                        <th>ID Cliente</th>
                        <th>Cliente</th>
                        <th>Etapa</th>
                        <th>Mes</th>
                        <th>Venta Embudo</th>
                        <th>Porcentaje</th>
                        <th>Valor Proyectado</th>
                        <th>Ver acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="5" class="text-end"><strong>Total Proyectado:</strong></td>
                        <td id="total_proyectado">$0.00</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <!-- Tabla de datos generales de proyección -->
                <div class="table-responsive mt-4">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Concepto</th>
                        <th>Valor</th>
                        <th>Porcentaje</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Meta Asesor</td>
                        <td id="meta_asesor_general">$0.00</td>
                        <td>100%</td>
                      </tr>
                      <tr>
                        <td>Ventas Actuales</td>
                        <td id="ventas_actuales_general">$0.00</td>
                        <td id="porcentaje_ventas">0%</td>
                      </tr>
                      <tr>
                        <td>Proyección</td>
                        <td id="proyeccion_general">$0.00</td>
                        <td id="porcentaje_proyeccion">0%</td>
                      </tr>
                      <tr>
                        <td>Prospección Faltante</td>
                        <td id="prospeccion_faltante_general">$0.00</td>
                        <td id="porcentaje_faltante">0%</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Etiquetas adicionales -->
                <div class="row mt-3">
                  <div class="col-md-4">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Productos</h6>
                        <p class="card-text" id="total_productos">$0.00</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Proyección</h6>
                        <p class="card-text" id="total_proyeccion">0%</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Prospección Faltante</h6>
                        <p class="card-text" id="prospeccion_faltante">$0.00</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- Container ends -->

        </div>
        <!-- App body ends -->

        <!-- App footer start -->
        <div class="app-footer">
          <div class="container">
            <span>© Bootstrap Gallery 2024</span>
          </div>
        </div>
        <!-- App footer end -->

      </div>
      <!-- App container ends -->

    </div>
    <!-- Page wrapper end -->

    <!-- *************
			************ JavaScript Files *************
		************* -->
    <!-- Required jQuery first, then Bootstrap Bundle JS -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- *************
			************ Vendor Js Files *************
		************* -->

    <!-- Overlay Scroll JS -->
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <!-- Add this JavaScript code before the closing body tag -->
    <script>
    $(document).ready(function() {
        let comparacionChart = null;
        let porcentajesEtapas = {};

        // Cargar los porcentajes de las etapas al inicio
        function cargarPorcentajesEtapas() {
            $.ajax({
                url: 'get_porcentajes_etapa.php',
                type: 'GET',
                success: function(response) {
                    porcentajesEtapas = response;
                    console.log('Porcentajes cargados:', porcentajesEtapas);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar porcentajes:', error);
                    // Usar valores por defecto si hay error
                    porcentajesEtapas = {
                        "BASE DE DATOS": 0,
                        "ACTIVAR": 0,
                        "ESTRECHAR": 0.25,
                        "EN PRONOSTICO": 0.7,
                        "CERRADO GANADO": 1,
                        "CERRADO PERDIDO": 0
                    };
                }
            });
        }

        // Cargar porcentajes al inicio
        cargarPorcentajesEtapas();

        // Si el usuario es COORDINADOR, cargar automáticamente sus asesores
        <?php if ($puesto === 'COORDINADOR'): ?>
            var coordinadorId = $('#coordinador').val();
            if(coordinadorId) {
                $.ajax({
                    url: 'get_asesores.php',
                    type: 'POST',
                    data: {coordinador_id: coordinadorId},
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#asesor').html(data.options);
                        updateTotals();
                    }
                });
            }
        <?php endif; ?>

        function formatCurrency(number) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        function actualizarGrafico(totalCoordinador, totalAsesor, totalVenta) {
            const ctx = document.getElementById('comparacionChart');
            
            // Destruir el gráfico anterior si existe
            if (comparacionChart) {
                comparacionChart.destroy();
            }

            // Calcular la diferencia entre la meta y las ventas
            const diferencia = totalAsesor - totalVenta;
            
            comparacionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Meta Coordinador', 'Meta Asesor', 'Ventas Actuales', 'Faltante'],
                    datasets: [{
                        label: 'Monto',
                        data: [totalCoordinador, totalAsesor, totalVenta, diferencia],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(255, 99, 132, 0.5)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatCurrency(context.raw);
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateTotals() {
            var coordinadorId = $('#coordinador').val();
            var asesorId = $('#asesor').val();
            var mes = '<?php echo $mes; ?>';

            if(coordinadorId) {
                $.ajax({
                    url: 'get_meta_coordinador.php',
                    type: 'POST',
                    data: {
                        coordinador_id: coordinadorId,
                        mes: mes
                    },
                    success: function(response) {
                        const totalCoordinador = parseFloat(response);
                        $('#total_coordinador').text(formatCurrency(totalCoordinador));
                        
                        // Actualizar gráfico si ya tenemos todos los datos
                        const totalAsesor = parseFloat($('#total_asesor').text().replace(/[^0-9.-]+/g, ''));
                        const totalVenta = parseFloat($('#total_venta').text().replace(/[^0-9.-]+/g, ''));
                        if (!isNaN(totalAsesor) && !isNaN(totalVenta)) {
                            actualizarGrafico(totalCoordinador, totalAsesor, totalVenta);
                        }
                    }
                });
            }
        }

        async function updateVentasActuales(){
           debugger;
            const id_asesor = $('#asesor').val();
            console.log("El id del asesor es: ", id_asesor);
            
            if(!id_asesor){
               console.log("No hay asesor seleccionado");
               $('#total_venta').text(formatCurrency(0));
               return;
            }
            debugger;
          try {
            const formData = new FormData();
            formData.append('asesor_id',id_asesor);

            const response = await fetch('get_venta.php',{
              method: 'POST',
              body: formData
            });

            if(!response.ok){
              throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = response.json();
            console.log("Respuesta completa: ",data);
            debugger;
            if (data.success) {
                 $('#total_venta').text(formatCurrency(parseFloat(data.venta)));

                 if(data.venta_faltante){
                  $('#venta_faltante').text(formatCurrency(parseFloat(data.venta_faltante)));
                 }

                 if(data.venta_pronostico){
                  $('#venta_pronostico').text(formatCurrency(parseFloat(data.venta_pronostico)))
                 }

                 const totalCoordinador = parseFloat($('#total_coordinador').text().replace(/[^0-9.-]+/g, '')) || 0;
                 
                 const totalAsesor = parseFloat($('#total_asesor').text().replace(/[^0-9.-]+/g, '')) || 0;
                
                 if (totalCoordinador > 0 && totalAsesor > 0) {
                      actualizarGrafico(totalCoordinador, totalAsesor, parseFloat(data.venta));
                  }
              }
            else{
              console.error("Error en la respuesta: ", data.message);
            }
          } catch (error) {
            console.error("Error en el fetch: ",error )
          }
        }
                

        function cargarClientes(asesorId) {
            if(asesorId) {
                $.ajax({
                    url: 'get_asesores.php',
                    type: 'POST',
                    data: {asesor_id: asesorId},
                    success: function(response) {
                        var data = JSON.parse(response);
                        var tbody = $('#tablaClientes tbody');
                        tbody.empty();
                        
                        data.clientes.forEach(function(cliente) {
                            tbody.append(`
                                <tr>
                                    <td>${cliente.fecha_creado}</td>
                                    <td>${cliente.atraso}</td>
                                    <td>${cliente.fecha_compromiso}</td>
                                    <td>${cliente.iniciado}</td>
                                    <td>${cliente.nombre_cliente}</td>
                                    <td>${formatCurrency(parseFloat(cliente.venta))}</td>
                                </tr>
                            `);
                        });
                        
                        $('#total_venta').text(formatCurrency(data.total_venta));
                        
                        // Actualizar gráfico si ya tenemos todos los datos
                        const totalCoordinador = parseFloat($('#total_coordinador').text().replace(/[^0-9.-]+/g, ''));
                        const totalAsesor = parseFloat($('#total_asesor').text().replace(/[^0-9.-]+/g, ''));
                        if (!isNaN(totalCoordinador) && !isNaN(totalAsesor)) {
                            actualizarGrafico(totalCoordinador, totalAsesor, data.total_venta);
                        }
                    }
                });
            } else {
                $('#tablaClientes tbody').empty();
                $('#total_venta').text(formatCurrency(0));
                if (comparacionChart) {
                    comparacionChart.destroy();
                    comparacionChart = null;
                }
            }
        }

        function actualizarProyeccion(asesorId) {
            if(asesorId) {
                $.ajax({
                    url: 'get_proyeccion.php',
                    type: 'POST',
                    data: {asesor_id: asesorId},
                    success: function(response) {
                        console.log('Respuesta de proyección:', response); // Para debugging
                        var data = JSON.parse(response);
                        var tbody = $('#tablaProyeccion tbody');
                        tbody.empty();
                        
                        let totalProyectado = 0;
                        const metaAsesor = parseFloat($('#total_asesor').text().replace(/[^0-9.-]+/g, ''));
                        
                        data.proyeccion.forEach(function(item) {
                            // Calcular el porcentaje según la etapa desde la base de datos
                            let porcentaje = 0;
                            if (porcentajesEtapas[item.etapa] !== undefined) {
                                porcentaje = porcentajesEtapas[item.etapa] * 100; // Convertir a porcentaje
                            }
                            
                            // Calcular el valor proyectado como porcentaje de la meta
                            const ventaEmbudo = parseFloat(item.venta_embudo);
                            const valorProyectado = metaAsesor > 0 ? (ventaEmbudo / metaAsesor) * 100 : 0;
                            totalProyectado += valorProyectado;
                            
                            tbody.append(`
                                <tr>
                                    <td>${item.id_cliente}</td>
                                    <td>${item.cliente}</td>
                                    <td>${item.etapa}</td>
                                    <td>${item.mes}</td>
                                    <td>${formatCurrency(ventaEmbudo)}</td>
                                    <td>${porcentaje}%</td>
                                    <td>${valorProyectado.toFixed(2)}%</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm ver-acciones" 
                                                data-cliente-id="${item.id_cliente}" 
                                                data-mes="${item.mes}">
                                            Ver acciones
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                        
                        $('#total_proyectado').text(totalProyectado.toFixed(2) + '%');
                        $('#total_productos').text(data.productos.toFixed(0));
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error);
                        console.error('Estado:', status);
                        console.error('Respuesta:', xhr.responseText);
                    }
                });
            } else {
                $('#tablaProyeccion tbody').empty();
                $('#total_proyectado').text('0%');
                $('#total_productos').text('0');
            }
        }

        $('#coordinador').change(function() {
            var coordinadorId = $(this).val();
            updateVentasActuales();
            console.log('Coordinador seleccionado:', coordinadorId);
            if(coordinadorId) {
                $.ajax({
                    url: 'get_asesores.php',
                    type: 'POST',
                    data: {coordinador_id: coordinadorId},
                    success: function(response) {
                        console.log('Respuesta de get_asesores.php:', response);
                        var data = JSON.parse(response);
                        $('#asesor').html(data.options);
                        $('#total_asesor').text(formatCurrency(0));
                        updateTotals();
                        $('#tablaClientes tbody').empty();
                        $('#total_venta').text(formatCurrency(0));
                        if (comparacionChart) {
                            comparacionChart.destroy();
                            comparacionChart = null;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error);
                        console.error('Estado:', status);
                        console.error('Respuesta:', xhr.responseText);
                    }
                });
            } else {
                $('#asesor').html('<option value="">Seleccione un asesor</option>');
                $('#total_coordinador').text(formatCurrency(0));
                $('#total_asesor').text(formatCurrency(0));
                $('#tablaClientes tbody').empty();
                $('#total_venta').text(formatCurrency(0));
                if (comparacionChart) {
                    comparacionChart.destroy();
                    comparacionChart = null;
                }
            }
        });

        $('#asesor').change(function() {
            var asesorId = $(this).val();
            updateVentasActuales();
            if(asesorId) {
                $.ajax({
                    url: 'get_asesores.php',
                    type: 'POST',
                    data: {coordinador_id: $('#coordinador').val()},
                    success: function(response) {
                        var data = JSON.parse(response);
                        if(data.asesores[asesorId]) {
                            $('#total_asesor').text(formatCurrency(parseFloat(data.asesores[asesorId])));
                        } else {
                            $('#total_asesor').text(formatCurrency(0));
                        }
                        cargarClientes(asesorId);
                        actualizarProyeccion(asesorId);
                    }
                });
            } else {
                $('#total_asesor').text(formatCurrency(0));
                $('#tablaClientes tbody').empty();
                $('#total_venta').text(formatCurrency(0));
                $('#tablaProyeccion tbody').empty();
                $('#total_proyectado').text(formatCurrency(0));
                $('#total_productos').text(formatCurrency(0));
                $('#total_proyeccion').text('0%');
                $('#prospeccion_faltante').text(formatCurrency(0));
                if (comparacionChart) {
                    comparacionChart.destroy();
                    comparacionChart = null;
                }
            }
        });

        // Agregar el manejador de eventos para el botón "Ver acciones"
        $(document).on('click', '.ver-acciones', function() {
            const clienteId = $(this).data('cliente-id');
            const mes = $(this).data('mes');
            
            $.ajax({
                url: 'get_acciones_cliente.php',
                type: 'POST',
                data: {
                    cliente_id: clienteId,
                    mes: mes
                },
                success: function(response) {
                    // Crear un modal para mostrar las acciones
                    const modal = `
                        <div class="modal fade" id="accionesModal" tabindex="-1" aria-labelledby="accionesModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="accionesModalLabel">Acciones del Cliente</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Actividad</th>
                                                        <th>Descripción</th>
                                                        <th>Estatus</th>
                                                        <th>Fuente de prospección</th>
                                                        <th>Notas</th>
                                                        <th>Fecha creado</th>
                                                        <th>Fecha modificado</th>
                                                        <th>Fecha Reprogramación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${response}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Agregar el modal al DOM
                    $('body').append(modal);
                    
                    // Mostrar el modal
                    const modalElement = new bootstrap.Modal(document.getElementById('accionesModal'));
                    modalElement.show();
                    
                    // Limpiar el modal cuando se cierre
                    $('#accionesModal').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                }
            });
        });
    });
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>