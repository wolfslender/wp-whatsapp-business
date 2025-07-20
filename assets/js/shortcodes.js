/**
 * WP WhatsApp Business - Shortcodes JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Configuración global
    const WPWhatsAppBusiness = window.wpWhatsAppBusiness || {};
    const config = WPWhatsAppBusiness.config || {};
    const strings = WPWhatsAppBusiness.strings || {};

    /**
     * Clase para manejar shortcodes de WhatsApp
     */
    class WhatsAppShortcodes {
        constructor() {
            this.init();
        }

        /**
         * Inicializar shortcodes
         */
        init() {
            this.bindEvents();
            this.initShortcodeEffects();
        }

        /**
         * Vincular eventos
         */
        bindEvents() {
            // Evento de clic en shortcodes
            $(document).on('click', '.whatsapp-shortcode, .whatsapp-button-shortcode', this.handleShortcodeClick.bind(this));
            
            // Evento de clic en enlaces de teléfono
            $(document).on('click', '.whatsapp-phone-link', this.handlePhoneClick.bind(this));
        }

        /**
         * Inicializar efectos de shortcodes
         */
        initShortcodeEffects() {
            // Agregar efectos de hover
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').each(function() {
                $(this).addClass('whatsapp-interactive');
            });

            // Agregar efectos de focus para accesibilidad
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').on('focus', function() {
                $(this).addClass('whatsapp-focused');
            }).on('blur', function() {
                $(this).removeClass('whatsapp-focused');
            });
        }

        /**
         * Manejar clic en shortcode
         */
        handleShortcodeClick(event) {
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

            // Trackear clic
            this.trackClick('whatsapp_shortcode', url);
        }

        /**
         * Manejar clic en enlace de teléfono
         */
        handlePhoneClick(event) {
            const link = $(event.currentTarget);
            const phone = link.attr('href').replace('tel:', '');

            // Agregar efecto de clic
            link.addClass('whatsapp-clicked');
            setTimeout(() => {
                link.removeClass('whatsapp-clicked');
            }, 150);

            // Trackear clic
            this.trackClick('whatsapp_phone', phone);
        }

        /**
         * Trackear clic (para analytics)
         */
        trackClick(type, data) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    'event_category': 'whatsapp',
                    'event_label': type,
                    'value': data
                });
            }

            // Enviar a Google Analytics 4 si está disponible
            if (typeof gtag !== 'undefined' && typeof gtag === 'function') {
                gtag('event', 'whatsapp_click', {
                    'whatsapp_type': type,
                    'whatsapp_data': data
                });
            }

            // Enviar a Facebook Pixel si está disponible
            if (typeof fbq !== 'undefined') {
                fbq('track', 'CustomEvent', {
                    event_name: 'whatsapp_click',
                    whatsapp_type: type,
                    whatsapp_data: data
                });
            }
        }

        /**
         * Actualizar estado de horarios de negocio
         */
        updateBusinessHours() {
            $('.whatsapp-hours-table, .whatsapp-hours-list').each(function() {
                const container = $(this);
                const today = new Date().getDay();
                const currentTime = new Date().getHours() + ':' + new Date().getMinutes();

                // Resaltar día actual
                container.find('.today').removeClass('today');
                container.find('tr, li').eq(today).addClass('today');

                // Actualizar estado de apertura
                container.find('.whatsapp-status').each(function() {
                    const status = $(this);
                    const isOpen = status.hasClass('open');
                    
                    if (isOpen) {
                        status.text(strings.open || 'Abierto');
                    } else {
                        status.text(strings.closed || 'Cerrado');
                    }
                });
            });
        }

        /**
         * Mostrar notificación
         */
        showNotification(message, type = 'info') {
            const notification = $('<div>')
                .addClass('whatsapp-notification')
                .addClass('whatsapp-notification-' + type)
                .text(message)
                .appendTo('body');

            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    }

    /**
     * Clase para analytics de shortcodes
     */
    class WhatsAppShortcodeAnalytics {
        constructor() {
            this.init();
        }

        /**
         * Inicializar analytics
         */
        init() {
            this.trackShortcodeViews();
            this.trackShortcodeInteractions();
        }

        /**
         * Trackear vistas de shortcodes
         */
        trackShortcodeViews() {
            const shortcodes = $('.whatsapp-shortcode, .whatsapp-button-shortcode, .whatsapp-phone, .whatsapp-hours-table, .whatsapp-hours-list');
            
            if (shortcodes.length > 0) {
                // Trackear vista de página con shortcodes
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'view', {
                        'event_category': 'whatsapp_shortcodes',
                        'event_label': 'shortcodes_present',
                        'value': shortcodes.length
                    });
                }
            }
        }

        /**
         * Trackear interacciones con shortcodes
         */
        trackShortcodeInteractions() {
            // Trackear hover en shortcodes
            $('.whatsapp-shortcode, .whatsapp-button-shortcode').on('mouseenter', function() {
                const type = $(this).hasClass('whatsapp-shortcode') ? 'link' : 'button';
                
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'hover', {
                        'event_category': 'whatsapp_shortcode',
                        'event_label': type
                    });
                }
            });
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Inicializar shortcodes
        new WhatsAppShortcodes();
        
        // Inicializar analytics
        new WhatsAppShortcodeAnalytics();
        
        // Actualizar horarios de negocio cada minuto
        setInterval(function() {
            new WhatsAppShortcodes().updateBusinessHours();
        }, 60000);
    });

})(jQuery); 