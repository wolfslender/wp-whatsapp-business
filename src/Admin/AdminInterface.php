<?php
/**
 * Interfaz de administración profesional del plugin
 *
 * @package WPWhatsAppBusiness\Admin
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Admin;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\ConfigService;
use WPWhatsAppBusiness\Services\ValidationService;
use WPWhatsAppBusiness\Services\WhatsAppService;

/**
 * Clase principal de la interfaz de administración profesional
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
     * Servicio de validación
     *
     * @var ValidationService
     */
    private $validation_service;

    /**
     * Servicio de WhatsApp
     *
     * @var WhatsAppService
     */
    private $whatsapp_service;

    /**
     * Páginas del plugin
     *
     * @var array
     */
    private $plugin_pages = [
        'wp-whatsapp-business',
        'wp-whatsapp-business-settings',
        'wp-whatsapp-business-messages',
        'wp-whatsapp-business-stats',
        'wp-whatsapp-business-help'
    ];

    /**
     * Constructor
     *
     * @param ConfigService $config_service Servicio de configuración
     */
    public function __construct(ConfigService $config_service) {
        $this->config_service = $config_service;
        $this->validation_service = \WPWhatsAppBusiness\Services\ServiceFactory::createValidationService();
        $this->whatsapp_service = \WPWhatsAppBusiness\Services\ServiceFactory::createWhatsAppService();
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
        
        // Configuración con WordPress Settings API
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

        // AJAX handlers
        $this->loader->addAction('wp_ajax_wp_whatsapp_validate_settings', $this, 'ajaxValidateSettings');
        $this->loader->addAction('wp_ajax_wp_whatsapp_preview_widget', $this, 'ajaxPreviewWidget');
        $this->loader->addAction('wp_ajax_wp_whatsapp_test_phone', $this, 'ajaxTestPhone');
        $this->loader->addAction('wp_ajax_wp_whatsapp_import_settings', $this, 'ajaxImportSettings');
        $this->loader->addAction('wp_ajax_wp_whatsapp_export_settings', $this, 'ajaxExportSettings');
        $this->loader->addAction('wp_ajax_wp_whatsapp_send_test_message', $this, 'ajaxSendTestMessage');
    }

    /**
     * Agregar menú de administración profesional
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

        // Submenús con iconos y descripciones
        add_submenu_page(
            'wp-whatsapp-business',
            __('Dashboard', 'wp-whatsapp-business'),
            __('Dashboard', 'wp-whatsapp-business'),
            'manage_options',
            'wp-whatsapp-business',
            [$this, 'renderMainPage']
        );

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
     * Inicializar configuración con WordPress Settings API completa
     *
     * @return void
     */
    public function initSettings(): void {
        // Registrar configuración principal
        register_setting(
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeSettings'],
                'default' => []
            ]
        );

        // Registrar configuración de widget
        register_setting(
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeWidgetSettings'],
                'default' => []
            ]
        );

        // Registrar configuración de horarios
        register_setting(
            'wp_whatsapp_business_hours',
            'wp_whatsapp_business_hours_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeBusinessHours'],
                'default' => []
            ]
        );

        // Registrar configuración de mensajes
        register_setting(
            'wp_whatsapp_business_messages',
            'wp_whatsapp_business_message_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeMessageSettings'],
                'default' => []
            ]
        );

        // Registrar configuración avanzada
        register_setting(
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_settings',
            [
                'sanitize_callback' => [$this, 'sanitizeAdvancedSettings'],
                'default' => []
            ]
        );

        // Secciones de configuración
        $this->registerGeneralSection();
        $this->registerWidgetSection();
        $this->registerBusinessHoursSection();
        $this->registerMessagesSection();
        $this->registerAdvancedSection();
    }

    /**
     * Registrar sección general
     *
     * @return void
     */
    private function registerGeneralSection(): void {
        add_settings_section(
            'wp_whatsapp_business_general',
            __('Configuración General', 'wp-whatsapp-business'),
            [$this, 'renderGeneralSection'],
            'wp_whatsapp_business_settings'
        );

        // Campos de configuración general
        add_settings_field(
            'api_key',
            __('API Key', 'wp-whatsapp-business'),
            [$this, 'renderApiKeyField'],
            'wp_whatsapp_business_settings',
            'wp_whatsapp_business_general'
        );

        add_settings_field(
            'phone_number_id',
            __('Phone Number ID', 'wp-whatsapp-business'),
            [$this, 'renderPhoneNumberIdField'],
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
     * Registrar sección de widget
     *
     * @return void
     */
    private function registerWidgetSection(): void {
        add_settings_section(
            'wp_whatsapp_business_widget',
            __('Personalización del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetSection'],
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_enabled',
            __('Habilitar Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetEnabledField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_position',
            __('Posición', 'wp-whatsapp-business'),
            [$this, 'renderWidgetPositionField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_size',
            __('Tamaño', 'wp-whatsapp-business'),
            [$this, 'renderWidgetSizeField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_color',
            __('Color', 'wp-whatsapp-business'),
            [$this, 'renderWidgetColorField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_text',
            __('Texto del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetTextField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_show_on_mobile',
            __('Mostrar en Móvil', 'wp-whatsapp-business'),
            [$this, 'renderWidgetMobileField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );

        add_settings_field(
            'widget_show_on_desktop',
            __('Mostrar en Desktop', 'wp-whatsapp-business'),
            [$this, 'renderWidgetDesktopField'],
            'wp_whatsapp_business_widget',
            'wp_whatsapp_business_widget'
        );
    }

    /**
     * Registrar sección de horarios
     *
     * @return void
     */
    private function registerBusinessHoursSection(): void {
        add_settings_section(
            'wp_whatsapp_business_hours',
            __('Horarios de Negocio', 'wp-whatsapp-business'),
            [$this, 'renderBusinessHoursSection'],
            'wp_whatsapp_business_hours'
        );

        $days = [
            'monday' => __('Lunes', 'wp-whatsapp-business'),
            'tuesday' => __('Martes', 'wp-whatsapp-business'),
            'wednesday' => __('Miércoles', 'wp-whatsapp-business'),
            'thursday' => __('Jueves', 'wp-whatsapp-business'),
            'friday' => __('Viernes', 'wp-whatsapp-business'),
            'saturday' => __('Sábado', 'wp-whatsapp-business'),
            'sunday' => __('Domingo', 'wp-whatsapp-business')
        ];

        foreach ($days as $day_key => $day_name) {
            add_settings_field(
                'business_hours_' . $day_key,
                $day_name,
                [$this, 'renderBusinessHoursField'],
                'wp_whatsapp_business_hours',
                'wp_whatsapp_business_hours',
                ['day' => $day_key, 'day_name' => $day_name]
            );
        }
    }

    /**
     * Registrar sección de mensajes
     *
     * @return void
     */
    private function registerMessagesSection(): void {
        add_settings_section(
            'wp_whatsapp_business_messages',
            __('Plantillas de Mensajes', 'wp-whatsapp-business'),
            [$this, 'renderMessagesSection'],
            'wp_whatsapp_business_messages'
        );

        add_settings_field(
            'welcome_message',
            __('Mensaje de Bienvenida', 'wp-whatsapp-business'),
            [$this, 'renderWelcomeMessageField'],
            'wp_whatsapp_business_messages',
            'wp_whatsapp_business_messages'
        );

        add_settings_field(
            'offline_message',
            __('Mensaje Fuera de Horario', 'wp-whatsapp-business'),
            [$this, 'renderOfflineMessageField'],
            'wp_whatsapp_business_messages',
            'wp_whatsapp_business_messages'
        );

        add_settings_field(
            'default_message',
            __('Mensaje por Defecto', 'wp-whatsapp-business'),
            [$this, 'renderDefaultMessageField'],
            'wp_whatsapp_business_messages',
            'wp_whatsapp_business_messages'
        );
    }

    /**
     * Registrar sección avanzada
     *
     * @return void
     */
    private function registerAdvancedSection(): void {
        add_settings_section(
            'wp_whatsapp_business_advanced',
            __('Configuración Avanzada', 'wp-whatsapp-business'),
            [$this, 'renderAdvancedSection'],
            'wp_whatsapp_business_advanced'
        );

        add_settings_field(
            'rate_limit_enabled',
            __('Habilitar Rate Limiting', 'wp-whatsapp-business'),
            [$this, 'renderRateLimitEnabledField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced'
        );

        add_settings_field(
            'rate_limit_hourly',
            __('Límite por Hora', 'wp-whatsapp-business'),
            [$this, 'renderRateLimitHourlyField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced'
        );

        add_settings_field(
            'rate_limit_daily',
            __('Límite por Día', 'wp-whatsapp-business'),
            [$this, 'renderRateLimitDailyField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced'
        );

        add_settings_field(
            'debug_mode',
            __('Modo Debug', 'wp-whatsapp-business'),
            [$this, 'renderDebugModeField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced'
        );
    }

    /**
     * Cargar assets de administración profesional
     *
     * @param string $hook_suffix Hook de la página actual
     * @return void
     */
    public function enqueueAdminAssets(string $hook_suffix): void {
        // Solo cargar en páginas del plugin
        if (!in_array($hook_suffix, $this->plugin_pages)) {
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

        $config = $this->config_service->getConfig();
        
        if (empty($config->getApiKey())) {
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
        $config = $this->config_service->getConfig();
        $value = $config->getApiKey();
        
        echo '<input type="text" id="api_key" name="wp_whatsapp_business_settings[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa tu API Key de WhatsApp Business.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo número de teléfono
     *
     * @return void
     */
    public function renderPhoneNumberField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getPhoneNumber();
        
        echo '<input type="text" id="phone_number" name="wp_whatsapp_business_settings[phone_number]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa tu número de teléfono de WhatsApp Business (formato: +1234567890).', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo nombre del negocio
     *
     * @return void
     */
    public function renderBusinessNameField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getBusinessName();
        
        echo '<input type="text" id="business_name" name="wp_whatsapp_business_settings[business_name]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa el nombre de tu negocio.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo Phone Number ID
     *
     * @return void
     */
    public function renderPhoneNumberIdField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getPhoneNumberId();
        
        echo '<input type="text" id="phone_number_id" name="wp_whatsapp_business_settings[phone_number_id]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa tu Phone Number ID de WhatsApp Business.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo habilitado
     *
     * @return void
     */
    public function renderEnabledField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->isEnabled();
        
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
        
        if (isset($input['phone_number_id'])) {
            $sanitized['phone_number_id'] = sanitize_text_field($input['phone_number_id']);
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

    /**
     * Sanitizar configuración del widget
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeWidgetSettings(array $input): array {
        $sanitized = [];
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        $sanitized['position'] = sanitize_text_field($input['position'] ?? 'bottom-right');
        $sanitized['size'] = sanitize_text_field($input['size'] ?? 'medium');
        $sanitized['color'] = sanitize_hex_color($input['color'] ?? '#25D366');
        $sanitized['text'] = sanitize_text_field($input['text'] ?? '');
        $sanitized['show_on_mobile'] = isset($input['show_on_mobile']) ? true : false;
        $sanitized['show_on_desktop'] = isset($input['show_on_desktop']) ? true : false;
        
        return $sanitized;
    }

    /**
     * Sanitizar horarios de negocio
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeBusinessHours(array $input): array {
        $sanitized = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            if (isset($input[$day])) {
                $sanitized[$day] = [
                    'enabled' => isset($input[$day]['enabled']) ? true : false,
                    'open' => sanitize_text_field($input[$day]['open'] ?? '09:00'),
                    'close' => sanitize_text_field($input[$day]['close'] ?? '18:00')
                ];
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitizar configuración de mensajes
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeMessageSettings(array $input): array {
        $sanitized = [];
        
        $sanitized['welcome_message'] = wp_kses_post($input['welcome_message'] ?? '');
        $sanitized['offline_message'] = wp_kses_post($input['offline_message'] ?? '');
        $sanitized['default_message'] = wp_kses_post($input['default_message'] ?? '');
        
        return $sanitized;
    }

    /**
     * Sanitizar configuración avanzada
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeAdvancedSettings(array $input): array {
        $sanitized = [];
        
        $sanitized['rate_limit_enabled'] = isset($input['rate_limit_enabled']) ? true : false;
        $sanitized['rate_limit_hourly'] = absint($input['rate_limit_hourly'] ?? 100);
        $sanitized['rate_limit_daily'] = absint($input['rate_limit_daily'] ?? 1000);
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? true : false;
        
        return $sanitized;
    }

    // Métodos de renderizado para las secciones adicionales
    public function renderWidgetSection(): void {
        echo '<p>' . __('Personaliza la apariencia y comportamiento del widget de WhatsApp.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderWidgetEnabledField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['enabled'] ?? true;
        
        echo '<input type="checkbox" id="widget_enabled" name="wp_whatsapp_business_widget_settings[enabled]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="widget_enabled">' . __('Habilitar widget flotante', 'wp-whatsapp-business') . '</label>';
    }

    public function renderWidgetPositionField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['position'] ?? 'bottom-right';
        
        echo '<select id="widget_position" name="wp_whatsapp_business_widget_settings[position]">';
        echo '<option value="bottom-right" ' . selected($value, 'bottom-right', false) . '>' . __('Inferior Derecha', 'wp-whatsapp-business') . '</option>';
        echo '<option value="bottom-left" ' . selected($value, 'bottom-left', false) . '>' . __('Inferior Izquierda', 'wp-whatsapp-business') . '</option>';
        echo '<option value="top-right" ' . selected($value, 'top-right', false) . '>' . __('Superior Derecha', 'wp-whatsapp-business') . '</option>';
        echo '<option value="top-left" ' . selected($value, 'top-left', false) . '>' . __('Superior Izquierda', 'wp-whatsapp-business') . '</option>';
        echo '</select>';
    }

    public function renderWidgetSizeField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['size'] ?? 'medium';
        
        echo '<select id="widget_size" name="wp_whatsapp_business_widget_settings[size]">';
        echo '<option value="small" ' . selected($value, 'small', false) . '>' . __('Pequeño', 'wp-whatsapp-business') . '</option>';
        echo '<option value="medium" ' . selected($value, 'medium', false) . '>' . __('Mediano', 'wp-whatsapp-business') . '</option>';
        echo '<option value="large" ' . selected($value, 'large', false) . '>' . __('Grande', 'wp-whatsapp-business') . '</option>';
        echo '</select>';
    }

    public function renderWidgetColorField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['color'] ?? '#25D366';
        
        echo '<input type="text" id="widget_color" name="wp_whatsapp_business_widget_settings[color]" value="' . esc_attr($value) . '" class="wp-whatsapp-color-picker" />';
    }

    public function renderWidgetTextField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['text'] ?? '';
        
        echo '<input type="text" id="widget_text" name="wp_whatsapp_business_widget_settings[text]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Texto que aparecerá en el widget.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderWidgetMobileField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['show_on_mobile'] ?? true;
        
        echo '<input type="checkbox" id="widget_show_on_mobile" name="wp_whatsapp_business_widget_settings[show_on_mobile]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="widget_show_on_mobile">' . __('Mostrar en dispositivos móviles', 'wp-whatsapp-business') . '</label>';
    }

    public function renderWidgetDesktopField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getWidgetSettings()['show_on_desktop'] ?? true;
        
        echo '<input type="checkbox" id="widget_show_on_desktop" name="wp_whatsapp_business_widget_settings[show_on_desktop]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="widget_show_on_desktop">' . __('Mostrar en dispositivos de escritorio', 'wp-whatsapp-business') . '</label>';
    }

    public function renderBusinessHoursSection(): void {
        echo '<p>' . __('Configura los horarios de tu negocio para que el widget solo aparezca cuando estés disponible.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderBusinessHoursField($args): void {
        $day = $args['day'];
        $day_name = $args['day_name'];
        $config = $this->config_service->getConfig();
        $hours = $config->getBusinessHours()[$day] ?? ['enabled' => true, 'open' => '09:00', 'close' => '18:00'];
        
        echo '<div class="business-hour-item">';
        echo '<h4>' . esc_html($day_name) . '</h4>';
        echo '<div class="business-hour-fields">';
        echo '<input type="time" name="wp_whatsapp_business_hours_settings[' . $day . '][open]" value="' . esc_attr($hours['open']) . '" />';
        echo '<span class="time-separator">-</span>';
        echo '<input type="time" name="wp_whatsapp_business_hours_settings[' . $day . '][close]" value="' . esc_attr($hours['close']) . '" />';
        echo '<input type="checkbox" name="wp_whatsapp_business_hours_settings[' . $day . '][enabled]" value="1" ' . checked($hours['enabled'], true, false) . ' />';
        echo '<label>' . __('Habilitado', 'wp-whatsapp-business') . '</label>';
        echo '</div>';
        echo '</div>';
    }

    public function renderMessagesSection(): void {
        echo '<p>' . __('Configura las plantillas de mensajes que se enviarán automáticamente.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderWelcomeMessageField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getMessageTemplates()['welcome'] ?? '';
        
        echo '<textarea id="welcome_message" name="wp_whatsapp_business_message_settings[welcome_message]" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Mensaje que se enviará cuando un cliente inicie una conversación.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderOfflineMessageField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getMessageTemplates()['offline'] ?? '';
        
        echo '<textarea id="offline_message" name="wp_whatsapp_business_message_settings[offline_message]" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Mensaje que se mostrará cuando el negocio esté cerrado.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderDefaultMessageField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getMessageTemplates()['default'] ?? '';
        
        echo '<textarea id="default_message" name="wp_whatsapp_business_message_settings[default_message]" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Mensaje por defecto que se enviará cuando no se especifique uno.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderAdvancedSection(): void {
        echo '<p>' . __('Configuraciones avanzadas para desarrolladores y administradores.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderRateLimitEnabledField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getRateLimitSettings()['enabled'] ?? false;
        
        echo '<input type="checkbox" id="rate_limit_enabled" name="wp_whatsapp_business_advanced_settings[rate_limit_enabled]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="rate_limit_enabled">' . __('Habilitar rate limiting', 'wp-whatsapp-business') . '</label>';
    }

    public function renderRateLimitHourlyField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getRateLimitSettings()['max_requests_per_hour'] ?? 100;
        
        echo '<input type="number" id="rate_limit_hourly" name="wp_whatsapp_business_advanced_settings[rate_limit_hourly]" value="' . esc_attr($value) . '" min="1" max="1000" />';
        echo '<p class="description">' . __('Máximo número de mensajes por hora.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderRateLimitDailyField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getRateLimitSettings()['max_requests_per_day'] ?? 1000;
        
        echo '<input type="number" id="rate_limit_daily" name="wp_whatsapp_business_advanced_settings[rate_limit_daily]" value="' . esc_attr($value) . '" min="1" max="10000" />';
        echo '<p class="description">' . __('Máximo número de mensajes por día.', 'wp-whatsapp-business') . '</p>';
    }

    public function renderDebugModeField(): void {
        $config = $this->config_service->getConfig();
        $value = $config->getRateLimitSettings()['debug_mode'] ?? false;
        
        echo '<input type="checkbox" id="debug_mode" name="wp_whatsapp_business_advanced_settings[debug_mode]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label for="debug_mode">' . __('Habilitar modo debug', 'wp-whatsapp-business') . '</label>';
        echo '<p class="description">' . __('Activa el modo debug para obtener información detallada de errores.', 'wp-whatsapp-business') . '</p>';
    }

    // AJAX handlers
    public function ajaxValidateSettings(): void {
        // Implementar validación AJAX
        wp_die();
    }

    public function ajaxPreviewWidget(): void {
        // Implementar preview AJAX
        wp_die();
    }

    public function ajaxTestPhone(): void {
        // Implementar testing AJAX
        wp_die();
    }

    public function ajaxImportSettings(): void {
        // Implementar import AJAX
        wp_die();
    }

    public function ajaxExportSettings(): void {
        // Implementar export AJAX
        wp_die();
    }

    public function ajaxSendTestMessage(): void {
        // Implementar envío de mensaje de prueba AJAX
        wp_die();
    }
} 