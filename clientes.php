<?php
// Conexión a la base de datos
$host = 'localhost';
$user = 'root'; // u423288535_TdnDBA
$password = ''; //Mau25Tdn+1
$database = 'global_mausoleo'; // u423288535_globalMausoleo

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Insertar o actualizar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $etapa = $_POST['etapa'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $fecha_cierre = $_POST['fecha_cierre'];
    $CURP = $_POST['CURP'];
    $articulo = $_POST['articulo'];
    $tipo_venta = $_POST['tipo_venta'];
    $fecha_compromiso = $_POST['fecha_compromiso'];
    $notas = $_POST['notas'];
    $origen_cliente = $_POST['origen_cliente'] ?? '';
    $asesor = $_POST['asesor'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $calle = $_POST['calle'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $plazo = $_POST['plazo'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $enganche = $_POST['enganche'] ?? 0;
    $venta_real = $_POST['venta_real'] ?? 0;
    $folio_contrato = $_POST['folio_contrato'] ?? '';
    $venta_frio = isset($_POST['venta_frio']) ? 1 : 0;
    $creado_por = 'admin';
    $modificado_por = 'admin';

    if ($id) {
        // Actualizar cliente existente
        $sql = "UPDATE cliente SET nombre=?, apellido_paterno=?, apellido_materno=?, etapa=?, fecha_nacimiento=?, correo=?, telefono=?, fecha_cierre=?, CURP=?, articulo=?, tipo_venta=?, fecha_compromiso=?, notas=?, origen_cliente=?, asesor=?, domicilio=?, calle=?, ciudad=?, plazo=?, codigo_postal=?, enganche=?, venta_real=?, folio_contrato=?, venta_frio=?, modificado_por=?, fecha_modificado=NOW() WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssisissssssisissiiiisi', $nombre, $apellido_paterno, $apellido_materno, $etapa, $fecha_nacimiento, $correo, $telefono, $fecha_cierre, $CURP, $articulo, $tipo_venta, $fecha_compromiso, $notas, $origen_cliente, $asesor, $domicilio, $calle, $ciudad, $plazo, $codigo_postal, $enganche, $venta_real, $folio_contrato, $venta_frio, $modificado_por, $id);
    } else {
        // Insertar nuevo cliente
        $sql = "INSERT INTO cliente (nombre, apellido_paterno, apellido_materno, etapa, fecha_nacimiento, correo, telefono, fecha_cierre, CURP, articulo, tipo_venta, fecha_compromiso, notas, origen_cliente, asesor, domicilio, calle, ciudad, plazo, codigo_postal, enganche, venta_real, folio_contrato, venta_frio, creado_por, fecha_creado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssisisssssisissiiiiss', $nombre, $apellido_paterno, $apellido_materno, $etapa, $fecha_nacimiento, $correo, $telefono, $fecha_cierre, $CURP, $articulo, $tipo_venta, $fecha_compromiso, $notas, $origen_cliente, $asesor, $domicilio, $calle, $ciudad, $plazo, $codigo_postal, $enganche, $venta_real, $folio_contrato, $venta_frio, $creado_por);
    }

    if ($stmt->execute()) {
        echo "Cliente guardado exitosamente.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Obtener lista de clientes
$clientes = $conn->query("SELECT id, nombre, apellido_paterno, apellido_materno FROM cliente");

// Obtener datos de un cliente específico
$cliente = null;
if (isset($_GET['cliente_id'])) {
    $cliente_id = $_GET['cliente_id'];
    $resultado = $conn->query("SELECT * FROM cliente WHERE id = $cliente_id");
    $cliente = $resultado->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
</head>
<body>
    <h1>Gestión de Clientes</h1>

    <form method="GET" action="">
        <label for="cliente_id">Seleccionar cliente:</label>
        <select name="cliente_id" id="cliente_id" onchange="this.form.submit()">
            <option value="">-- Nuevo Cliente --</option>
            <?php while ($row = $clientes->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php if (isset($cliente['id']) && $cliente['id'] == $row['id']) echo 'selected'; ?>>
                    <?php echo $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $cliente['id'] ?? ''; ?>">

        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo $cliente['nombre'] ?? ''; ?>" required>
        </div>

        <div>
            <label for="apellido_paterno">Apellido Paterno:</label>
            <input type="text" name="apellido_paterno" id="apellido_paterno" value="<?php echo $cliente['apellido_paterno'] ?? ''; ?>" required>
        </div>

        <div>
            <label for="apellido_materno">Apellido Materno:</label>
            <input type="text" name="apellido_materno" id="apellido_materno" value="<?php echo $cliente['apellido_materno'] ?? ''; ?>" required>
        </div>

        <div>
            <label for="etapa">Etapa:</label>
            <input type="text" name="etapa" id="etapa" value="<?php echo $cliente['etapa'] ?? ''; ?>">
        </div>

        <div>
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?php echo $cliente['fecha_nacimiento'] ?? ''; ?>">
        </div>

        <div>
            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" value="<?php echo $cliente['correo'] ?? ''; ?>">
        </div>

        <div>
            <label for="telefono">Teléfono:</label>
            <input type="number" name="telefono" id="telefono" value="<?php echo $cliente['telefono'] ?? ''; ?>" required>
        </div>

        <div>
            <label for="fecha_cierre">Fecha de Cierre:</label>
            <input type="date" name="fecha_cierre" id="fecha_cierre" value="<?php echo $cliente['fecha_cierre'] ?? ''; ?>">
        </div>

        <div>
            <label for="CURP">CURP:</label>
            <input type="text" name="CURP" id="CURP" value="<?php echo $cliente['CURP'] ?? ''; ?>">
        </div>

        <div>
            <label for="articulo">Artículo:</label>
            <input type="text" name="articulo" id="articulo" value="<?php echo $cliente['articulo'] ?? ''; ?>">
        </div>

        <div>
            <label for="tipo_venta">Tipo de Venta:</label>
            <input type="text" name="tipo_venta" id="tipo_venta" value="<?php echo $cliente['tipo_venta'] ?? ''; ?>">
        </div>

        <div>
            <label for="fecha_compromiso">Fecha de Compromiso:</label>
            <input type="date" name="fecha_compromiso" id="fecha_compromiso" value="<?php echo $cliente['fecha_compromiso'] ?? ''; ?>">
        </div>

        <div>
            <label for="notas">Notas:</label>
            <input type="text" name="notas" id="notas" value="<?php echo $cliente['notas'] ?? ''; ?>">
        </div>
        
        <!-- Agregar más campos según sea necesario -->
        
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
