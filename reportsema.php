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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semanal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1, h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 20px;
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .grid-header {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        .tachometer {
            margin: 20px 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .tachometer-item {
            width: 45%;
            height: 200px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(4, 1fr);
            }
            .tachometer-item {
                width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <link rel="shortcut icon" href="assets/images/favicon.svg" />

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
                    <img src="assets/images/logo.svg" class="logo" alt="Bootstrap Gallery" />
                  </a>
                  <a href="index.html" class="d-lg-none d-md-block">
                    <img src="assets/images/logo-sm.svg" class="logo" alt="Bootstrap Gallery" />
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
                      <img src="assets/images/user2.png" class="rounded-2 img-3x" alt="Bootstrap Gallery" />
                      <div class="ms-2 text-truncate d-lg-block d-none text-white">
                        <span class="d-flex opacity-50 small">Admin</span>
                        <span>Taylor Franklin</span>
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
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-stacked_line_chart"></i> Dashboards
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="index.html">
                        <span>Analytics</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="reports.html">
                        <span>Reports</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-add_task"></i>Tickets
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="all-tickets.html">
                        <span>All Tickets</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="open-tickets.html"><span>Open Tickets</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="pending-tickets.html"><span>Pending Tickets</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="closed-tickets.html"><span>Closed Tickets</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="solved-tickets.html"><span>Solved Tickets</span></a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="clients.html"><i class="icon-supervised_user_circle"></i> Clients
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="agents.html">
                    <i class="icon-support_agent"></i>Agents
                  </a>
                </li>
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-package"></i>Pages
                  </a>
                  <ul class="dropdown-menu dropdown-megamenu">
                    <li>
                      <a class="dropdown-item" href="agent-profile.html">
                        <span>Agent Profile</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item current-page" href="starter-page.html">
                        <span>Starter Page</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="client-list.html">
                        <span>Client List</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="create-invoice.html">
                        <span>Create Invoice</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="invoice.html">
                        <span>Invoice Details</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="invoice-list.html">
                        <span>Invoice List</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="faq.html">
                        <span>FAQ</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="contact-us.html">
                        <span>Contact Us</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="notifications.html">
                        <span>Notifications</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="subscribers.html">
                        <span>Subscribers</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="placeholder.html">
                        <span>Placeholder</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="account-settings.html">
                        <span>Account Settings</span></a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-server"></i>UI Elements
                  </a>
                  <ul class="dropdown-menu dropdown-megamenu">
                    <li>
                      <a class="dropdown-item" href="accordions.html">
                        <span>Accordions</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="alerts.html">
                        <span>Alerts</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="buttons.html">
                        <span>Buttons</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="badges.html">
                        <span>Badges</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="cards.html">
                        <span>Cards</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="custom-cards.html">
                        <span>Custom Cards</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="carousel.html">
                        <span>Carousel</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="icons.html">
                        <span>Icons</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="list-items.html">
                        <span>List Items</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="modals.html">
                        <span>Modals</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="progress.html">
                        <span>Progress Bars</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="popovers.html">
                        <span>Popovers</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="tables.html">
                        <span>Tables</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="tabs.html">
                        <span>Tabs</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="tooltips.html">
                        <span>Tooltips</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="typography.html">
                        <span>Typography</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-drive_file_rename_outline"></i>Forms
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="form-inputs.html"><span>Basic Inputs</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="form-checkbox-radio.html"><span>Checkbox &amp; Radio</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="form-file-input.html"><span>File Input</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="form-validations.html"><span>Validations</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="form-layouts.html">Form Layouts</a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-margin"></i> Plugins
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="apex.html"><span>Apex Graphs</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="morris.html"><span>Morris Graphs</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="editor.html"><span>Editor</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="calendar.html"><span>Calendar Daygrid View</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="calendar-external-draggable.html"><span>Calendar External
                          Draggable</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="calendar-google.html"><span>Calendar Google</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="calendar-list-view.html"><span>Calendar List View</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="calendar-selectable.html"><span>Calendar Selectable</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="date-time-pickers.html"><span>Date Time Pickers</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="datatables.html"><span>Data Tables</span></a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="maps.html"><span>Maps</span></a>
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
                        <span>Login</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="signup.html">
                        <span>Signup</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="forgot-password.html">
                        <span>Forgot Password</span>
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
                    <a href="index.html" class="text-decoration-none">Home</a>
                  </li>
                  <li class="breadcrumb-item">Pages</li>
                  <li class="breadcrumb-item">Starter Page</li>
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
                    <!--<h5 class="card-title">Card Title</h5>-->
                    <h1>Reporte Semanal</h1>

<!-- Tacómetros -->
<div class="tachometer">
    <div class="tachometer-item">
        <canvas id="proyeccionDiaria"></canvas>
    </div>
    <div class="tachometer-item">
        <canvas id="ventaDiaria"></canvas>
    </div>
</div>

<!-- Ventas Futuras -->
<h2>Ventas Futuras</h2>
<div class="grid-container">
    <div class="grid-item grid-header">PLAZA</div>
    <div class="grid-item grid-header">META</div>
    <div class="grid-item grid-header">VENTA</div>
    <div class="grid-item grid-header">AV %</div>
    <div class="grid-item grid-header">EMBUDO</div>
    <div class="grid-item grid-header">META ACUM</div>
    <div class="grid-item grid-header">VENTA ACUM</div>
    <div class="grid-item grid-header">AVANCE ACUMULADO</div>
    
    <!-- Datos de ejemplo - Serán reemplazados por datos dinámicos -->
    <div class="grid-item">Plaza 2</div>
    <div class="grid-item">1200</div>
    <div class="grid-item">900</div>
    <div class="grid-item">75%</div>
    <div class="grid-item">300</div>
    <div class="grid-item">6000</div>
    <div class="grid-item">4500</div>
    <div class="grid-item">70%</div>
</div>

<!-- Ventas Periféricos -->
<h2>Ventas Periféricos</h2>
<div class="grid-container">
    <div class="grid-item grid-header">PLAZA</div>
    <div class="grid-item grid-header">META</div>
    <div class="grid-item grid-header">VENTA</div>
    <div class="grid-item grid-header">AV %</div>
    <div class="grid-item grid-header">EMBUDO</div>
    <div class="grid-item grid-header">META ACUM</div>
    <div class="grid-item grid-header">VENTA ACUM</div>
    <div class="grid-item grid-header">AVANCE ACUMULADO</div>
    
    <!-- Datos de ejemplo - Serán reemplazados por datos dinámicos -->
    <div class="grid-item">Plaza 3</div>
    <div class="grid-item">1100</div>
    <div class="grid-item">850</div>
    <div class="grid-item">77%</div>
    <div class="grid-item">250</div>
    <div class="grid-item">5500</div>
    <div class="grid-item">4200</div>
    <div class="grid-item">76%</div>
</div>
                  </div>
                  <div class="card-body">
                    <!-- Your code goes here -->
                    
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
  </body>
  <script>
    // Example data for tachometer charts
    const data = {
        labels: ['Progress'],
        datasets: [{
            data: [75, 25],
            backgroundColor: ['#4caf50', '#e0e0e0'],
            borderWidth: 0
        }]
    };

    const options = {
        circumference: Math.PI,
        rotation: Math.PI,
        cutout: '80%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.raw + '%';
                    }
                }
            }
        }
    };

    // Create the tachometer charts
    const ctx1 = document.getElementById('proyeccionDiaria').getContext('2d');
    const ctx2 = document.getElementById('ventaDiaria').getContext('2d');

    // Add labels to the charts
    const proyeccionChart = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Progress'],
            datasets: [{
                data: [75, 25],
                backgroundColor: ['#4caf50', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: {
            ...options,
            plugins: {
                ...options.plugins,
                title: {
                    display: true,
                    text: 'Proyección Diaria',
                    position: 'bottom',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    const ventaChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Progress'],
            datasets: [{
                data: [80, 20],
                backgroundColor: ['#2196f3', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: {
            ...options,
            plugins: {
                ...options.plugins,
                title: {
                    display: true,
                    text: 'Venta Diaria',
                    position: 'bottom',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    // Add percentage text in the center of the charts
    function addCenterText(chart, text) {
        Chart.register({
            id: 'centerTextPlugin',
            afterDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                
                ctx.restore();
                const fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";
                
                const text = chart.data.datasets[0].data[0] + "%";
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;
                
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        });
    }
    
    addCenterText(proyeccionChart);
    addCenterText(ventaChart);
</script>

<?php if (!$acceso): ?>
<?php echo generarScriptDeshabilitarElementos(); ?>
<?php endif; ?>
</html>