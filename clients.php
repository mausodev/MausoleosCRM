<?php
session_start();

require './controlador/conexion.php';

// Helper function to handle NULL values in SQL queries
function sqlValue($value, $isNumeric = false, $isRequired = false) {
    // Para campos numéricos, asegurar que siempre devuelva un valor válido
    if ($isNumeric) {
        if ($value === null || $value === '') {
            return '0'; // Valor por defecto para campos numéricos
        }
        return (string)$value; // Convertir a string para evitar problemas
    }
    
    // Para campos de texto, manejar valores vacíos apropiadamente
    if (empty($value) && $value !== '0' && $value !== 0) {
        // Si es un campo requerido, enviar como cadena vacía en lugar de NULL
        if ($isRequired) {
            return "''";
        }
        // Para campos NOT NULL, devolver cadena vacía en lugar de NULL
        return "''";
    }
    
    return "'" . addslashes($value) . "'";
}

if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit();
}
// Acceder a los datos de la sesión
     $id_asesor = $_SESSION['id'];
     $inicial = $_SESSION['iniciales'];
     $supervisor = $_SESSION['supervisor'];
     $correo = $_SESSION['correo'];
     $sucursal = $_SESSION['sucursal'];
     $departamento = $_SESSION['departamento'];
     $puesto = $_SESSION['puesto'];
     $rol_venta = $_SESSION['rol_venta'];
     $modelo_negocio = $_SESSION['modelo_negocio'];
$loggedInUserPuesto = isset($_SESSION['puesto']) ? $_SESSION['puesto'] : '';
$loggedInUserIniciales = isset($_SESSION['correo']) ? $_SESSION['correo'] : '';
$Validacion = null;

$sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $mes = $row['mes']; 
      
  } else {
    $mes = 'N/A';
  }

 try {
  $cliente_id = filter_input(INPUT_GET, 'cliente_id', FILTER_VALIDATE_INT);
  /*if ($cliente_id === false || $cliente_id <= 0) {
    header("Location: clients.php");
  }*/
  if($puesto == 'ASESOR'){
    $sql = "SELECT id, correo FROM empleado WHERE sucursal = '{$_SESSION['sucursal']}' AND id = '$id_asesor'";
  } 

  if($puesto == 'COORDINADOR'){
    $sql = "SELECT id, correo FROM empleado WHERE sucursal = '{$_SESSION['sucursal']}' AND supervisor = '$inicial'";
  }else {
    $sql = "SELECT id, correo FROM empleado WHERE sucursal = '{$_SESSION['sucursal']}' AND puesto = 'ASESOR'";
  }
  
  $conn = new mysqli('localhost', 'root', 'admin', 'mausoleo_local'); //'u423288535_TdnDBA', 'Mau25Tdn+1', 'u423288535_globalMausoleo global_mausoleo


  // Verificar conexión
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sqlCliente =  "SELECT * FROM cliente WHERE id = ? AND asesor = ?";
  $stmt = $conn->prepare($sqlCliente );
  $stmt->bind_param("ii", $cliente_id, $id_asesor);
  $stmt->execute();
  $result = $stmt->get_result();
  $pertenece = FALSE;

  if($result->num_rows > 0 && $puesto == 'ASESOR'){
    $pertenece = TRUE;
    
  }

  IF($pertenece == FALSE && $cliente_id && $puesto == 'ASESOR'){
     DIE('Acceso denegado. Este cliente no pertenece a tu cuenta.');
  }

  $stmt = $conn->query($sql);

  // Obtener resultados
  $empleados = [];
  while ($row = $stmt->fetch_assoc()) {
      $empleados[] = $row;
  }

} catch (PDOException $e) {
  echo "Advertencia: " . $e->getMessage();
}



 if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  try {
    $search_rfc = $_POST['curp'] ?? NULL;
    $folio = $_POST['folio'] ?? 0;

   

    // Validar si el cliente ya existe en las etapas ACTIVAR, ESTRECHAR o EN PRONOSTICO
    if ($search_rfc) {
        $sqlCheckRFC = "SELECT * FROM cliente WHERE curp = ? AND etapa IN ('ESTRECHAR', 'EN PRONOSTICO') AND asesor != ?";
        $stmtCheck = $conn->prepare($sqlCheckRFC);
        $stmtCheck->bind_param("si", $search_rfc, $id_asesor);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            throw new Exception("Este cliente ya está siendo atendido por otro asesor en una etapa activa.");
        }
    }

    $sqlSearchRFC = "SELECT * FROM cliente WHERE curp = '$search_rfc' AND asesor = '$id_asesor' LIMIT 1";
    $resultSearchRFC = $conn->query($sqlSearchRFC);
    if ($resultSearchRFC && $resultSearchRFC->num_rows > 0) {
        $cliente = $resultSearchRFC->fetch_assoc();
        $num_venta = $cliente['numero_venta'] + 1;
    } else {
        $num_venta = 0;
    }

    

  // Obtener datos del formulario con validación
  $proyeccion = false;
  $porcentaje = 0;
  $venta_embudo = 0;
  $nombre_asesor = 'no asignado';
  $estatus = 'open';
  $iva = 0;

  
 // Asignación de valores de formulario

function limpiarNumero($campo) {
  return isset($_POST[$campo]) && is_numeric($_POST[$campo]) ? $_POST[$campo] : 0;
}
 
$id = $_POST['id'] ?? null;
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$apellidoPaterno = isset($_POST['apellidoPaterno']) ? $_POST['apellidoPaterno'] : '';
$apellidoMaterno = isset($_POST['apellidoMaterno']) ? $_POST['apellidoMaterno'] : '';
// Obtener etapa del formulario para todos los modelos
$etapa = $_POST['etapa'] ?? NULL;
$fechaCompromiso = isset($_POST['fechaCompromiso']) ? $_POST['fechaCompromiso'] : NULL;

// Validación especial para modelo TLA
if ($modelo_negocio === 'TLA') {
    // Validar que solo se permitan BASE DE DATOS, CERRADO GANADO y CERRADO PERDIDO
    if ($etapa !== 'BASE DE DATOS' && $etapa !== 'CERRADO GANADO' && $etapa !== 'CERRADO PERDIDO') {
        $error_message = "Para el modelo TLA solo se permiten las etapas BASE DE DATOS, CERRADO GANADO y CERRADO PERDIDO";
        $error_type = "danger";
        $proyeccion = false;
    }
    
    // Si es CERRADO GANADO, validar todos los campos requeridos
    if ($etapa === 'CERRADO GANADO') {
        $camposRequeridosTLA = [
            'telefono' => 'Teléfono',
            'tipo' => 'Tipo',
            'fechaNacimiento' => 'Fecha de Nacimiento',
            'fechaCierre' => 'Fecha de Cierre',
            'ventaReal' => 'Venta Real',
            'enganche' => 'Enganche',
            'fuenteProspec' => 'Fuente de Prospección',
            'curp' => 'CURP',
            'inputCity' => 'Ciudad',
            'calle' => 'Calle',
            'folio' => 'Folio',
            'producto' => 'Producto',
            'sexo' => 'Sexo',
            'estado' => 'Estado'
        ];
        
        $camposFaltantes = [];
        foreach ($camposRequeridosTLA as $campo => $nombre) {
            $valor = $_POST[$campo] ?? '';
            // Validación más estricta para campos numéricos
            if ($campo === 'telefono') {
                if (empty($valor) || $valor === '0' || $valor === '0000000000' || strlen($valor) < 10) {
                    $camposFaltantes[] = $nombre;
                }
            } elseif ($campo === 'ventaReal' || $campo === 'enganche' || $campo === 'folio') {
                if (empty($valor) || $valor === '0' || !is_numeric($valor) || $valor <= 0) {
                    $camposFaltantes[] = $nombre;
                }
            } else {
                if (empty($valor) || $valor === '0') {
                    $camposFaltantes[] = $nombre;
                }
            }
        }
        
        if (!empty($camposFaltantes)) {
            $error_message = "Para la etapa CERRADO GANADO en TLA, todos los campos son obligatorios. Faltan: " . implode(', ', $camposFaltantes);
            $error_type = "danger";
            $proyeccion = false;
        }
    }
    
    // Validación específica para CERRADO PERDIDO - solo motivo de rechazo
    if ($etapa === 'CERRADO PERDIDO') {
        $motivoRechazo = $_POST['motivoRechazo'] ?? '';
        if (empty($motivoRechazo)) {
            $error_message = "Para la etapa CERRADO PERDIDO, el motivo de pérdida es obligatorio";
            $error_type = "danger";
            $proyeccion = false;
        }
        
        // Validación especial para OTROS en motivo de rechazo
        if ($motivoRechazo === 'OTROS') {
            $notas = $_POST['notas'] ?? '';
            if (empty($notas) || trim($notas) === '') {
                $error_message = "Cuando se selecciona 'OTROS' como motivo de pérdida, el campo de notas es obligatorio";
                $error_type = "danger";
                $proyeccion = false;
            }
        }
    }
}

$tipo = $_POST['tipo'] ?? NULL;
$fechaNacimiento = $_POST['fechaNacimiento'] ?? NULL; 
$email = $_POST['email'] ?? NULL;
$telefono = $_POST['telefono'] ?? NULL;

// Validación del teléfono - debe ser obligatorio y válido
if (empty($telefono) || $telefono === '0' || $telefono === '0000000000' || strlen($telefono) < 10) {
    $error_message = "El teléfono es obligatorio y debe tener al menos 10 dígitos";
    $error_type = "danger";
    $proyeccion = false;
}
$fechaCierre = $_POST['fechaCierre'] ?? NULL;
$curp = $_POST['curp'] ?? NULL;
$producto = $_POST['producto'] ?? NULL; 
$tipoVenta = $_POST['tipoVenta'] ?? NULL;
$notas = $_POST['notas'] ?? NULL; 
$origenCliente = $_POST['fuenteProspec'] ?? NULL;  
$asesor = isset($_POST['asesor']) ? $_POST['asesor'] : $id_asesor;
$calle = isset($_POST['calle']) ? $_POST['calle'] : '';
$ciudad = isset($_POST['inputCity']) ? $_POST['inputCity'] : '';
$plazo = isset($_POST['plazo']) ? $_POST['plazo'] : NULL;
$cp = isset($_POST['inputZip']) ? $_POST['inputZip'] : '';
$enganche = limpiarNumero('enganche'); 
$ventaReal = limpiarNumero('ventaReal'); 
$folio = limpiarNumero('folio'); 
$ventaFrio = isset($_POST['ventaFrio']) ? 1 : 0;
$creadoPor = $correo;
$fechaCreado = date("Y-m-d H:i:s");
$modificadoPor = $correo;
$fechaModificado = date("Y-m-d H:i:s");
$domicilio = isset($_POST['domicilio']) ? $_POST['domicilio'] : '';
$codigoPostal = isset($_POST['inputZip']) ? $_POST['inputZip'] : '';
$folioContrato = isset($_POST['folio']) ? $_POST['folio'] : 0;
$precioOriginal = isset($_POST['precioOriginal']) ? $_POST['precioOriginal'] : 0;
$numero_venta = $num_venta;
$anio = date("Y");
// Ensure descuento is always a valid number (0-70 range)
$descuento = 0; // Default value
if (isset($_POST['descuento']) && $_POST['descuento'] !== '') {
    $descuento = is_numeric($_POST['descuento']) ? (float)$_POST['descuento'] : 0;
    $descuento = max(0, min(70, $descuento)); // Ensure it's within valid range
}
// Ensure descuento is always numeric, never null
$descuento = (float)$descuento;

$lealtad = $_POST['tarjeta_lealtad'] ?? '';
$precioOriginal = isset($_POST['precioOriginal']) ? (float)$_POST['precioOriginal'] : 0;



 // Validar si el folio ya existe solo cuando es una inserción (no hay ID)
 if ($folio > 0 && !$id) {
  $sqlCheckFolio = "SELECT * FROM cliente WHERE folio_contrato = ? AND asesor = ?";
  $stmtCheckFolio = $conn->prepare($sqlCheckFolio);
  $stmtCheckFolio->bind_param("ii", $folio, $id_asesor);
  $stmtCheckFolio->execute();
  $resultCheckFolio = $stmtCheckFolio->get_result();
  
  if ($resultCheckFolio->num_rows > 0) {
      throw new Exception("Este folio de contrato ya existe en el sistema para tu cuenta.");
  }
}

// Nueva condición para establecer la etapa
if ($ventaFrio == 1) {
    $etapa = "CERRADO GANADO";
}
$estado = ($etapa == "CERRADO GANADO") ? "VENTA" : (($etapa == "CERRADO PERDIDO") ? "PERDIDO" : "EMBUDO");
$etapas = [
  "BASE DE DATOS" => 0,
  "ACTIVAR" => 0,
  "ESTRECHAR" => 0.25,
  "EN PRONOSTICO" => 0.7,
  "CERRADO GANADO" => 1
];

$porcentaje = $etapas[$etapa] ?? 0.00;
$porcentaje = (float) $porcentaje;
$sin_iva = (float) $ventaReal / 1.16;

// Verifica que el porcentaje se esté calculando correctamente
if ($porcentaje !== null) {
    $venta_embudo = (float)$sin_iva * $porcentaje;
}

if($etapa == 'CERRADO GANADO'){
$estatus = 'won';
}

// Consulta para obtener las iniciales del asesor
if ($asesor) {
    $sqlAsesor = "SELECT iniciales FROM empleado WHERE id = $asesor";
    $resultadoAsesor = $conn->query($sqlAsesor);
    if ($resultadoAsesor && $resultadoAsesor->num_rows > 0) {
        $rowAsesor = $resultadoAsesor->fetch_assoc();
        $nombre_asesor = $rowAsesor['iniciales'];
    }
}


 if ($id) {

            $sql = "UPDATE cliente SET 
            nombre = " . sqlValue(strtoupper($nombre), false, true) . ", 
            apellido_paterno = " . sqlValue(strtoupper($apellidoPaterno), false, true) . ", 
            apellido_materno = " . sqlValue(strtoupper($apellidoMaterno), false, true) . ", 
            etapa = " . sqlValue(strtoupper($etapa)) . ", 
            tipo = " . sqlValue(strtoupper($tipo)) . ", 
            fecha_nacimiento = " . sqlValue($fechaNacimiento) . ", 
            correo = " . sqlValue(strtoupper($email)) . ", 
            telefono = " . sqlValue($telefono, true, true) . ", 
            fecha_cierre = " . sqlValue($fechaCierre) . ", 
            CURP = " . sqlValue(strtoupper($curp)) . ", 
            articulo = " . sqlValue(strtoupper($producto)) . ", 
            tipo_venta = " . sqlValue(strtoupper($tipoVenta)) . ", 
            fecha_compromiso = " . sqlValue($fechaCompromiso) . ", 
            notas = " . sqlValue(strtoupper($notas)) . ", 
            origen_cliente = " . sqlValue(strtoupper($origenCliente)) . ", 
            asesor = " . sqlValue($asesor, true) . ", 
            domicilio = " . sqlValue(strtoupper($domicilio)) . ", 
            calle = " . sqlValue(strtoupper($calle)) . ", 
            ciudad = " . sqlValue(strtoupper($ciudad)) . ", 
            plazo = " . sqlValue($plazo) . ", 
            codigo_postal = " . sqlValue($codigoPostal) . ", 
            enganche = " . sqlValue($enganche, true) . ", 
            venta_real = " . sqlValue($ventaReal, true) . ", 
            folio_contrato = " . sqlValue($folio, true) . ", 
            venta_frio = " . sqlValue($ventaFrio, true) . ", 
            modificado_por = " . sqlValue(strtoupper($modificadoPor)) . ", 
            fecha_modificado = " . sqlValue($fechaModificado) . ",
            porcentaje = " . sqlValue($porcentaje, true) . ", 
            estado = " . sqlValue(strtoupper($estado)) . ", 
            venta_embudo = " . sqlValue($venta_embudo, true) . ",
            nombre_asesor = " . sqlValue(strtoupper($nombre_asesor)) . ", 
            estatus = " . sqlValue(strtoupper($estatus)) . ",
            numero_venta = " . sqlValue($num_venta, true) . ",
            mes = " . sqlValue(strtoupper($mes)) . ",
            anual = " . sqlValue($anio, true) . ",
            descuento = " . sqlValue($descuento, true) . ",
            tarjeta_lealtad = " . sqlValue(strtoupper($lealtad)) . ",
            sexo = " . sqlValue(strtoupper($_POST['sexo'])) . ",
            estado_nacimiento = " . sqlValue(strtoupper($_POST['estado'])) . ",
            precio_original = " . sqlValue($precioOriginal, true) . ",
            motivo_rechazo = " . sqlValue(strtoupper($_POST['motivoRechazo'])) . "
            WHERE id = $id";

    } else {
        // Insertar nuevo cliente - Versión completa con orden correcto
        $sql = "INSERT INTO cliente (
          nombre, apellido_paterno, apellido_materno, etapa, tipo, 
          fecha_nacimiento, correo, telefono, fecha_cierre, CURP, 
          articulo, tipo_venta, fecha_compromiso, notas, origen_cliente, 
          asesor, domicilio, calle, ciudad, plazo, codigo_postal, 
          enganche, venta_real, folio_contrato, venta_frio, creado_por, 
          modificado_por, fecha_creado, fecha_modificado, porcentaje, 
          estado, venta_embudo, nombre_asesor, estatus, numero_venta, mes,
          anual, descuento, tarjeta_lealtad, sexo, estado_nacimiento, plaza, precio_original, motivo_rechazo
      ) VALUES (
          " . sqlValue(strtoupper($nombre), false, true) . ", " . sqlValue(strtoupper($apellidoPaterno), false, true) . ", " . sqlValue(strtoupper($apellidoMaterno), false, true) . ", " . sqlValue(strtoupper($etapa)) . ", " . sqlValue(strtoupper($tipo)) . ", 
          " . sqlValue($fechaNacimiento) . ", " . sqlValue(strtoupper($email)) . ", " . sqlValue($telefono, true, true) . ", " . sqlValue($fechaCierre) . ", " . sqlValue(strtoupper($curp)) . ", 
          " . sqlValue(strtoupper($producto)) . ", " . sqlValue(strtoupper($tipoVenta)) . ", " . sqlValue($fechaCompromiso) . ", " . sqlValue(strtoupper($notas)) . ", " . sqlValue(strtoupper($origenCliente)) . ", 
          " . sqlValue($asesor, true) . ", " . sqlValue(strtoupper($domicilio)) . ", " . sqlValue(strtoupper($calle)) . ", " . sqlValue(strtoupper($ciudad)) . ", " . sqlValue($plazo) . ", " . sqlValue($codigoPostal) . ", 
          " . sqlValue($enganche, true) . ", " . sqlValue($ventaReal, true) . ", " . sqlValue($folio, true) . ", " . sqlValue($ventaFrio, true) . ", 
          " . sqlValue(strtoupper($creadoPor)) . ", " . sqlValue(strtoupper($modificadoPor)) . ", " . sqlValue($fechaCreado) . ", " . sqlValue($fechaModificado) . ", 
          " . sqlValue($porcentaje, true) . ", " . sqlValue(strtoupper($estado)) . ", " . sqlValue($venta_embudo, true) . ", " . sqlValue(strtoupper($nombre_asesor)) . ", " . sqlValue(strtoupper($estatus)) . ", " . sqlValue($num_venta, true) . ", " . sqlValue(strtoupper($mes)) . ",
          " . sqlValue($anio, true) . ", " . sqlValue($descuento, true) . ", " . sqlValue(strtoupper($lealtad)) . ", " . sqlValue(strtoupper($_POST['sexo'])) . ", " . sqlValue(strtoupper($_POST['estado'])) . ", " . sqlValue(strtoupper($sucursal)) . ", " . sqlValue($precioOriginal, true) . ", " . sqlValue(strtoupper($_POST['motivoRechazo'])) . "
      )";
    }
    
    // Solo ejecutar la consulta si no hay errores de validación
    if (!isset($error_message)) {
        if ($con->query($sql) === TRUE) { // $stmt->execute()
          
          $respuesta = $cliente_id ? "Cliente actualizado exitosamente" : "Nuevo cliente registrado exitosamente";
          $proyeccion = true;
        } else {
            $error_message = "Error en la base de datos: " . $con->error;
            $error_type = "danger";
        }
    }

  } catch (Exception $e) {

    $error_message = "Error: " . htmlspecialchars($e->getMessage());
    $error_type = "danger";
  }
}

// Add a search form for client
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_client'])) {
    $search_nombre = $_POST['search_nombre'] ?? '';
    $search_apellidoPaterno = $_POST['search_apellidoPaterno'] ?? '';
    $search_apellidoMaterno = $_POST['search_apellidoMaterno'] ?? '';

    $sqlSearch = "SELECT * FROM cliente WHERE nombre = '$search_nombre' AND apellido_paterno = '$search_apellidoPaterno' AND apellido_materno = '$search_apellidoMaterno' LIMIT 1";
    $resultSearch = $conn->query($sqlSearch);
    if ($resultSearch && $resultSearch->num_rows > 0) {
        $cliente = $resultSearch->fetch_assoc();
        $fechaNacimiento = $cliente['fecha_nacimiento'];
        $telefono = $cliente['telefono'];
    }
}

// Add a search form for client by RFC
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_rfc'])) {
    $search_rfc = $_POST['search_rfc'] ?? '';

    // Query to find the client by RFC and belonging to the logged-in advisor
    $sqlSearchRFC = "SELECT * FROM cliente WHERE curp = '$search_rfc' AND asesor = '$id_asesor' LIMIT 1";
    $resultSearchRFC = $conn->query($sqlSearchRFC);
    if ($resultSearchRFC && $resultSearchRFC->num_rows > 0) {
        $cliente = $resultSearchRFC->fetch_assoc();
        $num_venta = $cliente['numero_venta'] + 1;
    } else {
        $num_venta = 0;
    }
}

// Calculate num_venta
/*$sqlNumVenta = "SELECT MAX(numero_venta) as max_num_venta FROM cliente";
$resultNumVenta = $conn->query($sqlNumVenta);
if ($resultNumVenta && $resultNumVenta->num_rows > 0) {
    $rowNumVenta = $resultNumVenta->fetch_assoc();
    $num_venta = $rowNumVenta['max_num_venta'] + 1;
} else {
    $num_venta = 1;
}*/

if ($puesto === 'COORDINADOR' ) {
  $clientes = $conn->query("SELECT 
  c.id AS id,
  c.nombre AS nombre,
  c.apellido_paterno,
  c.apellido_materno
FROM 
  cliente c
INNER JOIN 
  empleado e ON c.asesor = e.id
WHERE 
  e.id_supervisor = $id_asesor 
  AND c.etapa = 'CERRADO GANADO' 
  AND c.mes = '$mes'");
//var_dump( $id_asesor );
//die();
$cliente_id = $_GET['cliente_id'] ?? null;
$cliente = null;
if ($cliente_id) {
  $resultado = $conn->query("SELECT * FROM cliente WHERE id = $cliente_id");
  $cliente = $resultado->fetch_assoc();
  
  // Para modelo TLA, mantener la etapa original del cliente
  if ($modelo_negocio === 'TLA' && $cliente) {
    // No modificar la etapa automáticamente, usar la del cliente
  }
}
}

if ($puesto === 'ASESOR' || isset($_GET['cliente_id'])) {
  
  $clientes = $conn->query("SELECT id, nombre, apellido_paterno, apellido_materno FROM cliente WHERE asesor = '$id_asesor' AND etapa != 'CERRADO GANADO' AND etapa != 'CERRADO PERDIDO'");
  $cliente_id = $_GET['cliente_id'] ?? null;
  $cliente = null;
  if ($cliente_id) {
    $resultado = $conn->query("SELECT * FROM cliente WHERE id = $cliente_id");
    $cliente = $resultado->fetch_assoc();
    
    // Para modelo TLA, mantener la etapa original del cliente
    if ($modelo_negocio === 'TLA' && $cliente) {
      // No modificar la etapa automáticamente, usar la del cliente
    }
  }
}
if ($puesto === 'GERENTE' || $puesto === 'EJECUTIVO') {
  $clientes = $conn->query("SELECT 
    c.id AS id,
    c.nombre AS nombre,
    c.apellido_paterno,
    c.apellido_materno
  FROM 
    cliente c
  WHERE 
    c.plaza = '$sucursal' 
    AND c.etapa = 'CERRADO GANADO' 
    AND c.mes = '$mes'");
  
  $cliente_id = $_GET['cliente_id'] ?? null;
  $cliente = null;
  if ($cliente_id) {
    $resultado = $conn->query("SELECT * FROM cliente WHERE id = $cliente_id");
    $cliente = $resultado->fetch_assoc();
    
    // Para modelo TLA, mantener la etapa original del cliente
    if ($modelo_negocio === 'TLA' && $cliente) {
      // No modificar la etapa automáticamente, usar la del cliente
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
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

    <!-- Estilos personalizados - Diseño Plano Moderno -->
    <style>
        /* Variables CSS para el diseño plano */
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --secondary-color: #6b7280;
            
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --bg-input: #ffffff;
            --bg-header: #667eea;
            
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            
            --border-color: #e2e8f0;
            --border-radius: 8px;
            --border-radius-lg: 12px;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            --transition: all 0.2s ease-in-out;
        }

        /* Fondo del body */
        html, body {

            background: var(--bg-body);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

                  .app-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #f8f9fa; /* mismo color que tu navbar o blanco */
            padding: 10px 0;
            text-align: center;
            border-top: 1px solid #ddd;
          }
        /* Cards estilo plano moderno */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-1px);
        }
        
        /* Headers estilo plano */
        .card-header {
            background: var(--bg-header) !important;
            border: none;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0 !important;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.125rem;
            color: white;
            display: flex;
            align-items: center;
        }
        
        .card-header h5 i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }
        
        /* Formularios estilo plano */
        .form-control, .form-select {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            transition: var(--transition);
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--bg-input);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 1;
        }
        
        /* Input Groups estilo plano */
        .input-group-text {
            background: var(--secondary-color);
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            color: white;
            font-weight: 500;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }
        
        /* Labels estilo plano */
        .form-label, .col-form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Botones estilo plano */
        .btn-group-sm .btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            transition: var(--transition);
            color: var(--text-primary);
        }
        
        .btn-group-sm .btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-warning:hover {
            background: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
        }
        
        .btn-outline-success:hover {
            background: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-outline-danger:hover {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-outline-info:hover {
            background: var(--info-color);
            border-color: var(--info-color);
        }
        
        /* Checkbox estilo plano */
        .form-check {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1rem;
            transition: var(--transition);
        }
        
        .form-check:hover {
            background: #f8fafc;
            border-color: var(--primary-color);
        }
        
        .form-check-input:checked {
            background: var(--success-color);
            border-color: var(--success-color);
        }
        
        /* Botón principal estilo plano */
        .btn-success {
            background: var(--success-color);
            border: 1px solid var(--success-color);
            border-radius: var(--border-radius);
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }
        
        .btn-success:hover {
            background: #059669;
            border-color: #059669;
            box-shadow: var(--shadow-lg);
            transform: translateY(-1px);
        }
        
        /* Alertas Glass */
        .alert {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius-sm);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            box-shadow: var(--glass-shadow);
        }
        
        .alert-success {
            background: rgba(17, 153, 142, 0.1);
            border-color: rgba(17, 153, 142, 0.3);
            color: #0f7b6c;
        }
        
        .alert-warning {
            background: rgba(240, 147, 251, 0.1);
            border-color: rgba(240, 147, 251, 0.3);
            color: #8b3a4a;
        }
        
        .alert-danger {
            background: rgba(252, 70, 107, 0.1);
            border-color: rgba(252, 70, 107, 0.3);
            color: #a01e3c;
        }
        
        /* Campos especiales */
        .form-control[readonly] {
            background: rgba(17, 153, 142, 0.1) !important;
            color: #0f7b6c !important;
            font-weight: 700;
            border-color: rgba(17, 153, 142, 0.3);
            cursor: not-allowed;
        }
        
        /* Estados de validación para campos */
        .form-control.is-valid {
            border-color: #38ef7d;
            background-color: #f0fff4;
        }
        
        .form-control.is-warning {
            border-color: #f5576c;
            background-color: #fef2f2;
        }
        
        /* Mejorar contraste de texto */
        .text-muted {
            color: #495057 !important;
        }
        
        .form-label, .col-form-label {
            color: #2d3748 !important;
        }
        
        .bg-success.input-group-text {
            background: var(--success-gradient) !important;
            border-color: rgba(17, 153, 142, 0.3);
        }
        
        .bg-warning.input-group-text {
            background: var(--warning-gradient) !important;
            border-color: rgba(240, 147, 251, 0.3);
        }
        
        /* Animaciones mejoradas */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: var(--glass-shadow); }
            50% { box-shadow: 0 8px 32px 0 rgba(102, 126, 234, 0.4); }
        }
        
        .card {
            animation: float 6s ease-in-out infinite;
        }
        
        .card:nth-child(even) {
            animation-delay: -3s;
        }
        
        /* RESPONSIVE DESIGN MEJORADO */
        
        /* Tablets */
        @media (max-width: 991.98px) {
            .card-header {
                padding: 1rem;
                text-align: center;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .col-form-label {
                margin-bottom: 0.25rem;
                font-size: 0.9rem;
            }
            
            .btn-group-sm {
                display: flex;
                flex-wrap: wrap;
                gap: 0.25rem;
            }
            
            .btn-group-sm .btn {
                flex: 1;
                min-width: 60px;
            }
        }
        
        /* Móviles */
        @media (max-width: 767.98px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            .card {
                margin-bottom: 1rem;
                border-radius: var(--border-radius-sm);
            }
            
            .card-header {
                padding: 0.875rem 1rem;
            }
            
            .card-header h5 {
                font-size: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .row.mb-4 {
                margin-bottom: 1.25rem !important;
            }
            
            .col-sm-4 {
                margin-bottom: 0.5rem;
                text-align: left;
            }
            
            .col-sm-8, .col-sm-9 {
                margin-bottom: 0.75rem;
            }
            
            .form-control, .form-select {
                padding: 0.625rem 0.875rem;
                font-size: 16px; /* Evita zoom en iOS */
            }
            
            .input-group-text {
                padding: 0.625rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .btn-success {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
                width: 100%;
                margin-top: 1rem;
            }
            
            .btn-group-sm {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-group-sm .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
                min-width: 50px;
            }
            
            .form-check {
                padding: 0.875rem 1rem;
                margin: 0.5rem 0;
            }
            
            .alert {
                font-size: 0.9rem;
                margin-top: 1rem;
            }
        }
        
        /* Móviles pequeños */
        @media (max-width: 575.98px) {
            .card-body {
                padding: 0.875rem;
            }
            
            .row.mb-4 {
                margin-bottom: 1rem !important;
            }
            
            .col-form-label {
                font-size: 0.85rem;
                font-weight: 600;
            }
            
            .btn-group-sm .btn {
                font-size: 0.7rem;
                padding: 0.375rem 0.5rem;
            }
            
            .text-muted {
                font-size: 0.75rem;
            }
        }
        
        /* Mejoras para pantallas táctiles */
        @media (hover: none) and (pointer: coarse) {
            .card:hover {
                transform: none;
            }
            
            .btn-success:hover {
                transform: none;
            }
            
            .form-control:focus, .form-select:focus {
                transform: none;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .form-control, .form-select {
                background-color: #1a1d23;
                border-color: #374151;
                color: #e5e7eb;
            }
            
            .input-group-text {
                background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
                color: #e5e7eb;
                border-color: #374151;
            }
        }

        /* Nueva interfaz de captura - Diseño Compacto Justificado */
        .wizard-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: auto;
            padding: 1rem 0 0.5rem 0;
        }
        
        .wizard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            border: 1px solid #e9ecef;
            overflow: hidden;
            margin: 1.5rem auto;
            max-width: 1200px;
            width: 98%;
        }
        
        .wizard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .wizard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .wizard-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .wizard-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
            position: relative;
            z-index: 1;
        }
        
        .wizard-body {
            padding: 2rem;
        }
        
        /* Diseño de Grid Compacto */
        .form-grid {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        .form-section-large {
            grid-column: 1/3;
        }
        
        .form-section-combined {
            grid-column: 2;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .sub-section {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            color: #495057;
            font-family: Arial, sans-serif;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }
        
        .section-title i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        .sub-section-title {
            color: #495057;
            font-family: Arial, sans-serif;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .sub-section-title i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-family: Arial, sans-serif;
            font-weight: bold;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .section-title i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }
        
        .modern-input {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            height: auto;
            min-height: 2.5rem;
        }
        
        .modern-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }
        
        .modern-label {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-group-modern {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 3;
        }
        
        .input-with-icon {
            padding-left: 3rem;
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
            height: 3rem;
            display: flex;
            align-items: center;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
            outline: none;
            background-color: #f8f9ff;
        }
        
        .form-select {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 0.95rem;
            height: 3rem;
            display: flex;
            align-items: center;
        }
        
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
            background-color: #f8f9ff;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #6c757d;
            font-family: Arial, sans-serif;
            font-weight: bold;
            height: 3rem;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: white;
            font-size: 0.9rem;
        }
        
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        /* Estilos para mejorar visualización de datos cargados */
        .form-control:not(:placeholder-shown) {
            background-color: #f0f8ff;
            border-color: #667eea;
            color: #2c3e50;
        }
        
        .form-select:not([value=""]) {
            background-color: #f0f8ff;
            border-color: #667eea;
            color: #2c3e50;
        }
        
        .form-control[readonly] {
            background-color: #e8f4fd;
            border-color: #17a2b8;
            color: #0c5460;
            font-weight: bold;
        }
        
        .data-loaded {
            animation: highlightData 2s ease-in-out;
        }
        
        @keyframes highlightData {
            0% { background-color: #fff3cd; border-color: #ffc107; }
            50% { background-color: #d4edda; border-color: #28a745; }
            100% { background-color: #f0f8ff; border-color: #667eea; }
        }
        
        .success-message {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-family: Arial, sans-serif;
            font-weight: bold;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .btn-modern:focus {
            box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.5);
            color: white;
        }
        
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            grid-column: 1 / -1;
        }
        
        .search-title {
            font-family: Arial, sans-serif;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .search-input {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            color: white;
            font-size: 1rem;
            min-height: 3rem;
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-input:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .alert-modern {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success-modern {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-warning-modern {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }
        
        /* Headers uniformes con iconos consistentes */
        .card-header .d-flex {
            width: 100%;
        }
        
        .card-header i {
            font-size: 1.5rem;
            opacity: 0.9;
        }
        
        .card-header h5 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .card-header small {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }
        
        .form-control-lg {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            color: #495057;
            background-color: #ffffff;
            height: 3rem;
            font-size: 1rem;
        }
        
        .form-control-lg:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            color: #495057;
            background-color: #ffffff;
        }
        
        .form-control-lg::placeholder {
            color: #adb5bd;
            font-style: italic;
        }
        
        .form-select {
            height: 3rem;
            font-size: 1rem;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            color: #495057;
            background-color: #ffffff;
        }
        
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            border: 2px solid #e9ecef;
            border-right: none;
            background: #f8f9fa;
            color: #6c757d;
            height: 3rem;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
        }
        
        .form-control-lg + .input-group-text {
            border-left: none;
        }
        
        .card {
            border-radius: 1rem;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .card-header {
            border-bottom: none;
            padding: 1.5rem;
            min-height: 80px;
            display: flex;
            align-items: center;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
        
        .btn-lg {
            border-radius: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-label {
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .invalid-feedback {
            font-size: 0.875rem;
            font-weight: 500;
            color: #dc3545;
            display: none;
            margin-top: 0.25rem;
            padding: 0.5rem;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 0.375rem;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
        
        .is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .valid-feedback {
            font-size: 0.875rem;
            font-weight: 500;
            color: #28a745;
        }
        
        .alert {
            border-radius: 0.75rem;
            border: none;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }
        
        /* Mejoras adicionales para evitar colores negros */
        .text-muted {
            color: #6c757d !important;
        }
        
        .card-title {
            color: inherit;
        }
        
        .form-select {
            color: #495057;
            background-color: #ffffff;
        }
        
        .form-select:focus {
            color: #495057;
            background-color: #ffffff;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.5);
            color: white;
        }
        
        /* Eliminar todos los colores negros y mejorar responsividad */
        body {
            color: #495057;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #495057;
        }
        
        .text-dark {
            color: #495057 !important;
        }
        
        .text-black {
            color: #495057 !important;
        }
        
        /* Espaciado uniforme para todos los cuadros */
        .card.mb-4 {
            margin-bottom: 2rem !important;
        }
        
        .row.g-4 {
            --bs-gutter-x: 2rem;
            --bs-gutter-y: 2rem;
        }
        
        /* Optimización para pantallas grandes (laptop/desktop) */
        @media (min-width: 1200px) {
            .wizard-container {
                padding: 1.5rem 0 0.5rem 0;
            }
            
            .wizard-card {
                max-width: 1400px;
                margin: 2rem auto;
            }
            
            .wizard-header {
                padding: 2rem 3rem;
            }
            
            .wizard-title {
                font-size: 2rem;
            }
            
            .wizard-subtitle {
                font-size: 1.1rem;
            }
            
            .wizard-body {
                padding: 2.5rem;
            }
            
            .form-grid {
                grid-template-columns: 3fr 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .search-section {
                padding: 2rem;
            }
            
            .search-title {
                font-size: 1.3rem;
            }
        }
        
        /* Optimización para pantallas medianas (tablets grandes y laptops pequeñas) */
        @media (min-width: 992px) and (max-width: 1199px) {
            .wizard-container {
                padding: 1rem 0 0.5rem 0;
            }
            
            .wizard-card {
                max-width: 1200px;
                margin: 1.5rem auto;
            }
            
            .wizard-header {
                padding: 1.75rem 2.5rem;
            }
            
            .wizard-title {
                font-size: 1.8rem;
            }
            
            .wizard-subtitle {
                font-size: 1.05rem;
            }
            
            .wizard-body {
                padding: 2.25rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.75rem;
            }
            
            .form-section-combined {
                grid-column: 1;
                flex-direction: row;
                gap: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .search-section {
                padding: 1.75rem;
            }
            
            .search-title {
                font-size: 1.25rem;
            }
        }
        
        /* Mejoras de responsividad para wizard */
        @media (max-width: 768px) {
            .wizard-container {
                padding: 0.5rem 0 0.25rem 0;
            }
            
            .wizard-card {
                margin: 0.5rem;
                border-radius: 12px;
                width: 98%;
            }
            
            .wizard-header {
                padding: 1.25rem 1.5rem;
            }
            
            .wizard-title {
                font-size: 1.5rem;
            }
            
            .wizard-subtitle {
                font-size: 0.95rem;
            }
            
            .wizard-body {
                padding: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            
            .form-section-combined {
                grid-column: 1;
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .search-section {
                padding: 1.25rem;
            }
            
            .search-title {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .wizard-container {
                padding: 0.25rem 0 0.125rem 0;
            }
            
            .wizard-card {
                margin: 0.25rem;
                border-radius: 10px;
                width: 99%;
            }
            
            .wizard-header {
                padding: 1rem 1.25rem;
            }
            
            .wizard-title {
                font-size: 1.3rem;
            }
            
            .wizard-subtitle {
                font-size: 0.85rem;
            }
            
            .wizard-body {
                padding: 1.25rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .form-section-combined {
                grid-column: 1;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .search-section {
                padding: 1rem;
            }
            
            .search-title {
                font-size: 1rem;
            }
            
            .form-control {
                height: 2.75rem;
                font-size: 0.9rem;
            }
            
            .form-select {
                height: 2.75rem;
            }
            
            .input-group-text {
                height: 2.75rem;
            }
            
            .btn-modern {
                padding: 0.75rem 1.25rem;
                font-size: 0.85rem;
            }
        }
    </style>

     <script>
        // Función para resaltar campos con datos cargados
        function highlightLoadedData() {
            const inputs = document.querySelectorAll('.form-control, .form-select');
            inputs.forEach(input => {
                if (input.value && input.value.trim() !== '') {
                    input.classList.add('data-loaded');
                    setTimeout(() => {
                        input.classList.remove('data-loaded');
                    }, 2000);
                }
            });
            
            // Para modelo TLA, ejecutar validación cuando se selecciona un cliente existente
            <?php if ($modelo_negocio === 'TLA'): ?>
            if (typeof validarCampos === 'function') {
                setTimeout(validarCampos, 100);
            }
            <?php endif; ?>
        }
        
        // Función para mostrar mensaje de éxito
        function showSuccessMessage(message) {
            const existingMessage = document.querySelector('.success-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<i class="icon-check-circle me-2"></i>${message}`;
            
            const wizardBody = document.querySelector('.wizard-body');
            wizardBody.insertBefore(successDiv, wizardBody.firstChild);
            
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        }
        
        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Resaltar datos cargados
            setTimeout(highlightLoadedData, 500);
            
            // Verificar si hay mensaje de éxito en PHP
            <?php if (!empty($respuesta)): ?>
                showSuccessMessage('<?php echo addslashes($respuesta); ?>');
            <?php endif; ?>
        });
        
        function generarCURP() {
            let nombre = document.getElementById('nombre').value.toUpperCase();
            let apellidoPaterno = document.getElementById('apellidoPaterno').value.toUpperCase();
            let apellidoMaterno = document.getElementById('apellidoMaterno').value.toUpperCase();
            let fechaNacimiento = document.getElementById('fechaNacimiento').value;

            let curp = '';
            
            // Lógica para formar el CURP
            curp += apellidoPaterno.charAt(0);
            curp += primeraVocal(apellidoPaterno);
            curp += apellidoMaterno.charAt(0) || 'X';
            curp += nombre.charAt(0);
            curp += fechaNacimiento.substr(2, 2);
            curp += fechaNacimiento.substr(5, 2);
            curp += fechaNacimiento.substr(8, 2);
            curp += 'H'; // Asumiendo masculino; puedes cambiar según el género ingresado
            curp += 'XX'; // Lugar de nacimiento, por defecto "XX"
            curp += siguienteConsonante(apellidoPaterno);
            curp += siguienteConsonante(apellidoMaterno);
            curp += siguienteConsonante(nombre);
            curp += '00'; // Homoclave y dígito verificador, por defecto

            document.getElementById('curp').value = curp;
        }

        function primeraVocal(str) {
            for (let i = 1; i < str.length; i++) {
                if ('AEIOU'.includes(str[i])) {
                    return str[i];
                }
            }
            return 'X';
        }

        function siguienteConsonante(str) {
            for (let i = 1; i < str.length; i++) {
                if (!'AEIOU'.includes(str[i]) && /[A-Z]/.test(str[i])) {
                    return str[i];
                }
            }
            return 'X';
        }

        // Asegurar que el descuento sea numérico al enviar el formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formClientes');
            if (form) {
                form.addEventListener('submit', function(event) {
                    const descuento = document.getElementById('descuento')?.value;
                
                    // Ensure descuento is always numeric
                    if (descuento !== null && descuento !== undefined) {
                        const descuentoNum = parseFloat(descuento) || 0;
                        document.getElementById('descuento').value = descuentoNum;
                    }
                });
            }
        });
    </script>
    

  </head>

  <body>
    <!-- Page wrapper start -->
    <div class="page-wrapper">

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

                <li class="nav-item dropdown active-link">
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
                <li class="nav-item ">
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
                      <a class="dropdown-item" href="reporte_diario.php">
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
        <div class="app-body wizard-container">

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
                  <li class="breadcrumb-item">Cliente</li>
                </ol>
                <!-- Breadcrumb end -->
              </div>
            </div>
            <!-- Row end -->

            <!-- Wizard Container -->
            <div class="wizard-card">
              <div class="wizard-header">
                <h1 class="wizard-title">📝 Captura de Cliente</h1>
                <p class="wizard-subtitle">Informcion del cliente</p>
                <label id="numventa" name = "numventa" class="d-none"> </label> 
                <?php
                  if (!empty($respuesta)) {
                    echo '<div class="alert-modern alert-success-modern mt-3" role="alert">
                            <i class="icon-check-circle me-2"></i>' . htmlspecialchars($respuesta) . '
                          </div>';
                  }
                  if (!empty($Validacion)) {
                    echo '<div class="alert-modern alert-warning-modern mt-3" role="alert">
                            <i class="icon-exclamation-triangle me-2"></i>' . htmlspecialchars($Validacion) . '
                          </div>';
                  }
                ?>
              </div>
              
              <div class="wizard-body">
                <!-- Grid Principal -->
                <div class="form-grid">
                  
                  <!-- Sección de Búsqueda -->
                  <div class="search-section">
                    <h3 class="search-title">🔍 Buscar Cliente Existente</h3>
                    <form method="GET" action="" class="row g-3">
                      <div class="col-lg-8 col-md-12">
                        <div class="input-group">
                          <span class="input-group-text">
                            <i class="icon-search"></i>
                          </span>
                          <select class="form-control" name="cliente_id" id="cliente_id" onchange="this.form.submit(); highlightLoadedData();">
                            <option value="">Seleccionar cliente existente...</option>
                            <?php 
                            if (isset($clientes) && $clientes->num_rows > 0) {
                              while ($row = $clientes->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php if (isset($cliente['id']) && $cliente['id'] == $row['id']) echo 'selected'; ?>>
                                  <?php echo $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']; ?>
                                </option>
                              <?php endwhile;
                            } else {
                                  echo '<option value="">No hay clientes disponibles</option>';
                                }
                                ?>
                          </select>
                        </div>
                        <small class="text-white-50">Selecciona un cliente existente para editar sus datos</small>
                      </div>
                      
                    </form>
                  </div>
                    <!-- Row start -->
                   
                  <!-- Sección de Datos Personales (Agrandada) -->
                  <div class="form-section form-section-large">
                    <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 2rem;">
                      <i class="icon-user"></i>Datos Personales del Cliente
                    </h2>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-<?php echo $error_type ?? 'danger'; ?> alert-dismissible fade show" role="alert" style="margin-bottom: 2rem;">
                      <i class="icon-warning me-2"></i>
                      <?php echo htmlspecialchars($error_message); ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($respuesta)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 2rem;">
                      <i class="icon-check me-2"></i>
                      <?php echo htmlspecialchars($respuesta); ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form id="formClientes" action="clients.php" method="POST" class="needs-validation" onsubmit="return validarCampos()" novalidate>
                      <input type="hidden" name="id" value="<?php echo $cliente['id'] ?? ''; ?>">
                      
                      <div class="form-row">
                        <div class="form-group">
                          <label for="nombre">Nombres *</label>
                          <input type="text" class="form-control" id="nombre" name="nombre" 
                                 value="<?php echo $cliente['nombre'] ?? ''; ?>" 
                                 placeholder="Ingresa los nombres" 
                                 style="text-transform:uppercase;" 
                                 required pattern=".*\S.*" />
                          <div class="invalid-feedback">
                            Por favor ingresa el nombre del cliente.
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label for="apellidoPaterno">Apellido Paterno *</label>
                          <input type="text" class="form-control" id="apellidoPaterno" name="apellidoPaterno" 
                                 value="<?php echo $cliente['apellido_paterno'] ?? ''; ?>" 
                                 placeholder="Apellido paterno" 
                                 style="text-transform:uppercase;" 
                                 required />
                          <div class="invalid-feedback">
                            Por favor ingresa el apellido paterno.
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label for="apellidoMaterno">Apellido Materno *</label>
                          <input type="text" class="form-control" id="apellidoMaterno" name="apellidoMaterno" 
                                 placeholder="Apellido materno" 
                                 value="<?php echo $cliente['apellido_materno'] ?? ''; ?>" 
                                 style="text-transform:uppercase;" 
                                 required />
                          <div class="invalid-feedback">
                            Por favor ingresa el apellido materno.
                          </div>
                        </div>
                      </div>
                      
                      <!-- Campos adicionales para agrandar la sección -->
                      <div class="form-row">
                        <div class="form-group">
                          <label for="fechaNacimiento">Fecha de Nacimiento *</label>
                          <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" 
                                 value="<?php echo isset($cliente['fecha_nacimiento']) ? date('Y-m-d', strtotime($cliente['fecha_nacimiento'])) : ''; ?>" 
                                 onChange="generarCURP()" required />
                          <div class="invalid-feedback">
                            Por favor selecciona la fecha de nacimiento.
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label for="email">Correo Electrónico</label>
                          <input type="email" class="form-control" id="email" name="email" 
                                 value="<?php echo $cliente['correo'] ?? ''; ?>" 
                                 placeholder="correo@ejemplo.com" />
                        </div>
                        
                        <div class="form-group">
                          <label for="telefono">Teléfono *</label>
                          <input type="tel" class="form-control" id="telefono" name="telefono" 
                                 value="<?php echo $cliente['telefono'] ?? ''; ?>" 
                                 placeholder="1234567890" 
                                 pattern="\d{10}" maxlength="10" required />
                          <div class="invalid-feedback">
                            Por favor ingresa un número de teléfono válido (10 dígitos).
                          </div>
                        </div>
                      </div>
                      <div class="col-12" id="sexoContainer" style="display: <?php echo ($modelo_negocio === 'TLA') ? 'block' : 'none'; ?>;">
                        <label class="form-label" for="sexo">Sexo</label>
                        <select class="form-select" id="sexo" name="sexo">
                          <option value="<?php echo $cliente['sexo'] ?? ''; ?>">
                            <?php echo $cliente['sexo'] ?? 'Seleccione sexo'; ?>
                          </option>
                          <option value="MUJER">MUJER</option>
                          <option value="HOMBRE">HOMBRE</option>
                          <option value="NO BINARIO">NO BINARIO</option>
                        </select>
                      </div>
                      <div class="col-12" id="estadoContainer" style="display: <?php echo ($modelo_negocio === 'TLA') ? 'block' : 'none'; ?>;">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                          <option value="">Seleccione estado</option>
                          <?php
                          $sqlEstados = "SELECT nombre FROM estados ORDER BY nombre";
                          $resultadoEstados = $conn->query($sqlEstados);
                          while ($rowEstado = $resultadoEstados->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($rowEstado['nombre']); ?>" 
                              <?php echo (isset($cliente['estado_nacimiento']) && strtoupper($cliente['estado_nacimiento']) === strtoupper($rowEstado['nombre'])) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($rowEstado['nombre']); ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="form-row">
                        <div class="form-group">
                          <label for="curp">RFC/CURP</label>
                          <input type="text" class="form-control" id="curp" name="curp" 
                                 value="<?php echo $cliente['CURP'] ?? ''; ?>" 
                                 placeholder="Se genera automáticamente" 
                                 readonly />
                          <small class="form-text text-muted">Se genera automáticamente con la fecha de nacimiento</small>
                        </div>
                        
                        <?php if ($modelo_negocio !== 'TLA'): ?>
                        <div class="form-group">
                          <label for="fechaCompromiso">Fecha Seguimiento *</label>
                          <input type="date" class="form-control" id="fechaCompromiso" name="fechaCompromiso" 
                                 value="<?php echo isset($cliente['fecha_compromiso']) ? date('Y-m-d', strtotime($cliente['fecha_compromiso'])) : ''; ?>" />
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                          <label for="fechaCierre">Fecha Prevista de Cierre</label>
                          <input type="date" class="form-control" id="fechaCierre" name="fechaCierre" 
                                 value="<?php echo isset($cliente['fecha_cierre']) ? date('Y-m-d', strtotime($cliente['fecha_cierre'])) : ''; ?>" />
                        </div>
                        
                        
                      </div>
                      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 2rem;">
                      <i class="fs-3 icon-loop"></i>Datos Seguimiento
                    </h2>
                      <div class="col-12">
                      <label for="etapa" class="form-label"> Etapa: </label>
                        <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                        <select class="form-select" id="etapa" name="etapa">
                          <?php if ($modelo_negocio === 'TLA'): ?>
                            <option value="BASE DE DATOS" <?php echo (empty($cliente['etapa']) || $cliente['etapa'] === 'BASE DE DATOS') ? 'selected' : ''; ?>>BASE DE DATOS</option>
                            <option value="CERRADO GANADO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO GANADO') ? 'selected' : ''; ?>>CERRADO GANADO</option>
                            <option value="CERRADO PERDIDO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO PERDIDO') ? 'selected' : ''; ?>>CERRADO PERDIDO</option>
                          <?php elseif ($puesto === 'COORDINADOR' || $puesto === 'EJECUTIVO'): ?>
                            <option value="CERRADO GANADO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO GANADO') ? 'selected' : ''; ?>>CERRADO GANADO</option>
                            <option value="CERRADO PERDIDO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO PERDIDO') ? 'selected' : ''; ?>>CERRADO PERDIDO</option>
                          <?php else: ?>
                            <option value="BASE DE DATOS" <?php echo (empty($cliente['etapa'])) ? 'selected' : ''; ?>>BASE DE DATOS</option>
                            <option value="ACTIVAR" <?php echo (isset($cliente) && $cliente['etapa'] === 'ACTIVAR') ? 'selected' : ''; ?>>ACTIVAR</option>
                            <option value="ESTRECHAR" <?php echo (isset($cliente) && $cliente['etapa'] === 'ESTRECHAR') ? 'selected' : ''; ?>>ESTRECHAR</option>
                            <option value="EN PRONOSTICO" <?php echo (isset($cliente) && $cliente['etapa'] === 'EN PRONOSTICO') ? 'selected' : ''; ?>>EN PRONOSTICO</option>
                            <option value="CERRADO GANADO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO GANADO') ? 'selected' : ''; ?>>CERRADO GANADO</option>
                            <option value="CERRADO PERDIDO" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO PERDIDO') ? 'selected' : ''; ?>>CERRADO PERDIDO</option>
                          <?php endif; ?>
                        </select>
                      </div>
                      <div class="col-12" id="motivoPerdidaContainer" style="display: <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO PERDIDO') ? 'block' : 'none'; ?>;">
                      <label for="motivoRechazo" class="form-label"> Motivo de Pérdida: </label>
                        <select class="form-select" id="motivoRechazo" name="motivoRechazo" <?php echo (isset($cliente) && $cliente['etapa'] === 'CERRADO PERDIDO') ? '' : 'disabled'; ?>>
                          <option value="">Seleccione motivo de pérdida</option>
                          <option value="PRODUCTO CON COSTO ALTO" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'PRODUCTO CON COSTO ALTO') ? 'selected' : ''; ?>>PRODUCTO CON COSTO ALTO</option>
                          <option value="SE FUE CON LA COMPETENCIA" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'SE FUE CON LA COMPETENCIA') ? 'selected' : ''; ?>>SE FUE CON LA COMPETENCIA</option>
                          <option value="MEDIOS DE PAGO" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'MEDIOS DE PAGO') ? 'selected' : ''; ?>>MEDIOS DE PAGO</option>
                          <option value="NO LE INTERESA" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'NO LE INTERESA') ? 'selected' : ''; ?>>NO LE INTERESA</option>
                          <option value="NO LE APARECE ATRACTIVO EL CONTENIDO" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'NO LE APARECE ATRACTIVO EL CONTENIDO') ? 'selected' : ''; ?>>NO LE APARECE ATRACTIVO EL CONTENIDO</option>
                          <option value="OTROS" <?php echo (isset($cliente) && $cliente['motivo_rechazo'] === 'OTROS') ? 'selected' : ''; ?>>OTROS</option>
                        </select>
                      </div>
                      <div class="col-12">
                      <label for="tipo" class="form-label"> Tipo: </label>
                        <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                        <select class="form-select" id="tipo" name="tipo">
                          <option value="<?php echo $cliente['tipo'] ?? ''; ?>">
                          <?php echo $cliente['tipo'] ?? 'Tipo'; ?>
                          </option>
                          <option value="SERVICIO">SERVICIO</option>
                          <option value="NICHO">NICHO</option>
                          <option value="FOSA">FOSA</option>
                          <option value="CRIPTA">CRIPTA</option>
                          <option value="MONUMENTO">MONUMENTO</option>
                          <option value="BOBEDA MEMORIAL">BOBEDA MEMORIAL</option>
                        </select>
                      </div>
                      <div class="mb-3">
                            <label for="producto" class="form-label"> Articulo/Servicio Venta</label>
                            <select id="producto" name="producto" class="form-select">
                              <option value="">Seleccionar servicio</option>
                              <?php
                              // Obtener servicios únicos de la tabla precio_servicio
                              $sqlServicios = "SELECT DISTINCT nombre FROM precio_servicio where plaza = '$sucursal'";
                              $resultadoServicios = $conn->query($sqlServicios);
                              while ($rowServicio = $resultadoServicios->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($rowServicio['nombre']); ?>" 
                                  <?php echo (isset($cliente['articulo']) && $cliente['articulo'] === $rowServicio['nombre']) ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($rowServicio['nombre']); ?>
                                </option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Tipo de venta</label>
                            <select id="tipoVenta" name="tipoVenta" class="form-select" disabled>
                              <option value="SFF">SFF</option>
                              <option value="SFI">SFI</option>
                            </select>
                          </div>
                      
                    
                    <!-- Row end -->
                  </div>
                </div>
              </div>
            </div>
            <!-- Row end -->
            <!-- Row end -->

                  <!-- Sección Unificada: Prospección e Información de Venta -->
                  <div class="form-section form-section-combined">
                    
                    <!-- Sub-sección: Prospección -->
                    <div class="sub-section">
                      <h4 class="sub-section-title">
                        <i class="icon-target"></i>Prospección
                      </h4>
                      
                      <div class="form-row">
                        <div class="form-group">
                          <label for="fuenteProspec">Origen de Clientes Potenciales</label>
                          <select id="fuenteProspec" name="fuenteProspec" class="form-select">
                            <option value="<?php echo $cliente['origen_cliente'] ?? ''; ?>">
                              <?php echo $cliente['origen_cliente'] ?? 'Seleccionar origen...'; ?>
                            </option>
                            <option value="ANUNCIO">📢 ANUNCIO</option>
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
                        
                        <div class="form-group">
                          <label for="asesor">Asesor *</label>
                          <select id="asesor" name="asesor" class="form-select" style="text-transform:uppercase;" <?php echo ($loggedInUserPuesto === 'ASESOR') ? 'disabled' : ''; ?>>
                            <?php foreach ($empleados as $empleado): ?>
                              <option value="<?php echo isset($empleado['id']) ? htmlspecialchars($empleado['id']) : ''; ?>" 
                                <?php 
                                  if ($puesto === 'COORDINADOR' || $puesto === 'EJECUTIVO') {
                                      echo (isset($cliente['asesor']) && $cliente['asesor'] == $empleado['id']) ? 'selected' : '';
                                  } else {
                                      echo ($empleado['correo'] === $loggedInUserIniciales) ? 'selected' : '';
                                  }
                                ?>>
                                <?php echo htmlspecialchars($empleado['correo']); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      
                      <div class="form-row">
                        <div class="form-group">
                          <label for="domicilio">Domicilio</label>
                          <input type="text" class="form-control" id="domicilio" name="domicilio" maxlength="100" placeholder="Colonia" style="text-transform:uppercase;" value="<?php echo $cliente['domicilio'] ?? ''; ?>" />
                        </div>
                        
                        <div class="form-group">
                          <label for="calle">Calle</label>
                          <input type="text" class="form-control" id="calle" name="calle" maxlength="80" placeholder="Calle / Piso / #exterior" style="text-transform:uppercase;" value="<?php echo $cliente['calle'] ?? ''; ?>" />
                        </div>
                      </div>
                      
                      <div class="form-row">
                        <div class="form-group">
                          <label for="inputCity">Ciudad</label>
                          <input type="text" class="form-control" id="inputCity" name="inputCity" maxlength="80" style="text-transform:uppercase;" value="<?php echo $cliente['ciudad'] ?? ''; ?>" />
                        </div>
                        
                        <div class="form-group">
                          <label for="inputZip">Código Postal</label>
                          <input type="number" class="form-control" id="inputZip" name="inputZip" maxlength="5" value="<?php echo $cliente['codigo_postal'] ?? ''; ?>" />
                        </div>
                      </div>
                      
                      <div class="form-row">
                        <div class="form-group">
                          <label for="plazo">Plazo Sugerido</label>
                          <select id="plazo" name="plazo" class="form-select">
                            <option value="1" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 1) ? 'selected' : ''; ?>>1 mes</option>
                            <option value="3" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 3) ? 'selected' : ''; ?>>3 meses</option>
                            <option value="4" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 4) ? 'selected' : ''; ?>>4 meses</option>
                            <option value="6" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 6) ? 'selected' : ''; ?>>6 meses</option>
                            <option value="9" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 9) ? 'selected' : ''; ?>>9 meses</option>
                            <option value="12" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 12) ? 'selected' : ''; ?>>12 meses</option>
                            <option value="24" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 24) ? 'selected' : ''; ?>>24 meses</option>
                            <option value="36" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 36) ? 'selected' : ''; ?>>36 meses</option>
                            <option value="42" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 42) ? 'selected' : ''; ?>>42 meses</option>
                            <option value="48" <?php echo (isset($cliente['plazo']) && $cliente['plazo'] == 48) ? 'selected' : ''; ?>>48 meses</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Sub-sección: Información de Venta -->
                    <div class="sub-section">
                      <h4 class="sub-section-title">
                        <i class="icon-shopping-cart"></i>Información de Venta
                      </h4>
                      
                      <form action="clients.php" method="POST" class="needs-validation" novalidate>
                        <div class="form-row">
                          <div class="form-group">
                            <label for="enganche">Enganche</label>
                            <div class="input-group">
                              <span class="input-group-text">$</span>
                              <input type="number" step="0.01" class="form-control" id="enganche" name="enganche" value="<?php echo $cliente['enganche'] ?? ''; ?>" placeholder="0.00" />
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label for="precioOriginal">Precio Original</label>
                            <div class="input-group">
                              <span class="input-group-text">$</span>
                              <input type="number" step="0.01" class="form-control" id="precioOriginal" name="precioOriginal" readonly placeholder="0.00" style="background-color: #f8f9fa;" />
                            </div>
                            <small class="text-muted">Precio base sin descuentos</small>
                          </div>
                        </div>
                        
                        <div class="form-row">
                          <div class="form-group">
                            <label for="descuento">Descuento (%)</label>
                            <div class="input-group">
                              <input type="number" min="0" max="70" step="0.1" class="form-control" id="descuento" name="descuento" value="<?php echo $cliente['descuento'] ?? '0'; ?>" placeholder="0.0" />
                              <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Rango: 0% - 70%</small>
                          </div>
                          
                          <div class="form-group">
                            <label for="ventaReal">Venta Real</label>
                            <div class="input-group">
                              <span class="input-group-text">$</span>
                              <input type="number" step="0.01" class="form-control" id="ventaReal" name="ventaReal" value="<?php echo $cliente['venta_real'] ?? ''; ?>" />
                            </div>
                            <small class="text-success">Precio final con descuento</small>
                          </div>
                        </div>
                        
                        <div class="form-row">
                          <div class="form-group">
                            <label for="folio">Folio Contrato</label>
                            <input type="number" class="form-control" id="folio" name="folio" maxlength="10" value="<?php echo $cliente['folio_contrato'] ?? ''; ?>" placeholder="Número de folio" />
                          </div>
                          
                          <div class="form-group">
                            <label for="lealtad">Plan Lealtad</label>
                            <input type="text" class="form-control" id="lealtad" name="lealtad" maxlength="5" oninput="validateInput(this)" placeholder="Código de lealtad" value="<?php echo $cliente['tarjeta_lealtad'] ?? ''; ?>" style="text-transform:uppercase;" />
                            <small class="text-muted">Máximo 5 caracteres</small>
                          </div>
                          <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" placeholder="Opcional" rows="3"><?php echo $cliente['notas'] ?? ''; ?></textarea>
                          </div>
                        </div>
                        <?php if ($modelo_negocio !== 'TLA'): ?>
                        <div class="form-group">
                          <div class="venta-frio-container">
                            <div class="venta-frio-card">
                              <div class="venta-frio-header">
                                <div class="venta-frio-icon">
                                  <i class="icon-thermometer"></i>
                                </div>
                                <div class="venta-frio-title">
                                  <h6 class="mb-0">Venta en Frío</h6>
                                  <small class="text-muted">Venta sin calentamiento previo</small>
                                </div>
                              </div>
                              <div class="venta-frio-toggle">
                                <input class="venta-frio-checkbox" type="checkbox" id="ventaFrio" name="ventaFrio" value="1" <?php echo (isset($cliente['venta_frio']) && $cliente['venta_frio'] == 1) ? 'checked' : ''; ?> />
                                <label class="venta-frio-switch" for="ventaFrio">
                                  <span class="venta-frio-slider"></span>
                                </label>
                              </div>
                            </div>
                            <div class="venta-frio-description">
                              <i class="icon-info-circle me-1"></i>
                              <span>Activar cuando la venta sea directa</span>
                            </div>
                          </div>
                        </div>
                        <?php endif; ?>
                        <!-- Botón de Guardar -->
                        <div class="d-grid mt-3">
                          <button type="submit" id="btnGuardar" class="btn-modern" >
                            <i class="icon-save me-2"></i>Guardar Cliente
                          </button>
                        </div>
                        
                        <!-- Botones de prueba para validación -->
                       <!-- <div class="d-grid gap-2 mt-2">
                          <button type="button" class="btn btn-warning" onclick="probarValidacion()">
                            <i class="icon-check me-2"></i>Probar Validación
                          </button>
                          <button type="button" class="btn btn-info" onclick="validarCamposTiempoReal()">
                            <i class="icon-refresh me-2"></i>Revalidar Campos
                          </button>
                        </div> -->
                        
                        <!-- Información de estado del botón -->
                        <div class="mt-2">
                          <small class="text-muted" id="estadoValidacion">
                            <i class="icon-info-circle me-1"></i>Complete los campos requeridos para habilitar el botón de guardar
                          </small>
                        </div>
                        
                        <!-- Notificaciones -->
                        <div class="mt-3">
                          <div id="noti" class="alert alert-info d-none" role="alert"></div>
                        </div>
                      </form>
                    </div>
                  </div>
            <!-- Row end -->

                </div>
                <!-- Grid Principal ends -->
              </div>
            </div>
            <!-- Wizard Container ends -->

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


    <script>
let camposRequeridos = {
    "BASE DE DATOS": ["telefono"],
    "ACTIVAR": ["telefono", "fechaCierre", "fechaCompromiso", "ventaReal"],
    "ESTRECHAR": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal"],
    "EN PRONOSTICO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp", "producto"],
    "CERRADO GANADO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp", "inputCity", "calle", "folio", "producto", "sexo", "estado", "domicilio", "plazo", "inputZip"]
};

if($modelo_negocio === 'TLA'){
    camposRequeridos = {
        "BASE DE DATOS": ["telefono"],
        "CERRADO GANADO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp", "inputCity", "calle", "folio", "producto", "sexo", "estado"],
        "CERRADO PERDIDO": ["motivoRechazo"]
    };
}

// Función para verificar si un valor es numérico
function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// Función para mostrar mensaje debajo del campo
function mostrarError(input, mensaje) {
    // limpiar mensajes previos
    let existente = input.parentNode.querySelector(".mensaje-error");
    if (existente) {
        existente.remove();
    }

    // marcar en rojo
    input.style.border = "2px solid red";

    // agregar mensaje
    let span = document.createElement("span");
    span.classList.add("mensaje-error");
    span.style.color = "red";
    span.style.fontSize = "12px";
    span.textContent = mensaje;
    input.parentNode.appendChild(span);
}

// Función unificada de validación
function validarCampos() {
    console.log("=== INICIANDO VALIDACIÓN UNIFICADA ===");
    
    // Obtener la etapa
    let etapa = document.getElementById("etapa") ? document.getElementById("etapa").value : null;
    
    // Para modelo TLA, usar la etapa seleccionada en el formulario
    <?php if ($modelo_negocio === 'TLA'): ?>
    let etapaSelect = document.getElementById("etapa");
    if (etapaSelect) {
        etapa = etapaSelect.value;
    } else {
        console.error("No se encontró el campo etapa para TLA");
        alert("Error: No se pudo encontrar el campo de etapa");
        return false;
    }
    <?php endif; ?>
    
    console.log("Etapa seleccionada:", etapa);
    
    // Verificar si ventaFrio está marcado
    let checkVentaFrio = document.getElementById("ventaFrio");
    let ventaFrio = checkVentaFrio && checkVentaFrio.checked;
    console.log("Venta frío marcado:", ventaFrio);
    
    // Limpiar estilos/mensajes previos
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.mensaje-error').forEach(el => el.remove());

    // Validación especial para CERRADO PERDIDO - solo motivo de rechazo
    if (etapa === "CERRADO PERDIDO") {
        let faltantes = [];
        let esValido = true;
        let motivoRechazo = document.getElementById("motivoRechazo");
        if (motivoRechazo && motivoRechazo.value === "") {
            faltantes.push("motivoRechazo");
            esValido = false;
            mostrarError(motivoRechazo, "El motivo de pérdida es obligatorio para CERRADO PERDIDO");
        }
        
        // Si es OTROS, validar notas
        if (motivoRechazo && motivoRechazo.value === "OTROS") {
            let notas = document.getElementById("notas");
            if (notas && (notas.value === "" || notas.value.trim() === "")) {
                faltantes.push("notas");
                esValido = false;
                mostrarError(notas, "El campo de notas es obligatorio cuando se selecciona 'OTROS'");
            }
        }
        
        // Para CERRADO PERDIDO, no validar otros campos, solo motivo de rechazo
        if (!esValido) {
            console.log("=== VALIDACIÓN FALLIDA PARA CERRADO PERDIDO ===");
            console.log("Campos faltantes:", faltantes);
            alert(`Faltan campos obligatorios para la etapa "${etapa}": ${faltantes.join(', ')}`);
            return false;
        }
        
        console.log("=== VALIDACIÓN EXITOSA PARA CERRADO PERDIDO ===");
        return true;
    }

    // Obtener campos requeridos para la etapa
    let requeridos = camposRequeridos[etapa] || [];
    console.log("Campos requeridos para", etapa, ":", requeridos);

    // Si ventaFrio está marcado, usar campos de CERRADO GANADO
    if (ventaFrio) {
        requeridos = camposRequeridos["CERRADO GANADO"];
        etapa = "CERRADO GANADO";
        console.log("Venta frío activada, validando campos de CERRADO GANADO:", requeridos);
    }

    // Si no hay campos requeridos, mostrar error
    if (!requeridos || requeridos.length === 0) {
        console.error("No se encontraron campos requeridos para la etapa:", etapa);
        alert(`Error: No se encontraron campos requeridos para la etapa "${etapa}"`);
        return false;
    }

    let faltantes = [];
    let esValido = true;

    // Validar campos requeridos para todas las etapas
    console.log("Validando", requeridos.length, "campos requeridos");
    
    requeridos.forEach(campo => {
        let input = document.getElementById(campo);
        if (input) {
            let valor = input.value ? input.value.trim() : '';
            let campoValido = true;
            let mensajeError = "Campo obligatorio";
            
            console.log(`Validando campo ${campo}: "${valor}"`);
            
            // Validación especial para diferentes tipos de campos
            if (campo === 'telefono') {
                if (!valor || valor === '0' || valor === '0000000000' || valor.length < 10) {
                    campoValido = false;
                    mensajeError = "El teléfono es obligatorio y debe tener al menos 10 dígitos";
                }
            } else if (campo === 'ventaReal' || campo === 'enganche' || campo === 'folio') {
                if (!valor || valor === '0' || !isNumeric(valor) || parseFloat(valor) <= 0) {
                    campoValido = false;
                    mensajeError = `El campo ${campo} es obligatorio y debe ser un número mayor a 0`;
                }
            } else if (!valor || valor === '0') {
                campoValido = false;
            }
            
            if (!campoValido) {
                faltantes.push(campo);
                esValido = false;
                
                // Resaltar campo con error
                input.classList.add('is-invalid');
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#f8d7da';
                
                // Mapear nombres técnicos a nombres amigables
                const nombresAmigables = {
                    'telefono': 'Teléfono',
                    'fechaCierre': 'Fecha de Cierre',
                    'fechaCompromiso': 'Fecha de Compromiso',
                    'ventaReal': 'Venta Real',
                    'tipo': 'Tipo',
                    'fechaNacimiento': 'Fecha de Nacimiento',
                    'enganche': 'Enganche',
                    'fuenteProspec': 'Fuente de Prospección',
                    'curp': 'CURP',
                    'inputCity': 'Ciudad',
                    'calle': 'Calle',
                    'folio': 'Folio',
                    'producto': 'Producto',
                    'sexo': 'Sexo',
                    'estado': 'Estado',
                    'asesor': 'Asesor',
                    'domicilio': 'Domicilio',
                    'plazo': 'Plazo',
                    'inputZip': 'Código Postal',
                    'motivoRechazo': 'Motivo de Rechazo'
                };
                
                let nombreCampo = nombresAmigables[campo] || campo;
                
                // Mostrar mensaje de error específico
                let feedbackElement = input.parentNode.querySelector('.invalid-feedback');
                if (feedbackElement) {
                    if (mensajeError && mensajeError !== "Campo obligatorio") {
                        feedbackElement.textContent = mensajeError;
                    } else {
                        feedbackElement.textContent = `El campo "${nombreCampo}" es obligatorio para la etapa "${etapa}"`;
                    }
                    feedbackElement.style.display = 'block';
                }
                
                // También mostrar error con la función existente
                mostrarError(input, mensajeError);
            } else {
                // Limpiar estilos de error si el campo está lleno
                input.classList.remove('is-invalid');
                input.style.borderColor = '';
                input.style.backgroundColor = '';
                let feedbackElement = input.parentNode.querySelector('.invalid-feedback');
                if (feedbackElement) {
                    feedbackElement.style.display = 'none';
                }
            }
        } else {
            console.warn(`Campo ${campo} no encontrado en el DOM`);
        }
    });

    if (!esValido) {
        console.log("=== VALIDACIÓN FALLIDA ===");
        console.log("Campos faltantes:", faltantes);
        let mensajeEtapa = ventaFrio ? "venta frío" : etapa;
        alert(`Faltan campos obligatorios para ${mensajeEtapa}: ${faltantes.join(', ')}`);
        console.log("=== BLOQUEANDO ENVÍO DEL FORMULARIO ===");
        return false;
    }

    console.log("=== VALIDACIÓN EXITOSA - PERMITIENDO ENVÍO ===");
    return true;
}

// Función de prueba para verificar que la validación funcione
function probarValidacion() {
    console.log("=== PROBANDO VALIDACIÓN ===");
    let resultado = validarCampos();
    console.log("Resultado de validación:", resultado);
    if (resultado) {
        alert("✅ Validación exitosa - El formulario se puede enviar");
    } else {
        alert("❌ Validación fallida - El formulario NO se puede enviar");
    }
    return resultado;
}

// Función para validar campos en tiempo real y habilitar/deshabilitar botón
function validarCamposTiempoReal() {
    console.log("=== VALIDACIÓN EN TIEMPO REAL ===");
    
    // Obtener la etapa
    let etapa = document.getElementById("etapa") ? document.getElementById("etapa").value : null;
    
    // Para modelo TLA, usar la etapa seleccionada en el formulario
    <?php if ($modelo_negocio === 'TLA'): ?>
    let etapaSelect = document.getElementById("etapa");
    if (etapaSelect) {
        etapa = etapaSelect.value;
    }
    <?php endif; ?>
    
    // Verificar si ventaFrio está marcado
    let checkVentaFrio = document.getElementById("ventaFrio");
    let ventaFrio = checkVentaFrio && checkVentaFrio.checked;
    
    // Validación especial para CERRADO PERDIDO - solo motivo de rechazo
    if (etapa === "CERRADO PERDIDO") {
        let motivoRechazo = document.getElementById("motivoRechazo");
        let esValido = motivoRechazo && motivoRechazo.value !== "";
        
        let btnGuardar = document.getElementById("btnGuardar");
        if (btnGuardar) {
            if (esValido) {
                btnGuardar.disabled = false;
                btnGuardar.classList.remove("btn-secondary");
                btnGuardar.classList.add("btn-primary");
            } else {
                btnGuardar.disabled = true;
                btnGuardar.classList.remove("btn-primary");
                btnGuardar.classList.add("btn-secondary");
            }
        }
        return esValido;
    }

    // Obtener campos requeridos para la etapa
    let requeridos = camposRequeridos[etapa] || [];
    
    // Si ventaFrio está marcado, usar campos de CERRADO GANADO
    if (ventaFrio) {
        requeridos = camposRequeridos["CERRADO GANADO"];
        etapa = "CERRADO GANADO";
    }
    
    console.log("Etapa:", etapa, "Campos requeridos:", requeridos);
    
    // Si no hay campos requeridos, deshabilitar botón
    if (!requeridos || requeridos.length === 0) {
        let btnGuardar = document.getElementById("btnGuardar");
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.classList.remove("btn-primary");
            btnGuardar.classList.add("btn-secondary");
        }
        return false;
    }
    
    // Validar cada campo requerido
    let todosCompletos = true;
    let camposFaltantes = [];
    
    requeridos.forEach(campo => {
        let input = document.getElementById(campo);
        if (input) {
            let valor = input.value ? input.value.trim() : '';
            let esValido = true;
            
            // Validación especial para diferentes tipos de campos
            if (campo === 'telefono') {
                if (!valor || valor === '0' || valor === '0000000000' || valor.length < 10) {
                    esValido = false;
                }
            } else if (campo === 'ventaReal' || campo === 'enganche' || campo === 'folio') {
                if (!valor || valor === '0' || !isNumeric(valor) || parseFloat(valor) <= 0) {
                    esValido = false;
                }
            } else if (!valor || valor === '0') {
                esValido = false;
            }
            
            if (!esValido) {
                todosCompletos = false;
                camposFaltantes.push(campo);
            }
        } else {
            console.warn(`Campo ${campo} no encontrado en el DOM`);
            todosCompletos = false;
            camposFaltantes.push(campo);
        }
    });
    
    console.log("Campos faltantes:", camposFaltantes);
    
    // Habilitar/deshabilitar botón según validación
    let btnGuardar = document.getElementById("btnGuardar");
    let estadoValidacion = document.getElementById("estadoValidacion");
    
    if (todosCompletos) {
        // HABILITAR BOTÓN - Todos los campos están completos
        btnGuardar.disabled = false;
        btnGuardar.classList.remove("btn-secondary");
        btnGuardar.classList.add("btn-primary");
        btnGuardar.style.opacity = "1";
        btnGuardar.style.cursor = "pointer";
        
        if (estadoValidacion) {
            estadoValidacion.innerHTML = '<i class="icon-check-circle me-1 text-success"></i>Todos los campos requeridos están completos - Puede guardar';
            estadoValidacion.className = "text-success";
        }
        console.log("✅ Todos los campos completos - Botón HABILITADO");
    } else {
        // DESHABILITAR BOTÓN - Faltan campos
        btnGuardar.disabled = true;
        btnGuardar.classList.remove("btn-primary");
        btnGuardar.classList.add("btn-secondary");
        btnGuardar.style.opacity = "0.6";
        btnGuardar.style.cursor = "not-allowed";
        
        if (estadoValidacion) {
            let mensajeEtapa = ventaFrio ? "venta frío" : etapa;
            let camposFaltantesTexto = camposFaltantes.slice(0, 3).join(", ");
            if (camposFaltantes.length > 3) {
                camposFaltantesTexto += ` y ${camposFaltantes.length - 3} más`;
            }
            estadoValidacion.innerHTML = `<i class="icon-info-circle me-1"></i>Complete los campos requeridos para ${mensajeEtapa}: ${camposFaltantesTexto}`;
            estadoValidacion.className = "text-muted";
        }
        console.log("❌ Faltan campos - Botón DESHABILITADO");
        console.log("Campos faltantes:", camposFaltantes);
    }
    
    return todosCompletos;
}

// Función para configurar listeners en todos los campos
function configurarValidacionTiempoReal() {
    console.log("=== CONFIGURANDO VALIDACIÓN EN TIEMPO REAL ===");
    
    // Obtener todos los campos del formulario
    let todosLosCampos = [
        "nombre", "apellidoPaterno", "apellidoMaterno", "telefono", "tipo", 
        "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", 
        "enganche", "fuenteProspec", "curp", "inputCity", "calle", "folio", 
        "producto", "sexo", "estado", "domicilio", "plazo", "inputZip", "motivoRechazo"
    ];
    
    // Agregar listeners a todos los campos
    todosLosCampos.forEach(campo => {
        let input = document.getElementById(campo);
        if (input) {
            // Remover listeners previos para evitar duplicados
            input.removeEventListener("input", validarCamposTiempoReal);
            input.removeEventListener("change", validarCamposTiempoReal);
            
            // Agregar nuevos listeners
            input.addEventListener("input", validarCamposTiempoReal);
            input.addEventListener("change", validarCamposTiempoReal);
            console.log(`✅ Listener agregado a campo: ${campo}`);
        } else {
            console.warn(`⚠️ Campo no encontrado: ${campo}`);
        }
    });
    
    // Agregar listener al select de etapa
    let etapaSelect = document.getElementById("etapa");
    if (etapaSelect) {
        etapaSelect.removeEventListener("change", validarCamposTiempoReal);
        etapaSelect.addEventListener("change", validarCamposTiempoReal);
        console.log("✅ Listener agregado al select de etapa");
    } else {
        console.warn("⚠️ Select de etapa no encontrado");
    }
    
    // Agregar listener al checkbox de venta frío
    let ventaFrioCheckbox = document.getElementById("ventaFrio");
    if (ventaFrioCheckbox) {
        ventaFrioCheckbox.removeEventListener("change", validarCamposTiempoReal);
        ventaFrioCheckbox.addEventListener("change", validarCamposTiempoReal);
        console.log("✅ Listener agregado al checkbox de venta frío");
    } else {
        console.warn("⚠️ Checkbox de venta frío no encontrado");
    }
    
    // Validación inicial
    console.log("=== EJECUTANDO VALIDACIÓN INICIAL ===");
    validarCamposTiempoReal();
}

// Inicializar validación en tiempo real cuando se carga la página
document.addEventListener("DOMContentLoaded", function() {
    console.log("=== INICIALIZANDO VALIDACIÓN EN TIEMPO REAL ===");
    configurarValidacionTiempoReal();
});

// La validación se ejecuta directamente desde el atributo onsubmit del formulario
</script>




</html>
    <!-- *************
      ************ JavaScript Files *************
    ************* -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Inicializar variables cuando el DOM esté listo
        let etapaSelect = document.getElementById("etapa");
        let ventaFrioCheckbox = document.getElementById("ventaFrio");
        let sexoContainer = document.getElementById("sexoContainer");
        let estadoContainer = document.getElementById("estadoContainer");
        
        // Función para habilitar/deshabilitar campo ventaReal según el tipo
        function toggleVentaRealField() {
            const tipoSelect = document.getElementById("tipo");
            const ventaRealInput = document.getElementById("ventaReal");
            
            if (tipoSelect && ventaRealInput) {
                const tipoValue = tipoSelect.value;
                
                if (tipoValue === "SERVICIO") {
                    // Deshabilitar campo ventaReal cuando tipo es SERVICIO
                    ventaRealInput.disabled = false;
                    ventaRealInput.style.backgroundColor = "#f8f9fa";
                    ventaRealInput.style.color = "#6c757d";
                    ventaRealInput.value = ""; // Limpiar el valor
                } else {
                    // Habilitar campo ventaReal cuando tipo es diferente de SERVICIO
                    ventaRealInput.disabled = false;
                    ventaRealInput.style.backgroundColor = "";
                    ventaRealInput.style.color = "";
                }
            }
        }
        
        // Agregar event listener al campo tipo
        const tipoSelect = document.getElementById("tipo");
        if (tipoSelect) {
            tipoSelect.addEventListener("change", toggleVentaRealField);
            // Ejecutar al cargar la página para establecer el estado inicial
            toggleVentaRealField();
        }
        let sexoSelect = document.getElementById("sexo");
        let estadoSelect = document.getElementById("estado");
        let motivoPerdidaContainer = document.getElementById("motivoPerdidaContainer");
        let motivoRechazoSelect = document.getElementById("motivoRechazo");
        let notasField = document.getElementById("notas");

        // Definir los campos requeridos por etapa
        let camposRequeridos = {
            "BASE DE DATOS": ["telefono"],
            "ACTIVAR": ["telefono", "fechaCierre", "fechaCompromiso", "ventaReal"],
            "ESTRECHAR": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal"],
            "EN PRONOSTICO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp"],
            "CERRADO GANADO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp", "inputCity", "calle", "folio", "producto", "sexo", "estado"],
            "CERRADO PERDIDO": ["motivoRechazo"]
        };

        <?php if ($modelo_negocio === 'TLA'): ?>
        // Para modelo TLA, redefinir campos requeridos
        camposRequeridos = {
            "BASE DE DATOS": ["telefono"],
            "CERRADO GANADO": ["telefono", "tipo", "fechaNacimiento", "fechaCierre", "fechaCompromiso", "ventaReal", "enganche", "fuenteProspec", "curp", "inputCity", "calle", "folio", "producto", "sexo", "estado"],
            "CERRADO PERDIDO": ["motivoRechazo"]
        };
        <?php endif; ?>

        function validarCampos() {
            if (!etapaSelect || !ventaFrioCheckbox) {
                console.warn("Elementos del formulario no encontrados en validarCampos()");
                return;
            }
            
            let etapa = etapaSelect.value;
            let ventaFrio = ventaFrioCheckbox.checked;

            // Controlar visibilidad de la sección "Datos Personales del Cliente"
            let datosPersonalesSection = document.querySelector('.form-section-large');
            if (datosPersonalesSection) {
                <?php if ($modelo_negocio === 'TLA'): ?>
                // Para TLA, siempre mostrar la sección de datos personales
                datosPersonalesSection.style.display = "block";
                <?php else: ?>
                // Mostrar la sección para todas las etapas excepto "BASE DE DATOS"
                // PERO SIEMPRE mostrar cuando ventaFrio está marcado
                if (etapa === "BASE DE DATOS" && !ventaFrio) {
                    datosPersonalesSection.style.display = "none";
                } else {
                    datosPersonalesSection.style.display = "block";
                }
                <?php endif; ?>
            }

            // Mostrar/ocultar y habilitar/deshabilitar motivo de pérdida para CERRADO PERDIDO (para todos los modelos)
            if (etapa === "CERRADO PERDIDO") {
                if (motivoPerdidaContainer) {
                    motivoPerdidaContainer.style.display = "block";
                }
                if (motivoRechazoSelect) {
                    motivoRechazoSelect.required = true;
                    motivoRechazoSelect.disabled = false;
                }
            } else {
                if (motivoPerdidaContainer) {
                    motivoPerdidaContainer.style.display = "none";
                }
                if (motivoRechazoSelect) {
                    motivoRechazoSelect.required = false;
                    motivoRechazoSelect.disabled = true;
                    motivoRechazoSelect.value = ""; // Limpiar el valor cuando se deshabilita
                }
            }

            // Mostrar/ocultar campos de sexo y estado
            <?php if ($modelo_negocio === 'TLA'): ?>
            // Para modelo TLA, mostrar campos de sexo y estado según la etapa
            if (etapa === "CERRADO GANADO") {
                sexoContainer.style.display = "block";
                estadoContainer.style.display = "block";
                sexoSelect.required = true;
                estadoSelect.required = true;
            } else {
                sexoContainer.style.display = "block";
                estadoContainer.style.display = "block";
                sexoSelect.required = false;
                estadoSelect.required = false;
            }
            <?php else: ?>
            if (etapa === "CERRADO GANADO" || ventaFrio) {
                sexoContainer.style.display = "block";
                estadoContainer.style.display = "block";
                sexoSelect.required = true;
                estadoSelect.required = true;
            } else {
                sexoContainer.style.display = "none";
                estadoContainer.style.display = "none";
                sexoSelect.required = false;
                estadoSelect.required = false;
            }
            <?php endif; ?>

            // Reiniciar validaciones (quitar required de todos los campos)
            document.querySelectorAll("[required]").forEach(function (input) {
                input.removeAttribute("required");
            });

            // Agregar "required" solo a los campos de la etapa seleccionada
            // Para CERRADO PERDIDO, solo requerir motivo de rechazo (para todos los modelos)
            if (etapa === "CERRADO PERDIDO") {
                if (motivoRechazoSelect) {
                    motivoRechazoSelect.setAttribute("required", "true");
                }
            } else {
                <?php if ($modelo_negocio === 'TLA'): ?>
                // Para TLA, manejar validaciones según la etapa
                if (camposRequeridos[etapa]) {
                    camposRequeridos[etapa].forEach(function (campoId) {
                        let campo = document.getElementById(campoId);
                        if (campo) {
                            campo.setAttribute("required", "true");
                        }
                    });
                }
                <?php else: ?>
                if (camposRequeridos[etapa]) {
                    camposRequeridos[etapa].forEach(function (campoId) {
                        let campo = document.getElementById(campoId);
                        if (campo) {
                            campo.setAttribute("required", "true");
                        }
                    });
                }
                <?php endif; ?>
            }

            // Si el checkbox "ventaFrio" está marcado, requerir todos los campos excepto correo
            if (ventaFrio) {
                let camposObligatorios = ["nombre", "apellidoPaterno", "apellidoMaterno", "etapa", "tipo", "fechaNacimiento", "telefono", "fechaCierre", "curp", "producto", "tipoVenta", "fechaCompromiso", "fuenteProspec", "asesor", "domicilio", "calle", "inputCity", "plazo", "inputZip", "enganche", "ventaReal", "folio", "sexo", "estado"];
                camposObligatorios.forEach(function (campoId) {
                    let campo = document.getElementById(campoId);
                    if (campo) {
                        campo.setAttribute("required", "true");
                    }
                });
            }
            
            // Manejar requerimientos para CERRADO PERDIDO (para todos los modelos)
            if (etapa === "CERRADO PERDIDO") {
                // En CERRADO PERDIDO, solo requerir motivo de rechazo
                if (motivoRechazoSelect) {
                    motivoRechazoSelect.required = true;
                }
                
                // Si es OTROS, también requerir notas
                if (motivoRechazoSelect && motivoRechazoSelect.value === "OTROS") {
                    if (notasField) {
                        notasField.required = true;
                        notasField.setAttribute("required", "true");
                    }
                }
            }
        }



        // Ejecutar validación al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            validarCampos();
        });

        // Ejecutar validación cuando cambie la etapa
        if (etapaSelect) {
            etapaSelect.addEventListener("change", function() {
                // Manejar habilitación/deshabilitación del motivo de rechazo
                if (etapaSelect.value === "CERRADO PERDIDO") {
                    if (motivoRechazoSelect) {
                        motivoRechazoSelect.disabled = false;
                        motivoRechazoSelect.required = true;
                    }
                    if (motivoPerdidaContainer) {
                        motivoPerdidaContainer.style.display = "block";
                    }
                } else {
                    if (motivoRechazoSelect) {
                        motivoRechazoSelect.disabled = true;
                        motivoRechazoSelect.required = false;
                        motivoRechazoSelect.value = ""; // Limpiar el valor
                    }
                    if (motivoPerdidaContainer) {
                        motivoPerdidaContainer.style.display = "none";
                    }
                }
                validarCampos();
            });
        }
        // Ejecutar validación cuando cambie el checkbox
        if (ventaFrioCheckbox) {
            ventaFrioCheckbox.addEventListener("change", validarCampos);
        }
        
        // Agregar listener para motivo de rechazo (para todos los modelos)
        if (motivoRechazoSelect) {
            motivoRechazoSelect.addEventListener("change", function() {
                // Si es OTROS, hacer notas requerido
                if (motivoRechazoSelect.value === "OTROS") {
                    if (notasField) {
                        notasField.required = true;
                        notasField.setAttribute("required", "true");
                    }
                } else {
                    // Si no es OTROS, quitar requerimiento de notas
                    if (notasField) {
                        notasField.required = false;
                        notasField.removeAttribute("required");
                    }
                }
                validarCampos();
            });
        }

        // Limpiar estilos de error cuando el usuario escriba en cualquier campo
        function limpiarErrorCampo(campo) {
            if (campo.value.trim() !== '') {
                campo.classList.remove('is-invalid');
                campo.style.borderColor = '';
                campo.style.backgroundColor = '';
                const feedbackElement = campo.parentNode.querySelector('.invalid-feedback');
                if (feedbackElement) {
                    feedbackElement.style.display = 'none';
                }
            }
        }

        // Aplicar limpieza de errores a todos los campos del formulario
        const todosLosCampos = [
            'nombre', 'apellidoPaterno', 'apellidoMaterno', 'telefono', 'etapa', 'tipo', 
            'fechaNacimiento', 'fechaCierre', 'fechaCompromiso', 'ventaReal', 'enganche', 
            'fuenteProspec', 'curp', 'inputCity', 'calle', 'folio', 'producto', 'sexo', 
            'estado', 'asesor', 'domicilio', 'plazo', 'inputZip'
        ];
        
        todosLosCampos.forEach(function(campoId) {
            const campo = document.getElementById(campoId);
            if (campo) {
                campo.addEventListener('input', function() {
                    limpiarErrorCampo(this);
                });
                campo.addEventListener('change', function() {
                    limpiarErrorCampo(this);
                });
            }
        });

        // Función para obtener el precio y enganche
        function obtenerPrecioYEnganche() {
            const producto = document.getElementById("producto").value;
            const plazo = document.getElementById("plazo").value;
            const sucursal = '<?php echo $_SESSION['sucursal']; ?>'; // Obtener la sucursal del usuario logueado

            // Debugger: Imprimir los valores que se enviarán
            console.log("Producto:", producto);
            console.log("Plazo:", plazo);
            console.log("Sucursal:", sucursal);

            if (producto && plazo) {
                // Realizar una solicitud AJAX para obtener el precio y enganche
                fetch(`get_price.php?producto=${encodeURIComponent(producto)}&plazo=${encodeURIComponent(plazo)}&sucursal=${encodeURIComponent(sucursal)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Guardar el precio original (precio total, NO dividido entre plazo)
                            let precioBase = parseFloat(data.precio) || 0;
                            
                            // Si el precio viene dividido entre plazo, multiplicarlo para obtener el total
                            const plazoSeleccionado = parseInt(document.getElementById("plazo").value) || 1;
                            
                            // Verificar si el precio parece estar dividido entre plazo
                            // (esto es una heurística, ajusta según tu lógica de negocio)
                            if (precioBase < 10000 && plazoSeleccionado > 1) {
                                precioBase = precioBase * plazoSeleccionado;
                                console.log(`🔧 Precio ajustado: ${data.precio} x ${plazoSeleccionado} = ${precioBase}`);
                            }
                            
                            document.getElementById("precioOriginal").value = precioBase;
                            document.getElementById("enganche").value = data.enganche || (precioBase * 0.20); // 20% por defecto
                            
                            // Calcular venta real con descuento actual
                            calcularVentaReal();
                            
                            // Mostrar notificación de éxito
                            mostrarNotificacion(`💰 Precio actualizado: $${precioBase.toFixed(2)} para ${plazoSeleccionado} meses`, "success");
                        } else {
                            mostrarNotificacion("No se encontraron datos para el producto y plazo seleccionados.", "warning");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarNotificacion("Ocurrió un error al obtener los datos.", "danger");
                    });
            }
        }

        // Variables globales para funciones
        window.calcularVentaReal = function() {
            const precioOriginalEl = document.getElementById('precioOriginal');
            const descuentoEl = document.getElementById('descuento');
            const ventaRealEl = document.getElementById('ventaReal');
            const engancheEl = document.getElementById('enganche');
            
            if (!precioOriginalEl || !descuentoEl || !ventaRealEl) {
                console.warn('Elementos no encontrados para calcular venta real');
                return;
            }
            
            let precioOriginal = parseFloat(precioOriginalEl.value) || 0;
            let descuento = parseFloat(descuentoEl.value) || 0;
            
            // Si no hay precio original pero sí hay venta real, usar venta real como base
            if (precioOriginal === 0 && ventaRealEl.value) {
                precioOriginal = parseFloat(ventaRealEl.value) || 0;
                precioOriginalEl.value = precioOriginal;
            }
            
            // Validar que el descuento no exceda 70%
            if (descuento > 70) {
                descuentoEl.value = 70;
                descuento = 70;
                mostrarNotificacion("El descuento máximo permitido es 70%", "warning");
            }
            
            if (descuento < 0) {
                descuentoEl.value = 0;
                descuento = 0;
                mostrarNotificacion("El descuento no puede ser negativo", "warning");
            }
            
            // Calcular precio con descuento (SIN dividir entre plazo)
            const precioConDescuento = precioOriginal - (precioOriginal * (descuento / 100));
            
            // Actualizar el campo de venta real
            ventaRealEl.value = precioConDescuento.toFixed(2);
            
            // Calcular y ajustar enganche automáticamente
            calcularEngancheAutomatico(precioConDescuento);
            
            // Mostrar enganche mínimo sugerido
            mostrarEngancheMinimo();
            
            // Actualizar indicadores visuales
            actualizarIndicadoresVisuales(descuento, precioOriginal, precioConDescuento);
            
            console.log(`Descuento aplicado: ${descuento}% | Precio original: $${precioOriginal} | Precio final: $${precioConDescuento.toFixed(2)}`);
        };

        // Función para calcular y ajustar enganche automáticamente
        window.calcularEngancheAutomatico = function(precioFinal) {
            const engancheEl = document.getElementById('enganche');
            const plazoEl = document.getElementById('plazo');
            const descuentoEl = document.getElementById('descuento');
            
            if (!engancheEl || !plazoEl || !descuentoEl) return;
            
            const plazo = parseInt(plazoEl.value) || 1;
            const descuento = parseFloat(descuentoEl.value) || 0;
            
            // Calcular enganche automático basado en el descuento
            let engancheCalculado;
            
            if (descuento > 0) {
                // Si hay descuento, el enganche se ajusta automáticamente
                // Enganche = Venta Real / Plazo (esto asegura que el enganche sea el mínimo requerido)
                engancheCalculado = precioFinal / plazo;
            } else {
                // Si no hay descuento, usar el enganche original o 20% del precio
                const precioOriginal = parseFloat(document.getElementById('precioOriginal').value) || precioFinal;
                engancheCalculado = precioOriginal * 0.20; // 20% por defecto
            }
            
            // Actualizar el campo de enganche
            engancheEl.value = engancheCalculado.toFixed(2);
            
            // Aplicar estilos de éxito
            engancheEl.style.borderColor = '#28a745';
            engancheEl.style.backgroundColor = '#d4edda';
            engancheEl.style.color = '#155724';
            engancheEl.setCustomValidity('');
            
            // Mostrar información del cálculo
            console.log(`💰 Enganche ajustado automáticamente: $${precioFinal.toFixed(2)} ÷ ${plazo} meses = $${engancheCalculado.toFixed(2)}`);
            
            // Mostrar notificación de ajuste
            mostrarNotificacion(
                `✅ Enganche ajustado automáticamente a $${engancheCalculado.toFixed(2)} (Venta Real ÷ Plazo)`,
                'success'
            );
            
        };
        
        // Función para validar enganche (mantener para validación manual)
        window.validarEnganche = function(precioFinal) {
            const engancheEl = document.getElementById('enganche');
            const plazoEl = document.getElementById('plazo');
            
            if (!engancheEl || !plazoEl) return;
            
            const enganche = parseFloat(engancheEl.value) || 0;
            const plazo = parseInt(plazoEl.value) || 1;
            
            // Calcular enganche mínimo: Venta Real / Plazo Sugerido
            const engancheMinimo = precioFinal / plazo;
            
            if (enganche > 0) {
                if (enganche < engancheMinimo) {
                    // Enganche menor al mínimo requerido
                    engancheEl.style.borderColor = '#dc3545';
                    engancheEl.style.backgroundColor = '#f8d7da';
                    engancheEl.style.color = '#721c24';
                    
                    mostrarNotificacion(
                        `❌ El enganche mínimo es de $${engancheMinimo.toFixed(2)} (Venta Real ÷ Plazo). Solo podrá dar enganche si es igual o mayor a este valor.`,
                        'danger'
                    );
                    
                    // Marcar el campo como inválido
                    engancheEl.setCustomValidity(`El enganche mínimo es $${engancheMinimo.toFixed(2)}`);
                } else {
                    // Enganche válido
                    engancheEl.style.borderColor = '#28a745';
                    engancheEl.style.backgroundColor = '#d4edda';
                    engancheEl.style.color = '#155724';
                    
                    // Limpiar mensaje de error
                    engancheEl.setCustomValidity('');
                }
            } else {
                // Resetear estilos si está vacío
                engancheEl.style.borderColor = '';
                engancheEl.style.backgroundColor = '';
                engancheEl.style.color = '';
                engancheEl.setCustomValidity('');
            }
            
            // Mostrar información del cálculo
            console.log(`💰 Cálculo de enganche mínimo: $${precioFinal.toFixed(2)} ÷ ${plazo} meses = $${engancheMinimo.toFixed(2)}`);
        };

        // Función para actualizar indicadores visuales
        window.actualizarIndicadoresVisuales = function(descuento, precioOriginal, precioFinal) {
            const ahorroTotal = precioOriginal - precioFinal;
            
            // Actualizar barra de progreso
            const barraEl = document.getElementById('descuento-barra');
            if (barraEl) {
                const porcentajeBarra = (descuento / 70) * 100; // 70% es el máximo
                barraEl.style.width = porcentajeBarra + '%';
                
                // Cambiar color según el descuento
                if (descuento === 0) {
                    barraEl.className = 'progress-bar bg-secondary';
                } else if (descuento <= 10) {
                    barraEl.className = 'progress-bar bg-primary';
                } else if (descuento <= 25) {
                    barraEl.className = 'progress-bar bg-info';
                } else if (descuento <= 50) {
                    barraEl.className = 'progress-bar bg-success';
                } else if (descuento <= 60) {
                    barraEl.className = 'progress-bar bg-warning';
                } else {
                    barraEl.className = 'progress-bar bg-danger';
                }
            }
            
            // Actualizar indicador de texto
            const indicadorEl = document.getElementById('descuento-indicador');
            if (indicadorEl) {
                if (descuento === 0) {
                    indicadorEl.textContent = 'Sin descuento';
                    indicadorEl.className = 'badge bg-secondary';
                } else if (descuento <= 10) {
                    indicadorEl.textContent = 'Descuento básico';
                    indicadorEl.className = 'badge bg-primary';
                } else if (descuento <= 25) {
                    indicadorEl.textContent = 'Descuento bueno';
                    indicadorEl.className = 'badge bg-info';
                } else if (descuento <= 50) {
                    indicadorEl.textContent = 'Descuento excelente';
                    indicadorEl.className = 'badge bg-success';
                } else if (descuento <= 60) {
                    indicadorEl.textContent = 'Descuento premium';
                    indicadorEl.className = 'badge bg-warning';
                } else {
                    indicadorEl.textContent = 'Descuento máximo';
                    indicadorEl.className = 'badge bg-danger';
                }
            }
            
            // Actualizar texto de notificación si existe
            const notiEl = document.getElementById('noti');
            if (notiEl && descuento > 0) {
                const mensaje = `🎉 ¡Ahorro de $${ahorroTotal.toFixed(2)} con ${descuento}% de descuento!`;
                notiEl.className = 'alert alert-success';
                notiEl.textContent = mensaje;
                notiEl.classList.remove('d-none');
                
                // Ocultar después de 5 segundos
                setTimeout(() => {
                    notiEl.classList.add('d-none');
                }, 5000);
            } else if (notiEl && descuento === 0) {
                notiEl.classList.add('d-none');
            }
        };

        // Función para establecer descuento rápido
        window.setDescuento = function(valor) {
            document.getElementById('descuento').value = valor;
            calcularVentaReal();
        };

        // Función para mostrar notificaciones
        window.mostrarNotificacion = function(mensaje, tipo) {
            const notiElement = document.getElementById('noti');
            if (notiElement) {
                notiElement.className = `alert alert-${tipo}`;
                notiElement.textContent = mensaje;
                notiElement.classList.remove('d-none');
                
                // Ocultar después de 3 segundos
                setTimeout(() => {
                    notiElement.classList.add('d-none');
                }, 3000);
            }
        };
        
        // Función para mostrar enganche mínimo sugerido y cálculo de división
        window.mostrarEngancheMinimo = function() {
            const ventaRealEl = document.getElementById('ventaReal');
            const plazoEl = document.getElementById('plazo');
            const engancheEl = document.getElementById('enganche');
            
            if (ventaRealEl && plazoEl && engancheEl) {
                const ventaReal = parseFloat(ventaRealEl.value) || 0;
                const plazo = parseInt(plazoEl.value) || 1;
                const enganche = parseFloat(engancheEl.value) || 0;
                
                if (ventaReal > 0 && plazo > 0) {
                    const engancheMinimo = ventaReal / plazo;
                    
                    // Crear o actualizar el elemento de ayuda
                    let ayudaElement = document.getElementById('enganche-ayuda');
                    if (!ayudaElement) {
                        ayudaElement = document.createElement('small');
                        ayudaElement.id = 'enganche-ayuda';
                        ayudaElement.className = 'form-text text-info mt-1';
                        engancheEl.parentNode.appendChild(ayudaElement);
                    }
                    
                    let mensaje = `💡 Enganche mínimo: $${engancheMinimo.toFixed(2)} (Venta Real ÷ Plazo)`;
                    
                    
                    ayudaElement.innerHTML = mensaje;
                }
            }
        };
        

        // Agregar un evento para el cambio en el select de producto
        document.getElementById("producto").addEventListener("change", obtenerPrecioYEnganche);

        // Agregar un evento para el cambio en el select de plazo
        document.getElementById("plazo").addEventListener("change", function() {
            obtenerPrecioYEnganche();
            // También validar enganche cuando cambie el plazo
            const ventaRealEl = document.getElementById('ventaReal');
            if (ventaRealEl && ventaRealEl.value) {
                validarEnganche(parseFloat(ventaRealEl.value));
            }
        });
    });
    </script>


    <script>
      // Inicializar event listeners para descuento
      document.addEventListener('DOMContentLoaded', function() {
          console.log('🚀 Inicializando sistema de descuentos...');
          
          // Obtener elementos
          const ventaRealEl = document.getElementById('ventaReal');
          const precioOriginalEl = document.getElementById('precioOriginal');
          const descuentoEl = document.getElementById('descuento');
          
          // Cargar precio original si existe venta real
          if (ventaRealEl && precioOriginalEl && ventaRealEl.value && !precioOriginalEl.value) {
              precioOriginalEl.value = ventaRealEl.value;
              console.log('✅ Precio original cargado:', precioOriginalEl.value);
          }
          
          // Ensure descuento field is always numeric
          if (descuentoEl) {
              const descuentoValue = descuentoEl.value;
              if (descuentoValue === '' || descuentoValue === null || descuentoValue === undefined) {
                  descuentoEl.value = '0';
              } else {
                  const descuentoNum = parseFloat(descuentoValue) || 0;
                  descuentoEl.value = descuentoNum;
              }
              console.log('✅ Descuento inicializado:', descuentoEl.value);
          }
          
          // Event listeners múltiples para capturar todos los cambios
          if (descuentoEl) {
              // Eventos para entrada de teclado
              descuentoEl.addEventListener('input', function(e) {
                  console.log('📝 Input detectado:', e.target.value);
                  
                  // Ensure descuento is always numeric
                  const value = e.target.value;
                  if (value === '' || value === null || value === undefined) {
                      e.target.value = '0';
                  } else {
                      const numValue = parseFloat(value) || 0;
                      e.target.value = numValue;
                  }
                  
                  if (typeof calcularVentaReal === 'function') {
                      calcularVentaReal();
                  }
              });
              
              // Eventos para cambios de valor
              descuentoEl.addEventListener('change', function(e) {
                  console.log('🔄 Change detectado:', e.target.value);
                  
                  // Ensure descuento is always numeric
                  const value = e.target.value;
                  if (value === '' || value === null || value === undefined) {
                      e.target.value = '0';
                  } else {
                      const numValue = parseFloat(value) || 0;
                      e.target.value = numValue;
                  }
                  
                  if (typeof calcularVentaReal === 'function') {
                      calcularVentaReal();
                  }
              });
              
              // Eventos para teclas específicas
              descuentoEl.addEventListener('keyup', function(e) {
                  console.log('⌨️ Keyup detectado:', e.target.value);
                  if (typeof calcularVentaReal === 'function') {
                      calcularVentaReal();
                  }
              });
              
              // Evento para cuando pierde el foco
              descuentoEl.addEventListener('blur', function(e) {
                  console.log('👀 Blur detectado:', e.target.value);
                  if (typeof calcularVentaReal === 'function') {
                      calcularVentaReal();
                  }
              });
              
              // Evento para paste (pegar)
              descuentoEl.addEventListener('paste', function(e) {
                  console.log('📋 Paste detectado');
                  setTimeout(() => {
                      if (typeof calcularVentaReal === 'function') {
                          calcularVentaReal();
                      }
                  }, 100); // Pequeño delay para que se procese el paste
              });
          }
          
          // Event listener para precio original también
          if (precioOriginalEl) {
              precioOriginalEl.addEventListener('input', function(e) {
                  console.log('💰 Precio original cambiado:', e.target.value);
                  if (typeof calcularVentaReal === 'function') {
                      calcularVentaReal();
                  }
              });
          }
          
          // Event listener para enganche
          const engancheEl = document.getElementById('enganche');
          if (engancheEl) {
              engancheEl.addEventListener('input', function(e) {
                  console.log('💳 Enganche cambiado:', e.target.value);
                  const ventaRealEl = document.getElementById('ventaReal');
                  if (ventaRealEl && ventaRealEl.value && typeof validarEnganche === 'function') {
                      validarEnganche(parseFloat(ventaRealEl.value));
                  }
              });
              
              engancheEl.addEventListener('blur', function(e) {
                  console.log('💳 Enganche perdió foco:', e.target.value);
                  const ventaRealEl = document.getElementById('ventaReal');
                  if (ventaRealEl && ventaRealEl.value && typeof validarEnganche === 'function') {
                      validarEnganche(parseFloat(ventaRealEl.value));
                  }
              });
          }
          
          // Calcular venta real inicial si hay datos
          setTimeout(() => {
              if (typeof calcularVentaReal === 'function') {
                  if (precioOriginalEl && precioOriginalEl.value) {
                      console.log('🎯 Calculando venta real inicial...');
                      calcularVentaReal();
                  }
              }
          }, 500);
          
          // Mostrar mensaje de funcionalidad lista
          console.log('✅ Sistema de descuentos inicializado correctamente');
          
          // Test de funciones
          if (typeof calcularVentaReal === 'function') {
              console.log('✅ Función calcularVentaReal disponible');
          } else {
              console.error('❌ Función calcularVentaReal NO disponible');
          }
          
          if (typeof setDescuento === 'function') {
              console.log('✅ Función setDescuento disponible');
          } else {
              console.error('❌ Función setDescuento NO disponible');
          }
      });
     </script> 
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
      function validateInput(input) {
          input.value = input.value.replace(/[^a-zA-Z0-9]/g, ''); // Elimina caracteres no alfanuméricos
      }
</script>

<script>
  function validarVacio() {
    const campo = document.getElementById("nombre").value.trim();
    if (campo === "") {
      alert("El campo no puede estar vacío ni solo con espacios.");
      return false;
    }
    return true;
  }
</script>

<script>
function obtenerVenta() {
  fetch('get_venta.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Convertir los valores a números y formatearlos
      const venta = parseFloat(data.venta) || 0;
      const ventaFaltante = parseFloat(data.venta_faltante) || 0;
      const ventaPronostico = parseFloat(data.venta_pronostico) || 0;

      // Formatear los valores con separadores de miles y dos decimales
      const formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      // Actualizar los elementos HTML
      const ventaElement = document.getElementById('venta');
      const ventaFaltanteElement = document.getElementById('venta_faltante');
      const ventaPronosticoElement = document.getElementById('venta_pronostico');

      if (ventaElement) ventaElement.textContent = formatter.format(venta);
      if (ventaFaltanteElement) ventaFaltanteElement.textContent = formatter.format(ventaFaltante);
      if (ventaPronosticoElement) ventaPronosticoElement.textContent = formatter.format(ventaPronostico);

      console.log('Valores actualizados:', {
        venta: formatter.format(venta),
        venta_faltante: formatter.format(ventaFaltante),
        venta_pronostico: formatter.format(ventaPronostico)
      });
    } else {
      console.error('Error en la respuesta:', data.message);
    }
  })
  .catch(error => console.error('Error en la solicitud:', error));
}

document.addEventListener("DOMContentLoaded", () => {
  obtenerVenta(); // Se ejecuta al cargar la página
  setInterval(obtenerVenta, 300000); // Se repite cada 5 minutos
});
</script>

   
</html>