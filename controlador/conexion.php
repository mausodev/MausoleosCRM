<?php
// Parámetros de conexión a la base de datos
$servidor = "localhost";     // Servidor de la base de datos
$usuario = "root";          // u423288535_TdnDBA
$password = "admin";             // Mau25Tdn+1
$basedatos = "mausoleo_local";    // u423288535_globalMausoleo



// Crear conexión
$con = mysqli_connect($servidor, $usuario, $password, $basedatos);


if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}


mysqli_set_charset($con, "utf8");

// No es necesario cerrar la conexión aquí, ya que este archivo será incluido en otros
// y la conexión se cerrará cuando sea necesario en esos archivos