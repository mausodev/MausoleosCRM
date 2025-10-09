<?php
require './controlador/conexion.php';

if(isset($_POST['asesor_id']) && isset($_POST['mes'])) {
    $asesor_id = $_POST['asesor_id'];
    $mes = $_POST['mes'];
    
    $query = "SELECT venta FROM meta_venta WHERE id_asesor = ? AND mes = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $asesor_id, $mes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo $row['venta'];
    } else {
        echo "0";
    }
    
    $stmt->close();
} else {
    echo "0";
}
?> 