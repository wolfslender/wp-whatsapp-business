<?php
/**
 * Página principal del admin
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
    
    <div class="wp-whatsapp-dashboard">
        <!-- Estado del plugin -->
        <div class="wp-whatsapp-status-card">
            <h2><?php _e('Estado del Plugin', 'wp-whatsapp-business'); ?></h2>
            <div class="status-indicator <?php echo $config->isEnabled() ? 'enabled' : 'disabled'; ?>">
                <span class="status-dot"></span>
                <span class="status-text">
                    <?php echo $config->isEnabled() ? __('Habilitado', 'wp-whatsapp-business') : __('Deshabilitado', 'wp-whatsapp-business'); ?>
                </span>
            </div>
            
            <?php if ($config->isEnabled()): ?>
                <div class="business-status">
                    <h3><?php _e('Estado del Negocio', 'wp-whatsapp-business'); ?></h3>
                    <div class="status-indicator <?php echo $whatsappService->isBusinessOpen() ? 'open' : 'closed'; ?>">
                        <span class="status-dot"></span>
                        <span class="status-text">
                            <?php echo $whatsappService->isBusinessOpen() ? __('Abierto', 'wp-whatsapp-business') : __('Cerrado', 'wp-whatsapp-business'); ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Configuración rápida -->
        <div class="wp-whatsapp-quick-config">
            <h2><?php _e('Configuración Rápida', 'wp-whatsapp-business'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('wp_whatsapp_business_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('API Key', 'wp-whatsapp-business'); ?></label>
                        </th>
                        <td>
                            <input type="password" 
                                   id="api_key" 
                                   name="wp_whatsapp_business_api_key" 
                                   value="<?php echo esc_attr($config->getApiKey()); ?>" 
                                   class="regular-text"
                                   placeholder="<?php _e('Ingresa tu API Key de WhatsApp Business', 'wp-whatsapp-business'); ?>">
                            <p class="description">
                                <?php _e('Obtén tu API Key desde el Facebook Developer Console', 'wp-whatsapp-business'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="phone_number_id"><?php _e('Phone Number ID', 'wp-whatsapp-business'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="phone_number_id" 
                                   name="wp_whatsapp_business_phone_number_id" 
                                   value="<?php echo esc_attr($config->getPhoneNumberId()); ?>" 
                                   class="regular-text"
                                   placeholder="<?php _e('Ej: 123456789', 'wp-whatsapp-business'); ?>">
                            <p class="description">
                                <?php _e('ID del número de teléfono de WhatsApp Business', 'wp-whatsapp-business'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="phone_number"><?php _e('Número de Teléfono', 'wp-whatsapp-business'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="phone_number" 
                                   name="wp_whatsapp_business_phone_number" 
                                   value="<?php echo esc_attr($config->getPhoneNumber()); ?>" 
                                   class="regular-text"
                                   placeholder="<?php _e('+1234567890', 'wp-whatsapp-business'); ?>">
                            <p class="description">
                                <?php _e('Número en formato internacional (E.164)', 'wp-whatsapp-business'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_name"><?php _e('Nombre del Negocio', 'wp-whatsapp-business'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="business_name" 
                                   name="wp_whatsapp_business_business_name" 
                                   value="<?php echo esc_attr($config->getBusinessName()); ?>" 
                                   class="regular-text"
                                   placeholder="<?php _e('Nombre de tu negocio', 'wp-whatsapp-business'); ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php _e('Habilitar Plugin', 'wp-whatsapp-business'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="enabled" 
                                   name="wp_whatsapp_business_enabled" 
                                   value="1" 
                                   <?php checked($config->isEnabled()); ?>>
                            <label for="enabled"><?php _e('Activar WhatsApp Business en el sitio', 'wp-whatsapp-business'); ?></label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Guardar Configuración', 'wp-whatsapp-business')); ?>
            </form>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="wp-whatsapp-stats">
            <h2><?php _e('Estadísticas Rápidas', 'wp-whatsapp-business'); ?></h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php _e('Widget', 'wp-whatsapp-business'); ?></h3>
                    <div class="stat-value">
                        <?php echo $config->getWidgetSettings()['enabled'] ? __('Activo', 'wp-whatsapp-business') : __('Inactivo', 'wp-whatsapp-business'); ?>
                    </div>
                    <div class="stat-description">
                        <?php _e('Botón flotante de WhatsApp', 'wp-whatsapp-business'); ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><?php _e('Dispositivo', 'wp-whatsapp-business'); ?></h3>
                    <div class="stat-value">
                        <?php echo ucfirst($whatsappService->detectDevice()); ?>
                    </div>
                    <div class="stat-description">
                        <?php _e('Tipo de dispositivo detectado', 'wp-whatsapp-business'); ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><?php _e('Plantillas', 'wp-whatsapp-business'); ?></h3>
                    <div class="stat-value">
                        <?php echo count($config->getMessageTemplates()); ?>
                    </div>
                    <div class="stat-description">
                        <?php _e('Mensajes predefinidos', 'wp-whatsapp-business'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="wp-whatsapp-quick-actions">
            <h2><?php _e('Acciones Rápidas', 'wp-whatsapp-business'); ?></h2>
            
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=wp-whatsapp-business-settings'); ?>" class="button button-primary">
                    <?php _e('Configuración Avanzada', 'wp-whatsapp-business'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=wp-whatsapp-business-messages'); ?>" class="button">
                    <?php _e('Ver Mensajes', 'wp-whatsapp-business'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=wp-whatsapp-business-stats'); ?>" class="button">
                    <?php _e('Estadísticas', 'wp-whatsapp-business'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=wp-whatsapp-business-help'); ?>" class="button">
                    <?php _e('Ayuda', 'wp-whatsapp-business'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.wp-whatsapp-dashboard {
    margin-top: 20px;
}

.wp-whatsapp-status-card,
.wp-whatsapp-quick-config,
.wp-whatsapp-stats,
.wp-whatsapp-quick-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.status-indicator {
    display: flex;
    align-items: center;
    margin: 10px 0;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-indicator.enabled .status-dot,
.status-indicator.open .status-dot {
    background-color: #46b450;
}

.status-indicator.disabled .status-dot,
.status-indicator.closed .status-dot {
    background-color: #dc3232;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.stat-card {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
    margin: 10px 0;
}

.stat-description {
    color: #666;
    font-size: 12px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.business-status {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}
</style> 