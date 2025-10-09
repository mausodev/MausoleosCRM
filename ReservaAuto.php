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

// Ensure no output before JSON response
/*error_reporting(E_ALL);
ini_set('display_errors', 0);*/
$response = ['success' => false, 'message' => ''];

// Add new condition to handle terms acceptance
if (isset($_POST['accept_terms'])) {
    $usuario = 'test@mle.com.mx'; // Replace with actual session user
    $acepta_terminos = $_POST['accept_terms'] === 'SI' ? 'SI' : 'NO';
    
    $sql = "INSERT INTO politica_reserva (usuario, fecha, acepta_terminos) 
            VALUES (?, NOW(), ?)";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $usuario, $acepta_terminos);
    
    if ($stmt->execute()) {
        $_SESSION['terms_accepted'] = true;
    } else {
        error_log("Error saving terms acceptance: " . $stmt->error);
    }
    
    exit(); // End execution after handling terms
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log incoming data for debugging
        error_log('POST request received: ' . json_encode($_POST));
        
        // Validate required fields
        $required_fields = ['start_date', 'end_date', 'selected_vehicle', 'kilometraje', 'destino'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Campo requerido faltante: $field");
            }
        }

        $fecha_inicio = $_POST['start_date'];
        $fecha_fin = $_POST['end_date'];
        $id_vehiculo = $_POST['selected_vehicle'];
        $kilometraje = $_POST['kilometraje'];
        $destino = $_POST['destino'];
        
        // Get vehicle model
        $model_sql = "SELECT CONCAT(marca, ' ', modelo) as modelo FROM vehiculo WHERE id = ?";
        $stmt = $con->prepare($model_sql);
        $stmt->bind_param("i", $id_vehiculo);
        $stmt->execute();
        $model_result = $stmt->get_result();
        $modelo = $model_result->fetch_assoc()['modelo'];
        
        // Verificar si la sesión está activa y contiene user_id
        /*if (!isset($_SESSION['user_id'])) {
            $response['message'] = 'Usuario no autenticado';
            error_log('Usuario no autenticado');
            echo json_encode($response);
            exit;
        }*/
        
        $user_id = 'test@mle.com.mx';//$_SESSION['user_id']
        
        // Verificar si ya existe una reserva para ese vehículo en ese horario
        $check_sql = "SELECT COUNT(*) as count FROM reserva_auto 
                      WHERE id_vehiculo = ? 
                      AND ((fecha_inicio BETWEEN ? AND ?) 
                      OR (fecha_fin BETWEEN ? AND ?))
                      AND estatus = 'ACTIVO'";
        
        $stmt = $con->prepare($check_sql);
        $stmt->bind_param("issss", $id_vehiculo, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
       
        
        if ($row['count'] > 0) {
            echo "<script>
                document.getElementById('mensaje').innerHTML = 'Ya existe una reserva para este vehículo en el horario seleccionado';
                document.getElementById('mensaje').style.color = 'red';
            </script>";
        } else {
            $sql = "INSERT INTO reserva_auto (id_vehiculo, modelo, fecha_inicio, fecha_fin, usuario, kilometraje, destino, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')";
           
            $stmt = $con->prepare($sql);
            $stmt->bind_param("issssss", $id_vehiculo, $modelo, $fecha_inicio, $fecha_fin, $user_id, $kilometraje, $destino);
           
            if ($stmt->execute()) {
                echo "<script>
                    document.getElementById('mensaje').innerHTML = 'Reserva guardada exitosamente';
                    document.getElementById('mensaje').style.color = 'green';
                </script>";
            } else {
                echo "<script>
                    document.getElementById('mensaje').innerHTML = 'Error al guardar la reserva: " . $stmt->error . "';
                    document.getElementById('mensaje').style.color = 'red';
                </script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>
            document.getElementById('mensaje').innerHTML = 'Error en el servidor: " . $e->getMessage() . "';
            document.getElementById('mensaje').style.color = 'red';
        </script>";
    }
    
    // Redirect back to the same page to display the message
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Display message if exists
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['message']['type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message']['text'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
   unset($_SESSION['message']); // Clear the message after displaying
}

// Only proceed to HTML output if not a POST request
$showTerms = !isset($_SESSION['terms_accepted']);
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

    <!-- Calendar CSS -->
    <link rel="stylesheet" href="assets/vendor/calendar/css/main.min.css" />
    <link rel="stylesheet" href="assets/vendor/calendar/css/custom.css" />

    <!-- Before closing </head> tag, add these styles -->
    <style>
    .fc-event {
        cursor: pointer;
    }
    .vehicle-select {
        margin-bottom: 15px;
    }
    #selectableCalendar {
        height: 800px; /* o el alto que prefieras */
        width: 100%;
        margin: 20px 0;
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
 
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-margin"></i> Reserva Auto
                  </a>
                  <ul class="dropdown-menu">
      
                    <li>
                      <a class="dropdown-item current-page" href="reservaAuto.php"><span>Reservar Auto</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="alta_auto.php"><span>Control Auto</span></a>
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
                  <li class="breadcrumb-item">Plugins</li>
                  <li class="breadcrumb-item">Calendar Selectable</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="vehicle-select">
                      <select id="vehiculo" class="form-control">
                        <option value="">Seleccione un vehículo</option>
                        <?php
                        $sql = "SELECT id, marca, modelo FROM vehiculo WHERE estatus = 'ACTIVO'";
                        $result = $con->query($sql);
                        
                        if ($result) {
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='".$row['id']."'>".$row['marca']." ".$row['modelo']."</option>";
                            }
                        } else {
                            echo "<!-- Error en la consulta: " . $con->error . " -->";
                        }
                        ?>
                      </select>
                      <label id="mensaje" name="mensaje"></label>
                    </div>
                    <div id="selectableCalendar"></div>
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
            <span>© portal Mausoleos 2025</span>
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

    <!-- Calendar JS -->
    <script src="assets/vendor/calendar/js/main.min.js"></script>
    <script src="assets/vendor/calendar/custom/selectable-calendar.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <!-- Add Modal for Reservation -->
    <div class="modal fade" id="reservationModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nueva Reserva</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <!-- Add message display div -->
            <div id="messageContainer" class="alert" style="display: none;"></div>
            
            <form id="reservationForm" method="POST" action="ReservaAuto.php">
              <div class="mb-3">
                <label class="form-label">Vehículo Seleccionado</label>
                <div class="form-control bg-light" readonly>
                  <span id="vehicleInfo">ID: <span id="vehicleId"></span> - Modelo: <span id="vehicleModel"></span></span>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Fecha y Hora de Inicio</label>
                <input type="datetime-local" id="start_date" name="start_date" class="form-control" required>
                <small class="text-muted">Seleccione la fecha y hora de inicio</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Fecha y Hora de Fin</label>
                <input type="datetime-local" id="end_date" name="end_date" class="form-control" required>
                <small class="text-muted">Mínimo 2 horas, máximo 5 días después del inicio</small>
              </div>
              <input type="hidden" id="selected_vehicle" name="selected_vehicle">
              <div class="mb-3">
                <label class="form-label">Kilometraje Inicial</label>
                <input type="number" id="kilometraje" name="kilometraje" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Destino</label>
                <input type="text" id="destino" name="destino" class="form-control" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Reserva</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modify the JavaScript to only handle date validation and modal display -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('selectableCalendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            selectMirror: true,
            headerToolbar: {
                left: 'prev,next today verReservas',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            locale: 'es',
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana'
            },
            customButtons: {
                verReservas: {
                    text: 'Ver Reservas',
                    click: function() {
                        // Fetch and show reservations
                        fetch('get_reservations.php')
                            .then(response => response.json())
                            .then(data => {
                                populateReservationsList(data);
                                var modal = new bootstrap.Modal(document.getElementById('reservationsListModal'));
                                modal.show();
                            });
                    }
                }
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch('get_reservations.php')
                    .then(response => response.json())
                    .then(data => {
                        // Transform the events to include color
                        const coloredEvents = data.map(event => ({
                            ...event,
                            color: '#28a745', // Green color for reserved days
                            textColor: '#ffffff',
                            display: 'background', // This makes the event show as background
                            overlap: true
                        }));
                        
                        // Add a second event for the text display
                        const textEvents = data.map(event => ({
                            ...event,
                            color: '#28a745',
                            textColor: '#ffffff'
                        }));
                        
                        // Combine both types of events
                        successCallback([...coloredEvents, ...textEvents]);
                    })
                    .catch(error => {
                        console.error('Error al cargar eventos:', error);
                        failureCallback(error);
                    });
            },
            select: function(info) {
                const vehiculoSelect = document.getElementById('vehiculo');
                const vehiculo = vehiculoSelect.value;
                if (!vehiculo) {
                    document.getElementById('messageContainer').className = 'alert alert-warning';
                    document.getElementById('messageContainer').style.display = 'block';
                    document.getElementById('messageContainer').textContent = 'Por favor seleccione un vehículo primero';
                    return;
                }

                const selectedOption = vehiculoSelect.options[vehiculoSelect.selectedIndex];
                
                document.getElementById('vehicleId').textContent = vehiculo;
                document.getElementById('vehicleModel').textContent = selectedOption.text;

                const minDate = info.start.toISOString().slice(0, 16);
                
                const maxDate = new Date(info.start);
                maxDate.setDate(maxDate.getDate() + 5);
                const maxDateStr = maxDate.toISOString().slice(0, 16);

                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');
                
                startDateInput.value = minDate;
                startDateInput.min = minDate;
                startDateInput.max = maxDateStr;
                
                endDateInput.value = new Date(info.start.getTime() + 2*60*60*1000).toISOString().slice(0, 16);
                endDateInput.min = minDate;
                endDateInput.max = maxDateStr;

                document.getElementById('selected_vehicle').value = vehiculo;

                var modal = new bootstrap.Modal(document.getElementById('reservationModal'));
                modal.show();
            }
        });

        calendar.render();

        // Only keep date validation
        function validateDates() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate && endDate) {
                const diffHours = (endDate - startDate) / (1000 * 60 * 60);
                const diffDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
                
                if (diffHours < 2 || diffDays > 5) {
                    document.getElementById('messageContainer').className = 'alert alert-warning';
                    document.getElementById('messageContainer').style.display = 'block';
                    document.getElementById('messageContainer').textContent = 
                        diffHours < 2 ? 'La reserva debe ser de al menos 2 horas' : 'La reserva no puede ser por más de 5 días';
                    return false;
                }
                
                document.getElementById('messageContainer').style.display = 'none';
                return true;
            }
        }

        document.getElementById('start_date').addEventListener('change', validateDates);
        document.getElementById('end_date').addEventListener('change', validateDates);
        
        // Add form validation before submit
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
            }
        });
    });

    // Add function to populate reservations list
    function populateReservationsList(reservations) {
        const tbody = document.getElementById('reservationsTableBody');
        tbody.innerHTML = '';
        
        reservations.forEach(reservation => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${reservation.title}</td>
                <td>${new Date(reservation.start).toLocaleString()}</td>
                <td>${new Date(reservation.end).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editReservation(${reservation.id})">
                        <i class="icon-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteReservation(${reservation.id})">
                        <i class="icon-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    </script>

    <!-- Add Reservations List Modal -->
    <div class="modal fade" id="reservationsListModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lista de Reservas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="reservationsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Términos y Condiciones de Uso de Vehículos</h5>
                </div>
                <div class="modal-body">
                    <div class="terms-content">
                        <h6>Por favor lea cuidadosamente las siguientes políticas:</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">• Se deberá de dejar el carro limpio por dentro y fuera con tanque lleno, en caso de que se requiera aplicar un reembolso por lavado del vehículo favor de tramitarlo con pagos y copia a brenda.valles@mle.com.mx.</li>
                            <li class="list-group-item">• El vehículo debe de permanecer en las instalaciones de mausoleos cuando no esté en uso y forzosamente los fines de semana.</li>
                            <li class="list-group-item">• Si no se cuenta con licencia vigente no se prestará el vehículo.</li>
                            <li class="list-group-item">• Favor de asegurarse que el vehículo traiga la tarjeta de circulación y póliza de seguro vigente en la guantera. (si no favor de comunicarse con el coordinador corporativo de mantenimiento)</li>
                            <li class="list-group-item">• En caso de cualquier siniestro favor de comunicarse con la aseguradora de inmediato y posteriormente avisar al coordinador corporativo de mantenimiento.</li>
                            <li class="list-group-item">• De tener la culpa en un siniestro en horario y actividades laborales, deberá de pagar el 50% del total de los daños ocasionados, en caso de no ser en horario laboral, o estar haciendo otras actividades fuera de lo laboral, así como estar en mal estado físico o no contar con su licencia vigente, deberá de pagar el 100% de todos los daños.</li>
                        </ul>
                        
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="acceptTerms">
                            <label class="form-check-label" for="acceptTerms">
                                He leído y acepto los términos y condiciones
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="acceptButton" disabled>Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add before closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show terms modal if not accepted
        <?php if ($showTerms): ?>
        var termsModal = new bootstrap.Modal(document.getElementById('termsModal'), {
            backdrop: 'static',
            keyboard: false
        });
        termsModal.show();
        <?php endif; ?>

        // Handle checkbox change
        document.getElementById('acceptTerms').addEventListener('change', function() {
            document.getElementById('acceptButton').disabled = !this.checked;
        });

        // Handle accept button click
        document.getElementById('acceptButton').addEventListener('click', function() {
            fetch('ReservaAuto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'accept_terms=SI'
            })
            .then(response => {
                if (response.ok) {
                    var termsModal = bootstrap.Modal.getInstance(document.getElementById('termsModal'));
                    termsModal.hide();
                    location.reload(); // Reload page to update session state
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>