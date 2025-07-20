<?php

namespace WPWhatsAppBusiness\Services\Config;

/**
 * Clase de configuración inmutable para WhatsApp Business
 */
final class WhatsAppConfig
{
    private string $apiKey;
    private string $phoneNumberId;
    private string $phoneNumber;
    private string $businessName;
    private bool $enabled;
    private array $businessHours;
    private array $widgetSettings;
    private array $messageTemplates;
    private array $rateLimitSettings;
    private string $version;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? '';
        $this->phoneNumber = $config['phone_number'] ?? '';
        $this->businessName = $config['business_name'] ?? '';
        $this->enabled = $config['enabled'] ?? false;
        $this->businessHours = $config['business_hours'] ?? $this->getDefaultBusinessHours();
        $this->widgetSettings = $config['widget_settings'] ?? $this->getDefaultWidgetSettings();
        $this->messageTemplates = $config['message_templates'] ?? $this->getDefaultMessageTemplates();
        $this->rateLimitSettings = $config['rate_limit_settings'] ?? $this->getDefaultRateLimitSettings();
        $this->version = $config['version'] ?? '1.0.0';
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getPhoneNumberId(): string
    {
        return $this->phoneNumberId;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getBusinessName(): string
    {
        return $this->businessName;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getBusinessHours(): array
    {
        return $this->businessHours;
    }

    public function getWidgetSettings(): array
    {
        return $this->widgetSettings;
    }

    public function getMessageTemplates(): array
    {
        return $this->messageTemplates;
    }

    public function getRateLimitSettings(): array
    {
        return $this->rateLimitSettings;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function toArray(): array
    {
        return [
            'api_key' => $this->apiKey,
            'phone_number_id' => $this->phoneNumberId,
            'phone_number' => $this->phoneNumber,
            'business_name' => $this->businessName,
            'enabled' => $this->enabled,
            'business_hours' => $this->businessHours,
            'widget_settings' => $this->widgetSettings,
            'message_templates' => $this->messageTemplates,
            'rate_limit_settings' => $this->rateLimitSettings,
            'version' => $this->version,
        ];
    }

    public function withApiKey(string $apiKey): self
    {
        $new = clone $this;
        $new->apiKey = $apiKey;
        return $new;
    }

    public function withPhoneNumberId(string $phoneNumberId): self
    {
        $new = clone $this;
        $new->phoneNumberId = $phoneNumberId;
        return $new;
    }

    public function withPhoneNumber(string $phoneNumber): self
    {
        $new = clone $this;
        $new->phoneNumber = $phoneNumber;
        return $new;
    }

    public function withBusinessName(string $businessName): self
    {
        $new = clone $this;
        $new->businessName = $businessName;
        return $new;
    }

    public function withEnabled(bool $enabled): self
    {
        $new = clone $this;
        $new->enabled = $enabled;
        return $new;
    }

    public function withBusinessHours(array $businessHours): self
    {
        $new = clone $this;
        $new->businessHours = $businessHours;
        return $new;
    }

    public function withWidgetSettings(array $widgetSettings): self
    {
        $new = clone $this;
        $new->widgetSettings = $widgetSettings;
        return $new;
    }

    public function withMessageTemplates(array $messageTemplates): self
    {
        $new = clone $this;
        $new->messageTemplates = $messageTemplates;
        return $new;
    }

    public function withRateLimitSettings(array $rateLimitSettings): self
    {
        $new = clone $this;
        $new->rateLimitSettings = $rateLimitSettings;
        return $new;
    }

    public function withVersion(string $version): self
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    private function getDefaultBusinessHours(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'tuesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'wednesday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'thursday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'friday' => ['open' => '09:00', 'close' => '18:00', 'enabled' => true],
            'saturday' => ['open' => '10:00', 'close' => '14:00', 'enabled' => true],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'enabled' => false],
        ];
    }

    private function getDefaultWidgetSettings(): array
    {
        return [
            'position' => 'bottom-right',
            'size' => 'medium',
            'color' => '#25D366',
            'text' => '¿Necesitas ayuda?',
            'enabled' => true,
            'show_on_mobile' => true,
            'show_on_desktop' => true,
        ];
    }

    private function getDefaultMessageTemplates(): array
    {
        return [
            'default' => '¡Hola! ¿En qué puedo ayudarte?',
            'welcome' => '¡Bienvenido a {business_name}! ¿Cómo puedo asistirte hoy?',
            'support' => 'Hola, necesito soporte técnico.',
            'sales' => 'Hola, me interesa conocer más sobre sus productos.',
            'appointment' => 'Hola, me gustaría agendar una cita.',
        ];
    }

    private function getDefaultRateLimitSettings(): array
    {
        return [
            'enabled' => true,
            'max_requests_per_hour' => 100,
            'max_requests_per_day' => 1000,
            'time_window' => 3600,
        ];
    }
} 