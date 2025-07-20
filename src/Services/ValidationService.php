<?php
/**
 * Servicio de validación
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

use WPWhatsAppBusiness\Services\Interfaces\ValidationServiceInterface;

/**
 * Servicio de validación completo
 */
class ValidationService implements ValidationServiceInterface
{
    private array $errors = [];
    private array $validationSchema = [];

    public function __construct()
    {
        $this->initializeValidationSchema();
    }

    public function validatePhoneNumber(string $phoneNumber, string $countryCode = ''): array
    {
        $this->clearErrors();
        
        // Validación básica de formato E.164
        if (empty($phoneNumber)) {
            $this->addError('phone_number', 'El número de teléfono es requerido');
            return $this->getErrors();
        }

        // Remover espacios y caracteres especiales
        $cleanNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Validar formato E.164
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $cleanNumber)) {
            $this->addError('phone_number', 'El número debe estar en formato E.164 (+1234567890)');
        }

        // Validar longitud
        if (strlen($cleanNumber) < 8 || strlen($cleanNumber) > 16) {
            $this->addError('phone_number', 'El número debe tener entre 8 y 16 dígitos');
        }

        // Validar código de país si se proporciona
        if (!empty($countryCode)) {
            $this->validateCountryCode($cleanNumber, $countryCode);
        }

        return $this->getErrors();
    }

    public function validateMessage(string $message, array $options = []): array
    {
        $this->clearErrors();
        
        $maxLength = $options['max_length'] ?? 1000;
        $minLength = $options['min_length'] ?? 1;
        $allowHtml = $options['allow_html'] ?? false;
        $allowEmojis = $options['allow_emojis'] ?? true;

        if (empty($message)) {
            $this->addError('message', 'El mensaje es requerido');
            return $this->getErrors();
        }

        // Validar longitud
        $messageLength = mb_strlen($message);
        if ($messageLength < $minLength) {
            $this->addError('message', "El mensaje debe tener al menos {$minLength} caracteres");
        }

        if ($messageLength > $maxLength) {
            $this->addError('message', "El mensaje no puede exceder {$maxLength} caracteres");
        }

        // Validar HTML si no está permitido
        if (!$allowHtml && strip_tags($message) !== $message) {
            $this->addError('message', 'El HTML no está permitido en el mensaje');
        }

        // Validar emojis si no están permitidos
        if (!$allowEmojis && $this->containsEmojis($message)) {
            $this->addError('message', 'Los emojis no están permitidos en el mensaje');
        }

        // Validar caracteres especiales peligrosos
        if ($this->containsDangerousCharacters($message)) {
            $this->addError('message', 'El mensaje contiene caracteres no permitidos');
        }

        return $this->getErrors();
    }

    public function validateBusinessHours(array $businessHours): array
    {
        $this->clearErrors();
        
        $requiredDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($requiredDays as $day) {
            if (!isset($businessHours[$day])) {
                $this->addError("business_hours.{$day}", "Los horarios para {$day} son requeridos");
                continue;
            }

            $dayHours = $businessHours[$day];
            
            // Validar estructura
            if (!is_array($dayHours)) {
                $this->addError("business_hours.{$day}", "Formato inválido para {$day}");
                continue;
            }

            // Validar campos requeridos
            $requiredFields = ['open', 'close', 'enabled'];
            foreach ($requiredFields as $field) {
                if (!isset($dayHours[$field])) {
                    $this->addError("business_hours.{$day}.{$field}", "El campo {$field} es requerido para {$day}");
                }
            }

            // Validar formato de tiempo
            if (isset($dayHours['open']) && !$this->isValidTimeFormat($dayHours['open'])) {
                $this->addError("business_hours.{$day}.open", "Formato de hora inválido para {$day}");
            }

            if (isset($dayHours['close']) && !$this->isValidTimeFormat($dayHours['close'])) {
                $this->addError("business_hours.{$day}.close", "Formato de hora inválido para {$day}");
            }

            // Validar que enabled sea booleano
            if (isset($dayHours['enabled']) && !is_bool($dayHours['enabled'])) {
                $this->addError("business_hours.{$day}.enabled", "El campo enabled debe ser true o false para {$day}");
            }

            // Validar lógica de horarios si está habilitado
            if (isset($dayHours['enabled']) && $dayHours['enabled'] && 
                isset($dayHours['open']) && isset($dayHours['close'])) {
                
                if ($dayHours['open'] === $dayHours['close'] && $dayHours['open'] !== '00:00') {
                    $this->addError("business_hours.{$day}", "Los horarios de apertura y cierre no pueden ser iguales para {$day}");
                }
            }
        }

        return $this->getErrors();
    }

    public function validateColor(string $color, string $type = 'hex'): array
    {
        $this->clearErrors();
        
        if (empty($color)) {
            $this->addError('color', 'El color es requerido');
            return $this->getErrors();
        }

        switch ($type) {
            case 'hex':
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                    $this->addError('color', 'Formato de color hexadecimal inválido');
                }
                break;
                
            case 'rgb':
                if (!preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', $color)) {
                    $this->addError('color', 'Formato de color RGB inválido');
                }
                break;
                
            case 'css':
                // Validar nombres de colores CSS válidos
                $validCssColors = [
                    'black', 'white', 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta',
                    'gray', 'grey', 'orange', 'purple', 'pink', 'brown', 'lime', 'navy',
                    'teal', 'silver', 'gold', 'maroon', 'olive', 'aqua', 'fuchsia'
                ];
                
                if (!in_array(strtolower($color), $validCssColors) && 
                    !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) &&
                    !preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', $color)) {
                    $this->addError('color', 'Color CSS inválido');
                }
                break;
                
            default:
                $this->addError('color', 'Tipo de validación de color inválido');
        }

        return $this->getErrors();
    }

    public function validateFileUpload(array $file, array $options = []): array
    {
        $this->clearErrors();
        
        $maxSize = $options['max_size'] ?? 5242880; // 5MB por defecto
        $allowedTypes = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif'];
        $maxWidth = $options['max_width'] ?? 1920;
        $maxHeight = $options['max_height'] ?? 1080;

        // Validar estructura del archivo
        if (!isset($file['tmp_name']) || !isset($file['name']) || !isset($file['size'])) {
            $this->addError('file', 'Estructura de archivo inválida');
            return $this->getErrors();
        }

        // Validar si se subió correctamente
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->addError('file', 'El archivo no se subió correctamente');
        }

        // Validar tamaño
        if ($file['size'] > $maxSize) {
            $this->addError('file', "El archivo excede el tamaño máximo permitido (" . $this->formatBytes($maxSize) . ")");
        }

        // Validar tipo de archivo
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $this->addError('file', "Tipo de archivo no permitido. Tipos válidos: " . implode(', ', $allowedTypes));
        }

        // Validar contenido del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];

        if (isset($allowedMimeTypes[$fileExtension]) && $mimeType !== $allowedMimeTypes[$fileExtension]) {
            $this->addError('file', 'El contenido del archivo no coincide con su extensión');
        }

        // Validar dimensiones si es una imagen
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $this->addError('file', 'No se pudo obtener información de la imagen');
            } else {
                list($width, $height) = $imageInfo;
                if ($width > $maxWidth || $height > $maxHeight) {
                    $this->addError('file', "Las dimensiones de la imagen exceden el máximo permitido ({$maxWidth}x{$maxHeight})");
                }
            }
        }

        return $this->getErrors();
    }

    public function validateUrl(string $url, array $options = []): array
    {
        $this->clearErrors();
        
        $allowedSchemes = $options['allowed_schemes'] ?? ['http', 'https'];
        $maxLength = $options['max_length'] ?? 2048;

        if (empty($url)) {
            $this->addError('url', 'La URL es requerida');
            return $this->getErrors();
        }

        // Validar longitud
        if (strlen($url) > $maxLength) {
            $this->addError('url', "La URL no puede exceder {$maxLength} caracteres");
        }

        // Validar formato básico
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->addError('url', 'Formato de URL inválido');
        } else {
            // Validar esquema
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], $allowedSchemes)) {
                $this->addError('url', "Esquema no permitido. Esquemas válidos: " . implode(', ', $allowedSchemes));
            }

            // Validar host
            if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
                $this->addError('url', 'La URL debe tener un host válido');
            }
        }

        return $this->getErrors();
    }

    public function validateEmail(string $email): array
    {
        $this->clearErrors();
        
        if (empty($email)) {
            $this->addError('email', 'El email es requerido');
            return $this->getErrors();
        }

        // Validar formato básico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Formato de email inválido');
        } else {
            // Validaciones adicionales
            $parts = explode('@', $email);
            $localPart = $parts[0];
            $domain = $parts[1];

            // Validar longitud de parte local
            if (strlen($localPart) > 64) {
                $this->addError('email', 'La parte local del email es demasiado larga');
            }

            // Validar longitud del dominio
            if (strlen($domain) > 253) {
                $this->addError('email', 'El dominio del email es demasiado largo');
            }

            // Validar caracteres especiales en parte local
            if (!preg_match('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+$/', $localPart)) {
                $this->addError('email', 'La parte local del email contiene caracteres no permitidos');
            }
        }

        return $this->getErrors();
    }

    public function validateConfig(array $config): array
    {
        $this->clearErrors();
        
        $schema = $this->getValidationSchema();
        
        foreach ($schema as $field => $rules) {
            $value = $this->getNestedValue($config, $field);
            
            // Validar campo requerido
            if (isset($rules['required']) && $rules['required'] && empty($value)) {
                $this->addError($field, "El campo {$field} es requerido");
                continue;
            }

            // Si el campo no es requerido y está vacío, continuar
            if (empty($value)) {
                continue;
            }

            // Validar tipo
            if (isset($rules['type'])) {
                $this->validateType($field, $value, $rules['type']);
            }

            // Validar longitud mínima
            if (isset($rules['min_length']) && is_string($value)) {
                if (mb_strlen($value) < $rules['min_length']) {
                    $this->addError($field, "El campo {$field} debe tener al menos {$rules['min_length']} caracteres");
                }
            }

            // Validar longitud máxima
            if (isset($rules['max_length']) && is_string($value)) {
                if (mb_strlen($value) > $rules['max_length']) {
                    $this->addError($field, "El campo {$field} no puede exceder {$rules['max_length']} caracteres");
                }
            }

            // Validar patrón
            if (isset($rules['pattern']) && is_string($value)) {
                if (!preg_match($rules['pattern'], $value)) {
                    $this->addError($field, "El campo {$field} no cumple con el formato requerido");
                }
            }
        }

        return $this->getErrors();
    }

    public function sanitize($input, string $type = 'text')
    {
        switch ($type) {
            case 'text':
                return sanitize_text_field($input);
                
            case 'textarea':
                return sanitize_textarea_field($input);
                
            case 'email':
                return sanitize_email($input);
                
            case 'url':
                return esc_url_raw($input);
                
            case 'phone':
                return preg_replace('/[^0-9+]/', '', $input);
                
            case 'color':
                return sanitize_hex_color($input);
                
            case 'int':
                return intval($input);
                
            case 'float':
                return floatval($input);
                
            case 'bool':
                return (bool) $input;
                
            case 'array':
                return is_array($input) ? array_map([$this, 'sanitize'], $input) : [];
                
            case 'html':
                return wp_kses_post($input);
                
            case 'filename':
                return sanitize_file_name($input);
                
            default:
                return $input;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    private function initializeValidationSchema(): void
    {
        $this->validationSchema = [
            'api_key' => [
                'type' => 'string',
                'required' => true,
                'min_length' => 1,
                'max_length' => 255,
            ],
            'phone_number_id' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^\d+$/',
            ],
            'phone_number' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^\+[1-9]\d{1,14}$/',
            ],
            'business_name' => [
                'type' => 'string',
                'required' => true,
                'min_length' => 1,
                'max_length' => 100,
            ],
            'enabled' => [
                'type' => 'boolean',
                'required' => false,
            ],
            'business_hours' => [
                'type' => 'array',
                'required' => false,
            ],
            'widget_settings' => [
                'type' => 'array',
                'required' => false,
            ],
            'message_templates' => [
                'type' => 'array',
                'required' => false,
            ],
            'rate_limit_settings' => [
                'type' => 'array',
                'required' => false,
            ],
        ];
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function validateCountryCode(string $phoneNumber, string $countryCode): void
    {
        $countryCodes = [
            'US' => '+1',
            'CA' => '+1',
            'MX' => '+52',
            'ES' => '+34',
            'AR' => '+54',
            'BR' => '+55',
            'CO' => '+57',
            'PE' => '+51',
            'VE' => '+58',
            'CL' => '+56',
            'EC' => '+593',
            'BO' => '+591',
            'PY' => '+595',
            'UY' => '+598',
            'GY' => '+592',
            'SR' => '+597',
            'GF' => '+594',
            'FK' => '+500',
        ];

        if (isset($countryCodes[$countryCode])) {
            $expectedPrefix = $countryCodes[$countryCode];
            if (strpos($phoneNumber, $expectedPrefix) !== 0) {
                $this->addError('phone_number', "El número no coincide con el código de país {$countryCode}");
            }
        }
    }

    private function containsEmojis(string $text): bool
    {
        return preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $text);
    }

    private function containsDangerousCharacters(string $text): bool
    {
        $dangerousPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    private function isValidTimeFormat(string $time): bool
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    private function validateType(string $field, $value, string $type): void
    {
        $isValid = false;

        switch ($type) {
            case 'string':
                $isValid = is_string($value);
                break;
            case 'int':
            case 'integer':
                $isValid = is_int($value) || (is_string($value) && ctype_digit($value));
                break;
            case 'float':
            case 'double':
                $isValid = is_float($value) || is_numeric($value);
                break;
            case 'bool':
            case 'boolean':
                $isValid = is_bool($value) || in_array($value, [0, 1, '0', '1', true, false], true);
                break;
            case 'array':
                $isValid = is_array($value);
                break;
            case 'email':
                $isValid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                break;
            case 'url':
                $isValid = filter_var($value, FILTER_VALIDATE_URL) !== false;
                break;
        }

        if (!$isValid) {
            $this->addError($field, "El campo {$field} debe ser de tipo {$type}");
        }
    }

    private function getNestedValue(array $array, string $key)
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return null;
            }
            $current = $current[$k];
        }

        return $current;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 