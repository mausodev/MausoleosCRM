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
                    <h5 class="card-title">Card Title</h5>
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
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>