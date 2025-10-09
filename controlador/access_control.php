<?php
/**
 * Access Control Functions
 * Reusable functions for role-based access control across all PHP screens
 */

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

function verificarAcceso() {
  global $con;
  
  // Start session if not already started
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  
  // Check if user is logged in
  if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
  }
  
  // Get current file path
  $rutaActual = basename($_SERVER['PHP_SELF']);
  
  // Get session variables
  $id_asesor = $_SESSION['id'];
  $inicial = $_SESSION['iniciales'];
  $supervisor = $_SESSION['supervisor'];
  $correo = $_SESSION['correo'];
  $sucursal = $_SESSION['sucursal'];
  $departamento = $_SESSION['departamento'];
  $puesto = $_SESSION['puesto'];
  $rol_venta = $_SESSION['rol_venta'];
  $id_Rol = $_SESSION['id_rol'];
  
  // Check access permission
  $acceso = true;
  if (!tienePermiso($id_Rol, $rutaActual)) {
    $acceso = false;
  }
  
  // Return access control data
  return [
    'acceso' => $acceso,
    'id_asesor' => $id_asesor,
    'inicial' => $inicial,
    'supervisor' => $supervisor,
    'correo' => $correo,
    'sucursal' => $sucursal,
    'departamento' => $departamento,
    'puesto' => $puesto,
    'rol_venta' => $rol_venta,
    'id_Rol' => $id_Rol,
    'rutaActual' => $rutaActual
  ];
}

function generarOverlayAccesoDenegado() {
  return '
  <!-- Overlay de bloqueo cuando no hay acceso -->
  <div id="access-denied-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
    <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
      <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">
        <i class="fas fa-lock"></i>
      </div>
      <h2 style="color: #333; margin-bottom: 15px;">Acceso Denegado</h2>
      <p style="color: #666; margin-bottom: 25px; font-size: 16px;">
        No tienes permisos para acceder a esta pantalla.
      </p>
      <button onclick="window.history.back()" style="background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 14px;">
        <i class="fas fa-arrow-left"></i> Volver
      </button>
    </div>
  </div>';
}

function generarScriptDeshabilitarElementos() {
  return '
  <script>
  document.addEventListener("DOMContentLoaded", function () {
      // Deshabilitar todos los elementos interactivos si no hay acceso
      document.querySelectorAll("form").forEach(function(form) {
          form.style.pointerEvents = "none";
          form.style.opacity = "0.5";
      });
      
      // Deshabilitar todos los botones
      document.querySelectorAll("button").forEach(function(button) {
          button.disabled = true;
          button.style.opacity = "0.5";
      });
      
      // Deshabilitar todos los enlaces
      document.querySelectorAll("a").forEach(function(link) {
          link.style.pointerEvents = "none";
          link.style.opacity = "0.5";
      });
      
      // Deshabilitar todos los inputs
      document.querySelectorAll("input, select, textarea").forEach(function(input) {
          input.disabled = true;
          input.style.opacity = "0.5";
      });
  });
  </script>';
}
?>
