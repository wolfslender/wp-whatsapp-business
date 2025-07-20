<?php

namespace WPWhatsAppBusiness\Tests\Services;

use PHPUnit\Framework\TestCase;
use WPWhatsAppBusiness\Services\WhatsAppService;
use WPWhatsAppBusiness\Services\ConfigService;
use WPWhatsAppBusiness\Services\ValidationService;
use WPWhatsAppBusiness\Services\Config\WhatsAppConfig;

/**
 * Test unitario para WhatsAppService
 */
class WhatsAppServiceTest extends TestCase
{
    private WhatsAppService $whatsappService;
    private ConfigService $configService;
    private ValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear mocks para las dependencias
        $this->validationService = $this->createMock(ValidationService::class);
        $this->configService = $this->createMock(ConfigService::class);
        
        // Configurar el mock de ConfigService
        $config = new WhatsAppConfig([
            'api_key' => 'test_api_key',
            'phone_number_id' => '123456789',
            'phone_number' => '+1234567890',
            'business_name' => 'Test Business',
            'enabled' => true,
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
                'tuesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
                'wednesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
                'thursday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
                'friday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
                'saturday' => ['open' => '10:00', 'close' => '14:00', 'enabled' => true],
                'sunday' => ['open' => '00:00', 'close' => '00:00', 'enabled' => false],
            ],
            'rate_limit_settings' => [
                'enabled' => true,
                'max_requests_per_hour' => 100,
                'max_requests_per_day' => 1000,
                'time_window' => 3600,
            ]
        ]);
        
        $this->configService->method('getConfig')->willReturn($config);
        
        // Crear instancia del servicio
        $this->whatsappService = new WhatsAppService($this->configService, $this->validationService);
    }

    public function testValidatePhoneNumberWithValidNumber(): void
    {
        $phoneNumber = '+1234567890';
        
        $this->validationService
            ->expects($this->once())
            ->method('validatePhoneNumber')
            ->with($phoneNumber)
            ->willReturn([]);
            
        $this->validationService
            ->method('sanitize')
            ->willReturnArgument(0);

        $result = $this->whatsappService->validatePhoneNumber($phoneNumber);
        
        $this->assertTrue($result);
    }

    public function testValidatePhoneNumberWithInvalidNumber(): void
    {
        $phoneNumber = 'invalid_number';
        
        $this->validationService
            ->expects($this->once())
            ->method('validatePhoneNumber')
            ->with($phoneNumber)
            ->willReturn(['phone_number' => ['Número inválido']]);

        $result = $this->whatsappService->validatePhoneNumber($phoneNumber);
        
        $this->assertFalse($result);
    }

    public function testFormatPhoneNumberWithValidInput(): void
    {
        $testCases = [
            '+1234567890' => '+1234567890',
            '1234567890' => '+1234567890',
            '+1 (234) 567-890' => '+1234567890',
            '1-234-567-890' => '+1234567890',
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->whatsappService->formatPhoneNumber($input);
            $this->assertEquals($expected, $result, "Failed for input: {$input}");
        }
    }

    public function testFormatPhoneNumberWithInvalidInput(): void
    {
        $testCases = [
            '',
            'abc',
            '123',
            '+',
            '++1234567890',
        ];

        foreach ($testCases as $input) {
            $result = $this->whatsappService->formatPhoneNumber($input);
            $this->assertEquals('', $result, "Should return empty string for invalid input: {$input}");
        }
    }

    public function testGenerateWhatsAppUrl(): void
    {
        $phoneNumber = '+1234567890';
        $message = 'Hola, ¿cómo estás?';
        
        $result = $this->whatsappService->generateWhatsAppUrl($phoneNumber, $message, 'web');
        
        $expectedUrl = 'https://wa.me/1234567890?text=' . urlencode($message);
        $this->assertEquals($expectedUrl, $result);
    }

    public function testGenerateWhatsAppUrlWithMobileType(): void
    {
        $phoneNumber = '+1234567890';
        
        $result = $this->whatsappService->generateWhatsAppUrl($phoneNumber, '', 'mobile');
        
        $expectedUrl = 'whatsapp://send?phone=1234567890';
        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetTemplateMessage(): void
    {
        $templateName = 'welcome';
        $variables = ['customer_name' => 'Juan'];
        
        $result = $this->whatsappService->getTemplateMessage($templateName, $variables);
        
        // Debería reemplazar las variables en la plantilla
        $this->assertStringContainsString('Juan', $result);
        $this->assertStringContainsString('Test Business', $result);
    }

    public function testGetTemplateMessageWithNonExistentTemplate(): void
    {
        $templateName = 'non_existent';
        
        $result = $this->whatsappService->getTemplateMessage($templateName);
        
        // Debería devolver la plantilla por defecto
        $this->assertStringContainsString('¿En qué puedo ayudarte?', $result);
    }

    public function testDetectDevice(): void
    {
        // Simular User-Agent de móvil
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15';
        
        $result = $this->whatsappService->detectDevice();
        
        $this->assertEquals('mobile', $result);
        
        // Simular User-Agent de desktop
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        
        $result = $this->whatsappService->detectDevice();
        
        $this->assertEquals('desktop', $result);
    }

    public function testIsBusinessOpen(): void
    {
        // Mock de current_time para simular diferentes horarios
        $this->mockCurrentTime('2024-01-15 10:00:00'); // Lunes 10:00 AM
        
        $result = $this->whatsappService->isBusinessOpen();
        
        $this->assertTrue($result);
    }

    public function testIsBusinessOpenWhenClosed(): void
    {
        // Mock de current_time para simular horario fuera de servicio
        $this->mockCurrentTime('2024-01-15 20:00:00'); // Lunes 8:00 PM
        
        $result = $this->whatsappService->isBusinessOpen();
        
        $this->assertFalse($result);
    }

    public function testIsBusinessOpenOnSunday(): void
    {
        // Mock de current_time para simular domingo
        $this->mockCurrentTime('2024-01-14 12:00:00'); // Domingo 12:00 PM
        
        $result = $this->whatsappService->isBusinessOpen();
        
        $this->assertFalse($result);
    }

    public function testSendTextMessageWithValidData(): void
    {
        $phoneNumber = '+1234567890';
        $message = 'Hola, ¿cómo estás?';
        
        // Configurar mocks
        $this->validationService
            ->method('validatePhoneNumber')
            ->willReturn([]);
            
        $this->validationService
            ->method('validateMessage')
            ->willReturn([]);
            
        $this->validationService
            ->method('sanitize')
            ->willReturnArgument(0);

        // Mock de wp_remote_post
        $this->mockWpRemotePost([
            'response' => ['code' => 200],
            'body' => json_encode(['id' => 'test_message_id'])
        ]);

        $result = $this->whatsappService->sendTextMessage($phoneNumber, $message);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testSendTextMessageWithInvalidPhoneNumber(): void
    {
        $phoneNumber = 'invalid';
        $message = 'Hola';
        
        $this->validationService
            ->method('validatePhoneNumber')
            ->willReturn(['phone_number' => ['Número inválido']]);

        $result = $this->whatsappService->sendTextMessage($phoneNumber, $message);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('validation_error', $result['error']['type']);
    }

    public function testSendTextMessageWithInvalidMessage(): void
    {
        $phoneNumber = '+1234567890';
        $message = '';
        
        $this->validationService
            ->method('validatePhoneNumber')
            ->willReturn([]);
            
        $this->validationService
            ->method('validateMessage')
            ->willReturn(['message' => ['El mensaje es requerido']]);

        $result = $this->whatsappService->sendTextMessage($phoneNumber, $message);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('validation_error', $result['error']['type']);
    }

    private function mockCurrentTime(string $time): void
    {
        // Esta función simula el comportamiento de current_time()
        // En un entorno real, usarías un mock más sofisticado
        if (!function_exists('current_time')) {
            function current_time($format = 'Y-m-d H:i:s') {
                return '2024-01-15 10:00:00';
            }
        }
    }

    private function mockWpRemotePost(array $response): void
    {
        // Mock de wp_remote_post para testing
        if (!function_exists('wp_remote_post')) {
            function wp_remote_post($url, $args) {
                return [
                    'response' => ['code' => 200],
                    'body' => json_encode(['id' => 'test_message_id'])
                ];
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Limpiar variables globales
        unset($_SERVER['HTTP_USER_AGENT']);
    }
} 