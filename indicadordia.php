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

// Asegúrate de que la conexión a la base de datos esté establecida
$conexion = mysqli_connect('localhost', 'root', 'admin', 'mausoleo_local');

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Define la consulta SQL para obtener coordinadores y asesores únicos
if ($puesto == 'COORDINADOR') {
    $sql = "SELECT DISTINCT e.id_supervisor, e.id AS id_asesor, e.iniciales,
               (SELECT iniciales FROM empleado WHERE id = e.id_supervisor) AS iniciales_coordinador
        FROM empleado e
        LEFT JOIN cliente c ON c.asesor = e.id
        WHERE c.etapa NOT IN ('Cerrado Ganado', 'Cerrado Perdido')
        AND e.id_supervisor = $id_asesor
        ORDER BY e.id_supervisor, e.id";
} else {
    $sql = "SELECT DISTINCT e.id_supervisor, e.id AS id_asesor, e.iniciales,
               (SELECT iniciales FROM empleado WHERE id = e.id_supervisor) AS iniciales_coordinador
        FROM empleado e
        LEFT JOIN cliente c ON c.asesor = e.id
        WHERE c.etapa NOT IN ('Cerrado Ganado', 'Cerrado Perdido')
        ORDER BY e.id_supervisor, e.id";
}

// Ejecuta la consulta y maneja errores
$result = mysqli_query($conexion, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

// Agrupar los resultados por coordinador
$coordinadores = [];
while ($row = mysqli_fetch_assoc($result)) {
    $coordinadores[$row['id_supervisor']][] = $row;
}

// Ahora puedes usar $result para obtener los datos
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
                            <h6 class="mb-1 fw-semibold">Informativo</h6>
                              <p class="mb-1 text-secondary">
                                Total Ventas mes $.
                              </p>
                              <p class="small m-0 text-secondary">
                                30 mins
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
                              <p class="small m-0 text-secondary">1 dia</p>
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
                              <p class="small m-0 text-secondary">3 dias</p>
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
                        <a href="login.html" class="btn btn-outline-danger">Logout</a>
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
                <?php if ($puesto !== 'ASESOR'): ?>
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
                        <span>Embudo de ventas</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item current-page" href="indicadordia.php">
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
                  <li class="breadcrumb-item">Actividad Día</li>
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
                    

            <!-- Row start -->
            
            <!-- Row end -->

            <!-- Row start -->
            
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body">
                    <div class="table-outer">
                      <div class="table-responsive">
                        <table class="table table-striped align-middle m-0">
                          <!--<thead>
                            <tr>
                              <th>ID Cliente</th>
                              <th>Nombre Cliente</th>
                              <th>ID Supervisor</th>
                              <th>Acciones</th>
                            </tr>
                          </thead>-->
                          <tbody>
                            <!-- Aquí se insertarán las filas de la tabla con PHP -->
                            <?php
                            // Suponiendo que $result contiene el resultado de la consulta SQL
                            while ($row = mysqli_fetch_assoc($result)) {
                              echo "<tr>";
                              echo "<td>{$row['id_cliente']}</td>";
                              echo "<td>{$row['nombre_cliente']}</td>";
                              echo "<td>{$row['id_supervisor']}</td>";
                              echo "<td><button class='btn btn-primary' onclick='toggleDetails({$row['id_supervisor']})'><i class='icon-plus'></i></button></td>";
                              echo "</tr>";
                            }
                            ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

            <div class="container mt-4">
              <?php foreach ($coordinadores as $id_supervisor => $asesores): ?>
                <?php
                // Contar el número total de clientes para el coordinador
                $totalClientes = 0;
                foreach ($asesores as $asesor) {
                    $sql = "SELECT COUNT(*) as total
                            FROM cliente
                            WHERE asesor = {$asesor['id_asesor']} 
                              AND etapa IN ('ACTIVAR', 'ESTRECHAR', 'EN PRONOSTICO')
                              AND DATE(fecha_modificado) = CURDATE()";
                    $result = mysqli_query($conexion, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $totalClientes += $row['total'];
                }
                ?>
                <div class="coordinator mb-3">
                  <h3>Coordinador: <?php echo $asesores[0]['iniciales_coordinador']; ?> (Total Clientes: <?php echo $totalClientes; ?>)</h3>
                  <button class="btn btn-info" onclick="toggleAdvisors(<?php echo $id_supervisor; ?>)">Mostrar Asesores</button>
                  <div id="advisors-<?php echo $id_supervisor; ?>" class="mt-2" style="display: none;">
                    <ul class="list-group">
                      <?php foreach ($asesores as $asesor): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <?php echo $asesor['iniciales']; ?>
                          <button class="btn btn-primary btn-sm" onclick="showClients(<?php echo $asesor['id_asesor']; ?>)">Mostrar Clientes</button>
                          <div id="clients-<?php echo $asesor['id_asesor']; ?>" class="mt-2" style="display: none;"></div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

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

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <script>
    function toggleDetails(supervisorId) {
      // Aquí puedes implementar la lógica para mostrar/ocultar los detalles de los asesores
      // Por ejemplo, podrías hacer una llamada AJAX para obtener los asesores de este supervisor
      console.log("Mostrar detalles para el supervisor con ID:", supervisorId);
    }

    function toggleAdvisors(supervisorId) {
      const advisorsDiv = document.getElementById(`advisors-${supervisorId}`);
      advisorsDiv.style.display = advisorsDiv.style.display === 'none' ? 'block' : 'none';
    }

    function showClients(asesorId) {
      const clientsDiv = document.getElementById(`clients-${asesorId}`);
      if (clientsDiv.style.display === 'none') {
        // Realizar una llamada AJAX para obtener los clientes del asesor
        fetch(`get_clients.php?asesor_id=${asesorId}`)
          .then(response => response.json())
          .then(data => {
            if (data.length > 0) {
              clientsDiv.innerHTML = data.map(client => `<p>${client.nombre_cliente} - Etapa: ${client.etapa}</p>`).join('');
            } else {
              clientsDiv.innerHTML = '<p>No hay clientes disponibles.</p>';
            }
            clientsDiv.style.display = 'block';
          })
          .catch(error => {
            console.error('Error al obtener los clientes:', error);
            clientsDiv.innerHTML = '<p>Error al cargar los clientes.</p>';
            clientsDiv.style.display = 'block';
          });
      } else {
        clientsDiv.style.display = 'none';
      }
    }
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>