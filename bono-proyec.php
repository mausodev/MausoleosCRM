<?php
require './controlador/conexion.php';
require './controlador/access_control.php';

// Verificar acceso y obtener datos de sesión
$accessData = verificarAcceso();
$acceso = $accessData['acceso'];
$id_usuario = $accessData['id_asesor'];
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
                        <span><?php echo htmlspecialchars($correo); ?></span>
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
                      <a class="dropdown-item " href="controlventa.php">
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
                      <a class="dropdown-item current-page" href="bono-proyec.php">
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
                  <li class="breadcrumb-item">Bono Proyeccion</li>
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
                    <h5 class="card-title">Mes: <?php echo $mes; ?></h5>
                  </div>
                  <div class="card-body">
                    <!-- Your code goes here -->
                    <h2>Reporte: Bono Proyección</h2>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tabla_reporte">
                        <thead>
                          <tr>
                            <th>Coordinador</th>
                            <th>APSI Coord</th>
                            <th>Asesor</th>
                            <th>APSI Asesor</th>
                            <th>Venta</th>
                            <th>Venta Embudo</th>
                            <th>Proyección</th>
                            <th>Avance asesor</th>
                            <th>Meta</th>
                            <th>Fecha Consulta</th>
                            <th>Mes</th>
                            <th>Plaza</th>
                            <th>Meta Coordinador</th>
                            <th>Proyección Cord</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>

                    <p id="total_registros" class="mt-3"></p>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

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

    <!-- *************
			************ Vendor Js Files *************
		************* -->

    <!-- Overlay Scroll JS -->
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <style>
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
        }
        .dataTables_wrapper .dataTables_length {
            float: none;
            text-align: left;
        }
        .table-responsive {
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .dataTables_wrapper .dataTables_info {
            padding-top: 0;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: 12px 8px;
        }
        .table tbody tr.coordinador-group {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .table tbody tr.coordinador-group td {
            border-top: 2px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
        }
        .card-title {
            margin: 0;
            color: #333;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        #total_registros {
            font-weight: 500;
            color: #666;
        }
        @media (max-width: 768px) {
            .table-responsive {
                margin: 10px 0;
            }
            .table td, .table th {
                padding: 8px 6px;
                font-size: 14px;
            }
            h2 {
                font-size: 24px;
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            let dataTable;

            function cargarDatos() {
                $.post('get_data.php?accion=consultar', function (data) {
                    let filas = '';
                    let coordinadorActual = '';
                    let sumaEtapas = 0;
                    let venta = 0;
                    let venta_embudo = 0;
                    let meta = 0;

                    // Agrupar datos por coordinador para calcular proyección total
                    let coordinadoresData = {};
                    
                    data.datos.forEach(function (fila) {
                        let coord = fila.coordinador;
                        if (!coordinadoresData[coord]) {
                            coordinadoresData[coord] = {
                                totalVenta: 0,
                                totalVentaEmbudo: 0,
                                metaCoordinador: parseFloat(fila.meta_coordinador || 0)
                            };
                        }
                        coordinadoresData[coord].totalVenta += parseFloat(fila.venta || 0);
                        coordinadoresData[coord].totalVentaEmbudo += parseFloat(fila.proyeccion || 0);
                    });

                    data.datos.forEach(function (fila, index) {
                        // Obtener los valores directamente de la fila
                        venta = parseFloat(fila.venta || 0);
                        venta_embudo = parseFloat(fila.proyeccion || 0); // Este es el valor de venta_embudo sumado por asesor
                        meta = parseFloat(fila.meta || 0);
                        
                        // Calcular el porcentaje de proyección según la nueva fórmula
                        let proyeccion = 0;
                        if (meta > 0) {
                            proyeccion = ((venta + venta_embudo) / meta) * 100 ;
                        }
                        let avance = 0;
                        if(venta > 0){
                          avance = (venta / meta) * 100 ;
                        }
                        // Calcular proyección del coordinador
                        let proyeccionCoord = 0;
                        let coordData = coordinadoresData[fila.coordinador];
                        if (coordData && coordData.metaCoordinador > 0) {
                            proyeccionCoord = ((coordData.totalVenta + coordData.totalVentaEmbudo) / coordData.metaCoordinador) * 100;
                        }
                        
                        if (fila.coordinador !== coordinadorActual) {
                            filas += `<tr class="coordinador-group">
                                <td>${fila.coordinador}</td>
                                <td>${fila.apsi_coordinador || ''}</td>
                                <td>${fila.asesor}</td>
                                <td>${fila.apsi_asesor || ''}</td>
                                <td>${venta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${venta_embudo.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${proyeccion.toFixed(2)}%</td>
                                <td>${avance.toFixed(2)}%</td>
                                <td>${meta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${fila.fecha}</td>
                                <td>${fila.mes}</td>
                                <td>${fila.plaza}</td>
                                <td>${fila.meta_coordinador}</td>
                                <td>${proyeccionCoord.toFixed(2)}%</td>
                            </tr>`;
                        } else {
                            filas += `<tr>
                                <td>${fila.coordinador}</td>
                                <td>${fila.apsi_coordinador || ''}</td>
                                <td>${fila.asesor}</td>
                                <td>${fila.apsi_asesor || ''}</td>
                                <td>${venta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${venta_embudo.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${proyeccion.toFixed(2)}%</td>
                                <td>${avance.toFixed(2)}%</td>
                                <td>${meta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>${fila.fecha}</td>
                                <td>${fila.mes}</td>
                                <td>${fila.plaza}</td>
                                <td>${fila.meta_coordinador}</td>
                                <td>${proyeccionCoord.toFixed(2)}%</td>
                            </tr>`;
                        }
                        coordinadorActual = fila.coordinador;
                    });
                    
                    $('#tabla_reporte tbody').html(filas);
                    
                    if (dataTable) {
                        dataTable.destroy();
                    }
                    
                    dataTable = $('#tabla_reporte').DataTable({
                        responsive: true,
                        fixedHeader: true,
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                             '<"row"<"col-sm-12"tr>>' +
                             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>' +
                             '<"row"<"col-sm-12"B>>',
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                        },
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'Exportar a Excel',
                                className: 'btn btn-primary',
                                title: 'Reporte Bono Proyección',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                                },
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    $('row c[r^="A"]', sheet).attr('s', '2');
                                }
                            }
                        ],
                        pageLength: 25,
                        order: [[0, 'asc']],
                        drawCallback: function() {
                            // Mantener el estilo de los grupos de coordinadores después de la paginación
                            $('tr.coordinador-group').css('background-color', '#e9ecef');
                        },
                        columnDefs: [
                            { 
                                targets: [4, 5, 6, 7, 8, 12], // Columnas de venta, venta embudo, proyección, meta, fecha, meta coordinador, proyección coord
                                className: 'text-end'
                            }
                        ]
                    });
                    
                    $('#total_registros').text(`Total registros: ${data.total}`);
                }, 'json');
            }

            cargarDatos();
        });
    </script>
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>