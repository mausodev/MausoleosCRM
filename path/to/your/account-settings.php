<?php
//session_start();
//require './../controlador/conexion.php';

/*if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}*/

// Handle form submission for inserting or updating an employee
/*if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $iniciales = $_POST['iniciales'];
    $supervisor = $_POST['supervisor'];
    $sucursal = $_POST['sucursal'];
    $departamento = $_POST['departamento'];
    $puesto = $_POST['puesto'];
    $estado = $_POST['estado'];
    $categoria = $_POST['categoria'];
    $meta = $_POST['meta'];
    $equipo = $_POST['equipo'];
    $activo = $_POST['activo'];
    $employee_id = $_POST['employee_id'];

    if ($employee_id) {
        // Update existing employee
        $query = "UPDATE empleado SET nombre='$nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno', iniciales='$iniciales', supervisor='$supervisor', sucursal='$sucursal', departamento='$departamento', puesto='$puesto', estado='$estado', categoria='$categoria', meta='$meta', equipo='$equipo', activo='$activo' WHERE id='$employee_id'";
    } else {
        // Insert new employee
        $query = "INSERT INTO empleado (nombre, apellido_paterno, apellido_materno, iniciales, supervisor, sucursal, departamento, puesto, estado, categoria, meta, equipo, activo) VALUES ('$nombre', '$apellido_paterno', '$apellido_materno', '$iniciales', '$supervisor', '$sucursal', '$departamento', '$puesto', '$estado', '$categoria', '$meta', '$equipo', '$activo')";
    }

    if (mysqli_query($con, $query)) {
        echo "Employee record saved successfully.";
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

// Fetch employees for dropdown
$query = "SELECT id, correo FROM empleado";
$result = mysqli_query($con, $query);*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portal Mausoleos</title>
    <!-- Include your CSS and JS files here -->
</head>
<body>
    <form method="POST" action="">
        <label for="employee_id">Select Employee:</label>
        <select name="employee_id" id="employee_id">
            <option value="">New Employee</option>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['correo']; ?> (<?php echo $row['id']; ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required />

        <label for="apellido_paterno">Apellido Paterno:</label>
        <input type="text" name="apellido_paterno" id="apellido_paterno" required />

        <label for="apellido_materno">Apellido Materno:</label>
        <input type="text" name="apellido_materno" id="apellido_materno" required />

        <label for="iniciales">Iniciales:</label>
        <input type="text" name="iniciales" id="iniciales" required />

        <label for="supervisor">Supervisor:</label>
        <input type="text" name="supervisor" id="supervisor" required />

        <label for="sucursal">Sucursal:</label>
        <input type="text" name="sucursal" id="sucursal" required />

        <label for="departamento">Departamento:</label>
        <input type="text" name="departamento" id="departamento" required />

        <label for="puesto">Puesto:</label>
        <input type="text" name="puesto" id="puesto" required />

        <label for="estado">Estado:</label>
        <input type="text" name="estado" id="estado" required />

        <label for="categoria">Categoria:</label>
        <input type="text" name="categoria" id="categoria" required />

        <label for="meta">Meta:</label>
        <input type="text" name="meta" id="meta" required />

        <label for="equipo">Equipo:</label>
        <input type="text" name="equipo" id="equipo" required />

        <label for="activo">Activo:</label>
        <select name="activo" id="activo">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button type="submit">Save</button>
    </form>
</body>
</html>