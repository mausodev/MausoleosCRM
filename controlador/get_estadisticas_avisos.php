<?php
require './conexion.php';
require './access_control.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar acceso
$access_data = verificarAcceso();
if (!$access_data['acceso']) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id_usuario = $access_data['id_asesor'];
    
    // Estadísticas generales del usuario
    $sql_stats = "SELECT 
                    COUNT(*) as total_avisos,
                    SUM(CASE WHEN ar.leido = 0 THEN 1 ELSE 0 END) as no_leidos,
                    SUM(CASE WHEN ar.leido = 1 THEN 1 ELSE 0 END) as leidos,
                    SUM(CASE WHEN ap.tipo_aviso = 'IT' THEN 1 ELSE 0 END) as tickets_it,
                    SUM(CASE WHEN ap.tipo_aviso = 'URGENTE' THEN 1 ELSE 0 END) as urgentes,
                    SUM(CASE WHEN ap.tipo_aviso = 'INFORMATIVO' THEN 1 ELSE 0 END) as informativos
                  FROM avisos_receptores ar
                  JOIN aviso_portal ap ON ar.id_aviso = ap.id_aviso
                  WHERE ar.id_receptor = ?";
    
    $stmt_stats = $con->prepare($sql_stats);
    $stmt_stats->bind_param("i", $id_usuario);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
    
    // Avisos recientes (últimos 5)
    $sql_recientes = "SELECT ap.id_aviso, ap.titulo, ap.tipo_aviso, ap.fecha_envio, 
                             e.nombre as emisor, ar.leido
                      FROM aviso_portal ap
                      JOIN empleado e ON ap.id_emisor = e.id
                      JOIN avisos_receptores ar ON ap.id_aviso = ar.id_aviso
                      WHERE ar.id_receptor = ?
                      ORDER BY ap.fecha_envio DESC
                      LIMIT 5";
    
    $stmt_recientes = $con->prepare($sql_recientes);
    $stmt_recientes->bind_param("i", $id_usuario);
    $stmt_recientes->execute();
    $recientes = $stmt_recientes->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Formatear fechas
    foreach ($recientes as &$aviso) {
        $aviso['fecha_formateada'] = date('d/m/Y H:i', strtotime($aviso['fecha_envio']));
    }
    
    // Estadísticas por tipo
    $sql_tipos = "SELECT ap.tipo_aviso, COUNT(*) as cantidad
                  FROM avisos_receptores ar
                  JOIN aviso_portal ap ON ar.id_aviso = ap.id_aviso
                  WHERE ar.id_receptor = ?
                  GROUP BY ap.tipo_aviso
                  ORDER BY cantidad DESC";
    
    $stmt_tipos = $con->prepare($sql_tipos);
    $stmt_tipos->bind_param("i", $id_usuario);
    $stmt_tipos->execute();
    $tipos = $stmt_tipos->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'estadisticas' => [
            'total' => (int)$stats['total_avisos'],
            'no_leidos' => (int)$stats['no_leidos'],
            'leidos' => (int)$stats['leidos'],
            'tickets_it' => (int)$stats['tickets_it'],
            'urgentes' => (int)$stats['urgentes'],
            'informativos' => (int)$stats['informativos']
        ],
        'recientes' => $recientes,
        'por_tipo' => $tipos
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
} finally {
    if (isset($stmt_stats)) $stmt_stats->close();
    if (isset($stmt_recientes)) $stmt_recientes->close();
    if (isset($stmt_tipos)) $stmt_tipos->close();
    $con->close();
}
?>
