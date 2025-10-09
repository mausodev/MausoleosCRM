<?php
/**
 * Script para agregar campos plaza e id_empleado a la tabla agenda_personal
 */

require './controlador/conexion.php';

try {
    // Agregar columna plaza si no existe
    $sql1 = "ALTER TABLE `agenda_personal` 
             ADD COLUMN IF NOT EXISTS `plaza` varchar(50) DEFAULT NULL COMMENT 'Plaza del usuario logueado' AFTER `correo_asesor`";
    
    if ($con->query($sql1)) {
        echo "✓ Campo 'plaza' agregado exitosamente<br>";
    } else {
        echo "⚠ Error agregando campo 'plaza': " . $con->error . "<br>";
    }

    // Agregar columna id_empleado si no existe
    $sql2 = "ALTER TABLE `agenda_personal` 
             ADD COLUMN IF NOT EXISTS `id_empleado` int(11) DEFAULT NULL COMMENT 'ID del empleado logueado' AFTER `plaza`";
    
    if ($con->query($sql2)) {
        echo "✓ Campo 'id_empleado' agregado exitosamente<br>";
    } else {
        echo "⚠ Error agregando campo 'id_empleado': " . $con->error . "<br>";
    }

    // Agregar índices para mejorar el rendimiento
    $sql3 = "ALTER TABLE `agenda_personal` 
             ADD INDEX IF NOT EXISTS `idx_plaza` (`plaza`),
             ADD INDEX IF NOT EXISTS `idx_id_empleado` (`id_empleado`)";
    
    if ($con->query($sql3)) {
        echo "✓ Índices agregados exitosamente<br>";
    } else {
        echo "⚠ Error agregando índices: " . $con->error . "<br>";
    }

    // Agregar foreign key constraint si no existe
    $sql4 = "ALTER TABLE `agenda_personal` 
             ADD CONSTRAINT IF NOT EXISTS `fk_agenda_empleado` 
             FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id`) ON DELETE SET NULL";
    
    if ($con->query($sql4)) {
        echo "✓ Foreign key constraint agregado exitosamente<br>";
    } else {
        echo "⚠ Error agregando foreign key: " . $con->error . "<br>";
    }

    echo "<br><strong>✅ Configuración completada. Los campos plaza e id_empleado han sido agregados a la tabla agenda_personal.</strong><br>";
    echo "<br><a href='agenda.php'>← Volver a la Agenda</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
