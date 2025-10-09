<?php
// Archivo de prueba para verificar el sistema de avisos
require './controlador/conexion.php';

echo "<h1>Prueba del Sistema de Avisos</h1>";

// Verificar conexión a la base de datos
if ($con) {
    echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";
} else {
    echo "<p style='color: red;'>✗ Error de conexión a la base de datos</p>";
    exit;
}

// Verificar que las tablas existen
$tablas_requeridas = ['aviso_portal', 'avisos_receptores', 'empleado'];

foreach ($tablas_requeridas as $tabla) {
    $result = $con->query("SHOW TABLES LIKE '$tabla'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Tabla '$tabla' existe</p>";
    } else {
        echo "<p style='color: red;'>✗ Tabla '$tabla' no existe</p>";
    }
}

// Verificar estructura de la tabla aviso_portal
echo "<h2>Estructura de la tabla aviso_portal:</h2>";
$result = $con->query("DESCRIBE aviso_portal");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error al obtener estructura de la tabla</p>";
}

// Verificar estructura de la tabla avisos_receptores
echo "<h2>Estructura de la tabla avisos_receptores:</h2>";
$result = $con->query("DESCRIBE avisos_receptores");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error al obtener estructura de la tabla</p>";
}

// Verificar empleados disponibles
echo "<h2>Empleados disponibles:</h2>";
$result = $con->query("SELECT id, nombre, correo, puesto FROM empleado WHERE activo = 1 LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Puesto</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['correo'] . "</td>";
        echo "<td>" . $row['puesto'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No hay empleados activos o error en la consulta</p>";
}

// Verificar avisos existentes
echo "<h2>Avisos existentes:</h2>";
$result = $con->query("SELECT COUNT(*) as total FROM aviso_portal");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Total de avisos en la base de datos: <strong>" . $row['total'] . "</strong></p>";
} else {
    echo "<p style='color: red;'>Error al contar avisos</p>";
}

// Verificar archivos del sistema
echo "<h2>Archivos del sistema:</h2>";
$archivos_requeridos = [
    'aviso.php',
    'controlador/guardar_aviso.php',
    'controlador/ver_aviso.php',
    'controlador/marcar_leido.php',
    'controlador/guardar_ticket_it.php',
    'controlador/get_estadisticas_avisos.php',
    'assets/css/avisos.css'
];

foreach ($archivos_requeridos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✓ Archivo '$archivo' existe</p>";
    } else {
        echo "<p style='color: red;'>✗ Archivo '$archivo' no existe</p>";
    }
}

echo "<h2>Instrucciones de uso:</h2>";
echo "<ol>";
echo "<li>Accede a <a href='aviso.php'>aviso.php</a> para usar el sistema</li>";
echo "<li>Asegúrate de estar logueado en el sistema</li>";
echo "<li>Verifica que tienes permisos para acceder a la pantalla de avisos</li>";
echo "<li>Las tablas de base de datos deben estar creadas según el esquema proporcionado</li>";
echo "</ol>";

echo "<h2>Notas importantes:</h2>";
echo "<ul>";
echo "<li>El sistema utiliza las tablas 'aviso_portal' y 'avisos_receptores'</li>";
echo "<li>Los empleados deben estar en la tabla 'empleado' con campo 'activo = 1'</li>";
echo "<li>El sistema requiere sesión de usuario activa</li>";
echo "<li>Los tickets IT se asignan automáticamente al área de IT</li>";
echo "</ul>";

$con->close();
?>
