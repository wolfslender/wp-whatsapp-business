/**
 * WP WhatsApp Business - Frontend JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Configuración global
    const WPWhatsAppBusiness = window.wpWhatsAppBusiness || {};
    const config = WPWhatsAppBusiness.config || {};
    const strings = WPWhatsAppBusiness.strings || {};

    /**
     * Clase principal del frontend
     */
    class WhatsAppBusinessFrontend {
        constructor() {
            this.widget = null;
            this.isVisible = false;
            this.lastScrollTop = 0;
            this.init();
        }

        /**
         * Inicializar el frontend
         */
        init() {
            this.bindEvents();
            this.initWidget();
            this.initShortcodes();
        }

        /**
         * Vincular eventos
         */
        bindEvents() {
            // Evento de scroll para ocultar/mostrar widget
            $(window).on('scroll', this.handleScroll.bind(this));
            
            // Evento de resize para ajustar posiciones
            $(window).on('resize', this.handleResize.bind(this));
            
            // Evento de clic en enlaces de WhatsApp
            $(document).on('click', 'a[href*="wa.me"], a[href*="whatsapp.com"], .whatsapp-shortcode, .whatsapp-button-shortcode', this.handleWhatsAppClick.bind(this));
            
            // Evento de clic en el widget
            $(document).on('click', '.wp-whatsapp-business-button', this.handleWidgetClick.bind(this));
        }

        /**
         * Inicializar widget
         */
        initWidget() {
            this.widget = $('#wp-whatsapp-business-widget');
            
            if (this.widget.length) {
                this.isVisible = true;
                this.addWidgetEffects();
            }
        }

        /**
         * Inicializar shortcodes
         */
        initShortcodes() {
            // Agregar efectos a shortcodes
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').each(function() {
                $(this).addClass('whatsapp-interactive');
            });
        }

        /**
         * Manejar scroll
         */
        handleScroll() {
            if (!this.widget || !this.isVisible) return;

            const scrollTop = $(window).scrollTop();
            const scrollThreshold = 100;

            // Ocultar widget al hacer scroll hacia abajo, mostrar al hacer scroll hacia arriba
            if (scrollTop > this.lastScrollTop && scrollTop > scrollThreshold) {
                this.hideWidget();
            } else {
                this.showWidget();
            }

            this.lastScrollTop = scrollTop;
        }

        /**
         * Manejar resize
         */
        handleResize() {
            if (!this.widget) return;

            // Ajustar posición en dispositivos móviles
            if ($(window).width() <= 768) {
                this.widget.removeClass('wp-whatsapp-business-top-left wp-whatsapp-business-top-right wp-whatsapp-business-bottom-left')
                           .addClass('wp-whatsapp-business-bottom-right');
            }
        }

        /**
         * Manejar clic en enlaces de WhatsApp
         */
        handleWhatsAppClick(event) {
            const link = $(event.currentTarget);
            const url = link.attr('href');

            // Agregar efecto de clic
            link.addClass('whatsapp-clicked');
            setTimeout(() => {
                link.removeClass('whatsapp-clicked');
            }, 150);

            // Abrir en nueva ventana
            window.open(url, '_blank', 'noopener,noreferrer');
            event.preventDefault();

            // Trackear clic si está disponible
            this.trackClick('whatsapp_link', url);
        }

        /**
         * Manejar clic en el widget
         */
        handleWidgetClick(event) {
            const button = $(event.currentTarget);
            const url = button.attr('href');

            // Agregar efecto de clic
            button.addClass('whatsapp-widget-clicked');
            setTimeout(() => {
                button.removeClass('whatsapp-widget-clicked');
            }, 150);

            // Abrir en nueva ventana
            window.open(url, '_blank', 'noopener,noreferrer');
            event.preventDefault();

            // Trackear clic si está disponible
            this.trackClick('whatsapp_widget', url);
        }

        /**
         * Ocultar widget
         */
        hideWidget() {
            if (this.widget && this.isVisible) {
                this.widget.addClass('wp-whatsapp-business-fade-out');
                setTimeout(() => {
                    this.widget.hide();
                    this.isVisible = false;
                }, 300);
            }
        }

        /**
         * Mostrar widget
         */
        showWidget() {
            if (this.widget && !this.isVisible) {
                this.widget.removeClass('wp-whatsapp-business-fade-out')
                           .addClass('wp-whatsapp-business-fade-in')
                           .show();
                this.isVisible = true;
                
                setTimeout(() => {
                    this.widget.removeClass('wp-whatsapp-business-fade-in');
                }, 300);
            }
        }

        /**
         * Agregar efectos al widget
         */
        addWidgetEffects() {
            // Efecto de hover
            this.widget.hover(
                function() {
                    $(this).addClass('wp-whatsapp-business-hover');
                },
                function() {
                    $(this).removeClass('wp-whatsapp-business-hover');
                }
            );

            // Efecto de focus para accesibilidad
            this.widget.find('.wp-whatsapp-business-button').on('focus', function() {
                $(this).closest('.wp-whatsapp-business-widget').addClass('wp-whatsapp-business-focused');
            }).on('blur', function() {
                $(this).closest('.wp-whatsapp-business-widget').removeClass('wp-whatsapp-business-focused');
            });
        }

        /**
         * Trackear clic (para analytics)
         */
        trackClick(type, url) {
            // Enviar evento a Google Analytics si está disponible
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_click', {
                    'event_category': 'engagement',
                    'event_label': type,
                    'value': 1
                });
            }

            // Enviar evento a Facebook Pixel si está disponible
            if (typeof fbq !== 'undefined') {
                fbq('track', 'CustomEvent', {
                    event_name: 'whatsapp_click',
                    event_type: type,
                    event_url: url
                });
            }

            // Enviar evento personalizado
            $(document).trigger('wp_whatsapp_business_click', [type, url]);
        }

        /**
         * Verificar si el negocio está abierto
         */
        isBusinessOpen() {
            if (!config.business_hours) return true;

            const now = new Date();
            const currentDay = now.toLocaleDateString('en-US', { weekday: 'lowercase' });
            const currentTime = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });

            const dayHours = config.business_hours[currentDay];
            if (!dayHours || dayHours === 'closed') return false;

            if (Array.isArray(dayHours) && dayHours.length >= 2) {
                const openTime = dayHours[0];
                const closeTime = dayHours[1];
                return currentTime >= openTime && currentTime <= closeTime;
            }

            return true;
        }

        /**
         * Actualizar estado del widget según horarios
         */
        updateWidgetStatus() {
            if (!this.isBusinessOpen()) {
                this.widget.addClass('wp-whatsapp-business-closed');
                this.widget.find('.wp-whatsapp-business-subtitle').text(strings.closed || 'Cerrado');
            } else {
                this.widget.removeClass('wp-whatsapp-business-closed');
            }
        }

        /**
         * Mostrar notificación
         */
        showNotification(message, type = 'info') {
            const notification = $(`
                <div class="wp-whatsapp-business-notification wp-whatsapp-business-notification-${type}">
                    <span>${message}</span>
                    <button class="wp-whatsapp-business-notification-close">&times;</button>
                </div>
            `);

            $('body').append(notification);

            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                notification.fadeOut(() => {
                    notification.remove();
                });
            }, 5000);

            // Cerrar manualmente
            notification.find('.wp-whatsapp-business-notification-close').on('click', function() {
                notification.fadeOut(() => {
                    notification.remove();
                });
            });
        }
    }

    /**
     * Clase para manejar shortcodes
     */
    class WhatsAppShortcodes {
        constructor() {
            this.init();
        }

        init() {
            this.initShortcodeEffects();
            this.initShortcodeInteractions();
        }

        /**
         * Inicializar efectos de shortcodes
         */
        initShortcodeEffects() {
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').each(function() {
                const shortcode = $(this);
                
                // Efecto de hover
                shortcode.hover(
                    function() {
                        $(this).addClass('whatsapp-shortcode-hover');
                    },
                    function() {
                        $(this).removeClass('whatsapp-shortcode-hover');
                    }
                );

                // Efecto de focus
                shortcode.on('focus', function() {
                    $(this).addClass('whatsapp-shortcode-focused');
                }).on('blur', function() {
                    $(this).removeClass('whatsapp-shortcode-focused');
                });
            });
        }

        /**
         * Inicializar interacciones de shortcodes
         */
        initShortcodeInteractions() {
            // Contador de clics para shortcodes
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').on('click', function() {
                const shortcode = $(this);
                const type = shortcode.hasClass('whatsapp-button-shortcode') ? 'button' : 'link';
                
                // Incrementar contador de clics
                let clickCount = parseInt(shortcode.data('click-count') || 0);
                shortcode.data('click-count', clickCount + 1);

                // Trackear clic
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'whatsapp_shortcode_click', {
                        'event_category': 'engagement',
                        'event_label': type,
                        'value': 1
                    });
                }
            });
        }
    }

    /**
     * Clase para manejar analytics
     */
    class WhatsAppAnalytics {
        constructor() {
            this.init();
        }

        init() {
            this.trackPageViews();
            this.trackWidgetVisibility();
        }

        /**
         * Trackear vistas de página
         */
        trackPageViews() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_widget_page_view', {
                    'event_category': 'engagement',
                    'event_label': window.location.pathname
                });
            }
        }

        /**
         * Trackear visibilidad del widget
         */
        trackWidgetVisibility() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'whatsapp_widget_visible', {
                                'event_category': 'engagement',
                                'event_label': 'widget_visible'
                            });
                        }
                    }
                });
            });

            const widget = document.getElementById('wp-whatsapp-business-widget');
            if (widget) {
                observer.observe(widget);
            }
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Inicializar frontend
        window.wpWhatsAppBusinessFrontend = new WhatsAppBusinessFrontend();
        
        // Inicializar shortcodes
        window.wpWhatsAppShortcodes = new WhatsAppShortcodes();
        
        // Inicializar analytics
        window.wpWhatsAppAnalytics = new WhatsAppAnalytics();
        
        // Actualizar estado del widget según horarios
        if (window.wpWhatsAppBusinessFrontend) {
            window.wpWhatsAppBusinessFrontend.updateWidgetStatus();
        }
    });

    // Exponer clases globalmente para debugging
    window.WPWhatsAppBusinessClasses = {
        Frontend: WhatsAppBusinessFrontend,
        Shortcodes: WhatsAppShortcodes,
        Analytics: WhatsAppAnalytics
    };

})(jQuery); 