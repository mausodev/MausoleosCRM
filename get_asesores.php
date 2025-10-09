<?php
require './controlador/conexion.php';

if(isset($_POST['coordinador_id'])) {
    $coordinador_id = $_POST['coordinador_id'];
    $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);
    $sqlPlaza = "SELECT sucursal FROM empleado WHERE id = '$coordinador_id'";

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mes = $row['mes']; 
    } else {
        $mes = 'N/A';
    }

    $result2 = $con->query($sqlPlaza);
    if ($result2->num_rows > 0) {
        $row = $result2->fetch_assoc();
        $plaza = $row['sucursal']; 
    } else {
        $plaza = 'N/A';
    }
    
    $query = "SELECT e.id, e.correo, COALESCE(mv.meta, 0) as meta 
              FROM empleado e 
              LEFT JOIN meta_venta mv ON e.id = mv.id_asesor AND mv.nombre_mes = ?
              WHERE e.id_supervisor = ? 
              AND e.activo = 1
              AND e.puesto = 'ASESOR' 
              AND e.sucursal = '$plaza'";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("si", $mes, $coordinador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = "<option value=''>Seleccione un asesor</option>";
    $asesores = array();
    
    while($row = $result->fetch_assoc()) {
        $options .= "<option value='" . $row['id'] . "'>" . $row['correo'] . " - Meta: " . number_format($row['meta'], 2) . "</option>";
        $asesores[$row['id']] = $row['meta'];
    }
    
    $response = array(
        'options' => $options,
        'asesores' => $asesores
    );
    
    echo json_encode($response);
    $stmt->close();
} else if(isset($_POST['asesor_id'])) {
    $asesor_id = $_POST['asesor_id'];
    $sqlCierre = "SELECT mes FROM calendario WHERE SYSDATE() BETWEEN fecha_inicio AND fecha_cierre";
    $result = $con->query($sqlCierre);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mes = $row['mes']; 
    } else {
        $mes = 'N/A';
    }
    VAR_DUMP($mes);
    die();
    $query = "SELECT 
                c.id,
                CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) as nombre_cliente,
                DATE_FORMAT(c.fecha_creado, '%d/%m/%Y') as fecha_creado,
               
                DATE_FORMAT(c.fecha_compromiso, '%d/%m/%Y') as fecha_compromiso,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM agenda_personal ap 
                        WHERE ap.cliente = c.id 
                        AND ap.fechahora_fin < SYSDATE() 
                        AND ap.completada = 'COMPLETADA'
                    ) THEN 'NO'
                    ELSE 'SI'
                END as iniciado,
                COALESCE(c.venta_embudo, 0) as venta
              FROM cliente c
              WHERE c.asesor = ?
              AND c.etapa = 'CERRADO GANADO'
              AND c.mes = ?
              ORDER BY c.fecha_creado DESC";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $asesor_id, $mes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clientes = array();
    $total_venta = 0;
    
    while($row = $result->fetch_assoc()) {
        $clientes[] = $row;
        $total_venta += $row['venta'];
    }
    
    $response = array(
        'clientes' => $clientes,
        'total_venta' => $total_venta
    );
    
    echo json_encode($response);
    $stmt->close();
} else {
    echo json_encode(array(
        'options' => "<option value=''>Seleccione un asesor</option>",
        'asesores' => array()
    ));
}
?> 