<?php
/**
 * Servicio de validación
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

/**
 * Clase para validar datos del plugin
 */
class ValidationService {

    /**
     * Validar número de teléfono
     *
     * @param string $phone_number Número de teléfono a validar
     * @return bool
     */
    public function isValidPhoneNumber(string $phone_number): bool {
        // Remover espacios y caracteres especiales
        $clean_number = preg_replace('/[^0-9+]/', '', $phone_number);
        
        // Verificar formato básico
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $clean_number)) {
            return false;
        }
        
        // Verificar longitud mínima (código de país + número)
        if (strlen($clean_number) < 8) {
            return false;
        }
        
        // Verificar longitud máxima (según estándar E.164)
        if (strlen($clean_number) > 16) {
            return false;
        }
        
        return true;
    }

    /**
     * Validar URL
     *
     * @param string $url URL a validar
     * @return bool
     */
    public function isValidUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar email
     *
     * @param string $email Email a validar
     * @return bool
     */
    public function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar API Key de WhatsApp
     *
     * @param string $api_key API Key a validar
     * @return bool
     */
    public function isValidWhatsAppApiKey(string $api_key): bool {
        // Verificar que no esté vacía
        if (empty($api_key)) {
            return false;
        }
        
        // Verificar formato básico (debe ser una cadena alfanumérica)
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $api_key)) {
            return false;
        }
        
        // Verificar longitud mínima
        if (strlen($api_key) < 10) {
            return false;
        }
        
        return true;
    }

    /**
     * Validar configuración del plugin
     *
     * @param array $settings Configuración a validar
     * @return array Array con errores de validación
     */
    public function validateSettings(array $settings): array {
        $errors = [];

        // Validar API Key
        if (!empty($settings['api_key'])) {
            if (!$this->isValidWhatsAppApiKey($settings['api_key'])) {
                $errors['api_key'] = __('La API Key no tiene un formato válido', 'wp-whatsapp-business');
            }
        }

        // Validar número de teléfono
        if (!empty($settings['phone_number'])) {
            if (!$this->isValidPhoneNumber($settings['phone_number'])) {
                $errors['phone_number'] = __('El número de teléfono no tiene un formato válido', 'wp-whatsapp-business');
            }
        }

        // Validar nombre del negocio
        if (!empty($settings['business_name'])) {
            if (strlen($settings['business_name']) > 100) {
                $errors['business_name'] = __('El nombre del negocio no puede tener más de 100 caracteres', 'wp-whatsapp-business');
            }
        }

        // Validar email de administrador
        if (!empty($settings['notification_settings']['admin_email'])) {
            if (!$this->isValidEmail($settings['notification_settings']['admin_email'])) {
                $errors['admin_email'] = __('El email de administrador no es válido', 'wp-whatsapp-business');
            }
        }

        // Validar horarios de negocio
        if (!empty($settings['business_hours'])) {
            $hours_errors = $this->validateBusinessHours($settings['business_hours']);
            if (!empty($hours_errors)) {
                $errors['business_hours'] = $hours_errors;
            }
        }

        return $errors;
    }

    /**
     * Validar horarios de negocio
     *
     * @param array $business_hours Horarios a validar
     * @return array Array con errores de validación
     */
    public function validateBusinessHours(array $business_hours): array {
        $errors = [];
        $valid_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($business_hours as $day => $hours) {
            // Verificar que el día sea válido
            if (!in_array($day, $valid_days)) {
                $errors[] = sprintf(__('Día inválido: %s', 'wp-whatsapp-business'), $day);
                continue;
            }

            // Si está cerrado, no necesitamos validar más
            if ($hours === 'closed') {
                continue;
            }

            // Verificar que sea un array
            if (!is_array($hours)) {
                $errors[] = sprintf(__('Formato inválido para %s', 'wp-whatsapp-business'), $day);
                continue;
            }

            // Validar formato de hora
            foreach ($hours as $hour) {
                if (!$this->isValidTimeFormat($hour)) {
                    $errors[] = sprintf(__('Formato de hora inválido para %s: %s', 'wp-whatsapp-business'), $day, $hour);
                }
            }

            // Verificar que haya al menos 2 horas (apertura y cierre)
            if (count($hours) < 2) {
                $errors[] = sprintf(__('Debe especificar hora de apertura y cierre para %s', 'wp-whatsapp-business'), $day);
            }
        }

        return $errors;
    }

    /**
     * Validar formato de hora
     *
     * @param string $time Hora a validar (formato HH:MM)
     * @return bool
     */
    public function isValidTimeFormat(string $time): bool {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
    }

    /**
     * Validar mensaje de WhatsApp
     *
     * @param string $message Mensaje a validar
     * @return array Array con errores de validación
     */
    public function validateWhatsAppMessage(string $message): array {
        $errors = [];

        // Verificar que no esté vacío
        if (empty(trim($message))) {
            $errors[] = __('El mensaje no puede estar vacío', 'wp-whatsapp-business');
        }

        // Verificar longitud máxima
        if (strlen($message) > 4096) {
            $errors[] = __('El mensaje no puede tener más de 4096 caracteres', 'wp-whatsapp-business');
        }

        // Verificar caracteres especiales
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $message)) {
            $errors[] = __('El mensaje contiene caracteres no permitidos', 'wp-whatsapp-business');
        }

        return $errors;
    }

    /**
     * Validar datos de botones para mensajes interactivos
     *
     * @param array $buttons Botones a validar
     * @return array Array con errores de validación
     */
    public function validateButtons(array $buttons): array {
        $errors = [];

        // Verificar número de botones
        if (empty($buttons)) {
            $errors[] = __('Debe especificar al menos un botón', 'wp-whatsapp-business');
        }

        if (count($buttons) > 3) {
            $errors[] = __('No puede tener más de 3 botones', 'wp-whatsapp-business');
        }

        foreach ($buttons as $index => $button) {
            // Verificar estructura del botón
            if (!isset($button['type']) || !isset($button['reply'])) {
                $errors[] = sprintf(__('Botón %d: estructura inválida', 'wp-whatsapp-business'), $index + 1);
                continue;
            }

            // Verificar tipo de botón
            if ($button['type'] !== 'reply') {
                $errors[] = sprintf(__('Botón %d: tipo inválido', 'wp-whatsapp-business'), $index + 1);
            }

            // Verificar texto del botón
            if (empty($button['reply']['title'])) {
                $errors[] = sprintf(__('Botón %d: título requerido', 'wp-whatsapp-business'), $index + 1);
            }

            if (strlen($button['reply']['title']) > 20) {
                $errors[] = sprintf(__('Botón %d: título demasiado largo', 'wp-whatsapp-business'), $index + 1);
            }

            if (strlen($button['reply']['id']) > 256) {
                $errors[] = sprintf(__('Botón %d: ID demasiado largo', 'wp-whatsapp-business'), $index + 1);
            }
        }

        return $errors;
    }

    /**
     * Validar datos de lista para mensajes interactivos
     *
     * @param array $sections Secciones a validar
     * @return array Array con errores de validación
     */
    public function validateListSections(array $sections): array {
        $errors = [];

        // Verificar que haya al menos una sección
        if (empty($sections)) {
            $errors[] = __('Debe especificar al menos una sección', 'wp-whatsapp-business');
        }

        foreach ($sections as $index => $section) {
            // Verificar estructura de la sección
            if (!isset($section['title']) || !isset($section['rows'])) {
                $errors[] = sprintf(__('Sección %d: estructura inválida', 'wp-whatsapp-business'), $index + 1);
                continue;
            }

            // Verificar título de la sección
            if (strlen($section['title']) > 24) {
                $errors[] = sprintf(__('Sección %d: título demasiado largo', 'wp-whatsapp-business'), $index + 1);
            }

            // Verificar filas
            if (empty($section['rows'])) {
                $errors[] = sprintf(__('Sección %d: debe tener al menos una fila', 'wp-whatsapp-business'), $index + 1);
            }

            if (count($section['rows']) > 10) {
                $errors[] = sprintf(__('Sección %d: demasiadas filas', 'wp-whatsapp-business'), $index + 1);
            }

            foreach ($section['rows'] as $row_index => $row) {
                if (!isset($row['id']) || !isset($row['title'])) {
                    $errors[] = sprintf(__('Sección %d, Fila %d: estructura inválida', 'wp-whatsapp-business'), $index + 1, $row_index + 1);
                }

                if (strlen($row['title']) > 24) {
                    $errors[] = sprintf(__('Sección %d, Fila %d: título demasiado largo', 'wp-whatsapp-business'), $index + 1, $row_index + 1);
                }

                if (strlen($row['id']) > 200) {
                    $errors[] = sprintf(__('Sección %d, Fila %d: ID demasiado largo', 'wp-whatsapp-business'), $index + 1, $row_index + 1);
                }
            }
        }

        return $errors;
    }

    /**
     * Validar URL de imagen
     *
     * @param string $url URL de la imagen
     * @return array Array con errores de validación
     */
    public function validateImageUrl(string $url): array {
        $errors = [];

        // Validar formato de URL
        if (!$this->isValidUrl($url)) {
            $errors[] = __('URL de imagen inválida', 'wp-whatsapp-business');
            return $errors;
        }

        // Verificar extensión de imagen
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_extensions)) {
            $errors[] = __('Formato de imagen no soportado', 'wp-whatsapp-business');
        }

        return $errors;
    }

    /**
     * Validar URL de documento
     *
     * @param string $url URL del documento
     * @return array Array con errores de validación
     */
    public function validateDocumentUrl(string $url): array {
        $errors = [];

        // Validar formato de URL
        if (!$this->isValidUrl($url)) {
            $errors[] = __('URL del documento inválida', 'wp-whatsapp-business');
            return $errors;
        }

        // Verificar extensión de documento
        $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_extensions)) {
            $errors[] = __('Formato de documento no soportado', 'wp-whatsapp-business');
        }

        return $errors;
    }

    /**
     * Sanitizar número de teléfono
     *
     * @param string $phone_number Número de teléfono a sanitizar
     * @return string
     */
    public function sanitizePhoneNumber(string $phone_number): string {
        // Remover todos los caracteres no numéricos excepto el +
        $sanitized = preg_replace('/[^0-9+]/', '', $phone_number);
        
        // Asegurar que tenga el código de país
        if (!str_starts_with($sanitized, '+')) {
            $sanitized = '+' . $sanitized;
        }
        
        return $sanitized;
    }

    /**
     * Sanitizar mensaje
     *
     * @param string $message Mensaje a sanitizar
     * @return string
     */
    public function sanitizeMessage(string $message): string {
        // Remover caracteres de control
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message);
        
        // Limpiar espacios en blanco
        $sanitized = trim($sanitized);
        
        // Limitar longitud
        if (strlen($sanitized) > 4096) {
            $sanitized = substr($sanitized, 0, 4096);
        }
        
        return $sanitized;
    }

    /**
     * Sanitizar configuración
     *
     * @param array $settings Configuración a sanitizar
     * @return array
     */
    public function sanitizeSettings(array $settings): array {
        $sanitized = [];

        // Sanitizar campos básicos
        if (isset($settings['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($settings['api_key']);
        }

        if (isset($settings['phone_number'])) {
            $sanitized['phone_number'] = $this->sanitizePhoneNumber($settings['phone_number']);
        }

        if (isset($settings['business_name'])) {
            $sanitized['business_name'] = sanitize_text_field($settings['business_name']);
        }

        if (isset($settings['enabled'])) {
            $sanitized['enabled'] = (bool) $settings['enabled'];
        }

        if (isset($settings['widget_position'])) {
            $valid_positions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
            $sanitized['widget_position'] = in_array($settings['widget_position'], $valid_positions) 
                ? $settings['widget_position'] 
                : 'bottom-right';
        }

        if (isset($settings['widget_text'])) {
            $sanitized['widget_text'] = $this->sanitizeMessage($settings['widget_text']);
        }

        // Sanitizar horarios de negocio
        if (isset($settings['business_hours']) && is_array($settings['business_hours'])) {
            $sanitized['business_hours'] = $this->sanitizeBusinessHours($settings['business_hours']);
        }

        return $sanitized;
    }

    /**
     * Sanitizar horarios de negocio
     *
     * @param array $business_hours Horarios a sanitizar
     * @return array
     */
    private function sanitizeBusinessHours(array $business_hours): array {
        $sanitized = [];
        $valid_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($business_hours as $day => $hours) {
            if (!in_array($day, $valid_days)) {
                continue;
            }

            if ($hours === 'closed') {
                $sanitized[$day] = 'closed';
                continue;
            }

            if (is_array($hours)) {
                $sanitized_hours = [];
                foreach ($hours as $hour) {
                    if ($this->isValidTimeFormat($hour)) {
                        $sanitized_hours[] = $hour;
                    }
                }
                if (!empty($sanitized_hours)) {
                    $sanitized[$day] = $sanitized_hours;
                }
            }
        }

        return $sanitized;
    }
} 