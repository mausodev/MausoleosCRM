<?php


session_start();

$host = 'localhost';
$db = 'mausoleo_local'; // u423288535_globalMausoleo
$user = 'root'; // u423288535_TdnDBA
$pass = 'admin'; // Mau25Tdn+1


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

$correo = 'userMauso@mle.com.mx';
$contrasena = password_hash('Mauso@123', PASSWORD_DEFAULT);

//$role = 'admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $correo = $_POST['correo'];
  $contrasena = $_POST['contrasena'];

  // Consultar si el usuario existe
  $stmt = $pdo->prepare("SELECT * FROM empleado WHERE correo = :correo");
  //$stmt->bind_param("correo", $correo);
  $stmt->execute(['correo' => $correo]);
  //$result = $stmt->get_result(); // Get the result set
  $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the associative array
 

  if ($user && password_verify($contrasena, $user['contrasena'])) { // 
    
      // Verificar si el usuario tiene rol asignado
      if (empty($user['id_rol']) || $user['id_rol'] == null) {
          $error = "Este usuario no tiene rol asignado. Contacte al administrador.";
      } else {
          // Verificar si el puesto permite selección de sucursal
          if (($user['puesto'] == 'DIRECTOR' || $user['puesto'] == 'EJECUTIVO PLAZA') && !isset($_POST['sucursal_seleccionada'])) {
              // Mostrar formulario de selección de sucursal
              $mostrar_seleccion_sucursal = true;
              $usuario_para_sucursal = $user;
          } else {
              // Login exitoso
              $_SESSION['id'] = $user['id'];
              $_SESSION['iniciales'] = $user['iniciales'];
              $_SESSION['supervisor'] = $user['supervisor'];
              $_SESSION['correo'] = $user['correo'];
              
              // Asignar sucursal según el caso
              if (($user['puesto'] == 'DIRECTOR' || $user['puesto'] == 'EJECUTIVO PLAZA') && isset($_POST['sucursal_seleccionada'])) {
                  $_SESSION['sucursal'] = $_POST['sucursal_seleccionada'];
              } else {
                  $_SESSION['sucursal'] = $user['sucursal'];
              }
              
              $_SESSION['departamento'] = $user['departamento'];
              $_SESSION['puesto'] = $user['puesto'];
              $_SESSION['rol_venta'] = $user['rol_venta'];
              $_SESSION['id_rol'] = $user['id_rol'];
              
              // Obtener pantallas permitidas para el rol del usuario
              $stmt_pantallas = $pdo->prepare("
                  SELECT p.ruta 
                  FROM permiso pe 
                  INNER JOIN pantalla p ON pe.id_pantalla = p.id 
                  WHERE pe.id_rol = :id_rol AND pe.puede_ver = 1 
                  ORDER BY p.id ASC 
                  LIMIT 1
              ");
              $stmt_pantallas->execute(['id_rol' => $user['id_rol']]);
              $pantalla_permitida = $stmt_pantallas->fetch(PDO::FETCH_ASSOC);
              
              if ($pantalla_permitida) {
                  // Redirigir a la primera pantalla permitida
                  header("Location: " . $pantalla_permitida['ruta']);
              } else {
                  // Si no tiene pantallas asignadas, mostrar mensaje
                  $error = "No tiene permisos para acceder a ninguna pantalla. Contacte al administrador.";
              }
              exit(); // Asegurarse de que no se ejecute más código después de la redirección
          }
      }
  } else {
    $error = "Usuario o contraseña incorrectos.";
      //echo '<label style="color: red;"><strong>' . htmlspecialchars($error) . '<strong></label>';
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
		************* login-bg -->
    <!-- Icomoon Font Icons css -->
    <link rel="stylesheet" href="assets/fonts/icomoon/style.css" />

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.min.css" />
  </head>

  <body class="login-bg">
    <!-- Container start -->
    <div class="container p-0">
      <!-- Row start -->
      <div class="row g-0">
        <div class="col-xl-6 col-lg-12"></div>
        <div class="col-xl-6 col-lg-12">
          <!-- Row start -->
          <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-sm-4 col-12">
              <form  id="login.php" method="POST" class="my-5" >
                <div class="bg-white p-5 rounded-4">
                  <div class="login-form">
                    <a href="#" class="mb-3 d-flex">
                     <img src="assets/images/MLElogoSmall.png" class="img-fluid login-logo style:text-align: center; " alt="Admin Dashboard" />
                    </a>
                    <h2 class="mt-4 mb-4">
                      <?php echo (isset($mostrar_seleccion_sucursal) && $mostrar_seleccion_sucursal) ? 'Seleccionar Sucursal' : 'Ingresar'; ?>
                    </h2>
                    <?php if (isset($mostrar_seleccion_sucursal) && $mostrar_seleccion_sucursal): ?>
                    <div class="alert alert-info mb-3">
                      <i class="icon-info-circle me-2"></i>
                      Como <?php echo htmlspecialchars($usuario_para_sucursal['puesto']); ?>, puede seleccionar la sucursal que desea consultar.
                    </div>
                    <?php endif; ?>
                    <?php if (!isset($mostrar_seleccion_sucursal) || !$mostrar_seleccion_sucursal): ?>
                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input type="text" class="form-control" id="correo" name="correo" placeholder="Ingresa tu correo" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Contraseña</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" />
                        <a href="#" class="input-group-text">
                          <i class="icon-eye"></i>
                        </a>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($mostrar_seleccion_sucursal) && $mostrar_seleccion_sucursal): ?>
                    <div class="mb-3">
                      <label class="form-label">Seleccione la sucursal a consultar</label>
                      <select class="form-select" name="sucursal_seleccionada" required>
                        <option value="">-- Seleccione una sucursal --</option>
                        <option value="DELICIAS">DELICIAS</option>
                        <option value="CUAUHTEMOC">CUAUHTEMOC</option>
                        <option value="CHIHUAHUA">CHIHUAHUA</option>
                        <option value="JUAREZ">JUAREZ</option>
                        <option value="TIJUANA">TIJUANA</option>
                      </select>
                      <!-- Campos ocultos para mantener los datos del usuario -->
                      <input type="hidden" name="correo" value="<?php echo htmlspecialchars($usuario_para_sucursal['correo']); ?>">
                      <input type="hidden" name="contrasena" value="<?php echo htmlspecialchars($_POST['contrasena']); ?>">
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="form-check m-0">
                        <input class="form-check-input" type="checkbox" value="" id="rememberPassword" />
                        <label class="form-check-label" for="rememberPassword">Recordar</label>
                      </div>
                      <a href="forgot-password.html" class="text-primary text-decoration-underline">olvidaste tu contraseña?</a>
                    </div>
                    <?php endif; ?>
                    <p >
                      <?php
                      if (!empty($error)) {
                        echo '<label style="color: red;">' . htmlspecialchars($error) . '</label>';
                      }
                      ?>
                      <!--<label id="message"></label>-->
                    </p>
                    <div class="d-grid py-3 mt-3 gap-3">
                      
                        <button type="submit" class="btn btn-lg btn-primary">
                          <?php echo (isset($mostrar_seleccion_sucursal) && $mostrar_seleccion_sucursal) ? 'CONTINUAR' : 'INICIAR SESION'; ?>
                        </button>
                      
                      <?php if (!isset($mostrar_seleccion_sucursal) || !$mostrar_seleccion_sucursal): ?>
                      <a href="#" class="btn btn-lg btn-outline-dark"><!--signup.html-->
                        SOLICITAR ACCESO
                      </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- Row end -->
        </div>
      </div>
      <!-- Row end -->
    </div>
    <!-- Container end -->

    <!--<script>
      // Usuario y contraseña hardcodeados
      const validUsername = "testMauso@mle.com.mx";
      const validPassword = "Mauso@123";

      document.getElementById("loginForm").addEventListener("submit", function(event) {
          event.preventDefault(); // Prevenir el envío del formulario

          const username = document.getElementById("username").value;
          const password = document.getElementById("password").value;
          const message = document.getElementById("message");

          if (username === validUsername && password === validPassword) {
              message.style.color = "green";
              message.textContent = "Inicio de sesión exitoso.";
              setTimeout(() => {
                    window.location.href = "clients.php"; // Reemplaza con tu página de destino
                }, 1000);
          } else {
              message.style.color = "red";
              message.textContent = "Usuario o contraseña incorrectos.";
          }
      });
  </script>-->
  </body>

</html>