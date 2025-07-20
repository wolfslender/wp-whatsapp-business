/**
 * JavaScript principal para la administración de WhatsApp Business
 *
 * @package WPWhatsAppBusiness\Admin
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Variables globales
    var wpWhatsAppAdmin = {
        ajaxUrl: wpWhatsAppAdminVars.ajaxUrl,
        nonce: wpWhatsAppAdminVars.nonce,
        strings: wpWhatsAppAdminVars.strings,
        currentTab: 'general',
        previewTimeout: null
    };

    /**
     * Inicializar la administración
     */
    function init() {
        initTabs();
        initColorPickers();
        initMediaUploader();
        initRichTextEditors();
        initFormValidation();
        initRealTimePreview();
        initBulkActions();
        initImportExport();
        initPhoneTesting();
        initAccessibility();
    }

    /**
     * Inicializar sistema de pestañas
     */
    function initTabs() {
        $('.wp-whatsapp-tabs').on('click', '.nav-tab', function(e) {
            e.preventDefault();
            
            var target = $(this).data('tab');
            if (!target) return;

            // Actualizar pestañas activas
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Mostrar contenido correspondiente
            $('.tab-content').removeClass('active');
            $('#' + target + '-tab').addClass('active');

            // Guardar pestaña actual
            wpWhatsAppAdmin.currentTab = target;

            // Actualizar URL sin recargar
            var url = new URL(window.location);
            url.searchParams.set('tab', target);
            window.history.pushState({}, '', url);
        });

        // Restaurar pestaña desde URL
        var urlParams = new URLSearchParams(window.location.search);
        var activeTab = urlParams.get('tab');
        if (activeTab && $('[data-tab="' + activeTab + '"]').length) {
            $('[data-tab="' + activeTab + '"]').click();
        }
    }

    /**
     * Inicializar color pickers
     */
    function initColorPickers() {
        $('.wp-whatsapp-color-picker').wpColorPicker({
            change: function(event, ui) {
                updateWidgetPreview();
            }
        });
    }

    /**
     * Inicializar media uploader
     */
    function initMediaUploader() {
        $('.wp-whatsapp-media-upload').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var input = button.siblings('input[type="hidden"]');
            var preview = button.siblings('.media-preview');

            var frame = wp.media({
                title: wpWhatsAppAdmin.strings.selectMedia,
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.url);
                preview.html('<img src="' + attachment.url + '" alt="" style="max-width: 100px; height: auto;" />');
                updateWidgetPreview();
            });

            frame.open();
        });
    }

    /**
     * Inicializar editores de texto enriquecido
     */
    function initRichTextEditors() {
        $('.wp-whatsapp-rich-editor').each(function() {
            var editorId = $(this).attr('id');
            if (editorId && typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#' + editorId,
                    height: 200,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                             alignleft aligncenter alignright alignjustify | \
                             bullist numlist outdent indent | removeformat | help',
                    content_css: [
                        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                        '//www.tiny.cloud/css/codepen.min.css'
                    ],
                    setup: function(editor) {
                        editor.on('change', function() {
                            updateWidgetPreview();
                        });
                    }
                });
            }
        });
    }

    /**
     * Inicializar validación de formularios
     */
    function initFormValidation() {
        $('.wp-whatsapp-form').on('submit', function(e) {
            if (!validateForm($(this))) {
                e.preventDefault();
                return false;
            }
        });

        // Validación en tiempo real
        $('.wp-whatsapp-form input, .wp-whatsapp-form textarea, .wp-whatsapp-form select').on('blur', function() {
            validateField($(this));
        });

        $('.wp-whatsapp-form input, .wp-whatsapp-form textarea, .wp-whatsapp-form select').on('input', function() {
            clearFieldError($(this));
        });
    }

    /**
     * Validar formulario completo
     */
    function validateForm(form) {
        var isValid = true;
        
        form.find('input[required], textarea[required], select[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validar campo individual
     */
    function validateField(field) {
        var value = field.val().trim();
        var type = field.attr('type');
        var required = field.prop('required');
        var pattern = field.attr('pattern');

        // Limpiar errores previos
        clearFieldError(field);

        // Validar campo requerido
        if (required && !value) {
            showFieldError(field, wpWhatsAppAdmin.strings.fieldRequired);
            return false;
        }

        // Validar patrón
        if (pattern && value && !new RegExp(pattern).test(value)) {
            showFieldError(field, wpWhatsAppAdmin.strings.invalidFormat);
            return false;
        }

        // Validaciones específicas
        if (type === 'email' && value && !isValidEmail(value)) {
            showFieldError(field, wpWhatsAppAdmin.strings.invalidEmail);
            return false;
        }

        if (field.hasClass('phone-number') && value && !isValidPhone(value)) {
            showFieldError(field, wpWhatsAppAdmin.strings.invalidPhone);
            return false;
        }

        return true;
    }

    /**
     * Mostrar error de campo
     */
    function showFieldError(field, message) {
        field.addClass('error');
        var errorDiv = $('<div class="field-error">' + message + '</div>');
        field.after(errorDiv);
    }

    /**
     * Limpiar error de campo
     */
    function clearFieldError(field) {
        field.removeClass('error');
        field.siblings('.field-error').remove();
    }

    /**
     * Validar email
     */
    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Validar teléfono
     */
    function isValidPhone(phone) {
        var re = /^\+[1-9]\d{1,14}$/;
        return re.test(phone);
    }

    /**
     * Inicializar preview en tiempo real
     */
    function initRealTimePreview() {
        var previewContainer = $('#widget-preview');
        if (!previewContainer.length) return;

        // Actualizar preview cuando cambien los campos
        $('.wp-whatsapp-form input, .wp-whatsapp-form textarea, .wp-whatsapp-form select').on('change input', function() {
            clearTimeout(wpWhatsAppAdmin.previewTimeout);
            wpWhatsAppAdmin.previewTimeout = setTimeout(updateWidgetPreview, 500);
        });
    }

    /**
     * Actualizar preview del widget
     */
    function updateWidgetPreview() {
        var previewContainer = $('#widget-preview');
        if (!previewContainer.length) return;

        var formData = $('.wp-whatsapp-form').serialize();
        
        $.ajax({
            url: wpWhatsAppAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_whatsapp_preview_widget',
                form_data: formData,
                nonce: wpWhatsAppAdmin.nonce
            },
            beforeSend: function() {
                previewContainer.addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    previewContainer.html(response.data.html);
                } else {
                    previewContainer.html('<div class="preview-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                previewContainer.html('<div class="preview-error">' + wpWhatsAppAdmin.strings.previewError + '</div>');
            },
            complete: function() {
                previewContainer.removeClass('loading');
            }
        });
    }

    /**
     * Inicializar acciones en lote
     */
    function initBulkActions() {
        // Seleccionar todo
        $('.wp-whatsapp-bulk-select-all').on('change', function() {
            var checked = $(this).prop('checked');
            $('.wp-whatsapp-bulk-select').prop('checked', checked);
            updateBulkActions();
        });

        // Selección individual
        $('.wp-whatsapp-bulk-select').on('change', function() {
            updateBulkActions();
        });

        // Aplicar acción en lote
        $('.wp-whatsapp-bulk-apply').on('click', function(e) {
            e.preventDefault();
            
            var action = $('.wp-whatsapp-bulk-action').val();
            var selected = $('.wp-whatsapp-bulk-select:checked');
            
            if (!action || action === '-1') {
                alert(wpWhatsAppAdmin.strings.selectAction);
                return;
            }
            
            if (selected.length === 0) {
                alert(wpWhatsAppAdmin.strings.selectItems);
                return;
            }

            if (confirm(wpWhatsAppAdmin.strings.confirmBulkAction)) {
                executeBulkAction(action, selected);
            }
        });
    }

    /**
     * Actualizar acciones en lote
     */
    function updateBulkActions() {
        var selected = $('.wp-whatsapp-bulk-select:checked').length;
        var total = $('.wp-whatsapp-bulk-select').length;
        
        if (selected > 0) {
            $('.wp-whatsapp-bulk-apply').prop('disabled', false);
            $('.wp-whatsapp-bulk-count').text(selected + ' de ' + total + ' seleccionados');
        } else {
            $('.wp-whatsapp-bulk-apply').prop('disabled', true);
            $('.wp-whatsapp-bulk-count').text('');
        }
    }

    /**
     * Ejecutar acción en lote
     */
    function executeBulkAction(action, selected) {
        var ids = selected.map(function() {
            return $(this).val();
        }).get();

        $.ajax({
            url: wpWhatsAppAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_whatsapp_bulk_action',
                bulk_action: action,
                ids: ids,
                nonce: wpWhatsAppAdmin.nonce
            },
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(wpWhatsAppAdmin.strings.ajaxError);
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    /**
     * Inicializar import/export
     */
    function initImportExport() {
        // Exportar configuración
        $('.wp-whatsapp-export').on('click', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: wpWhatsAppAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_whatsapp_export_settings',
                    nonce: wpWhatsAppAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        downloadFile(response.data.filename, response.data.content);
                    } else {
                        showError(response.data.message);
                    }
                }
            });
        });

        // Importar configuración
        $('.wp-whatsapp-import').on('change', function() {
            var file = this.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function(e) {
                var content = e.target.result;
                
                $.ajax({
                    url: wpWhatsAppAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wp_whatsapp_import_settings',
                        content: content,
                        nonce: wpWhatsAppAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccess(response.data.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showError(response.data.message);
                        }
                    }
                });
            };
            reader.readAsText(file);
        });
    }

    /**
     * Inicializar testing de teléfono
     */
    function initPhoneTesting() {
        $('.wp-whatsapp-test-phone').on('click', function(e) {
            e.preventDefault();
            
            var phone = $('.wp-whatsapp-phone-input').val();
            if (!phone) {
                showError(wpWhatsAppAdmin.strings.enterPhone);
                return;
            }

            $.ajax({
                url: wpWhatsAppAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_whatsapp_test_phone',
                    phone: phone,
                    nonce: wpWhatsAppAdmin.nonce
                },
                beforeSend: function() {
                    showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.data.message);
                    } else {
                        showError(response.data.message);
                    }
                },
                error: function() {
                    showError(wpWhatsAppAdmin.strings.testError);
                },
                complete: function() {
                    hideLoading();
                }
            });
        });
    }

    /**
     * Inicializar accesibilidad
     */
    function initAccessibility() {
        // Navegación por teclado
        $('.wp-whatsapp-form').on('keydown', function(e) {
            if (e.key === 'Tab') {
                $(this).addClass('keyboard-navigation');
            }
        });

        $('.wp-whatsapp-form').on('mousedown', function() {
            $(this).removeClass('keyboard-navigation');
        });

        // ARIA labels dinámicos
        $('.wp-whatsapp-form input, .wp-whatsapp-form textarea, .wp-whatsapp-form select').each(function() {
            var field = $(this);
            var label = field.siblings('label').text();
            if (label) {
                field.attr('aria-label', label);
            }
        });

        // Focus management
        $('.wp-whatsapp-modal').on('shown', function() {
            $(this).find('input:first').focus();
        });
    }

    /**
     * Utilidades
     */
    function showLoading() {
        $('body').append('<div id="wp-whatsapp-loading" class="wp-whatsapp-loading"><div class="spinner"></div></div>');
    }

    function hideLoading() {
        $('#wp-whatsapp-loading').remove();
    }

    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'error');
    }

    function showNotification(message, type) {
        var notification = $('<div class="wp-whatsapp-notification wp-whatsapp-' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    function downloadFile(filename, content) {
        var blob = new Blob([content], { type: 'application/json' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(init);

})(jQuery); 