<?php

namespace WPWhatsAppBusiness\Services\Interfaces;

/**
 * Interface para el servicio de validación
 */
interface ValidationServiceInterface
{
    /**
     * Validar número de teléfono
     *
     * @param string $phoneNumber Número a validar
     * @param string $countryCode Código de país (opcional)
     * @return array Resultado de validación con errores
     */
    public function validatePhoneNumber(string $phoneNumber, string $countryCode = ''): array;

    /**
     * Validar contenido de mensaje
     *
     * @param string $message Mensaje a validar
     * @param array $options Opciones de validación
     * @return array Resultado de validación con errores
     */
    public function validateMessage(string $message, array $options = []): array;

    /**
     * Validar formato de horarios de negocio
     *
     * @param array $businessHours Horarios a validar
     * @return array Resultado de validación con errores
     */
    public function validateBusinessHours(array $businessHours): array;

    /**
     * Validar color/CSS
     *
     * @param string $color Color a validar
     * @param string $type Tipo de validación (hex, rgb, css)
     * @return array Resultado de validación con errores
     */
    public function validateColor(string $color, string $type = 'hex'): array;

    /**
     * Validar archivo subido
     *
     * @param array $file Array de archivo ($_FILES)
     * @param array $options Opciones de validación
     * @return array Resultado de validación con errores
     */
    public function validateFileUpload(array $file, array $options = []): array;

    /**
     * Validar URL
     *
     * @param string $url URL a validar
     * @param array $options Opciones de validación
     * @return array Resultado de validación con errores
     */
    public function validateUrl(string $url, array $options = []): array;

    /**
     * Validar email
     *
     * @param string $email Email a validar
     * @return array Resultado de validación con errores
     */
    public function validateEmail(string $email): array;

    /**
     * Validar configuración completa
     *
     * @param array $config Configuración a validar
     * @return array Resultado de validación con errores
     */
    public function validateConfig(array $config): array;

    /**
     * Sanitizar entrada
     *
     * @param mixed $input Entrada a sanitizar
     * @param string $type Tipo de sanitización
     * @return mixed Entrada sanitizada
     */
    public function sanitize($input, string $type = 'text');

    /**
     * Obtener errores de validación
     *
     * @return array Errores acumulados
     */
    public function getErrors(): array;

    /**
     * Limpiar errores de validación
     *
     * @return void
     */
    public function clearErrors(): void;

    /**
     * Verificar si hay errores
     *
     * @return bool True si hay errores
     */
    public function hasErrors(): bool;
} 