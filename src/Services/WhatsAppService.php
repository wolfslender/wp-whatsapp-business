<?php
/**
 * Servicio de WhatsApp Business
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

/**
 * Clase para manejar la integración con WhatsApp Business API
 */
class WhatsAppService {

    /**
     * Servicio de configuración
     *
     * @var ConfigService
     */
    private $config_service;

    /**
     * Servicio de validación
     *
     * @var ValidationService
     */
    private $validation_service;

    /**
     * URL base de la API de WhatsApp
     *
     * @var string
     */
    private const API_BASE_URL = 'https://graph.facebook.com/v18.0';

    /**
     * Constructor
     *
     * @param ConfigService $config_service Servicio de configuración
     * @param ValidationService $validation_service Servicio de validación
     */
    public function __construct(ConfigService $config_service, ValidationService $validation_service) {
        $this->config_service = $config_service;
        $this->validation_service = $validation_service;
    }

    /**
     * Enviar mensaje de texto
     *
     * @param string $phone_number Número de teléfono del destinatario
     * @param string $message Mensaje a enviar
     * @param array $options Opciones adicionales
     * @return array
     */
    public function sendTextMessage(string $phone_number, string $message, array $options = []): array {
        // Validar número de teléfono
        if (!$this->validation_service->isValidPhoneNumber($phone_number)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
                'code' => 'INVALID_PHONE'
            ];
        }

        // Validar mensaje
        if (empty($message)) {
            return [
                'success' => false,
                'error' => 'El mensaje no puede estar vacío',
                'code' => 'EMPTY_MESSAGE'
            ];
        }

        // Preparar datos del mensaje
        $message_data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phone_number),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        // Agregar opciones adicionales
        if (!empty($options['preview_url'])) {
            $message_data['text']['preview_url'] = $options['preview_url'];
        }

        return $this->sendMessage($message_data);
    }

    /**
     * Enviar mensaje con imagen
     *
     * @param string $phone_number Número de teléfono del destinatario
     * @param string $image_url URL de la imagen
     * @param string $caption Captión de la imagen (opcional)
     * @param array $options Opciones adicionales
     * @return array
     */
    public function sendImageMessage(string $phone_number, string $image_url, string $caption = '', array $options = []): array {
        // Validar número de teléfono
        if (!$this->validation_service->isValidPhoneNumber($phone_number)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
                'code' => 'INVALID_PHONE'
            ];
        }

        // Validar URL de imagen
        if (!$this->validation_service->isValidUrl($image_url)) {
            return [
                'success' => false,
                'error' => 'URL de imagen inválida',
                'code' => 'INVALID_IMAGE_URL'
            ];
        }

        // Preparar datos del mensaje
        $message_data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phone_number),
            'type' => 'image',
            'image' => [
                'link' => $image_url
            ]
        ];

        // Agregar captión si existe
        if (!empty($caption)) {
            $message_data['image']['caption'] = $caption;
        }

        return $this->sendMessage($message_data);
    }

    /**
     * Enviar mensaje con documento
     *
     * @param string $phone_number Número de teléfono del destinatario
     * @param string $document_url URL del documento
     * @param string $filename Nombre del archivo
     * @param string $caption Captión del documento (opcional)
     * @return array
     */
    public function sendDocumentMessage(string $phone_number, string $document_url, string $filename, string $caption = ''): array {
        // Validar número de teléfono
        if (!$this->validation_service->isValidPhoneNumber($phone_number)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
                'code' => 'INVALID_PHONE'
            ];
        }

        // Validar URL del documento
        if (!$this->validation_service->isValidUrl($document_url)) {
            return [
                'success' => false,
                'error' => 'URL del documento inválida',
                'code' => 'INVALID_DOCUMENT_URL'
            ];
        }

        // Preparar datos del mensaje
        $message_data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phone_number),
            'type' => 'document',
            'document' => [
                'link' => $document_url,
                'filename' => $filename
            ]
        ];

        // Agregar captión si existe
        if (!empty($caption)) {
            $message_data['document']['caption'] = $caption;
        }

        return $this->sendMessage($message_data);
    }

    /**
     * Enviar mensaje con botones
     *
     * @param string $phone_number Número de teléfono del destinatario
     * @param string $header_text Texto del encabezado
     * @param string $body_text Texto del cuerpo
     * @param array $buttons Array de botones
     * @return array
     */
    public function sendButtonMessage(string $phone_number, string $header_text, string $body_text, array $buttons): array {
        // Validar número de teléfono
        if (!$this->validation_service->isValidPhoneNumber($phone_number)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
                'code' => 'INVALID_PHONE'
            ];
        }

        // Validar botones
        if (empty($buttons) || count($buttons) > 3) {
            return [
                'success' => false,
                'error' => 'Debe tener entre 1 y 3 botones',
                'code' => 'INVALID_BUTTONS'
            ];
        }

        // Preparar datos del mensaje
        $message_data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phone_number),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'header' => [
                    'type' => 'text',
                    'text' => $header_text
                ],
                'body' => [
                    'text' => $body_text
                ],
                'action' => [
                    'buttons' => $buttons
                ]
            ]
        ];

        return $this->sendMessage($message_data);
    }

    /**
     * Enviar mensaje con lista
     *
     * @param string $phone_number Número de teléfono del destinatario
     * @param string $header_text Texto del encabezado
     * @param string $body_text Texto del cuerpo
     * @param string $button_text Texto del botón
     * @param array $sections Array de secciones
     * @return array
     */
    public function sendListMessage(string $phone_number, string $header_text, string $body_text, string $button_text, array $sections): array {
        // Validar número de teléfono
        if (!$this->validation_service->isValidPhoneNumber($phone_number)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
                'code' => 'INVALID_PHONE'
            ];
        }

        // Preparar datos del mensaje
        $message_data = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phone_number),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'header' => [
                    'type' => 'text',
                    'text' => $header_text
                ],
                'body' => [
                    'text' => $body_text
                ],
                'action' => [
                    'button' => $button_text,
                    'sections' => $sections
                ]
            ]
        ];

        return $this->sendMessage($message_data);
    }

    /**
     * Enviar mensaje a través de la API
     *
     * @param array $message_data Datos del mensaje
     * @return array
     */
    private function sendMessage(array $message_data): array {
        $settings = $this->config_service->getSettings();
        $api_key = $settings['api_key'] ?? '';
        $phone_number_id = $settings['phone_number_id'] ?? '';

        if (empty($api_key)) {
            return [
                'success' => false,
                'error' => 'API Key no configurada',
                'code' => 'NO_API_KEY'
            ];
        }

        if (empty($phone_number_id)) {
            return [
                'success' => false,
                'error' => 'Phone Number ID no configurado',
                'code' => 'NO_PHONE_NUMBER_ID'
            ];
        }

        // URL de la API
        $url = self::API_BASE_URL . '/' . $phone_number_id . '/messages';

        // Headers de la petición
        $headers = [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ];

        // Realizar petición
        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => json_encode($message_data),
            'timeout' => 30,
            'sslverify' => true
        ]);

        // Verificar si hay error en la petición
        if (is_wp_error($response)) {
            $this->logError('Error en petición HTTP: ' . $response->get_error_message());
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $response->get_error_message(),
                'code' => 'HTTP_ERROR'
            ];
        }

        // Obtener respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        // Verificar código de respuesta
        if ($response_code !== 200) {
            $error_message = $response_data['error']['message'] ?? 'Error desconocido';
            $error_code = $response_data['error']['code'] ?? 'UNKNOWN_ERROR';
            
            $this->logError("Error de API: {$error_message} (Código: {$error_code})");
            
            return [
                'success' => false,
                'error' => $error_message,
                'code' => $error_code,
                'response_code' => $response_code
            ];
        }

        // Mensaje enviado correctamente
        $this->logMessage('Mensaje enviado correctamente', $message_data);
        
        return [
            'success' => true,
            'message_id' => $response_data['messages'][0]['id'] ?? '',
            'response' => $response_data
        ];
    }

    /**
     * Construir URL de WhatsApp para el widget
     *
     * @param string $phone_number Número de teléfono
     * @param string $message Mensaje predefinido
     * @return string
     */
    public function buildWhatsAppUrl(string $phone_number, string $message = ''): string {
        // Formatear número de teléfono
        $formatted_number = $this->formatPhoneNumber($phone_number);
        
        // Construir URL base
        $url = "https://wa.me/{$formatted_number}";
        
        // Agregar mensaje si existe
        if (!empty($message)) {
            $url .= '?text=' . urlencode($message);
        }
        
        return $url;
    }

    /**
     * Formatear número de teléfono
     *
     * @param string $phone_number Número de teléfono
     * @return string
     */
    private function formatPhoneNumber(string $phone_number): string {
        // Remover todos los caracteres no numéricos excepto el +
        $formatted = preg_replace('/[^0-9+]/', '', $phone_number);
        
        // Asegurar que tenga el código de país
        if (!str_starts_with($formatted, '+')) {
            $formatted = '+' . $formatted;
        }
        
        return $formatted;
    }

    /**
     * Obtener información del número de teléfono
     *
     * @param string $phone_number_id ID del número de teléfono
     * @return array
     */
    public function getPhoneNumberInfo(string $phone_number_id): array {
        $settings = $this->config_service->getSettings();
        $api_key = $settings['api_key'] ?? '';

        if (empty($api_key)) {
            return [
                'success' => false,
                'error' => 'API Key no configurada',
                'code' => 'NO_API_KEY'
            ];
        }

        // URL de la API
        $url = self::API_BASE_URL . '/' . $phone_number_id;

        // Headers de la petición
        $headers = [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ];

        // Realizar petición
        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 30,
            'sslverify' => true
        ]);

        // Verificar si hay error en la petición
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $response->get_error_message(),
                'code' => 'HTTP_ERROR'
            ];
        }

        // Obtener respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        // Verificar código de respuesta
        if ($response_code !== 200) {
            $error_message = $response_data['error']['message'] ?? 'Error desconocido';
            $error_code = $response_data['error']['code'] ?? 'UNKNOWN_ERROR';
            
            return [
                'success' => false,
                'error' => $error_message,
                'code' => $error_code,
                'response_code' => $response_code
            ];
        }

        return [
            'success' => true,
            'data' => $response_data
        ];
    }

    /**
     * Verificar estado de la API
     *
     * @return array
     */
    public function checkApiStatus(): array {
        $settings = $this->config_service->getSettings();
        $api_key = $settings['api_key'] ?? '';

        if (empty($api_key)) {
            return [
                'success' => false,
                'error' => 'API Key no configurada',
                'code' => 'NO_API_KEY'
            ];
        }

        // URL de la API para verificar estado
        $url = self::API_BASE_URL . '/me';

        // Headers de la petición
        $headers = [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ];

        // Realizar petición
        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 30,
            'sslverify' => true
        ]);

        // Verificar si hay error en la petición
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $response->get_error_message(),
                'code' => 'HTTP_ERROR'
            ];
        }

        // Obtener respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        // Verificar código de respuesta
        if ($response_code !== 200) {
            $error_message = $response_data['error']['message'] ?? 'Error desconocido';
            $error_code = $response_data['error']['code'] ?? 'UNKNOWN_ERROR';
            
            return [
                'success' => false,
                'error' => $error_message,
                'code' => $error_code,
                'response_code' => $response_code
            ];
        }

        return [
            'success' => true,
            'data' => $response_data
        ];
    }

    /**
     * Registrar mensaje en el log
     *
     * @param string $message Mensaje a registrar
     * @param array $data Datos adicionales
     * @return void
     */
    private function logMessage(string $message, array $data = []): void {
        if (!$this->config_service->isLoggingEnabled()) {
            return;
        }

        $log_entry = [
            'timestamp' => current_time('mysql'),
            'message' => $message,
            'data' => $data
        ];

        // Guardar en la base de datos
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'whatsapp_messages',
            [
                'phone_number' => $data['to'] ?? '',
                'message' => $message,
                'message_type' => 'log',
                'status' => 'success'
            ]
        );
    }

    /**
     * Registrar error en el log
     *
     * @param string $error Mensaje de error
     * @param array $data Datos adicionales
     * @return void
     */
    private function logError(string $error, array $data = []): void {
        if (!$this->config_service->isLoggingEnabled()) {
            return;
        }

        $log_entry = [
            'timestamp' => current_time('mysql'),
            'error' => $error,
            'data' => $data
        ];

        // Guardar en la base de datos
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'whatsapp_messages',
            [
                'phone_number' => $data['to'] ?? '',
                'message' => $error,
                'message_type' => 'error',
                'status' => 'failed'
            ]
        );
    }

    /**
     * Obtener el servicio de configuración
     *
     * @return ConfigService
     */
    public function getConfigService(): ConfigService {
        return $this->config_service;
    }

    /**
     * Obtener el servicio de validación
     *
     * @return ValidationService
     */
    public function getValidationService(): ValidationService {
        return $this->validation_service;
    }
} 