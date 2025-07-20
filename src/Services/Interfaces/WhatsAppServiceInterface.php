<?php

namespace WPWhatsAppBusiness\Services\Interfaces;

/**
 * Interface para el servicio de WhatsApp Business API
 */
interface WhatsAppServiceInterface
{
    /**
     * Enviar mensaje de texto
     *
     * @param string $phoneNumber Número de teléfono en formato E.164
     * @param string $message Mensaje a enviar
     * @param array $options Opciones adicionales
     * @return array Resultado de la operación
     */
    public function sendTextMessage(string $phoneNumber, string $message, array $options = []): array;

    /**
     * Enviar mensaje con imagen
     *
     * @param string $phoneNumber Número de teléfono en formato E.164
     * @param string $imageUrl URL de la imagen
     * @param string $caption Caption de la imagen
     * @param array $options Opciones adicionales
     * @return array Resultado de la operación
     */
    public function sendImageMessage(string $phoneNumber, string $imageUrl, string $caption = '', array $options = []): array;

    /**
     * Enviar mensaje con botones
     *
     * @param string $phoneNumber Número de teléfono en formato E.164
     * @param string $header Texto del header
     * @param string $body Texto del cuerpo
     * @param array $buttons Array de botones
     * @param array $options Opciones adicionales
     * @return array Resultado de la operación
     */
    public function sendButtonMessage(string $phoneNumber, string $header, string $body, array $buttons, array $options = []): array;

    /**
     * Generar URL de WhatsApp Web/móvil
     *
     * @param string $phoneNumber Número de teléfono
     * @param string $message Mensaje predefinido
     * @param string $type Tipo de URL (web, mobile, api)
     * @return string URL generada
     */
    public function generateWhatsAppUrl(string $phoneNumber, string $message = '', string $type = 'web'): string;

    /**
     * Validar número de teléfono
     *
     * @param string $phoneNumber Número a validar
     * @return bool True si es válido
     */
    public function validatePhoneNumber(string $phoneNumber): bool;

    /**
     * Formatear número de teléfono a E.164
     *
     * @param string $phoneNumber Número a formatear
     * @return string Número formateado o string vacío si no es válido
     */
    public function formatPhoneNumber(string $phoneNumber): string;

    /**
     * Detectar tipo de dispositivo
     *
     * @return string 'mobile' o 'desktop'
     */
    public function detectDevice(): string;

    /**
     * Verificar si el negocio está abierto
     *
     * @return bool True si está abierto
     */
    public function isBusinessOpen(): bool;

    /**
     * Obtener plantilla de mensaje
     *
     * @param string $templateName Nombre de la plantilla
     * @param array $variables Variables para reemplazar
     * @return string Mensaje procesado
     */
    public function getTemplateMessage(string $templateName, array $variables = []): string;

    /**
     * Verificar rate limit
     *
     * @param string $identifier Identificador único
     * @param int $maxRequests Máximo de requests permitidos
     * @param int $timeWindow Ventana de tiempo en segundos
     * @return bool True si no se ha excedido el límite
     */
    public function checkRateLimit(string $identifier, int $maxRequests = 10, int $timeWindow = 3600): bool;
} 