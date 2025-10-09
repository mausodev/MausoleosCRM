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

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
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

    <!-- Data Tables -->
    <link rel="stylesheet" href="assets/vendor/datatables/dataTables.bs5.css" />
    <link rel="stylesheet" href="assets/vendor/datatables/dataTables.bs5-custom.css" />
    <link rel="stylesheet" href="assets/vendor/datatables/buttons/dataTables.bs5-custom.css" />
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
                          Avisos
                        </h5>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-success rounded-circle me-3">
                              <i class="icon-shopping-bag text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">Equipo de soporte</h6>
                              <p class="mb-1 text-secondary">
                                
                              </p>
                              <p class="small m-0 text-secondary">
                                3*
                              </p>
                            </div>
                          </div>
                        </div>
                       
                        <div class="d-grid mx-3 my-1">
                          <a href="javascript:void(0)" class="btn btn-outline-primary">Mostrar</a>
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
               
               
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fs-3 icon-local_taxi"></i> Reserva Auto
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="ReservaAuto.php"><span>Reservar Auto</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item current-page" href="alta_auto.php"><span>Control Auto</span></a>
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
                      <a class="dropdown-item" href="login.html">
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
                    <a href="index.html" class="text-decoration-none">Inicio</a>
                  </li>
                  <li class="breadcrumb-item">Reserva Auto</li>
                  <li class="breadcrumb-item">Vehiculos</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <!-- Card start -->
                <div class="card mb-3">
                  <div class="card-header">
                    <div class="card-title"><label style="font-size: large;"><strong>Gestión de Vehículos</strong></label></div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                      Agregar Vehículo
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table id="customButtons" class="table table-striped">
                        <thead>
                          <tr>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Kilometraje</th>
                            <th>Póliza</th>
                            <th>Vigencia</th>
                            <th>Servicio</th>
                            <th>Próximo Servicio</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          require './controlador/conexion.php';
                          $query = "SELECT * FROM vehiculo";
                          $result = mysqli_query($con, $query);
                          
                          if (!$result) {
                              die("Error en la consulta: " . mysqli_error($con));
                          }
                          
                          // Depuración
                          $first_row = mysqli_fetch_assoc($result);
                          //var_dump($first_row);  // Esto mostrará la estructura del primer registro
                          mysqli_data_seek($result, 0);  // Regresa el puntero al inicio
                          
                          while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>".($row['marca'] ?? '')."</td>";
                            echo "<td>".($row['modelo'] ?? '')."</td>";
                            echo "<td>".($row['anio'] ?? '')."</td>";
                            echo "<td>".($row['kilometraje'] ?? '')."</td>";
                            echo "<td>".($row['poliza'] ?? '')."</td>";
                            echo "<td>".($row['vigencia'] ?? '')."</td>";
                            echo "<td>".($row['servicio'] ?? '')."</td>";
                            echo "<td>".($row['proximo_servicio'] ?? '')."</td>";
                            echo "<td>".($row['estatus'] ?? '')."</td>";
                            echo "<td>
                                    <button class='btn btn-warning btn-sm' onclick='editVehicle(".($row['id'] ?? 0).")'>Editar</button>
                                    <button class='btn btn-danger btn-sm' onclick='deleteVehicle(".($row['id'] ?? 0).")'>Eliminar</button>
                                  </td>";
                            echo "</tr>";
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- Card end -->

                <!-- Modal para Agregar/Editar Vehículo -->
                <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="addVehicleModalLabel">Agregar Vehículo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form id="vehicleForm" action="controlador/procesar_vehiculo.php" method="POST">
                          <input type="hidden" name="id" id="vehicleId">
                          <div class="mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                          </div>
                          <div class="mb-3">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" required>
                          </div>
                          <div class="mb-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" id="anio" name="anio" required>
                          </div>
                          <div class="mb-3">
                            <label for="kilometraje" class="form-label">Kilometraje</label>
                            <input type="number" class="form-control" id="kilometraje" name="kilometraje" required>
                          </div>
                          <div class="mb-3">
                            <label for="poliza" class="form-label">Póliza</label>
                            <input type="text" class="form-control" id="poliza" name="poliza" required>
                          </div>
                          <div class="mb-3">
                            <label for="vigencia" class="form-label">Vigencia</label>
                            <input type="date" class="form-control" id="vigencia" name="vigencia" required>
                          </div>
                          <div class="mb-3">
                            <label for="servicio" class="form-label">Fecha de Servicio</label>
                            <input type="datetime-local" class="form-control" id="servicio" name="servicio">
                          </div>
                          <div class="mb-3">
                            <label for="proximo_servicio" class="form-label">Próximo Servicio</label>
                            <input type="datetime-local" class="form-control" id="proximo_servicio" name="proximo_servicio">
                          </div>
                          <div class="mb-3">
                            <label for="estatus" class="form-label">Estatus</label>
                            <select class="form-control" id="estatus" name="estatus">
                              <option value="ACTIVO">ACTIVO</option>
                              <option value="INACTIVO">INACTIVO</option>
                              <option value="TEMPORAL">TEMPORAL</option>
                            </select>
                          </div>
                          <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

                <script>
                function editVehicle(id) {
                    console.log('Editando vehículo:', id);
                    
                    // Cambiar el título del modal
                    $('#addVehicleModalLabel').text('Editar Vehículo');
                    
                    // Obtener datos del vehículo mediante AJAX
                    $.ajax({
                        url: 'controlador/get_vehiculo.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Datos recibidos:', response);
                            
                            // Llenar los campos del formulario con los datos recibidos
                            $('#vehicleId').val(response.id);
                            $('#marca').val(response.marca);
                            $('#modelo').val(response.modelo);
                            $('#anio').val(response.anio);
                            $('#kilometraje').val(response.kilometraje);
                            $('#poliza').val(response.poliza);
                            $('#vigencia').val(response.vigencia);
                            $('#servicio').val(response.servicio);
                            $('#proximo_servicio').val(response.proximo_servicio);
                            $('#estatus').val(response.estatus);
                            
                            // Mostrar el modal
                            $('#addVehicleModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en la petición AJAX:', error);
                            console.error('Estado:', status);
                            console.error('Respuesta:', xhr.responseText);
                            alert('Error al obtener los datos del vehículo');
                        }
                    });
                }

                function deleteVehicle(id) {
                  if(confirm('¿Está seguro de eliminar este vehículo?')) {
                    $.ajax({
                      url: 'controlador/eliminar_vehiculo.php',
                      type: 'POST',
                      data: {id: id},
                      success: function(response) {
                        location.reload();
                      }
                    });
                  }
                }

                // Reset form when modal is closed
                $('#addVehicleModal').on('hidden.bs.modal', function () {
                  $('#vehicleForm')[0].reset();
                  $('#vehicleId').val('');
                  $('#addVehicleModalLabel').text('Agregar Vehículo');
                  $('#estatus').val('ACTIVO');
                });

                // Cuando se abre el modal para un nuevo registro
                $('#addVehicleModal').on('show.bs.modal', function (e) {
                    // Si no hay ID en el campo oculto, es un nuevo registro
                    if (!$('#vehicleId').val()) {
                        $('#addVehicleModalLabel').text('Agregar Vehículo');
                        $('#estatus').val('ACTIVO');
                    }
                });
                </script>

              

              
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

    <!-- Data Tables -->
    <script src="assets/vendor/datatables/dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap.min.js"></script>
    <script src="assets/vendor/datatables/custom/custom-datatables.js"></script>
    <!-- DataTable Buttons -->
    <script src="assets/vendor/datatables/buttons/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables/buttons/jszip.min.js"></script>
    <script src="assets/vendor/datatables/buttons/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables/buttons/pdfmake.min.js"></script>
    <script src="assets/vendor/datatables/buttons/vfs_fonts.js"></script>
    <script src="assets/vendor/datatables/buttons/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables/buttons/buttons.print.min.js"></script>
    <script src="assets/vendor/datatables/buttons/buttons.colVis.min.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>
  </body>

</html>