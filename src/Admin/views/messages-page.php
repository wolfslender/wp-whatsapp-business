<?php
/**
 * Página de mensajes del admin
 *
 * @package WPWhatsAppBusiness\Admin\views
 * @since 1.0.0
 */

// Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

$whatsappService = \WPWhatsAppBusiness\Services\ServiceFactory::createWhatsAppService();
$configService = \WPWhatsAppBusiness\Services\ServiceFactory::createConfigService();
$config = $configService->getConfig();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wp-whatsapp-messages">
        <!-- Envío de mensaje de prueba -->
        <div class="wp-whatsapp-send-message">
            <h2><?php _e('Enviar Mensaje de Prueba', 'wp-whatsapp-business'); ?></h2>
            
            <?php if (!$config->isEnabled()): ?>
                <div class="notice notice-warning">
                    <p><?php _e('El plugin está deshabilitado. Habilítalo en la configuración para enviar mensajes.', 'wp-whatsapp-business'); ?></p>
                </div>
            <?php elseif (empty($config->getApiKey()) || empty($config->getPhoneNumberId())): ?>
                <div class="notice notice-error">
                    <p><?php _e('API Key o Phone Number ID no configurados. Configúralos en la página principal.', 'wp-whatsapp-business'); ?></p>
                </div>
            <?php else: ?>
                <form method="post" action="" id="send-test-message-form">
                    <?php wp_nonce_field('wp_whatsapp_send_test_message', 'wp_whatsapp_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_phone_number"><?php _e('Número de Teléfono', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="test_phone_number" 
                                       name="test_phone_number" 
                                       class="regular-text"
                                       placeholder="<?php _e('+1234567890', 'wp-whatsapp-business'); ?>"
                                       required>
                                <p class="description">
                                    <?php _e('Número en formato internacional (E.164)', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="test_message"><?php _e('Mensaje', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <textarea id="test_message" 
                                          name="test_message" 
                                          rows="4" 
                                          class="large-text"
                                          placeholder="<?php _e('Escribe tu mensaje aquí...', 'wp-whatsapp-business'); ?>"
                                          required></textarea>
                                <p class="description">
                                    <?php _e('Máximo 1000 caracteres', 'wp-whatsapp-business'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="message_type"><?php _e('Tipo de Mensaje', 'wp-whatsapp-business'); ?></label>
                            </th>
                            <td>
                                <select id="message_type" name="message_type">
                                    <option value="text"><?php _e('Texto', 'wp-whatsapp-business'); ?></option>
                                    <option value="template"><?php _e('Plantilla', 'wp-whatsapp-business'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Enviar Mensaje', 'wp-whatsapp-business'), 'primary', 'send_test_message'); ?>
                </form>
                
                <div id="message-result" style="display: none;"></div>
            <?php endif; ?>
        </div>

        <!-- Plantillas de mensajes -->
        <div class="wp-whatsapp-templates">
            <h2><?php _e('Plantillas de Mensajes', 'wp-whatsapp-business'); ?></h2>
            
            <div class="templates-grid">
                <?php
                $templates = $config->getMessageTemplates();
                foreach ($templates as $key => $template):
                ?>
                    <div class="template-card">
                        <h3><?php echo esc_html(ucfirst($key)); ?></h3>
                        <div class="template-content">
                            <?php echo esc_html($template); ?>
                        </div>
                        <div class="template-actions">
                            <button type="button" 
                                    class="button button-small use-template" 
                                    data-template="<?php echo esc_attr($key); ?>">
                                <?php _e('Usar', 'wp-whatsapp-business'); ?>
                            </button>
                            <button type="button" 
                                    class="button button-small edit-template" 
                                    data-template="<?php echo esc_attr($key); ?>">
                                <?php _e('Editar', 'wp-whatsapp-business'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Historial de mensajes -->
        <div class="wp-whatsapp-history">
            <h2><?php _e('Historial de Mensajes', 'wp-whatsapp-business'); ?></h2>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="bulk_action">
                        <option value="-1"><?php _e('Acciones en lote', 'wp-whatsapp-business'); ?></option>
                        <option value="delete"><?php _e('Eliminar', 'wp-whatsapp-business'); ?></option>
                    </select>
                    <input type="submit" class="button action" value="<?php _e('Aplicar', 'wp-whatsapp-business'); ?>">
                </div>
                <div class="alignright">
                    <input type="text" placeholder="<?php _e('Buscar mensajes...', 'wp-whatsapp-business'); ?>" class="regular-text">
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all-1">
                        </td>
                        <th scope="col" class="manage-column column-phone"><?php _e('Número', 'wp-whatsapp-business'); ?></th>
                        <th scope="col" class="manage-column column-message"><?php _e('Mensaje', 'wp-whatsapp-business'); ?></th>
                        <th scope="col" class="manage-column column-type"><?php _e('Tipo', 'wp-whatsapp-business'); ?></th>
                        <th scope="col" class="manage-column column-status"><?php _e('Estado', 'wp-whatsapp-business'); ?></th>
                        <th scope="col" class="manage-column column-date"><?php _e('Fecha', 'wp-whatsapp-business'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="no-messages">
                            <?php _e('No hay mensajes para mostrar.', 'wp-whatsapp-business'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.wp-whatsapp-messages {
    margin-top: 20px;
}

.wp-whatsapp-send-message,
.wp-whatsapp-templates,
.wp-whatsapp-history {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.template-card {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
}

.template-content {
    background: #fff;
    padding: 10px;
    border-radius: 3px;
    margin: 10px 0;
    font-style: italic;
    color: #666;
}

.template-actions {
    display: flex;
    gap: 10px;
}

#message-result {
    margin-top: 15px;
    padding: 10px;
    border-radius: 4px;
}

#message-result.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

#message-result.error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.no-messages {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Usar plantilla
    $('.use-template').on('click', function() {
        var templateKey = $(this).data('template');
        var templateText = '<?php echo addslashes(json_encode($templates)); ?>';
        var templates = JSON.parse(templateText);
        
        if (templates[templateKey]) {
            $('#test_message').val(templates[templateKey]);
        }
    });
    
    // Enviar mensaje de prueba
    $('#send-test-message-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitButton = $(this).find('input[type="submit"]');
        var originalText = submitButton.val();
        
        submitButton.val('<?php _e('Enviando...', 'wp-whatsapp-business'); ?>').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_whatsapp_send_test_message',
                form_data: formData
            },
            success: function(response) {
                var resultDiv = $('#message-result');
                
                if (response.success) {
                    resultDiv.removeClass('error').addClass('success')
                        .html('<strong><?php _e('Éxito:', 'wp-whatsapp-business'); ?></strong> ' + response.data.message)
                        .show();
                    
                    // Limpiar formulario
                    $('#send-test-message-form')[0].reset();
                } else {
                    resultDiv.removeClass('success').addClass('error')
                        .html('<strong><?php _e('Error:', 'wp-whatsapp-business'); ?></strong> ' + response.data.message)
                        .show();
                }
            },
            error: function() {
                $('#message-result').removeClass('success').addClass('error')
                    .html('<strong><?php _e('Error:', 'wp-whatsapp-business'); ?></strong> <?php _e('Error de conexión', 'wp-whatsapp-business'); ?>')
                    .show();
            },
            complete: function() {
                submitButton.val(originalText).prop('disabled', false);
            }
        });
    });
});
</script> 