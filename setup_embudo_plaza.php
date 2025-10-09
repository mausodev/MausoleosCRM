<?php
require './controlador/conexion.php';

echo "<h2>Configuración de Tabla embudo_plaza</h2>";

try {
    // Verificar si la tabla existe
    $sqlCheck = "SHOW TABLES LIKE 'embudo_plaza'";
    $resultCheck = $con->query($sqlCheck);
    
    if ($resultCheck->num_rows == 0) {
        echo "<p>La tabla embudo_plaza no existe. Creándola...</p>";
        
        // Crear la tabla
        $sqlCreate = "CREATE TABLE IF NOT EXISTS `embudo_plaza` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `plaza` varchar(50) NOT NULL,
          `etapa` varchar(50) NOT NULL,
          `porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
          `activo` tinyint(1) NOT NULL DEFAULT 1,
          `fecha_creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `fecha_modificado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_plaza_etapa` (`plaza`, `etapa`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($con->query($sqlCreate)) {
            echo "<p style='color: green;'>✓ Tabla embudo_plaza creada exitosamente</p>";
        } else {
            throw new Exception("Error creando la tabla: " . $con->error);
        }
        
        // Insertar datos iniciales
        $plazas = ['CUAUHTEMOC', 'DELICIAS'];
        $etapas = [
            'BASE DE DATOS' => 0.00,
            'ACTIVAR' => 0.00,
            'ESTRECHAR' => 0.25,
            'EN PRONOSTICO' => 0.70,
            'CERRADO GANADO' => 1.00,
            'CERRADO PERDIDO' => 0.00
        ];
        
        $insertCount = 0;
        foreach ($plazas as $plaza) {
            foreach ($etapas as $etapa => $porcentaje) {
                $sqlInsert = "INSERT INTO embudo_plaza (plaza, etapa, porcentaje) VALUES (?, ?, ?)";
                $stmtInsert = $con->prepare($sqlInsert);
                $stmtInsert->bind_param("ssd", $plaza, $etapa, $porcentaje);
                
                if ($stmtInsert->execute()) {
                    $insertCount++;
                } else {
                    echo "<p style='color: orange;'>⚠ Error insertando $plaza - $etapa: " . $stmtInsert->error . "</p>";
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Se insertaron $insertCount registros iniciales</p>";
        
    } else {
        echo "<p style='color: green;'>✓ La tabla embudo_plaza ya existe</p>";
    }
    
    // Mostrar datos actuales
    echo "<h3>Datos actuales en la tabla:</h3>";
    $sqlShow = "SELECT plaza, etapa, porcentaje FROM embudo_plaza WHERE activo = 1 ORDER BY plaza, 
      CASE etapa
        WHEN 'BASE DE DATOS' THEN 1
        WHEN 'ACTIVAR' THEN 2
        WHEN 'ESTRECHAR' THEN 3
        WHEN 'EN PRONOSTICO' THEN 4
        WHEN 'CERRADO GANADO' THEN 5
        WHEN 'CERRADO PERDIDO' THEN 6
        ELSE 7
      END";
    
    $resultShow = $con->query($sqlShow);
    
    if ($resultShow->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Plaza</th><th>Etapa</th><th>Porcentaje</th></tr>";
        
        while ($row = $resultShow->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['plaza']) . "</td>";
            echo "<td>" . htmlspecialchars($row['etapa']) . "</td>";
            echo "<td>" . ($row['porcentaje'] * 100) . "%</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay datos en la tabla</p>";
    }
    
    echo "<p><a href='test_porcentajes.php'>Probar funcionamiento</a></p>";
    echo "<p><a href='embudo.php'>Ir a embudo.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 