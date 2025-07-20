<?php
/**
 * Autoloader simple para WP WhatsApp Business
 * 
 * @package WPWhatsAppBusiness
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader simple para las clases del plugin
 */
spl_autoload_register(function ($class) {
    // Verificar si la clase pertenece a nuestro namespace
    if (strpos($class, 'WPWhatsAppBusiness\\') !== 0) {
        return;
    }

    // Convertir namespace a ruta de archivo
    $class = str_replace('WPWhatsAppBusiness\\', '', $class);
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Construir la ruta del archivo
    $file = WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
    
    // Cargar el archivo si existe
    if (file_exists($file)) {
        require_once $file;
    }
}); 