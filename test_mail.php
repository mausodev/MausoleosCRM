<?php
// test_mail.php
// Coloca este archivo en la MISMA carpeta donde está tu account-settings.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Probando configuración de PHPMailer</h2>";

// Primero, verificar si existen los archivos
echo "<h3>1. Verificando archivos de PHPMailer:</h3>";

$files_to_check = [
    'PHPMailer/PHPMailer.php',
    'PHPMailer/Exception.php',
    'PHPMailer/SMTP.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✓ <span style='color:green'>Encontrado: {$file}</span><br>";
    } else {
        echo "✗ <span style='color:red'>NO encontrado: {$file}</span><br>";
        echo "  Ruta completa intentada: " . __DIR__ . "/{$file}<br>";
    }
}

echo "<hr>";

// Intentar diferentes formas de incluir PHPMailer
echo "<h3>2. Intentando cargar PHPMailer:</h3>";

// Opción 1: Ruta relativa (como en tu código original)
if (file_exists('PHPMailer/PHPMailer.php')) {
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/SMTP.php';
    echo "✓ Cargado con ruta relativa<br>";
}
// Opción 2: Buscar en carpeta padre
elseif (file_exists('../PHPMailer/PHPMailer.php')) {
    require '../PHPMailer/PHPMailer.php';
    require '../PHPMailer/Exception.php';
    require '../PHPMailer/SMTP.php';
    echo "✓ Cargado desde carpeta padre<br>";
}
// Opción 3: Buscar en vendor (si usas Composer)
elseif (file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require 'vendor/phpmailer/phpmailer/src/Exception.php';
    require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    echo "✓ Cargado desde vendor (Composer)<br>";
}
else {
    die("<span style='color:red'>ERROR: No se pueden encontrar los archivos de PHPMailer en ninguna ubicación común.</span><br>
         Por favor, verifica que la carpeta PHPMailer existe y contiene los archivos necesarios.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<hr>";
echo "<h3>3. Probando envío de correo:</h3>";

try {
    $mail = new PHPMailer(true);
    
    // Configuración del servidor SMTP con debug
    $mail->SMTPDebug = 2; // 0 = off, 1 = client, 2 = client and server
    $mail->Debugoutput = function($str, $level) {
        echo "Debug level $level: $str<br>";
    };
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'crmmauso@gmail.com';
    $mail->Password = 'fjqa kkqe nmbm cxpy'; // Tu contraseña de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // o 'ssl'
    $mail->Port = 465;
    
    // Configuración del correo
    $mail->setFrom('crmmauso@gmail.com', 'Portal Mausoleos - TEST');
    
    // IMPORTANTE: Cambia este correo por uno tuyo real para la prueba
    $mail->addAddress('arnold.gonzalez@mle.com.mx', 'Usuario Test');
    
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <h2>Correo de Prueba</h2>
        <p>Si recibes este correo, significa que PHPMailer está funcionando correctamente.</p>
        <p>Fecha y hora de envío: ' . date('Y-m-d H:i:s') . '</p>
    ';
    $mail->AltBody = 'Correo de prueba - PHPMailer funcionando';
    
    // Intentar enviar
    if ($mail->send()) {
        echo "<hr>";
        echo "<h3 style='color:green'>✓ ¡CORREO ENVIADO EXITOSAMENTE!</h3>";
        echo "<p>Revisa la bandeja de entrada (y spam) de: arnold.gonzalez@mle.com.mx</p>";
    }
    
} catch (Exception $e) {
    echo "<hr>";
    echo "<h3 style='color:red'>✗ ERROR AL ENVIAR CORREO:</h3>";
    echo "<pre style='background:#ffe6e6; padding:10px; border:1px solid red;'>";
    echo "Mensaje de error: {$mail->ErrorInfo}\n";
    echo "Excepción: {$e->getMessage()}";
    echo "</pre>";
    
    echo "<h4>Posibles soluciones:</h4>";
    echo "<ol>";
    echo "<li>Verifica que la contraseña de aplicación de Gmail sea correcta</li>";
    echo "<li>Asegúrate de tener habilitada la verificación en 2 pasos en Gmail</li>";
    echo "<li>Genera una nueva contraseña de aplicación en: <a href='https://myaccount.google.com/apppasswords' target='_blank'>https://myaccount.google.com/apppasswords</a></li>";
    echo "<li>Verifica que el servidor no bloquee el puerto 465</li>";
    echo "<li>Intenta con el puerto 587 y SMTPSecure = 'tls'</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h3>4. Información del sistema:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✓ Habilitado' : '✗ Deshabilitado') . "<br>";
echo "Socket: " . (extension_loaded('sockets') ? '✓ Habilitado' : '✗ Deshabilitado') . "<br>";
echo "Ruta actual: " . __DIR__ . "<br>";
?>