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
$id_Rol = $accessData['id_Rol'];

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Generación de Contactos - Sistema de Gestión</title>

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
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="assets/vendor/datatables/dataTables.bootstrap5.min.css" />
    
    <!-- DatePicker CSS -->
    <link rel="stylesheet" href="assets/vendor/daterange/daterangepicker.css" />
  </head>

  <body>
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
                    <a href="index.html" class="text-decoration-none">Home</a>
                  </li>
                  <li class="breadcrumb-item">Contactos</li>
                  <li class="breadcrumb-item">Generación de Contactos</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Generación de Contactos</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProspecto">
                      <i class="icon-plus"></i> Nuevo Prospecto
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table id="tablaProspectos" class="table table-striped table-hover">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Fecha Captación</th>
                            <th>Nombre Prospecto</th>
                            <th>Teléfono</th>
                            <th>Estatus Lead</th>
                            <th>Canal/Repositorio</th>
                            <th>Producto Interés</th>
                            <th>Estatus Venta</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

            <!-- Modal para crear/editar prospecto -->
            <div class="modal fade" id="modalProspecto" tabindex="-1" aria-labelledby="modalProspectoLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalProspectoLabel">Nuevo Prospecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form id="formProspecto">
                      <input type="hidden" id="prospecto_id" name="id">
                      
                      <div class="row">
                        <!-- Primera columna -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="mes_seguimiento" class="form-label">Mes Seguimiento</label>
                            <select class="form-control" id="mes_seguimiento" name="mes_seguimiento">
                              <option value="Enero">Enero</option>
                              <option value="Febrero">Febrero</option>
                              <option value="Marzo">Marzo</option>
                              <option value="Abril">Abril</option>
                              <option value="Mayo">Mayo</option>
                              <option value="Junio">Junio</option>
                              <option value="Julio">Julio</option>
                              <option value="Agosto">Agosto</option>
                              <option value="Septiembre">Septiembre</option>
                              <option value="Octubre">Octubre</option>
                              <option value="Noviembre">Noviembre</option>
                              <option value="Diciembre">Diciembre</option>
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="fecha_captacion" class="form-label">Fecha Captación</label>
                            <input type="date" class="form-control" id="fecha_captacion" name="fecha_captacion">
                          </div>
                          
                          <div class="mb-3">
                            <label for="servicio_persona_fallecida" class="form-label">Servicio Persona Fallecida</label>
                            <input type="text" class="form-control" id="servicio_persona_fallecida" name="servicio_persona_fallecida" maxlength="150">
                          </div>
                          
                          <div class="mb-3">
                            <label for="fuente_revisar_nota" class="form-label">Fuente/Revisar Nota</label>
                            <input type="text" class="form-control" id="fuente_revisar_nota" name="fuente_revisar_nota" maxlength="150">
                          </div>
                          
                          <div class="mb-3">
                            <label for="canal_repositorio" class="form-label">Canal/Repositorio</label>
                            <input type="text" class="form-control" id="canal_repositorio" name="canal_repositorio" maxlength="100">
                          </div>
                          
                          <div class="mb-3">
                            <label for="nombre_prospecto" class="form-label">Nombre Prospecto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_prospecto" name="nombre_prospecto" maxlength="200" required>
                          </div>
                          
                          <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20">
                          </div>
                          
                          <div class="mb-3">
                            <label for="estatus_lead" class="form-label">Estatus Lead</label>
                            <select class="form-control" id="estatus_lead" name="estatus_lead">
                              <option value="">Seleccione...</option>
                              <option value="Nuevo">Nuevo</option>
                              <option value="Contactado">Contactado</option>
                              <option value="Calificado">Calificado</option>
                              <option value="Perdido">Perdido</option>
                            </select>
                          </div>
                        </div>
                        
                        <!-- Segunda columna -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="realizo_llamada_nombre_callcenter" class="form-label">Realizó Llamada/Nombre CallCenter</label>
                            <select class="form-control" id="realizo_llamada_nombre_callcenter" name="realizo_llamada_nombre_callcenter">
                              <option value="">Seleccione empleado...</option>
                              <!-- Se cargarán dinámicamente los empleados -->
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="dia_encuesta" class="form-label">Día Encuesta</label>
                            <input type="date" class="form-control" id="dia_encuesta" name="dia_encuesta">
                          </div>
                          
                          <div class="mb-3">
                            <label for="accion_a_realizar" class="form-label">Acción a Realizar</label>
                            <input type="text" class="form-control" id="accion_a_realizar" name="accion_a_realizar" maxlength="255">
                          </div>
                          
                          <div class="mb-3">
                            <label for="producto_interes" class="form-label">Producto Interés</label>
                            <select class="form-control" id="producto_interes" name="producto_interes">
                              <option value="">Seleccione producto...</option>
                              <!-- Se cargarán dinámicamente los productos -->
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="asesor_guardia" class="form-label">Asesor Guardia</label>
                            <select class="form-control" id="asesor_guardia" name="asesor_guardia">
                              <option value="">Seleccione asesor...</option>
                              <!-- Se cargarán dinámicamente los asesores -->
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="se_canalizo_asesor" class="form-label">Se Canalizó Asesor</label>
                            <select class="form-control" id="se_canalizo_asesor" name="se_canalizo_asesor">
                              <option value="">Seleccione...</option>
                              <option value="Si">Si</option>
                              <option value="No">No</option>
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="estatus_venta" class="form-label">Estatus Venta</label>
                            <select class="form-control" id="estatus_venta" name="estatus_venta">
                              <option value="">Seleccione...</option>
                              <option value="Pendiente">Pendiente</option>
                              <option value="CANALIZADA">Asignada Asesor</option>
                            </select>
                          </div>
                          
                          <div class="mb-3">
                            <label for="numero_contrato" class="form-label">Número Contrato</label>
                            <input type="text" class="form-control" id="numero_contrato" name="numero_contrato" maxlength="100">
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0">
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="descripcion_venta" class="form-label">Descripción Venta</label>
                            <textarea class="form-control" id="descripcion_venta" name="descripcion_venta" rows="3"></textarea>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="comentarios_finales" class="form-label">Comentarios Finales</label>
                            <textarea class="form-control" id="comentarios_finales" name="comentarios_finales" rows="3"></textarea>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarProspecto()">Guardar</button>
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

    <!-- *************
			************ Vendor Js Files *************
		************* -->

    <!-- Overlay Scroll JS -->
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>

    <!-- DataTables JS -->
    <script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>
    
    <script>
      let tablaProspectos;
      
      $(document).ready(function() {
        // Establecer el mes actual como default
        setMesActual();
        
        // Cargar empleados de call center
        cargarEmpleadosCallCenter();
        
        // Cargar asesores de guardia
        cargarAsesoresGuardia();
        
        // Cargar productos de interés
        cargarProductosInteres();
        
        // Inicializar DataTable
        tablaProspectos = $('#tablaProspectos').DataTable({
          ajax: {
            url: 'controlador/get_prospectos.php',
            type: 'GET'
          },
          columns: [
            { data: 'id' },
            { data: 'fecha_captacion' },
            { data: 'nombre_prospecto' },
            { data: 'telefono' },
            { data: 'estatus_lead' },
            { data: 'canal_repositorio' },
            { data: 'producto_interes' },
            { data: 'estatus_venta' },
            { 
              data: 'monto',
              render: function(data, type, row) {
                return data ? '$' + parseFloat(data).toLocaleString() : '-';
              }
            },
            {
              data: null,
              orderable: false,
              render: function(data, type, row) {
                return `
                  <button class="btn btn-sm btn-primary" onclick="editarProspecto(${row.id})">
                    <i class="icon-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="eliminarProspecto(${row.id})">
                    <i class="icon-trash"></i>
                  </button>
                `;
              }
            }
          ],
          language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
          },
          responsive: true,
          order: [[0, 'desc']]
        });
        
        // Reset form when modal is closed
        $('#modalProspecto').on('hidden.bs.modal', function () {
          resetearFormulario();
        });
      });
      
      function resetearFormulario() {
        $('#formProspecto')[0].reset();
        $('#prospecto_id').val('');
        $('#modalProspectoLabel').text('Nuevo Prospecto');
        // Restablecer el mes actual como default
        setMesActual();
      }
      
      function guardarProspecto() {
        const formData = new FormData($('#formProspecto')[0]);
        
        // Validar campos requeridos
        if (!$('#nombre_prospecto').val()) {
          alert('El nombre del prospecto es requerido');
          return;
        }
        
        $.ajax({
          url: 'controlador/save_prospecto.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            try {
              const result = JSON.parse(response);
              if (result.success) {
                alert('Prospecto guardado exitosamente');
                $('#modalProspecto').modal('hide');
                tablaProspectos.ajax.reload();
              } else {
                alert('Error: ' + result.message);
              }
            } catch (e) {
              alert('Error al procesar la respuesta del servidor');
            }
          },
          error: function() {
            alert('Error de conexión con el servidor');
          }
        });
      }
      
      function editarProspecto(id) {
        $.ajax({
          url: 'controlador/get_prospecto.php',
          type: 'GET',
          data: { id: id },
          success: function(response) {
            try {
              const result = JSON.parse(response);
              if (result.success) {
                const data = result.data;
                
                // Llenar el formulario con los datos
                $('#prospecto_id').val(data.id);
                $('#mes_seguimiento').val(data.mes_seguimiento);
                $('#fecha_captacion').val(data.fecha_captacion);
                $('#servicio_persona_fallecida').val(data.servicio_persona_fallecida);
                $('#fuente_revisar_nota').val(data.fuente_revisar_nota);
                $('#canal_repositorio').val(data.canal_repositorio);
                $('#nombre_prospecto').val(data.nombre_prospecto);
                $('#telefono').val(data.telefono);
                $('#estatus_lead').val(data.estatus_lead);
                $('#realizo_llamada_nombre_callcenter').val(data.realizo_llamada_nombre_callcenter);
                $('#dia_encuesta').val(data.dia_encuesta);
                $('#accion_a_realizar').val(data.accion_a_realizar);
                $('#producto_interes').val(data.producto_interes);
                $('#asesor_guardia').val(data.asesor_guardia);
                $('#se_canalizo_asesor').val(data.se_canalizo_asesor);
                $('#estatus_venta').val(data.estatus_venta);
                $('#numero_contrato').val(data.numero_contrato);
                $('#descripcion_venta').val(data.descripcion_venta);
                $('#monto').val(data.monto);
                $('#comentarios_finales').val(data.comentarios_finales);
                
                // Cambiar el título del modal
                $('#modalProspectoLabel').text('Editar Prospecto');
                
                // Mostrar el modal
                $('#modalProspecto').modal('show');
              } else {
                alert('Error: ' + result.message);
              }
            } catch (e) {
              alert('Error al procesar la respuesta del servidor');
            }
          },
          error: function() {
            alert('Error de conexión con el servidor');
          }
        });
      }
      
      function eliminarProspecto(id) {
        if (confirm('¿Está seguro de que desea eliminar este prospecto?')) {
          $.ajax({
            url: 'controlador/delete_prospecto.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
              try {
                const result = JSON.parse(response);
                if (result.success) {
                  alert('Prospecto eliminado exitosamente');
                  tablaProspectos.ajax.reload();
                } else {
                  alert('Error: ' + result.message);
                }
              } catch (e) {
                alert('Error al procesar la respuesta del servidor');
              }
            },
            error: function() {
              alert('Error de conexión con el servidor');
            }
          });
        }
      }
      
      // Función para establecer el mes actual como default
      function setMesActual() {
        const meses = [
          'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        const mesActual = new Date().getMonth();
        const nombreMesActual = meses[mesActual];
        
        $('#mes_seguimiento').val(nombreMesActual);
      }
      
      // Función para cargar empleados de call center (excluyendo EJECUTIVO, GERENTE, DIRECTOR, SISTEMAS)
      function cargarEmpleadosCallCenter() {
        $.ajax({
          url: 'controlador/get_empleados_callcenter.php',
          type: 'GET',
          success: function(response) {
            try {
              const result = JSON.parse(response);
              if (result.success) {
                const select = $('#realizo_llamada_nombre_callcenter');
                select.empty();
                select.append('<option value="">Seleccione empleado...</option>');
                
                result.data.forEach(function(empleado) {
                  select.append(`<option value="${empleado.nombre_completo}">${empleado.nombre_completo} - ${empleado.puesto}</option>`);
                });
              } else {
                console.error('Error al cargar empleados call center:', result.message);
              }
            } catch (e) {
              console.error('Error al procesar empleados call center:', e);
            }
          },
          error: function() {
            console.error('Error de conexión al cargar empleados call center');
          }
        });
      }
      
      // Función para cargar asesores de guardia (solo puesto = ASESOR)
      function cargarAsesoresGuardia() {
        $.ajax({
          url: 'controlador/get_asesores_guardia.php',
          type: 'GET',
          success: function(response) {
            try {
              const result = JSON.parse(response);
              if (result.success) {
                const select = $('#asesor_guardia');
                select.empty();
                select.append('<option value="">Seleccione asesor...</option>');
                
                result.data.forEach(function(asesor) {
                  select.append(`<option value="${asesor.nombre_completo}">${asesor.nombre_completo}</option>`);
                });
              } else {
                console.error('Error al cargar asesores guardia:', result.message);
              }
            } catch (e) {
              console.error('Error al procesar asesores guardia:', e);
            }
          },
          error: function() {
            console.error('Error de conexión al cargar asesores guardia');
          }
        });
      }
      
      // Función para cargar productos de interés desde precio_servicio
      function cargarProductosInteres() {
        $.ajax({
          url: 'controlador/get_productos_interes.php',
          type: 'GET',
          success: function(response) {
            try {
              const result = JSON.parse(response);
              if (result.success) {
                const select = $('#producto_interes');
                select.empty();
                select.append('<option value="">Seleccione producto...</option>');
                
                result.data.forEach(function(producto) {
                  select.append(`<option value="${producto.nombre}">${producto.nombre}</option>`);
                });
              } else {
                console.error('Error al cargar productos de interés:', result.message);
              }
            } catch (e) {
              console.error('Error al procesar productos de interés:', e);
            }
          },
          error: function() {
            console.error('Error de conexión al cargar productos de interés');
          }
        });
      }
    </script>
  </body>

</html>








