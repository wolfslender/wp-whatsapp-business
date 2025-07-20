<?php
/**
 * Clase principal del plugin WP WhatsApp Business
 *
 * @package WPWhatsAppBusiness\Core
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Core;

use WPWhatsAppBusiness\Admin\AdminInterface;
use WPWhatsAppBusiness\Frontend\Assets;
use WPWhatsAppBusiness\Frontend\Shortcodes;
use WPWhatsAppBusiness\Frontend\Widget;
use WPWhatsAppBusiness\Services\ConfigService;
use WPWhatsAppBusiness\Services\WhatsAppService;
use WPWhatsAppBusiness\Services\ValidationService;

/**
 * Clase principal del plugin que implementa el patrón Singleton
 */
final class Plugin {

    /**
     * Instancia única de la clase
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Container de inyección de dependencias
     *
     * @var Container
     */
    private $container;

    /**
     * Loader de hooks
     *
     * @var Loader
     */
    private $loader;

    /**
     * Constructor privado para prevenir instanciación directa
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Prevenir clonación de la instancia
     */
    private function __clone() {}

    /**
     * Prevenir deserialización de la instancia
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Obtener la instancia única del plugin
     *
     * @return Plugin
     */
    public static function getInstance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar el plugin
     *
     * @return void
     */
    private function init(): void {
        // Inicializar el container de DI
        $this->container = new Container();
        
        // Registrar servicios en el container
        $this->registerServices();
        
        // Inicializar el loader de hooks
        $this->loader = new Loader();
        
        // Cargar componentes del plugin
        $this->loadComponents();
        
        // Registrar hooks
        $this->registerHooks();
    }

    /**
     * Registrar servicios en el container
     *
     * @return void
     */
    private function registerServices(): void {
        // Servicios básicos - ValidationService primero (sin dependencias)
        $this->container->singleton(ValidationService::class, function() {
            return new ValidationService();
        });

        // ConfigService después (depende de ValidationService)
        $this->container->singleton(ConfigService::class, function() {
            return new ConfigService($this->container->get(ValidationService::class));
        });

        // WhatsAppService al final (depende de ambos)
        $this->container->singleton(WhatsAppService::class, function() {
            return new WhatsAppService(
                $this->container->get(ConfigService::class),
                $this->container->get(ValidationService::class)
            );
        });

        // Componentes del frontend
        $this->container->singleton(Assets::class, function() {
            return new Assets($this->container->get(ConfigService::class));
        });

        $this->container->singleton(Shortcodes::class, function() {
            return new Shortcodes(
                $this->container->get(WhatsAppService::class)
            );
        });

        $this->container->singleton(Widget::class, function() {
            return new Widget(
                $this->container->get(WhatsAppService::class)
            );
        });

        // Componentes del admin
        $this->container->singleton(AdminInterface::class, function() {
            return new AdminInterface(
                $this->container->get(ConfigService::class)
            );
        });
    }

    /**
     * Cargar componentes del plugin
     *
     * @return void
     */
    private function loadComponents(): void {
        // Cargar componentes del frontend
        $this->container->get(Assets::class);
        $this->container->get(Shortcodes::class);
        $this->container->get(Widget::class);

        // Cargar componentes del admin solo si estamos en el admin
        if (is_admin()) {
            $this->container->get(AdminInterface::class);
        }
    }

    /**
     * Registrar hooks del plugin
     *
     * @return void
     */
    private function registerHooks(): void {
        // Hook de inicialización
        $this->loader->addAction('init', $this, 'onInit');
        
        // Hook de activación
        $this->loader->addAction('wp_whatsapp_business_activated', $this, 'onActivation');
        
        // Hook de desactivación
        $this->loader->addAction('wp_whatsapp_business_deactivated', $this, 'onDeactivation');
    }

    /**
     * Hook de inicialización
     *
     * @return void
     */
    public function onInit(): void {
        // Cargar archivos de idioma
        load_plugin_textdomain(
            'wp-whatsapp-business',
            false,
            dirname(WP_WHATSAPP_BUSINESS_PLUGIN_BASENAME) . '/languages'
        );

        // Ejecutar acciones de inicialización
        do_action('wp_whatsapp_business_init', $this);
    }

    /**
     * Hook de activación
     *
     * @return void
     */
    public function onActivation(): void {
        // Crear tablas de base de datos si es necesario
        $this->createDatabaseTables();
        
        // Configurar opciones por defecto
        $this->setDefaultOptions();
        
        // Limpiar caché
        $this->clearCache();
    }

    /**
     * Hook de desactivación
     *
     * @return void
     */
    public function onDeactivation(): void {
        // Limpiar caché
        $this->clearCache();
    }

    /**
     * Crear tablas de base de datos
     *
     * @return void
     */
    private function createDatabaseTables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}whatsapp_messages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            phone_number varchar(20) NOT NULL,
            message text NOT NULL,
            message_type varchar(20) DEFAULT 'text',
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY phone_number (phone_number),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Configurar opciones por defecto
     *
     * @return void
     */
    private function setDefaultOptions(): void {
        $default_settings = [
            'api_key' => '',
            'phone_number' => '',
            'business_name' => '',
            'enabled' => false,
            'widget_position' => 'bottom-right',
            'widget_text' => __('¿Necesitas ayuda? ¡Chatea con nosotros!', 'wp-whatsapp-business'),
            'business_hours' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['10:00', '14:00'],
                'sunday' => ['closed']
            ]
        ];

        add_option('wp_whatsapp_business_settings', $default_settings);
    }

    /**
     * Limpiar caché
     *
     * @return void
     */
    private function clearCache(): void {
        // Limpiar caché de transients
        delete_transient('wp_whatsapp_business_config');
        
        // Limpiar caché de rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Obtener el container de DI
     *
     * @return Container
     */
    public function getContainer(): Container {
        return $this->container;
    }

    /**
     * Obtener el loader de hooks
     *
     * @return Loader
     */
    public function getLoader(): Loader {
        return $this->loader;
    }

    /**
     * Ejecutar el plugin
     *
     * @return void
     */
    public function run(): void {
        $this->loader->run();
    }
}