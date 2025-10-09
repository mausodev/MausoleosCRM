<?php
// Configuración del Sistema de Avisos

// Tipos de aviso disponibles
define('TIPOS_AVISO', [
    'INFORMATIVO' => [
        'nombre' => 'Informativo',
        'color' => 'info',
        'icono' => 'icon-info',
        'prioridad' => 1
    ],
    'URGENTE' => [
        'nombre' => 'Urgente',
        'color' => 'warning',
        'icono' => 'icon-warning',
        'prioridad' => 3
    ],
    'IT' => [
        'nombre' => 'Ticket IT',
        'color' => 'danger',
        'icono' => 'icon-bug',
        'prioridad' => 4
    ],
    'GENERAL' => [
        'nombre' => 'General',
        'color' => 'info',
        'icono' => 'icon-notification',
        'prioridad' => 1
    ]
]);

// Tipos de problemas IT
define('TIPOS_PROBLEMA_IT', [
    'SOFTWARE' => 'Software',
    'HARDWARE' => 'Hardware',
    'RED' => 'Red/Conectividad',
    'EMAIL' => 'Correo Electrónico',
    'SISTEMA' => 'Sistema',
    'OTRO' => 'Otro'
]);

// Prioridades para tickets IT
define('PRIORIDADES_IT', [
    'BAJA' => ['nombre' => 'Baja', 'color' => 'success', 'tiempo' => 72],
    'MEDIA' => ['nombre' => 'Media', 'color' => 'info', 'tiempo' => 48],
    'ALTA' => ['nombre' => 'Alta', 'color' => 'warning', 'tiempo' => 24],
    'CRITICA' => ['nombre' => 'Crítica', 'color' => 'danger', 'tiempo' => 4]
]);

// Configuración de notificaciones
define('CONFIG_NOTIFICACIONES', [
    'email_habilitado' => false,
    'email_smtp' => '',
    'email_puerto' => 587,
    'email_usuario' => '',
    'email_password' => '',
    'email_from' => 'noreply@empresa.com'
]);

// Configuración de la interfaz
define('CONFIG_INTERFAZ', [
    'avisos_por_pagina' => 20,
    'auto_refresh' => 30, // segundos
    'mostrar_estadisticas' => true,
    'habilitar_filtros' => true,
    'habilitar_busqueda' => false
]);

// Configuración de seguridad
define('CONFIG_SEGURIDAD', [
    'max_longitud_titulo' => 255,
    'max_longitud_mensaje' => 5000,
    'max_destinatarios' => 50,
    'requerir_confirmacion' => false,
    'log_actividad' => true
]);

// Función para obtener configuración
function getConfigAvisos($seccion = null) {
    if ($seccion === null) {
        return [
            'tipos' => TIPOS_AVISO,
            'tipos_problema_it' => TIPOS_PROBLEMA_IT,
            'prioridades_it' => PRIORIDADES_IT,
            'notificaciones' => CONFIG_NOTIFICACIONES,
            'interfaz' => CONFIG_INTERFAZ,
            'seguridad' => CONFIG_SEGURIDAD
        ];
    }
    
    switch ($seccion) {
        case 'tipos':
            return TIPOS_AVISO;
        case 'tipos_problema_it':
            return TIPOS_PROBLEMA_IT;
        case 'prioridades_it':
            return PRIORIDADES_IT;
        case 'notificaciones':
            return CONFIG_NOTIFICACIONES;
        case 'interfaz':
            return CONFIG_INTERFAZ;
        case 'seguridad':
            return CONFIG_SEGURIDAD;
        default:
            return null;
    }
}

// Función para validar tipo de aviso
function validarTipoAviso($tipo) {
    return array_key_exists($tipo, TIPOS_AVISO);
}

// Función para validar prioridad IT
function validarPrioridadIT($prioridad) {
    return array_key_exists($prioridad, PRIORIDADES_IT);
}

// Función para obtener color del tipo
function getColorTipo($tipo) {
    return TIPOS_AVISO[$tipo]['color'] ?? 'secondary';
}

// Función para obtener icono del tipo
function getIconoTipo($tipo) {
    return TIPOS_AVISO[$tipo]['icono'] ?? 'icon-notification';
}
?>
