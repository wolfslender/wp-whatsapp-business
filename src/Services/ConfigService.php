<?php
/**
 * Servicio de configuración
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

/**
 * Clase para gestionar la configuración del plugin
 */
class ConfigService {

    /**
     * Nombre de la opción en la base de datos
     *
     * @var string
     */
    private const OPTION_NAME = 'wp_whatsapp_business_settings';

    /**
     * Configuración por defecto
     *
     * @var array
     */
    private const DEFAULT_SETTINGS = [
        'api_key' => '',
        'phone_number' => '',
        'business_name' => '',
        'enabled' => false,
        'widget_position' => 'bottom-right',
        'widget_text' => '¿Necesitas ayuda? ¡Chatea con nosotros!',
        'business_hours' => [
            'monday' => ['09:00', '18:00'],
            'tuesday' => ['09:00', '18:00'],
            'wednesday' => ['09:00', '18:00'],
            'thursday' => ['09:00', '18:00'],
            'friday' => ['09:00', '18:00'],
            'saturday' => ['10:00', '14:00'],
            'sunday' => 'closed'
        ],
        'notification_settings' => [
            'email_notifications' => false,
            'admin_email' => '',
            'notification_events' => [
                'new_message' => true,
                'message_delivered' => false,
                'message_read' => false
            ]
        ],
        'appearance' => [
            'widget_color' => '#25D366',
            'widget_text_color' => '#ffffff',
            'widget_size' => 'medium',
            'show_business_name' => true,
            'show_message_preview' => true
        ],
        'advanced' => [
            'debug_mode' => false,
            'log_messages' => true,
            'rate_limit' => 100,
            'cache_duration' => 3600
        ]
    ];

    /**
     * Configuración actual
     *
     * @var array|null
     */
    private $settings = null;

    /**
     * Obtener toda la configuración
     *
     * @return array
     */
    public function getSettings(): array {
        if ($this->settings === null) {
            $this->settings = $this->loadSettings();
        }

        return $this->settings;
    }

    /**
     * Obtener un valor específico de configuración
     *
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $settings = $this->getSettings();
        
        return $this->getNestedValue($settings, $key, $default);
    }

    /**
     * Establecer un valor de configuración
     *
     * @param string $key Clave de configuración
     * @param mixed $value Valor a establecer
     * @return bool
     */
    public function set(string $key, $value): bool {
        $settings = $this->getSettings();
        
        $this->setNestedValue($settings, $key, $value);
        
        return $this->saveSettings($settings);
    }

    /**
     * Actualizar múltiples valores de configuración
     *
     * @param array $settings Configuración a actualizar
     * @return bool
     */
    public function updateSettings(array $settings): bool {
        $current_settings = $this->getSettings();
        $updated_settings = array_merge($current_settings, $settings);
        
        return $this->saveSettings($updated_settings);
    }

    /**
     * Restablecer configuración por defecto
     *
     * @return bool
     */
    public function resetToDefaults(): bool {
        $this->settings = self::DEFAULT_SETTINGS;
        return $this->saveSettings($this->settings);
    }

    /**
     * Verificar si una configuración existe
     *
     * @param string $key Clave de configuración
     * @return bool
     */
    public function has(string $key): bool {
        $settings = $this->getSettings();
        return $this->hasNestedKey($settings, $key);
    }

    /**
     * Eliminar una configuración
     *
     * @param string $key Clave de configuración
     * @return bool
     */
    public function delete(string $key): bool {
        $settings = $this->getSettings();
        
        if ($this->deleteNestedKey($settings, $key)) {
            return $this->saveSettings($settings);
        }
        
        return false;
    }

    /**
     * Obtener configuración de horarios de negocio
     *
     * @return array
     */
    public function getBusinessHours(): array {
        return $this->get('business_hours', self::DEFAULT_SETTINGS['business_hours']);
    }

    /**
     * Verificar si el negocio está abierto
     *
     * @return bool
     */
    public function isBusinessOpen(): bool {
        $business_hours = $this->getBusinessHours();
        
        if (empty($business_hours)) {
            return true; // Si no hay horarios configurados, considerar abierto
        }

        $current_time = current_time('timestamp');
        $current_day = strtolower(date('l', $current_time));
        $current_hour = date('H:i', $current_time);

        if (!isset($business_hours[$current_day])) {
            return false;
        }

        $day_hours = $business_hours[$current_day];

        // Si está cerrado
        if ($day_hours === 'closed' || empty($day_hours)) {
            return false;
        }

        // Si solo hay un horario (abierto todo el día)
        if (count($day_hours) === 1) {
            return true;
        }

        // Verificar si estamos dentro del horario
        if (count($day_hours) >= 2) {
            $open_time = $day_hours[0];
            $close_time = $day_hours[1];

            return $current_hour >= $open_time && $current_hour <= $close_time;
        }

        return true;
    }

    /**
     * Obtener configuración de notificaciones
     *
     * @return array
     */
    public function getNotificationSettings(): array {
        return $this->get('notification_settings', self::DEFAULT_SETTINGS['notification_settings']);
    }

    /**
     * Obtener configuración de apariencia
     *
     * @return array
     */
    public function getAppearanceSettings(): array {
        return $this->get('appearance', self::DEFAULT_SETTINGS['appearance']);
    }

    /**
     * Obtener configuración avanzada
     *
     * @return array
     */
    public function getAdvancedSettings(): array {
        return $this->get('advanced', self::DEFAULT_SETTINGS['advanced']);
    }

    /**
     * Verificar si el modo debug está habilitado
     *
     * @return bool
     */
    public function isDebugMode(): bool {
        return $this->get('advanced.debug_mode', false);
    }

    /**
     * Verificar si el logging está habilitado
     *
     * @return bool
     */
    public function isLoggingEnabled(): bool {
        return $this->get('advanced.log_messages', true);
    }

    /**
     * Obtener límite de tasa
     *
     * @return int
     */
    public function getRateLimit(): int {
        return $this->get('advanced.rate_limit', 100);
    }

    /**
     * Obtener duración del caché
     *
     * @return int
     */
    public function getCacheDuration(): int {
        return $this->get('advanced.cache_duration', 3600);
    }

    /**
     * Cargar configuración desde la base de datos
     *
     * @return array
     */
    private function loadSettings(): array {
        $settings = get_option(self::OPTION_NAME, []);
        
        // Combinar con configuración por defecto
        return array_merge(self::DEFAULT_SETTINGS, $settings);
    }

    /**
     * Guardar configuración en la base de datos
     *
     * @param array $settings Configuración a guardar
     * @return bool
     */
    private function saveSettings(array $settings): bool {
        $this->settings = $settings;
        
        // Limpiar caché de transients
        delete_transient('wp_whatsapp_business_config');
        
        return update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Obtener valor anidado de un array
     *
     * @param array $array Array
     * @param string $key Clave (puede ser anidada con puntos)
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    private function getNestedValue(array $array, string $key, $default = null) {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    /**
     * Establecer valor anidado en un array
     *
     * @param array $array Array de referencia
     * @param string $key Clave (puede ser anidada con puntos)
     * @param mixed $value Valor a establecer
     * @return void
     */
    private function setNestedValue(array &$array, string $key, $value): void {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Verificar si existe una clave anidada
     *
     * @param array $array Array
     * @param string $key Clave (puede ser anidada con puntos)
     * @return bool
     */
    private function hasNestedKey(array $array, string $key): bool {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    /**
     * Eliminar una clave anidada
     *
     * @param array $array Array de referencia
     * @param string $key Clave (puede ser anidada con puntos)
     * @return bool
     */
    private function deleteNestedKey(array &$array, string $key): bool {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = &$current[$k];
        }

        unset($current);
        return true;
    }

    /**
     * Exportar configuración
     *
     * @return array
     */
    public function export(): array {
        return $this->getSettings();
    }

    /**
     * Importar configuración
     *
     * @param array $settings Configuración a importar
     * @return bool
     */
    public function import(array $settings): bool {
        // Validar estructura básica
        if (!is_array($settings)) {
            return false;
        }

        // Combinar con configuración por defecto para asegurar estructura completa
        $merged_settings = array_merge(self::DEFAULT_SETTINGS, $settings);
        
        return $this->saveSettings($merged_settings);
    }

    /**
     * Obtener configuración para JavaScript
     *
     * @return array
     */
    public function getJsConfig(): array {
        $settings = $this->getSettings();
        
        // Solo incluir configuraciones seguras para el frontend
        return [
            'enabled' => $settings['enabled'] ?? false,
            'phone_number' => $settings['phone_number'] ?? '',
            'business_name' => $settings['business_name'] ?? '',
            'widget_position' => $settings['widget_position'] ?? 'bottom-right',
            'widget_text' => $settings['widget_text'] ?? '',
            'appearance' => $settings['appearance'] ?? [],
            'business_hours' => $settings['business_hours'] ?? []
        ];
    }
} 