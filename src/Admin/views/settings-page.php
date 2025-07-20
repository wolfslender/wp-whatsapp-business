<?php
/**
 * Página de configuración profesional con pestañas
 *
 * @package WPWhatsAppBusiness\Admin\views
 * @since 1.0.0
 */

// Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

$configService = \WPWhatsAppBusiness\Services\ServiceFactory::createConfigService();
$config = $configService->getConfig();
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap wp-whatsapp-admin">
    <div class="wp-whatsapp-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="description">
            <?php _e('Configuración completa del plugin de WhatsApp Business. Personaliza todos los aspectos de la integración.', 'wp-whatsapp-business'); ?>
        </p>
    </div>

    <div class="wp-whatsapp-tabs">
        <!-- Navegación de pestañas -->
        <div class="nav-tab-wrapper">
            <a href="?page=wp-whatsapp-business-settings&tab=general" 
               class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>"
               data-tab="general">
                <?php _e('Configuración General', 'wp-whatsapp-business'); ?>
            </a>
            <a href="?page=wp-whatsapp-business-settings&tab=widget" 
               class="nav-tab <?php echo $current_tab === 'widget' ? 'nav-tab-active' : ''; ?>"
               data-tab="widget">
                <?php _e('Personalización del Widget', 'wp-whatsapp-business'); ?>
            </a>
            <a href="?page=wp-whatsapp-business-settings&tab=hours" 
               class="nav-tab <?php echo $current_tab === 'hours' ? 'nav-tab-active' : ''; ?>"
               data-tab="hours">
                <?php _e('Horarios de Negocio', 'wp-whatsapp-business'); ?>
            </a>
            <a href="?page=wp-whatsapp-business-settings&tab=messages" 
               class="nav-tab <?php echo $current_tab === 'messages' ? 'nav-tab-active' : ''; ?>"
               data-tab="messages">
                <?php _e('Plantillas de Mensajes', 'wp-whatsapp-business'); ?>
            </a>
            <a href="?page=wp-whatsapp-business-settings&tab=advanced" 
               class="nav-tab <?php echo $current_tab === 'advanced' ? 'nav-tab-active' : ''; ?>"
               data-tab="advanced">
                <?php _e('Configuración Avanzada', 'wp-whatsapp-business'); ?>
            </a>
        </div>

        <!-- Contenido de las pestañas -->
        <div class="tab-content active" id="general-tab">
            <form method="post" action="options.php" class="wp-whatsapp-form">
                <?php
                settings_fields('wp_whatsapp_business_settings');
                do_settings_sections('wp_whatsapp_business_settings');
                ?>
                
                <div class="wp-whatsapp-form-actions">
                    <?php submit_button(__('Guardar Configuración General', 'wp-whatsapp-business'), 'primary', 'submit', false); ?>
                    <button type="button" class="button wp-whatsapp-test-phone">
                        <?php _e('Probar Conexión', 'wp-whatsapp-business'); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="tab-content" id="widget-tab">
            <form method="post" action="options.php" class="wp-whatsapp-form">
                <?php
                settings_fields('wp_whatsapp_business_widget');
                do_settings_sections('wp_whatsapp_business_widget');
                ?>
                
                <!-- Preview del widget -->
                <div class="widget-preview-container">
                    <h3><?php _e('Vista Previa del Widget', 'wp-whatsapp-business'); ?></h3>
                    <div id="widget-preview">
                        <p><?php _e('Configura las opciones del widget para ver la vista previa en tiempo real.', 'wp-whatsapp-business'); ?></p>
                    </div>
                </div>
                
                <div class="wp-whatsapp-form-actions">
                    <?php submit_button(__('Guardar Configuración del Widget', 'wp-whatsapp-business'), 'primary', 'submit', false); ?>
                    <button type="button" class="button wp-whatsapp-reset-widget">
                        <?php _e('Restablecer Valores', 'wp-whatsapp-business'); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="tab-content" id="hours-tab">
            <form method="post" action="options.php" class="wp-whatsapp-form">
                <?php
                settings_fields('wp_whatsapp_business_hours');
                do_settings_sections('wp_whatsapp_business_hours');
                ?>
                
                <div class="wp-whatsapp-form-actions">
                    <?php submit_button(__('Guardar Horarios', 'wp-whatsapp-business'), 'primary', 'submit', false); ?>
                    <button type="button" class="button wp-whatsapp-copy-hours">
                        <?php _e('Copiar Horarios', 'wp-whatsapp-business'); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="tab-content" id="messages-tab">
            <form method="post" action="options.php" class="wp-whatsapp-form">
                <?php
                settings_fields('wp_whatsapp_business_messages');
                do_settings_sections('wp_whatsapp_business_messages');
                ?>
                
                <div class="wp-whatsapp-form-actions">
                    <?php submit_button(__('Guardar Plantillas', 'wp-whatsapp-business'), 'primary', 'submit', false); ?>
                    <button type="button" class="button wp-whatsapp-test-message">
                        <?php _e('Probar Mensaje', 'wp-whatsapp-business'); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="tab-content" id="advanced-tab">
            <form method="post" action="options.php" class="wp-whatsapp-form">
                <?php
                settings_fields('wp_whatsapp_business_advanced');
                do_settings_sections('wp_whatsapp_business_advanced');
                ?>
                
                <!-- Import/Export -->
                <div class="import-export-container">
                    <h3><?php _e('Importar/Exportar Configuración', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Guarda una copia de tu configuración o restaura desde un archivo de respaldo.', 'wp-whatsapp-business'); ?></p>
                    
                    <div class="import-export-actions">
                        <button type="button" class="button wp-whatsapp-export">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Exportar Configuración', 'wp-whatsapp-business'); ?>
                        </button>
                        
                        <label for="wp-whatsapp-import" class="button">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Importar Configuración', 'wp-whatsapp-business'); ?>
                        </label>
                        <input type="file" id="wp-whatsapp-import" class="wp-whatsapp-import" accept=".json" style="display: none;">
                    </div>
                </div>
                
                <div class="wp-whatsapp-form-actions">
                    <?php submit_button(__('Guardar Configuración Avanzada', 'wp-whatsapp-business'), 'primary', 'submit', false); ?>
                    <button type="button" class="button wp-whatsapp-clear-cache">
                        <?php _e('Limpiar Caché', 'wp-whatsapp-business'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para testing de teléfono -->
<div id="wp-whatsapp-test-modal" class="wp-whatsapp-modal" style="display: none;">
    <div class="wp-whatsapp-modal-content">
        <div class="wp-whatsapp-modal-header">
            <h3><?php _e('Probar Conexión de WhatsApp', 'wp-whatsapp-business'); ?></h3>
            <button type="button" class="wp-whatsapp-modal-close">&times;</button>
        </div>
        <div class="wp-whatsapp-modal-body">
            <p><?php _e('Ingresa un número de teléfono para probar la conexión con WhatsApp Business API:', 'wp-whatsapp-business'); ?></p>
            <input type="text" class="wp-whatsapp-phone-input" placeholder="+1234567890" pattern="^\+[1-9]\d{1,14}$">
            <div class="wp-whatsapp-modal-actions">
                <button type="button" class="button button-primary wp-whatsapp-test-phone-confirm">
                    <?php _e('Probar Conexión', 'wp-whatsapp-business'); ?>
                </button>
                <button type="button" class="button wp-whatsapp-modal-cancel">
                    <?php _e('Cancelar', 'wp-whatsapp-business'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para testing de mensaje -->
<div id="wp-whatsapp-message-modal" class="wp-whatsapp-modal" style="display: none;">
    <div class="wp-whatsapp-modal-content">
        <div class="wp-whatsapp-modal-header">
            <h3><?php _e('Probar Mensaje', 'wp-whatsapp-business'); ?></h3>
            <button type="button" class="wp-whatsapp-modal-close">&times;</button>
        </div>
        <div class="wp-whatsapp-modal-body">
            <p><?php _e('Envía un mensaje de prueba para verificar la configuración:', 'wp-whatsapp-business'); ?></p>
            <textarea class="wp-whatsapp-message-input" placeholder="<?php _e('Escribe tu mensaje de prueba aquí...', 'wp-whatsapp-business'); ?>" rows="4"></textarea>
            <div class="wp-whatsapp-modal-actions">
                <button type="button" class="button button-primary wp-whatsapp-send-test-message">
                    <?php _e('Enviar Mensaje', 'wp-whatsapp-business'); ?>
                </button>
                <button type="button" class="button wp-whatsapp-modal-cancel">
                    <?php _e('Cancelar', 'wp-whatsapp-business'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para la página de configuración */
.wp-whatsapp-form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--wp-whatsapp-border);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.wp-whatsapp-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wp-whatsapp-modal-content {
    background: #fff;
    border-radius: var(--wp-whatsapp-radius);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.wp-whatsapp-modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--wp-whatsapp-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wp-whatsapp-modal-header h3 {
    margin: 0;
    color: var(--wp-whatsapp-primary);
}

.wp-whatsapp-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--wp-whatsapp-text-light);
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wp-whatsapp-modal-close:hover {
    color: var(--wp-whatsapp-text);
}

.wp-whatsapp-modal-body {
    padding: 20px;
}

.wp-whatsapp-modal-body p {
    margin-bottom: 15px;
    color: var(--wp-whatsapp-text);
}

.wp-whatsapp-phone-input,
.wp-whatsapp-message-input {
    width: 100%;
    margin-bottom: 20px;
}

.wp-whatsapp-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 600px) {
    .wp-whatsapp-modal-content {
        width: 95%;
        margin: 10px;
    }
    
    .wp-whatsapp-modal-actions {
        flex-direction: column;
    }
    
    .wp-whatsapp-form-actions {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Inicializar pestañas
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).data('tab');
        if (!target) return;

        // Actualizar pestañas activas
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Mostrar contenido correspondiente
        $('.tab-content').removeClass('active');
        $('#' + target + '-tab').addClass('active');
    });

    // Testing de teléfono
    $('.wp-whatsapp-test-phone').on('click', function() {
        $('#wp-whatsapp-test-modal').show();
    });

    // Testing de mensaje
    $('.wp-whatsapp-test-message').on('click', function() {
        $('#wp-whatsapp-message-modal').show();
    });

    // Cerrar modales
    $('.wp-whatsapp-modal-close, .wp-whatsapp-modal-cancel').on('click', function() {
        $('.wp-whatsapp-modal').hide();
    });

    // Cerrar modal al hacer clic fuera
    $('.wp-whatsapp-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    // Confirmar testing de teléfono
    $('.wp-whatsapp-test-phone-confirm').on('click', function() {
        var phone = $('.wp-whatsapp-phone-input').val();
        if (!phone) {
            alert('<?php _e('Por favor ingresa un número de teléfono', 'wp-whatsapp-business'); ?>');
            return;
        }

        // Aquí iría la lógica AJAX para probar la conexión
        $('#wp-whatsapp-test-modal').hide();
    });

    // Enviar mensaje de prueba
    $('.wp-whatsapp-send-test-message').on('click', function() {
        var message = $('.wp-whatsapp-message-input').val();
        if (!message) {
            alert('<?php _e('Por favor ingresa un mensaje', 'wp-whatsapp-business'); ?>');
            return;
        }

        // Aquí iría la lógica AJAX para enviar el mensaje
        $('#wp-whatsapp-message-modal').hide();
    });
});
</script> 