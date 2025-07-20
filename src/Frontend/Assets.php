<?php
/**
 * Gestión de assets del frontend
 *
 * @package WPWhatsAppBusiness\Frontend
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Frontend;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\ConfigService;

/**
 * Clase para gestionar los assets del frontend
 */
class Assets {

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
     * Constructor
     *
     * @param ConfigService $config_service Servicio de configuración
     */
    public function __construct(ConfigService $config_service) {
        $this->config_service = $config_service;
        $this->loader = new Loader();
        $this->init();
    }

    /**
     * Inicializar assets
     *
     * @return void
     */
    private function init(): void {
        $this->registerHooks();
        $this->loader->run();
    }

    /**
     * Registrar hooks de assets
     *
     * @return void
     */
    private function registerHooks(): void {
        // Cargar estilos y scripts del frontend
        $this->loader->addAction('wp_enqueue_scripts', $this, 'enqueueFrontendAssets');
        
        // Cargar estilos y scripts de shortcodes
        $this->loader->addAction('wp_enqueue_scripts', $this, 'enqueueShortcodeAssets');
        
        // Agregar estilos inline personalizados
        $this->loader->addAction('wp_head', $this, 'addCustomStyles');
        
        // Agregar scripts inline personalizados
        $this->loader->addAction('wp_footer', $this, 'addCustomScripts');
    }

    /**
     * Cargar assets del frontend
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void {
        // Verificar si el plugin está habilitado
        $settings = $this->config_service->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }

        // Cargar estilos del frontend
        wp_enqueue_style(
            'wp-whatsapp-business-frontend',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            WP_WHATSAPP_BUSINESS_VERSION
        );

        // Cargar scripts del frontend
        wp_enqueue_script(
            'wp-whatsapp-business-frontend',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            WP_WHATSAPP_BUSINESS_VERSION,
            true
        );

        // Localizar script con configuración
        wp_localize_script('wp-whatsapp-business-frontend', 'wpWhatsAppBusiness', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_whatsapp_business_frontend_nonce'),
            'config' => $this->config_service->getJsConfig(),
            'strings' => [
                'loading' => __('Cargando...', 'wp-whatsapp-business'),
                'error' => __('Error', 'wp-whatsapp-business'),
                'success' => __('Éxito', 'wp-whatsapp-business')
            ]
        ]);
    }

    /**
     * Cargar assets de shortcodes
     *
     * @return void
     */
    public function enqueueShortcodeAssets(): void {
        // Verificar si el plugin está habilitado
        $settings = $this->config_service->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }

        // Cargar estilos de shortcodes
        wp_enqueue_style(
            'wp-whatsapp-business-shortcodes',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/css/shortcodes.css',
            [],
            WP_WHATSAPP_BUSINESS_VERSION
        );

        // Cargar scripts de shortcodes
        wp_enqueue_script(
            'wp-whatsapp-business-shortcodes',
            WP_WHATSAPP_BUSINESS_PLUGIN_URL . 'assets/js/shortcodes.js',
            ['jquery'],
            WP_WHATSAPP_BUSINESS_VERSION,
            true
        );
    }

    /**
     * Agregar estilos personalizados
     *
     * @return void
     */
    public function addCustomStyles(): void {
        // Verificar si el plugin está habilitado
        $settings = $this->config_service->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }

        $appearance = $settings['appearance'] ?? [];
        $custom_css = '';

        // Estilos personalizados para el widget
        if (!empty($appearance['widget_color'])) {
            $custom_css .= '
            .wp-whatsapp-business-widget .wp-whatsapp-business-button {
                background: linear-gradient(135deg, ' . esc_attr($appearance['widget_color']) . ' 0%, ' . $this->darkenColor($appearance['widget_color'], 20) . ' 100%) !important;
            }
            ';
        }

        if (!empty($appearance['widget_text_color'])) {
            $custom_css .= '
            .wp-whatsapp-business-widget .wp-whatsapp-business-button {
                color: ' . esc_attr($appearance['widget_text_color']) . ' !important;
            }
            ';
        }

        // Estilos para shortcodes
        $custom_css .= '
        .whatsapp-shortcode {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .whatsapp-shortcode:hover {
            background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .whatsapp-button-shortcode {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.2);
        }

        .whatsapp-button-shortcode:hover {
            background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
        }

        .whatsapp-button-rounded {
            border-radius: 50px;
        }

        .whatsapp-button-size-small {
            padding: 8px 16px;
            font-size: 14px;
        }

        .whatsapp-button-size-large {
            padding: 16px 32px;
            font-size: 18px;
        }

        .whatsapp-phone {
            font-weight: 600;
            color: #25D366;
        }

        .whatsapp-phone-link {
            color: #25D366;
            text-decoration: none;
            font-weight: 600;
        }

        .whatsapp-phone-link:hover {
            color: #128C7E;
            text-decoration: underline;
        }

        .whatsapp-hours-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .whatsapp-hours-table th,
        .whatsapp-hours-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .whatsapp-hours-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .whatsapp-hours-table .today {
            background-color: #e8f5e8;
            font-weight: 600;
        }

        .whatsapp-hours-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .whatsapp-hours-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .whatsapp-hours-list .today {
            background-color: #e8f5e8;
            padding: 8px 12px;
            margin: 0 -12px;
            border-radius: 4px;
            font-weight: 600;
        }

        .whatsapp-hours-inline {
            font-weight: 600;
            color: #25D366;
        }

        .whatsapp-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .whatsapp-status-open {
            background-color: #e8f5e8;
            color: #2e7d32;
        }

        .whatsapp-status-closed {
            background-color: #ffebee;
            color: #c62828;
        }

        .whatsapp-status-icon {
            margin-right: 4px;
            font-weight: bold;
        }

        .closed {
            color: #999;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .whatsapp-hours-table {
                font-size: 14px;
            }

            .whatsapp-hours-table th,
            .whatsapp-hours-table td {
                padding: 8px;
            }

            .whatsapp-button-shortcode {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
        ';

        if (!empty($custom_css)) {
            echo '<style id="wp-whatsapp-business-custom-styles">' . $custom_css . '</style>';
        }
    }

    /**
     * Agregar scripts personalizados
     *
     * @return void
     */
    public function addCustomScripts(): void {
        // Verificar si el plugin está habilitado
        $settings = $this->config_service->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }

        ?>
        <script id="wp-whatsapp-business-custom-scripts">
        (function() {
            'use strict';
            
            // Función para manejar clics en enlaces de WhatsApp
            function handleWhatsAppLinks() {
                const whatsappLinks = document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp.com"], .whatsapp-shortcode, .whatsapp-button-shortcode');
                
                whatsappLinks.forEach(function(link) {
                    link.addEventListener('click', function(event) {
                        // Agregar efecto de clic
                        this.style.transform = 'scale(0.95)';
                        setTimeout(function() {
                            this.style.transform = '';
                        }.bind(this), 150);
                        
                        // Abrir en nueva ventana
                        window.open(this.href, '_blank', 'noopener,noreferrer');
                        event.preventDefault();
                    });
                });
            }
            
            // Función para inicializar cuando el DOM esté listo
            function init() {
                handleWhatsAppLinks();
                
                // Agregar efectos hover a botones
                const buttons = document.querySelectorAll('.whatsapp-button-shortcode, .whatsapp-shortcode');
                buttons.forEach(function(button) {
                    button.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    
                    button.addEventListener('mouseleave', function() {
                        this.style.transform = '';
                    });
                });
            }
            
            // Inicializar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
            
        })();
        </script>
        <?php
    }

    /**
     * Oscurecer un color
     *
     * @param string $color Color en formato hexadecimal
     * @param int $percent Porcentaje a oscurecer
     * @return string
     */
    private function darkenColor(string $color, int $percent): string {
        // Remover # si existe
        $color = ltrim($color, '#');
        
        // Convertir a RGB
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        
        // Oscurecer
        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));
        
        // Convertir de vuelta a hexadecimal
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
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