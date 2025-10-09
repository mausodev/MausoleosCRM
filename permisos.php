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

// Procesar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_rol':
            $nombre = trim($_POST['nombre'] ?? '');
            if (!empty($nombre)) {
                $stmt = $con->prepare("INSERT INTO rol (nombre) VALUES (?)");
                $stmt->bind_param("s", $nombre);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Rol creado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear el rol']);
                }
                $stmt->close();
            }
            exit;
            
        case 'update_rol':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            if ($id > 0 && !empty($nombre)) {
                $stmt = $con->prepare("UPDATE rol SET nombre = ? WHERE id = ?");
                $stmt->bind_param("si", $nombre, $id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Rol actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el rol']);
                }
                $stmt->close();
            }
            exit;
            
        case 'delete_rol':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                // Verificar si el rol tiene permisos asignados
                $check = $con->prepare("SELECT COUNT(*) FROM permiso WHERE id_rol = ?");
                $check->bind_param("i", $id);
                $check->execute();
                $result = $check->get_result();
                $count = $result->fetch_row()[0];
                $check->close();
                
                if ($count > 0) {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el rol porque tiene permisos asignados']);
                } else {
                    $stmt = $con->prepare("DELETE FROM rol WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Rol eliminado exitosamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al eliminar el rol']);
                    }
                    $stmt->close();
                }
            }
            exit;
            
        case 'create_pantalla':
            $nombre = trim($_POST['nombre'] ?? '');
            $ruta = trim($_POST['ruta'] ?? '');
            if (!empty($nombre) && !empty($ruta)) {
                $stmt = $con->prepare("INSERT INTO pantalla (nombre, ruta) VALUES (?, ?)");
                $stmt->bind_param("ss", $nombre, $ruta);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Pantalla creada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear la pantalla']);
                }
                $stmt->close();
            }
            exit;
            
        case 'update_pantalla':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $ruta = trim($_POST['ruta'] ?? '');
            if ($id > 0 && !empty($nombre) && !empty($ruta)) {
                $stmt = $con->prepare("UPDATE pantalla SET nombre = ?, ruta = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nombre, $ruta, $id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Pantalla actualizada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la pantalla']);
                }
                $stmt->close();
            }
            exit;
            
        case 'delete_pantalla':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                // Verificar si la pantalla tiene permisos asignados
                $check = $con->prepare("SELECT COUNT(*) FROM permiso WHERE id_pantalla = ?");
                $check->bind_param("i", $id);
                $check->execute();
                $result = $check->get_result();
                $count = $result->fetch_row()[0];
                $check->close();
                
                if ($count > 0) {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar la pantalla porque tiene permisos asignados']);
                } else {
                    $stmt = $con->prepare("DELETE FROM pantalla WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Pantalla eliminada exitosamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al eliminar la pantalla']);
                    }
                    $stmt->close();
                }
            }
            exit;
            
        case 'update_permiso':
            $id_rol = intval($_POST['id_rol'] ?? 0);
            $id_pantalla = intval($_POST['id_pantalla'] ?? 0);
            $puede_ver = intval($_POST['puede_ver'] ?? 0);
            $puede_editar = intval($_POST['puede_editar'] ?? 0);
            
            if ($id_rol > 0 && $id_pantalla > 0) {
                // Verificar si ya existe el permiso
                $check = $con->prepare("SELECT id FROM permiso WHERE id_rol = ? AND id_pantalla = ?");
                $check->bind_param("ii", $id_rol, $id_pantalla);
                $check->execute();
                $result = $check->get_result();
                $existing = $result->fetch_assoc();
                $check->close();
                
                if ($existing) {
                    // Actualizar permiso existente
                    $stmt = $con->prepare("UPDATE permiso SET puede_ver = ?, puede_editar = ? WHERE id = ?");
                    $stmt->bind_param("iii", $puede_ver, $puede_editar, $existing['id']);
                } else {
                    // Crear nuevo permiso
                    $stmt = $con->prepare("INSERT INTO permiso (id_rol, id_pantalla, puede_ver, puede_editar) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiii", $id_rol, $id_pantalla, $puede_ver, $puede_editar);
                }
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Permiso actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el permiso']);
                }
                $stmt->close();
            }
            exit;
    }
}

// Obtener datos para mostrar
$roles = [];
$pantallas = [];
$permisos = [];

// Obtener roles
$result = $con->query("SELECT * FROM rol ORDER BY nombre");
if ($result) {
    $roles = $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener pantallas
$result = $con->query("SELECT * FROM pantalla ORDER BY nombre");
if ($result) {
    $pantallas = $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener permisos
$result = $con->query("
    SELECT p.*, r.nombre as rol_nombre, pa.nombre as pantalla_nombre 
    FROM permiso p 
    LEFT JOIN rol r ON p.id_rol = r.id 
    LEFT JOIN pantalla pa ON p.id_pantalla = pa.id 
    ORDER BY r.nombre, pa.nombre
");
if ($result) {
    $permisos = $result->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Permisos - Sistema de Administración</title>

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
                <?php if ($puesto !== 'ASESOR'): ?>
                <li class="nav-item ">
                  <a class="nav-link" href="leds.php">
                    <i class="fs-3 icon-contacts"></i>Leads Digitales
                  </a>
                </li>
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-package"></i>Configuracion
                  </a>
                 <ul class="dropdown-menu dropdown-megamenu">
                    <li>
                      <a class="dropdown-item" href="account-settings.php">
                        <span>Control de usuario</span></a>
                    </li>
                    <?php if (in_array($puesto, ['DIRECTOR','GERENTE', 'EJECUTIVO', 'COORDINADOR'])): ?>
                    <li>
                      <a class="dropdown-item" href="admin_embudo_plaza.php">
                        <span>Porcentajes por Etapa</span></a>
                    </li>
                    <?php endif; ?>
                    <?php if (in_array($puesto, ['DIRECTOR','SISTEMAS'])): ?>
                    <li>
                      <a class="dropdown-item current-page" href="permisos.php">
                        <span>Gestión de Permisos</span></a>
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
                    <a href="index.html" class="text-decoration-none">Home</a>
                  </li>
                  <li class="breadcrumb-item">Configuración</li>
                  <li class="breadcrumb-item active">Gestión de Permisos</li>
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
                    <h5 class="card-title">Gestión de Permisos del Sistema</h5>
                  </div>
                  <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="permisosTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
                          <i class="icon-user"></i> Roles
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pantallas-tab" data-bs-toggle="tab" data-bs-target="#pantallas" type="button" role="tab">
                          <i class="icon-monitor"></i> Pantallas
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="permisos-tab" data-bs-toggle="tab" data-bs-target="#permisos" type="button" role="tab">
                          <i class="icon-lock"></i> Matriz de Permisos
                        </button>
                      </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="permisosTabsContent">
                      <!-- Roles Tab -->
                      <div class="tab-pane fade show active" id="roles" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                          <h6>Gestión de Roles</h6>
                          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRol">
                            <i class="icon-plus"></i> Nuevo Rol
                          </button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-hover">
                            <thead>
                              <tr>
                                <th>ID</th>
                                <th>Nombre del Rol</th>
                                <th>Acciones</th>
                              </tr>
                            </thead>
                            <tbody id="rolesTableBody">
                              <?php foreach ($roles as $rol): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($rol['id']); ?></td>
                                <td><?php echo htmlspecialchars($rol['nombre']); ?></td>
                                <td>
                                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="editRol(<?php echo $rol['id']; ?>, '<?php echo htmlspecialchars($rol['nombre']); ?>')">
                                    <i class="icon-edit"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRol(<?php echo $rol['id']; ?>, '<?php echo htmlspecialchars($rol['nombre']); ?>')">
                                    <i class="icon-delete"></i>
                                  </button>
                                </td>
                              </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <!-- Pantallas Tab -->
                      <div class="tab-pane fade" id="pantallas" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                          <h6>Gestión de Pantallas</h6>
                          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPantalla">
                            <i class="icon-plus"></i> Nueva Pantalla
                          </button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-hover">
                            <thead>
                              <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Ruta</th>
                                <th>Acciones</th>
                              </tr>
                            </thead>
                            <tbody id="pantallasTableBody">
                              <?php foreach ($pantallas as $pantalla): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($pantalla['id']); ?></td>
                                <td><?php echo htmlspecialchars($pantalla['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($pantalla['ruta']); ?></td>
                                <td>
                                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPantalla(<?php echo $pantalla['id']; ?>, '<?php echo htmlspecialchars($pantalla['nombre']); ?>', '<?php echo htmlspecialchars($pantalla['ruta']); ?>')">
                                    <i class="icon-edit"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePantalla(<?php echo $pantalla['id']; ?>, '<?php echo htmlspecialchars($pantalla['nombre']); ?>')">
                                    <i class="icon-delete"></i>
                                  </button>
                                </td>
                              </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <!-- Permisos Tab -->
                      <div class="tab-pane fade" id="permisos" role="tabpanel">
                        <div class="mb-3 mt-3">
                          <h6>Matriz de Permisos</h6>
                          <p class="text-muted">Configure los permisos de cada rol para cada pantalla del sistema.</p>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                              <tr>
                                <th>Rol</th>
                                <?php foreach ($pantallas as $pantalla): ?>
                                <th class="text-center" title="<?php echo htmlspecialchars($pantalla['ruta']); ?>">
                                  <?php echo htmlspecialchars($pantalla['nombre']); ?>
                                </th>
                                <?php endforeach; ?>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($roles as $rol): ?>
                              <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($rol['nombre']); ?></td>
                                <?php foreach ($pantallas as $pantalla): ?>
                                <td class="text-center">
                                  <?php
                                  $permiso = null;
                                  foreach ($permisos as $p) {
                                    if ($p['id_rol'] == $rol['id'] && $p['id_pantalla'] == $pantalla['id']) {
                                      $permiso = $p;
                                      break;
                                    }
                                  }
                                  ?>
                                  <div class="btn-group" role="group">
                                    <input type="checkbox" class="btn-check" id="ver_<?php echo $rol['id']; ?>_<?php echo $pantalla['id']; ?>" 
                                           <?php echo ($permiso && $permiso['puede_ver']) ? 'checked' : ''; ?>
                                           onchange="updatePermiso(<?php echo $rol['id']; ?>, <?php echo $pantalla['id']; ?>, 'ver', this.checked)">
                                    <label class="btn btn-outline-success btn-sm" for="ver_<?php echo $rol['id']; ?>_<?php echo $pantalla['id']; ?>">
                                      <i class="icon-eye"></i>
                                    </label>
                                    
                                    <input type="checkbox" class="btn-check" id="editar_<?php echo $rol['id']; ?>_<?php echo $pantalla['id']; ?>" 
                                           <?php echo ($permiso && $permiso['puede_editar']) ? 'checked' : ''; ?>
                                           onchange="updatePermiso(<?php echo $rol['id']; ?>, <?php echo $pantalla['id']; ?>, 'editar', this.checked)">
                                    <label class="btn btn-outline-warning btn-sm" for="editar_<?php echo $rol['id']; ?>_<?php echo $pantalla['id']; ?>">
                                      <i class="icon-edit"></i>
                                    </label>
                                  </div>
                                </td>
                                <?php endforeach; ?>
                              </tr>
                              <?php endforeach; ?>
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

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>

    <!-- Modals -->
    <!-- Modal para Rol -->
    <div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalRolLabel">Nuevo Rol</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="formRol">
            <div class="modal-body">
              <input type="hidden" id="rolId" name="id">
              <div class="mb-3">
                <label for="rolNombre" class="form-label">Nombre del Rol</label>
                <input type="text" class="form-control" id="rolNombre" name="nombre" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal para Pantalla -->
    <div class="modal fade" id="modalPantalla" tabindex="-1" aria-labelledby="modalPantallaLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPantallaLabel">Nueva Pantalla</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="formPantalla">
            <div class="modal-body">
              <input type="hidden" id="pantallaId" name="id">
              <div class="mb-3">
                <label for="pantallaNombre" class="form-label">Nombre de la Pantalla</label>
                <input type="text" class="form-control" id="pantallaNombre" name="nombre" required>
              </div>
              <div class="mb-3">
                <label for="pantallaRuta" class="form-label">Ruta del Archivo</label>
                <input type="text" class="form-control" id="pantallaRuta" name="ruta" placeholder="ejemplo.php" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- JavaScript para funcionalidad CRUD -->
    <script>
    // Variables globales
    let currentAction = 'create';

    // Funciones para Roles
    function editRol(id, nombre) {
        currentAction = 'update';
        document.getElementById('modalRolLabel').textContent = 'Editar Rol';
        document.getElementById('rolId').value = id;
        document.getElementById('rolNombre').value = nombre;
        new bootstrap.Modal(document.getElementById('modalRol')).show();
    }

    function deleteRol(id, nombre) {
        if (confirm(`¿Está seguro de que desea eliminar el rol "${nombre}"?`)) {
            const formData = new FormData();
            formData.append('action', 'delete_rol');
            formData.append('id', id);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error al procesar la solicitud');
            });
        }
    }

    // Funciones para Pantallas
    function editPantalla(id, nombre, ruta) {
        currentAction = 'update';
        document.getElementById('modalPantallaLabel').textContent = 'Editar Pantalla';
        document.getElementById('pantallaId').value = id;
        document.getElementById('pantallaNombre').value = nombre;
        document.getElementById('pantallaRuta').value = ruta;
        new bootstrap.Modal(document.getElementById('modalPantalla')).show();
    }

    function deletePantalla(id, nombre) {
        if (confirm(`¿Está seguro de que desea eliminar la pantalla "${nombre}"?`)) {
            const formData = new FormData();
            formData.append('action', 'delete_pantalla');
            formData.append('id', id);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error al procesar la solicitud');
            });
        }
    }

    // Función para actualizar permisos
    function updatePermiso(idRol, idPantalla, tipo, valor) {
        const formData = new FormData();
        formData.append('action', 'update_permiso');
        formData.append('id_rol', idRol);
        formData.append('id_pantalla', idPantalla);
        formData.append('puede_ver', document.getElementById(`ver_${idRol}_${idPantalla}`).checked ? 1 : 0);
        formData.append('puede_editar', document.getElementById(`editar_${idRol}_${idPantalla}`).checked ? 1 : 0);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
                // Revertir el cambio si falló
                document.getElementById(`${tipo}_${idRol}_${idPantalla}`).checked = !valor;
            }
        })
        .catch(error => {
            showAlert('danger', 'Error al procesar la solicitud');
            // Revertir el cambio si falló
            document.getElementById(`${tipo}_${idRol}_${idPantalla}`).checked = !valor;
        });
    }

    // Función para mostrar alertas
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    // Event listeners para formularios
    document.getElementById('formRol').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', currentAction === 'create' ? 'create_rol' : 'update_rol');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('modalRol')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error al procesar la solicitud');
        });
    });

    document.getElementById('formPantalla').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', currentAction === 'create' ? 'create_pantalla' : 'update_pantalla');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('modalPantalla')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error al procesar la solicitud');
        });
    });

    // Resetear formularios cuando se abren los modales
    document.getElementById('modalRol').addEventListener('show.bs.modal', function() {
        if (currentAction === 'create') {
            document.getElementById('modalRolLabel').textContent = 'Nuevo Rol';
            document.getElementById('formRol').reset();
            document.getElementById('rolId').value = '';
        }
    });

    document.getElementById('modalPantalla').addEventListener('show.bs.modal', function() {
        if (currentAction === 'create') {
            document.getElementById('modalPantallaLabel').textContent = 'Nueva Pantalla';
            document.getElementById('formPantalla').reset();
            document.getElementById('pantallaId').value = '';
        }
    });

    // Resetear action cuando se cierran los modales
    document.getElementById('modalRol').addEventListener('hidden.bs.modal', function() {
        currentAction = 'create';
    });

    document.getElementById('modalPantalla').addEventListener('hidden.bs.modal', function() {
        currentAction = 'create';
    });
    </script>
  </body>

</html>