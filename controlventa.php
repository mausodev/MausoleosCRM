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

     // Check if a month is selected
     if (isset($_POST['month']) && !empty($_POST['month'])) {
       $mes = $_POST['month'];
     } else {
       // Default to current month from calendario table
       $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
       $result = $con->query($sqlCierre);

       if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         $mes = $row['mes']; 
       } else {
         $mes = 'N/A';
       }
     }
    

     $query = "WITH presupuestos AS (
                      SELECT 
                          id_cordinador, 
                          SUM(meta) AS ppto_mes
                      FROM meta_venta 
                      WHERE nombre_mes = '$mes'
                      GROUP BY id_cordinador
                  ),
                  pronostico_hoy AS (
                      SELECT 
                          e.id_supervisor,
                          SUM(c.venta_embudo)  AS pronostico_hoy_total
                      FROM cliente c
                      INNER JOIN empleado e ON c.asesor = e.id
                      WHERE c.fecha_cierre = CURDATE()
                      GROUP BY e.id_supervisor
                  )
                  SELECT      
                      e.sucursal AS plaza,     
                      e.supervisor AS nombre_supervisor,     
                      SUM(CASE WHEN c.etapa = 'EN PRONOSTICO' THEN c.venta_embudo ELSE 0 END)  AS pronostico,     
                      SUM(CASE WHEN c.etapa = 'ESTRECHAR' THEN c.venta_embudo ELSE 0 END) AS estrechar,     
                      SUM(CASE WHEN c.etapa = 'ACTIVAR' THEN c.venta_embudo ELSE 0 END)  AS activar,     
                      (SUM(CASE WHEN c.etapa = 'EN PRONOSTICO' THEN c.venta_embudo ELSE 0 END) +      
                      SUM(CASE WHEN c.etapa = 'ESTRECHAR' THEN c.venta_embudo ELSE 0 END) +      
                      SUM(CASE WHEN c.etapa = 'ACTIVAR' THEN c.venta_embudo ELSE 0 END)) AS total_embudo,     
                      SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_real ELSE 0 END) / 1.16 AS venta,     
                      COALESCE(p.ppto_mes, 0) AS ppto_mes,     
                      COALESCE(p.ppto_mes, 0) - SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_real ELSE 0 END) / 1.16 AS venta_faltante,     
                      (SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_real ELSE 0 END) / 1.16) / NULLIF(COALESCE(p.ppto_mes, 0), 0) * 100 AS avance_porcentaje,     
                      (((SUM(CASE WHEN c.etapa = 'EN PRONOSTICO' THEN c.venta_embudo ELSE 0 END) +        
                        SUM(CASE WHEN c.etapa = 'ESTRECHAR' THEN c.venta_embudo ELSE 0 END) +        
                        SUM(CASE WHEN c.etapa = 'ACTIVAR' THEN c.venta_embudo ELSE 0 END))  +       
                        SUM(CASE WHEN c.etapa = 'CERRADO GANADO' THEN c.venta_real ELSE 0 END) / 1.16) /        
                        NULLIF(COALESCE(p.ppto_mes, 0), 0) * 1) AS proyeccion,
                      COALESCE(ph.pronostico_hoy_total, 0) AS pronostico_hoy,
                      (COALESCE(ph.pronostico_hoy_total, 0) / NULLIF(COALESCE(p.ppto_mes, 0), 0)) * 100 AS avance_hoy
                  FROM cliente c 
                  INNER JOIN empleado e ON c.asesor = e.id 
                  LEFT JOIN presupuestos p ON p.id_cordinador = e.id_supervisor
                  LEFT JOIN pronostico_hoy ph ON ph.id_supervisor = e.id_supervisor
                  WHERE e.puesto = 'ASESOR' AND c.mes = '$mes' AND e.sucursal = '$sucursal'
                  GROUP BY e.sucursal, e.supervisor, e.id_supervisor, p.ppto_mes, ph.pronostico_hoy_total;";
                  $stmt = $con->prepare($query);
                  $stmt->execute();
                  $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                  // Calculate totals manually
                  $totalRow = array();
                  $totalRow['plaza'] = 'TOTAL';
                  $totalRow['nombre_supervisor'] = null;
                  $totalRow['pronostico'] = 0;
                  $totalRow['estrechar'] = 0;
                  $totalRow['activar'] = 0;
                  $totalRow['total_embudo'] = 0;
                  $totalRow['venta'] = 0;
                  $totalRow['ppto_mes'] = 0;
                  $totalRow['venta_faltante'] = 0;
                  $totalRow['avance_porcentaje'] = 0;
                  $totalRow['proyeccion'] = 0;
                  $totalRow['pronostico_hoy'] = 0;
                  $totalRow['avance_hoy'] = 0;
                  
                  foreach ($ventas as $venta) {
                      $totalRow['pronostico'] += $venta['pronostico'];
                      $totalRow['estrechar'] += $venta['estrechar'];
                      $totalRow['activar'] += $venta['activar'];
                      $totalRow['total_embudo'] += $venta['total_embudo'];
                      $totalRow['venta'] += $venta['venta'];
                      $totalRow['ppto_mes'] += $venta['ppto_mes'];
                      $totalRow['pronostico_hoy'] += $venta['pronostico_hoy'];
                  }
                  
                  // Calculate derived totals correctly
                  $totalRow['venta_faltante'] = $totalRow['ppto_mes'] - $totalRow['venta'];
                  $totalRow['avance_porcentaje'] = ($totalRow['ppto_mes'] > 0) ? ($totalRow['venta'] / $totalRow['ppto_mes']) * 100 : 0;
                  $totalRow['proyeccion'] = ($totalRow['ppto_mes'] > 0) ? (($totalRow['total_embudo'] + $totalRow['venta']) / $totalRow['ppto_mes']) * 100 : 0;
                  $totalRow['avance_hoy'] = ($totalRow['ppto_mes'] > 0) ? ($totalRow['pronostico_hoy'] / $totalRow['ppto_mes']) * 100 : 0;

     ?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bootstrap Gallery - Support Admin Dashboard</title>

    <!-- Meta -->
    <meta name="description" content="Marketplace for Bootstrap Admin Dashboards" />
    <meta name="author" content="Bootstrap Gallery" />
    <link rel="canonical" href="https://www.bootstrap.gallery/">
    <meta property="og:url" content="https://www.bootstrap.gallery">
    <meta property="og:title" content="Admin Templates - Dashboard Templates | Bootstrap Gallery">
    <meta property="og:description" content="Marketplace for Bootstrap Admin Dashboards">
    <meta property="og:type" content="Website">
    <meta property="og:site_name" content="Bootstrap Gallery">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Estilos personalizados para la tabla -->
    <style>
        /* Estilos para mejorar la cuadrícula de la tabla */
        .table-custom {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .table-custom th,
        .table-custom td {
            border: 2px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
            font-size: 0.875rem;
        }
        
        .table-custom th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-bottom: 3px solid #6c757d;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table-custom tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table-custom tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.2s ease;
        }
        
        /* Fila de totales destacada */
        .table-custom tbody tr:last-child {
            background-color: #e3f2fd;
            font-weight: 600;
            border-top: 3px solid #2196f3;
        }
        
        .table-custom tbody tr:last-child td {
            border-top: 2px solid #2196f3;
        }
        
        /* Responsividad mejorada para móviles */
        .table-responsive-custom {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        
        /* Estilos para dispositivos móviles */
        @media (max-width: 768px) {
            .table-custom th,
            .table-custom td {
                padding: 8px 4px;
                font-size: 0.75rem;
                min-width: 80px;
            }
            
            .table-custom th:first-child,
            .table-custom td:first-child {
                position: sticky;
                left: 0;
                background-color: #fff;
                z-index: 5;
                border-right: 2px solid #6c757d;
            }
            
            .table-custom th:nth-child(2),
            .table-custom td:nth-child(2) {
                position: sticky;
                left: 80px;
                background-color: #fff;
                z-index: 5;
                border-right: 2px solid #6c757d;
            }
            
            .table-responsive-custom {
                max-height: 60vh;
            }
            
            /* Ocultar columnas menos importantes en móviles */
            .table-custom th:nth-child(n+7),
            .table-custom td:nth-child(n+7) {
                min-width: 70px;
            }
        }
        
        /* Estilos para pantallas muy pequeñas */
        @media (max-width: 576px) {
            .table-custom th,
            .table-custom td {
                padding: 6px 3px;
                font-size: 0.7rem;
                min-width: 60px;
            }
            
            .table-custom th:nth-child(2),
            .table-custom td:nth-child(2) {
                left: 60px;
            }
        }
        
        /* Indicador de scroll horizontal */
        .table-scroll-indicator {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            margin: 10px 0;
            display: none;
        }
        
        @media (max-width: 768px) {
            .table-scroll-indicator {
                display: block;
            }
        }
        
        /* Mejoras en el hover de las filas */
        .table-custom tbody tr:hover td {
            background-color: #e3f2fd !important;
            border-color: #2196f3 !important;
            transition: all 0.3s ease;
        }
        
        /* Estilos para números y porcentajes */
        .table-custom td:nth-child(n+3):nth-child(-n+6),
        .table-custom td:nth-child(7),
        .table-custom td:nth-child(8),
        .table-custom td:nth-child(9),
        .table-custom td:nth-child(11) {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        
        /* Estilos para porcentajes */
        .table-custom td:nth-child(10),
        .table-custom td:nth-child(12) {
            font-weight: 700;
            color: #2196f3;
        }
        
        /* Estilos para la fila de totales */
        .table-custom tbody tr:last-child td {
            font-weight: 700;
            color: #1976d2;
        }
        
        /* Botón de volver al inicio */
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1000;
            display: none;
        }
        
        .scroll-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .scroll-to-top.show {
            display: block;
        }
        
        /* Mejoras en el responsive */
        @media (max-width: 480px) {
            .month-selector {
                padding: 15px;
            }
            
            .month-selector h5 {
                font-size: 1.1rem;
            }
            
            .table-custom th,
            .table-custom td {
                padding: 4px 2px;
                font-size: 0.65rem;
                min-width: 50px;
            }
        }
        
        /* Mejoras en el selector de mes */
        .month-selector {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
            margin-bottom: 20px;
        }
        
        .month-selector label {
            font-weight: 600;
            margin-right: 10px;
        }
        
        .month-selector select {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            margin-right: 10px;
            font-size: 14px;
        }
        
        .month-selector button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .month-selector button:hover {
            background-color: #218838;
        }
        
        /* Mejoras en las tarjetas de métricas */
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .metric-card .card-title {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .metric-card .fs-4 {
            font-size: 2rem !important;
            font-weight: 700;
        }
        
        .metric-card small {
            color: rgba(255,255,255,0.9);
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
                        <span class="count">!</span>
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
                              <h6 class="mb-1 fw-semibold">Informativo</h6>
                              <p class="mb-1 text-secondary">
                                Total Ventas mes $.
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
                              <h6 class="mb-1 fw-semibold">Meta Faltalte</h6>
                              <p class="mb-2">$ 0.0 .</p>
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
                              <h6 class="mb-1 fw-semibold">Venta pronostico cierre</h6>
                              <p class="mb-2">$ 0.0.</p>
                              <p class="small m-0 text-secondary">3 days ago</p>
                            </div>
                          </div>
                        </div>
                        <div class="d-grid mx-3 my-1">
                          <a href="javascript:void(0)" class="btn btn-outline-primary">Ver todas</a>
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
                            <img src="assets/images/GrupoMausoleos.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
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
                            <img src="assets/images/GrupoMausoleos.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
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
                            <img src="assets/images/GrupoMausoleos.png" class="img-3x me-3 rounded-5" alt="Admin Theme" />
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
                        <a href="login.php" class="btn btn-outline-danger">Salir</a>
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
                <a class="nav-link dropdown-toggle" href="clients.php" >
                    <i class="icon-supervised_user_circle"></i> Cliente
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
                  <li class="breadcrumb-item">Plaza</li>
                  <li class="breadcrumb-item">Plaza Venta</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body">
                  <div class="month-selector">
                      <h5 class="card-title mb-3">EMBUDO / VENTA MES: <?php echo htmlspecialchars($mes); ?></h5> 
                      <div class="d-flex flex-wrap align-items-center gap-3">
                        <form method="POST" action="controlventa.php" class="d-flex align-items-center gap-2">
                          <label for="monthSelect" class="mb-0">Seleccionar Mes:</label>
                          <select id="monthSelect" name="month" class="form-select">
                            <option value="ENERO" <?php echo ($mes == 'ENERO') ? 'selected' : ''; ?>>Enero</option>
                            <option value="FEBRERO" <?php echo ($mes == 'FEBRERO') ? 'selected' : ''; ?>>Febrero</option>
                            <option value="MARZO" <?php echo ($mes == 'MARZO') ? 'selected' : ''; ?>>Marzo</option>
                            <option value="ABRIL" <?php echo ($mes == 'ABRIL') ? 'selected' : ''; ?>>Abril</option>
                            <option value="MAYO" <?php echo ($mes == 'MAYO') ? 'selected' : ''; ?>>Mayo</option>
                            <option value="JUNIO" <?php echo ($mes == 'JUNIO') ? 'selected' : ''; ?>>Junio</option>
                            <option value="JULIO" <?php echo ($mes == 'JULIO') ? 'selected' : ''; ?>>Julio</option>
                            <option value="AGOSTO" <?php echo ($mes == 'AGOSTO') ? 'selected' : ''; ?>>Agosto</option>
                            <option value="SEPTIEMBRE" <?php echo ($mes == 'SEPTIEMBRE') ? 'selected' : ''; ?>>Septiembre</option>
                            <option value="OCTUBRE" <?php echo ($mes == 'OCTUBRE') ? 'selected' : ''; ?>>Octubre</option>
                            <option value="NOVIEMBRE" <?php echo ($mes == 'NOVIEMBRE') ? 'selected' : ''; ?>>Noviembre</option>
                            <option value="DICIEMBRE" <?php echo ($mes == 'DICIEMBRE') ? 'selected' : ''; ?>>Diciembre</option>
                          </select>
                          <button type="submit" id="queryButton" class="btn btn-success">Consultar</button>
                        </form>
                      </div>
                    </div>
                    <div class="table-scroll-indicator">
                      📱 Desliza horizontalmente para ver todas las columnas
                    </div>
                    <div class="table-outer">
                      <div class="table-responsive-custom">
                        <table class="table-custom" name="embudo">
                          <thead>
                          <tr>
                            <th><strong>PLAZA</strong></th>
                            <th><strong>COORDINACION</strong></th>
                            <th><strong>PRONOSTICO</strong></th>
                            <th><strong>ESTRECHAR</strong></th>
                            <th><strong>ACTIVAR</strong></th>
                            <th><strong>TOTAL EMBUDO</strong></th>
                            <th><strong>VENTA</strong></th>
                            <th><strong>VENTA FALTANTE</strong></th>
                            <th><strong>PTO.MES</strong></th>
                            <th><strong>AVANCE</strong></th>
                            <th><strong>PROYECCION</strong></th>
                            <th><strong>PRONOSTICO HOY</strong></th>
                            <th><strong>AVANCE HOY</strong></th>
                             </tr>
                          </thead>
                          <tbody>
                            <?php
                            foreach ($ventas as $venta) {
                            ?>
                            <tr>
                              <td><?php echo htmlspecialchars($venta['plaza'] ?? 'N/A'); ?></td>
                              <td><?php echo htmlspecialchars($venta['nombre_supervisor'] ?? 'N/A'); ?></td>
                              <td><?php echo '$' . number_format($venta['pronostico'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['estrechar'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['activar'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['total_embudo'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['venta'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['venta_faltante'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($venta['ppto_mes'] ?? 0, 2); ?></td>
                              <td><?php echo number_format($venta['avance_porcentaje'] ?? 0, 2) . '%'; ?></td>
                              <td><?php echo number_format(($venta['proyeccion'] * 100) ?? 0, 2) . '%'; ?></td>
                              <td><?php echo '$' . number_format($venta['pronostico_hoy'] ?? 0, 2); ?></td>
                              <td><?php echo number_format($venta['avance_hoy'] ?? 0, 2) . '%'; ?></td>
                            </tr>
                            <?php } ?>
                            <!-- Fila de Totales -->
                            <tr>
                              <td colspan="2" class="text-end fw-bold">Total:</td>
                              <td><?php echo '$' . number_format($totalRow['pronostico'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['estrechar'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['activar'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['total_embudo'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['venta'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['venta_faltante'] ?? 0, 2); ?></td>
                              <td><?php echo '$' . number_format($totalRow['ppto_mes'] ?? 0, 2); ?></td>
                              <td><?php echo number_format($totalRow['avance_porcentaje'] ?? 0, 2) . '%'; ?></td>
                              <td><?php echo number_format(($totalRow['proyeccion'] * 100) ?? 0, 2) . '%'; ?></td>
                              <td><?php echo '$' . number_format($totalRow['pronostico_hoy'] ?? 0, 2); ?></td>
                              <td><?php echo number_format($totalRow['avance_hoy'] ?? 0, 2) . '%'; ?></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->
            <div class="row gx-3">
              <div class="col-md-6">
                <div class="metric-card">
                  <h5 class="card-title">Avance Plaza</h5>
                  <div class="d-flex align-items-center">
                    <div class="fs-4 fw-bold">
                      <?php 
                        $avance_plaza = ($totalRow['ppto_mes'] > 0) ? ($totalRow['venta'] / $totalRow['ppto_mes']) * 100 : 0;
                        echo number_format($avance_plaza, 2) . '%';
                      ?>
                    </div>
                    <div class="ms-2 fw-bold d-flex">
                      <i class="icon-trending-up fs-4 me-1"></i>
                    </div>
                  </div>
                  <small>Porcentaje de avance total de la plaza</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="metric-card">
                  <h5 class="card-title">Proyeccion Plaza</h5>
                  <div class="d-flex align-items-center">
                    <div class="fs-4 fw-bold">
                      <?php 
                       /* $proyeccion_plaza = (($totalRow['total_embudo'] + $totalRow['venta']) / $totalRow['ppto_mes']) * 100;
                        echo number_format($proyeccion_plaza, 2) . '%';*/
                       $proyeccion_plaza = ($totalRow['ppto_mes'] > 0) ? (($totalRow['total_embudo'] + $totalRow['venta']) / $totalRow['ppto_mes']) * 100 : 0;
                        $proyeccion_redondeada = ceil($proyeccion_plaza); // Redondea hacia arriba si .5 o más
                        echo $proyeccion_redondeada . '%';
                     ?>
                    </div>
                    <div class="ms-2 fw-bold d-flex">
                      <i class="icon-trending-up fs-4 me-1"></i>
                    </div>
                  </div>
                  <small>Proyeccion total de la plaza</small>
                </div>
              </div>
            </div>
            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Porcentaje por cordinacion</h5>
                  </div>
                  <div class="card-body">
                    <div>
                        <label for="supervisorSelect">Seleccionar Supervisor:</label>
                        <select id="supervisorSelect">
                            <option value="">--Seleccione un Supervisor--</option>
                            <?php foreach ($ventas as $venta): ?>
                                <option value="<?php echo htmlspecialchars($venta['nombre_supervisor']); ?>">
                                    <?php echo htmlspecialchars($venta['nombre_supervisor']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="paymentsData">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="m-0">
                      <div class="d-flex align-items-center">
                        <div class="fs-4 fw-bold"></div>
                        <div class="ms-2 text-primary fw-bold d-flex">
                          <i class="icon-trending-up fs-4 me-1"></i>
                        </div>
                      </div>
                      <small class="text-dark">Porcentaje venta</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
          
            <!-- Row end -->

          </div>
          <!-- Container ends -->

        </div>
        <!-- App body ends -->

        <!-- Botón de volver al inicio -->
        <button class="scroll-to-top" id="scrollToTop" title="Volver al inicio">↑</button>
        
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

    <script>
        // Prepare data for the chart
        const supervisors = <?php echo json_encode(array_column($ventas, 'nombre_supervisor')); ?>;
        const ventas = <?php echo json_encode(array_column($ventas, 'venta')); ?>;
        const ventasFaltantes = <?php echo json_encode(array_column($ventas, 'venta_faltante')); ?>;

        console.log(supervisors);
        console.log(ventas);
        console.log(ventasFaltantes);

        // Create the initial chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        let salesChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Ventas', 'Ventas Faltantes'],
                datasets: [{
                    label: 'Porcentaje de Ventas',
                    data: [0, 0], // Initial data
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Color for sales
                        'rgba(255, 99, 132, 0.6)'  // Color for missing sales
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Porcentaje de Ventas vs Ventas Faltantes por Supervisor'
                    }
                }
            }
        });

        // Function to update the chart based on selected supervisor
        document.getElementById('supervisorSelect').addEventListener('change', function() {
            const selectedSupervisor = this.value;
            console.log("Selected Supervisor:", selectedSupervisor);
            const index = supervisors.indexOf(selectedSupervisor);

            if (index !== -1) {
                const selectedVentas = ventas[index];
                const selectedVentasFaltantes = ventasFaltantes[index];

                // Update chart data
                salesChart.data.datasets[0].data = [selectedVentas, selectedVentasFaltantes];
                salesChart.update();
            } else {
                // Reset chart if no supervisor is selected
                salesChart.data.datasets[0].data = [0, 0];
                salesChart.update();
            }
        });
        
        // Control del botón de volver al inicio
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Mejorar la experiencia de scroll en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const tableContainer = document.querySelector('.table-responsive-custom');
            if (tableContainer) {
                tableContainer.addEventListener('scroll', function() {
                    // Agregar indicador visual de scroll
                    if (this.scrollLeft > 0) {
                        this.style.boxShadow = 'inset 5px 0 10px -5px rgba(0,0,0,0.1)';
                    } else {
                        this.style.boxShadow = 'none';
                    }
                });
            }
        });
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>