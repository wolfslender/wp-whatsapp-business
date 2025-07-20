<?php
/**
 * Página de estadísticas del admin
 *
 * @package WPWhatsAppBusiness\Admin\views
 * @since 1.0.0
 */

// Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

$configService = \WPWhatsAppBusiness\Services\ServiceFactory::createConfigService();
$whatsappService = \WPWhatsAppBusiness\Services\ServiceFactory::createWhatsAppService();
$config = $configService->getConfig();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wp-whatsapp-stats">
        <!-- Resumen general -->
        <div class="wp-whatsapp-summary">
            <h2><?php _e('Resumen General', 'wp-whatsapp-business'); ?></h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📱</div>
                    <div class="stat-number"><?php echo $config->isEnabled() ? '1' : '0'; ?></div>
                    <div class="stat-label"><?php _e('Plugin Activo', 'wp-whatsapp-business'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🕒</div>
                    <div class="stat-number"><?php echo $whatsappService->isBusinessOpen() ? '1' : '0'; ?></div>
                    <div class="stat-label"><?php _e('Negocio Abierto', 'wp-whatsapp-business'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?php echo count($config->getMessageTemplates()); ?></div>
                    <div class="stat-label"><?php _e('Plantillas', 'wp-whatsapp-business'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⚙️</div>
                    <div class="stat-number"><?php echo !empty($config->getApiKey()) ? '1' : '0'; ?></div>
                    <div class="stat-label"><?php _e('API Configurada', 'wp-whatsapp-business'); ?></div>
                </div>
            </div>
        </div>

        <!-- Configuración del widget -->
        <div class="wp-whatsapp-widget-stats">
            <h2><?php _e('Configuración del Widget', 'wp-whatsapp-business'); ?></h2>
            
            <div class="widget-config-grid">
                <div class="config-item">
                    <strong><?php _e('Posición:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(ucfirst(str_replace('-', ' ', $config->getWidgetSettings()['position']))); ?></span>
                </div>
                
                <div class="config-item">
                    <strong><?php _e('Tamaño:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(ucfirst($config->getWidgetSettings()['size'])); ?></span>
                </div>
                
                <div class="config-item">
                    <strong><?php _e('Color:', 'wp-whatsapp-business'); ?></strong>
                    <span class="color-preview" style="background-color: <?php echo esc_attr($config->getWidgetSettings()['color']); ?>"></span>
                    <span><?php echo esc_html($config->getWidgetSettings()['color']); ?></span>
                </div>
                
                <div class="config-item">
                    <strong><?php _e('Texto:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html($config->getWidgetSettings()['text']); ?></span>
                </div>
                
                <div class="config-item">
                    <strong><?php _e('Móvil:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo $config->getWidgetSettings()['show_on_mobile'] ? __('Sí', 'wp-whatsapp-business') : __('No', 'wp-whatsapp-business'); ?></span>
                </div>
                
                <div class="config-item">
                    <strong><?php _e('Desktop:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo $config->getWidgetSettings()['show_on_desktop'] ? __('Sí', 'wp-whatsapp-business') : __('No', 'wp-whatsapp-business'); ?></span>
                </div>
            </div>
        </div>

        <!-- Horarios de negocio -->
        <div class="wp-whatsapp-business-hours">
            <h2><?php _e('Horarios de Negocio', 'wp-whatsapp-business'); ?></h2>
            
            <div class="business-hours-grid">
                <?php
                $businessHours = $config->getBusinessHours();
                $days = [
                    'monday' => __('Lunes', 'wp-whatsapp-business'),
                    'tuesday' => __('Martes', 'wp-whatsapp-business'),
                    'wednesday' => __('Miércoles', 'wp-whatsapp-business'),
                    'thursday' => __('Jueves', 'wp-whatsapp-business'),
                    'friday' => __('Viernes', 'wp-whatsapp-business'),
                    'saturday' => __('Sábado', 'wp-whatsapp-business'),
                    'sunday' => __('Domingo', 'wp-whatsapp-business')
                ];
                
                foreach ($days as $dayKey => $dayName):
                    $hours = $businessHours[$dayKey] ?? [];
                ?>
                    <div class="day-item <?php echo $hours['enabled'] ? 'enabled' : 'disabled'; ?>">
                        <div class="day-name"><?php echo esc_html($dayName); ?></div>
                        <div class="day-hours">
                            <?php if ($hours['enabled']): ?>
                                <?php echo esc_html($hours['open']); ?> - <?php echo esc_html($hours['close']); ?>
                            <?php else: ?>
                                <span class="closed"><?php _e('Cerrado', 'wp-whatsapp-business'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="day-status">
                            <span class="status-dot <?php echo $hours['enabled'] ? 'enabled' : 'disabled'; ?>"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Rate limiting -->
        <div class="wp-whatsapp-rate-limit">
            <h2><?php _e('Rate Limiting', 'wp-whatsapp-business'); ?></h2>
            
            <div class="rate-limit-grid">
                <div class="rate-item">
                    <strong><?php _e('Habilitado:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo $config->getRateLimitSettings()['enabled'] ? __('Sí', 'wp-whatsapp-business') : __('No', 'wp-whatsapp-business'); ?></span>
                </div>
                
                <div class="rate-item">
                    <strong><?php _e('Máximo por hora:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html($config->getRateLimitSettings()['max_requests_per_hour']); ?></span>
                </div>
                
                <div class="rate-item">
                    <strong><?php _e('Máximo por día:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html($config->getRateLimitSettings()['max_requests_per_day']); ?></span>
                </div>
                
                <div class="rate-item">
                    <strong><?php _e('Ventana de tiempo:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html($config->getRateLimitSettings()['time_window']); ?>s</span>
                </div>
            </div>
        </div>

        <!-- Información del sistema -->
        <div class="wp-whatsapp-system-info">
            <h2><?php _e('Información del Sistema', 'wp-whatsapp-business'); ?></h2>
            
            <div class="system-info-grid">
                <div class="info-item">
                    <strong><?php _e('Versión del Plugin:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html($config->getVersion() ?? '1.0.0'); ?></span>
                </div>
                
                <div class="info-item">
                    <strong><?php _e('Versión de WordPress:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(get_bloginfo('version')); ?></span>
                </div>
                
                <div class="info-item">
                    <strong><?php _e('Versión de PHP:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(PHP_VERSION); ?></span>
                </div>
                
                <div class="info-item">
                    <strong><?php _e('Tipo de Dispositivo:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(ucfirst($whatsappService->detectDevice())); ?></span>
                </div>
                
                <div class="info-item">
                    <strong><?php _e('Zona Horaria:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(wp_timezone_string()); ?></span>
                </div>
                
                <div class="info-item">
                    <strong><?php _e('Hora Actual:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php echo esc_html(current_time('Y-m-d H:i:s')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wp-whatsapp-stats {
    margin-top: 20px;
}

.wp-whatsapp-summary,
.wp-whatsapp-widget-stats,
.wp-whatsapp-business-hours,
.wp-whatsapp-rate-limit,
.wp-whatsapp-system-info {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.stat-card {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.stat-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.widget-config-grid,
.rate-limit-grid,
.system-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.config-item,
.rate-item,
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.color-preview {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1px solid #ddd;
    margin-right: 5px;
}

.business-hours-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.day-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    border-left: 4px solid #ddd;
}

.day-item.enabled {
    border-left-color: #46b450;
}

.day-item.disabled {
    border-left-color: #dc3232;
}

.day-name {
    font-weight: bold;
}

.day-hours {
    color: #666;
}

.closed {
    color: #dc3232;
    font-style: italic;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-dot.enabled {
    background-color: #46b450;
}

.status-dot.disabled {
    background-color: #dc3232;
}
</style> 