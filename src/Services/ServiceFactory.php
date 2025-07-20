<?php

namespace WPWhatsAppBusiness\Services;

use WPWhatsAppBusiness\Services\Interfaces\WhatsAppServiceInterface;
use WPWhatsAppBusiness\Services\Interfaces\ConfigServiceInterface;
use WPWhatsAppBusiness\Services\Interfaces\ValidationServiceInterface;

/**
 * Factory para crear instancias de servicios
 */
class ServiceFactory
{
    private static array $instances = [];

    /**
     * Crear instancia del servicio de configuración
     */
    public static function createConfigService(): ConfigServiceInterface
    {
        if (!isset(self::$instances['config'])) {
            $validationService = self::createValidationService();
            self::$instances['config'] = new ConfigService($validationService);
        }

        return self::$instances['config'];
    }

    /**
     * Crear instancia del servicio de validación
     */
    public static function createValidationService(): ValidationServiceInterface
    {
        if (!isset(self::$instances['validation'])) {
            self::$instances['validation'] = new ValidationService();
        }

        return self::$instances['validation'];
    }

    /**
     * Crear instancia del servicio de WhatsApp
     */
    public static function createWhatsAppService(): WhatsAppServiceInterface
    {
        if (!isset(self::$instances['whatsapp'])) {
            $configService = self::createConfigService();
            $validationService = self::createValidationService();
            self::$instances['whatsapp'] = new WhatsAppService($configService, $validationService);
        }

        return self::$instances['whatsapp'];
    }

    /**
     * Obtener instancia existente de un servicio
     */
    public static function getService(string $serviceName)
    {
        switch ($serviceName) {
            case 'config':
                return self::createConfigService();
            case 'validation':
                return self::createValidationService();
            case 'whatsapp':
                return self::createWhatsAppService();
            default:
                throw new \InvalidArgumentException("Servicio '{$serviceName}' no encontrado");
        }
    }

    /**
     * Limpiar todas las instancias (útil para testing)
     */
    public static function clearInstances(): void
    {
        self::$instances = [];
    }

    /**
     * Verificar si un servicio ya está instanciado
     */
    public static function hasInstance(string $serviceName): bool
    {
        return isset(self::$instances[$serviceName]);
    }

    /**
     * Obtener todas las instancias (útil para debugging)
     */
    public static function getAllInstances(): array
    {
        return self::$instances;
    }
} 