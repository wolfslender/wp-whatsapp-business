<?php
/**
 * Widget de WhatsApp para el frontend
 *
 * @package WPWhatsAppBusiness\Frontend
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Frontend;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\WhatsAppService;

/**
 * Clase del widget flotante de WhatsApp
 */
class Widget {

    /**
     * Loader de hooks
     *
     * @var Loader
     */
    private $loader;

    /**
     * Servicio de WhatsApp
     *
     * @var WhatsAppService
     */
    private $whatsapp_service;

    /**
     * Constructor
     *
     * @param WhatsAppService $whatsapp_service Servicio de WhatsApp
     */
    public function __construct(WhatsAppService $whatsapp_service) {
        $this->whatsapp_service = $whatsapp_service;
        $this->loader = new Loader();
        $this->init();
    }

    /**
     * Inicializar el widget
     *
     * @return void
     */
    private function init(): void {
        $this->registerHooks();
        $this->loader->run();
    }

    /**
     * Registrar hooks del widget
     *
     * @return void
     */
    private function registerHooks(): void {
        // Renderizar widget en el footer
        $this->loader->addAction('wp_footer', $this, 'renderWidget');
        
        // Agregar estilos inline
        $this->loader->addAction('wp_head', $this, 'addInlineStyles');
        
        // Agregar scripts inline
        $this->loader->addAction('wp_footer', $this, 'addInlineScripts');
        
        // Verificar si debe mostrar el widget
        $this->loader->addFilter('wp_whatsapp_business_should_show_widget', $this, 'shouldShowWidget');
    }

    /**
     * Renderizar el widget
     *
     * @return void
     */
    public function renderWidget(): void {
        if (!$this->shouldShowWidget()) {
            return;
        }

        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        $phone_number = $settings['phone_number'] ?? '';
        $business_name = $settings['business_name'] ?? '';
        $widget_text = $settings['widget_text'] ?? __('¿Necesitas ayuda? ¡Chatea con nosotros!', 'wp-whatsapp-business');
        $position = $settings['widget_position'] ?? 'bottom-right';

        if (empty($phone_number)) {
            return;
        }

        // Obtener mensaje personalizado del post actual
        $custom_message = '';
        if (is_singular()) {
            $post_id = get_the_ID();
            $custom_message = get_post_meta($post_id, '_wp_whatsapp_business_message', true);
        }

        // Si no hay mensaje personalizado, usar el mensaje por defecto
        if (empty($custom_message)) {
            $custom_message = $widget_text;
        }

        // Construir URL de WhatsApp
        $whatsapp_url = $this->whatsapp_service->buildWhatsAppUrl($phone_number, $custom_message);

        ?>
        <div id="wp-whatsapp-business-widget" class="wp-whatsapp-business-widget wp-whatsapp-business-<?php echo esc_attr($position); ?>">
            <a href="<?php echo esc_url($whatsapp_url); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="wp-whatsapp-business-button"
               aria-label="<?php echo esc_attr(sprintf(__('Chatear con %s en WhatsApp', 'wp-whatsapp-business'), $business_name)); ?>">
                
                <div class="wp-whatsapp-business-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                </div>
                
                <div class="wp-whatsapp-business-text">
                    <span class="wp-whatsapp-business-title"><?php echo esc_html($business_name); ?></span>
                    <span class="wp-whatsapp-business-subtitle"><?php echo esc_html($custom_message); ?></span>
                </div>
            </a>
        </div>
        <?php
    }

    /**
     * Agregar estilos inline
     *
     * @return void
     */
    public function addInlineStyles(): void {
        if (!$this->shouldShowWidget()) {
            return;
        }

        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        $position = $settings['widget_position'] ?? 'bottom-right';
        
        ?>
        <style id="wp-whatsapp-business-widget-styles">
            .wp-whatsapp-business-widget {
                position: fixed;
                z-index: 999999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 14px;
                line-height: 1.4;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.3s ease;
                max-width: 300px;
            }

            .wp-whatsapp-business-widget:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            }

            .wp-whatsapp-business-bottom-right {
                bottom: 20px;
                right: 20px;
            }

            .wp-whatsapp-business-bottom-left {
                bottom: 20px;
                left: 20px;
            }

            .wp-whatsapp-business-top-right {
                top: 20px;
                right: 20px;
            }

            .wp-whatsapp-business-top-left {
                top: 20px;
                left: 20px;
            }

            .wp-whatsapp-business-button {
                display: flex;
                align-items: center;
                padding: 12px 16px;
                background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                transition: all 0.3s ease;
            }

            .wp-whatsapp-business-button:hover {
                background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
                color: white;
                text-decoration: none;
            }

            .wp-whatsapp-business-icon {
                flex-shrink: 0;
                margin-right: 12px;
                width: 24px;
                height: 24px;
            }

            .wp-whatsapp-business-text {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }

            .wp-whatsapp-business-title {
                font-weight: 600;
                font-size: 13px;
                margin-bottom: 2px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .wp-whatsapp-business-subtitle {
                font-size: 11px;
                opacity: 0.9;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            @media (max-width: 768px) {
                .wp-whatsapp-business-widget {
                    bottom: 10px !important;
                    right: 10px !important;
                    left: auto !important;
                    top: auto !important;
                    max-width: 280px;
                }
            }

            @media (max-width: 480px) {
                .wp-whatsapp-business-widget {
                    max-width: 260px;
                }
                
                .wp-whatsapp-business-button {
                    padding: 10px 14px;
                }
                
                .wp-whatsapp-business-icon {
                    width: 20px;
                    height: 20px;
                    margin-right: 10px;
                }
                
                .wp-whatsapp-business-title {
                    font-size: 12px;
                }
                
                .wp-whatsapp-business-subtitle {
                    font-size: 10px;
                }
            }
        </style>
        <?php
    }

    /**
     * Agregar scripts inline
     *
     * @return void
     */
    public function addInlineScripts(): void {
        if (!$this->shouldShowWidget()) {
            return;
        }

        ?>
        <script id="wp-whatsapp-business-widget-script">
        (function() {
            'use strict';
            
            // Función para manejar el clic en el widget
            function handleWidgetClick(event) {
                const widget = event.currentTarget;
                const url = widget.href;
                
                // Agregar efecto de clic
                widget.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    widget.style.transform = '';
                }, 150);
                
                // Abrir WhatsApp en nueva ventana
                window.open(url, '_blank', 'noopener,noreferrer');
                
                // Prevenir comportamiento por defecto
                event.preventDefault();
            }
            
            // Función para inicializar el widget
            function initWidget() {
                const widget = document.getElementById('wp-whatsapp-business-widget');
                if (widget) {
                    const button = widget.querySelector('.wp-whatsapp-business-button');
                    if (button) {
                        button.addEventListener('click', handleWidgetClick);
                    }
                }
            }
            
            // Inicializar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWidget);
            } else {
                initWidget();
            }
            
            // Función para ocultar/mostrar el widget al hacer scroll
            let lastScrollTop = 0;
            let widget = null;
            
            function handleScroll() {
                if (!widget) {
                    widget = document.getElementById('wp-whatsapp-business-widget');
                }
                
                if (!widget) return;
                
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Ocultar widget al hacer scroll hacia abajo, mostrar al hacer scroll hacia arriba
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    widget.style.transform = 'translateY(100px)';
                    widget.style.opacity = '0';
                } else {
                    widget.style.transform = '';
                    widget.style.opacity = '1';
                }
                
                lastScrollTop = scrollTop;
            }
            
            // Agregar listener de scroll
            window.addEventListener('scroll', handleScroll, { passive: true });
            
        })();
        </script>
        <?php
    }

    /**
     * Verificar si debe mostrar el widget
     *
     * @return bool
     */
    public function shouldShowWidget(): bool {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return false;
        }

        // Verificar si estamos en el admin
        if (is_admin()) {
            return false;
        }

        // Verificar si estamos en una página de login
        if (is_login()) {
            return false;
        }

        // Verificar si estamos en una página de registro
        if (is_register()) {
            return false;
        }

        // Verificar si estamos en una página de recuperación de contraseña
        if (is_reset_password()) {
            return false;
        }

        // Verificar si el post actual tiene el widget deshabilitado
        if (is_singular()) {
            $post_id = get_the_ID();
            $disabled = get_post_meta($post_id, '_wp_whatsapp_business_enabled', true);
            if ($disabled === '0') {
                return false;
            }
        }

        // Verificar horarios de negocio
        if (!$this->isBusinessHours()) {
            return false;
        }

        // Permitir filtros personalizados
        return apply_filters('wp_whatsapp_business_should_show_widget', true);
    }

    /**
     * Verificar si estamos en horario de negocio
     *
     * @return bool
     */
    private function isBusinessHours(): bool {
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        $business_hours = $settings['business_hours'] ?? [];

        if (empty($business_hours)) {
            return true; // Si no hay horarios configurados, mostrar siempre
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
     * Obtener el loader de hooks
     *
     * @return Loader
     */
    public function getLoader(): Loader {
        return $this->loader;
    }
} 