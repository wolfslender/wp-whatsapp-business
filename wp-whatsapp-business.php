<?php
/**
 * Plugin Name: WP WhatsApp Business
 * Plugin URI: https://oliverodev.pages.com
 * Description: Plugin empresarial para integración con WhatsApp Business API
 * Version: 1.0.0
 * Author: OliveroDEv
 * Author URI: https://oliverodev.pages.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-whatsapp-business
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WPWhatsAppBusiness
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WP_WHATSAPP_BUSINESS_VERSION', '1.0.0');
define('WP_WHATSAPP_BUSINESS_PLUGIN_FILE', __FILE__);
define('WP_WHATSAPP_BUSINESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_WHATSAPP_BUSINESS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_WHATSAPP_BUSINESS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
if (file_exists(WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'vendor/autoload.php')) {
    // Usar Composer si está disponible
    require_once WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Usar autoloader simple
    require_once WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'autoload.php';
}

/**
 * Función de inicialización del plugin
 */
function wp_whatsapp_business_init() {
    // Verificar que Composer esté disponible
    if (!class_exists('WPWhatsAppBusiness\\Core\\Plugin')) {
        add_action('admin_notices', 'wp_whatsapp_business_composer_error');
        return;
    }
    
    // Inicializar el plugin
    WPWhatsAppBusiness\Core\Plugin::getInstance();
}

/**
 * Mostrar error si las clases del plugin no están disponibles
 */
function wp_whatsapp_business_composer_error() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WP WhatsApp Business no pudo cargar correctamente. Verifica que todos los archivos del plugin estén presentes.', 'wp-whatsapp-business');
    echo '</p></div>';
}

// Inicializar el plugin
add_action('plugins_loaded', 'wp_whatsapp_business_init');

/**
 * Función de activación del plugin
 */
function wp_whatsapp_business_activate() {
    // Crear tablas de base de datos si es necesario
    // Configurar opciones por defecto
    add_option('wp_whatsapp_business_version', WP_WHATSAPP_BUSINESS_VERSION);
    add_option('wp_whatsapp_business_settings', [
        'api_key' => '',
        'phone_number' => '',
        'business_name' => '',
        'enabled' => false
    ]);
    
    // Limpiar caché de rewrite rules
    flush_rewrite_rules();
}

/**
 * Función de desactivación del plugin
 */
function wp_whatsapp_business_deactivate() {
    // Limpiar caché de rewrite rules
    flush_rewrite_rules();
}

/**
 * Función de desinstalación del plugin
 */
function wp_whatsapp_business_uninstall() {
    // Eliminar opciones de la base de datos
    delete_option('wp_whatsapp_business_version');
    delete_option('wp_whatsapp_business_settings');
    
    // Eliminar tablas personalizadas si existen
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}whatsapp_messages");
}

// Registrar hooks de activación, desactivación y desinstalación
register_activation_hook(__FILE__, 'wp_whatsapp_business_activate');
register_deactivation_hook(__FILE__, 'wp_whatsapp_business_deactivate');
register_uninstall_hook(__FILE__, 'wp_whatsapp_business_uninstall'); 