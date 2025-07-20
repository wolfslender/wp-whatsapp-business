<?php
/**
 * Servicio de configuración
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

use WPWhatsAppBusiness\Services\Interfaces\ConfigServiceInterface;
use WPWhatsAppBusiness\Services\Interfaces\ValidationServiceInterface;
use WPWhatsAppBusiness\Services\Config\WhatsAppConfig;

/**
 * Servicio de configuración centralizada
 */
class ConfigService implements ConfigServiceInterface
{
    private const OPTION_PREFIX = 'wp_whatsapp_business_';
    private const CACHE_PREFIX = 'wp_whatsapp_config_';
    private const CACHE_EXPIRATION = 3600; // 1 hora

    private ValidationServiceInterface $validationService;
    private array $cache = [];
    private array $errors = [];

    public function __construct(ValidationServiceInterface $validationService)
    {
        $this->validationService = $validationService;
    }

    public function get(string $key, $default = null)
    {
        $cacheKey = $this->getCacheKey($key);
        
        // Verificar cache en memoria
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Verificar cache de WordPress
        $cached = get_transient($cacheKey);
        if ($cached !== false) {
            $this->cache[$cacheKey] = $cached;
            return $cached;
        }

        // Obtener de la base de datos
        $value = get_option($this->getOptionKey($key), $default);
        
        // Guardar en cache
        $this->setCache($cacheKey, $value);
        
        return $value;
    }

    public function set(string $key, $value): bool
    {
        try {
            // Validar valor antes de guardar
            $validationResult = $this->validationService->validateConfig([$key => $value]);
            if (!empty($validationResult)) {
                $this->errors = array_merge($this->errors, $validationResult);
                return false;
            }

            $optionKey = $this->getOptionKey($key);
            $cacheKey = $this->getCacheKey($key);
            
            // Guardar en la base de datos
            $result = update_option($optionKey, $value);
            
            if ($result) {
                // Actualizar cache
                $this->setCache($cacheKey, $value);
                
                // Limpiar cache de configuración completa
                $this->clearConfigCache();
                
                do_action('wp_whatsapp_business_config_updated', $key, $value);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError('Error al guardar configuración: ' . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'all';
        
        // Verificar cache
        $cached = get_transient($cacheKey);
        if ($cached !== false) {
            return $cached;
        }

        $config = [
            'api_key' => $this->get('api_key', ''),
            'phone_number_id' => $this->get('phone_number_id', ''),
            'phone_number' => $this->get('phone_number', ''),
            'business_name' => $this->get('business_name', ''),
            'enabled' => $this->get('enabled', false),
            'business_hours' => $this->get('business_hours', $this->getDefaultBusinessHours()),
            'widget_settings' => $this->get('widget_settings', $this->getDefaultWidgetSettings()),
            'message_templates' => $this->get('message_templates', $this->getDefaultMessageTemplates()),
            'rate_limit_settings' => $this->get('rate_limit_settings', $this->getDefaultRateLimitSettings()),
            'version' => $this->get('version', '1.0.0'),
        ];

        // Guardar en cache
        set_transient($cacheKey, $config, self::CACHE_EXPIRATION);
        
        return $config;
    }

    public function setAll(array $config): bool
    {
        try {
            // Validar configuración completa
            $validationResult = $this->validationService->validateConfig($config);
            if (!empty($validationResult)) {
                $this->errors = $validationResult;
                return false;
            }

            $success = true;
            
            foreach ($config as $key => $value) {
                if (!$this->set($key, $value)) {
                    $success = false;
                }
            }

            if ($success) {
                // Actualizar versión
                $this->set('version', $this->getCurrentVersion());
                do_action('wp_whatsapp_business_config_updated_all', $config);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logError('Error al guardar configuración completa: ' . $e->getMessage());
            return false;
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $optionKey = $this->getOptionKey($key);
        $cacheKey = $this->getCacheKey($key);
        
        // Eliminar de la base de datos
        $result = delete_option($optionKey);
        
        if ($result) {
            // Limpiar cache
            delete_transient($cacheKey);
            unset($this->cache[$cacheKey]);
            
            do_action('wp_whatsapp_business_config_deleted', $key);
        }
        
        return $result;
    }

    public function clearCache(): bool
    {
        global $wpdb;
        
        try {
            // Limpiar cache de WordPress
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . self::CACHE_PREFIX . '%'
                )
            );
            
            // Limpiar cache en memoria
            $this->cache = [];
            
            do_action('wp_whatsapp_business_config_cache_cleared');
            
            return true;
        } catch (\Exception $e) {
            $this->logError('Error al limpiar cache: ' . $e->getMessage());
            return false;
        }
    }

    public function getDefaults(): array
    {
        return [
            'api_key' => '',
            'phone_number_id' => '',
            'phone_number' => '',
            'business_name' => get_bloginfo('name'),
            'enabled' => false,
            'business_hours' => $this->getDefaultBusinessHours(),
            'widget_settings' => $this->getDefaultWidgetSettings(),
            'message_templates' => $this->getDefaultMessageTemplates(),
            'rate_limit_settings' => $this->getDefaultRateLimitSettings(),
            'version' => '1.0.0',
        ];
    }

    public function validate(array $config): array
    {
        return $this->validationService->validateConfig($config);
    }

    public function migrate(string $fromVersion, string $toVersion): bool
    {
        try {
            $migrations = $this->getMigrations();
            
            foreach ($migrations as $migration) {
                if (version_compare($fromVersion, $migration['from'], '<') && 
                    version_compare($toVersion, $migration['to'], '>=')) {
                    
                    if (!$this->executeMigration($migration)) {
                        return false;
                    }
                }
            }
            
            // Actualizar versión
            $this->set('version', $toVersion);
            
            do_action('wp_whatsapp_business_config_migrated', $fromVersion, $toVersion);
            
            return true;
        } catch (\Exception $e) {
            $this->logError('Error en migración: ' . $e->getMessage());
            return false;
        }
    }

    public function getValidationSchema(): array
    {
        return [
            'api_key' => [
                'type' => 'string',
                'required' => true,
                'min_length' => 1,
                'max_length' => 255,
            ],
            'phone_number_id' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^\d+$/',
            ],
            'phone_number' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^\+[1-9]\d{1,14}$/',
            ],
            'business_name' => [
                'type' => 'string',
                'required' => true,
                'min_length' => 1,
                'max_length' => 100,
            ],
            'enabled' => [
                'type' => 'boolean',
                'required' => false,
            ],
            'business_hours' => [
                'type' => 'array',
                'required' => false,
            ],
            'widget_settings' => [
                'type' => 'array',
                'required' => false,
            ],
            'message_templates' => [
                'type' => 'array',
                'required' => false,
            ],
            'rate_limit_settings' => [
                'type' => 'array',
                'required' => false,
            ],
        ];
    }

    public function export(): array
    {
        $config = $this->getAll();
        
        // Remover datos sensibles
        unset($config['api_key']);
        
        return [
            'version' => $this->getCurrentVersion(),
            'exported_at' => current_time('mysql'),
            'config' => $config,
        ];
    }

    public function import(array $config): bool
    {
        try {
            if (!isset($config['config']) || !is_array($config['config'])) {
                throw new \InvalidArgumentException('Formato de configuración inválido');
            }

            $importConfig = $config['config'];
            
            // Validar configuración importada
            $validationResult = $this->validate($importConfig);
            if (!empty($validationResult)) {
                $this->errors = $validationResult;
                return false;
            }

            // Importar configuración
            return $this->setAll($importConfig);
        } catch (\Exception $e) {
            $this->logError('Error al importar configuración: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener configuración como objeto inmutable
     */
    public function getConfig(): WhatsAppConfig
    {
        return new WhatsAppConfig($this->getAll());
    }

    private function getOptionKey(string $key): string
    {
        return self::OPTION_PREFIX . $key;
    }

    private function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . md5($key);
    }

    private function setCache(string $key, $value): void
    {
        $this->cache[$key] = $value;
        set_transient($key, $value, self::CACHE_EXPIRATION);
    }

    private function clearConfigCache(): void
    {
        delete_transient(self::CACHE_PREFIX . 'all');
    }

    private function getDefaultBusinessHours(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'tuesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'wednesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'thursday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'friday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'saturday' => ['open' => '10:00', 'close' => '14:00', 'enabled' => true],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'enabled' => false],
        ];
    }

    private function getDefaultWidgetSettings(): array
    {
        return [
            'position' => 'bottom-right',
            'size' => 'medium',
            'color' => '#25D366',
            'text' => '¿Necesitas ayuda?',
            'enabled' => true,
            'show_on_mobile' => true,
            'show_on_desktop' => true,
        ];
    }

    private function getDefaultMessageTemplates(): array
    {
        return [
            'default' => '¡Hola! ¿En qué puedo ayudarte?',
            'welcome' => '¡Bienvenido a {business_name}! ¿Cómo puedo asistirte hoy?',
            'support' => 'Hola, necesito soporte técnico.',
            'sales' => 'Hola, me interesa conocer más sobre sus productos.',
            'appointment' => 'Hola, me gustaría agendar una cita.',
        ];
    }

    private function getDefaultRateLimitSettings(): array
    {
        return [
            'enabled' => true,
            'max_requests_per_hour' => 100,
            'max_requests_per_day' => 1000,
            'time_window' => 3600,
        ];
    }

    private function getMigrations(): array
    {
        return [
            [
                'from' => '1.0.0',
                'to' => '1.1.0',
                'callback' => [$this, 'migrateTo110'],
            ],
            [
                'from' => '1.1.0',
                'to' => '1.2.0',
                'callback' => [$this, 'migrateTo120'],
            ],
        ];
    }

    private function executeMigration(array $migration): bool
    {
        try {
            return call_user_func($migration['callback']);
        } catch (\Exception $e) {
            $this->logError('Error en migración ' . $migration['from'] . ' -> ' . $migration['to'] . ': ' . $e->getMessage());
            return false;
        }
    }

    private function migrateTo110(): bool
    {
        // Migración de ejemplo: agregar nuevos campos
        $widgetSettings = $this->get('widget_settings', []);
        if (!isset($widgetSettings['show_on_mobile'])) {
            $widgetSettings['show_on_mobile'] = true;
            $this->set('widget_settings', $widgetSettings);
        }
        
        return true;
    }

    private function migrateTo120(): bool
    {
        // Migración de ejemplo: actualizar formato de horarios
        $businessHours = $this->get('business_hours', []);
        foreach ($businessHours as $day => $hours) {
            if (!isset($hours['enabled'])) {
                $businessHours[$day]['enabled'] = true;
            }
        }
        $this->set('business_hours', $businessHours);
        
        return true;
    }

    private function getCurrentVersion(): string
    {
        return defined('WP_WHATSAPP_BUSINESS_VERSION') ? WP_WHATSAPP_BUSINESS_VERSION : '1.0.0';
    }

    private function logError(string $message): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[WP WhatsApp Business] ' . $message);
        }
        
        do_action('wp_whatsapp_business_error_logged', $message);
    }
} 