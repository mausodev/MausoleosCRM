<?php
require './controlador/conexion.php';

function tienePermiso($idRol, $rutaPantalla, $accion = 'puede_ver') {
  global $con; // conexión a MySQL
  
  $stmt = $con->prepare("
      SELECT p.$accion 
      FROM permiso p
      INNER JOIN pantalla pan ON p.id_pantalla = pan.id
      WHERE p.id_rol = ? AND pan.ruta = ?
      LIMIT 1
  ");
  $stmt->bind_param("is", $idRol, $rutaPantalla);
  $stmt->execute();
  $stmt->bind_result($permitido);
  $stmt->fetch();
  return $permitido == 1;
}

function obtenerPermisosPantalla($idRol, $rutaPantalla) {
  global $con;

  $sql = "
      SELECT s.nombre, ps.puede_ver, ps.puede_editar
      FROM permiso_seccion ps
      INNER JOIN pantalla pan ON ps.id_pantalla = pan.id
      INNER JOIN seccion s ON ps.id_seccion = s.id
      WHERE ps.id_rol = ? AND pan.ruta = ?
  ";

  $stmt = $con->prepare($sql);
  $stmt->bind_param("is", $idRol, $rutaPantalla);
  $stmt->execute();
  $result = $stmt->get_result();

  $permisos = [];
  while ($row = $result->fetch_assoc()) {
      $permisos[$row['nombre']] = [
          'puede_ver'   => (int)$row['puede_ver'],
          'puede_editar'=> (int)$row['puede_editar']
      ];
  }
 
  return $permisos;
}

session_start();

if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit();
}

$rutaActual = basename($_SERVER['PHP_SELF']);

// Acceder a los datos de la sesión
$id_asesor = $_SESSION['id'];
$inicial = $_SESSION['iniciales'];
$supervisor = $_SESSION['supervisor'];
$correo = $_SESSION['correo'];
$sucursal = $_SESSION['sucursal'];
$departamento = $_SESSION['departamento'];
$puesto = $_SESSION['puesto'];
$rol_venta = $_SESSION['rol_venta'];
$id_Rol = $_SESSION['id_rol'];

$acceso = false;
if (!tienePermiso($id_Rol, $rutaActual)) {
  $acceso = true;
  //var_dump($acceso);
  //die();
  //die("No tienes permiso para acceder a esta pantalla.");
}

$respuesta='';

     $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $mes = $row['mes']; 
      
      } else {
        $mes = 'N/A';
      }

     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $actividad = $_POST['actividad'];
      $cita = $_POST['cita'];
      $inicio = $_POST['inicio'];
      $fin = $_POST['fin'];
      $disponible = $_POST['disponible'];
      $estatus = $_POST['estatus'];
      $cliente_id = $_POST['cliente_id'];
      $creadoPor = $correo;
      $fechaCreado = date("Y-m-d H:i:s");
      $modificadoPor = $correo;
      $fechaModificado = date("Y-m-d H:i:s");
  
      // Validar que la fecha no sea anterior a hoy
      $fecha_actual = date('Y-m-d H:i:s');
      if ($inicio < $fecha_actual) {
        $respuesta = "Error: No se pueden crear actividades en fechas anteriores. Solo se permiten fechas actuales o futuras.";
      } else {
        // Validar empalme de horarios
        require __DIR__ . '/controlador/validate_schedule.php';
        $hayEmpalme = validarEmpalmeHorarios($id_asesor, $inicio, $fin);
        
        if ($hayEmpalme) {
          $actividadesEmpalmadas = obtenerActividadesEmpalmadas($id_asesor, $inicio, $fin);
          $respuesta = "Error: Existe un empalme de horarios con actividades existentes. Por favor, seleccione otro horario.";
        } else {
          $sql = "INSERT INTO agenda_personal (actividad, cita, fechahora_inicio, fechahora_fin, disponible, completada, cliente, correo_asesor, mes, fecha_creado, fecha_modificado, modificado_por, plaza, id_empleado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
          $stmt = $con->prepare($sql);
          $stmt->bind_param('ssssssisssssss', $actividad, $cita, $inicio, $fin, $disponible, $estatus, $cliente_id, $correo, $mes, $fechaCreado, $fechaModificado, $modificadoPor, $sucursal, $id_asesor);
    
          if ($stmt->execute()) {
            $respuesta = "Registro guardado exitosamente en la agenda.";
          } else {
              echo "Error: " . $stmt->error;
          }
        }
      }
  }

     $clientes = $con->query("SELECT id, nombre, apellido_paterno, apellido_materno, etapa FROM cliente WHERE asesor = $id_asesor");


// Obtener filtros de la URL
$filtro_estatus = isset($_GET['estatus']) ? $_GET['estatus'] : '';
$filtro_asesor = isset($_GET['asesor']) ? $_GET['asesor'] : '';
$filtro_coordinador = isset($_GET['coordinador']) ? $_GET['coordinador'] : '';
$filtro_ejecutivo = isset($_GET['ejecutivo']) ? $_GET['ejecutivo'] : '';

// Construir consulta base con filtros
$where_conditions = [];
$params = [];
$param_types = '';

// Filtro por plaza del usuario logueado
$where_conditions[] = "ap.plaza = ?";
$params[] = $sucursal;
$param_types .= 's';

// Filtro por estatus
if (!empty($filtro_estatus)) {
    $where_conditions[] = "ap.completada = ?";
    $params[] = $filtro_estatus;
    $param_types .= 's';
}

// Verificar si hay filtros aplicados
$hay_filtros = !empty($filtro_estatus) || !empty($filtro_asesor) || !empty($filtro_coordinador) || !empty($filtro_ejecutivo);

if (!$hay_filtros) {
    // Sin filtros: mostrar actividades propias del usuario logueado
    if ($puesto == 'COORDINADOR') {
        // COORDINADOR sin filtros: mostrar sus propias actividades
        $sql = "SELECT ap.id, ap.actividad, ap.cita, ap.fechahora_inicio, ap.fechahora_fin, 
                       ap.completada, ap.fuente_prospeccion, ap.fecha_creado, ap.fecha_modificado,
                       ap.notas, c.nombre, c.apellido_paterno, c.apellido_materno,
                       e.iniciales as asesor_iniciales,
                       CASE 
                         WHEN ap.cliente = 0 THEN 'PROSPECCION'
                         ELSE CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno)
                       END AS cliente_nombre
                FROM agenda_personal ap
                LEFT JOIN cliente c ON ap.cliente = c.id
                LEFT JOIN empleado e ON ap.id_empleado = e.id
                WHERE ap.id_empleado = ? AND ap.mes = ?
                AND ap.fechahora_inicio >= CURDATE()
                ORDER BY ap.fechahora_inicio ASC";
        
        $params = [$id_asesor, $mes];
        $param_types = 'is';
    } else {
        // Otros roles sin filtros: mostrar sus propias actividades
        $sql = "SELECT ap.id, ap.actividad, ap.cita, ap.fechahora_inicio, ap.fechahora_fin, 
                       ap.completada, ap.fuente_prospeccion, ap.fecha_creado, ap.fecha_modificado,
                       ap.notas, c.nombre, c.apellido_paterno, c.apellido_materno,
                       e.iniciales as asesor_iniciales,
                       CASE 
                         WHEN ap.cliente = 0 THEN 'PROSPECCION'
                         ELSE CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno)
                       END AS cliente_nombre
                FROM agenda_personal ap
                LEFT JOIN cliente c ON ap.cliente = c.id
                LEFT JOIN empleado e ON ap.id_empleado = e.id
                WHERE ap.id_empleado = ? AND ap.mes = ?
                AND ap.fechahora_inicio >= CURDATE()
                ORDER BY ap.fechahora_inicio ASC";
        
        $params = [$id_asesor, $mes];
        $param_types = 'is';
    }
} else {
    // Con filtros: usar lógica diferenciada por rol
    if ($puesto == 'COORDINADOR') {
        // COORDINADOR puede ver actividades de su equipo o filtrar por asesor específico
        if (!empty($filtro_asesor)) {
            $where_conditions[] = "ap.correo_asesor = ?";
            $params[] = $filtro_asesor;
            $param_types .= 's';
        } else {
            $where_conditions[] = "ap.correo_asesor IN (SELECT correo FROM empleado WHERE id_supervisor = ?)";
            $params[] = $id_asesor;
            $param_types .= 'i';
        }
    } elseif ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE' || $puesto == 'DIRECTOR') {
        // Filtros jerárquicos para roles ejecutivos
        if (!empty($filtro_asesor)) {
            // Filtro específico por asesor
            $where_conditions[] = "ap.correo_asesor = ?";
            $params[] = $filtro_asesor;
            $param_types .= 's';
        } elseif (!empty($filtro_coordinador)) {
            // Filtro por coordinador - mostrar asesores de ese coordinador
            $where_conditions[] = "ap.correo_asesor IN (SELECT correo FROM empleado WHERE supervisor = ? AND sucursal = ?)";
            $params[] = $filtro_coordinador;
            $params[] = $sucursal;
            $param_types .= 'ss';
        } elseif (!empty($filtro_ejecutivo)) {
            // Filtro por ejecutivo - mostrar coordinadores y asesores bajo ese ejecutivo
            $where_conditions[] = "ap.correo_asesor IN (
                SELECT correo FROM empleado 
                WHERE sucursal = ? AND (
                    supervisor IN (SELECT correo FROM empleado WHERE supervisor = ?) OR
                    supervisor = ?
                )
            )";
            $params[] = $sucursal;
            $params[] = $filtro_ejecutivo;
            $params[] = $filtro_ejecutivo;
            $param_types .= 'sss';
        } else {
            // Sin filtros específicos - mostrar actividades propias
            $where_conditions[] = "ap.id_empleado = ?";
            $params[] = $id_asesor;
            $param_types .= 'i';
        }
    } else {
        // Otros roles ven solo sus propias actividades (agenda personal)
        $where_conditions[] = "ap.id_empleado = ?";
        $params[] = $id_asesor;
        $param_types .= 'i';
    }

    $sql = "SELECT ap.id, ap.actividad, ap.cita, ap.fechahora_inicio, ap.fechahora_fin, 
            ap.disponible, ap.completada, ap.fecha_reprogramacion, ap.cumplio, 
           ap.fuente_prospeccion, ap.fecha_modificado, ap.plaza, ap.id_empleado,
           e.iniciales as asesor_iniciales,
            CASE 
              WHEN ap.cliente = 0 THEN 'PROSPECCION'
              ELSE CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno)
            END AS cliente_nombre
            FROM agenda_personal ap
            LEFT JOIN cliente c ON ap.cliente = c.id
           LEFT JOIN empleado e ON (ap.correo_asesor = e.correo OR ap.id_empleado = e.id)
           WHERE " . implode(' AND ', $where_conditions) . "
           ORDER BY ap.fechahora_inicio ASC";
}

  $stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
  $stmt->execute();
  $agenda = $stmt->get_result();

// Debug temporal - remover en producción
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #dee2e6; border-radius: 5px;'>";
    echo "<h6>Debug Info:</h6>";
    echo "<p><strong>Puesto:</strong> " . $puesto . "</p>";
    echo "<p><strong>ID Asesor:</strong> " . $id_asesor . "</p>";
    echo "<p><strong>Correo:</strong> " . $correo . "</p>";
    echo "<p><strong>Mes:</strong> " . $mes . "</p>";
    echo "<p><strong>Filtro Estatus:</strong> '" . $filtro_estatus . "'</p>";
    echo "<p><strong>Filtro Asesor:</strong> '" . $filtro_asesor . "'</p>";
    echo "<p><strong>Filtro Coordinador:</strong> '" . $filtro_coordinador . "'</p>";
    echo "<p><strong>Filtro Ejecutivo:</strong> '" . $filtro_ejecutivo . "'</p>";
    echo "<p><strong>Hay Filtros:</strong> " . ($hay_filtros ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>Tipo Consulta:</strong> " . (!$hay_filtros ? 'SIN FILTROS (actividades propias del usuario - desde hoy hacia adelante)' : 'CON FILTROS (lógica por rol)') . "</p>";
    echo "<p><strong>SQL:</strong> " . $sql . "</p>";
    echo "<p><strong>Parámetros:</strong> " . implode(', ', $params) . "</p>";
    echo "<p><strong>Tipos:</strong> " . $param_types . "</p>";
    echo "<p><strong>Número de filas:</strong> " . $agenda->num_rows . "</p>";
    echo "</div>";
}


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
    
    <!-- Avisos Custom CSS -->
    <link rel="stylesheet" href="assets/css/avisos.css" />

    <!-- *************
			************ Vendor Css Files *************
		************ -->

    <!-- Scrollbar CSS -->
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />
  </head>

  <body>
    <?php if ($acceso): ?>
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
    <div class="page-wrapper" <?php echo $acceso ? 'style="pointer-events: none; opacity: 0.3;"' : ''; ?>>

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
                      <!--<img src="assets/images/user2.png" class="rounded-2 img-3x" alt="Bootstrap Gallery" />-->
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
                <?php if (!$acceso): ?>
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="clients.php"><i class="icon-supervised_user_circle"></i> Cliente
                  </a>
                  <ul class="dropdown-menu dropdown-megamenu">
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
                <li class="nav-item">
                  <a class="nav-link" href="aviso.php">
                    <i class="icon-notifications"></i>Avisos
                  </a>
                </li>
                <?php if ($puesto !== 'ASESOR'): ?>
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
                      <a class="dropdown-item" href="#">
                        <span>Indicador Asesor</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="indicadordia.php">
                        <span>Actividad diaria</span>
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
                      <a class="dropdown-item" href="#">
                        <span>Bitacora</span>
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
                  <li class="breadcrumb-item">Cliente</li>
                  <li class="breadcrumb-item">Agenda</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            
            <!-- Row end -->

            <!-- Row start -->
            
            <!-- Row end -->

            <!-- Row start -->
            <div class="row gx-3">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                      <h5 class="card-title mb-0">Calendario de Actividades</h5>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" id="viewMonth">Mes</button>
                        <button type="button" class="btn btn-outline-primary" id="viewWeek">Semana</button>
                        <button type="button" class="btn btn-outline-primary" id="viewDay">Día</button>
                      </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="row mt-3 align-items-end">
                      <div class="col-md-3">
                        <label for="filtro_estatus" class="form-label">Filtrar por Estatus</label>
                        <select id="filtro_estatus" class="form-select" onchange="aplicarFiltros()">
                          <option value="">Todos los estatus</option>
                          <option value="PROGRAMADA" <?php echo $filtro_estatus == 'PROGRAMADA' ? 'selected' : ''; ?>>Programada</option>
                          <option value="COMPLETADA" <?php echo $filtro_estatus == 'COMPLETADA' ? 'selected' : ''; ?>>Completada</option>
                          <option value="CANCELADA" <?php echo $filtro_estatus == 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                          <option value="REPROGRAMADA" <?php echo $filtro_estatus == 'REPROGRAMADA' ? 'selected' : ''; ?>>Reprogramada</option>
                        </select>
                      </div>
                      
                      <?php if ($puesto == 'COORDINADOR'): ?>
                      <div class="col-md-3">
                        <label for="filtro_asesor" class="form-label">Filtrar por Asesor</label>
                        <select id="filtro_asesor" class="form-select" onchange="aplicarFiltros()">
                          <option value="">Todos los asesores</option>
                          <?php
                          $sql_asesores = "SELECT correo, iniciales FROM empleado WHERE id_supervisor = ? AND activo = 1 ORDER BY correo";
                          $stmt_asesores = $con->prepare($sql_asesores);
                          $stmt_asesores->bind_param('i', $id_asesor);
                          $stmt_asesores->execute();
                          $result_asesores = $stmt_asesores->get_result();
                          while ($row_asesor = $result_asesores->fetch_assoc()): ?>
                            <option value="<?php echo $row_asesor['correo']; ?>" <?php echo $filtro_asesor == $row_asesor['correo'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($row_asesor['correo']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <?php endif; ?>
                      
                      <?php if ($puesto == 'EJECUTIVO' || $puesto == 'GERENTE' || $puesto == 'DIRECTOR'): ?>
                      <div class="w-100"></div> <!-- Separador para nueva línea -->
                      <div class="col-12 mt-2 mb-2">
                        <small class="text-muted">
                          <i class="icon-info"></i> 
                          Filtros jerárquicos: Selecciona en orden (Ejecutivo → Coordinador → Asesor) para filtrar la información
                        </small>
                      </div>
                      
                      <!-- Filtro de Ejecutivo (solo para GERENTE y DIRECTOR) -->
                      <?php if ($puesto == 'GERENTE' || $puesto == 'DIRECTOR'): ?>
                      <div class="col-md-3">
                        <label for="filtro_ejecutivo" class="form-label">
                          <i class="icon-person"></i> Filtrar por Ejecutivo
                        </label>
                        <select id="filtro_ejecutivo" class="form-select" onchange="filtrarPorEjecutivo()">
                          <option value="">Todos los ejecutivos</option>
                          <?php
                          $sql_ejecutivos = "SELECT DISTINCT correo FROM empleado WHERE puesto = 'EJECUTIVO' AND sucursal = ? AND activo = 1 ORDER BY correo";
                          $stmt_ejecutivos = $con->prepare($sql_ejecutivos);
                          $stmt_ejecutivos->bind_param('s', $sucursal);
                          $stmt_ejecutivos->execute();
                          $result_ejecutivos = $stmt_ejecutivos->get_result();
                          while ($row_ejecutivo = $result_ejecutivos->fetch_assoc()): ?>
                            <option value="<?php echo $row_ejecutivo['correo']; ?>" <?php echo $filtro_ejecutivo == $row_ejecutivo['correo'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($row_ejecutivo['correo']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <?php endif; ?>
                      
                      <!-- Filtro de Coordinador -->
                      <div class="col-md-3">
                        <label for="filtro_coordinador" class="form-label">
                          <i class="icon-supervised_user_circle"></i> Filtrar por Coordinador
                        </label>
                        <select id="filtro_coordinador" class="form-select" onchange="filtrarPorCoordinador()">
                          <option value="">Todos los coordinadores</option>
                          <?php
                          $sql_coordinadores = "SELECT DISTINCT correo FROM empleado WHERE puesto = 'COORDINADOR' AND sucursal = ? AND activo = 1 ORDER BY correo";
                          $stmt_coordinadores = $con->prepare($sql_coordinadores);
                          $stmt_coordinadores->bind_param('s', $sucursal);
                          $stmt_coordinadores->execute();
                          $result_coordinadores = $stmt_coordinadores->get_result();
                          while ($row_coordinador = $result_coordinadores->fetch_assoc()): ?>
                            <option value="<?php echo $row_coordinador['correo']; ?>" <?php echo $filtro_coordinador == $row_coordinador['correo'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($row_coordinador['correo']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      
                      <!-- Filtro de Asesor -->
                      <div class="col-md-3">
                        <label for="filtro_asesor_ejecutivo" class="form-label">
                          <i class="icon-support_agent"></i> Filtrar por Asesor
                        </label>
                        <select id="filtro_asesor_ejecutivo" class="form-select" onchange="aplicarFiltros()">
                          <option value="">Todos los asesores</option>
                          <?php
                          // Mostrar asesores según filtros aplicados
                          $sql_asesores_ejecutivo = "SELECT DISTINCT correo FROM empleado WHERE puesto = 'ASESOR' AND sucursal = ?";
                          $params_asesor = [$sucursal];
                          $param_types_asesor = 's';
                          
                          if (!empty($filtro_coordinador)) {
                              $sql_asesores_ejecutivo .= " AND supervisor = ?";
                              $params_asesor[] = $filtro_coordinador;
                              $param_types_asesor .= 's';
                          } elseif (!empty($filtro_ejecutivo)) {
                              $sql_asesores_ejecutivo .= " AND supervisor IN (SELECT correo FROM empleado WHERE supervisor = ?)";
                              $params_asesor[] = $filtro_ejecutivo;
                              $param_types_asesor .= 's';
                          }
                          
                          $sql_asesores_ejecutivo .= " AND activo = 1 ORDER BY correo";
                          $stmt_asesores_ejecutivo = $con->prepare($sql_asesores_ejecutivo);
                          $stmt_asesores_ejecutivo->bind_param($param_types_asesor, ...$params_asesor);
                          $stmt_asesores_ejecutivo->execute();
                          $result_asesores_ejecutivo = $stmt_asesores_ejecutivo->get_result();
                          while ($row_asesor_ejecutivo = $result_asesores_ejecutivo->fetch_assoc()): ?>
                            <option value="<?php echo $row_asesor_ejecutivo['correo']; ?>" <?php echo $filtro_asesor == $row_asesor_ejecutivo['correo'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($row_asesor_ejecutivo['correo']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      
                      <!-- Botón para limpiar filtros -->
                      <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary d-block w-100" onclick="limpiarFiltros()">
                          <i class="icon-clear"></i> Limpiar Filtros
                        </button>
                      </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="card-body">
                    <?php
                    if (!empty($respuesta)) {
                      $alertClass = strpos($respuesta, 'Error:') === 0 ? 'alert-danger' : 'alert-success';
                      echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($respuesta) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    }
                    
                    // Mostrar mensajes de URL
                    if (isset($_GET['update']) && $_GET['update'] == 'success') {
                      echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Actividad actualizada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    }
                    if (isset($_GET['error']) && $_GET['error'] == 'empalme') {
                      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error: Existe un empalme de horarios con actividades existentes.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    }
                    if (isset($_GET['error']) && $_GET['error'] == 'past_date') {
                      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error: No se pueden crear o modificar actividades en fechas anteriores. Solo se permiten fechas actuales o futuras.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    }
                    ?>
                    
                    <!-- Controles del calendario -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div class="d-flex align-items-center">
                        <button class="btn btn-outline-secondary" id="prevMonth">
                          <i class="icon-arrow-left"></i>
                        </button>
                        <h4 class="mx-3 mb-0" id="currentMonth"><?php echo date('F Y'); ?></h4>
                        <button class="btn btn-outline-secondary" id="nextMonth">
                          <i class="icon-arrow-right"></i>
                        </button>
                      </div>
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal">
                        <i class="icon-plus"></i> Nueva Actividad
                      </button>
                    </div>

                    <!-- Calendario -->
                    <div id="calendar-container">
                      <div class="calendar-grid" id="calendarGrid">
                        <!-- El calendario se generará dinámicamente con JavaScript -->
                      </div>
                    </div>

                    <!-- Panel de actividades del día seleccionado -->
                    <div class="mt-4" id="dayActivitiesPanel" style="display: none;">
                      <div class="card">
                        <div class="card-header">
                          <h6 class="mb-0">Actividades del <span id="selectedDate"></span></h6>
                        </div>
                        <div class="card-body">
                          <div id="dayActivitiesList">
                            <!-- Las actividades del día se cargarán aquí -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Resumen de actividades -->
                    <div class="summary-container" id="summaryContainer">
                      <h3 class="summary-title">Resumen de Actividades</h3>
                      
                      <!-- Tarjetas de resumen general -->
                      <div class="summary-grid" id="summaryGrid">
                        <div class="summary-card">
                          <h6>Total Actividades</h6>
                          <div class="summary-value" id="totalActivities">0</div>
                          <div class="summary-subtitle">Este mes</div>
                        </div>
                        <div class="summary-card">
                          <h6>Actividades Efectivas</h6>
                          <div class="summary-value" id="effectiveActivities">0</div>
                          <div class="summary-subtitle" id="effectivenessPercentage">0% efectividad</div>
                        </div>
                        <div class="summary-card">
                          <h6>Venta Diaria Promedio</h6>
                          <div class="summary-value" id="averageDailySales">$0</div>
                          <div class="summary-subtitle">Total embudo</div>
                        </div>
                        <div class="summary-card">
                          <h6>Total Ventas</h6>
                          <div class="summary-value" id="totalSales">$0</div>
                          <div class="summary-subtitle">Acumulado del mes</div>
                        </div>
                      </div>

                      <!-- Desglose por actividad -->
                      <div class="activity-breakdown">
                        <h5 class="breakdown-title">Desglose por Actividad</h5>
                        <div id="activityBreakdown">
                          <!-- El desglose se cargará aquí -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
            
            <!-- Row end -->

            <!-- Row start -->
            
            <!-- Row end -->


            <!-- Modal -->
            <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="activityModalLabel">Nueva Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="agenda.php" method="POST">
                      <div class="mb-3">
                        <label for="actividad" class="form-label">Actividad</label>
                        <select id="actividad" name="actividad" class="form-select">
                          <option value="CITA">CITA</option>
                          <option value="LLAMADA">LLAMADA</option>
                          <option value="MENSAJE DE TEXTO">MENSAJE DE TEXTO</option>
                          <option value="CORREO">CORREO</option>
                          <option value="ENVIO DE COTIZACION">ENVIO DE COTIZACIÓN</option>
                          <option value="TAREA">TAREA</option>
                          <option value="RECORDATORIO">RECORDATORIO</option>
                          <option value="TELEMARKETING">TELEMARKETING</option>
                          <option value="CAMBACEO">CAMBACEO</option>
                          <option value="EVENTO">EVENTO</option>
                          <option value="MODULO">MÓDULO</option>
                          <option value="GUARDIA">GUARDIA</option>
                          <option value="ACOMPAÑAMIENTO">ACOMPAÑAMIENTO</option>
                          <option value="WHATS APP">WHATS APP</option>
                          <!-- Agregar más opciones según sea necesario -->
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="cita" class="form-label">Descripcion breve</label>
                        <input type="text" id="cita" name="cita" class="form-control" maxlength="120" required />
                      </div>
                      <div class="mb-3">
                        <label for="inicio" class="form-label">Inicio</label>
                        <input type="datetime-local" id="inicio" name="inicio" class="form-control" required 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" 
                               title="Solo se permiten fechas actuales o futuras" />
                        <div class="form-text">Solo se permiten fechas actuales o futuras</div>
                      </div>
                      <div class="mb-3">
                        <label for="fin" class="form-label">Fin</label>
                        <input type="datetime-local" id="fin" name="fin" class="form-control" required 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" 
                               title="Solo se permiten fechas actuales o futuras" />
                        <div class="form-text">Solo se permiten fechas actuales o futuras</div>
                      </div>
                      <div class="mb-3">
                        <label for="disponible" class="form-label">Disponible</label>
                        <select id="disponible" name="disponible" class="form-select">
                          <option value="Sí">Sí</option>
                          <option value="No">No</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="estatus" class="form-label">Estatus</label>
                        <select id="estatus" name="estatus" class="form-select">
                          <option value="PROGRAMADA">PROGRAMADA</option>
                          <option value="COMPLETADA">COMPLETADA</option>
                          <option value="CANCELADA">CANCELADA</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="stage_filter" class="form-label">Filtrar por Etapa</label>
                        <select id="stage_filter" class="form-select">
                          <option value="">-- Seleccionar Etapa --</option>
                          <option value="BASE DE DATOS">BASE DE DATOS</option>
                          <option value="ACTIVAR">ACTIVAR</option>
                          <option value="ESTRECHAR">ESTRECHAR</option>
                          <option value="EN PRONOSTICO">EN PRONOSTICO</option>
                          <!-- Agregar más etapas según sea necesario -->
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="cliente_id">Seleccionar cliente:</label>
                        <select name="cliente_id" id="cliente_id" class="form-select">
                          <option value="">-- Seleccionar Cliente--</option>
                          <?php while ($row = $clientes->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" data-stage="<?php echo $row['etapa']; ?>">
                              <?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal para editar actividad -->
            <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editActivityModalLabel">Editar Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="edit_agenda.php" method="POST">
                      <div class="mb-3">
                        <label for="edit_actividad" class="form-label">Actividad</label>
                        <input type="text" id="edit_actividad" name="actividad" class="form-control" required readonly />
                      </div>
                      <div class="mb-3">
                        <label for="edit_cita" class="form-label">Descripcion breve</label>
                        <input type="text" id="edit_cita" name="cita" class="form-control" required readonly />
                      </div>
                      <div class="mb-3">
                        <label for="edit_inicio" class="form-label">Inicio</label>
                        <input type="datetime-local" id="edit_inicio" name="inicio" class="form-control" required 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" 
                               title="Solo se permiten fechas actuales o futuras" />
                        <div class="form-text">Solo se permiten fechas actuales o futuras</div>
                      </div>
                      <div class="mb-3">
                        <label for="edit_fin" class="form-label">Fin</label>
                        <input type="datetime-local" id="edit_fin" name="fin" class="form-control" required 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" 
                               title="Solo se permiten fechas actuales o futuras" />
                        <div class="form-text">Solo se permiten fechas actuales o futuras</div>
                      </div>
                      <div class="mb-3">
                        <label for="edit_disponible" class="form-label">Disponible</label>
                        <select id="edit_disponible" name="disponible" class="form-select">
                          <option value="Sí">Sí</option>
                          <option value="No">No</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="edit_estatus" class="form-label">Estatus</label>
                        <select id="edit_estatus" name="estatus" class="form-select">
                          <option value="COMPLETADA">COMPLETADA</option>
                          <option value="CANCELADA">CANCELADA</option>
                          <option value="REPROGRAMADA">REPROGRAMADA</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="fecha_reprogramacion" class="form-label">Fecha Reprogramación</label>
                        <input type="date" id="fecha_reprogramacion" name="fecha_reprogramacion" class="form-control" min="<?php echo date('Y-m-d'); ?>" />
                      </div>
                      <div class="mb-3">
                        <label for="cumplio" class="form-label">Actividad Efectiva</label>
                        <select id="cumplio" name="cumplio" class="form-select">
                          <option value="SI">SI</option>
                          <option value="NO">NO</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="fuente_prospeccion" class="form-label">Fuente de Prospección</label>
                        <select id="fuente_prospeccion" name="fuente_prospeccion" class="form-select">
                        <option value="ANUNCIO">ANUNCIO</option>
                           <option value="CAMBACEO">CAMBACEO</option>
                           <option value="Telemarketing">TELEMARKETING</option>
                           <option value="Venta Digital">VENTA DIGITAL</option>
                           <option value="FUNERAL">FUNERAL</option>
                           <option value="CLIENTE META">CLIENTE META</option>
                           <option value="FACEBOOK">FACEBOOK</option>
                           <option value="EVENTO">EVENTO</option>
                           <option value="REFERIDO">REFERIDO</option>
                           <option value="MERCADO NATURAL">MERCADO NATURAL</option>
                           <option value="TITULOS">TITULOS</option>
                           <option value="MODULO">MODULO</option>
                           <option value="DEMOSTRACIONES">DEMOSTRACIONES</option>
                           <option value="PUNTO">PUNTO</option>
                           <option value="GUARDIA">GUARDIA</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="edit_notas" class="form-label">Notas</label>
                        <textarea id="edit_notas" name="notas" class="form-control" required></textarea>
                      </div>
                      <input type="hidden"  id="fecha_modificado" name="fecha_modificado" value="<?php echo htmlspecialchars($fechaModificado); ?>" />
                      <input type="hidden" id="modificado_por" name="modificado_por" value="<?php echo htmlspecialchars($modificadoPor); ?>" />
                      <input type="hidden" id="edit_id" name="id" />
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                      </div>
                    </form>
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
            <span>© Portal Mausoleos 2025</span>
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
    document.addEventListener("DOMContentLoaded", function () {
        // Deshabilitar todos los elementos interactivos si no hay acceso
        <?php if ($acceso): ?>
        // Deshabilitar todos los formularios
        document.querySelectorAll('form').forEach(function(form) {
            form.style.pointerEvents = 'none';
        });
        
        // Deshabilitar todos los inputs, selects, textareas y botones
        document.querySelectorAll('input, select, textarea, button').forEach(function(element) {
            element.disabled = true;
            element.style.pointerEvents = 'none';
        });
        
        // Deshabilitar todos los enlaces
        document.querySelectorAll('a').forEach(function(link) {
            if (!link.href.includes('login.php') && !link.href.includes('javascript:')) {
                link.style.pointerEvents = 'none';
                link.style.color = '#6c757d';
            }
        });
        
        // Prevenir eventos de clic en toda la página
        document.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, true);
        
        // Prevenir eventos de teclado
        document.addEventListener('keydown', function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, true);
        <?php endif; ?>
    });
    </script>

    <!-- Estilos CSS para el calendario -->
    <style>
      .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .calendar-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        color: white;
        border-bottom: 1px solid #dee2e6;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
      }

      .calendar-day {
        background-color: white;
        min-height: 100px;
        padding: 6px;
        border: 1px solid #dee2e6;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
      }

      .calendar-day:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }

      .calendar-day.other-month {
        background-color: #f8f9fa;
        color: #6c757d;
        opacity: 0.6;
      }

      .calendar-day.today {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        border: 2px solid #2196f3 !important;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
      }

      .calendar-day.selected {
        background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%) !important;
        border: 2px solid #1976d2 !important;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
      }

      .day-number {
        font-weight: 700;
        margin-bottom: 4px;
        font-size: 16px;
        color: #333;
        text-align: center;
        padding: 4px;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 4px auto;
      }

      .calendar-day.today .day-number {
        background-color: #2196f3;
        color: white;
      }

      .calendar-day.selected .day-number {
        background-color: #1976d2;
        color: white;
      }

      .activity-item {
        background-color: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 6px;
        padding: 3px 6px;
        margin: 1px 0;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      }

      .activity-item:hover {
        background-color: #bbdefb;
        transform: scale(1.02);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      }

      .activity-item.completed {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        border-color: #4caf50;
        color: #2e7d32;
      }

      .activity-item.cancelled {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        border-color: #f44336;
        color: #c62828;
      }

      .activity-item.in-progress {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border-color: #ff9800;
        color: #ef6c00;
      }

      .activity-item.reprogrammed {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        border-color: #4caf50;
        color: #2e7d32;
        border-style: dashed;
      }

      .activity-item.past-activity {
        background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
        border-color: #9e9e9e;
        color: #616161;
        opacity: 0.7;
        cursor: not-allowed;
      }

      .activity-item.past-activity:hover {
        transform: none;
        box-shadow: none;
      }

      .performance-tag {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 600;
        margin-left: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .performance-tag.alto {
        background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
      }

      .performance-tag.bajo {
        background: linear-gradient(135deg, #f44336 0%, #ef5350 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(244, 67, 54, 0.3);
      }

      .performance-tag.nuevo {
        background: linear-gradient(135deg, #2196f3 0%, #42a5f5 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(33, 150, 243, 0.3);
      }

      .advisor-info {
        font-size: 9px;
        color: #666;
        margin-top: 2px;
        text-align: center;
      }

      .last-updated {
        font-size: 10px;
        color: #6c757d;
        font-style: italic;
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        border-left: 3px solid #6c757d;
        margin-top: 4px;
        display: inline-block;
      }

      .day-actions {
        position: absolute;
        top: 4px;
        right: 4px;
        opacity: 0;
        transition: opacity 0.2s ease;
        display: flex;
        gap: 2px;
      }

      .calendar-day:hover .day-actions {
        opacity: 1;
      }

      .day-actions .btn {
        padding: 4px 6px;
        font-size: 10px;
        line-height: 1;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .activity-detail {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }

      .activity-detail h6 {
        margin-bottom: 8px;
        color: #495057;
        font-weight: 600;
      }

      .activity-detail .activity-meta {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 4px;
      }

      .view-controls .btn.active {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        border-color: #0d6efd;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
      }

      /* Resumen de actividades */
      .summary-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        color: white;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      }

      .summary-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: center;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
      }

      .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
      }

      .summary-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease;
      }

      .summary-card:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.15);
      }

      .summary-card h6 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .summary-value {
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
      }

      .summary-subtitle {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 4px;
      }

      .activity-breakdown {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 15px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .breakdown-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #fff;
        text-align: center;
      }

      .breakdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      }

      .breakdown-item:last-child {
        border-bottom: none;
      }

      .breakdown-activity {
        font-weight: 600;
        color: #fff;
      }

      .breakdown-stats {
        display: flex;
        gap: 15px;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.9);
      }

      .breakdown-stat {
        text-align: center;
      }

      .breakdown-stat-value {
        font-weight: 700;
        font-size: 14px;
        color: #fff;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .calendar-grid {
          gap: 0;
          border-radius: 8px;
        }

        .calendar-day {
          min-height: 80px;
          padding: 4px;
        }

        .day-number {
          font-size: 14px;
          width: 24px;
          height: 24px;
        }

        .activity-item {
          font-size: 9px;
          padding: 2px 4px;
        }

        .calendar-header {
          padding: 8px 4px;
          font-size: 12px;
        }

        .summary-container {
          padding: 15px;
          margin-top: 15px;
        }

        .summary-title {
          font-size: 20px;
          margin-bottom: 15px;
        }

        .summary-grid {
          grid-template-columns: repeat(2, 1fr);
          gap: 10px;
        }

        .summary-card {
          padding: 12px;
        }

        .summary-value {
          font-size: 20px;
        }

        .breakdown-stats {
          flex-direction: column;
          gap: 5px;
        }

        .breakdown-item {
          flex-direction: column;
          align-items: flex-start;
          gap: 5px;
        }

        .day-actions .btn {
          width: 20px;
          height: 20px;
          font-size: 8px;
        }

        /* Mejorar controles de navegación en móvil */
        .d-flex.justify-content-between.align-items-center.mb-3 {
          flex-direction: column;
          gap: 15px;
        }

        .d-flex.align-items-center {
          justify-content: center;
        }

        .btn-group {
          width: 100%;
          justify-content: center;
        }

        .btn-group .btn {
          flex: 1;
          font-size: 12px;
          padding: 8px 4px;
        }
      }

      @media (max-width: 480px) {
        .calendar-day {
          min-height: 70px;
        }

        .day-number {
          font-size: 12px;
          width: 20px;
          height: 20px;
        }

        .activity-item {
          font-size: 8px;
          padding: 1px 3px;
        }

        .summary-container {
          padding: 10px;
        }

        .summary-title {
          font-size: 18px;
        }

        .summary-value {
          font-size: 18px;
        }

        .summary-grid {
          grid-template-columns: 1fr;
        }

        .breakdown-stats {
          flex-direction: row;
          flex-wrap: wrap;
          justify-content: space-around;
        }

        .breakdown-stat {
          min-width: 60px;
        }

        .breakdown-stat-value {
          font-size: 12px;
        }

        .breakdown-activity {
          font-size: 14px;
          margin-bottom: 5px;
        }

        .performance-tag {
          font-size: 8px;
          padding: 1px 4px;
        }

        .advisor-info {
          font-size: 8px;
        }
      }
    </style>

    <script>
      // Variables globales
      let currentDate = new Date();
      let selectedDate = null;
      let activities = [];
      let currentView = 'month';

      // Nombres de los días y meses
      const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
      const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

      // Inicializar el calendario
      document.addEventListener('DOMContentLoaded', function() {
        loadActivities();
        loadSummaryData();
        renderCalendar();
        setupEventListeners();
        
        // Mostrar automáticamente las actividades del día actual
        setTimeout(() => {
          const today = new Date();
          viewDayActivities(today.toISOString().split('T')[0]);
        }, 1000);
      });

      // Cargar actividades desde el servidor
      async function loadActivities() {
        try {
          const response = await fetch('get_calendar_data.php');
          activities = await response.json();
          renderCalendar();
        } catch (error) {
          console.error('Error cargando actividades:', error);
        }
      }

      // Cargar datos del resumen
      async function loadSummaryData() {
        try {
          const response = await fetch('get_summary_data.php');
          const summaryData = await response.json();
          updateSummaryDisplay(summaryData);
        } catch (error) {
          console.error('Error cargando datos del resumen:', error);
        }
      }

      // Actualizar la visualización del resumen
      function updateSummaryDisplay(data) {
        // Actualizar tarjetas principales
        document.getElementById('totalActivities').textContent = data.totales.total_actividades;
        document.getElementById('effectiveActivities').textContent = data.totales.total_efectivas;
        document.getElementById('effectivenessPercentage').textContent = data.totales.porcentaje_efectividad + '% efectividad';
        document.getElementById('averageDailySales').textContent = '$' + data.totales.promedio_venta_diaria.toLocaleString();
        document.getElementById('totalSales').textContent = '$' + data.totales.total_venta_diaria.toLocaleString();

        // Actualizar desglose por actividad
        const breakdownContainer = document.getElementById('activityBreakdown');
        breakdownContainer.innerHTML = '';

        if (data.actividades.length === 0) {
          breakdownContainer.innerHTML = '<p style="color: rgba(255,255,255,0.8); text-align: center;">No hay actividades registradas</p>';
          return;
        }

        // Agrupar actividades por tipo
        const groupedActivities = {};
        data.actividades.forEach(activity => {
          if (!groupedActivities[activity.actividad]) {
            groupedActivities[activity.actividad] = {
              total: 0,
              efectivas: 0,
              fuentes: new Set()
            };
          }
          groupedActivities[activity.actividad].total += parseInt(activity.total_actividades);
          groupedActivities[activity.actividad].efectivas += parseInt(activity.efectivas);
          groupedActivities[activity.actividad].fuentes.add(activity.fuente_prospeccion);
        });

        // Crear elementos del desglose
        Object.keys(groupedActivities).forEach(actividad => {
          const group = groupedActivities[actividad];
          const efectividad = group.total > 0 ? ((group.efectivas / group.total) * 100).toFixed(1) : 0;
          
          const breakdownItem = document.createElement('div');
          breakdownItem.className = 'breakdown-item';
          breakdownItem.innerHTML = `
            <div class="breakdown-activity">${actividad}</div>
            <div class="breakdown-stats">
              <div class="breakdown-stat">
                <div class="breakdown-stat-value">${group.total}</div>
                <div>Total</div>
              </div>
              <div class="breakdown-stat">
                <div class="breakdown-stat-value">${group.efectivas}</div>
                <div>Efectivas</div>
              </div>
              <div class="breakdown-stat">
                <div class="breakdown-stat-value">${efectividad}%</div>
                <div>Efectividad</div>
              </div>
              <div class="breakdown-stat">
                <div class="breakdown-stat-value">${group.fuentes.size}</div>
                <div>Fuentes</div>
              </div>
            </div>
          `;
          breakdownContainer.appendChild(breakdownItem);
        });
      }

      // Configurar event listeners
      function setupEventListeners() {
        document.getElementById('prevMonth').addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() - 1);
          renderCalendar();
          loadSummaryData();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() + 1);
          renderCalendar();
          loadSummaryData();
        });

        document.getElementById('viewMonth').addEventListener('click', () => setView('month'));
        document.getElementById('viewWeek').addEventListener('click', () => setView('week'));
        document.getElementById('viewDay').addEventListener('click', () => setView('day'));

        // Validación del formulario de nueva actividad
        document.getElementById('activityModal').addEventListener('submit', function(event) {
          const inicio = new Date(document.getElementById('inicio').value);
          const fin = new Date(document.getElementById('fin').value);
          
          if (fin < inicio) {
            event.preventDefault();
            alert('La fecha de fin no puede ser menor que la fecha de inicio.');
          }
        });

        // Filtro de etapas
        document.getElementById('stage_filter').addEventListener('change', function() {
          const selectedStage = this.value;
          const clientOptions = document.querySelectorAll('#cliente_id option');

          clientOptions.forEach(option => {
            if (selectedStage === '' || option.getAttribute('data-stage') === selectedStage) {
              option.style.display = 'block';
            } else {
              option.style.display = 'none';
            }
          });
        });
      }

      // Establecer vista del calendario
      function setView(view) {
        currentView = view;
        document.querySelectorAll('.view-controls .btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('view' + view.charAt(0).toUpperCase() + view.slice(1)).classList.add('active');
        renderCalendar();
      }

      // Renderizar el calendario
      function renderCalendar() {
        const calendarGrid = document.getElementById('calendarGrid');
        calendarGrid.innerHTML = '';

        if (currentView === 'month') {
          renderMonthView();
        } else if (currentView === 'week') {
          renderWeekView();
        } else if (currentView === 'day') {
          renderDayView();
        }
      }

      // Renderizar vista mensual
      function renderMonthView() {
        const calendarGrid = document.getElementById('calendarGrid');
        
        // Agregar encabezados de días
        dayNames.forEach(day => {
          const header = document.createElement('div');
          header.className = 'calendar-header';
          header.textContent = day;
          calendarGrid.appendChild(header);
        });

        // Obtener primer día del mes y número de días
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();

        // Agregar días del mes anterior
        const prevMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 0);
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
          const day = prevMonth.getDate() - i;
          const dayElement = createDayElement(day, true, new Date(prevMonth.getFullYear(), prevMonth.getMonth(), day));
          calendarGrid.appendChild(dayElement);
        }

        // Agregar días del mes actual
        for (let day = 1; day <= daysInMonth; day++) {
          const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
          const dayElement = createDayElement(day, false, date);
          calendarGrid.appendChild(dayElement);
        }

        // Agregar días del mes siguiente para completar la cuadrícula
        const remainingDays = 42 - (startingDayOfWeek + daysInMonth);
        for (let day = 1; day <= remainingDays; day++) {
          const date = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, day);
          const dayElement = createDayElement(day, true, date);
          calendarGrid.appendChild(dayElement);
        }

        // Actualizar título del mes
        document.getElementById('currentMonth').textContent = 
          monthNames[currentDate.getMonth()] + ' ' + currentDate.getFullYear();
      }

      // Renderizar vista semanal
      function renderWeekView() {
        const calendarGrid = document.getElementById('calendarGrid');
        
        // Agregar encabezados de días
        dayNames.forEach(day => {
          const header = document.createElement('div');
          header.className = 'calendar-header';
          header.textContent = day;
          calendarGrid.appendChild(header);
        });

        // Obtener lunes de la semana actual
        const monday = new Date(currentDate);
        monday.setDate(currentDate.getDate() - currentDate.getDay() + 1);

        // Agregar 7 días de la semana
        for (let i = 0; i < 7; i++) {
          const date = new Date(monday);
          date.setDate(monday.getDate() + i);
          const dayElement = createDayElement(date.getDate(), false, date);
          calendarGrid.appendChild(dayElement);
        }

        // Actualizar título
        const sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        document.getElementById('currentMonth').textContent = 
          `${monday.getDate()}/${monday.getMonth() + 1} - ${sunday.getDate()}/${sunday.getMonth() + 1}/${sunday.getFullYear()}`;
      }

      // Renderizar vista diaria
      function renderDayView() {
        const calendarGrid = document.getElementById('calendarGrid');
        
        // Agregar encabezados
        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.textContent = 'Hora';
        calendarGrid.appendChild(header);

        for (let i = 0; i < 6; i++) {
          const header = document.createElement('div');
          header.className = 'calendar-header';
          header.textContent = dayNames[i + 1];
          calendarGrid.appendChild(header);
        }

        // Crear vista de día
        const dayElement = createDayElement(currentDate.getDate(), false, currentDate);
        dayElement.style.gridColumn = '1 / -1';
        calendarGrid.appendChild(dayElement);

        // Actualizar título
        document.getElementById('currentMonth').textContent = 
          `${currentDate.getDate()} de ${monthNames[currentDate.getMonth()]} de ${currentDate.getFullYear()}`;
      }

      // Crear elemento de día
      function createDayElement(dayNumber, isOtherMonth, date) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        if (isOtherMonth) {
          dayElement.classList.add('other-month');
        }

        // Verificar si es hoy y resaltar
        const today = new Date();
        const isToday = date.toDateString() === today.toDateString();
        if (isToday) {
          dayElement.classList.add('today');
          // Asegurar que las actividades del día actual se muestren
          const todayActivities = getActivitiesForDate(today);
          if (todayActivities.length > 0) {
            dayElement.style.border = '3px solid #28a745';
            dayElement.style.boxShadow = '0 4px 12px rgba(40, 167, 69, 0.3)';
          }
        }

        // Crear contenido del día
        const dayNumberElement = document.createElement('div');
        dayNumberElement.className = 'day-number';
        dayNumberElement.textContent = dayNumber;
        dayElement.appendChild(dayNumberElement);

        // Obtener actividades para este día
        const dayActivities = getActivitiesForDate(date);
        
        // Mostrar actividades (máximo 3 en vista mensual)
        const maxActivities = currentView === 'month' ? 3 : 10;
        dayActivities.slice(0, maxActivities).forEach(activity => {
          const activityElement = createActivityElement(activity);
          dayElement.appendChild(activityElement);
        });

        // Mostrar indicador si hay más actividades
        if (dayActivities.length > maxActivities) {
          const moreElement = document.createElement('div');
          moreElement.className = 'activity-item';
          moreElement.style.backgroundColor = '#6c757d';
          moreElement.style.color = 'white';
          moreElement.textContent = `+${dayActivities.length - maxActivities} más`;
          dayElement.appendChild(moreElement);
        }

        // Agregar botones de acción
        const actionsElement = document.createElement('div');
        actionsElement.className = 'day-actions';
        actionsElement.innerHTML = `
          <button class="btn btn-sm btn-primary" onclick="openNewActivityModal('${date.toISOString().split('T')[0]}')">
            <i class="icon-plus"></i>
          </button>
          <button class="btn btn-sm btn-info" onclick="viewDayActivities('${date.toISOString().split('T')[0]}')">
            <i class="icon-eye"></i>
          </button>
        `;
        dayElement.appendChild(actionsElement);

        // Event listener para seleccionar día
        dayElement.addEventListener('click', (e) => {
          if (!e.target.closest('.day-actions')) {
            selectDay(date, dayElement);
          }
        });

        return dayElement;
      }

      // Crear elemento de actividad
      function createActivityElement(activity) {
        const activityElement = document.createElement('div');
        activityElement.className = 'activity-item';
        
        // Verificar si es una actividad pasada
        const startTime = new Date(activity.fechahora_inicio);
        const now = new Date();
        const isPastActivity = startTime < now;
        
        if (isPastActivity) {
          activityElement.classList.add('past-activity');
        }
        
        // Agregar clase según el estado
        if (activity.completada === 'COMPLETADA') {
          activityElement.classList.add('completed');
        } else if (activity.completada === 'CANCELADA') {
          activityElement.classList.add('cancelled');
        } else if (activity.completada === 'REPROGRAMADA') {
          activityElement.classList.add('reprogrammed');
        } else {
          activityElement.classList.add('in-progress');
        }

        // Crear contenido
        const content = document.createElement('div');
        const canEdit = (activity.completada === 'PROGRAMADA' || activity.completada === 'REPROGRAMADA');
        const lastUpdated = !canEdit && activity.fecha_modificado ? 
          `<div class="last-updated">Actualizado: ${new Date(activity.fecha_modificado).toLocaleDateString()}</div>` : '';
        
        content.innerHTML = `
          <div style="font-weight: 600;">${activity.actividad}</div>
          <div style="font-size: 10px; color: #666;">${activity.cliente_nombre}</div>
          <div class="advisor-info">
            ${activity.asesor_iniciales}
            <span class="performance-tag ${activity.etiqueta_desempeno.toLowerCase().replace(' ', '-')}">
              ${activity.etiqueta_desempeno}
            </span>
          </div>
          ${lastUpdated}
        `;
        activityElement.appendChild(content);

        // Event listener para mostrar detalles
        activityElement.addEventListener('click', (e) => {
          e.stopPropagation();
          showActivityDetails(activity);
        });

        return activityElement;
      }

      // Obtener actividades para una fecha específica
      function getActivitiesForDate(date) {
        const dateString = date.toISOString().split('T')[0];
        return activities.filter(activity => {
          const activityDate = new Date(activity.fechahora_inicio);
          return activityDate.toISOString().split('T')[0] === dateString;
        });
      }

      // Seleccionar día
      function selectDay(date, dayElement) {
        // Remover selección anterior
        document.querySelectorAll('.calendar-day.selected').forEach(el => {
          el.classList.remove('selected');
        });

        // Seleccionar nuevo día
        dayElement.classList.add('selected');
        selectedDate = date;

        // Mostrar actividades del día
        viewDayActivities(date.toISOString().split('T')[0]);
      }

      // Ver actividades del día
      function viewDayActivities(dateString) {
        const date = new Date(dateString);
        const dayActivities = getActivitiesForDate(date);
        
        document.getElementById('selectedDate').textContent = 
          `${date.getDate()} de ${monthNames[date.getMonth()]} de ${date.getFullYear()}`;
        
        const activitiesList = document.getElementById('dayActivitiesList');
        activitiesList.innerHTML = '';

        if (dayActivities.length === 0) {
          activitiesList.innerHTML = '<p class="text-muted">No hay actividades para este día.</p>';
        } else {
          dayActivities.forEach(activity => {
            const activityDetail = createActivityDetailElement(activity);
            activitiesList.appendChild(activityDetail);
          });
        }

        document.getElementById('dayActivitiesPanel').style.display = 'block';
      }

      // Crear elemento de detalle de actividad
      function createActivityDetailElement(activity) {
        const detailElement = document.createElement('div');
        detailElement.className = 'activity-detail';
        
        const startTime = new Date(activity.fechahora_inicio);
        const endTime = new Date(activity.fechahora_fin);
        const now = new Date();
        const isPastActivity = startTime < now;
        
        // Determinar si se puede editar (solo actividades con estatus PROGRAMADA o REPROGRAMADA)
        const canEdit = (activity.completada === 'PROGRAMADA' || activity.completada === 'REPROGRAMADA');
        
        detailElement.innerHTML = `
          <h6>${activity.actividad}</h6>
          <div class="activity-meta">
            <strong>Cliente:</strong> ${activity.cliente_nombre}<br>
            <strong>Asesor:</strong> ${activity.asesor_iniciales}
            <span class="performance-tag ${activity.etiqueta_desempeno.toLowerCase().replace(' ', '-')}">
              ${activity.etiqueta_desempeno}
            </span><br>
            <strong>Hora:</strong> ${startTime.toLocaleTimeString()} - ${endTime.toLocaleTimeString()}<br>
            <strong>Estado:</strong> ${activity.completada}<br>
            <strong>Descripción:</strong> ${activity.cita}
            ${activity.fecha_reprogramacion ? `<br><strong>Fecha Reprogramación:</strong> ${new Date(activity.fecha_reprogramacion).toLocaleDateString()}` : ''}
            ${!canEdit && activity.fecha_modificado ? `<br><strong>Última actualización:</strong> ${new Date(activity.fecha_modificado).toLocaleString()}` : ''}
          </div>
          <div class="mt-2">
            ${canEdit ? 
              `<button class="btn btn-sm btn-primary" onclick="editActivity(${activity.id})">
                <i class="icon-edit"></i> Editar
              </button>` : 
              '<span class="text-muted"><i class="icon-lock"></i> No editable (solo se pueden editar actividades PROGRAMADA o REPROGRAMADA)</span>'
            }
          </div>
        `;

        return detailElement;
      }

      // Mostrar detalles de actividad
      function showActivityDetails(activity) {
        const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
        // Aquí puedes implementar un modal para mostrar más detalles
        console.log('Mostrar detalles de actividad:', activity);
      }

      // Abrir modal de nueva actividad con fecha preseleccionada
      function openNewActivityModal(dateString) {
        const modal = new bootstrap.Modal(document.getElementById('activityModal'));
        // Preseleccionar la fecha en el formulario
        document.getElementById('inicio').value = dateString + 'T09:00';
        document.getElementById('fin').value = dateString + 'T10:00';
        modal.show();
      }

      // Editar actividad
      function editActivity(activityId) {
        // Implementar lógica de edición
        console.log('Editar actividad:', activityId);
      }

      // Script para llenar el modal de edición con los datos de la actividad
      const editActivityModal = document.getElementById('editActivityModal');
      editActivityModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget; // Botón que abrió el modal
        const id = button.getAttribute('data-id');
        
        // Fetch the activity data
        fetch(`edit_agenda.php?id=${id}`)
          .then(response => response.json())
          .then(data => {
            // Llenar los campos del modal
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_actividad').value = data.actividad;
            document.getElementById('edit_cita').value = data.cita;
            document.getElementById('edit_inicio').value = data.fechahora_inicio;
            document.getElementById('edit_fin').value = data.fechahora_fin;
            document.getElementById('edit_disponible').value = data.disponible;
            document.getElementById('edit_estatus').value = data.completada;
            document.getElementById('fecha_reprogramacion').value = data.fecha_reprogramacion;
            document.getElementById('cumplio').value = data.cumplio;
            document.getElementById('fuente_prospeccion').value = data.fuente_prospeccion;
            document.getElementById('edit_notas').value = data.notas;
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la actividad');
          });
      });

      // Función para editar actividad
      function editActivity(activityId) {
        // Verificar si la actividad se puede editar
        const activity = activities.find(a => a.id == activityId);
        if (activity) {
          const canEdit = (activity.completada === 'PROGRAMADA' || activity.completada === 'REPROGRAMADA');
          
          if (!canEdit) {
            alert('No se puede editar esta actividad. Solo se pueden editar actividades con estatus PROGRAMADA o REPROGRAMADA.');
            return;
          }
        }
        
        // Abrir modal de edición
        const modal = new bootstrap.Modal(document.getElementById('editActivityModal'));
        const button = document.createElement('button');
        button.setAttribute('data-id', activityId);
        button.setAttribute('data-bs-toggle', 'modal');
        button.setAttribute('data-bs-target', '#editActivityModal');
        document.body.appendChild(button);
        button.click();
        document.body.removeChild(button);
      }

      // Event listener para cambio automático de estatus cuando se selecciona fecha de reprogramación
      document.addEventListener('DOMContentLoaded', function() {
        const fechaReprogramacionInput = document.getElementById('fecha_reprogramacion');
        const estatusSelect = document.getElementById('edit_estatus');
        
        if (fechaReprogramacionInput && estatusSelect) {
          fechaReprogramacionInput.addEventListener('change', function() {
            if (this.value !== '') {
              // Cambiar automáticamente el estatus a REPROGRAMADA
              estatusSelect.value = 'REPROGRAMADA';
              estatusSelect.style.backgroundColor = '#e3f2fd';
              estatusSelect.style.borderColor = '#2196f3';
              
              // Mostrar mensaje informativo
              const infoDiv = document.createElement('div');
              infoDiv.className = 'alert alert-info mt-2';
              infoDiv.innerHTML = '<i class="icon-info"></i> El estatus se ha cambiado automáticamente a REPROGRAMADA.';
              infoDiv.id = 'reprogramacion-info';
              
              // Remover mensaje anterior si existe
              const existingInfo = document.getElementById('reprogramacion-info');
              if (existingInfo) {
                existingInfo.remove();
              }
              
              this.parentNode.appendChild(infoDiv);
            } else {
              // Restaurar estilos originales
              estatusSelect.style.backgroundColor = '';
              estatusSelect.style.borderColor = '';
              
              // Remover mensaje informativo
              const existingInfo = document.getElementById('reprogramacion-info');
              if (existingInfo) {
                existingInfo.remove();
              }
            }
          });
        }
      });

      // Función para aplicar filtros
      function aplicarFiltros() {
        const estatus = document.getElementById('filtro_estatus').value;
        const asesor = document.getElementById('filtro_asesor') ? document.getElementById('filtro_asesor').value : '';
        const asesorEjecutivo = document.getElementById('filtro_asesor_ejecutivo') ? document.getElementById('filtro_asesor_ejecutivo').value : '';
        const coordinador = document.getElementById('filtro_coordinador') ? document.getElementById('filtro_coordinador').value : '';
        const ejecutivo = document.getElementById('filtro_ejecutivo') ? document.getElementById('filtro_ejecutivo').value : '';
        
        const params = new URLSearchParams();
        if (estatus) params.append('estatus', estatus);
        if (asesor) params.append('asesor', asesor);
        if (asesorEjecutivo) params.append('asesor', asesorEjecutivo);
        if (coordinador) params.append('coordinador', coordinador);
        if (ejecutivo) params.append('ejecutivo', ejecutivo);
        
        window.location.href = 'agenda.php?' + params.toString();
      }

      // Función para filtrar por ejecutivo (actualiza coordinadores y asesores)
      function filtrarPorEjecutivo() {
        const ejecutivo = document.getElementById('filtro_ejecutivo').value;
        const coordinadorSelect = document.getElementById('filtro_coordinador');
        const asesorSelect = document.getElementById('filtro_asesor_ejecutivo');
        
        // Limpiar coordinadores y asesores
        coordinadorSelect.innerHTML = '<option value="">Todos los coordinadores</option>';
        asesorSelect.innerHTML = '<option value="">Todos los asesores</option>';
        
        if (ejecutivo) {
          // Cargar coordinadores del ejecutivo seleccionado
          fetch(`get_empleados_jerarquia.php?ejecutivo=${ejecutivo}&tipo=coordinador`)
            .then(response => response.json())
            .then(data => {
              data.forEach(coordinador => {
                const option = document.createElement('option');
                option.value = coordinador.correo;
                option.textContent = coordinador.correo;
                coordinadorSelect.appendChild(option);
              });
            })
            .catch(error => console.error('Error cargando coordinadores:', error));
        }
      }

      // Función para filtrar por coordinador (actualiza asesores)
      function filtrarPorCoordinador() {
        const coordinador = document.getElementById('filtro_coordinador').value;
        const asesorSelect = document.getElementById('filtro_asesor_ejecutivo');
        
        // Limpiar asesores
        asesorSelect.innerHTML = '<option value="">Todos los asesores</option>';
        
        if (coordinador) {
          // Cargar asesores del coordinador seleccionado
          fetch(`get_empleados_jerarquia.php?coordinador=${coordinador}&tipo=asesor`)
            .then(response => response.json())
            .then(data => {
              data.forEach(asesor => {
                const option = document.createElement('option');
                option.value = asesor.correo;
                option.textContent = asesor.correo;
                asesorSelect.appendChild(option);
              });
            })
            .catch(error => console.error('Error cargando asesores:', error));
        }
      }

      // Función para limpiar todos los filtros
      function limpiarFiltros() {
        const params = new URLSearchParams();
        window.location.href = 'agenda.php?' + params.toString();
      }

      // Función removida - no necesaria para agenda personal

      // Validación de fechas pasadas y empalme de horarios en tiempo real
      function validarHorarios() {
        const inicio = document.getElementById('inicio').value;
        const fin = document.getElementById('fin').value;
        const submitBtn = document.querySelector('#activityModal button[type="submit"]');
        
        if (!inicio || !fin) return;
        
        // Validar fechas pasadas
        const fechaActual = new Date();
        const fechaInicio = new Date(inicio);
        
        if (fechaInicio < fechaActual) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Fecha no permitida';
          submitBtn.className = 'btn btn-danger';
          return;
        }
        
        // Validar empalme de horarios
        const formData = new FormData();
        formData.append('action', 'validar_empalme');
        formData.append('id_empleado', <?php echo $id_asesor; ?>);
        formData.append('fecha_inicio', inicio);
        formData.append('fecha_fin', fin);
        
        fetch('controlador/validate_schedule.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.empalme) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Horario no disponible';
            submitBtn.className = 'btn btn-danger';
            
            // Mostrar actividades empalmadas
            let mensaje = 'Actividades que empalman:\n';
            data.actividades.forEach(act => {
              mensaje += `- ${act.actividad}: ${act.fechahora_inicio} a ${act.fechahora_fin}\n`;
            });
            alert(mensaje);
          } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Agregar';
            submitBtn.className = 'btn btn-primary';
          }
        })
        .catch(error => {
          console.error('Error validando horarios:', error);
        });
      }

      // Validación para modal de edición
      function validarHorariosEdicion() {
        const inicio = document.getElementById('edit_inicio').value;
        const fin = document.getElementById('edit_fin').value;
        const submitBtn = document.querySelector('#editActivityModal button[type="submit"]');
        
        if (!inicio || !fin) return;
        
        // Validar fechas pasadas
        const fechaActual = new Date();
        const fechaInicio = new Date(inicio);
        
        if (fechaInicio < fechaActual) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Fecha no permitida';
          submitBtn.className = 'btn btn-danger';
          return;
        }
        
        // Si las fechas son válidas, habilitar el botón
        submitBtn.disabled = false;
        submitBtn.textContent = 'Actualizar';
        submitBtn.className = 'btn btn-primary';
      }

      // Agregar event listeners para validación de horarios
      document.addEventListener('DOMContentLoaded', function() {
        const inicioInput = document.getElementById('inicio');
        const finInput = document.getElementById('fin');
        const editInicioInput = document.getElementById('edit_inicio');
        const editFinInput = document.getElementById('edit_fin');
        
        if (inicioInput && finInput) {
          inicioInput.addEventListener('change', validarHorarios);
          finInput.addEventListener('change', validarHorarios);
        }
        
        if (editInicioInput && editFinInput) {
          editInicioInput.addEventListener('change', validarHorariosEdicion);
          editFinInput.addEventListener('change', validarHorariosEdicion);
        }
      });
    </script>
  </body>

</html>