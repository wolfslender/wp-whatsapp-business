<?php
/**
 * Servicio de WhatsApp Business
 *
 * @package WPWhatsAppBusiness\Services
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Services;

use WPWhatsAppBusiness\Services\Interfaces\WhatsAppServiceInterface;
use WPWhatsAppBusiness\Services\Interfaces\ConfigServiceInterface;
use WPWhatsAppBusiness\Services\Interfaces\ValidationServiceInterface;
use WPWhatsAppBusiness\Services\Config\WhatsAppConfig;

/**
 * Servicio principal de WhatsApp Business API
 */
class WhatsAppService implements WhatsAppServiceInterface
{
    private const API_BASE_URL = 'https://graph.facebook.com/v18.0';
    private const CACHE_PREFIX = 'wp_whatsapp_rate_limit_';
    private const CACHE_EXPIRATION = 3600; // 1 hora

    private ConfigServiceInterface $configService;
    private ValidationServiceInterface $validationService;
    private WhatsAppConfig $config;

    public function __construct(
        ConfigServiceInterface $configService,
        ValidationServiceInterface $validationService
    ) {
        $this->configService = $configService;
        $this->validationService = $validationService;
        $this->config = $this->configService->getConfig();
    }

    public function sendTextMessage(string $phoneNumber, string $message, array $options = []): array
    {
        try {
            // Validar entrada
            $validationResult = $this->validationService->validatePhoneNumber($phoneNumber);
            if (!empty($validationResult)) {
                return $this->createErrorResponse('validation_error', 'Número de teléfono inválido', $validationResult);
            }

            $messageValidation = $this->validationService->validateMessage($message, $options);
            if (!empty($messageValidation)) {
                return $this->createErrorResponse('validation_error', 'Mensaje inválido', $messageValidation);
            }

            // Verificar rate limit
            if (!$this->checkRateLimit($phoneNumber)) {
                return $this->createErrorResponse('rate_limit_exceeded', 'Se ha excedido el límite de mensajes');
            }

            // Verificar si el negocio está abierto
            if (!$this->isBusinessOpen()) {
                return $this->createErrorResponse('business_closed', 'El negocio está cerrado en este momento');
            }

            // Preparar datos del mensaje
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            $sanitizedMessage = $this->validationService->sanitize($message, 'text');

            $messageData = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'text',
                'text' => [
                    'body' => $sanitizedMessage
                ]
            ];

            // Enviar mensaje
            $response = $this->makeApiRequest('messages', $messageData);

            if ($response['success']) {
                do_action('wp_whatsapp_business_message_sent', $response['data'], $formattedPhone, $sanitizedMessage);
            } else {
                do_action('wp_whatsapp_business_message_error', $response['error'], $formattedPhone, $sanitizedMessage);
            }

            return $response;

        } catch (\Exception $e) {
            $this->logError('Error al enviar mensaje de texto: ' . $e->getMessage());
            return $this->createErrorResponse('api_error', 'Error interno del servidor');
        }
    }

    public function sendImageMessage(string $phoneNumber, string $imageUrl, string $caption = '', array $options = []): array
    {
        try {
            // Validar entrada
            $validationResult = $this->validationService->validatePhoneNumber($phoneNumber);
            if (!empty($validationResult)) {
                return $this->createErrorResponse('validation_error', 'Número de teléfono inválido', $validationResult);
            }

            $urlValidation = $this->validationService->validateUrl($imageUrl);
            if (!empty($urlValidation)) {
                return $this->createErrorResponse('validation_error', 'URL de imagen inválida', $urlValidation);
            }

            if (!empty($caption)) {
                $captionValidation = $this->validationService->validateMessage($caption, ['max_length' => 1024]);
                if (!empty($captionValidation)) {
                    return $this->createErrorResponse('validation_error', 'Caption inválido', $captionValidation);
                }
            }

            // Verificar rate limit
            if (!$this->checkRateLimit($phoneNumber)) {
                return $this->createErrorResponse('rate_limit_exceeded', 'Se ha excedido el límite de mensajes');
            }

            // Verificar si el negocio está abierto
            if (!$this->isBusinessOpen()) {
                return $this->createErrorResponse('business_closed', 'El negocio está cerrado en este momento');
            }

            // Preparar datos del mensaje
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            $sanitizedUrl = $this->validationService->sanitize($imageUrl, 'url');
            $sanitizedCaption = $this->validationService->sanitize($caption, 'text');

            $messageData = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'image',
                'image' => [
                    'link' => $sanitizedUrl
                ]
            ];

            if (!empty($sanitizedCaption)) {
                $messageData['image']['caption'] = $sanitizedCaption;
            }

            // Enviar mensaje
            $response = $this->makeApiRequest('messages', $messageData);

            if ($response['success']) {
                do_action('wp_whatsapp_business_message_sent', $response['data'], $formattedPhone, 'image');
            } else {
                do_action('wp_whatsapp_business_message_error', $response['error'], $formattedPhone, 'image');
            }

            return $response;

        } catch (\Exception $e) {
            $this->logError('Error al enviar mensaje con imagen: ' . $e->getMessage());
            return $this->createErrorResponse('api_error', 'Error interno del servidor');
        }
    }

    public function sendButtonMessage(string $phoneNumber, string $header, string $body, array $buttons, array $options = []): array
    {
        try {
            // Validar entrada
            $validationResult = $this->validationService->validatePhoneNumber($phoneNumber);
            if (!empty($validationResult)) {
                return $this->createErrorResponse('validation_error', 'Número de teléfono inválido', $validationResult);
            }

            $headerValidation = $this->validationService->validateMessage($header, ['max_length' => 60]);
            if (!empty($headerValidation)) {
                return $this->createErrorResponse('validation_error', 'Header inválido', $headerValidation);
            }

            $bodyValidation = $this->validationService->validateMessage($body, ['max_length' => 1024]);
            if (!empty($bodyValidation)) {
                return $this->createErrorResponse('validation_error', 'Body inválido', $bodyValidation);
            }

            // Validar botones
            if (empty($buttons) || count($buttons) > 3) {
                return $this->createErrorResponse('validation_error', 'Debe proporcionar entre 1 y 3 botones');
            }

            foreach ($buttons as $index => $button) {
                if (!isset($button['type']) || $button['type'] !== 'reply') {
                    return $this->createErrorResponse('validation_error', "Botón {$index}: tipo inválido");
                }
                if (!isset($button['reply']['id']) || !isset($button['reply']['title'])) {
                    return $this->createErrorResponse('validation_error', "Botón {$index}: estructura inválida");
                }
                if (strlen($button['reply']['title']) > 20) {
                    return $this->createErrorResponse('validation_error', "Botón {$index}: título demasiado largo");
                }
            }

            // Verificar rate limit
            if (!$this->checkRateLimit($phoneNumber)) {
                return $this->createErrorResponse('rate_limit_exceeded', 'Se ha excedido el límite de mensajes');
            }

            // Verificar si el negocio está abierto
            if (!$this->isBusinessOpen()) {
                return $this->createErrorResponse('business_closed', 'El negocio está cerrado en este momento');
            }

            // Preparar datos del mensaje
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            $sanitizedHeader = $this->validationService->sanitize($header, 'text');
            $sanitizedBody = $this->validationService->sanitize($body, 'text');

            $messageData = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedPhone,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'header' => [
                        'type' => 'text',
                        'text' => $sanitizedHeader
                    ],
                    'body' => [
                        'text' => $sanitizedBody
                    ],
                    'action' => [
                        'buttons' => $buttons
                    ]
                ]
            ];

            // Enviar mensaje
            $response = $this->makeApiRequest('messages', $messageData);

            if ($response['success']) {
                do_action('wp_whatsapp_business_message_sent', $response['data'], $formattedPhone, 'button');
            } else {
                do_action('wp_whatsapp_business_message_error', $response['error'], $formattedPhone, 'button');
            }

            return $response;

        } catch (\Exception $e) {
            $this->logError('Error al enviar mensaje con botones: ' . $e->getMessage());
            return $this->createErrorResponse('api_error', 'Error interno del servidor');
        }
    }

    public function generateWhatsAppUrl(string $phoneNumber, string $message = '', string $type = 'web'): string
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);
        
        if (empty($formattedPhone)) {
            return '';
        }

        $sanitizedMessage = $this->validationService->sanitize($message, 'text');
        $encodedMessage = urlencode($sanitizedMessage);

        switch ($type) {
            case 'web':
                $baseUrl = 'https://wa.me/';
                break;
            case 'mobile':
                $baseUrl = 'whatsapp://send?phone=';
                break;
            case 'api':
                return $this->generateApiUrl($formattedPhone, $sanitizedMessage);
            default:
                $baseUrl = 'https://wa.me/';
        }

        $url = $baseUrl . ltrim($formattedPhone, '+');
        
        if (!empty($encodedMessage)) {
            $url .= '?text=' . $encodedMessage;
        }

        return $url;
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $validationResult = $this->validationService->validatePhoneNumber($phoneNumber);
        return empty($validationResult);
    }

    public function formatPhoneNumber(string $phoneNumber): string
    {
        if (empty($phoneNumber)) {
            return '';
        }

        // Remover todos los caracteres excepto números y +
        $cleanNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Asegurar que comience con +
        if (!str_starts_with($cleanNumber, '+')) {
            $cleanNumber = '+' . $cleanNumber;
        }

        // Validar formato E.164
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $cleanNumber)) {
            return '';
        }

        return $cleanNumber;
    }

    public function detectDevice(): string
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return 'desktop';
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Patrones para detectar dispositivos móviles
        $mobilePatterns = [
            '/Android/i',
            '/iPhone/i',
            '/iPad/i',
            '/iPod/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/Mobile/i',
            '/Tablet/i'
        ];

        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return 'mobile';
            }
        }

        return 'desktop';
    }

    public function isBusinessOpen(): bool
    {
        $businessHours = $this->config->getBusinessHours();
        
        if (empty($businessHours)) {
            return true; // Si no hay horarios configurados, considerar abierto
        }

        $currentTime = current_time('timestamp');
        $currentDay = strtolower(date('l', $currentTime));
        $currentHour = date('H:i', $currentTime);

        if (!isset($businessHours[$currentDay])) {
            return false;
        }

        $dayHours = $businessHours[$currentDay];
        
        // Si el día está deshabilitado
        if (!isset($dayHours['enabled']) || !$dayHours['enabled']) {
            return false;
        }

        // Si está configurado como cerrado (00:00 - 00:00)
        if ($dayHours['open'] === '00:00' && $dayHours['close'] === '00:00') {
            return false;
        }

        // Verificar si estamos dentro del horario
        return $currentHour >= $dayHours['open'] && $currentHour <= $dayHours['close'];
    }

    public function getTemplateMessage(string $templateName, array $variables = []): string
    {
        $templates = $this->config->getMessageTemplates();
        
        if (!isset($templates[$templateName])) {
            return $templates['default'] ?? '¡Hola! ¿En qué puedo ayudarte?';
        }

        $message = $templates[$templateName];
        
        // Reemplazar variables
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        // Reemplazar variables especiales
        $message = str_replace('{business_name}', $this->config->getBusinessName(), $message);
        $message = str_replace('{current_date}', date('d/m/Y'), $message);
        $message = str_replace('{current_time}', date('H:i'), $message);

        return $message;
    }

    public function checkRateLimit(string $identifier, int $maxRequests = 10, int $timeWindow = 3600): bool
    {
        $rateLimitSettings = $this->config->getRateLimitSettings();
        
        if (!isset($rateLimitSettings['enabled']) || !$rateLimitSettings['enabled']) {
            return true; // Rate limiting deshabilitado
        }

        $maxRequests = $rateLimitSettings['max_requests_per_hour'] ?? $maxRequests;
        $timeWindow = $rateLimitSettings['time_window'] ?? $timeWindow;

        $cacheKey = self::CACHE_PREFIX . md5($identifier);
        $currentRequests = get_transient($cacheKey) ?: 0;

        if ($currentRequests >= $maxRequests) {
            return false;
        }

        // Incrementar contador
        set_transient($cacheKey, $currentRequests + 1, $timeWindow);
        
        return true;
    }

    private function makeApiRequest(string $endpoint, array $data): array
    {
        $apiKey = $this->config->getApiKey();
        $phoneNumberId = $this->config->getPhoneNumberId();

        if (empty($apiKey) || empty($phoneNumberId)) {
            return $this->createErrorResponse('config_error', 'API Key o Phone Number ID no configurados');
        }

        $url = self::API_BASE_URL . '/' . $phoneNumberId . '/' . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 30,
            'sslverify' => true
        ]);

        if (is_wp_error($response)) {
            $this->logError('Error en request HTTP: ' . $response->get_error_message());
            return $this->createErrorResponse('http_error', 'Error de conexión con WhatsApp API');
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $responseData = json_decode($body, true);

        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'success' => true,
                'data' => $responseData,
                'status_code' => $statusCode
            ];
        } else {
            $errorMessage = $responseData['error']['message'] ?? 'Error desconocido de WhatsApp API';
            $this->logError("WhatsApp API Error ({$statusCode}): {$errorMessage}");
            
            return $this->createErrorResponse('api_error', $errorMessage, [
                'status_code' => $statusCode,
                'response' => $responseData
            ]);
        }
    }

    private function generateApiUrl(string $phoneNumber, string $message): string
    {
        $apiKey = $this->config->getApiKey();
        $phoneNumberId = $this->config->getPhoneNumberId();
        
        if (empty($apiKey) || empty($phoneNumberId)) {
            return '';
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return add_query_arg([
            'action' => 'wp_whatsapp_send',
            'data' => base64_encode(json_encode($data)),
            'nonce' => wp_create_nonce('wp_whatsapp_send')
        ], admin_url('admin-ajax.php'));
    }

    private function createErrorResponse(string $type, string $message, array $details = []): array
    {
        return [
            'success' => false,
            'error' => [
                'type' => $type,
                'message' => $message,
                'details' => $details,
                'timestamp' => current_time('mysql')
            ]
        ];
    }

    private function logError(string $message): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[WP WhatsApp Business] ' . $message);
        }
        
        do_action('wp_whatsapp_business_error_logged', $message);
    }
} 