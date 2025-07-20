# WP WhatsApp Business

Plugin empresarial para WordPress que permite la integración completa con WhatsApp Business API.

## Características

- **Widget flotante personalizable** - Botón de WhatsApp que aparece en todas las páginas
- **Shortcodes flexibles** - Múltiples shortcodes para diferentes necesidades
- **Configuración avanzada** - Panel de administración completo con validaciones
- **Horarios de negocio** - Control automático de disponibilidad según horarios
- **Mensajes personalizados** - Diferentes mensajes por página/post
- **Integración con API** - Envío de mensajes a través de WhatsApp Business API
- **Responsive design** - Funciona perfectamente en móviles y tablets
- **Multilingüe** - Soporte completo para internacionalización

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Composer (para instalación de dependencias)
- Cuenta de WhatsApp Business API

## Instalación

### 1. Instalación manual

1. Descarga el plugin y extráelo en la carpeta `/wp-content/plugins/`
2. Navega a la carpeta del plugin y ejecuta:
   ```bash
   composer install
   ```
3. Activa el plugin desde el panel de administración de WordPress
4. Configura tu API Key de WhatsApp Business en la página de configuración

### 2. Instalación con Composer

```bash
composer require wp-whatsapp-business/wp-whatsapp-business
```

## Configuración

### Configuración básica

1. Ve a **WhatsApp Business > Configuración** en el panel de administración
2. Ingresa tu **API Key** de WhatsApp Business
3. Configura tu **Phone Number ID**
4. Ingresa tu **número de teléfono** en formato internacional (+1234567890)
5. Establece el **nombre de tu negocio**
6. Habilita el plugin

### Configuración de horarios

Configura los horarios de tu negocio para que el widget solo aparezca cuando estés disponible:

- **Lunes a Viernes**: 09:00 - 18:00
- **Sábado**: 10:00 - 14:00
- **Domingo**: Cerrado

### Configuración de apariencia

Personaliza la apariencia del widget:

- **Color del widget**: Personaliza el color principal
- **Posición**: bottom-right, bottom-left, top-right, top-left
- **Tamaño**: small, medium, large
- **Texto personalizado**: Mensaje que aparece en el widget

## Uso

### Widget automático

Una vez configurado, el widget aparecerá automáticamente en todas las páginas del frontend.

### Shortcodes disponibles

#### Shortcode básico
```
[whatsapp]
```

#### Shortcode con parámetros
```
[whatsapp phone="+1234567890" message="¡Hola! ¿Necesitas ayuda?" text="Chatear ahora"]
```

#### Botón de WhatsApp
```
[whatsapp_button phone="+1234567890" text="Contactar por WhatsApp" size="large" color="#25D366"]
```

#### Número de teléfono
```
[whatsapp_phone format="link"]
```

#### Horarios de negocio
```
[whatsapp_hours format="table"]
```

#### Estado del negocio
```
[whatsapp_status]
```

### Meta boxes

En cada post y página encontrarás un meta box de WhatsApp Business que te permite:

- Habilitar/deshabilitar el widget para esa página específica
- Configurar un mensaje personalizado
- Establecer un número de teléfono específico

## API de WhatsApp Business

### Envío de mensajes

```php
// Obtener el servicio de WhatsApp
$whatsapp_service = WPWhatsAppBusiness\Core\Plugin::getInstance()->getContainer()->get('WPWhatsAppBusiness\Services\WhatsAppService');

// Enviar mensaje de texto
$result = $whatsapp_service->sendTextMessage('+1234567890', 'Hola, ¿cómo estás?');

// Enviar mensaje con imagen
$result = $whatsapp_service->sendImageMessage('+1234567890', 'https://example.com/image.jpg', 'Mira esta imagen');

// Enviar mensaje con botones
$buttons = [
    [
        'type' => 'reply',
        'reply' => [
            'id' => 'btn_1',
            'title' => 'Sí, me interesa'
        ]
    ],
    [
        'type' => 'reply',
        'reply' => [
            'id' => 'btn_2',
            'title' => 'No, gracias'
        ]
    ]
];

$result = $whatsapp_service->sendButtonMessage('+1234567890', '¿Te interesa nuestro producto?', 'Responde con uno de los botones', $buttons);
```

### Hooks y filtros

#### Filtros disponibles

```php
// Modificar si se debe mostrar el widget
add_filter('wp_whatsapp_business_should_show_widget', function($should_show) {
    // Tu lógica personalizada
    return $should_show;
});

// Modificar la configuración del widget
add_filter('wp_whatsapp_business_widget_config', function($config) {
    // Modificar configuración
    return $config;
});
```

#### Acciones disponibles

```php
// Cuando se envía un mensaje
add_action('wp_whatsapp_business_message_sent', function($result, $phone_number, $message) {
    // Tu código personalizado
}, 10, 3);

// Cuando hay un error al enviar mensaje
add_action('wp_whatsapp_business_message_error', function($error, $phone_number, $message) {
    // Tu código personalizado
}, 10, 3);
```

## Estructura del plugin

```
wp-whatsapp-business/
├── wp-whatsapp-business.php          # Archivo principal del plugin
├── composer.json                     # Configuración de Composer
├── README.md                         # Este archivo
├── src/
│   ├── Core/                         # Núcleo del plugin
│   │   ├── Plugin.php               # Clase principal (Singleton)
│   │   ├── Container.php            # Container de inyección de dependencias
│   │   └── Loader.php               # Cargador de hooks
│   ├── Admin/                       # Interfaz de administración
│   │   ├── AdminInterface.php       # Interfaz principal del admin
│   │   ├── Settings.php             # Configuración avanzada
│   │   ├── MetaBoxes.php            # Meta boxes
│   │   └── views/                   # Vistas del admin
│   ├── Frontend/                    # Frontend
│   │   ├── Widget.php               # Widget flotante
│   │   ├── Shortcodes.php           # Shortcodes
│   │   └── Assets.php               # Gestión de assets
│   └── Services/                    # Servicios
│       ├── WhatsAppService.php      # Servicio de WhatsApp API
│       ├── ConfigService.php        # Servicio de configuración
│       └── ValidationService.php    # Servicio de validación
├── assets/                          # Assets del frontend
│   ├── css/                         # Hojas de estilo
│   └── js/                          # Scripts JavaScript
├── languages/                       # Archivos de idioma
└── tests/                          # Tests unitarios
```

## Desarrollo

### Instalación para desarrollo

```bash
git clone https://github.com/tu-usuario/wp-whatsapp-business.git
cd wp-whatsapp-business
composer install
composer install --dev
```

### Ejecutar tests

```bash
composer test
```

### Verificar código

```bash
composer phpcs
composer phpcbf
```

### Generar documentación

```bash
composer docs
```

## Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia GPL v2 o posterior - ver el archivo [LICENSE](LICENSE) para más detalles.

## Soporte

Para soporte técnico, por favor contacta a través de:

- **Email**: soporte@example.com
- **WhatsApp**: +1234567890
- **Documentación**: https://docs.example.com

## Changelog

### 1.0.0
- Lanzamiento inicial
- Widget flotante personalizable
- Shortcodes básicos
- Integración con WhatsApp Business API
- Panel de administración completo
- Soporte para horarios de negocio
- Meta boxes para posts y páginas

## Créditos

Desarrollado con ❤️ para la comunidad de WordPress.

---

**Nota**: Este plugin requiere una cuenta de WhatsApp Business API activa para funcionar correctamente. 