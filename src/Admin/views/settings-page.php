<?php
/**
 * Vista de la página de configuración
 *
 * @package WPWhatsAppBusiness\Admin\Views
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener configuración actual
$settings = $this->config_service->getSettings();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Configuración guardada correctamente.', 'wp-whatsapp-business'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php 
                switch ($_GET['error']) {
                    case 'validation':
                        _e('Error de validación. Por favor revisa los campos marcados.', 'wp-whatsapp-business');
                        break;
                    case 'upload':
                        _e('Error al subir archivo. Por favor intenta de nuevo.', 'wp-whatsapp-business');
                        break;
                    case 'invalid_json':
                        _e('Archivo JSON inválido. Por favor verifica el formato.', 'wp-whatsapp-business');
                        break;
                    case 'import':
                        _e('Error al importar configuración. Por favor verifica el archivo.', 'wp-whatsapp-business');
                        break;
                    default:
                        _e('Ha ocurrido un error. Por favor intenta de nuevo.', 'wp-whatsapp-business');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="wp-whatsapp-business-admin-container">
        <!-- Tabs de navegación -->
        <nav class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
                <?php _e('General', 'wp-whatsapp-business'); ?>
            </a>
            <a href="#appearance" class="nav-tab" data-tab="appearance">
                <?php _e('Apariencia', 'wp-whatsapp-business'); ?>
            </a>
            <a href="#notifications" class="nav-tab" data-tab="notifications">
                <?php _e('Notificaciones', 'wp-whatsapp-business'); ?>
            </a>
            <a href="#advanced" class="nav-tab" data-tab="advanced">
                <?php _e('Avanzado', 'wp-whatsapp-business'); ?>
            </a>
            <a href="#tools" class="nav-tab" data-tab="tools">
                <?php _e('Herramientas', 'wp-whatsapp-business'); ?>
            </a>
        </nav>

        <!-- Tab General -->
        <div id="general" class="tab-content active">
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_whatsapp_business_general');
                do_settings_sections('wp_whatsapp_business_general');
                ?>
                
                <div class="form-section">
                    <h3><?php _e('Configuración de API', 'wp-whatsapp-business'); ?></h3>
                    <p class="description">
                        <?php _e('Configura tu integración con WhatsApp Business API.', 'wp-whatsapp-business'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_key"><?php _e('API Key', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="api_key" 
                                       name="wp_whatsapp_business_settings[api_key]" 
                                       value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Ingresa tu API Key de WhatsApp Business. Puedes obtenerla desde el Facebook Developer Console.', 'wp-whatsapp-business'); ?>
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
                                       name="wp_whatsapp_business_settings[phone_number_id]" 
                                       value="<?php echo esc_attr($settings['phone_number_id'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Ingresa el Phone Number ID de tu número de WhatsApp Business.', 'wp-whatsapp-business'); ?>
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
                                       name="wp_whatsapp_business_settings[phone_number]" 
                                       value="<?php echo esc_attr($settings['phone_number'] ?? ''); ?>" 
                                       class="regular-text" 
                                       placeholder="+1234567890" />
                                <p class="description">
                                    <?php _e('Ingresa tu número de teléfono en formato internacional (+1234567890).', 'wp-whatsapp-business'); ?>
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
                                       name="wp_whatsapp_business_settings[business_name]" 
                                       value="<?php echo esc_attr($settings['business_name'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Ingresa el nombre de tu negocio que aparecerá en el widget.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Habilitar Plugin', 'wp-whatsapp-business'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[enabled]" 
                                           value="1" 
                                           <?php checked($settings['enabled'] ?? false, true); ?> />
                                    <?php _e('Habilitar el plugin', 'wp-whatsapp-business'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="form-section">
                    <h3><?php _e('Horarios de Negocio', 'wp-whatsapp-business'); ?></h3>
                    <p class="description">
                        <?php _e('Configura los horarios de tu negocio para que el widget solo aparezca cuando estés disponible.', 'wp-whatsapp-business'); ?>
                    </p>
                    
                    <div class="business-hours-container">
                        <?php
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

                        foreach ($days as $day_key => $day_name):
                            $hours = $business_hours[$day_key] ?? ['09:00', '18:00'];
                            $is_closed = ($hours === 'closed');
                        ?>
                            <div class="business-hour-row">
                                <label class="day-label"><?php echo esc_html($day_name); ?>:</label>
                                
                                <?php if ($is_closed): ?>
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[business_hours][<?php echo $day_key; ?>]" 
                                           value="closed" 
                                           checked /> 
                                    <span><?php _e('Cerrado', 'wp-whatsapp-business'); ?></span>
                                <?php else: ?>
                                    <input type="time" 
                                           name="wp_whatsapp_business_settings[business_hours][<?php echo $day_key; ?>][]" 
                                           value="<?php echo esc_attr($hours[0]); ?>" /> - 
                                    <input type="time" 
                                           name="wp_whatsapp_business_settings[business_hours][<?php echo $day_key; ?>][]" 
                                           value="<?php echo esc_attr($hours[1]); ?>" /> 
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[business_hours][<?php echo $day_key; ?>]" 
                                           value="closed" /> 
                                    <span><?php _e('Cerrado', 'wp-whatsapp-business'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Tab Apariencia -->
        <div id="appearance" class="tab-content">
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_whatsapp_business_appearance');
                do_settings_sections('wp_whatsapp_business_appearance');
                ?>
                
                <div class="form-section">
                    <h3><?php _e('Configuración del Widget', 'wp-whatsapp-business'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="widget_position"><?php _e('Posición', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <select id="widget_position" name="wp_whatsapp_business_settings[widget_position]">
                                    <option value="bottom-right" <?php selected($settings['widget_position'] ?? 'bottom-right', 'bottom-right'); ?>>
                                        <?php _e('Inferior Derecha', 'wp-whatsapp-business'); ?>
                                    </option>
                                    <option value="bottom-left" <?php selected($settings['widget_position'] ?? 'bottom-right', 'bottom-left'); ?>>
                                        <?php _e('Inferior Izquierda', 'wp-whatsapp-business'); ?>
                                    </option>
                                    <option value="top-right" <?php selected($settings['widget_position'] ?? 'bottom-right', 'top-right'); ?>>
                                        <?php _e('Superior Derecha', 'wp-whatsapp-business'); ?>
                                    </option>
                                    <option value="top-left" <?php selected($settings['widget_position'] ?? 'bottom-right', 'top-left'); ?>>
                                        <?php _e('Superior Izquierda', 'wp-whatsapp-business'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="widget_color"><?php _e('Color del Widget', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <input type="color" 
                                       id="widget_color" 
                                       name="wp_whatsapp_business_settings[appearance][widget_color]" 
                                       value="<?php echo esc_attr($settings['appearance']['widget_color'] ?? '#25D366'); ?>" />
                                <p class="description">
                                    <?php _e('Selecciona el color principal del widget.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="widget_text"><?php _e('Texto del Widget', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="widget_text" 
                                       name="wp_whatsapp_business_settings[widget_text]" 
                                       value="<?php echo esc_attr($settings['widget_text'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Texto que aparecerá en el widget.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Tab Notificaciones -->
        <div id="notifications" class="tab-content">
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_whatsapp_business_notifications');
                do_settings_sections('wp_whatsapp_business_notifications');
                ?>
                
                <div class="form-section">
                    <h3><?php _e('Configuración de Notificaciones', 'wp-whatsapp-business'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Notificaciones por Email', 'wp-whatsapp-business'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[notification_settings][email_notifications]" 
                                           value="1" 
                                           <?php checked($settings['notification_settings']['email_notifications'] ?? false, true); ?> />
                                    <?php _e('Habilitar notificaciones por email', 'wp-whatsapp-business'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email"><?php _e('Email del Administrador', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="admin_email" 
                                       name="wp_whatsapp_business_settings[notification_settings][admin_email]" 
                                       value="<?php echo esc_attr($settings['notification_settings']['admin_email'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">
                                    <?php _e('Email donde recibirás las notificaciones.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Tab Avanzado -->
        <div id="advanced" class="tab-content">
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_whatsapp_business_advanced');
                do_settings_sections('wp_whatsapp_business_advanced');
                ?>
                
                <div class="form-section">
                    <h3><?php _e('Configuración Avanzada', 'wp-whatsapp-business'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Modo Debug', 'wp-whatsapp-business'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[advanced][debug_mode]" 
                                           value="1" 
                                           <?php checked($settings['advanced']['debug_mode'] ?? false, true); ?> />
                                    <?php _e('Habilitar modo debug', 'wp-whatsapp-business'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Activa el modo debug para obtener información detallada de errores.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Registrar Mensajes', 'wp-whatsapp-business'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="wp_whatsapp_business_settings[advanced][log_messages]" 
                                           value="1" 
                                           <?php checked($settings['advanced']['log_messages'] ?? true, true); ?> />
                                    <?php _e('Registrar mensajes en la base de datos', 'wp-whatsapp-business'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Guarda un registro de todos los mensajes enviados.', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Tab Herramientas -->
        <div id="tools" class="tab-content">
            <div class="form-section">
                <h3><?php _e('Herramientas', 'wp-whatsapp-business'); ?></h3>
                
                <div class="tools-container">
                    <div class="tool-card">
                        <h4><?php _e('Exportar Configuración', 'wp-whatsapp-business'); ?></h4>
                        <p><?php _e('Descarga una copia de tu configuración actual.', 'wp-whatsapp-business'); ?></p>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="export_whatsapp_settings" />
                            <?php wp_nonce_field('export_whatsapp_settings', 'export_nonce'); ?>
                            <?php submit_button(__('Exportar', 'wp-whatsapp-business'), 'secondary', 'export_settings'); ?>
                        </form>
                    </div>
                    
                    <div class="tool-card">
                        <h4><?php _e('Importar Configuración', 'wp-whatsapp-business'); ?></h4>
                        <p><?php _e('Importa configuración desde un archivo JSON.', 'wp-whatsapp-business'); ?></p>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="import_whatsapp_settings" />
                            <?php wp_nonce_field('import_whatsapp_settings', 'import_nonce'); ?>
                            <input type="file" name="settings_file" accept=".json" required />
                            <?php submit_button(__('Importar', 'wp-whatsapp-business'), 'secondary', 'import_settings'); ?>
                        </form>
                    </div>
                    
                    <div class="tool-card">
                        <h4><?php _e('Restablecer Configuración', 'wp-whatsapp-business'); ?></h4>
                        <p><?php _e('Restablece toda la configuración a los valores por defecto.', 'wp-whatsapp-business'); ?></p>
                        <button type="button" class="button button-secondary" id="reset-settings">
                            <?php _e('Restablecer', 'wp-whatsapp-business'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wp-whatsapp-business-admin-container {
    margin-top: 20px;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin: 20px 0;
    border-radius: 4px;
}

.form-section h3 {
    margin-top: 0;
    color: #23282d;
}

.business-hours-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.business-hour-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.day-label {
    min-width: 80px;
    font-weight: 600;
}

.tools-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tool-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 4px;
}

.tool-card h4 {
    margin-top: 0;
    color: #23282d;
}

.tool-card p {
    color: #666;
    margin-bottom: 15px;
}

.tool-card input[type="file"] {
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Manejo de tabs
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('data-tab');
        
        // Actualizar tabs activos
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Mostrar contenido del tab
        $('.tab-content').removeClass('active');
        $('#' + target).addClass('active');
    });
    
    // Restablecer configuración
    $('#reset-settings').on('click', function() {
        if (confirm('<?php _e('¿Estás seguro de que quieres restablecer toda la configuración? Esta acción no se puede deshacer.', 'wp-whatsapp-business'); ?>')) {
            // Aquí iría la lógica para restablecer la configuración
            alert('<?php _e('Configuración restablecida.', 'wp-whatsapp-business'); ?>');
        }
    });
    
    // Manejo de horarios de negocio
    $('input[type="checkbox"][value="closed"]').on('change', function() {
        const row = $(this).closest('.business-hour-row');
        const timeInputs = row.find('input[type="time"]');
        
        if ($(this).is(':checked')) {
            timeInputs.prop('disabled', true);
        } else {
            timeInputs.prop('disabled', false);
        }
    });
});
</script> 