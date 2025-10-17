<?php 
require './controlador/conexion.php';
require './controlador/access_control.php';

//use PHPMailer\PHPMailer;
//use PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// Function to generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Get positions from puesto table
$query_puestos = "SELECT id, nombre FROM puesto where activo = 1";
$result_puestos = mysqli_query($con, $query_puestos);

// Get departments from departamento table
$query_departamentos = "SELECT id, nombre FROM departamento WHERE estado = 'Activo'";
$result_departamentos = mysqli_query($con, $query_departamentos);

// Get teams filtered by user's plaza
$query_equipos = "SELECT id, nombre FROM equipo WHERE plaza = ?";
$stmt_equipos = $con->prepare($query_equipos);
$stmt_equipos->bind_param("s", $sucursal);
$stmt_equipos->execute();
$result_equipos = $stmt_equipos->get_result();


$query_notificaciones_baja = "SELECT correo FROM empleado WHERE notificacion_baja = 1 and sucursal = ? and (puesto = 'EJECUTIVO' OR puesto = 'COORDINADOR DE TALENTO Y CULTURA' OR puesto = 'DIRECTOR' OR puesto = 'GERENTE'";
$stmt_notificaciones_baja = $con->prepare($query_notificaciones_baja);
$stmt_notificaciones_baja->bind_param("s",$sucursal);
$stmt_notificaciones_baja -> execute();
$result_notificaciones_baja = $stmt_notificaciones_baja -> get_result();

$notificaciones_baja = [];

while ($baja = $result_notificaciones_baja->fetch_assoc()) {
  $notificaciones_baja[] = $baja['correo'];
}

// Get supervisors (COORDINADOR, EJECUTIVO, GERENTE) filtered by plaza
$query_supervisores = "SELECT id, correo FROM empleado WHERE puesto IN ('COORDINADOR', 'EJECUTIVO', 'GERENTE') AND sucursal = ? AND (estado_empleado = 'Activo' OR estado_empleado = '') AND activo = 1";
$stmt_supervisores = $con->prepare($query_supervisores);
$stmt_supervisores->bind_param("s", $sucursal);
$stmt_supervisores->execute();
$result_supervisores = $stmt_supervisores->get_result();

$supervisores = [];
while ($row = $result_supervisores->fetch_assoc()) {
  $supervisores[] = $row;
}
// Get active employees for update dropdown
$query_empleados = "SELECT id, correo FROM empleado WHERE activo = 1 and sucursal = '$sucursal'";
$result_empleados = mysqli_query($con, $query_empleados);
$to = 'raquel.moreno@mle.com.mx';
// Function to log email instead of sending (for development environment)
function sendEmail($to, $subject, $message) {
    // Log the email attempt instead of actually sending
    error_log("EMAIL WOULD BE SENT TO: {$to}");
    error_log("SUBJECT: {$subject}");
    error_log("MESSAGE: " . substr($message, 0, 100) . "...");
    
    // Create a log file in the local directory
    $log_file = 'email_log.txt';
    $log_content = "---------------------\n";
    $log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $log_content .= "To: {$to}\n";
    $log_content .= "Subject: {$subject}\n";
    $log_content .= "Message: " . substr($message, 0, 100) . "...\n\n";
    
    // Append to log file (with error handling)
    try {
        file_put_contents($log_file, $log_content, FILE_APPEND);
    } catch (Exception $e) {
        error_log("Could not write to log file: " . $e->getMessage());
    }
    
    // For development, always return success
    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert all text fields to uppercase
    $nombre = strtoupper($_POST['nombre'] ?? '');
    $apellido_paterno = strtoupper($_POST['apellido_paterno'] ?? '');
    $apellido_materno = strtoupper($_POST['apellido_materno'] ?? '');
    $correo_empleado = strtoupper($_POST['correo'] ?? '');
    $puesto_id = $_POST['puesto'] ?? '';
    $categoria = strtoupper($_POST['categoria'] ?? '');
    
    // Debug: Log the categoria value
    error_log("Categoria value: " . $categoria);
    $meta = $_POST['meta'] ?? 0;
    $equipo_id = $_POST['equipo'] ?? '';
    $apsi = strtoupper($_POST['apsi'] ?? '');
    $notas = strtoupper($_POST['notas'] ?? '');
    $supervisor_id = $_POST['supervisor'] ?? '';
    $estado_empleado = strtoupper($_POST['estatus'] ?? 'Activo');
    
    // Debug: Log the estado_empleado value
    error_log("Estado empleado value: " . $estado_empleado);
    $departamento_id = $_POST['departamento'] ?? '';
    
    // Get names from dropdowns instead of IDs
    $puesto_nombre = '';
    $departamento_nombre = '';
    $supervisor_nombre = '';
    
    // Get puesto name
    if (!empty($puesto_id)) {
        $query_puesto_name = "SELECT nombre FROM puesto WHERE id = ?";
        $stmt_puesto = $con->prepare($query_puesto_name);
        $stmt_puesto->bind_param("i", $puesto_id);
        $stmt_puesto->execute();
        $result_puesto = $stmt_puesto->get_result();
        $puesto_nombre = $result_puesto->fetch_assoc()['nombre'];
    }
    
    // Get departamento name
    if (!empty($departamento_id)) {
        $query_departamento_name = "SELECT nombre FROM departamento WHERE id = ?";
        $stmt_departamento = $con->prepare($query_departamento_name);
        $stmt_departamento->bind_param("i", $departamento_id);
        $stmt_departamento->execute();
        $result_departamento = $stmt_departamento->get_result();
        $departamento_nombre = $result_departamento->fetch_assoc()['nombre'];
    }
    
    // Get supervisor name (email)
    if (!empty($supervisor_id)) {
        $query_supervisor_name = "SELECT correo FROM empleado WHERE id = ?";
        $stmt_supervisor = $con->prepare($query_supervisor_name);
        $stmt_supervisor->bind_param("i", $supervisor_id);
        $stmt_supervisor->execute();
        $result_supervisor = $stmt_supervisor->get_result();
        $supervisor_nombre = $result_supervisor->fetch_assoc()['correo'];
    }
    $employee_id = $_POST['employee_id'] ?? null;
    $iniciales = strtoupper($_POST['shortName'] ?? '');
    $fecha_nacimiento = $_POST['birthDay'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $tipo = strtoupper($_POST['clase'] ?? '');
    
    
    // Validate meta for ASESOR and COORDINADOR
    if (($puesto_nombre == 'ASESOR' || $puesto_nombre == 'COORDINADOR') && ($meta == 0 || empty($meta))) {
        echo "<script>alert('El presupuesto anual es obligatorio para el puesto de " . $puesto_nombre . "');</script>";
        exit();
    }
    
    // Set sucursal - only editable for SISTEMAS users
    if ($puesto == 'SISTEMAS') {
        $sucursal_empleado = strtoupper($_POST['sucursal'] ?? $sucursal);
    } else {
        $sucursal_empleado = strtoupper($sucursal);
    }

    // Generate random password for all employees (new and updates)
    $contrasena = generateRandomPassword();
    $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);

    if ($employee_id) {
        // UPDATE existing employee
        $activo = 1;
        $fecha_baja = null;
        if ($estado_empleado == 'INACTIVO' || $estado_empleado == 'BAJA' ) {
            $fecha_baja = date('Y-m-d H:i:s');
            $activo = 2;
        }
        $plantilla = isset($_POST['plantilla']) ? intval($_POST['plantilla']) : 0;
       
        $sql = "UPDATE empleado SET 
                nombre = ?, 
                apellido_paterno = ?, 
                apellido_materno = ?, 
                iniciales = ?,
                supervisor = ?,
                correo = ?, 
                contrasena = ?,
                sucursal = ?,
                departamento = ?,
                puesto = ?, 
                categoria = ?, 
                estado_empleado = ?,
                fecha_baja = ?,
                equipo = ?,
                meta = ?, 
                notas = ?, 
                fecha_cumple = ?,
                fecha_inicio = ?,
                modificado_por = ?,
                fecha_modificado = NOW(),
                activo = ?,
                apsi = ?,
                plantilla = ?,
                modelo_negocio = 'MTLA'
                WHERE id = ?";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssssssssssssidsssssiiii", 
            $nombre, 
            $apellido_paterno, 
            $apellido_materno, 
            $iniciales,
            $supervisor_nombre,
            $correo_empleado, 
            $contrasena_hash,
            $sucursal_empleado,
            $departamento_nombre,
            $puesto_nombre, 
            $categoria, 
            $estado_empleado,
            $fecha_baja,
            $equipo_id,
            $meta, 
            $notas, 
            $fecha_nacimiento,
            $fecha_inicio,
            $correo, // usuario logueado
            $activo,
            $apsi,
            $plantilla,
            $employee_id,
            
        );

        if ($stmt->execute() ) {
            // Log update notification email instead of sending
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8'; 
             try {
              $mail->isSMTP();
              $mail->Host       = 'smtp.gmail.com';  
              $mail->SMTPAuth   = true;
              $mail->Username   = 'crmmauso@gmail.com'; 
              $mail->Password   = 'fjqa kkqe nmbm cxpy'; // clave de aplicación de Gmail
              $mail->SMTPSecure = 'ssl';
              $mail->Port       = 465;

              $mail->setFrom('crmmauso@gmail.com', 'Portal Mausoleos');
              $mail->isHTML(true);
              $mail->Subject = 'Actualización de Cuenta - Portal Mausoleos';

              $correos_por_plaza = [
                  //"CUAUHTEMOC" => ["isaac.chavez@mle.com.mx", "rogelio.torres@mle.com.mx", "diana.vargas@mle.com.mx"],
                  //"DELICIAS"  => ["jorge.betances@todaslasalmas.com.mx", "ramon.alderete@todaslasalmas.com.mx", "jessica.cobos@todaslasalmas.com.mx"],
                  //"CHIHUAHUA" => ["arnold.gonzalez@mle.com.mx", "luis.villalobos@mle.com.mx"]
                  //"CHIHUAHUA"  => ["@MLE.com", "@MLE.com"],
                  // agrega correos por plazaplazas que necesites
              ];
              
              // 🔹 Cambia el body y destinatarios si está en BAJA
              if ($estado_empleado == "BAJA") {
                  // --- destinatarios distintos ---
                 if (isset($notificaciones_baja)) {
                    foreach ($notificaciones_baja as $correo) {
                        $mail->addAddress($correo);
                    }
                    } else {
                    // fallback si no existe la plaza en el arreglo
                      $mail->addAddress("notificacionesmle@mle.com.mx");
                    }

                  // --- cuerpo del correo ---
                  $mail->Body = "
                      <h2>Notificación de Baja</h2>
                      <p>El empleado <b>{$nombre}</b> con correo <b>{$correo_empleado}</b> 
                      ha sido dado de <span style='color:red;'>BAJA</span> en el Portal Mausoleos.</p>
                      <p>Fecha de actualización: " . date('Y-m-d H:i:s') . "</p>
                      <p>Por favor, atender esta notificación.</p>
                  ";
              } else {
                  // --- destinatario normal ---

                  $correo_empleado = "arnold.gonzalez@mle.com.mx";
                  $mail->addAddress($correo_empleado);

                  // --- cuerpo del correo ---
                  $mail->Body = "
                      <h2>Actualización Portal Mausoleos</h2>
                      <p>Hola {$nombre},</p>
                      <p>Tu cuenta ha sido modificada en el Portal Mausoleos.</p>
                      <p>Detalles de la cuenta:</p>
                      <ul>
                          <li>Correo: {$correo_empleado}</li>
                          <li>Clave: {$contrasena}</li>
                      </ul>
                      <p>Fecha de creado: " . date('Y-m-d H:i:s') . "</p>
                      <p>Por favor, no contestar este correo, para cualquier duda o aclaración, favor de contactar al área de: 
                      <a href='mailto:sistemas@mle.com.mx'>sistemas@mle.com.mx</a></p>
                  ";
              }

              $mail->send();
              echo "<script>alert('Empleado actualizado exitosamente. La notificación fue registrada.');</script>";

              // echo "<script>alert('Error al actualizar el empleado: " . $stmt->error . "');</script>";
            } catch (\Throwable $th) {
            die($mail->ErrorInfo);
              echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
          }
         
              } else {
                  // INSERT new employee (same order as UPDATE)
                        // INSERT new employee
                      $sql = "INSERT INTO empleado (
                              nombre,
                              apellido_paterno,
                              apellido_materno,
                              iniciales,
                              correo,
                              contrasena,
                              sucursal,
                              departamento,
                              puesto,
                              categoria,
                              estado_empleado,
                              equipo,
                              meta,
                              notas,
                              supervisor,
                              apsi,
                              tipo,
                              fecha_cumple,
                              fecha_inicio,
                              creado_por,
                              fecha_creado,
                              activo,
                              plantilla,
                              id_supervisor  
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, ?, ?)";

                      // Definir las variables que faltan ANTES del bind_param
                      $plantilla = isset($_POST['plantilla']) ? intval($_POST['plantilla']) : 0;
                      $id_supervisor = isset($supervisor_id) && !empty($supervisor_id) ? intval($supervisor_id) : 0;

                      $stmt = $con->prepare($sql);
                      $stmt->bind_param("ssssssssssssssssssssii",  // 20 's' + 2 'i' = 22 caracteres total
                          $nombre,                    // 1 - s
                          $apellido_paterno,         // 2 - s
                          $apellido_materno,         // 3 - s
                          $iniciales,                // 4 - s
                          $correo_empleado,          // 5 - s
                          $contrasena_hash,          // 6 - s
                          $sucursal_empleado,        // 7 - s
                          $departamento_nombre,      // 8 - s
                          $puesto_nombre,            // 9 - s
                          $categoria,                // 10 - s
                          $estado_empleado,          // 11 - s
                          $equipo_id,                // 12 - s
                          $meta,                     // 13 - s
                          $notas,                    // 14 - s
                          $supervisor_nombre,        // 15 - s
                          $apsi,                     // 16 - s
                          $tipo,                     // 17 - s
                          $fecha_nacimiento,         // 18 - s
                          $fecha_inicio,             // 19 - s
                          $correo,                   // 20 - s (usuario logueado)
                          $plantilla,                // 21 - i (integer)
                          $id_supervisor             // 22 - i (integer)
                      );
        
        if ($stmt->execute()) {
            // Log welcome email instead of sending
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8'; 
            try {
              $mail->isSMTP();
              $mail->Host = 'smtp.gmail.com';  
              $mail->SMTPAuth = true;
              $mail->Username = 'crmmauso@gmail.com'; 
              $mail->Password   = 'fjqa kkqe nmbm cxpy';
              $mail->SMTPSecure = 'ssl';
              $mail->Port = 465;
  
              $mail->setFrom('crmmauso@gmail.com', 'Portal Mausoleos');
              $mail->addAddress($correo_empleado);
  
             $mail->isHTML(true);
             $mail->Subject = 'Bienvenido al Portal Mausoleos';
             $mail->Body   = "
                  <h2>Bienvenido al Portal Mausoleos</h2>
                  <p>Hola {$nombre},</p>
                  <p>Tu cuenta ha sido creada en el Portal Mausoleos.</p>
                  <p>Detalles de la cuenta:</p>
                  <ul>
                      <li>Correo: {$correo_empleado}</li>
                      <li>clave: {$contrasena}</li>
                  </ul>
                  <p>Fecha de creado: " . date('Y-m-d H:i:s') . "</p>
                  <p>Por favor, no contestar este correo, para cualquier duda o aclaración, favor de contactar al area de: TDN <a href='mailto:sistemas@mle.com.mx'>sistemas@mle.com.mx</a></p>
              ";
              
              $mail->send();
              echo 'Correo enviado con éxito';
            } catch (\Throwable $th) {
            die($mail->ErrorInfo);
              echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
            
        } else {
            echo "<script>alert('Error al crear el empleado: " . $stmt->error . "');</script>";
        }
    }
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

    <!-- *************
			************ Vendor Css Files *************
		************ -->

    <!-- Scrollbar CSS -->
    <link rel="stylesheet" href="assets/vendor/overlay-scroll/OverlayScrollbars.min.css" />

    <!-- Date Range CSS -->
    <link rel="stylesheet" href="assets/vendor/daterange/daterange.css" />

    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="assets/vendor/dropzone/dropzone.min.css" />

   


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
                              <h6 class="mb-1 fw-semibold">Venta pronostico cierre</h6>
                              <p class="mb-2">$ 0.0.</p>
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
                <!--<li class="nav-item dropdown">
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
                <li class="nav-item dropdown active-link">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="icon-package"></i>Configuracion
                  </a>
                  <ul class="dropdown-menu dropdown-megamenu">

                    <li>
                      <a class="dropdown-item current-page" href="account-settings.php">
                        <span>Control de usuarios</span></a>
                    </li>
                  </ul>
                </li>

                <?php if ($puesto !== 'ASESOR' && tienePermiso($id_Rol, 'controlventa.php')): ?>
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
                  <li class="breadcrumb-item">Configuracion</li>
                  <li class="breadcrumb-item">Usuarios</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Row start -->
             <form method="POST" action="">
            <input type="hidden" name="employee_id" id="employee_id" value="">
            <div class="row gx-3">
              <div class="col-xxl-12">
                <div class="card mb-3">
                  <div class="card-body">
                    <div class="custom-tabs-container">
                      <ul class="nav nav-tabs" id="customTab2" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-oneA" data-bs-toggle="tab" href="#oneA" role="tab"
                            aria-controls="oneA" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="tab-twoA" data-bs-toggle="tab" href="#twoA" role="tab"
                            aria-controls="twoA" aria-selected="false">Permisos</a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="tab-threeA" data-bs-toggle="tab" href="#threeA" role="tab"
                            aria-controls="threeA" aria-selected="false">Equipos de venta</a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="tab-forA" data-bs-toggle="tab" href="#fourA" role="tab"
                            aria-controls="fourA" aria-selected="false">Asignar presupuesto</a>
                        </li>
                      </ul>
                      <div class="tab-content h-350">
                        <div class="tab-pane fade show active" id="oneA" role="tabpanel">
                          <!-- Row start -->
                          <div class="row gx-3">
                            <div class="col-sm-4 col-12">
                              <div class="mb-3">         
                                    <label for="nombre" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombres" required />
                              </div>
                              <div class="col-md-12">
                        <label for="puesto" class="form-label">Puesto</label>
                        <select id="puesto" name="puesto" class="form-select" required>
                           <?php while ($row = mysqli_fetch_assoc($result_puestos)): ?>
                               <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                           <?php endwhile; ?>
                        </select>
                      </div>
                      <p></p>
                       <div class="mb-3">         
                                    <label for="meta" class="form-label">Presupuesto Anual</label>
                                    <input type="number" class="form-control" id="meta" name="meta" step="0.01" />
                              </div>
                              <div class="mb-3">
                                    <label for="equipo" class="form-label">Equipo</label>
                                    <select id="equipo" name="equipo" class="form-select" required>
                                      <?php while ($row = $result_equipos->fetch_assoc()): ?>
                                          <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label for="apsi" class="form-label">APSI / BUK</label>
                                    <input type="text" class="form-control" id="apsi" name="apsi" placeholder="identificador" />
                                  </div>
                                  <div class="mb-3">
                                    <label for="notas" class="form-label">Notas</label>
                                    <input type="text" class="form-control" id="notas" name="notas" placeholder="Nota" />
                                  </div>
                                  <div class="mb-3">
                                    <label for="update_user" class="form-label">Seleccionar usuario para actualizar</label>
                                    <select id="update_user" name="update_user" class="form-select">
                                      <option value="">Actualizar Usuario</option>
                                      <?php while ($row = mysqli_fetch_assoc($result_empleados)): ?>
                                          <option value="<?php echo $row['id']; ?>"><?php echo $row['correo']; ?></option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>                                  
                            </div>
                            <div class="col-sm-8 col-12">
                              <div class="row gx-3">
                                <div class="col-6">
                                  <!-- Form Field Start -->
                                  <div class="mb-3">
                                    <label for="apellido_paterno" class="form-label">Apellido paterno</label>
                                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" placeholder="Paterno" required />
                                  </div>

                                  <!-- Form Field Start -->
                                  <div class="mb-3">
                                    <label for="categoria" class="form-label">Nivel Asesor</label>
                                    <select id="categoria" name="categoria" class="form-select" required>
                                        <option value="JR">JR</option>
                                        <option value="INTERMEDIO">INTERMEDIO</option>
                                        <option value="SNR">SNR</option>
                                        <option value="ELITE">ELITE</option>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Sucursal</label>
                                    <?php if ($puesto == 'SISTEMAS'): ?>
                                        <input type="text" class="form-control" name="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>" />
                                    <?php else: ?>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($sucursal); ?>" disabled />
                                        <input type="hidden" name="sucursal" value="<?php echo htmlspecialchars($sucursal); ?>" />
                                    <?php endif; ?>
                                  </div>
                                  <div class="mb-3">
                                    <label for="clase" class="form-label">Modelo de negocio</label>
                                    <select id="clase" name="clase" class="form-select">
                                      <option value="MLE">MLE</option>
                                      <option value="TLA">TLA</option>
                                      <option value="MTLA">MTLA</option>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label for="estatus" class="form-label">Estatus</label>
                                    <select id="estatus" name="estatus" class="form-select" required>
                                      <option value="Activo">Activo</option>
                                      <option value="Inactivo">Inactivo</option>
                                      <option value="Baja">Baja</option>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label for="departamento" class="form-label">Departamento</label>
                                    <select id="departamento" name="departamento" class="form-select" required>
                                      <?php while ($row = mysqli_fetch_assoc($result_departamentos)): ?>
                                          <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                      <label for="plantilla" class="form-label">Plantilla</label>
                                      <select id="plantilla" name="plantilla" class="form-select">
                                          <?php for($i = 1; $i <= 20; $i++): ?>
                                              <option value="<?php echo $i; ?>" 
                                                  <?php echo (isset($plantilla_actual) && $plantilla_actual == $i) ? 'selected' : ''; ?>>
                                                  <?php echo $i; ?>
                                              </option>
                                          <?php endfor; ?>                                
                                      </select>
                                  </div>
                                
                                </div>
                                
                                <div class="col-6">
                                  <!-- Form Field Start -->
                                  <div class="mb-3">
                                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" placeholder="Materno" required />
                                  </div>
                                  <div class="mb-3">
                                    <label for="correo" class="form-label">Correo</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo" required />
                                  </div>

                                  <!-- Form Field Start -->
                                  <div class="mb-3">
                                    <label for="birthDay" class="form-label">Cumpleaños</label>
                                    <div class="input-group">
                                      <input type="date" class="form-control" id="birthDay" name="birthDay" />
                                      <span class="input-group-text">
                                        <i class="icon-calendar"></i>
                                      </span>
                                    </div>
                                  </div>
                                  <div class="mb-3">
                                    <label for="supervisor" class="form-label">Supervisor</label>
                                    <select id="supervisor" name="supervisor" class="form-select" required>
                                      <option value= "">Seleccione un supervisor</option>
                                      <?php foreach($supervisores as $supervisor): ?>
                                          <option value="<?php echo htmlspecialchars($supervisor['id']); ?>">
                                              <?php echo htmlspecialchars($supervisor['correo']); ?>
                                          </option>
                                      <?php endforeach; ?>
                                    </select>
                                  </div>
                                  <div class="mb-3">
                                    <label for="shortName" class="form-label">Nombre Corto</label>
                                    <input type="text" class="form-control" id="shortName" name="shortName" placeholder="Nombre Corto" />
                                  </div>
                                  <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                                    <div class="input-group">
                                      <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" />
                                      <span class="input-group-text">
                                        <i class="icon-calendar"></i>
                                      </span>
                                    </div>
                                  </div>
                                </div>
                           
                                <div class="col-6">
                                </div>
                                  <!-- Form Field Start -->
                                 
                                   <!-- New Fields Start -->
                                <div class="col-6">
                                 
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                
                                <div class="col-6">
                                  
                                </div>
                                <!-- New Fields End -->
                              
                              </div>
                            
                            </div>
                          </div>
                          <div class="tab-pane-buttons">
                          <!-- <?php if ($puesto !== 'ASESOR' && $puesto !== 'COORDINADOR'): ?>
                          <button type="submit" class="btn btn-primary">Guardar</button>
                          <?php endif; ?> -->
                          <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                                  
                          <!-- Row end -->
                        </div>
                        <div class="tab-pane fade" id="twoA" role="tabpanel">
                          <div class="card-body">
                            <!-- Row start -->
                            <div class="row gx-3">
                              <div class="col-md-6 col-sm-6 xol-12">
                                <!-- Card start -->
                                <div class="card mb-3">
                                  <div class="card-body">
                                    <ul class="list-group">
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Mostrar Notificaciones
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch"
                                            id="switchOne" />
                                        </div>
                                      </li>
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Solo visibilidad de su equipo
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch" id="switchTwo"
                                            checked />
                                        </div>
                                      </li>
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Ver toda las plazas
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch"
                                            id="switchThree" />
                                        </div>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                                <!-- Card end -->
                              </div>
                              <div class="col-md-6 col-sm-6 xol-12">
                                <!-- Card start -->
                                <div class="card mb-3">
                                  <div class="card-body">
                                    <ul class="list-group">
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Notificacion ajustes en prospectos
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch"
                                            id="switchFour" />
                                        </div>
                                      </li>
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Muestra personal de prospeccion
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch"
                                            id="switchFive" />
                                        </div>
                                      </li>
                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Acceso total
                                        <div class="form-check form-switch m-0">
                                          <input class="form-check-input" type="checkbox" role="switch"
                                            id="switchSix" />
                                        </div>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                                <!-- Card end -->
                              </div>
                            </div>
                            <!-- Row end -->
                          </div>
                        </div>

                        <div class="tab-pane fade" id="threeA" role="tabpanel">
                          <!-- Row start -->
                          <div class="input-group">
                            <span class="input-group-text">Nombre de equipo</span>
                            <input type="text" class="form-control" placeholder="Solo Ventas" id="teamName" />
                          </div>

                          <div class="input-group">
                            <span class="input-group-text">Jefe de ventas</span>
                            <select id="salesManager" class="form-select">
                              <option value="1">Jefe 1</option>
                              <option value="2">Jefe 2</option>
                              <option value="3">Jefe 3</option>
                            </select>
                          </div>

                          <div class="input-group">
                            <span class="input-group-text">Empleado</span>
                            <select id="employee" class="form-select">
                              <option value="1">Empleado 1</option>
                              <option value="2">Empleado 2</option>
                              <option value="3">Empleado 3</option>
                            </select>
                          </div>

                          <div class="table-responsive mt-3">
                            <table class="table align-middle m-0" id="teamTable">
                              <thead>
                                <tr>
                                  <th>Nombre de equipo</th>
                                  <th>Jefe de ventas</th>
                                  <th>Empleado</th>
                                  <th>Acciones</th>
                                </tr>
                              </thead>
                              <tbody>
                                <!-- Aquí se agregarán las filas dinámicamente -->
                              </tbody>
                            </table>
                          </div>
                          <div class="d-flex gap-2 justify-content-end mt-2">
                            <button type="button" class="btn btn-primary" id="addTeamButton">Agregar Equipo</button>
                          </div>
                        
                      </div>
                      <div class="tab-pane fade" id="fourA" role="tabpanel">
                          <div class="mb-3">
                                    <label for="clase" class="form-label">Aplicar presupuesto</label>
                                    <select id="venta_puesto" name="venta_puesto" class="form-select">
                                      <option value="ASESOR">ASESOR</option>
                                      <option value="COORDINADOR">COORDINADOR</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="clase" class="form-label">Asesor / Coordinador Ventas</label>
                                    <select id="presupuesto" name="preupuesto" class="form-select">
                                      <option value=""></option>
                                    </select>
                                </div>
                                <div id="presupuestoTable" style="display: none;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th id="idHeader">ID Asesor</th>
                                                <th>Meta</th>
                                                <th>Mes</th>
                                                <th>Nombre Mes</th>
                                                <th> Meta cero</th>
                                        </thead>
                                        <tbody id="presupuestoTableBody">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane-buttons">
                
                          <button type="submit" class="btn btn-primary" id='asignar_presupuesto'>Asignar presupuesto</button>
                          
                        </div>
                          </div>
                          <!-- Row end -->
                        </div>
                      <div class="d-flex gap-2 justify-content-end">
                        <!-- Botón visible solo en las primeras 2 pestañas -->
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </form>
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

    <!-- Moment JS -->
    <script src="assets/js/moment.min.js"></script>

    <!-- Date Range JS -->
    <script src="assets/vendor/daterange/daterange.js"></script>
    <script src="assets/vendor/daterange/custom-daterange.js"></script>

    <!-- Dropzone JS -->
    <script src="assets/vendor/dropzone/dropzone.min.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>
    
    <style>
        /* Convert text fields to uppercase */
        input[type="text"], 
        input[type="email"], 
        textarea {
            text-transform: uppercase;
        }
        
        /* Ensure placeholder text is also uppercase */
        input[type="text"]::placeholder, 
        input[type="email"]::placeholder, 
        textarea::placeholder {
            text-transform: uppercase;
        }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Handle update user selection
        const updateUserSelect = document.getElementById('update_user');
        console.log('Update user select found:', updateUserSelect);
        
        updateUserSelect.addEventListener('change', function() {
            const employeeId = this.value;
            console.log('Selected employee ID:', employeeId);
            
            if (employeeId) {
                console.log('Fetching employee data for ID:', employeeId);
                // Fetch employee data and populate form
                fetch(`./get_employee.php?id=${employeeId}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Employee data received:', data);
                        
                        // Check if each field exists before setting value
                        const fields = [
                            { id: 'employee_id', value: data.id },
                            { id: 'nombre', value: data.nombre },
                            { id: 'apellido_paterno', value: data.apellido_paterno },
                            { id: 'apellido_materno', value: data.apellido_materno },
                            { id: 'correo', value: data.correo },
                            { id: 'puesto', value: data.puesto },
                            { id: 'categoria', value: data.categoria },
                            { id: 'meta', value: data.meta },
                            { id: 'equipo', value: data.equipo },
                            { id: 'apsi', value: data.apsi },
                            { id: 'notas', value: data.notas },
                            { id: 'supervisor', value: data.supervisor },
                            { id: 'estatus', value: data.estado_empleado || 'Activo' },
                            { id: 'shortName', value: data.iniciales },
                            { id: 'departamento', value: data.departamento },
                            { id: 'clase', value: data.tipo || data.clase }, // Try both possible field names
                            { id: 'plantilla', value: data.plantilla},
                            { id: 'id_supervisor', value: data.id_supervisor || 0}
                        ];
                        
                        fields.forEach(field => {
                            const element = document.getElementById(field.id);
                            if (element) {
                                if (field.value !== null && field.value !== undefined) {
                                    // Convert text fields to uppercase
                                    if (element.type === 'text' || element.type === 'email' || element.tagName === 'TEXTAREA') {
                                        element.value = field.value.toUpperCase();
                                    } else {
                                        element.value = field.value;
                                    }
                                    console.log(`Set ${field.id} to:`, element.value);
                                } else {
                                    console.log(`Skipping ${field.id} - value is null/undefined`);
                                }
                            } else {
                                console.error(`Element with id '${field.id}' not found`);
                            }
                        });
                        
                        // Handle date fields separately
                        if (data.fecha_cumple || data.fecha_nacimiento) {
                            const birthDayElement = document.getElementById('birthDay');
                            if (birthDayElement) {
                                const birthDate = data.fecha_cumple || data.fecha_nacimiento;
                                birthDayElement.value = birthDate;
                                console.log('Set birthDay to:', birthDate);
                            }
                        }
                        
                        if (data.fecha_inicio) {
                            const fechaInicioElement = document.getElementById('fecha_inicio');
                            if (fechaInicioElement) {
                                fechaInicioElement.value = data.fecha_inicio;
                                console.log('Set fecha_inicio to:', data.fecha_inicio);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching employee data:', error);
                        console.error('Error details:', {
                            name: error.name,
                            message: error.message,
                            stack: error.stack
                        });
                        alert('Error al cargar los datos del empleado: ' + error.message);
                    });
            } else {
                console.log('Clearing form for new employee');
                // Clear form for new employee
                const employeeIdElement = document.getElementById('employee_id');
                if (employeeIdElement) {
                    employeeIdElement.value = '';
                }
                document.querySelector('form').reset();
            }
        });
        
        // Add validation for meta based on puesto
        const puestoSelect = document.getElementById('puesto');
        const metaInput = document.getElementById('meta');
        
        function validateMeta() {
            const puestoText = puestoSelect.options[puestoSelect.selectedIndex].text;
            if (puestoText === 'ASESOR' || puestoText === 'COORDINADOR') {
                metaInput.required = true;
                metaInput.setAttribute('min', '0.01');
            } else {
                metaInput.required = false;
                metaInput.removeAttribute('min');
            }
        }
        
        puestoSelect.addEventListener('change', validateMeta);
        validateMeta(); // Run on page load
        
        // Handle form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const puestoText = puestoSelect.options[puestoSelect.selectedIndex].text;
            const metaValue = parseFloat(metaInput.value) || 0;
            
            if ((puestoText === 'ASESOR' || puestoText === 'COORDINADOR') && metaValue <= 0) {
                e.preventDefault();
                alert('El presupuesto anual es obligatorio para el puesto de ' + puestoText);
                metaInput.focus();
                return false;
            }
        });
        
        // Handle sucursal field based on user role
        const sucursalInput = document.querySelector('input[name="sucursal"]');
        const userRole = '<?php echo $puesto; ?>';
        
        if (userRole !== 'SISTEMAS' && sucursalInput) {
            sucursalInput.disabled = true;
        }

        // Convert text fields to uppercase on input
        const textFields = document.querySelectorAll('input[type="text"], input[type="email"], textarea');
        textFields.forEach(field => {
            field.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        });

        // Add this new code for presupuesto functionality
        const ventaPuestoSelect = document.getElementById('venta_puesto');
        const presupuestoSelect = document.getElementById('presupuesto');
        const presupuestoTable = document.getElementById('presupuestoTable');
        const presupuestoTableBody = document.getElementById('presupuestoTableBody');
        const idHeader = document.getElementById('idHeader');

        // Define months array in global scope
        const months = [
            'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
            'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
        ];

        // Function to update presupuesto dropdown
        function updatePresupuestoDropdown() {
            const selectedPuesto = ventaPuestoSelect.value;
            
            // Clear current options
            presupuestoSelect.innerHTML = '<option value="">Seleccione un empleado</option>';
            
            // Fetch employees based on selected puesto and sucursal
            fetch(`get_employees_by_puesto.php?puesto=${selectedPuesto}&sucursal=<?php echo urlencode($sucursal); ?>`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(employee => {
                        const option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = employee.correo;
                        presupuestoSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        // Function to create month rows
        // Function to create month rows
          // Function to create month rows
          function createMonthRows(employeeId, puesto) {
              presupuestoTableBody.innerHTML = '';

              fetch(`get_meta_venta.php?id=${employeeId}&puesto=${puesto}`)
                  .then(response => response.json())
                  .then(response => {
                      console.log('Respuesta:', response);

                      if (!response.success) {
                          throw new Error(response.error || 'Error al cargar los datos');
                      }

                      // Create a map of existing meta values
                      const metaMap = new Map();
                      
                      // CAMBIO: Solo llenamos el Map si hay datos
                      if (response.data && response.data.length > 0) {

                        console.log('Datos encontrados:', response.data.length);
                          response.data.forEach(item => {
                              metaMap.set(item.mes, {
                                  meta: item.meta,
                                  meta_cero: item.meta_cero || 0
                              });
                          });
                      }

                      // SIEMPRE creamos las 12 filas, aunque no haya datos guardados
                      months.forEach((month, index) => {
                          const row = document.createElement('tr');
                          const mesNumero = index + 1;
                          
                          // Si no hay datos guardados, usamos valores vacíos por defecto
                          const existingData = metaMap.get(mesNumero) || { meta: '', meta_cero: 0 };
                          const isMetaCero = existingData.meta_cero == 1;
                          
                          row.innerHTML = `
                              <td>${employeeId}</td>
                              <td>
                                  <input type="number" class="form-control meta-input" 
                                        name="meta_mes_${mesNumero}" 
                                        data-mes="${mesNumero}"
                                        min="0" step="0.01" 
                                        value="${existingData.meta}"
                                        placeholder="0.00">
                              </td>
                              <td>${mesNumero}</td>
                              <td>${month}</td>
                              <td>
                                  <div class="form-check">
                                      <input class="form-check-input meta-cero-checkbox" 
                                            type="checkbox" 
                                            data-mes="${mesNumero}" 
                                            id="metaCero${mesNumero}"
                                            ${isMetaCero ? 'checked' : ''}>
                                      <label class="form-check-label" for="metaCero${mesNumero}">
                                          Meta en cero
                                      </label>
                                  </div>
                              </td>
                          `;
                          presupuestoTableBody.appendChild(row);
                      });

                      // Update header based on puesto
                      idHeader.textContent = puesto === 'ASESOR' ? 'ID Asesor' : 'ID Coordinador';
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert(error.message || 'Error al cargar los datos del presupuesto');
                  });
          }

          


        // Event listeners
        ventaPuestoSelect.addEventListener('change', function() {
            updatePresupuestoDropdown();
            presupuestoTable.style.display = 'none';
        });

        presupuestoSelect.addEventListener('change', function() {
            const selectedEmployeeId = this.value;
            const selectedPuesto = ventaPuestoSelect.value;
            
            if (selectedEmployeeId) {
                createMonthRows(selectedEmployeeId, selectedPuesto);
                presupuestoTable.style.display = 'table';
            } else {
                presupuestoTable.style.display = 'none';
            }
        });

        // Handle presupuesto form submission
        // Handle presupuesto form submission
          // Handle presupuesto form submission
          // Handle presupuesto form submission
              document.getElementById('asignar_presupuesto').addEventListener('click', function(e) {
                  e.preventDefault();
                  
                  const selectedEmployeeId = presupuestoSelect.value;
                  const selectedPuesto = ventaPuestoSelect.value;
                  
                  if (!selectedEmployeeId) {
                      alert('Por favor seleccione un empleado');
                      return;
                  }

                  const metas = [];
                  const inputs = presupuestoTableBody.querySelectorAll('.meta-input');
                  const checkboxes = presupuestoTableBody.querySelectorAll('.meta-cero-checkbox');
                  
                  inputs.forEach((input, index) => {
                      const value = parseFloat(input.value) || 0;
                      const metaCero = checkboxes[index].checked ? 1 : 0;
                      
                      if (value > 0) {
                          metas.push({
                              mes: index + 1,
                              meta: value,
                              nombre_mes: months[index],
                              meta_cero: metaCero
                          });
                      }
                  });

                  if (metas.length === 0) {
                      alert('Por favor ingrese al menos un valor de meta');
                      return;
                  }

                  // Save meta data with improved error handling
                  fetch('save_meta_venta.php', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                      },
                      body: JSON.stringify({
                          id: selectedEmployeeId,
                          puesto: selectedPuesto,
                          metas: metas
                      })
                  })
                  .then(response => {
                      // First, let's see what we actually got back
                      console.log('Response status:', response.status);
                      console.log('Response headers:', response.headers.get('content-type'));
                      
                      // Get the raw text first to see what's actually being returned
                      return response.text().then(text => {
                          console.log('Raw response:', text);
                          
                          // Try to parse it as JSON
                          try {
                              return JSON.parse(text);
                          } catch (e) {
                              console.error('Failed to parse JSON:', e);
                              throw new Error('El servidor no devolvió una respuesta JSON válida. Respuesta: ' + text.substring(0, 200));
                          }
                      });
                  })
                  .then(response => {
                      if (!response.success) {
                          throw new Error(response.error || 'Error al guardar el presupuesto');
                      }
                      alert('Presupuesto guardado exitosamente');
                      createMonthRows(selectedEmployeeId, selectedPuesto);
                  })
                  .catch(error => {
                      console.error('Error completo:', error);
                      alert(error.message || 'Error al guardar el presupuesto');
                  });
              });

        // Initial load
        updatePresupuestoDropdown();
      });
    </script>
    
    <?php if (!$acceso): ?>
    <?php echo generarScriptDeshabilitarElementos(); ?>
    <?php endif; ?>

  </body>

</html>