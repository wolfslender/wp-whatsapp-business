<?php

namespace WPWhatsAppBusiness\Services\Interfaces;

/**
 * Interface para el servicio de configuración
 */
interface ConfigServiceInterface
{
    /**
     * Obtener valor de configuración
     *
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed Valor de configuración
     */
    public function get(string $key, $default = null);

    /**
     * Establecer valor de configuración
     *
     * @param string $key Clave de configuración
     * @param mixed $value Valor a establecer
     * @return bool True si se guardó correctamente
     */
    public function set(string $key, $value): bool;

    /**
     * Obtener configuración completa
     *
     * @return array Configuración completa
     */
    public function getAll(): array;

    /**
     * Establecer configuración completa
     *
     * @param array $config Configuración completa
     * @return bool True si se guardó correctamente
     */
    public function setAll(array $config): bool;

    /**
     * Verificar si existe una configuración
     *
     * @param string $key Clave de configuración
     * @return bool True si existe
     */
    public function has(string $key): bool;

    /**
     * Eliminar configuración
     *
     * @param string $key Clave de configuración
     * @return bool True si se eliminó correctamente
     */
    public function delete(string $key): bool;

    /**
     * Limpiar cache de configuración
     *
     * @return bool True si se limpió correctamente
     */
    public function clearCache(): bool;

    /**
     * Obtener valores por defecto
     *
     * @return array Valores por defecto
     */
    public function getDefaults(): array;

    /**
     * Validar configuración
     *
     * @param array $config Configuración a validar
     * @return array Array con errores de validación
     */
    public function validate(array $config): array;

    /**
     * Ejecutar migración de configuración
     *
     * @param string $fromVersion Versión desde la cual migrar
     * @param string $toVersion Versión a la cual migrar
     * @return bool True si la migración fue exitosa
     */
    public function migrate(string $fromVersion, string $toVersion): bool;

    /**
     * Obtener esquema de validación
     *
     * @return array Esquema de validación
     */
    public function getValidationSchema(): array;

    /**
     * Exportar configuración
     *
     * @return array Configuración exportada
     */
    public function export(): array;

    /**
     * Importar configuración
     *
     * @param array $config Configuración a importar
     * @return bool True si se importó correctamente
     */
    public function import(array $config): bool;
} 