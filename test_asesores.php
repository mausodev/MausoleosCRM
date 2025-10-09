<?php
// Test file to verify get_asesores.php is working
echo "<h2>Testing get_asesores.php</h2>";

// Test CUAUHTEMOC
echo "<h3>Testing CUAUHTEMOC plaza:</h3>";
$url = "http://localhost/InicioPortal/controlador/get_asesores.php?plaza=CUAUHTEMOC";
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test DELICIAS
echo "<h3>Testing DELICIAS plaza:</h3>";
$url = "http://localhost/InicioPortal/controlador/get_asesores.php?plaza=DELICIAS";
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test invalid plaza
echo "<h3>Testing invalid plaza:</h3>";
$url = "http://localhost/InicioPortal/controlador/get_asesores.php?plaza=INVALID";
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
