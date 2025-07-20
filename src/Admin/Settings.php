<?php
/**
 * Configuración avanzada del plugin
 *
 * @package WPWhatsAppBusiness\Admin
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Admin;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\ConfigService;
use WPWhatsAppBusiness\Services\ValidationService;

/**
 * Clase para manejar la configuración avanzada
 */
class Settings {

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
     * Constructor
     *
     * @param ConfigService $config_service Servicio de configuración
     * @param ValidationService $validation_service Servicio de validación
     */
    public function __construct(ConfigService $config_service, ValidationService $validation_service) {
        $this->config_service = $config_service;
        $this->validation_service = $validation_service;
        $this->loader = new Loader();
        $this->init();
    }

    /**
     * Inicializar configuración
     *
     * @return void
     */
    private function init(): void {
        $this->registerHooks();
        $this->loader->run();
    }

    /**
     * Registrar hooks de configuración
     *
     * @return void
     */
    private function registerHooks(): void {
        // Inicializar configuración
        $this->loader->addAction('admin_init', $this, 'initSettings');
        
        // Guardar configuración
        $this->loader->addAction('admin_post_save_whatsapp_settings', $this, 'saveSettings');
        
        // Validar configuración
        $this->loader->addAction('admin_notices', $this, 'displayValidationErrors');
        
        // Exportar/Importar configuración
        $this->loader->addAction('admin_post_export_whatsapp_settings', $this, 'exportSettings');
        $this->loader->addAction('admin_post_import_whatsapp_settings', $this, 'importSettings');
    }

    /**
     * Inicializar configuración
     *
     * @return void
     */
    public function initSettings(): void {
        // Registrar configuración general
        register_setting(
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_settings',
            [$this, 'sanitizeGeneralSettings']
        );

        // Registrar configuración de notificaciones
        register_setting(
            'wp_whatsapp_business_notifications',
            'wp_whatsapp_business_notification_settings',
            [$this, 'sanitizeNotificationSettings']
        );

        // Registrar configuración de apariencia
        register_setting(
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_settings',
            [$this, 'sanitizeAppearanceSettings']
        );

        // Registrar configuración avanzada
        register_setting(
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_settings',
            [$this, 'sanitizeAdvancedSettings']
        );

        // Sección general
        add_settings_section(
            'wp_whatsapp_business_general_section',
            __('Configuración General', 'wp-whatsapp-business'),
            [$this, 'renderGeneralSection'],
            'wp_whatsapp_business_general'
        );

        // Sección de notificaciones
        add_settings_section(
            'wp_whatsapp_business_notifications_section',
            __('Configuración de Notificaciones', 'wp-whatsapp-business'),
            [$this, 'renderNotificationsSection'],
            'wp_whatsapp_business_notifications'
        );

        // Sección de apariencia
        add_settings_section(
            'wp_whatsapp_business_appearance_section',
            __('Configuración de Apariencia', 'wp-whatsapp-business'),
            [$this, 'renderAppearanceSection'],
            'wp_whatsapp_business_appearance'
        );

        // Sección avanzada
        add_settings_section(
            'wp_whatsapp_business_advanced_section',
            __('Configuración Avanzada', 'wp-whatsapp-business'),
            [$this, 'renderAdvancedSection'],
            'wp_whatsapp_business_advanced'
        );

        // Campos de configuración general
        $this->addGeneralFields();
        
        // Campos de configuración de notificaciones
        $this->addNotificationFields();
        
        // Campos de configuración de apariencia
        $this->addAppearanceFields();
        
        // Campos de configuración avanzada
        $this->addAdvancedFields();
    }

    /**
     * Agregar campos de configuración general
     *
     * @return void
     */
    private function addGeneralFields(): void {
        // API Key
        add_settings_field(
            'api_key',
            __('API Key de WhatsApp Business', 'wp-whatsapp-business'),
            [$this, 'renderApiKeyField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );

        // Phone Number ID
        add_settings_field(
            'phone_number_id',
            __('Phone Number ID', 'wp-whatsapp-business'),
            [$this, 'renderPhoneNumberIdField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );

        // Número de teléfono
        add_settings_field(
            'phone_number',
            __('Número de Teléfono', 'wp-whatsapp-business'),
            [$this, 'renderPhoneNumberField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );

        // Nombre del negocio
        add_settings_field(
            'business_name',
            __('Nombre del Negocio', 'wp-whatsapp-business'),
            [$this, 'renderBusinessNameField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );

        // Habilitar plugin
        add_settings_field(
            'enabled',
            __('Habilitar Plugin', 'wp-whatsapp-business'),
            [$this, 'renderEnabledField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );

        // Horarios de negocio
        add_settings_field(
            'business_hours',
            __('Horarios de Negocio', 'wp-whatsapp-business'),
            [$this, 'renderBusinessHoursField'],
            'wp_whatsapp_business_general',
            'wp_whatsapp_business_general_section'
        );
    }

    /**
     * Agregar campos de configuración de notificaciones
     *
     * @return void
     */
    private function addNotificationFields(): void {
        // Habilitar notificaciones por email
        add_settings_field(
            'email_notifications',
            __('Notificaciones por Email', 'wp-whatsapp-business'),
            [$this, 'renderEmailNotificationsField'],
            'wp_whatsapp_business_notifications',
            'wp_whatsapp_business_notifications_section'
        );

        // Email del administrador
        add_settings_field(
            'admin_email',
            __('Email del Administrador', 'wp-whatsapp-business'),
            [$this, 'renderAdminEmailField'],
            'wp_whatsapp_business_notifications',
            'wp_whatsapp_business_notifications_section'
        );

        // Eventos de notificación
        add_settings_field(
            'notification_events',
            __('Eventos de Notificación', 'wp-whatsapp-business'),
            [$this, 'renderNotificationEventsField'],
            'wp_whatsapp_business_notifications',
            'wp_whatsapp_business_notifications_section'
        );
    }

    /**
     * Agregar campos de configuración de apariencia
     *
     * @return void
     */
    private function addAppearanceFields(): void {
        // Color del widget
        add_settings_field(
            'widget_color',
            __('Color del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetColorField'],
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_section'
        );

        // Color del texto del widget
        add_settings_field(
            'widget_text_color',
            __('Color del Texto del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetTextColorField'],
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_section'
        );

        // Tamaño del widget
        add_settings_field(
            'widget_size',
            __('Tamaño del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetSizeField'],
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_section'
        );

        // Posición del widget
        add_settings_field(
            'widget_position',
            __('Posición del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetPositionField'],
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_section'
        );

        // Texto del widget
        add_settings_field(
            'widget_text',
            __('Texto del Widget', 'wp-whatsapp-business'),
            [$this, 'renderWidgetTextField'],
            'wp_whatsapp_business_appearance',
            'wp_whatsapp_business_appearance_section'
        );
    }

    /**
     * Agregar campos de configuración avanzada
     *
     * @return void
     */
    private function addAdvancedFields(): void {
        // Modo debug
        add_settings_field(
            'debug_mode',
            __('Modo Debug', 'wp-whatsapp-business'),
            [$this, 'renderDebugModeField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_section'
        );

        // Logging de mensajes
        add_settings_field(
            'log_messages',
            __('Registrar Mensajes', 'wp-whatsapp-business'),
            [$this, 'renderLogMessagesField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_section'
        );

        // Límite de tasa
        add_settings_field(
            'rate_limit',
            __('Límite de Tasa', 'wp-whatsapp-business'),
            [$this, 'renderRateLimitField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_section'
        );

        // Duración del caché
        add_settings_field(
            'cache_duration',
            __('Duración del Caché (segundos)', 'wp-whatsapp-business'),
            [$this, 'renderCacheDurationField'],
            'wp_whatsapp_business_advanced',
            'wp_whatsapp_business_advanced_section'
        );
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
     * Renderizar sección de notificaciones
     *
     * @return void
     */
    public function renderNotificationsSection(): void {
        echo '<p>' . __('Configura las notificaciones que recibirás cuando se envíen mensajes.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar sección de apariencia
     *
     * @return void
     */
    public function renderAppearanceSection(): void {
        echo '<p>' . __('Personaliza la apariencia del widget y los elementos del frontend.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar sección avanzada
     *
     * @return void
     */
    public function renderAdvancedSection(): void {
        echo '<p>' . __('Configuraciones avanzadas para desarrolladores y administradores.', 'wp-whatsapp-business') . '</p>';
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
        echo '<p class="description">' . __('Ingresa tu API Key de WhatsApp Business. Puedes obtenerla desde el Facebook Developer Console.', 'wp-whatsapp-business') . '</p>';
    }

    /**
     * Renderizar campo Phone Number ID
     *
     * @return void
     */
    public function renderPhoneNumberIdField(): void {
        $settings = $this->config_service->getSettings();
        $value = $settings['phone_number_id'] ?? '';
        
        echo '<input type="text" id="phone_number_id" name="wp_whatsapp_business_settings[phone_number_id]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Ingresa el Phone Number ID de tu número de WhatsApp Business.', 'wp-whatsapp-business') . '</p>';
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
        echo '<p class="description">' . __('Ingresa el nombre de tu negocio que aparecerá en el widget.', 'wp-whatsapp-business') . '</p>';
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
     * Renderizar campo horarios de negocio
     *
     * @return void
     */
    public function renderBusinessHoursField(): void {
        $settings = $this->config_service->getSettings();
        $business_hours = $settings['business_hours'] ?? [];
        
        $days = [
            'monday' => __('Lunes', 'wp-whatsapp-business'),
            'tuesday' => __('Martes', 'wp-whatsapp-business'),
            'wednesday' => __('Miércoles', 'wp-whatsapp-business'),
            'thursday' => __('Jueves', 'wp-whatsapp-business'),
            'friday' => __('Viernes', 'wp-whatsapp-business'),
            'saturday' => __('Sábado', 'wp-whatsapp-business'),
            'sunday' => __('Domingo', 'wp-whatsapp-business')
        ];

        echo '<div class="business-hours-container">';
        foreach ($days as $day_key => $day_name) {
            $hours = $business_hours[$day_key] ?? ['09:00', '18:00'];
            $is_closed = ($hours === 'closed');
            
            echo '<div class="business-hour-row">';
            echo '<label>' . esc_html($day_name) . ':</label>';
            
            if ($is_closed) {
                echo '<input type="checkbox" name="wp_whatsapp_business_settings[business_hours][' . $day_key . ']" value="closed" checked /> ';
                echo '<span>' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
            } else {
                echo '<input type="time" name="wp_whatsapp_business_settings[business_hours][' . $day_key . '][]" value="' . esc_attr($hours[0]) . '" /> - ';
                echo '<input type="time" name="wp_whatsapp_business_settings[business_hours][' . $day_key . '][]" value="' . esc_attr($hours[1]) . '" /> ';
                echo '<input type="checkbox" name="wp_whatsapp_business_settings[business_hours][' . $day_key . ']" value="closed" /> ';
                echo '<span>' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
            }
            
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Sanitizar configuración general
     *
     * @param array $input Datos de entrada
     * @return array
     */
    public function sanitizeGeneralSettings(array $input): array {
        $sanitized = [];
        
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        if (isset($input['phone_number_id'])) {
            $sanitized['phone_number_id'] = sanitize_text_field($input['phone_number_id']);
        }
        
        if (isset($input['phone_number'])) {
            $sanitized['phone_number'] = $this->validation_service->sanitizePhoneNumber($input['phone_number']);
        }
        
        if (isset($input['business_name'])) {
            $sanitized['business_name'] = sanitize_text_field($input['business_name']);
        }
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        
        if (isset($input['business_hours'])) {
            $sanitized['business_hours'] = $this->sanitizeBusinessHours($input['business_hours']);
        }
        
        return $sanitized;
    }

    /**
     * Sanitizar horarios de negocio
     *
     * @param array $business_hours Horarios a sanitizar
     * @return array
     */
    private function sanitizeBusinessHours(array $business_hours): array {
        $sanitized = [];
        $valid_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($business_hours as $day => $hours) {
            if (!in_array($day, $valid_days)) {
                continue;
            }

            if ($hours === 'closed') {
                $sanitized[$day] = 'closed';
                continue;
            }

            if (is_array($hours) && count($hours) >= 2) {
                $open_time = sanitize_text_field($hours[0]);
                $close_time = sanitize_text_field($hours[1]);
                
                if ($this->validation_service->isValidTimeFormat($open_time) && 
                    $this->validation_service->isValidTimeFormat($close_time)) {
                    $sanitized[$day] = [$open_time, $close_time];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Guardar configuración
     *
     * @return void
     */
    public function saveSettings(): void {
        // Verificar nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wp_whatsapp_business_settings')) {
            wp_die(__('Error de seguridad', 'wp-whatsapp-business'));
        }

        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wp-whatsapp-business'));
        }

        // Obtener datos del formulario
        $settings = $_POST['wp_whatsapp_business_settings'] ?? [];

        // Validar configuración
        $errors = $this->validation_service->validateSettings($settings);
        
        if (!empty($errors)) {
            // Guardar errores en transient
            set_transient('wp_whatsapp_business_errors', $errors, 30);
            wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&error=validation'));
            exit;
        }

        // Sanitizar y guardar configuración
        $sanitized_settings = $this->validation_service->sanitizeSettings($settings);
        $this->config_service->updateSettings($sanitized_settings);

        // Redireccionar con mensaje de éxito
        wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&updated=true'));
        exit;
    }

    /**
     * Mostrar errores de validación
     *
     * @return void
     */
    public function displayValidationErrors(): void {
        $errors = get_transient('wp_whatsapp_business_errors');
        
        if ($errors) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . __('Errores de validación:', 'wp-whatsapp-business') . '</strong></p>';
            echo '<ul>';
            foreach ($errors as $field => $error) {
                if (is_array($error)) {
                    foreach ($error as $sub_error) {
                        echo '<li>' . esc_html($sub_error) . '</li>';
                    }
                } else {
                    echo '<li>' . esc_html($error) . '</li>';
                }
            }
            echo '</ul>';
            echo '</div>';
            
            delete_transient('wp_whatsapp_business_errors');
        }
    }

    /**
     * Exportar configuración
     *
     * @return void
     */
    public function exportSettings(): void {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wp-whatsapp-business'));
        }

        $settings = $this->config_service->export();
        
        // Generar archivo JSON
        $filename = 'wp-whatsapp-business-settings-' . date('Y-m-d-H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen(json_encode($settings)));
        
        echo json_encode($settings, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Importar configuración
     *
     * @return void
     */
    public function importSettings(): void {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wp-whatsapp-business'));
        }

        // Verificar si se subió un archivo
        if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&error=upload'));
            exit;
        }

        $file_content = file_get_contents($_FILES['settings_file']['tmp_name']);
        $settings = json_decode($file_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&error=invalid_json'));
            exit;
        }

        // Importar configuración
        $success = $this->config_service->import($settings);
        
        if ($success) {
            wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&imported=true'));
        } else {
            wp_redirect(admin_url('admin.php?page=wp-whatsapp-business-settings&error=import'));
        }
        exit;
    }

    /**
     * Obtener el loader de hooks
     *
     * @return Loader
     */
    public function getLoader(): Loader {
        return $this->loader;
    }
} 