<?php
/**
 * Interfaz de administración del plugin
 *
 * @package WPWhatsAppBusiness\Admin
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Admin;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\ConfigService;

/**
 * Clase principal de la interfaz de administración
 */
class AdminInterface {

    /**
     * Loader de hooks
     *
     * @var Loader
     */
    private $loader;

    /**
     * Servicio de configuración
     *
     * @var ConfigService
     */
    private $config_service;

    /**
     * Constructor
     *
     * @param ConfigService $config_service Servicio de configuración
     */
    public function __construct(ConfigService $config_service) {
        $this->config_service = $config_service;
        $this->loader = new Loader();
        $this->init();
    }

    /**
     * Inicializar la interfaz de administración
     *
     * @return void
     */
    private function init(): void {
        $this->registerHooks();
        $this->loader->run();
    }

    /**
     * Registrar hooks de administración
     *
     * @return void
     */
    private function registerHooks(): void {
        // Menú de administración
        $this->loader->addAction('admin_menu', $this, 'addAdminMenu');
        
        // Página de configuración
        $this->loader->addAction('admin_init', $this, 'initSettings');
        
        // Scripts y estilos de administración
        $this->loader->addAction('admin_enqueue_scripts', $this, 'enqueueAdminAssets');
        
        // Enlaces de acción en la página de plugins
        $this->loader->addFilter('plugin_action_links_' . WP_WHATSAPP_BUSINESS_PLUGIN_BASENAME, $this, 'addPluginActionLinks');
        
        // Meta boxes
        $this->loader->addAction('add_meta_boxes', $this, 'addMetaBoxes');
        
        // Guardar meta datos
        $this->loader->addAction('save_post', $this, 'saveMetaData');
        
        // Notificaciones de administración
        $this->loader->addAction('admin_notices', $this, 'displayAdminNotices');
    }

    /**
     * Agregar menú de administración
     *
     * @return void
     */
    public function addAdminMenu(): void {
        // Menú principal
        add_menu_page(
            __('WhatsApp Business', 'wp-whatsapp-business'),
            __('WhatsApp Business', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business',
            [$this, 'renderMainPage'],
            'dashicons-whatsapp',
            30
        );

        // Submenús
        add_submenu_page(
            'wp-whatsapp-business',
            __('Configuración', 'wp-whatsapp-business'),
            __('Configuración', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business-settings',
            [$this, 'renderSettingsPage']
        );

        add_submenu_page(
            'wp-whatsapp-business',
            __('Mensajes', 'wp-whatsapp-business'),
            __('Mensajes', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business-messages',
            [$this, 'renderMessagesPage']
        );

        add_submenu_page(
            'wp-whatsapp-business',
            __('Estadísticas', 'wp-whatsapp-business'),
            __('Estadísticas', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business-stats',
            [$this, 'renderStatsPage']
        );

        add_submenu_page(
            'wp-whatsapp-business',
            __('Ayuda', 'wp-whatsapp-business'),
            __('Ayuda', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business-help',
            [$this, 'renderHelpPage']
        );
    }

    /**
     * Inicializar configuración
     *
     * @return void
     */
    public function initSettings(): void {
        // Registrar configuración
        register_setting(
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_settings',
            [$this, 'sanitizeSettings']
        );

        // Sección general
        add_settings_section(
            'wp_whatsapp_business_general',
            __('Configuración General', 'wp-whatsapp-business'),
            [$this, 'renderGeneralSection'],
            'wp_whatsapp_business_settings'
        );

        // Campos de configuración
        add_settings_field(
            'api_key',
            __('API Key', 'wp-whatsapp-business'),
            [$this, 'renderApiKeyField'],
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_general'
        );

        add_settings_field(
            'phone_number',
            __('Número de Teléfono', 'wp-whatsapp-business'),
            [$this, 'renderPhoneNumberField'],
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_general'
        );

        add_settings_field(
            'business_name',
            __('Nombre del Negocio', 'wp-whatsapp-business'),
            [$this, 'renderBusinessNameField'],
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_general'
        );

        add_settings_field(
            'enabled',
            __('Habilitar Plugin', 'wp-whatsapp-business'),
            [$this, 'renderEnabledField'],
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_general'
        );
    }

    /**
     * Cargar assets de administración
     *
     * @param string $hook_suffix Hook de la página actual
     * @return void
     */
    public function enqueueAdminAssets(string $hook_suffix): void {
        // Solo cargar en páginas del plugin
        if (strpos($hook_suffix, 'wp-whatsapp-business') === false) {
            return;
        }

        wp_enqueue_style(
            'wp-whatsapp-business-admin',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WP_WHATSAPP_BUSINESS_VERSION
        );

        wp_enqueue_script(
            'wp-whatsapp-business-admin',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WP_WHATSAPP_BUSINESS_VERSION,
            true
        );

        wp_localize_script('wp-whatsapp-business-admin', 'wpWhatsAppBusiness', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_whatsapp_business_nonce'),
            'strings' => [
                'confirmDelete' => __('¿Estás seguro de que quieres eliminar este elemento?', 'wp-whatsapp-business'),
                'saving' => __('Guardando...', 'wp-whatsapp-business'),
                'saved' => __('Guardado correctamente', 'wp-whatsapp-business'),
                'error' => __('Error al guardar', 'wp-whatsapp-business')
            ]
        ]);
    }

    /**
     * Agregar enlaces de acción en la página de plugins
     *
     * @param array $links Enlaces existentes
     * @return array
     */
    public function addPluginActionLinks(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wp-whatsapp-business-settings'),
            __('Configuración', 'wp-whatsapp-business')
        );

        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Agregar meta boxes
     *
     * @return void
     */
    public function addMetaBoxes(): void {
        $post_types = ['post', 'page'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wp_whatsapp_business_meta',
                __('WhatsApp Business', 'wp-whatsapp-business'),
                [$this, 'renderMetaBox'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Guardar meta datos
     *
     * @param int $post_id ID del post
     * @return void
     */
    public function saveMetaData(int $post_id): void {
        // Verificar nonce
        if (!isset($_POST['wp_whatsapp_business_meta_nonce']) || 
            !wp_verify_nonce($_POST['wp_whatsapp_meta_nonce'], 'wp_whatsapp_business_meta')) {
            return;
        }

        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Verificar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Guardar meta datos
        if (isset($_POST['wp_whatsapp_business_enabled'])) {
            update_post_meta($post_id, '_wp_whatsapp_business_enabled', sanitize_text_field($_POST['wp_whatsapp_business_enabled']));
        }

        if (isset($_POST['wp_whatsapp_business_message'])) {
            update_post_meta($post_id, '_wp_whatsapp_business_message', sanitize_textarea_field($_POST['wp_whatsapp_business_message']));
        }
    }

    /**
     * Mostrar notificaciones de administración
     *
     * @return void
     */
    public function displayAdminNotices(): void {
        $screen = get_current_screen();
        
        if (!$screen || strpos($screen->id, 'wp-whatsapp-business') === false) {
            return;
        }

        $settings = $this->config_service->getSettings();
        
        if (empty($settings['api_key'])) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . __('WhatsApp Business: Por favor configura tu API Key en la página de configuración.', 'wp-whatsapp-business') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Renderizar página principal
     *
     * @return void
     */
    public function renderMainPage(): void {
        include WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src/Admin/views/main-page.php';
    }

    /**
     * Renderizar página de configuración
     *
     * @return void
     */
    public function renderSettingsPage(): void {
        include WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src/Admin/views/settings-page.php';
    }

    /**
     * Renderizar página de mensajes
     *
     * @return void
     */
    public function renderMessagesPage(): void {
        include WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src/Admin/views/messages-page.php';
    }

    /**
     * Renderizar página de estadísticas
     *
     * @return void
     */
    public function renderStatsPage(): void {
        include WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src/Admin/views/stats-page.php';
    }

    /**
     * Renderizar página de ayuda
     *
     * @return void
     */
    public function renderHelpPage(): void {
        include WP_WHATSAPP_BUSINESS_PLUGIN_DIR . 'src/Admin/views/help-page.php';
    }

    /**
     * Renderizar sección general
     *
     * @return void
     */
    public function renderGeneralSection(): void {
        echo '<p>' . __('Configura los parámetros básicos de tu integración con WhatsApp Business.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo API Key
     *
     * @return void
     */
    public function renderApiKeyField(): void {
        $settings = $this->config_service->getSettings();
        $value = $settings['api_key'] ?? '';
        
        echo '<input type="text" id="api_key" name="wp_whatsapp_business_settings[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa tu API Key de WhatsApp Business.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo número de teléfono
     *
     * @return void
     */
    public function renderPhoneNumberField(): void {
        $settings = $this->config_service->getSettings();
        $value = $settings['phone_number'] ?? '';
        
        echo '<input type="text" id="phone_number" name="wp_whatsapp_business_settings[phone_number]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa tu número de teléfono de WhatsApp Business (formato: +1234567890).', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo nombre del negocio
     *
     * @return void
     */
    public function renderBusinessNameField(): void {
        $settings = $this->config_service->getSettings();
        $value = $settings['business_name'] ?? '';
        
        echo '<input type="text" id="business_name" name="wp_whatsapp_business_settings[business_name]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa el nombre de tu negocio.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo habilitado
     *
     * @return void
     */
    public function renderEnabledField(): void {
        $settings = $this->config_service->getSettings();
        $value = $settings['enabled'] ?? false;
        
        echo '<input type="checkbox" id="enabled" name="wp_whatsapp_business_settings[enabled]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="enabled">' . __('Habilitar el plugin', 'wp-whatsapp-business') . '</label>';
    }

    /**
     * Renderizar meta box
     *
     * @param \WP_Post $post Post actual
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void {
        wp_nonce_field('wp_whatsapp_business_meta', 'wp_whatsapp_business_meta_nonce');
        
        $enabled = get_post_meta($post->ID, '_wp_whatsapp_business_enabled', true);
        $message = get_post_meta($post->ID, '_wp_whatsapp_business_message', true);
        
        echo '<p>';
        echo '<label for="wp_whatsapp_business_enabled">';
        echo '<input type="checkbox" id="wp_whatsapp_business_enabled" name="wp_whatsapp_business_enabled" value="1" ' . checked($enabled, '1', false) . ' />';
        echo __('Mostrar botón de WhatsApp en esta página', 'wp-whatsapp-business');
        echo '</label>';
        echo '</p>';
        
        echo '<p>';
        echo '<label for="wp_whatsapp_business_message">' . __('Mensaje personalizado:', 'wp-whatsapp-business') . '</label><br />';
        echo '<textarea id="wp_whatsapp_business_message" name="wp_whatsapp_business_message" rows="3" cols="25">' . esc_textarea($message) . '</textarea>';
        echo '</p>';
    }

    /**
     * Sanitizar configuración
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeSettings(array $input): array {
        $sanitized = [];
        
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        if (isset($input['phone_number'])) {
            $sanitized['phone_number'] = sanitize_text_field($input['phone_number']);
        }
        
        if (isset($input['business_name'])) {
            $sanitized['business_name'] = sanitize_text_field($input['business_name']);
        }
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        
        return $sanitized;
    }
} 