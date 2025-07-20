<?php
/**
 * Tests unitarios para el plugin WP WhatsApp Business
 *
 * @package WPWhatsAppBusiness\Tests
 * @since 1.0.0
 */

namespace WPWhatsAppBusiness\Tests;

use WPWhatsAppBusiness\Core\Plugin;
use WPWhatsAppBusiness\Services\ConfigService;
use WPWhatsAppBusiness\Services\ValidationService;
use WPWhatsAppBusiness\Services\WhatsAppService;
use PHPUnit\Framework\TestCase;

/**
 * Tests para la clase principal del plugin
 */
class PluginTest extends TestCase {

    /**
     * Test para verificar que el plugin implementa el patrón Singleton
     */
    public function testPluginIsSingleton() {
        $instance1 = Plugin::getInstance();
        $instance2 = Plugin::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test para verificar que el plugin no puede ser clonado
     */
    public function testPluginCannotBeCloned() {
        $this->expectException(\Error::class);
        
        $instance = Plugin::getInstance();
        $cloned = clone $instance;
    }

    /**
     * Test para verificar que el plugin no puede ser deserializado
     */
    public function testPluginCannotBeUnserialized() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot unserialize singleton');
        
        $instance = Plugin::getInstance();
        $serialized = serialize($instance);
        unserialize($serialized);
    }

    /**
     * Test para verificar que el container está disponible
     */
    public function testContainerIsAvailable() {
        $plugin = Plugin::getInstance();
        $container = $plugin->getContainer();
        
        $this->assertInstanceOf('WPWhatsAppBusiness\Core\Container', $container);
    }

    /**
     * Test para verificar que el loader está disponible
     */
    public function testLoaderIsAvailable() {
        $plugin = Plugin::getInstance();
        $loader = $plugin->getLoader();
        
        $this->assertInstanceOf('WPWhatsAppBusiness\Core\Loader', $loader);
    }
}

/**
 * Tests para el servicio de configuración
 */
class ConfigServiceTest extends TestCase {

    private $config_service;

    protected function setUp(): void {
        parent::setUp();
        $this->config_service = new ConfigService();
    }

    /**
     * Test para verificar que se pueden obtener configuraciones
     */
    public function testGetSettings() {
        $settings = $this->config_service->getSettings();
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('api_key', $settings);
        $this->assertArrayHasKey('phone_number', $settings);
    }

    /**
     * Test para verificar que se pueden obtener valores específicos
     */
    public function testGetSpecificValue() {
        $enabled = $this->config_service->get('enabled');
        
        $this->assertIsBool($enabled);
    }

    /**
     * Test para verificar que se pueden establecer valores
     */
    public function testSetValue() {
        $test_value = 'test_value';
        $result = $this->config_service->set('test_key', $test_value);
        
        $this->assertTrue($result);
        
        $retrieved_value = $this->config_service->get('test_key');
        $this->assertEquals($test_value, $retrieved_value);
    }

    /**
     * Test para verificar que se pueden actualizar múltiples valores
     */
    public function testUpdateSettings() {
        $new_settings = [
            'test_key_1' => 'value_1',
            'test_key_2' => 'value_2'
        ];
        
        $result = $this->config_service->updateSettings($new_settings);
        
        $this->assertTrue($result);
        
        $this->assertEquals('value_1', $this->config_service->get('test_key_1'));
        $this->assertEquals('value_2', $this->config_service->get('test_key_2'));
    }

    /**
     * Test para verificar que se puede restablecer a valores por defecto
     */
    public function testResetToDefaults() {
        // Establecer un valor personalizado
        $this->config_service->set('test_key', 'custom_value');
        
        // Restablecer a valores por defecto
        $result = $this->config_service->resetToDefaults();
        
        $this->assertTrue($result);
        
        // Verificar que el valor personalizado ya no existe
        $this->assertNull($this->config_service->get('test_key'));
    }
}

/**
 * Tests para el servicio de validación
 */
class ValidationServiceTest extends TestCase {

    private $validation_service;

    protected function setUp(): void {
        parent::setUp();
        $this->validation_service = new ValidationService();
    }

    /**
     * Test para validar números de teléfono válidos
     */
    public function testValidPhoneNumbers() {
        $valid_numbers = [
            '+1234567890',
            '+34612345678',
            '+447911123456',
            '+5491112345678'
        ];
        
        foreach ($valid_numbers as $number) {
            $this->assertTrue(
                $this->validation_service->isValidPhoneNumber($number),
                "El número {$number} debería ser válido"
            );
        }
    }

    /**
     * Test para validar números de teléfono inválidos
     */
    public function testInvalidPhoneNumbers() {
        $invalid_numbers = [
            '1234567890', // Sin código de país
            '+', // Solo el +
            'abc123', // Caracteres no numéricos
            '+123', // Muy corto
            '+1234567890123456789' // Muy largo
        ];
        
        foreach ($invalid_numbers as $number) {
            $this->assertFalse(
                $this->validation_service->isValidPhoneNumber($number),
                "El número {$number} debería ser inválido"
            );
        }
    }

    /**
     * Test para validar URLs válidas
     */
    public function testValidUrls() {
        $valid_urls = [
            'https://example.com',
            'http://example.com',
            'https://www.example.com/path',
            'https://example.com/path?param=value'
        ];
        
        foreach ($valid_urls as $url) {
            $this->assertTrue(
                $this->validation_service->isValidUrl($url),
                "La URL {$url} debería ser válida"
            );
        }
    }

    /**
     * Test para validar URLs inválidas
     */
    public function testInvalidUrls() {
        $invalid_urls = [
            'not-a-url',
            'ftp://example.com',
            'example.com',
            ''
        ];
        
        foreach ($invalid_urls as $url) {
            $this->assertFalse(
                $this->validation_service->isValidUrl($url),
                "La URL {$url} debería ser inválida"
            );
        }
    }

    /**
     * Test para validar emails válidos
     */
    public function testValidEmails() {
        $valid_emails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'user+tag@example.org'
        ];
        
        foreach ($valid_emails as $email) {
            $this->assertTrue(
                $this->validation_service->isValidEmail($email),
                "El email {$email} debería ser válido"
            );
        }
    }

    /**
     * Test para validar emails inválidos
     */
    public function testInvalidEmails() {
        $invalid_emails = [
            'not-an-email',
            '@example.com',
            'user@',
            'user@.com'
        ];
        
        foreach ($invalid_emails as $email) {
            $this->assertFalse(
                $this->validation_service->isValidEmail($email),
                "El email {$email} debería ser inválido"
            );
        }
    }

    /**
     * Test para validar mensajes de WhatsApp
     */
    public function testValidWhatsAppMessages() {
        $valid_messages = [
            'Hola, ¿cómo estás?',
            'Mensaje con números 123',
            'Mensaje con símbolos: @#$%'
        ];
        
        foreach ($valid_messages as $message) {
            $errors = $this->validation_service->validateWhatsAppMessage($message);
            $this->assertEmpty($errors, "El mensaje '{$message}' debería ser válido");
        }
    }

    /**
     * Test para validar mensajes de WhatsApp inválidos
     */
    public function testInvalidWhatsAppMessages() {
        $invalid_messages = [
            '', // Vacío
            str_repeat('a', 4097) // Demasiado largo
        ];
        
        foreach ($invalid_messages as $message) {
            $errors = $this->validation_service->validateWhatsAppMessage($message);
            $this->assertNotEmpty($errors, "El mensaje debería ser inválido");
        }
    }

    /**
     * Test para sanitizar números de teléfono
     */
    public function testSanitizePhoneNumber() {
        $test_cases = [
            '1234567890' => '+1234567890',
            '+1234567890' => '+1234567890',
            '123-456-7890' => '+1234567890',
            '(123) 456-7890' => '+1234567890'
        ];
        
        foreach ($test_cases as $input => $expected) {
            $result = $this->validation_service->sanitizePhoneNumber($input);
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Test para sanitizar mensajes
     */
    public function testSanitizeMessage() {
        $test_cases = [
            '  Mensaje con espacios  ' => 'Mensaje con espacios',
            'Mensaje con caracteres de control' . chr(0) => 'Mensaje con caracteres de control',
            str_repeat('a', 5000) => str_repeat('a', 4096) // Truncado a 4096 caracteres
        ];
        
        foreach ($test_cases as $input => $expected) {
            $result = $this->validation_service->sanitizeMessage($input);
            $this->assertEquals($expected, $result);
        }
    }
}

/**
 * Tests para el servicio de WhatsApp
 */
class WhatsAppServiceTest extends TestCase {

    private $whatsapp_service;
    private $config_service;
    private $validation_service;

    protected function setUp(): void {
        parent::setUp();
        $this->config_service = new ConfigService();
        $this->validation_service = new ValidationService();
        $this->whatsapp_service = new WhatsAppService($this->config_service, $this->validation_service);
    }

    /**
     * Test para construir URL de WhatsApp
     */
    public function testBuildWhatsAppUrl() {
        $phone_number = '+1234567890';
        $message = 'Hola, ¿cómo estás?';
        
        $url = $this->whatsapp_service->buildWhatsAppUrl($phone_number, $message);
        
        $this->assertStringContainsString('wa.me', $url);
        $this->assertStringContainsString('1234567890', $url);
        $this->assertStringContainsString(urlencode($message), $url);
    }

    /**
     * Test para construir URL de WhatsApp sin mensaje
     */
    public function testBuildWhatsAppUrlWithoutMessage() {
        $phone_number = '+1234567890';
        
        $url = $this->whatsapp_service->buildWhatsAppUrl($phone_number);
        
        $this->assertStringContainsString('wa.me', $url);
        $this->assertStringContainsString('1234567890', $url);
        $this->assertStringNotContainsString('text=', $url);
    }

    /**
     * Test para verificar que el servicio de configuración está disponible
     */
    public function testConfigServiceIsAvailable() {
        $config_service = $this->whatsapp_service->getConfigService();
        
        $this->assertInstanceOf('WPWhatsAppBusiness\Services\ConfigService', $config_service);
    }

    /**
     * Test para verificar que el servicio de validación está disponible
     */
    public function testValidationServiceIsAvailable() {
        $validation_service = $this->whatsapp_service->getValidationService();
        
        $this->assertInstanceOf('WPWhatsAppBusiness\Services\ValidationService', $validation_service);
    }
} 