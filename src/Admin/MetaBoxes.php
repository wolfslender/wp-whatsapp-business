<?php
/**
 * Meta boxes del plugin
 *
 * @package WPWhatsAppBusiness\Admin
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Admin;

use WPWhatsAppBusiness\Core\Loader;
use WPWhatsAppBusiness\Services\ConfigService;

/**
 * Clase para manejar los meta boxes del plugin
 */
class MetaBoxes {

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
     * Inicializar meta boxes
     *
     * @return void
     */
    private function init(): void {
        $this->registerHooks();
        $this->loader->run();
    }

    /**
     * Registrar hooks de meta boxes
     *
     * @return void
     */
    private function registerHooks(): void {
        // Agregar meta boxes
        $this->loader->addAction('add_meta_boxes', $this, 'addMetaBoxes');
        
        // Guardar meta datos
        $this->loader->addAction('save_post', $this, 'saveMetaData');
        
        // Agregar columnas personalizadas
        $this->loader->addFilter('manage_posts_columns', $this, 'addCustomColumns');
        $this->loader->addFilter('manage_pages_columns', $this, 'addCustomColumns');
        
        // Mostrar contenido de columnas personalizadas
        $this->loader->addAction('manage_posts_custom_column', $this, 'showCustomColumnContent', 10, 2);
        $this->loader->addAction('manage_pages_custom_column', $this, 'showCustomColumnContent', 10, 2);
        
        // Hacer columnas ordenables
        $this->loader->addFilter('manage_edit-post_sortable_columns', $this, 'makeColumnsSortable');
        $this->loader->addFilter('manage_edit-page_sortable_columns', $this, 'makeColumnsSortable');
    }

    /**
     * Agregar meta boxes
     *
     * @return void
     */
    public function addMetaBoxes(): void {
        $post_types = ['post', 'page'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wp_whatsapp_business_meta',
                __('WhatsApp Business', 'wp-whatsapp-business'),
                [$this, 'renderMetaBox'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Renderizar meta box
     *
     * @param \WP_Post $post Post actual
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void {
        wp_nonce_field('wp_whatsapp_business_meta', 'wp_whatsapp_business_meta_nonce');
        
        $enabled = get_post_meta($post->ID, '_wp_whatsapp_business_enabled', true);
        $message = get_post_meta($post->ID, '_wp_whatsapp_business_message', true);
        $custom_phone = get_post_meta($post->ID, '_wp_whatsapp_business_phone', true);
        
        // Obtener configuración global
        $settings = $this->config_service->getSettings();
        $default_message = $settings['widget_text'] ?? __('¿Necesitas ayuda? ¡Chatea con nosotros!', 'wp-whatsapp-business');
        $default_phone = $settings['phone_number'] ?? '';
        
        ?>
        <div class="wp-whatsapp-business-meta-box">
            <!-- Habilitar WhatsApp -->
            <p>
                <label for="wp_whatsapp_business_enabled">
                    <input type="checkbox" 
                           id="wp_whatsapp_business_enabled" 
                           name="wp_whatsapp_business_enabled" 
                           value="1" 
                           <?php checked($enabled, '1'); ?> />
                    <?php _e('Mostrar botón de WhatsApp en esta página', 'wp-whatsapp-business'); ?>
                </label>
            </p>

            <!-- Mensaje personalizado -->
            <p>
                <label for="wp_whatsapp_business_message">
                    <strong><?php _e('Mensaje personalizado:', 'wp-whatsapp-business'); ?></strong>
                </label>
                <textarea id="wp_whatsapp_business_message" 
                          name="wp_whatsapp_business_message" 
                          rows="3" 
                          cols="25" 
                          placeholder="<?php echo esc_attr($default_message); ?>"
                          class="widefat"><?php echo esc_textarea($message); ?></textarea>
                <small><?php _e('Deja vacío para usar el mensaje por defecto.', 'wp-whatsapp-business'); ?></small>
            </p>

            <!-- Número de teléfono personalizado -->
            <p>
                <label for="wp_whatsapp_business_phone">
                    <strong><?php _e('Número de teléfono personalizado:', 'wp-whatsapp-business'); ?></strong>
                </label>
                <input type="text" 
                       id="wp_whatsapp_business_phone" 
                       name="wp_whatsapp_business_phone" 
                       value="<?php echo esc_attr($custom_phone); ?>" 
                       placeholder="<?php echo esc_attr($default_phone); ?>"
                       class="widefat" />
                <small><?php _e('Deja vacío para usar el número por defecto.', 'wp-whatsapp-business'); ?></small>
            </p>

            <!-- Vista previa -->
            <div class="wp-whatsapp-business-preview">
                <h4><?php _e('Vista previa:', 'wp-whatsapp-business'); ?></h4>
                <div class="preview-content">
                    <p><strong><?php _e('Mensaje:', 'wp-whatsapp-business'); ?></strong> 
                       <span id="preview-message"><?php echo esc_html($message ?: $default_message); ?></span></p>
                    <p><strong><?php _e('Teléfono:', 'wp-whatsapp-business'); ?></strong> 
                       <span id="preview-phone"><?php echo esc_html($custom_phone ?: $default_phone); ?></span></p>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="wp-whatsapp-business-info">
                <p><small>
                    <?php _e('Estos ajustes solo afectan a esta página específica. Los ajustes globales se configuran en la página de configuración del plugin.', 'wp-whatsapp-business'); ?>
                </small></p>
            </div>
        </div>

        <style>
        .wp-whatsapp-business-meta-box {
            padding: 10px 0;
        }
        
        .wp-whatsapp-business-meta-box label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        
        .wp-whatsapp-business-meta-box textarea,
        .wp-whatsapp-business-meta-box input[type="text"] {
            margin-bottom: 5px;
        }
        
        .wp-whatsapp-business-meta-box small {
            color: #666;
            font-style: italic;
        }
        
        .wp-whatsapp-business-preview {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .wp-whatsapp-business-preview h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .preview-content p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .wp-whatsapp-business-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Actualizar vista previa en tiempo real
            $('#wp_whatsapp_business_message').on('input', function() {
                var message = $(this).val();
                if (!message) {
                    message = '<?php echo esc_js($default_message); ?>';
                }
                $('#preview-message').text(message);
            });
            
            $('#wp_whatsapp_business_phone').on('input', function() {
                var phone = $(this).val();
                if (!phone) {
                    phone = '<?php echo esc_js($default_phone); ?>';
                }
                $('#preview-phone').text(phone);
            });
        });
        </script>
        <?php
    }

    /**
     * Guardar meta datos
     *
     * @param int $post_id ID del post
     * @return void
     */
    public function saveMetaData(int $post_id): void {
        // Verificar nonce
        if (!isset($_POST['wp_whatsapp_business_meta_nonce']) || 
            !wp_verify_nonce($_POST['wp_whatsapp_business_meta_nonce'], 'wp_whatsapp_business_meta')) {
            return;
        }

        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Verificar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Guardar meta datos
        $enabled = isset($_POST['wp_whatsapp_business_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_wp_whatsapp_business_enabled', $enabled);

        if (isset($_POST['wp_whatsapp_business_message'])) {
            $message = sanitize_textarea_field($_POST['wp_whatsapp_business_message']);
            update_post_meta($post_id, '_wp_whatsapp_business_message', $message);
        }

        if (isset($_POST['wp_whatsapp_business_phone'])) {
            $phone = sanitize_text_field($_POST['wp_whatsapp_business_phone']);
            update_post_meta($post_id, '_wp_whatsapp_business_phone', $phone);
        }
    }

    /**
     * Agregar columnas personalizadas
     *
     * @param array $columns Columnas existentes
     * @return array
     */
    public function addCustomColumns(array $columns): array {
        $columns['whatsapp_status'] = __('WhatsApp', 'wp-whatsapp-business');
        return $columns;
    }

    /**
     * Mostrar contenido de columnas personalizadas
     *
     * @param string $column Nombre de la columna
     * @param int $post_id ID del post
     * @return void
     */
    public function showCustomColumnContent(string $column, int $post_id): void {
        if ($column === 'whatsapp_status') {
            $enabled = get_post_meta($post_id, '_wp_whatsapp_business_enabled', true);
            $message = get_post_meta($post_id, '_wp_whatsapp_business_message', true);
            $phone = get_post_meta($post_id, '_wp_whatsapp_business_phone', true);
            
            if ($enabled === '1') {
                echo '<span style="color: #25D366; font-weight: bold;">✓ ' . __('Habilitado', 'wp-whatsapp-business') . '</span>';
                
                if ($message) {
                    echo '<br><small style="color: #666;">' . __('Mensaje personalizado', 'wp-whatsapp-business') . '</small>';
                }
                
                if ($phone) {
                    echo '<br><small style="color: #666;">' . __('Teléfono personalizado', 'wp-whatsapp-business') . '</small>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        }
    }

    /**
     * Hacer columnas ordenables
     *
     * @param array $columns Columnas ordenables
     * @return array
     */
    public function makeColumnsSortable(array $columns): array {
        $columns['whatsapp_status'] = 'whatsapp_status';
        return $columns;
    }

    /**
     * Obtener configuración de WhatsApp para un post específico
     *
     * @param int $post_id ID del post
     * @return array
     */
    public function getPostWhatsAppConfig(int $post_id): array {
        $enabled = get_post_meta($post_id, '_wp_whatsapp_business_enabled', true);
        $message = get_post_meta($post_id, '_wp_whatsapp_business_message', true);
        $phone = get_post_meta($post_id, '_wp_whatsapp_business_phone', true);
        
        // Obtener configuración global
        $settings = $this->config_service->getSettings();
        
        return [
            'enabled' => $enabled === '1',
            'message' => $message ?: ($settings['widget_text'] ?? ''),
            'phone' => $phone ?: ($settings['phone_number'] ?? '')
        ];
    }

    /**
     * Verificar si un post tiene configuración personalizada de WhatsApp
     *
     * @param int $post_id ID del post
     * @return bool
     */
    public function hasCustomWhatsAppConfig(int $post_id): bool {
        $enabled = get_post_meta($post_id, '_wp_whatsapp_business_enabled', true);
        $message = get_post_meta($post_id, '_wp_whatsapp_business_message', true);
        $phone = get_post_meta($post_id, '_wp_whatsapp_business_phone', true);
        
        return $enabled === '1' || !empty($message) || !empty($phone);
    }

    /**
     * Obtener estadísticas de uso de WhatsApp por posts
     *
     * @return array
     */
    public function getWhatsAppUsageStats(): array {
        global $wpdb;
        
        $stats = [
            'total_posts' => 0,
            'enabled_posts' => 0,
            'custom_messages' => 0,
            'custom_phones' => 0
        ];
        
        // Contar posts con WhatsApp habilitado
        $enabled_posts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
                '_wp_whatsapp_business_enabled',
                '1'
            )
        );
        
        // Contar posts con mensajes personalizados
        $custom_messages = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != %s",
                '_wp_whatsapp_business_message',
                ''
            )
        );
        
        // Contar posts con teléfonos personalizados
        $custom_phones = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != %s",
                '_wp_whatsapp_business_phone',
                ''
            )
        );
        
        // Contar total de posts
        $total_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page')"
        );
        
        return [
            'total_posts' => (int) $total_posts,
            'enabled_posts' => (int) $enabled_posts,
            'custom_messages' => (int) $custom_messages,
            'custom_phones' => (int) $custom_phones
        ];
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