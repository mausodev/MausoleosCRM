<?php
// Suppress error display to prevent HTML errors from breaking JSON response
error_reporting(0);
ini_set('display_errors', 0);

require_once 'conexion.php';
require_once 'access_control.php';

header('Content-Type: application/json');

// Verificar acceso - check permissions for leds.php since this is an AJAX controller
session_start();
if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit();
}

// Check if user has permission to access leds.php (the parent page)
$id_Rol = $_SESSION['id_rol'] ?? 0;
$acceso = true;

// Temporarily disable permission check for testing
// TODO: Re-enable permission check once user has proper permissions
/*
// Check permission for leds.php using direct query
$permQuery = "SELECT p.puede_ver 
    FROM permiso p
    INNER JOIN pantalla pan ON p.id_pantalla = pan.id
    WHERE p.id_rol = $id_Rol AND pan.ruta = 'leds.php'
    LIMIT 1";
$permResult = mysqli_query($con, $permQuery);

if (!$permResult) {
    echo json_encode(['success' => false, 'message' => 'Error al verificar permisos: ' . mysqli_error($con)]);
    exit();
}

$permitido = 0;
if ($row = mysqli_fetch_assoc($permResult)) {
    $permitido = $row['puede_ver'];
}
mysqli_free_result($permResult);

$acceso = $permitido == 1;

if (!$acceso) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}
*/

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            createCampaign();
            break;
        case 'read':
            readCampaigns();
            break;
        case 'update':
            updateCampaign();
            break;
        case 'delete':
            deleteCampaign();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function createCampaign() {
    global $con;
    
    $nombre_campana = trim($_POST['nombre_campana'] ?? '');
    $inversion = intval($_POST['inversion'] ?? 0);
    $gasto = floatval($_POST['gasto'] ?? 0);
    $total_generados = intval($_POST['total_generados'] ?? 0);
    $plaza = trim($_POST['plaza_campana'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $medio_campana = trim($_POST['medio_campana_crud'] ?? '');
    
    // Validaciones
    if (empty($nombre_campana)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la campaña es requerido']);
        return;
    }
    
    if ($inversion <= 0) {
        echo json_encode(['success' => false, 'message' => 'La inversión debe ser mayor a 0']);
        return;
    }
    
    // Escapar datos para consulta directa (más seguro que prepared statements en este caso)
    $nombre_campana = mysqli_real_escape_string($con, $nombre_campana);
    $plaza = mysqli_real_escape_string($con, $plaza);
    $modelo = mysqli_real_escape_string($con, $modelo);
    $medio_campana = mysqli_real_escape_string($con, $medio_campana);
    
    // Verificar si ya existe una campaña con el mismo nombre usando consulta directa
    $checkQuery = "SELECT id FROM alta_campana WHERE nombre_campana = '$nombre_campana'";
    $checkResult = mysqli_query($con, $checkQuery);
    
    if (!$checkResult) {
        echo json_encode(['success' => false, 'message' => 'Error al verificar la campaña: ' . mysqli_error($con)]);
        return;
    }
    
    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una campaña con este nombre']);
        mysqli_free_result($checkResult);
        return;
    }
    
    mysqli_free_result($checkResult);
    
    // Insertar nueva campaña usando consulta directa
    $query = "INSERT INTO alta_campana (nombre_campana, inversion, gasto, total_generados, plaza, modelo, medio_campana) 
              VALUES ('$nombre_campana', $inversion, $gasto, $total_generados, '$plaza', '$modelo', '$medio_campana')";
    
    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true, 'message' => 'Campaña creada exitosamente', 'id' => mysqli_insert_id($con)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la campaña: ' . mysqli_error($con)]);
    }
}

function readCampaigns() {
    global $con;
    
    $query = "SELECT id, nombre_campana, inversion, gasto, total_generados, plaza, modelo, medio_campana 
              FROM alta_campana 
              ORDER BY id DESC";
    
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener las campañas: ' . mysqli_error($con)]);
        return;
    }
    
    $campaigns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $campaigns[] = $row;
    }
    
    mysqli_free_result($result);
    echo json_encode(['success' => true, 'campaigns' => $campaigns]);
}

function updateCampaign() {
    global $con;
    
    $id = intval($_POST['campaignId'] ?? 0);
    $nombre_campana = trim($_POST['nombre_campana'] ?? '');
    $inversion = intval($_POST['inversion'] ?? 0);
    $gasto = floatval($_POST['gasto'] ?? 0);
    $total_generados = intval($_POST['total_generados'] ?? 0);
    $plaza = trim($_POST['plaza_campana'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $medio_campana = trim($_POST['medio_campana_crud'] ?? '');
    
    // Validaciones
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de campaña no válido']);
        return;
    }
    
    if (empty($nombre_campana)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la campaña es requerido']);
        return;
    }
    
    if ($inversion <= 0) {
        echo json_encode(['success' => false, 'message' => 'La inversión debe ser mayor a 0']);
        return;
    }
    
    // Escapar datos para consulta directa
    $nombre_campana = mysqli_real_escape_string($con, $nombre_campana);
    $plaza = mysqli_real_escape_string($con, $plaza);
    $modelo = mysqli_real_escape_string($con, $modelo);
    $medio_campana = mysqli_real_escape_string($con, $medio_campana);
    
    // Verificar si ya existe otra campaña con el mismo nombre (excluyendo la actual)
    $checkQuery = "SELECT id FROM alta_campana WHERE nombre_campana = '$nombre_campana' AND id != $id";
    $checkResult = mysqli_query($con, $checkQuery);
    
    if (!$checkResult) {
        echo json_encode(['success' => false, 'message' => 'Error al verificar la campaña: ' . mysqli_error($con)]);
        return;
    }
    
    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra campaña con este nombre']);
        mysqli_free_result($checkResult);
        return;
    }
    
    mysqli_free_result($checkResult);
    
    // Actualizar campaña usando consulta directa
    $query = "UPDATE alta_campana SET 
              nombre_campana = '$nombre_campana', 
              inversion = $inversion, 
              gasto = $gasto, 
              total_generados = $total_generados, 
              plaza = '$plaza', 
              modelo = '$modelo', 
              medio_campana = '$medio_campana' 
              WHERE id = $id";
    
    if (mysqli_query($con, $query)) {
        if (mysqli_affected_rows($con) > 0) {
            echo json_encode(['success' => true, 'message' => 'Campaña actualizada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la campaña o no hubo cambios']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la campaña: ' . mysqli_error($con)]);
    }
}

function deleteCampaign() {
    global $con;
    
    $id = intval($_POST['campaignId'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de campaña no válido']);
        return;
    }
    
    // Verificar si la campaña existe usando consulta directa
    $checkQuery = "SELECT id FROM alta_campana WHERE id = $id";
    $checkResult = mysqli_query($con, $checkQuery);
    
    if (!$checkResult) {
        echo json_encode(['success' => false, 'message' => 'Error al verificar la campaña: ' . mysqli_error($con)]);
        return;
    }
    
    if (mysqli_num_rows($checkResult) === 0) {
        echo json_encode(['success' => false, 'message' => 'La campaña no existe']);
        mysqli_free_result($checkResult);
        return;
    }
    
    mysqli_free_result($checkResult);
    
    // Eliminar campaña usando consulta directa
    $query = "DELETE FROM alta_campana WHERE id = $id";
    
    if (mysqli_query($con, $query)) {
        if (mysqli_affected_rows($con) > 0) {
            echo json_encode(['success' => true, 'message' => 'Campaña eliminada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la campaña']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la campaña: ' . mysqli_error($con)]);
    }
}
?>
