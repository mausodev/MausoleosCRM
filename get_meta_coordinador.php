<?php
require './controlador/conexion.php';

if(isset($_POST['coordinador_id']) && isset($_POST['mes'])) {
    $coordinador_id = $_POST['coordinador_id'];
    $mes = $_POST['mes'];
    /*var_dump($coordinador_id );
    die();*/
    $query = "SELECT meta FROM meta_venta WHERE id_cordinador = ? AND nombre_mes = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $coordinador_id, $mes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo $row['meta'];
    } else {
        echo "0";
    }
    
    $stmt->close();
} else {
    echo "0";
}
?> 