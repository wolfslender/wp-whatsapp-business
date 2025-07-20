<?php
/**
 * Página de ayuda del admin
 *
 * @package WPWhatsAppBusiness\Admin\views
 * @since 1.0.0
 */

// Verificar acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wp-whatsapp-help">
        <!-- Guía de inicio rápido -->
        <div class="wp-whatsapp-quick-start">
            <h2><?php _e('Guía de Inicio Rápido', 'wp-whatsapp-business'); ?></h2>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3><?php _e('Configurar WhatsApp Business API', 'wp-whatsapp-business'); ?></h3>
                        <p><?php _e('Primero necesitas crear una cuenta de WhatsApp Business API en Facebook Developer Console.', 'wp-whatsapp-business'); ?></p>
                        <ol>
                            <li><?php _e('Ve a <a href="https://developers.facebook.com" target="_blank">Facebook Developer Console</a>', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Crea una nueva aplicación o usa una existente', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Agrega el producto WhatsApp Business API', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Configura tu número de teléfono de WhatsApp Business', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Obtén tu API Key y Phone Number ID', 'wp-whatsapp-business'); ?></li>
                        </ol>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3><?php _e('Configurar el Plugin', 'wp-whatsapp-business'); ?></h3>
                        <p><?php _e('Una vez que tengas tus credenciales de API, configúralas en el plugin.', 'wp-whatsapp-business'); ?></p>
                        <ol>
                            <li><?php _e('Ve a la página principal del plugin', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Ingresa tu API Key de WhatsApp Business', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Ingresa tu Phone Number ID', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Configura tu número de teléfono en formato internacional', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Habilita el plugin', 'wp-whatsapp-business'); ?></li>
                        </ol>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3><?php _e('Personalizar el Widget', 'wp-whatsapp-business'); ?></h3>
                        <p><?php _e('Personaliza la apariencia y comportamiento del widget de WhatsApp.', 'wp-whatsapp-business'); ?></p>
                        <ol>
                            <li><?php _e('Configura la posición del widget (bottom-right, bottom-left, etc.)', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Personaliza el color y tamaño', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Configura el texto que aparece en el widget', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Define los horarios de negocio', 'wp-whatsapp-business'); ?></li>
                        </ol>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3><?php _e('Probar la Integración', 'wp-whatsapp-business'); ?></h3>
                        <p><?php _e('Verifica que todo funcione correctamente enviando un mensaje de prueba.', 'wp-whatsapp-business'); ?></p>
                        <ol>
                            <li><?php _e('Ve a la página de Mensajes', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Envía un mensaje de prueba a tu número', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Verifica que el widget aparezca en tu sitio', 'wp-whatsapp-business'); ?></li>
                            <li><?php _e('Prueba los shortcodes en tus páginas', 'wp-whatsapp-business'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcodes disponibles -->
        <div class="wp-whatsapp-shortcodes">
            <h2><?php _e('Shortcodes Disponibles', 'wp-whatsapp-business'); ?></h2>
            
            <div class="shortcodes-grid">
                <div class="shortcode-card">
                    <h3><?php _e('Shortcode Básico', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp]</code>
                    <p><?php _e('Muestra el botón de WhatsApp con la configuración por defecto.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="shortcode-card">
                    <h3><?php _e('Shortcode con Parámetros', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp phone="+1234567890" message="¡Hola! ¿Necesitas ayuda?" text="Chatear ahora"]</code>
                    <p><?php _e('Personaliza el número, mensaje y texto del botón.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="shortcode-card">
                    <h3><?php _e('Botón de WhatsApp', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp_button phone="+1234567890" text="Contactar por WhatsApp" size="large" color="#25D366"]</code>
                    <p><?php _e('Crea un botón personalizado con estilo específico.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="shortcode-card">
                    <h3><?php _e('Número de Teléfono', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp_phone format="link"]</code>
                    <p><?php _e('Muestra el número de teléfono configurado como enlace de WhatsApp.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="shortcode-card">
                    <h3><?php _e('Horarios de Negocio', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp_hours format="table"]</code>
                    <p><?php _e('Muestra los horarios de negocio en formato tabla.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="shortcode-card">
                    <h3><?php _e('Estado del Negocio', 'wp-whatsapp-business'); ?></h3>
                    <code>[whatsapp_status]</code>
                    <p><?php _e('Muestra si el negocio está abierto o cerrado actualmente.', 'wp-whatsapp-business'); ?></p>
                </div>
            </div>
        </div>

        <!-- Preguntas frecuentes -->
        <div class="wp-whatsapp-faq">
            <h2><?php _e('Preguntas Frecuentes', 'wp-whatsapp-business'); ?></h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <h3><?php _e('¿Cómo obtengo mi API Key de WhatsApp Business?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Para obtener tu API Key, necesitas crear una aplicación en Facebook Developer Console y configurar WhatsApp Business API. Sigue la guía de inicio rápido para más detalles.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h3><?php _e('¿El widget aparece en todas las páginas?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Por defecto, el widget aparece en todas las páginas del frontend. Puedes deshabilitarlo para páginas específicas usando los meta boxes en el editor de posts y páginas.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h3><?php _e('¿Puedo personalizar el mensaje por página?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Sí, puedes configurar mensajes personalizados para cada página usando los meta boxes en el editor de posts y páginas, o usando los parámetros de los shortcodes.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h3><?php _e('¿El plugin funciona en móviles?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Sí, el plugin detecta automáticamente el tipo de dispositivo y genera URLs apropiadas para WhatsApp Web (desktop) o WhatsApp móvil.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h3><?php _e('¿Hay límites en el envío de mensajes?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Sí, el plugin incluye rate limiting configurable para evitar exceder los límites de la API de WhatsApp Business. Puedes configurar los límites en la configuración avanzada.', 'wp-whatsapp-business'); ?></p>
                </div>
                
                <div class="faq-item">
                    <h3><?php _e('¿Puedo usar múltiples números de WhatsApp?', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Actualmente el plugin soporta un número principal configurado globalmente. Para múltiples números, puedes usar los parámetros de los shortcodes para especificar números diferentes.', 'wp-whatsapp-business'); ?></p>
                </div>
            </div>
        </div>

        <!-- Recursos adicionales -->
        <div class="wp-whatsapp-resources">
            <h2><?php _e('Recursos Adicionales', 'wp-whatsapp-business'); ?></h2>
            
            <div class="resources-grid">
                <div class="resource-card">
                    <h3><?php _e('Documentación de WhatsApp Business API', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Documentación oficial de la API de WhatsApp Business para desarrolladores.', 'wp-whatsapp-business'); ?></p>
                    <a href="https://developers.facebook.com/docs/whatsapp" target="_blank" class="button">
                        <?php _e('Ver Documentación', 'wp-whatsapp-business'); ?>
                    </a>
                </div>
                
                <div class="resource-card">
                    <h3><?php _e('Facebook Developer Console', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('Plataforma para crear y gestionar aplicaciones de Facebook y WhatsApp Business.', 'wp-whatsapp-business'); ?></p>
                    <a href="https://developers.facebook.com" target="_blank" class="button">
                        <?php _e('Ir a Developer Console', 'wp-whatsapp-business'); ?>
                    </a>
                </div>
                
                <div class="resource-card">
                    <h3><?php _e('Soporte Técnico', 'wp-whatsapp-business'); ?></h3>
                    <p><?php _e('¿Necesitas ayuda? Contacta con nuestro equipo de soporte técnico.', 'wp-whatsapp-business'); ?></p>
                    <a href="mailto:oliverodevs@hotmail.com" class="button">
                        <?php _e('Contactar Soporte', 'wp-whatsapp-business'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Información de contacto -->
        <div class="wp-whatsapp-contact">
            <h2><?php _e('Información de Contacto', 'wp-whatsapp-business'); ?></h2>
            
            <div class="contact-info">
                <div class="contact-item">
                    <strong><?php _e('Email:', 'wp-whatsapp-business'); ?></strong>
                    <a href="mailto:oliverodevs@hotmail.com">oliverodevs@hotmail.com</a>
                </div>
                
                <div class="contact-item">
                    <strong><?php _e('WhatsApp:', 'wp-whatsapp-business'); ?></strong>
                    <a href="https://wa.me/18299832502" target="_blank">+1 (829) 983-2502</a>
                </div>
                
                <div class="contact-item">
                    <strong><?php _e('Documentación:', 'wp-whatsapp-business'); ?></strong>
                    <span><?php _e('Coming Soon', 'wp-whatsapp-business'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wp-whatsapp-help {
    margin-top: 20px;
}

.wp-whatsapp-quick-start,
.wp-whatsapp-shortcodes,
.wp-whatsapp-faq,
.wp-whatsapp-resources,
.wp-whatsapp-contact {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.steps-container {
    margin-top: 15px;
}

.step {
    display: flex;
    margin-bottom: 30px;
    align-items: flex-start;
}

.step-number {
    background: #0073aa;
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    margin-right: 20px;
    flex-shrink: 0;
}

.step-content h3 {
    margin-top: 0;
    color: #0073aa;
}

.step-content ol {
    margin-left: 20px;
}

.step-content li {
    margin-bottom: 5px;
}

.shortcodes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.shortcode-card {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
}

.shortcode-card code {
    background: #fff;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    display: block;
    margin: 10px 0;
    word-break: break-all;
}

.faq-container {
    margin-top: 15px;
}

.faq-item {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.faq-item h3 {
    color: #0073aa;
    margin-bottom: 10px;
}

.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.resource-card {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.resource-card h3 {
    margin-top: 0;
    color: #0073aa;
}

.resource-card p {
    margin-bottom: 15px;
    color: #666;
}

.contact-info {
    margin-top: 15px;
}

.contact-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    margin-bottom: 10px;
}

.contact-item a {
    color: #0073aa;
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}
</style> 