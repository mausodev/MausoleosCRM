<?php
require './controlador/conexion.php';
require './controlador/access_control.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar acceso y obtener datos de sesión
$access_data = verificarAcceso();
$acceso = $access_data['acceso'];
$id_usuario = $access_data['id_asesor']; // usuario logueado
$correo = $access_data['correo'];
$puesto = $access_data['puesto'];

// Usar la variable de conexión correcta
$conn = $con;

// Obtener avisos del usuario (recibidos y enviados)
$sql = "(
    -- Avisos recibidos por el usuario
    SELECT ap.id_aviso, ap.titulo, ap.tipo_aviso, ap.mensaje, ap.fecha_envio, 
           e.nombre AS emisor, e.correo AS correo_emisor, ar.leido, ar.fecha_lectura,
           'recibido' AS tipo_aviso_usuario, NULL AS total_destinatarios, NULL AS leidos_count
    FROM aviso_portal ap
    JOIN empleado e ON ap.id_emisor = e.id
    JOIN avisos_receptores ar ON ap.id_aviso = ar.id_aviso
    WHERE ar.id_receptor = ?
)
UNION ALL
(
    -- Avisos enviados por el usuario
    SELECT ap.id_aviso, ap.titulo, ap.tipo_aviso, ap.mensaje, ap.fecha_envio,
           e.nombre AS emisor, e.correo AS correo_emisor, NULL AS leido, NULL AS fecha_lectura,
           'enviado' AS tipo_aviso_usuario,
           COUNT(ar.id_receptor) AS total_destinatarios,
           SUM(CASE WHEN ar.leido = 1 THEN 1 ELSE 0 END) AS leidos_count
    FROM aviso_portal ap
    JOIN empleado e ON ap.id_emisor = e.id
    LEFT JOIN avisos_receptores ar ON ap.id_aviso = ar.id_aviso
    WHERE ap.id_emisor = ?
    GROUP BY ap.id_aviso, ap.titulo, ap.tipo_aviso, ap.mensaje, ap.fecha_envio, e.nombre, e.correo
)
ORDER BY fecha_envio DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$avisos = $result->fetch_all(MYSQLI_ASSOC);

// Obtener empleados para el formulario (todos los empleados activos)
$sucursal_usuario = $access_data['sucursal'];
$empleados_sql = "SELECT id, nombre, correo, puesto, sucursal FROM empleado WHERE activo = 1 ORDER BY nombre";
$empleados_result = $conn->query($empleados_sql);
$empleados = $empleados_result->fetch_all(MYSQLI_ASSOC);

// Obtener todas las sucursales para el filtro
$sucursales_sql = "SELECT DISTINCT sucursal FROM empleado WHERE activo = 1 ORDER BY sucursal";
$sucursales_result = $conn->query($sucursales_sql);
$sucursales = $sucursales_result->fetch_all(MYSQLI_ASSOC);

// Contar avisos no leídos
$no_leidos = array_filter($avisos, function($aviso) {
    return !$aviso['leido'];
});
$total_no_leidos = count($no_leidos);

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
    
    <!-- Custom CSS for Avisos -->
    <link rel="stylesheet" href="assets/css/avisos.css" />

    <!-- *************
			************ Vendor Css Files *************
		************ -->

    <!-- Scrollbar CSS -->
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />
  </head>

  <body>
  <?php if (!$acceso): ?>
    <!-- Overlay de bloqueo cuando no hay acceso -->
    <div id="access-denied-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
      <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">
          <i class="icon-warning"></i>
        </div>
        <h2 style="color: #dc3545; margin-bottom: 20px;">ACCESO DENEGADO</h2>
        <p style="font-size: 18px; color: #6c757d; margin-bottom: 30px;">TU ROL NO TIENE ACCESO A ESTA PANTALLA</p>
        <a href="javascript:history.back()" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Regresar</a>
      </div>
    </div>
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
                  <a href="#" class="d-lg-block d-none">
                    <img src="assets/images/GrupoMausoleos.png" class="logo style width; 50%" alt="Bootstrap Gallery" />
                    
                  </a>
                  <a href="#" class="d-lg-none d-md-block">
                    <img src="assets/images/GrupoMausoleos.png" class="logo style width; 50%" alt="Bootstrap Gallery" />
                   
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
                        <span class="count">8</span>
                      </a>
                      <div class="dropdown-menu dropdown-menu-end dropdown-menu-md">
                        <h5 class="fw-semibold px-3 py-2 text-primary">
                        Informativo
                        </h5>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-success rounded-circle me-3">
                              <i class="icon-shopping-bag text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">VENTA</h6>
                              <p class="mb-1 text-secondary">
                              </p>
                              <p class="medium m-0 text-secondary">
                                $<span id="venta" class="fw-bold text-primary" style="font-size: 1.2em;">0.00</span>
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
                              <h6 class="mb-1 fw-semibold">META FALTANTE</h6>
                              <p class="mb-2"></p>
                              <p class="small m-0 text-secondary">$<span id="venta_faltante" class="fw-bold text-danger" style="font-size: 1.2em;">0.00</span></p>
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-item">
                          <div class="d-flex py-2">
                            <div class="icons-box md bg-warning rounded-circle me-3">
                              <i class="icon-shopping-cart text-white fs-4"></i>
                            </div>
                            <div class="m-0">
                              <h6 class="mb-1 fw-semibold">VENTA PRONOSTICO</h6>
                              <p class="mb-2"></p>
                              <p class="small m-0 text-secondary">$<span id="venta_pronostico" class="fw-bold text-warning" style="font-size: 1.2em;">0.00</span></p>
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
                <?php if ($acceso): ?>
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
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
                <li class="nav-item active-link">
                  <a class="nav-link" href="aviso.php">
                    <i class="icon-notifications"></i>Avisos
                  </a>
                </li>
                <?php if ($puesto !== 'ASESOR'): ?>
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
                    <?php if (in_array($puesto, ['GERENTE', 'EJECUTIVO', 'COORDINADOR'])): ?>
                    <li>
                      <a class="dropdown-item" href="admin_embudo_plaza.php">
                        <span>Porcentajes por Etapa</span></a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </li>
                <?php endif; ?>
                <?php if ($puesto !== 'ASESOR'): ?>
                <li class="nav-item dropdown">
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
                      <a class="dropdown-item" href="controlventa.php">
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
                <?php endif; ?>
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
                      <a class="dropdown-item" href="forgot-password.html">
                        <span>Cambio de password</span>
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
                    <a href="#" class="text-decoration-none">Inicio</a>
                  </li>
                  <li class="breadcrumb-item">Avisos</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <!-- Bandeja de Entrada -->
              <div class="col-12 col-lg-8">
                <div class="card mb-3">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                      <i class="icon-inbox me-2"></i>Bandeja de Entrada
                      <?php if ($total_no_leidos > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $total_no_leidos; ?></span>
                      <?php endif; ?>
                    </h5>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="filtrarAvisos('todos')">Todos</button>
                      <button type="button" class="btn btn-sm btn-outline-warning" onclick="filtrarAvisos('no_leidos')">No leídos</button>
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="filtrarAvisos('leidos')">Leídos</button>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover mb-0">
                        <thead class="table-light">
                          <tr>
                            <th width="50">Estado</th>
                            <th>Título</th>
                            <th>Emisor</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th width="120">Lectura</th>
                            <th width="100">Acciones</th>
                          </tr>
                        </thead>
                        <tbody id="tablaAvisos">
                          <?php if (empty($avisos)): ?>
                            <tr>
                              <td colspan="7" class="text-center py-4 text-muted">
                                <i class="icon-inbox fs-1 d-block mb-2"></i>
                                No tienes avisos
                              </td>
                            </tr>
                          <?php else: ?>
                            <?php foreach ($avisos as $aviso): ?>
                              <tr class="aviso-row <?php echo $aviso['tipo_aviso_usuario'] === 'recibido' ? ($aviso['leido'] ? 'table-light' : 'table-warning') : 'table-info'; ?>" 
                                  data-leido="<?php echo $aviso['leido'] ? 'true' : 'false'; ?>"
                                  data-aviso-id="<?php echo $aviso['id_aviso']; ?>"
                                  data-tipo-usuario="<?php echo $aviso['tipo_aviso_usuario']; ?>">
                                <td class="text-center">
                                  <?php if ($aviso['tipo_aviso_usuario'] === 'recibido'): ?>
                                    <?php if ($aviso['leido']): ?>
                                      <i class="icon-check-circle text-success" title="Leído"></i>
                                    <?php else: ?>
                                      <i class="icon-circle text-warning" title="No leído"></i>
                                    <?php endif; ?>
                                  <?php else: ?>
                                    <i class="icon-send text-info" title="Enviado por ti"></i>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <strong><?php echo htmlspecialchars($aviso['titulo']); ?></strong>
                                  <?php if ($aviso['tipo_aviso_usuario'] === 'enviado'): ?>
                                    <small class="badge bg-info ms-1">Enviado</small>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-<?php echo $aviso['tipo_aviso_usuario'] === 'enviado' ? 'success' : 'primary'; ?> rounded-circle d-flex align-items-center justify-content-center me-2">
                                      <span class="text-white fw-bold"><?php echo strtoupper(substr($aviso['emisor'], 0, 1)); ?></span>
                                    </div>
                                    <div>
                                      <div class="fw-semibold"><?php echo htmlspecialchars($aviso['emisor']); ?></div>
                                      <small class="text-muted"><?php echo htmlspecialchars($aviso['correo_emisor']); ?></small>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <span class="badge bg-<?php echo $aviso['tipo_aviso'] === 'IT' ? 'danger' : ($aviso['tipo_aviso'] === 'URGENTE' ? 'warning' : 'info'); ?>">
                                    <?php echo htmlspecialchars($aviso['tipo_aviso']); ?>
                                  </span>
                                </td>
                                <td>
                                  <small><?php echo date('d/m/Y H:i', strtotime($aviso['fecha_envio'])); ?></small>
                                </td>
                                <td>
                                  <?php if ($aviso['tipo_aviso_usuario'] === 'recibido'): ?>
                                    <small class="text-muted">-</small>
                                  <?php else: ?>
                                    <div class="text-center">
                                      <small class="text-success fw-bold"><?php echo $aviso['leidos_count']; ?>/<?php echo $aviso['total_destinatarios']; ?></small>
                                      <br>
                                      <small class="text-muted">leídos</small>
                                    </div>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <button class="btn btn-sm btn-outline-primary" onclick="verAviso(<?php echo $aviso['id_aviso']; ?>)">
                                    <i class="icon-eye"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Panel de Acciones -->
              <div class="col-12 col-lg-4">
                <!-- Crear Aviso -->
                <div class="card mb-3">
                  <div class="card-header">
                    <h5 class="card-title mb-0">
                      <i class="icon-plus-circle me-2"></i>Nuevo Aviso
                    </h5>
                  </div>
                  <div class="card-body">
                    <form id="formAviso" method="POST" action="controlador/guardar_aviso.php">
                      <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control" name="titulo" required>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label">Tipo de Aviso</label>
                        <select class="form-select" name="tipo_aviso" required>
                          <option value="">Seleccionar tipo</option>
                          <option value="INFORMATIVO">Informativo</option>
                          <option value="URGENTE">Urgente</option>
                          <option value="IT">Ticket IT</option>
                          <option value="GENERAL">General</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Mensaje</label>
                        <textarea class="form-control" name="mensaje" rows="4" required></textarea>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Destinatarios</label>
                        
                        <?php if (in_array($puesto, ['EJECUTIVO', 'GERENTE', 'DIRECTOR'])): ?>
                        <!-- Filtros especiales para EJECUTIVO, GERENTE, DIRECTOR -->
                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label small">Tipo de Selección</label>
                            <select class="form-select" id="selectionType">
                              <option value="individual">Selección Individual</option>
                              <option value="coordinador">Por Coordinador</option>
                              <option value="todos_asesores">Todos los Asesores de mi Plaza</option>
                            </select>
                          </div>
                          <div class="col-md-4" id="coordinadorFilter" style="display: none;">
                            <label class="form-label small">Seleccionar Coordinador</label>
                            <select class="form-select" id="coordinadorSelect">
                              <option value="">Selecciona un coordinador...</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small">Filtro por Sucursal</label>
                            <select class="form-select" id="branchFilter">
                              <option value="">Todas las sucursales</option>
                              <?php foreach ($sucursales as $sucursal): ?>
                                <option value="<?php echo htmlspecialchars($sucursal['sucursal']); ?>" 
                                        <?php echo $sucursal['sucursal'] === $sucursal_usuario ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($sucursal['sucursal']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        
                        <div class="row mb-2">
                          <div class="col-md-12">
                            <input type="text" class="form-control" id="searchRecipients" placeholder="Buscar por correo...">
                          </div>
                        </div>
                        <?php else: ?>
                        <!-- Filtros normales para otros puestos -->
                        <div class="row mb-2">
                          <div class="col-md-6">
                            <select class="form-select" id="branchFilter">
                              <option value="">Todas las sucursales</option>
                              <?php foreach ($sucursales as $sucursal): ?>
                                <option value="<?php echo htmlspecialchars($sucursal['sucursal']); ?>" 
                                        <?php echo $sucursal['sucursal'] === $sucursal_usuario ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($sucursal['sucursal']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-6">
                            <input type="text" class="form-control" id="searchRecipients" placeholder="Buscar por correo...">
                          </div>
                        </div>
                        <?php endif; ?>

                        <!-- Lista de empleados disponibles -->
                        <div class="card mb-3">
                          <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Empleados Disponibles</h6>
                            <div class="d-flex align-items-center gap-2">
                              <small class="text-muted" id="employeeCount">0 empleados</small>
                              <?php if (in_array($puesto, ['EJECUTIVO', 'GERENTE', 'DIRECTOR'])): ?>
                              <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-success" id="selectAllVisible" title="Seleccionar todos los visibles">
                                  <i class="icon-check"></i> Todos
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="clearSelection" title="Limpiar selección">
                                  <i class="icon-clear"></i> Limpiar
                                </button>
                              </div>
                              <?php endif; ?>
                            </div>
                          </div>
                          <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                            <div id="employeesList">
                              <!-- Los empleados aparecerán aquí -->
                            </div>
                          </div>
                        </div>

                        <!-- Lista de destinatarios seleccionados -->
                        <div class="card">
                          <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Destinatarios Seleccionados</h6>
                            <small class="text-muted" id="recipientCount">0 destinatarios</small>
                          </div>
                          <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;">
                            <div id="selectedRecipients">
                              <div class="text-muted text-center py-3">
                                <i class="icon-person fs-1 d-block mb-2"></i>
                                No hay destinatarios seleccionados
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <input type="hidden" name="destinatarios" id="hiddenDestinatarios" required>
                        <small class="form-text text-muted">Selecciona empleados de la lista superior para agregarlos como destinatarios. Busca por correo electrónico.</small>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Área (opcional)</label>
                        <input type="text" class="form-control" name="area" placeholder="Ej: Ventas, IT, Administración">
                      </div>

                      <button type="submit" class="btn btn-primary w-100" id="btnEnviarAviso">
                        <i class="icon-send me-2"></i>Enviar Aviso
                      </button>
                      
                      <!-- Test buttons for debugging -->
                      <!--<div class="row mt-2">
                        <div class="col-4">
                          <button type="button" class="btn btn-outline-info w-100" onclick="testConnection()">
                            <i class="icon-bug me-1"></i>Test Conexión
                          </button>
                        </div>
                        <div class="col-4">
                          <button type="button" class="btn btn-outline-warning w-100" onclick="testMarcarLeido()">
                            <i class="icon-check me-1"></i>Test Marcar Leído
                          </button>
                        </div>
                        <div class="col-4">
                          <button type="button" class="btn btn-outline-success w-100" onclick="testMarcarLeidoSimple()">
                            <i class="icon-test me-1"></i>Test Simple
                          </button>
                        </div>
                      </div>-->
                      
                      <!-- Loading state -->
                      <div id="loadingAviso" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                          <span class="visually-hidden">Enviando...</span>
                        </div>
                        <p class="mt-2 text-muted">Enviando aviso...</p>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Tickets IT -->
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">
                      <i class="icon-support_agent me-2"></i>Tickets IT
                    </h5>
                  </div>
                  <div class="card-body">
                    <p class="text-muted mb-3">¿Necesitas soporte técnico? Crea un ticket para el área de IT.</p>
                    <button class="btn btn-danger w-100" onclick="abrirTicketIT()">
                      <i class="icon-bug me-2"></i>Crear Ticket IT
                    </button>
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
    <!-- Modal para ver avisos -->
    <div class="modal fade" id="modalAviso" tabindex="-1" aria-labelledby="modalAvisoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAvisoLabel">Detalle del Aviso</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="contenidoAviso">
            <!-- Contenido del aviso se carga aquí -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Ticket IT -->
    <div class="modal fade" id="modalTicketIT" tabindex="-1" aria-labelledby="modalTicketITLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="modalTicketITLabel">
              <i class="icon-bug me-2"></i>Crear Ticket IT
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="formTicketIT" method="POST" action="controlador/guardar_ticket_it.php">
              <div class="mb-3">
                <label class="form-label">Título del Problema</label>
                <input type="text" class="form-control" name="titulo" required placeholder="Ej: Error en el sistema de ventas">
              </div>
              
              <div class="mb-3">
                <label class="form-label">Tipo de Problema</label>
                <select class="form-select" name="tipo_problema" required>
                  <option value="">Seleccionar tipo</option>
                  <option value="SOFTWARE">Software</option>
                  <option value="HARDWARE">Hardware</option>
                  <option value="RED">Red/Conectividad</option>
                  <option value="EMAIL">Correo Electrónico</option>
                  <option value="SISTEMA">Sistema</option>
                  <option value="OTRO">Otro</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Prioridad</label>
                <select class="form-select" name="prioridad" required>
                  <option value="BAJA">Baja</option>
                  <option value="MEDIA" selected>Media</option>
                  <option value="ALTA">Alta</option>
                  <option value="CRITICA">Crítica</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Descripción del Problema</label>
                <textarea class="form-control" name="descripcion" rows="5" required 
                          placeholder="Describe detalladamente el problema que estás experimentando..."></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Pasos para Reproducir (opcional)</label>
                <textarea class="form-control" name="pasos_reproducir" rows="3" 
                          placeholder="1. Paso uno...&#10;2. Paso dos...&#10;3. Resultado esperado vs resultado actual"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Información Adicional</label>
                <input type="text" class="form-control" name="info_adicional" 
                       placeholder="Ej: Navegador usado, versión del sistema, etc.">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" onclick="enviarTicketIT()">
              <i class="icon-send me-2"></i>Enviar Ticket
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de éxito para avisos -->
    <div class="modal fade" id="modalAvisoExitoso" tabindex="-1" aria-labelledby="modalAvisoExitosoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="modalAvisoExitosoLabel">
              <i class="icon-check-circle me-2"></i>Aviso Enviado Exitosamente
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div class="mb-4">
              <div class="icons-box lg bg-success rounded-circle mx-auto mb-3">
                <i class="icon-check text-white fs-1"></i>
              </div>
              <h4 class="text-success mb-2">¡Aviso Enviado!</h4>
              <p class="text-muted">El aviso ha sido enviado correctamente a los destinatarios seleccionados.</p>
            </div>
            
            <div class="card bg-light mb-3">
              <div class="card-body">
                <h6 class="card-title">Detalles del Aviso</h6>
                <div id="avisoDetalles">
                  <!-- Los detalles se llenarán dinámicamente -->
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
              <i class="icon-plus me-1"></i>Crear Otro Aviso
            </button>
            <a href="aviso.php" class="btn btn-primary">
              <i class="icon-arrow-left me-1"></i>Regresar a Avisos
            </a>
          </div>
        </div>
      </div>
    </div>

    <script>
    // Función para manejar el envío del formulario de avisos
    function handleAvisoSubmit(event) {
      event.preventDefault();
      
      const form = document.getElementById('formAviso');
      const btnEnviar = document.getElementById('btnEnviarAviso');
      const loading = document.getElementById('loadingAviso');
      
      // Mostrar loading
      btnEnviar.style.display = 'none';
      loading.style.display = 'block';
      
      // Preparar datos del formulario
      const formData = new FormData(form);
      
      // Enviar aviso
      fetch('controlador/guardar_aviso.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        // Verificar si la respuesta es JSON válido
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Respuesta del servidor no es JSON válido');
        }
        return response.text().then(text => {
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error('Error parsing JSON:', text);
            throw new Error('Error al procesar respuesta del servidor: ' + text.substring(0, 100));
          }
        });
      })
      .then(data => {
        // Ocultar loading
        loading.style.display = 'none';
        btnEnviar.style.display = 'block';
        
        if (data.success) {
          // Mostrar modal de éxito
          showSuccessModal(data);
          // Limpiar formulario
          form.reset();
          selectedRecipients = [];
          updateSelectedRecipients();
          updateEmployeesList();
        } else {
          // Mostrar error
          showErrorModal(data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        loading.style.display = 'none';
        btnEnviar.style.display = 'block';
        showErrorModal('Error: ' + error.message);
      });
    }

    // Función para mostrar modal de éxito
    function showSuccessModal(data) {
      const modal = new bootstrap.Modal(document.getElementById('modalAvisoExitoso'));
      
      // Llenar detalles del aviso
      document.getElementById('avisoDetalles').innerHTML = `
        <div class="row text-start">
          <div class="col-md-6">
            <strong>Título:</strong> ${data.aviso.titulo}<br>
            <strong>Tipo:</strong> <span class="badge bg-${getTipoBadgeColor(data.aviso.tipo)}">${data.aviso.tipo}</span><br>
            <strong>Destinatarios:</strong> ${data.aviso.destinatarios_count} persona(s)
          </div>
          <div class="col-md-6">
            <strong>Emisor:</strong> ${data.aviso.emisor}<br>
            <strong>Fecha:</strong> ${new Date(data.aviso.fecha).toLocaleString()}<br>
            <strong>ID:</strong> #${data.aviso.id}
          </div>
        </div>
      `;
      
      modal.show();
    }

    // Función para mostrar modal de error
    function showErrorModal(message) {
      // Crear modal de error dinámicamente
      const errorModal = `
        <div class="modal fade" id="modalError" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                  <i class="icon-alert-triangle me-2"></i>Error al Enviar Aviso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <div class="icons-box lg bg-danger rounded-circle mx-auto mb-3">
                  <i class="icon-x text-white fs-1"></i>
                </div>
                <h4 class="text-danger mb-2">Error</h4>
                <p class="text-muted">${message}</p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                  <i class="icon-check me-1"></i>Entendido
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Remover modal anterior si existe
      const existingModal = document.getElementById('modalError');
      if (existingModal) {
        existingModal.remove();
      }
      
      // Agregar nuevo modal
      document.body.insertAdjacentHTML('beforeend', errorModal);
      
      // Mostrar modal
      const modal = new bootstrap.Modal(document.getElementById('modalError'));
      modal.show();
      
      // Remover modal después de cerrar
      document.getElementById('modalError').addEventListener('hidden.bs.modal', function() {
        this.remove();
      });
    }

    // Función para obtener color del badge según tipo
    function getTipoBadgeColor(tipo) {
      switch(tipo) {
        case 'IT': return 'danger';
        case 'URGENTE': return 'warning';
        case 'INFORMATIVO': return 'info';
        default: return 'primary';
      }
    }

    // Función para probar la conexión
    function testConnection() {
      console.log('Probando conexión...');
      
      fetch('controlador/test_aviso.php')
        .then(response => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          return response.text();
        })
        .then(text => {
          console.log('Raw response:', text);
          try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            if (data.success) {
              alert('Conexión exitosa: ' + data.message);
            } else {
              alert('Error de conexión: ' + data.message);
            }
          } catch (e) {
            console.error('Error parsing JSON:', e);
            alert('Error: La respuesta no es JSON válido. Respuesta: ' + text.substring(0, 200));
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          alert('Error de red: ' + error.message);
        });
    }

    // Función para probar marcar como leído
    function testMarcarLeido() {
      // Buscar el primer aviso no leído
      const avisoNoLeido = document.querySelector('.aviso-row[data-leido="false"]');
      if (!avisoNoLeido) {
        alert('No hay avisos no leídos para probar');
        return;
      }
      
      const avisoId = avisoNoLeido.getAttribute('data-aviso-id');
      console.log('Probando marcar como leído el aviso:', avisoId);
      
      marcarComoLeido(avisoId);
    }

    // Función para probar marcar como leído (versión simple)
    function testMarcarLeidoSimple() {
      console.log('Probando endpoint simple de marcar leído...');
      
      fetch('controlador/test_marcar_leido.php')
        .then(response => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          return response.text();
        })
        .then(text => {
          console.log('Raw response:', text);
          try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            if (data.success) {
              alert('Test simple exitoso: ' + data.message);
            } else {
              alert('Error en test simple: ' + data.message);
            }
          } catch (e) {
            console.error('Error parsing JSON:', e);
            alert('Error: La respuesta no es JSON válido. Respuesta: ' + text.substring(0, 200));
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          alert('Error de red: ' + error.message);
        });
    }

    // Función para ver avisos
    function verAviso(id) {
      console.log('Cargando aviso ID:', id);
      
      fetch("controlador/ver_aviso.php?id=" + id)
        .then(response => {
          console.log('Response status:', response.status);
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta del servidor no es JSON válido');
          }
          return response.text().then(text => {
            try {
              return JSON.parse(text);
            } catch (e) {
              console.error('Error parsing JSON:', text);
              throw new Error('Error al procesar respuesta del servidor: ' + text.substring(0, 100));
            }
          });
        })
        .then(data => {
          console.log('Aviso data:', data);
          if (data.success) {
            const modal = new bootstrap.Modal(document.getElementById('modalAviso'));
            document.getElementById('modalAvisoLabel').textContent = data.aviso.titulo;
            // Determinar si es un aviso enviado o recibido
            const esEnviado = data.aviso.destinatarios && data.aviso.destinatarios.length > 0;
            
            let destinatariosHtml = '';
            if (esEnviado && data.aviso.destinatarios) {
              destinatariosHtml = `
                <div class="mt-3">
                  <strong>Destinatarios (${data.aviso.destinatarios.length}):</strong>
                  <div class="mt-2">
                    ${data.aviso.destinatarios.map(dest => `
                      <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div>
                          <strong>${dest.nombre}</strong> - ${dest.puesto}
                          <br><small class="text-muted">${dest.correo}</small>
                        </div>
                        <div class="text-end">
                          ${dest.leido ? 
                            '<span class="badge bg-success">Leído</span>' : 
                            '<span class="badge bg-warning">No leído</span>'
                          }
                          ${dest.fecha_lectura ? `<br><small class="text-muted">${dest.fecha_lectura}</small>` : ''}
                        </div>
                      </div>
                    `).join('')}
                  </div>
                </div>
              `;
            }
            
            document.getElementById('contenidoAviso').innerHTML = `
              <div class="row">
                <div class="col-md-6">
                  <strong>Emisor:</strong> ${data.aviso.emisor}<br>
                  <strong>Email:</strong> ${data.aviso.correo_emisor}<br>
                  <strong>Tipo:</strong> <span class="badge bg-${data.aviso.tipo_aviso === 'IT' ? 'danger' : (data.aviso.tipo_aviso === 'URGENTE' ? 'warning' : 'info')}">${data.aviso.tipo_aviso}</span>
                </div>
                <div class="col-md-6">
                  <strong>Fecha:</strong> ${data.aviso.fecha_envio}<br>
                  <strong>Área:</strong> ${data.aviso.area || 'No especificada'}<br>
                  <strong>Estado:</strong> ${data.aviso.leido ? '<span class="text-success">Leído</span>' : '<span class="text-warning">No leído</span>'}
                </div>
              </div>
              <hr>
              <div class="mt-3">
                <strong>Mensaje:</strong>
                <div class="mt-2 p-3 bg-light rounded">
                  ${data.aviso.mensaje.replace(/\n/g, '<br>')}
                </div>
              </div>
              ${destinatariosHtml}
            `;
            modal.show();
            
            // Marcar como leído si no lo está
            if (!data.aviso.leido) {
              marcarComoLeido(id);
            }
          } else {
            showErrorModal('Error al cargar el aviso: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showErrorModal('Error al cargar el aviso: ' + error.message);
        });
    }

    // Función para marcar aviso como leído
    function marcarComoLeido(idAviso) {
      console.log('Marcando aviso como leído:', idAviso);
      
      fetch("controlador/marcar_leido.php", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({id_aviso: idAviso})
      })
      .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        return response.text().then(text => {
          console.log('Raw response from marcar_leido.php:', text);
          
          // Intentar parsear como JSON independientemente del Content-Type
          try {
            const data = JSON.parse(text);
            console.log('Successfully parsed JSON:', data);
            return data;
          } catch (e) {
            console.error('Error parsing JSON:', e);
            // Si no es JSON válido, mostrar el error
            if (!contentType || !contentType.includes('application/json')) {
              throw new Error('Respuesta del servidor no es JSON válido. Content-Type: ' + contentType + '. Respuesta: ' + text.substring(0, 200));
            } else {
              throw new Error('Error al procesar respuesta del servidor: ' + text.substring(0, 200));
            }
          }
        });
      })
      .then(data => {
        console.log('Marcar leído response:', data);
        if (data.success) {
          // Actualizar la fila en la tabla
          const fila = document.querySelector(`tr[data-aviso-id="${idAviso}"]`);
          if (fila) {
            fila.classList.remove('table-warning');
            fila.classList.add('table-light');
            
            // Buscar el icono correctamente
            const iconElement = fila.querySelector('.icon-circle, .icon-check-circle');
            if (iconElement) {
              iconElement.className = 'icon-check-circle text-success';
              iconElement.title = 'Leído';
            }
            
            fila.setAttribute('data-leido', 'true');
            console.log('Aviso marcado como leído en la interfaz');
          }
          // Actualizar contador
          actualizarContador();
        } else {
          console.error('Error al marcar como leído:', data.message);
        }
      })
      .catch(error => {
        console.error('Error marcando como leído:', error);
      });
    }

    // Función para filtrar avisos
    function filtrarAvisos(filtro) {
      const filas = document.querySelectorAll('.aviso-row');
      filas.forEach(fila => {
        const leido = fila.getAttribute('data-leido') === 'true';
        let mostrar = true;
        
        switch(filtro) {
          case 'no_leidos':
            mostrar = !leido;
            break;
          case 'leidos':
            mostrar = leido;
            break;
          case 'todos':
          default:
            mostrar = true;
            break;
        }
        
        fila.style.display = mostrar ? '' : 'none';
      });
      
      // Actualizar botones activos
      document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');
    }

    // Función para abrir modal de Ticket IT
    function abrirTicketIT() {
      const modal = new bootstrap.Modal(document.getElementById('modalTicketIT'));
      modal.show();
    }

    // Función para enviar Ticket IT
    function enviarTicketIT() {
      const form = document.getElementById('formTicketIT');
      const formData = new FormData(form);
      
      fetch("controlador/guardar_ticket_it.php", {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Ticket IT enviado correctamente');
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalTicketIT'));
          modal.hide();
          form.reset();
        } else {
          alert('Error al enviar el ticket: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el ticket');
      });
    }

    // Función para actualizar contador de no leídos
    function actualizarContador() {
      const noLeidos = document.querySelectorAll('.aviso-row[data-leido="false"]').length;
      const badge = document.querySelector('.badge.bg-danger');
      if (noLeidos > 0) {
        if (badge) {
          badge.textContent = noLeidos;
        } else {
          const header = document.querySelector('.card-header h5');
          header.innerHTML += ` <span class="badge bg-danger ms-2">${noLeidos}</span>`;
        }
      } else if (badge) {
        badge.remove();
      }
    }

    // Datos de empleados para búsqueda
    const empleados = <?php echo json_encode($empleados); ?>;
    const sucursalUsuario = '<?php echo $sucursal_usuario; ?>';
    let selectedRecipients = [];
    let filteredEmployees = [...empleados];

    // Debug: Mostrar empleados en consola
    console.log('Empleados cargados:', empleados);
    console.log('Total empleados:', empleados.length);
    console.log('Sucursal del usuario:', sucursalUsuario);
    
    // Verificar si hay empleados
    if (empleados.length === 0) {
      console.error('No se encontraron empleados en la base de datos');
    } else {
      console.log('Primer empleado:', empleados[0]);
    }

    // Función para filtrar empleados por sucursal y búsqueda
    function filterEmployees() {
      const branchFilter = document.getElementById('branchFilter').value;
      const searchTerm = document.getElementById('searchRecipients').value.toLowerCase();
      
      console.log('Filtrando empleados - Branch:', branchFilter, 'Search:', searchTerm);
      
      filteredEmployees = empleados.filter(empleado => {
        const matchesBranch = !branchFilter || empleado.sucursal === branchFilter;
        const matchesSearch = !searchTerm || 
          empleado.correo.toLowerCase().includes(searchTerm);
        
        return matchesBranch && matchesSearch;
      });
      
      console.log('Empleados filtrados:', filteredEmployees.length);
      updateEmployeesList();
    }

    // Función para actualizar la lista de empleados
    function updateEmployeesList() {
      const employeesList = document.getElementById('employeesList');
      const employeeCount = document.getElementById('employeeCount');
      
      employeeCount.textContent = `${filteredEmployees.length} empleados`;
      
      if (filteredEmployees.length === 0) {
        employeesList.innerHTML = '<div class="text-muted text-center py-3">No hay empleados disponibles</div>';
        return;
      }

      employeesList.innerHTML = filteredEmployees.map(empleado => {
        const isSelected = selectedRecipients.find(r => r.id === empleado.id);
        return `
          <div class="employee-item d-flex align-items-center justify-content-between p-2 border-bottom ${isSelected ? 'bg-light' : ''}" 
               data-id="${empleado.id}" data-name="${empleado.nombre}" data-email="${empleado.correo}" data-puesto="${empleado.puesto}" data-sucursal="${empleado.sucursal}">
            <div class="d-flex align-items-center">
              <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                <span class="text-white fw-bold">${empleado.nombre.charAt(0).toUpperCase()}</span>
              </div>
              <div>
                <div class="fw-semibold">${empleado.nombre}</div>
                <small class="text-muted">${empleado.correo} - ${empleado.puesto}</small>
                <small class="badge bg-secondary ms-1">${empleado.sucursal}</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm ${isSelected ? 'btn-outline-danger' : 'btn-outline-primary'}" 
                    onclick="${isSelected ? 'removeRecipient' : 'addRecipient'}(${empleado.id})">
              <i class="icon-${isSelected ? 'remove' : 'add'}"></i>
              ${isSelected ? 'Quitar' : 'Agregar'}
            </button>
          </div>
        `;
      }).join('');
    }

    // Función para agregar destinatario
    function addRecipient(empleadoId) {
      const empleado = empleados.find(e => e.id == empleadoId);
      if (!empleado || selectedRecipients.find(r => r.id == empleadoId)) {
        return; // Ya está seleccionado o no existe
      }

      selectedRecipients.push(empleado);
      updateSelectedRecipients();
      updateHiddenInput();
      updateEmployeesList(); // Actualizar la lista para mostrar el estado seleccionado
    }

    // Función para remover destinatario
    function removeRecipient(empleadoId) {
      selectedRecipients = selectedRecipients.filter(r => r.id != empleadoId);
      updateSelectedRecipients();
      updateHiddenInput();
      updateEmployeesList(); // Actualizar la lista para mostrar el estado no seleccionado
    }

    // Función para actualizar la visualización de destinatarios seleccionados
    function updateSelectedRecipients() {
      const container = document.getElementById('selectedRecipients');
      const recipientCount = document.getElementById('recipientCount');
      
      recipientCount.textContent = `${selectedRecipients.length} destinatarios`;
      
      // Agregar indicador visual para selección masiva
      <?php if (in_array($puesto, ['EJECUTIVO', 'GERENTE', 'DIRECTOR'])): ?>
      if (selectedRecipients.length > 5) {
        recipientCount.innerHTML = `${selectedRecipients.length} destinatarios <span class="selection-indicator">Selección Masiva</span>`;
      }
      <?php endif; ?>
      
      if (selectedRecipients.length === 0) {
        container.innerHTML = `
          <div class="text-muted text-center py-3">
            <i class="icon-person fs-1 d-block mb-2"></i>
            No hay destinatarios seleccionados
          </div>
        `;
        return;
      }

      container.innerHTML = selectedRecipients.map(empleado => `
        <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
          <div class="d-flex align-items-center">
            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
              <span class="text-white fw-bold">${empleado.nombre.charAt(0).toUpperCase()}</span>
            </div>
            <div>
              <div class="fw-semibold">${empleado.nombre}</div>
              <small class="text-muted">${empleado.correo} - ${empleado.puesto}</small>
              <small class="badge bg-secondary ms-1">${empleado.sucursal}</small>
            </div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRecipient(${empleado.id})">
            <i class="icon-remove"></i> Quitar
          </button>
        </div>
      `).join('');
    }

    // Función para actualizar el input hidden
    function updateHiddenInput() {
      const hiddenInput = document.getElementById('hiddenDestinatarios');
      hiddenInput.value = selectedRecipients.map(r => r.id).join(',');
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchRecipients');
      const branchFilter = document.getElementById('branchFilter');
      const formAviso = document.getElementById('formAviso');

      // Manejar envío del formulario de avisos
      formAviso.addEventListener('submit', handleAvisoSubmit);

      // Filtrar empleados cuando cambia la sucursal
      branchFilter.addEventListener('change', filterEmployees);

      // Búsqueda en tiempo real
      searchInput.addEventListener('input', filterEmployees);

      // Cargar empleados inicialmente
      filterEmployees();

      // Configurar tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Funcionalidad especial para EJECUTIVO, GERENTE, DIRECTOR
      <?php if (in_array($puesto, ['EJECUTIVO', 'GERENTE', 'DIRECTOR'])): ?>
      const selectionType = document.getElementById('selectionType');
      const coordinadorFilter = document.getElementById('coordinadorFilter');
      const coordinadorSelect = document.getElementById('coordinadorSelect');
      const selectAllVisible = document.getElementById('selectAllVisible');
      const clearSelection = document.getElementById('clearSelection');

      // Cargar coordinadores de la plaza del usuario
      loadCoordinadores();

      // Manejar cambio de tipo de selección
      selectionType.addEventListener('change', function() {
        const tipo = this.value;
        
        if (tipo === 'coordinador') {
          coordinadorFilter.style.display = 'block';
          loadCoordinadores();
        } else {
          coordinadorFilter.style.display = 'none';
          if (tipo === 'todos_asesores') {
            selectAllAsesoresPlaza();
          } else {
            filterEmployees();
          }
        }
      });

      // Manejar selección de coordinador
      coordinadorSelect.addEventListener('change', function() {
        const coordinadorId = this.value;
        if (coordinadorId) {
          selectAsesoresCoordinador(coordinadorId);
        }
      });

      // Botón seleccionar todos los visibles
      selectAllVisible.addEventListener('click', function() {
        selectAllVisibleEmployees();
      });

      // Botón limpiar selección
      clearSelection.addEventListener('click', function() {
        selectedRecipients = [];
        updateSelectedRecipients();
        updateHiddenInput();
        updateEmployeesList();
      });

      // Función para cargar coordinadores de la plaza
      function loadCoordinadores() {
        const coordinadores = empleados.filter(emp => 
          emp.puesto === 'COORDINADOR' && emp.sucursal === sucursalUsuario
        );
        
        coordinadorSelect.innerHTML = '<option value="">Selecciona un coordinador...</option>';
        coordinadores.forEach(coord => {
          const option = document.createElement('option');
          option.value = coord.id;
          option.textContent = `${coord.nombre} - ${coord.correo}`;
          coordinadorSelect.appendChild(option);
        });
      }

      // Función para seleccionar todos los asesores de un coordinador
      function selectAsesoresCoordinador(coordinadorId) {
        // Limpiar selección actual
        selectedRecipients = [];
        
        // Mostrar loading
        const loadingHtml = `
          <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <div class="mt-2">Cargando asesores del coordinador...</div>
          </div>
        `;
        document.getElementById('employeesList').innerHTML = loadingHtml;
        
        // Obtener asesores del coordinador desde el servidor
        fetch('controlador/get_asesores_coordinador.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `coordinador_id=${coordinadorId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Agregar todos los asesores a la selección
            data.asesores.forEach(asesor => {
              selectedRecipients.push(asesor);
            });
            
            updateSelectedRecipients();
            updateHiddenInput();
            updateEmployeesList();
            
            // Mostrar mensaje de éxito
            console.log(`Seleccionados ${data.total} asesores del coordinador ${data.coordinador.nombre}`);
          } else {
            console.error('Error al cargar asesores:', data.message);
            // Fallback: seleccionar todos los asesores de la sucursal
            const asesores = empleados.filter(emp => 
              emp.puesto === 'ASESOR' && emp.sucursal === sucursalUsuario
            );
            
            asesores.forEach(asesor => {
              selectedRecipients.push(asesor);
            });
            
            updateSelectedRecipients();
            updateHiddenInput();
            updateEmployeesList();
          }
        })
        .catch(error => {
          console.error('Error en la petición:', error);
          // Fallback: seleccionar todos los asesores de la sucursal
          const asesores = empleados.filter(emp => 
            emp.puesto === 'ASESOR' && emp.sucursal === sucursalUsuario
          );
          
          asesores.forEach(asesor => {
            selectedRecipients.push(asesor);
          });
          
          updateSelectedRecipients();
          updateHiddenInput();
          updateEmployeesList();
        });
      }

      // Función para seleccionar todos los asesores de la plaza
      function selectAllAsesoresPlaza() {
        selectedRecipients = [];
        
        const asesores = empleados.filter(emp => 
          emp.puesto === 'ASESOR' && emp.sucursal === sucursalUsuario
        );
        
        asesores.forEach(asesor => {
          selectedRecipients.push(asesor);
        });
        
        updateSelectedRecipients();
        updateHiddenInput();
        updateEmployeesList();
      }

      // Función para seleccionar todos los empleados visibles
      function selectAllVisibleEmployees() {
        filteredEmployees.forEach(emp => {
          if (!selectedRecipients.find(r => r.id == emp.id)) {
            selectedRecipients.push(emp);
          }
        });
        
        updateSelectedRecipients();
        updateHiddenInput();
        updateEmployeesList();
      }
      <?php endif; ?>
    });
    </script>

    <!-- *************
			************ Vendor Js Files *************
		************* -->

    <!-- Overlay Scroll JS -->
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>
  </body>

</html>