<?php
require './controlador/conexion.php';
require './controlador/access_control.php';
session_start();

$rutaActual = basename($_SERVER['PHP_SELF']);


if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit();
}
// Acceder a los datos de la sesión
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


// Conectar a la base de datos y obtener los datos de los leds
$query = "SELECT id, name, email, telefono, campaign_name, created_at, status, plaza, comentarios,
                 asesor, fecha_prospectado, etapa_lead, monto_venta, producto, 
                 fecha_seguimiento, nombre_campana, medio_campana
          FROM leads 
          ORDER BY created_at DESC";
$stmt = $con->prepare($query);
$stmt->execute();
$leds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portal Mausoleos</title>

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
  </head>

  <body>
  <?php if (!$acceso): ?>
    <?php echo generarOverlayAccesoDenegado(); ?>
    <?php endif; ?>
    <!-- Page wrapper start -->
    <div class="page-wrapper">

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
                  <a href="#" class="d-lg-block d-none">
                    <img src="assets/images/GrupoMausoleos.png" class="logo" alt="Bootstrap Gallery" />
                  </a>
                  <a href="#" class="d-lg-none d-md-block">
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
                        <a class="dropdown-item" href="#"><i
                            class="icon-user border border-primary text-primary"></i>Perfil</a>
                        <a class="dropdown-item" href="#"><i
                            class="icon-settings border border-danger text-danger"></i>Configurar</a>
                        <a class="dropdown-item" href="#"><i
                            class="icon-box border border-info text-info"></i>Ajustes</a>
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
               <!-- <li class="nav-item dropdown">
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
                </li>-->
                <?php if (tienePermiso($id_Rol, 'clients.php')): ?>
                <li class="nav-item">
                  <a class="nav-link" href="clients.php"><i class="icon-supervised_user_circle"></i> Cliente
                  </a>
                </li>
                <?php endif; ?>
                <?php if (tienePermiso($id_Rol, 'agents.php')): ?>
                <li class="nav-item">
                  <a class="nav-link" href="agents.php">
                    <i class="icon-support_agent"></i>Seguimiento
                  </a>
                </li>
                <?php endif; ?>
                <?php if (tienePermiso($id_Rol, 'leds.php')): ?>
                <li class="nav-item active-link">
                  <a class="nav-link" href="leds.php">
                    <i class="fs-3 icon-contacts"></i>Leads Digitales
                  </a>
                </li>
                <?php endif; ?>
                <?php if (tienePermiso($id_Rol, 'account-settings.php')): ?>
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
                <?php endif; ?>
                <?php if (tienePermiso($id_Rol, 'login.php')): ?>
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
                        <span>Bitacora</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="forgot-password.html">
                        <span>Cambiar contraseña</span>
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
                <?php endif; ?>
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
                    <a href="#" class="text-decoration-none">Inicio</a>
                  </li>
                  <li class="breadcrumb-item">Seguimiento embudo</li>
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
                    <!-- Header with Add Campaign Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <h4 class="mb-0">Campañas Digitales</h4>
                        <small class="text-muted">Gestión de leads y campañas</small>
                      </div>
                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#campaignModal">
                          <i class="icon-plus"></i> Nueva Campaña
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#campaignCrudModal">
                          <i class="icon-settings"></i> Gestionar Campañas
                        </button>
                      </div>
                    </div>
                    
                    <!-- View Toggle Buttons -->
                    <div class="mb-3">
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="tableViewBtn" onclick="toggleView('table')">
                          <i class="icon-table"></i> Vista Tabla
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="canvasViewBtn" onclick="toggleView('canvas')">
                          <i class="icon-grid"></i> Vista Canvas
                        </button>
                      </div>
                    </div>

                    <!-- Canvas View -->
                    <div id="canvasView" style="display: none;">
                      <div class="row g-3" id="campaignsCanvas">
                        <!-- Campaign cards will be loaded here -->
                      </div>
                    </div>

                    <!-- Table View -->
                    <div id="tableView">
                      <!-- Total Records and Sales -->
                      <div class="mb-3">
                        <div class="row">
                          <div class="col-md-4">
                            <strong>Total de Registros: </strong><span id="totalRecords"><?php echo count($leds); ?></span>
                          </div>
                          <div class="col-md-4">
                            <strong>Total Ventas: </strong><span id="totalVentas">$<?php echo number_format($totalVentas, 2); ?></span>
                          </div>
                          <div class="col-md-4">
                            <strong>Total Embudo: </strong><span id="totalEmbudo">$<?php echo number_format($totalEmbudo, 2); ?></span>
                          </div>
                        </div>
                      </div>
                      <!-- Botón para exportar a XLS -->
                      <div class="mb-3">
                        <button id="exportButton" class="btn btn-primary" onclick="exportToExcel()">Exportar a XLS</button>
                      </div>
                    <!-- Filters -->
                    <div class="mb-3">
                      <input type="text" id="filterInput" class="form-control" placeholder="Filtrar por " onkeyup="filterTable()" />
                    </div>
                    <div class="table-outer">
                      <div class="table-responsive">
                        <table class="table table-striped align-middle m-0" id="ledsTable">
                          <thead>
                            <tr>
                              <th>Name</th>
                              <th>Email</th>
                              <th>Phone</th>
                              <th>Campaign Name</th>
                              <th>Asesor</th>
                              <th>Etapa Lead</th>
                              <th>Monto Venta</th>
                              <th>Producto</th>
                              <th>Fecha Prospectado</th>
                              <th>Fecha Seguimiento</th>
                              <th>Nombre Campaña</th>
                              <th>Medio Campaña</th>
                              <th>Status</th>
                              <th>Acciones</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
                              $totalVentas = 0; // Inicializar variable para total de ventas
                              $totalEmbudo = 0; // Inicializar variable para total embudo
                              foreach ($leds as $led): 
                                $totalVentas += $led['monto_venta'] ?? 0; // Sumar ventas
                                $totalEmbudo += $led['monto_venta'] ?? 0; // Sumar ventas embudo
                                
                                // Get asesor email
                                $asesorEmail = 'N/A';
                                if (!empty($led['asesor'])) {
                                    $asesorQuery = "SELECT correo FROM empleado WHERE id = ?";
                                    $asesorStmt = $con->prepare($asesorQuery);
                                    $asesorStmt->bind_param("i", $led['asesor']);
                                    $asesorStmt->execute();
                                    $asesorResult = $asesorStmt->get_result();
                                    if ($asesorRow = $asesorResult->fetch_assoc()) {
                                        $asesorEmail = $asesorRow['correo'];
                                    }
                                    $asesorStmt->close();
                                }
                            ?>
                              <tr>
                                <td><?php echo htmlspecialchars($led['name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['telefono'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['campaign_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($asesorEmail); ?></td>
                                <td>
                                  <span class="badge bg-<?php echo $led['etapa_lead'] === 'Cierre' ? 'success' : ($led['etapa_lead'] === 'Perdido' ? 'danger' : 'primary'); ?>">
                                    <?php echo htmlspecialchars($led['etapa_lead'] ?? 'N/A'); ?>
                                  </span>
                                </td>
                                <td><?php echo $led['monto_venta'] ? '$' . number_format($led['monto_venta'], 2) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($led['producto'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['fecha_prospectado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['fecha_seguimiento'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['nombre_campana'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($led['medio_campana'] ?? 'N/A'); ?></td>
                                <td>
                                  <span class="badge bg-<?php echo $led['status'] === 'Activo' ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars($led['status'] ?? 'N/A'); ?>
                                  </span>
                                </td>
                                <td>
                                  <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editLead(<?php echo $led['id']; ?>)" title="Editar">
                                      <i class="icon-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteLead(<?php echo $led['id']; ?>)" title="Eliminar">
                                      <i class="icon-trash"></i>
                                    </button>
                                  </div>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                            <script>
                              // Actualizar el total de ventas y embudo en las etiquetas
                              const totalVentasEl = document.getElementById('totalVentas');
                              const totalEmbudoEl = document.getElementById('totalEmbudo');
                              if (totalVentasEl) totalVentasEl.innerText = '$<?php echo number_format($totalVentas, 2); ?>';
                              if (totalEmbudoEl) totalEmbudoEl.innerText = '$<?php echo number_format($totalEmbudo, 2); ?>';

                              // Función para filtrar la tabla
                              function filterTable() {
                                const input = document.getElementById('filterInput');
                                const filter = input.value.toLowerCase();
                                const table = document.getElementById('ledsTable');
                                const tr = table.getElementsByTagName('tr');
                                let filteredEmbudoTotal = 0; // Inicializar total embudo filtrado
                                let filteredVentasTotal = 0; // Inicializar total ventas filtrado
                                let visibleCount = 0; // Contador de registros visibles

                                for (let i = 1; i < tr.length; i++) { // Comenzar desde 1 para omitir el encabezado
                                  const td = tr[i].getElementsByTagName('td');
                                  let rowVisible = false;

                                  if (filter === "") { // Si el filtro está vacío, mostrar todas las filas
                                    tr[i].style.display = ""; // Mostrar fila
                                    visibleCount++; // Contar registros visibles
                                    // Sumar el valor de monto_venta para todas las filas visibles
                                    const montoVentaText = td[6].innerText.replace(/[$,]/g, ''); // Columna 6 es monto_venta
                                    filteredVentasTotal += parseFloat(montoVentaText) || 0;
                                    filteredEmbudoTotal += parseFloat(montoVentaText) || 0;
                                    continue; // Saltar al siguiente ciclo
                                  }

                                  for (let j = 0; j < td.length; j++) {
                                    if (td[j]) {
                                      const txtValue = td[j].textContent || td[j].innerText;
                                      if (txtValue.toLowerCase().indexOf(filter) > -1) {
                                        rowVisible = true;
                                        break;
                                      }
                                    }
                                  }
                                  tr[i].style.display = rowVisible ? "" : "none"; // Mostrar u ocultar la fila
                                  if (rowVisible) {
                                    visibleCount++; // Contar registros visibles
                                    // Sumar el valor de monto_venta si la fila es visible
                                    const montoVentaText = td[6].innerText.replace(/[$,]/g, ''); // Columna 6 es monto_venta
                                    filteredVentasTotal += parseFloat(montoVentaText) || 0;
                                    filteredEmbudoTotal += parseFloat(montoVentaText) || 0;
                                  }
                                }
                                const totalEmbudoEl = document.getElementById('totalEmbudo');
                                const totalRecordsEl = document.getElementById('totalRecords');
                                const totalVentasEl = document.getElementById('totalVentas');
                                
                                if (totalEmbudoEl) totalEmbudoEl.innerText = '$' + filteredEmbudoTotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                if (totalRecordsEl) totalRecordsEl.innerText = visibleCount;
                                if (totalVentasEl) totalVentasEl.innerText = '$' + filteredVentasTotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                              }

                              // Función para exportar a Excel
                              function exportToExcel() {
                                const table = document.getElementById('ledsTable');
                                const rows = Array.from(table.rows);
                                const filteredRows = rows.filter(row => row.style.display !== "none"); // Filtrar filas visibles
                                let csvContent = "data:text/csv;charset=utf-8,";

                                filteredRows.forEach(row => {
                                  const cols = Array.from(row.querySelectorAll('td, th'));
                                  const rowData = cols.map(col => col.innerText).join(",");
                                  csvContent += rowData + "\r\n"; // Agregar nueva línea
                                });

                                const encodedUri = encodeURI(csvContent);
                                const link = document.createElement("a");
                                link.setAttribute("href", encodedUri);
                                link.setAttribute("download", "leds_filtrados.csv");
                                document.body.appendChild(link);
                                link.click(); // Simular clic para descargar
                                document.body.removeChild(link); // Limpiar el DOM
                              }

                              // Load asesores and productos dropdown based on selected plaza
                              function loadAsesores(plaza) {
                                console.log('=== LOAD ASESORES FUNCTION CALLED ===');
                                console.log('Loading asesores and productos for plaza:', plaza);
                                
                                if (!plaza) {
                                  console.log('No plaza selected, clearing dropdowns');
                                  const asesorSelect = document.getElementById('asesor');
                                  const productoSelect = document.getElementById('producto');
                                  if (asesorSelect) {
                                    asesorSelect.innerHTML = '<option value="">Seleccionar asesor...</option>';
                                  }
                                  if (productoSelect) {
                                    productoSelect.innerHTML = '<option value="">Seleccionar producto...</option>';
                                  }
                                  return;
                                }
                                
                                console.log('Plaza selected, proceeding to load data...');
                                
                                // Load asesores
                                console.log('Fetching asesores from: controlador/get_asesores.php?plaza=' + plaza);
                                fetch(`controlador/get_asesores.php?plaza=${plaza}`)
                                  .then(response => {
                                    console.log('Asesores response status:', response.status);
                                    console.log('Asesores response headers:', response.headers);
                                    if (!response.ok) {
                                      throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                  })
                                  .then(data => {
                                    console.log('Asesores data received:', data);
                                    console.log('Data type:', typeof data);
                                    console.log('Is array:', Array.isArray(data));
                                    console.log('Data length:', data ? data.length : 'null/undefined');
                                    
                                    const select = document.getElementById('asesor');
                                    console.log('Asesor select element found:', !!select);
                                    
                                    if (select) {
                                      select.innerHTML = '<option value="">Seleccionar asesor...</option>';
                                      
                                      if (Array.isArray(data) && data.length > 0) {
                                        console.log('Adding asesores to dropdown...');
                                        data.forEach((asesor, index) => {
                                          console.log(`Adding asesor ${index + 1}:`, asesor);
                                          const option = document.createElement('option');
                                          option.value = asesor.id;
                                          option.textContent = asesor.correo;
                                          select.appendChild(option);
                                        });
                                        console.log('Total asesores added:', data.length);
                                      } else {
                                        console.log('No asesores found for plaza:', plaza);
                                        select.innerHTML = '<option value="">No hay asesores disponibles</option>';
                                      }
                                    } else {
                                      console.error('Asesor select element not found!');
                                    }
                                  })
                                  .catch(error => {
                                    console.error('Error loading asesores:', error);
                                    const select = document.getElementById('asesor');
                                    if (select) {
                                      select.innerHTML = '<option value="">Error al cargar asesores</option>';
                                    }
                                  });
                                  
                                // Load productos
                                fetch(`controlador/get_productos.php?plaza=${plaza}`)
                                  .then(response => {
                                    console.log('Productos response status:', response.status);
                                    if (!response.ok) {
                                      throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                  })
                                  .then(data => {
                                    console.log('Productos data received:', data);
                                    const select = document.getElementById('producto');
                                    select.innerHTML = '<option value="">Seleccionar producto...</option>';
                                    
                                    if (Array.isArray(data) && data.length > 0) {
                                      data.forEach(producto => {
                                        const option = document.createElement('option');
                                        option.value = producto.nombre;
                                        option.textContent = producto.nombre;
                                        select.appendChild(option);
                                      });
                                    } else {
                                      console.log('No productos found for plaza:', plaza);
                                      select.innerHTML = '<option value="">No hay productos disponibles</option>';
                                    }
                                  })
                                  .catch(error => {
                                    console.error('Error loading productos:', error);
                                    const select = document.getElementById('producto');
                                    select.innerHTML = '<option value="">Error al cargar productos</option>';
                                  });
                              }

                              // View toggle functionality
                              function toggleView(view) {
                                const tableView = document.getElementById('tableView');
                                const canvasView = document.getElementById('canvasView');
                                const tableViewBtn = document.getElementById('tableViewBtn');
                                const canvasViewBtn = document.getElementById('canvasViewBtn');
                                
                                if (view === 'table') {
                                  tableView.style.display = 'block';
                                  canvasView.style.display = 'none';
                                  tableViewBtn.classList.add('active');
                                  canvasViewBtn.classList.remove('active');
                                } else {
                                  tableView.style.display = 'none';
                                  canvasView.style.display = 'block';
                                  canvasViewBtn.classList.add('active');
                                  tableViewBtn.classList.remove('active');
                                  loadCanvasView();
                                }
                              }

                              // Update save function to handle both create and update
                              function saveCampaign() {
                                const form = document.getElementById('campaignForm');
                                const formData = new FormData(form);
                                
                                // Determine action based on whether leadId exists
                                const leadId = document.getElementById('leadId').value;
                                formData.append('action', leadId ? 'update' : 'save');
                                
                                // Set current date for fecha_creado if not set
                                if (!formData.get('fecha_creado')) {
                                  formData.set('fecha_creado', new Date().toISOString().split('T')[0]);
                                }
                                
                                // Debug: Log form data
                                console.log('Form data being sent:');
                                for (let [key, value] of formData.entries()) {
                                  console.log(key, value);
                                }
                                
                                fetch('controlador/save_lead.php', {
                                  method: 'POST',
                                  body: formData
                                })
                                .then(response => {
                                  console.log('Response status:', response.status);
                                  console.log('Response headers:', response.headers);
                                  
                                  if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                  }
                                  
                                  return response.text().then(text => {
                                    console.log('Raw response:', text);
                                    try {
                                      return JSON.parse(text);
                                    } catch (e) {
                                      console.error('JSON parse error:', e);
                                      console.error('Response text:', text);
                                      throw new Error('Invalid JSON response from server. Server returned: ' + text.substring(0, 200));
                                    }
                                  });
                                })
                                .then(data => {
                                  console.log('Parsed data:', data);
                                  if (data.success) {
                                    alert(leadId ? 'Lead actualizado exitosamente' : 'Lead guardado exitosamente');
                                    location.reload();
                                  } else {
                                    alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                                  }
                                })
                                .catch(error => {
                                  console.error('Error details:', error);
                                  alert('Error al guardar la campaña: ' + error.message);
                                });
                              }

                              // Edit campaign
                              function editCampaign(campaignName) {
                                // Implementation for editing campaign
                                console.log('Edit campaign:', campaignName);
                              }

                              // Add lead to campaign
                              function addLeadToCampaign(campaignName) {
                                // Clear form and set campaign name
                                document.getElementById('campaignForm').reset();
                                document.getElementById('campaignModalLabel').textContent = 'Agregar Lead a: ' + campaignName;
                                document.getElementById('campaignModal').style.display = 'block';
                                new bootstrap.Modal(document.getElementById('campaignModal')).show();
                              }

                              // Edit lead
                              function editLead(leadId) {
                                // Fetch lead data and populate form
                                fetch(`controlador/get_lead.php?id=${leadId}`)
                                  .then(response => response.json())
                                  .then(data => {
                                    if (data.success) {
                                      const lead = data.lead;
                                      document.getElementById('leadId').value = lead.id;
                                      document.getElementById('asesor').value = lead.asesor;
                                      document.getElementById('fechaProspectado').value = lead.fecha_prospectado;
                                      document.getElementById('etapaLead').value = lead.etapa_lead;
                                      document.getElementById('montoVenta').value = lead.monto_venta;
                                      document.getElementById('producto').value = lead.producto;
                                      document.getElementById('fechaSeguimiento').value = lead.fecha_seguimiento;
                                      document.getElementById('nombreCampana').value = lead.nombre_campana || '';
                                      document.getElementById('medioCampana').value = lead.medio_campana;
                                      document.getElementById('comentarios').value = lead.comentarios;
                                      
                                      document.getElementById('campaignModalLabel').textContent = 'Editar Lead';
                                      new bootstrap.Modal(document.getElementById('campaignModal')).show();
                                    } else {
                                      alert('Error al cargar los datos del lead');
                                    }
                                  })
                                  .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error al cargar los datos del lead');
                                  });
                              }

                              // Delete lead
                              function deleteLead(leadId) {
                                if (confirm('¿Estás seguro de que quieres eliminar este lead?')) {
                                  const formData = new FormData();
                                  formData.append('action', 'delete');
                                  formData.append('leadId', leadId);
                                  
                                  fetch('controlador/save_lead.php', {
                                    method: 'POST',
                                    body: formData
                                  })
                                  .then(response => response.json())
                                  .then(data => {
                                    if (data.success) {
                                      alert('Lead eliminado exitosamente');
                                      location.reload();
                                    } else {
                                      alert('Error al eliminar: ' + data.message);
                                    }
                                  })
                                  .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error al eliminar el lead');
                                  });
                                }
                              }

                              // Load campaigns for dropdown
                              function loadCampaignsForDropdown() {
                                console.log('=== LOAD CAMPAIGNS FOR DROPDOWN ===');
                                console.log('Loading campaigns for dropdown...');
                                
                                const select = document.getElementById('nombreCampana');
                                console.log('Campaign select element found:', !!select);
                                
                                if (!select) {
                                  console.error('Campaign select element not found!');
                                  return;
                                }
                                
                                loadCampaignsData(select);
                              }
                              
                              function loadCampaignsData(select) {
                                console.log('Fetching campaigns data...');
                                
                                fetch('controlador/campaign_crud.php?action=read')
                                  .then(response => {
                                    console.log('Campaigns response status:', response.status);
                                    return response.json();
                                  })
                                  .then(data => {
                                    console.log('Campaigns data received:', data);
                                    console.log('Data success:', data.success);
                                    console.log('Campaigns array:', data.campaigns);
                                    
                                    if (data.success) {
                                      select.innerHTML = '<option value="">Seleccionar campaña...</option>';
                                      
                                      if (data.campaigns && data.campaigns.length > 0) {
                                        console.log('Adding campaigns to dropdown...');
                                        data.campaigns.forEach((campaign, index) => {
                                          console.log(`Adding campaign ${index + 1}:`, campaign);
                                          const option = document.createElement('option');
                                          option.value = campaign.nombre_campana;
                                          option.textContent = campaign.nombre_campana;
                                          select.appendChild(option);
                                        });
                                        console.log('Total campaigns added:', data.campaigns.length);
                                      } else {
                                        console.log('No campaigns found');
                                        select.innerHTML = '<option value="">No hay campañas disponibles</option>';
                                      }
                                    } else {
                                      console.error('Error loading campaigns:', data.message);
                                      select.innerHTML = '<option value="">Error al cargar campañas</option>';
                                    }
                                  })
                                  .catch(error => {
                                    console.error('Error loading campaigns:', error);
                                    select.innerHTML = '<option value="">Error al cargar campañas</option>';
                                  });
                              }
                            </script>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    </div>
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
            <span>© Portal mausoleos 2025</span>
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

    <!-- Rating -->
    <script src="assets/vendor/rating/raty.js"></script>
    <script src="assets/vendor/rating/raty-custom.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <!-- Campaign CRUD Modal -->
    <div class="modal fade" id="campaignModal" tabindex="-1" aria-labelledby="campaignModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="campaignModalLabel">Nueva Campaña</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="campaignForm">
              <input type="hidden" id="leadId" name="leadId">
              
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="plaza" class="form-label">Plaza *</label>
                  <select class="form-select" id="plaza" name="plaza" required onchange="loadAsesores(this.value)">
                    <option value="">Seleccionar plaza...</option>
                    <option value="CUAUHTEMOC">CUAUHTEMOC</option>
                    <option value="DELICIAS">DELICIAS</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="asesor" class="form-label">Asesor *</label>
                  <select class="form-select" id="asesor" name="asesor" required>
                    <option value="">Seleccionar asesor...</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="nombre_cliente" class="form-label">Nombre Cliente *</label>
                  <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required placeholder="Nombre del cliente">
                </div>
                
                <div class="col-md-6">
                  <label for="apellidos" class="form-label">Apellidos *</label>
                  <input type="text" class="form-control" id="apellidos" name="apellidos" required placeholder="Apellidos del cliente">
                </div>
                
                <div class="col-md-6">
                  <label for="fecha_creado" class="form-label">Fecha Creado *</label>
                  <input type="date" class="form-control" id="fecha_creado" name="fecha_creado" required>
                </div>
                
                <div class="col-md-6">
                  <label for="creado_por" class="form-label">Creado Por</label>
                  <input type="text" class="form-control" id="creado_por" name="creado_por" readonly value="<?php echo $_SESSION['correo'] ?? ''; ?>">
                </div>
                
                <div class="col-md-6">
                  <label for="fechaProspectado" class="form-label">Fecha Prospectado *</label>
                  <input type="date" class="form-control" id="fechaProspectado" name="fechaProspectado" required>
                </div>
                
                <div class="col-md-6">
                  <label for="etapaLead" class="form-label">Etapa Lead *</label>
                  <select class="form-select" id="etapaLead" name="etapaLead" required>
                    <option value="">Seleccionar etapa (SIN ASIGNAR)</option>
                    <option value="CANALIZADO">CANALIZADO</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="montoVenta" class="form-label">Monto Venta</label>
                  <input type="number" class="form-control" id="montoVenta" name="montoVenta" step="0.01" min="0">
                </div>
                
                <div class="col-md-6">
                  <label for="producto" class="form-label">Producto</label>
                  <select class="form-select" id="producto" name="producto">
                    <option value="">Seleccionar producto...</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="fechaSeguimiento" class="form-label">Fecha Seguimiento</label>
                  <input type="date" class="form-control" id="fechaSeguimiento" name="fechaSeguimiento">
                </div>
                
                <div class="col-md-6">
                  <label for="nombreCampana" class="form-label">Nombre Campaña</label>
                  <select class="form-select" id="nombreCampana" name="nombreCampana">
                    <option value="">Seleccionar campaña...</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="medioCampana" class="form-label">Medio Campaña</label>
                  <select class="form-select" id="medioCampana" name="medioCampana">
                    <option value="">Seleccionar medio...</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Instagram">Instagram</option>
                    <option value="Google Ads">Google Ads</option>
                    <option value="Email">Email</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Teléfono">Teléfono</option>
                    <option value="Referido">Referido</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                
                <div class="col-12">
                  <label for="comentarios" class="form-label">Comentarios</label>
                  <textarea class="form-control" id="comentarios" name="comentarios" rows="3" placeholder="Comentarios adicionales..."></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="saveCampaign()">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Campaign CRUD Modal -->
    <div class="modal fade" id="campaignCrudModal" tabindex="-1" aria-labelledby="campaignCrudModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="campaignCrudModalLabel">Gestión de Campañas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Campaign Form -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h6 class="mb-0">Formulario de Campaña</h6>
                  </div>
                  <div class="card-body">
                    <form id="campaignCrudForm">
                      <input type="hidden" id="campaignId" name="campaignId">
                      
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label for="nombre_campana" class="form-label">Nombre de Campaña *</label>
                          <input type="text" class="form-control" id="nombre_campana" name="nombre_campana" required placeholder="Nombre de la campaña">
                        </div>
                        
                        <div class="col-md-6">
                          <label for="inversion" class="form-label">Inversión *</label>
                          <input type="number" class="form-control" id="inversion" name="inversion" required min="0" placeholder="Monto de inversión">
                        </div>
                        
                        <div class="col-md-6">
                          <label for="gasto" class="form-label">Gasto</label>
                          <input type="number" class="form-control" id="gasto" name="gasto" step="0.01" min="0" placeholder="Gasto realizado">
                        </div>
                        
                        <div class="col-md-6">
                          <label for="total_generados" class="form-label">Total Generados</label>
                          <input type="number" class="form-control" id="total_generados" name="total_generados" min="0" placeholder="Total de leads generados">
                        </div>
                        
                        <div class="col-md-6">
                          <label for="plaza_campana" class="form-label">Plaza</label>
                          <select class="form-select" id="plaza_campana" name="plaza_campana">
                            <option value="">Seleccionar plaza...</option>
                            <option value="CUAUHTEMOC">CUAUHTEMOC</option>
                            <option value="DELICIAS">DELICIAS</option>
                          </select>
                        </div>
                        
                        <div class="col-md-6">
                          <label for="modelo" class="form-label">Modelo</label>
                          <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Modelo de campaña">
                        </div>
                        
                        <div class="col-12">
                          <label for="medio_campana_crud" class="form-label">Medio de Campaña</label>
                          <select class="form-select" id="medio_campana_crud" name="medio_campana_crud">
                            <option value="">Seleccionar medio...</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Google Ads">Google Ads</option>
                            <option value="Email">Email</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Teléfono">Teléfono</option>
                            <option value="Referido">Referido</option>
                            <option value="Otro">Otro</option>
                          </select>
                        </div>
                      </div>
                    </form>
                  </div>
                  <div class="card-footer">
                    <button type="button" class="btn btn-primary" onclick="saveCampaignCrud()">
                      <i class="icon-save"></i> Guardar Campaña
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearCampaignForm()">
                      <i class="icon-clear"></i> Limpiar
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Campaigns Table -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Campañas Existentes</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadCampaigns()">
                      <i class="icon-refresh"></i> Actualizar
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle" id="campaignsTable">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Nombre Campaña</th>
                            <th>Inversión</th>
                            <th>Gasto</th>
                            <th>Total Generados</th>
                            <th>Plaza</th>
                            <th>Modelo</th>
                            <th>Medio</th>
                            <th>Acciones</th>
                          </tr>
                        </thead>
                        <tbody id="campaignsTableBody">
                          <!-- Campaign data will be loaded here -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <script>

      // Load canvas view with campaign cards
      function loadCanvasView() {
        const canvas = document.getElementById('campaignsCanvas');
        canvas.innerHTML = '';
        
        // Group leads by campaign
        const campaigns = {};
        <?php foreach ($leds as $led): ?>
          const campaignName = '<?php echo addslashes($led['campaign_name'] ?? 'Sin Campaña'); ?>';
          if (!campaigns[campaignName]) {
            campaigns[campaignName] = [];
          }
          campaigns[campaignName].push({
            id: <?php echo $led['id']; ?>,
            name: '<?php echo addslashes($led['name'] ?? ''); ?>',
            email: '<?php echo addslashes($led['email'] ?? ''); ?>',
            phone: '<?php echo addslashes($led['telefono'] ?? ''); ?>',
            status: '<?php echo addslashes($led['status'] ?? ''); ?>',
            created_at: '<?php echo addslashes($led['created_at'] ?? ''); ?>',
            plaza: '<?php echo addslashes($led['plaza'] ?? ''); ?>',
            comentarios: '<?php echo addslashes($led['comentarios'] ?? ''); ?>',
            asesor: '<?php echo addslashes($led['asesor'] ?? ''); ?>',
            etapa_lead: '<?php echo addslashes($led['etapa_lead'] ?? ''); ?>',
            monto_venta: <?php echo $led['monto_venta'] ?? 0; ?>,
            producto: '<?php echo addslashes($led['producto'] ?? ''); ?>',
            fecha_prospectado: '<?php echo addslashes($led['fecha_prospectado'] ?? ''); ?>',
            fecha_seguimiento: '<?php echo addslashes($led['fecha_seguimiento'] ?? ''); ?>',
            medio_campana: '<?php echo addslashes($led['medio_campana'] ?? ''); ?>'
          });
        <?php endforeach; ?>
        
        // Create campaign cards
        Object.keys(campaigns).forEach(campaignName => {
          const campaign = campaigns[campaignName];
          const card = document.createElement('div');
          card.className = 'col-md-6 col-lg-4';
          card.innerHTML = `
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">${campaignName}</h6>
                <span class="badge bg-primary">${campaign.length} leads</span>
              </div>
              <div class="card-body">
                <div class="leads-list" style="max-height: 300px; overflow-y: auto;">
                  ${campaign.map(lead => `
                    <div class="lead-item border-bottom py-2">
                      <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                          <strong>${lead.name}</strong>
                          <div class="small text-muted">${lead.email}</div>
                          <div class="small text-muted">${lead.phone}</div>
                          ${lead.producto ? `<div class="small text-info">${lead.producto}</div>` : ''}
                          ${lead.monto_venta > 0 ? `<div class="small text-success">$${lead.monto_venta.toLocaleString()}</div>` : ''}
                        </div>
                        <div class="text-end">
                          <span class="badge bg-${lead.status === 'Activo' ? 'success' : 'secondary'} mb-1">${lead.status}</span><br>
                          <span class="badge bg-${lead.etapa_lead === 'Cierre' ? 'success' : (lead.etapa_lead === 'Perdido' ? 'danger' : 'primary')}">${lead.etapa_lead}</span>
                        </div>
                      </div>
                      <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="editLead(${lead.id})" title="Editar">
                          <i class="icon-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLead(${lead.id})" title="Eliminar">
                          <i class="icon-trash"></i>
                        </button>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>
              <div class="card-footer">
                <button class="btn btn-sm btn-outline-primary" onclick="editCampaign('${campaignName}')">
                  <i class="icon-edit"></i> Editar
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="addLeadToCampaign('${campaignName}')">
                  <i class="icon-plus"></i> Agregar Lead
                </button>
              </div>
            </div>
          `;
          canvas.appendChild(card);
        });
      }





      // Campaign CRUD Functions
      function loadCampaigns() {
        console.log('Loading campaigns...');
        fetch('controlador/campaign_crud.php?action=read')
          .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text().then(text => {
              console.log('Raw response:', text);
              try {
                return JSON.parse(text);
              } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response: ' + text.substring(0, 200));
              }
            });
          })
          .then(data => {
            console.log('Parsed data:', data);
            if (data.success) {
              displayCampaigns(data.campaigns);
            } else {
              console.error('Error loading campaigns:', data.message);
              alert('Error al cargar las campañas: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar las campañas: ' + error.message);
          });
      }

      function displayCampaigns(campaigns) {
        const tbody = document.getElementById('campaignsTableBody');
        tbody.innerHTML = '';
        
        if (campaigns.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay campañas registradas</td></tr>';
          return;
        }
        
        campaigns.forEach(campaign => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${campaign.id}</td>
            <td>${campaign.nombre_campana}</td>
            <td>$${campaign.inversion.toLocaleString()}</td>
            <td>${campaign.gasto ? '$' + parseFloat(campaign.gasto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/A'}</td>
            <td>${campaign.total_generados || 'N/A'}</td>
            <td>${campaign.plaza || 'N/A'}</td>
            <td>${campaign.modelo || 'N/A'}</td>
            <td>${campaign.medio_campaña || 'N/A'}</td>
            <td>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="editCampaignCrud(${campaign.id})" title="Editar">
                  <i class="icon-edit"></i>
                </button>
                <button class="btn btn-outline-danger" onclick="deleteCampaignCrud(${campaign.id})" title="Eliminar">
                  <i class="icon-trash"></i>
                </button>
              </div>
            </td>
          `;
          tbody.appendChild(row);
        });
      }

      function saveCampaignCrud() {
        const form = document.getElementById('campaignCrudForm');
        const formData = new FormData(form);
        
        const campaignId = document.getElementById('campaignId').value;
        const action = campaignId ? 'update' : 'create';
        formData.append('action', action);
        
        console.log('Saving campaign with action:', action);
        console.log('Form data:');
        for (let [key, value] of formData.entries()) {
          console.log(key, value);
        }
        
        fetch('controlador/campaign_crud.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          console.log('Save response status:', response.status);
          return response.text().then(text => {
            console.log('Save raw response:', text);
            try {
              return JSON.parse(text);
            } catch (e) {
              console.error('Save JSON parse error:', e);
              console.error('Save response text:', text);
              throw new Error('Invalid JSON response: ' + text.substring(0, 200));
            }
          });
        })
        .then(data => {
          console.log('Save parsed data:', data);
          if (data.success) {
            alert(campaignId ? 'Campaña actualizada exitosamente' : 'Campaña creada exitosamente');
            clearCampaignForm();
            loadCampaigns();
            loadCampaignsForDropdown();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Save error:', error);
          alert('Error al guardar la campaña: ' + error.message);
        });
      }

      function editCampaignCrud(campaignId) {
        // Buscar la campaña en la tabla actual
        const tbody = document.getElementById('campaignsTableBody');
        const rows = tbody.getElementsByTagName('tr');
        
        for (let row of rows) {
          const cells = row.getElementsByTagName('td');
          if (cells.length > 0 && cells[0].textContent == campaignId) {
            // Llenar el formulario con los datos de la campaña
            document.getElementById('campaignId').value = campaignId;
            document.getElementById('nombre_campana').value = cells[1].textContent;
            document.getElementById('inversion').value = cells[2].textContent.replace(/[$,]/g, '');
            document.getElementById('gasto').value = cells[3].textContent === 'N/A' ? '' : cells[3].textContent.replace(/[$,]/g, '');
            document.getElementById('total_generados').value = cells[4].textContent === 'N/A' ? '' : cells[4].textContent;
            document.getElementById('plaza_campana').value = cells[5].textContent === 'N/A' ? '' : cells[5].textContent;
            document.getElementById('modelo').value = cells[6].textContent === 'N/A' ? '' : cells[6].textContent;
            document.getElementById('medio_campana_crud').value = cells[7].textContent === 'N/A' ? '' : cells[7].textContent;
            break;
          }
        }
      }

      function deleteCampaignCrud(campaignId) {
        if (confirm('¿Estás seguro de que quieres eliminar esta campaña?')) {
          const formData = new FormData();
          formData.append('action', 'delete');
          formData.append('campaignId', campaignId);
          
          fetch('controlador/campaign_crud.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Campaña eliminada exitosamente');
              loadCampaigns();
              loadCampaignsForDropdown();
            } else {
              alert('Error al eliminar: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la campaña');
          });
        }
      }

      function clearCampaignForm() {
        document.getElementById('campaignCrudForm').reset();
        document.getElementById('campaignId').value = '';
        loadCampaignsForDropdown();
      }

      // Load campaigns when modal is shown
      document.getElementById('campaignCrudModal').addEventListener('shown.bs.modal', function () {
        loadCampaigns();
      });


      // Initialize page
      document.addEventListener('DOMContentLoaded', function() {
        console.log('=== PAGE LOADED - INITIALIZING ===');
        loadAsesores();
        
        // Load campaigns dropdown after a short delay to ensure DOM is ready
        setTimeout(() => {
          loadCampaignsForDropdown();
        }, 200);
      });
    </script>
  </body>

</html>