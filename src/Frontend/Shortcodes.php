<?php
/**
 * Shortcodes del plugin
 *
 * @package WPWhatsAppBusiness\Frontend
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Frontend;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\WhatsAppService;

/**
 * Clase para manejar los shortcodes del plugin
 */
class Shortcodes {

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
     * Inicializar shortcodes
     *
     * @return void
     */
    private function init(): void {
        $this->registerShortcodes();
        $this->loader->run();
    }

    /**
     * Registrar shortcodes
     *
     * @return void
     */
    private function registerShortcodes(): void {
        // Shortcode principal de WhatsApp
        $this->loader->addAction('init', $this, 'registerWhatsAppShortcode');
        
        // Shortcode de botón de WhatsApp
        $this->loader->addAction('init', $this, 'registerWhatsAppButtonShortcode');
        
        // Shortcode de número de teléfono
        $this->loader->addAction('init', $this, 'registerPhoneNumberShortcode');
        
        // Shortcode de horarios de negocio
        $this->loader->addAction('init', $this, 'registerBusinessHoursShortcode');
        
        // Shortcode de estado del negocio
        $this->loader->addAction('init', $this, 'registerBusinessStatusShortcode');
    }

    /**
     * Registrar shortcode principal de WhatsApp
     *
     * @return void
     */
    public function registerWhatsAppShortcode(): void {
        add_shortcode('whatsapp', [$this, 'renderWhatsAppShortcode']);
    }

    /**
     * Registrar shortcode de botón de WhatsApp
     *
     * @return void
     */
    public function registerWhatsAppButtonShortcode(): void {
        add_shortcode('whatsapp_button', [$this, 'renderWhatsAppButtonShortcode']);
    }

    /**
     * Registrar shortcode de número de teléfono
     *
     * @return void
     */
    public function registerPhoneNumberShortcode(): void {
        add_shortcode('whatsapp_phone', [$this, 'renderPhoneNumberShortcode']);
    }

    /**
     * Registrar shortcode de horarios de negocio
     *
     * @return void
     */
    public function registerBusinessHoursShortcode(): void {
        add_shortcode('whatsapp_hours', [$this, 'renderBusinessHoursShortcode']);
    }

    /**
     * Registrar shortcode de estado del negocio
     *
     * @return void
     */
    public function registerBusinessStatusShortcode(): void {
        add_shortcode('whatsapp_status', [$this, 'renderBusinessStatusShortcode']);
    }

    /**
     * Renderizar shortcode principal de WhatsApp
     *
     * @param array $atts Atributos del shortcode
     * @param string $content Contenido del shortcode
     * @return string
     */
    public function renderWhatsAppShortcode($atts, $content = ''): string {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return '';
        }

        // Parsear atributos
        $atts = shortcode_atts([
            'phone' => $settings['phone_number'] ?? '',
            'message' => $settings['widget_text'] ?? __('¿Necesitas ayuda? ¡Chatea con nosotros!', 'wp-whatsapp-business'),
            'text' => __('Chatear en WhatsApp', 'wp-whatsapp-business'),
            'class' => 'whatsapp-link',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'icon' => 'true',
            'size' => 'medium',
            'color' => '#25D366'
        ], $atts, 'whatsapp');

        // Verificar si hay número de teléfono
        if (empty($atts['phone'])) {
            return '';
        }

        // Construir URL de WhatsApp
        $whatsapp_url = $this->whatsapp_service->buildWhatsAppUrl($atts['phone'], $atts['message']);

        // Preparar clases CSS
        $classes = ['whatsapp-shortcode', $atts['class']];
        if ($atts['size'] !== 'medium') {
            $classes[] = 'whatsapp-size-' . $atts['size'];
        }

        // Preparar estilos inline
        $styles = '';
        if ($atts['color'] !== '#25D366') {
            $styles = ' style="background-color: ' . esc_attr($atts['color']) . ';"';
        }

        // Construir HTML
        $html = '<a href="' . esc_url($whatsapp_url) . '" ';
        $html .= 'class="' . esc_attr(implode(' ', $classes)) . '" ';
        $html .= 'target="' . esc_attr($atts['target']) . '" ';
        $html .= 'rel="' . esc_attr($atts['rel']) . '"';
        $html .= $styles . '>';

        // Agregar icono si está habilitado
        if ($atts['icon'] === 'true') {
            $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">';
            $html .= '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>';
            $html .= '</svg>';
        }

        // Agregar texto
        $html .= esc_html($atts['text']);

        $html .= '</a>';

        return $html;
    }

    /**
     * Renderizar shortcode de botón de WhatsApp
     *
     * @param array $atts Atributos del shortcode
     * @param string $content Contenido del shortcode
     * @return string
     */
    public function renderWhatsAppButtonShortcode($atts, $content = ''): string {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return '';
        }

        // Parsear atributos
        $atts = shortcode_atts([
            'phone' => $settings['phone_number'] ?? '',
            'message' => $settings['widget_text'] ?? __('¿Necesitas ayuda? ¡Chatea con nosotros!', 'wp-whatsapp-business'),
            'text' => __('Chatear en WhatsApp', 'wp-whatsapp-business'),
            'class' => 'whatsapp-button',
            'size' => 'medium',
            'color' => '#25D366',
            'rounded' => 'true'
        ], $atts, 'whatsapp_button');

        // Verificar si hay número de teléfono
        if (empty($atts['phone'])) {
            return '';
        }

        // Construir URL de WhatsApp
        $whatsapp_url = $this->whatsapp_service->buildWhatsAppUrl($atts['phone'], $atts['message']);

        // Preparar clases CSS
        $classes = ['whatsapp-button-shortcode', $atts['class']];
        if ($atts['size'] !== 'medium') {
            $classes[] = 'whatsapp-button-size-' . $atts['size'];
        }
        if ($atts['rounded'] === 'true') {
            $classes[] = 'whatsapp-button-rounded';
        }

        // Preparar estilos inline
        $styles = 'background-color: ' . esc_attr($atts['color']) . ';';

        // Construir HTML
        $html = '<a href="' . esc_url($whatsapp_url) . '" ';
        $html .= 'class="' . esc_attr(implode(' ', $classes)) . '" ';
        $html .= 'target="_blank" rel="noopener noreferrer" ';
        $html .= 'style="' . $styles . '">';

        // Agregar icono
        $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">';
        $html .= '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>';
        $html .= '</svg>';

        // Agregar texto
        $html .= esc_html($atts['text']);

        $html .= '</a>';

        return $html;
    }

    /**
     * Renderizar shortcode de número de teléfono
     *
     * @param array $atts Atributos del shortcode
     * @param string $content Contenido del shortcode
     * @return string
     */
    public function renderPhoneNumberShortcode($atts, $content = ''): string {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return '';
        }

        // Parsear atributos
        $atts = shortcode_atts([
            'phone' => $settings['phone_number'] ?? '',
            'format' => 'display', // display, link, both
            'class' => 'whatsapp-phone'
        ], $atts, 'whatsapp_phone');

        // Verificar si hay número de teléfono
        if (empty($atts['phone'])) {
            return '';
        }

        // Formatear número para mostrar
        $display_number = $this->formatPhoneNumberForDisplay($atts['phone']);

        $html = '';

        if ($atts['format'] === 'display' || $atts['format'] === 'both') {
            $html .= '<span class="' . esc_attr($atts['class']) . '">' . esc_html($display_number) . '</span>';
        }

        if ($atts['format'] === 'link' || $atts['format'] === 'both') {
            $whatsapp_url = $this->whatsapp_service->buildWhatsAppUrl($atts['phone']);
            $html .= '<a href="' . esc_url($whatsapp_url) . '" target="_blank" rel="noopener noreferrer" class="' . esc_attr($atts['class']) . '-link">' . esc_html($display_number) . '</a>';
        }

        return $html;
    }

    /**
     * Renderizar shortcode de horarios de negocio
     *
     * @param array $atts Atributos del shortcode
     * @param string $content Contenido del shortcode
     * @return string
     */
    public function renderBusinessHoursShortcode($atts, $content = ''): string {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return '';
        }

        // Parsear atributos
        $atts = shortcode_atts([
            'format' => 'table', // table, list, inline
            'class' => 'whatsapp-hours',
            'show_today' => 'true',
            'highlight_today' => 'true'
        ], $atts, 'whatsapp_hours');

        $business_hours = $settings['business_hours'] ?? [];
        if (empty($business_hours)) {
            return '';
        }

        $html = '<div class="' . esc_attr($atts['class']) . '">';

        if ($atts['format'] === 'table') {
            $html .= $this->renderBusinessHoursTable($business_hours, $atts);
        } elseif ($atts['format'] === 'list') {
            $html .= $this->renderBusinessHoursList($business_hours, $atts);
        } else {
            $html .= $this->renderBusinessHoursInline($business_hours, $atts);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Renderizar shortcode de estado del negocio
     *
     * @param array $atts Atributos del shortcode
     * @param string $content Contenido del shortcode
     * @return string
     */
    public function renderBusinessStatusShortcode($atts, $content = ''): string {
        // Verificar si el plugin está habilitado
        $settings = $this->whatsapp_service->getConfigService()->getSettings();
        if (empty($settings['enabled'])) {
            return '';
        }

        // Parsear atributos
        $atts = shortcode_atts([
            'class' => 'whatsapp-status',
            'show_icon' => 'true',
            'text_open' => __('Abierto', 'wp-whatsapp-business'),
            'text_closed' => __('Cerrado', 'wp-whatsapp-business')
        ], $atts, 'whatsapp_status');

        $is_open = $this->whatsapp_service->getConfigService()->isBusinessOpen();

        $html = '<span class="' . esc_attr($atts['class']) . ' whatsapp-status-' . ($is_open ? 'open' : 'closed') . '">';

        if ($atts['show_icon'] === 'true') {
            $icon = $is_open ? '✓' : '✗';
            $html .= '<span class="whatsapp-status-icon">' . $icon . '</span> ';
        }

        $text = $is_open ? $atts['text_open'] : $atts['text_closed'];
        $html .= '<span class="whatsapp-status-text">' . esc_html($text) . '</span>';

        $html .= '</span>';

        return $html;
    }

    /**
     * Renderizar tabla de horarios de negocio
     *
     * @param array $business_hours Horarios de negocio
     * @param array $atts Atributos del shortcode
     * @return string
     */
    private function renderBusinessHoursTable(array $business_hours, array $atts): string {
        $html = '<table class="whatsapp-hours-table">';
        $html .= '<thead><tr><th>' . __('Día', 'wp-whatsapp-business') . '</th><th>' . __('Horario', 'wp-whatsapp-business') . '</th></tr></thead>';
        $html .= '<tbody>';

        $days = [
            'monday' => __('Lunes', 'wp-whatsapp-business'),
            'tuesday' => __('Martes', 'wp-whatsapp-business'),
            'wednesday' => __('Miércoles', 'wp-whatsapp-business'),
            'thursday' => __('Jueves', 'wp-whatsapp-business'),
            'friday' => __('Viernes', 'wp-whatsapp-business'),
            'saturday' => __('Sábado', 'wp-whatsapp-business'),
            'sunday' => __('Domingo', 'wp-whatsapp-business')
        ];

        $current_day = strtolower(date('l'));
        $current_day_key = array_search($current_day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);

        foreach ($days as $day_key => $day_name) {
            $is_today = ($day_key === $current_day_key);
            $row_class = $is_today && $atts['highlight_today'] === 'true' ? ' class="today"' : '';

            $html .= '<tr' . $row_class . '>';
            $html .= '<td>' . esc_html($day_name) . '</td>';
            $html .= '<td>';

            if (isset($business_hours[$day_key])) {
                $hours = $business_hours[$day_key];
                if ($hours === 'closed') {
                    $html .= '<span class="closed">' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
                } else {
                    $html .= esc_html($hours[0] . ' - ' . $hours[1]);
                }
            } else {
                $html .= '<span class="closed">' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
            }

            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Renderizar lista de horarios de negocio
     *
     * @param array $business_hours Horarios de negocio
     * @param array $atts Atributos del shortcode
     * @return string
     */
    private function renderBusinessHoursList(array $business_hours, array $atts): string {
        $html = '<ul class="whatsapp-hours-list">';

        $days = [
            'monday' => __('Lunes', 'wp-whatsapp-business'),
            'tuesday' => __('Martes', 'wp-whatsapp-business'),
            'wednesday' => __('Miércoles', 'wp-whatsapp-business'),
            'thursday' => __('Jueves', 'wp-whatsapp-business'),
            'friday' => __('Viernes', 'wp-whatsapp-business'),
            'saturday' => __('Sábado', 'wp-whatsapp-business'),
            'sunday' => __('Domingo', 'wp-whatsapp-business')
        ];

        $current_day = strtolower(date('l'));
        $current_day_key = array_search($current_day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);

        foreach ($days as $day_key => $day_name) {
            $is_today = ($day_key === $current_day_key);
            $item_class = $is_today && $atts['highlight_today'] === 'true' ? ' class="today"' : '';

            $html .= '<li' . $item_class . '>';
            $html .= '<strong>' . esc_html($day_name) . ':</strong> ';

            if (isset($business_hours[$day_key])) {
                $hours = $business_hours[$day_key];
                if ($hours === 'closed') {
                    $html .= '<span class="closed">' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
                } else {
                    $html .= esc_html($hours[0] . ' - ' . $hours[1]);
                }
            } else {
                $html .= '<span class="closed">' . __('Cerrado', 'wp-whatsapp-business') . '</span>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Renderizar horarios de negocio en línea
     *
     * @param array $business_hours Horarios de negocio
     * @param array $atts Atributos del shortcode
     * @return string
     */
    private function renderBusinessHoursInline(array $business_hours, array $atts): string {
        $html = '<span class="whatsapp-hours-inline">';

        $current_day = strtolower(date('l'));
        $current_day_key = array_search($current_day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);

        if (isset($business_hours[$current_day_key])) {
            $hours = $business_hours[$current_day_key];
            if ($hours === 'closed') {
                $html .= '<span class="closed">' . __('Cerrado hoy', 'wp-whatsapp-business') . '</span>';
            } else {
                $html .= sprintf(
                    __('Abierto hoy: %s - %s', 'wp-whatsapp-business'),
                    esc_html($hours[0]),
                    esc_html($hours[1])
                );
            }
        } else {
            $html .= '<span class="closed">' . __('Cerrado hoy', 'wp-whatsapp-business') . '</span>';
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Formatear número de teléfono para mostrar
     *
     * @param string $phone_number Número de teléfono
     * @return string
     */
    private function formatPhoneNumberForDisplay(string $phone_number): string {
        // Remover el + del inicio
        $number = ltrim($phone_number, '+');
        
        // Formatear según la longitud
        if (strlen($number) === 10) {
            return '(' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6);
        } elseif (strlen($number) === 11) {
            return '(' . substr($number, 0, 2) . ') ' . substr($number, 2, 5) . '-' . substr($number, 7);
        } else {
            return $phone_number;
        }
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