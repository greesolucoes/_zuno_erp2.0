(function ($) {
    'use strict';

    var VetBilling = {
        selectors: {
            form: '#vet-billing-form',
            submitButton: '.vet-billing-submit',
            serviceTable: '.vet-billing-services',
            productTable: '.vet-billing-products',
            serviceTemplate: '#vet-billing-service-template tr',
            productTemplate: '#vet-billing-product-template tr',
        },

        isSubmitting: false,

        init: function () {
            this.cacheDom();
            this.bindEvents();
            this.initializeDynamicFields();
            this.updateTotals();
            this.updateSaveButton();
        },

        cacheDom: function () {
            this.$form = $(this.selectors.form);
            this.$submitButton = this.$form.find(this.selectors.submitButton);
            this.$serviceTable = $(this.selectors.serviceTable);
            this.$productTable = $(this.selectors.productTable);

            this.defaultButtonText = this.$submitButton.data('default-text') || 'Salvar faturamento';
            this.loadingButtonText = this.$submitButton.data('loading-text') || 'Salvando...';

            this.$submitButton.data('default-text', this.defaultButtonText);
            this.$submitButton.data('loading-text', this.loadingButtonText);
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '.vet-billing-add-service', function (event) {
                event.preventDefault();
                self.addServiceRow();
            });

            $(document).on('click', '.vet-billing-add-product', function (event) {
                event.preventDefault();
                self.addProductRow();
            });

            $(document).on('click', '.vet-billing-remove-row', function (event) {
                event.preventDefault();
                self.removeRow($(this).closest('tr'));
            });

            $(document).on('change', '.vet-billing-service-select', function () {
                self.updateSaveButton();
            });

            $(document).on('blur change', '.vet-billing-service-value', function () {
                self.updateTotals();
            });

            $(document).on('blur change', '.vet-billing-product-quantity', function () {
                self.updateProductSubtotal($(this).closest('tr'));
            });

            $(document).on('submit', self.selectors.form, function (event) {
                event.preventDefault();
                self.submitForm();
            });
        },

        initializeDynamicFields: function (context) {
            var $context = context ? $(context) : $(document);

            this.initializeServiceSelects($context.find('.vet-billing-service-select'));
            this.initializeProductSelects($context.find('.vet-billing-product-select'));
            this.updateSaveButton();
        },

        initializeServiceSelects: function ($elements) {
            var self = this;
            var vetServiceCategory = 'ATENDIMENTO VETERINARIO';

            $elements.each(function () {
                var $select = $(this);

                if ($select.data('select2')) {
                    $select.off('select2:select');
                    $select.select2('destroy');
                }

                $select.select2({
                    minimumInputLength: 2,
                    language: 'pt-BR',
                    placeholder: $select.data('placeholder') || 'Buscar serviço',
                    width: '100%',
                    ajax: {
                        cache: true,
                        url: path_url + 'api/petshop/servicos',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                pesquisa: params.term,
                                empresa_id: $('#empresa_id').val(),
                                categoria: vetServiceCategory,
                            };
                        },
                        processResults: function (response) {
                            return {
                                results: $.map(response, function (item) {
                                    return {
                                        id: item.id,
                                        text: item.nome + ' • R$ ' + convertFloatToMoeda(item.valor || 0),
                                        valor: item.valor || 0,
                                        categoria: item.categoria ? item.categoria.nome : '',
                                    };
                                }),
                            };
                        },
                    },
                }).on('select2:select', function (event) {
                    var data = event.params.data || {};
                    var $row = $select.closest('tr');

                    $row.find('.vet-billing-service-value')
                        .val('R$ ' + convertFloatToMoeda(data.valor || 0))
                        .trigger('change');

                    $row.find('input[name="servico_categoria[]"]').val(data.categoria || '');

                    self.updateTotals();
                    self.updateSaveButton();
                });
            });

            self.updateSaveButton();
        },

        initializeProductSelects: function ($elements) {
            var self = this;

            $elements.each(function () {
                var $select = $(this);

                if ($select.data('select2')) {
                    $select.off('select2:select');
                    $select.select2('destroy');
                }

                $select.select2({
                    minimumInputLength: 2,
                    language: 'pt-BR',
                    placeholder: $select.data('placeholder') || 'Buscar produto',
                    width: '100%',
                    ajax: {
                        cache: true,
                        url: path_url + 'api/produtos',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                pesquisa: params.term,
                                empresa_id: $('#empresa_id').val(),
                            };
                        },
                        processResults: function (response) {
                            return {
                                results: $.map(response, function (item) {
                                    return {
                                        id: item.id,
                                        text: item.nome + ' • R$ ' + convertFloatToMoeda(item.valor_unitario || 0),
                                        valor: item.valor_unitario || 0,
                                    };
                                }),
                            };
                        },
                    },
                }).on('select2:select', function (event) {
                    var data = event.params.data || {};
                    var $row = $select.closest('tr');

                    $row.find('.vet-billing-product-quantity').val(1);
                    $row.find('.vet-billing-product-unit').val('R$ ' + convertFloatToMoeda(data.valor || 0));
                    $row.find('.vet-billing-product-subtotal').val('R$ ' + convertFloatToMoeda(data.valor || 0));

                    self.updateTotals();
                });
            });
        },

        addServiceRow: function () {
            var $template = $(this.selectors.serviceTemplate).first().clone();

            if (!$template.length) {
                return;
            }

            this.resetRow($template);
            this.$serviceTable.find('tbody').append($template);
            this.initializeDynamicFields($template);
            this.updateTotals();
            this.updateSaveButton();
        },

        addProductRow: function () {
            var $template = $(this.selectors.productTemplate).first().clone();

            if (!$template.length) {
                return;
            }

            this.resetRow($template);
            this.$productTable.find('tbody').append($template);
            this.initializeDynamicFields($template);
            this.updateTotals();
        },

        resetRow: function ($row) {
            $row.find('input').each(function () {
                $(this).val('');
            });

            $row.find('select').each(function () {
                var $select = $(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                $select
                    .val(null)
                    .removeClass('select2-hidden-accessible')
                    .removeAttr('data-select2-id')
                    .removeAttr('aria-hidden')
                    .removeAttr('tabindex');
            });

            $row.find('.select2-container').remove();

            this.updateSaveButton();
        },

        removeRow: function ($row) {
            var $tbody = $row.closest('tbody');
            var rowCount = $tbody.find('tr').length;

            $row.find('select').each(function () {
                var $select = $(this);
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });

            if (rowCount > 1) {
                $row.remove();
            } else {
                this.resetRow($row);
            }

            this.updateTotals();
        },

        hasSelectedService: function () {
            var hasService = false;

            if (!this.$serviceTable || !this.$serviceTable.length) {
                return hasService;
            }

            this.$serviceTable.find('.vet-billing-service-select').each(function () {
                var value = $(this).val();
                if (value !== null && value !== undefined && String(value).trim() !== '') {
                    hasService = true;
                    return false;
                }
            });

            return hasService;
        },

        updateSaveButton: function () {
            if (!this.$submitButton || !this.$submitButton.length) {
                return;
            }

            var hasService = this.hasSelectedService();
            var hasAction = this.$form && this.$form.length && this.$form.attr('action');
            var disabled = this.isSubmitting || !hasService || !hasAction;

            this.$submitButton.prop('disabled', disabled);
            this.$submitButton.toggleClass('disabled', disabled);

            var icon = this.$submitButton.find('.ri');
            if (icon.length) {
                icon.toggleClass('ri-checkbox-circle-line', !this.isSubmitting);
                icon.toggleClass('ri-loader-4-line', this.isSubmitting);
                icon.toggleClass('ri-spin', this.isSubmitting);
            }

            var defaultText = this.defaultButtonText || 'Salvar faturamento';
            var loadingText = this.loadingButtonText || 'Salvando...';
            var textContainer = this.$submitButton.find('.vet-billing-submit-text');

            if (textContainer.length) {
                textContainer.text(this.isSubmitting ? loadingText : defaultText);
            }
        },

        setSubmitting: function (state) {
            this.isSubmitting = Boolean(state);
            this.updateSaveButton();
        },

        submitForm: function () {
            if (!this.$form || !this.$form.length) {
                return;
            }

            var action = this.$form.attr('action');

            if (!action) {
                this.notify({
                    type: 'error',
                    title: 'Configuração ausente',
                    message: 'Endpoint para salvar o faturamento não foi configurado.',
                });
                return;
            }

            if (this.isSubmitting) {
                return;
            }

            if (!this.hasSelectedService()) {
                this.notify({
                    type: 'warning',
                    title: 'Atenção',
                    message: 'Selecione ao menos um serviço para salvar o faturamento.',
                });
                return;
            }

            var method = this.$form.attr('method') || 'POST';
            var formElement = this.$form.get(0);
            var formData = formElement ? new FormData(formElement) : null;

            if (!formData) {
                this.notify({
                    type: 'error',
                    title: 'Erro inesperado',
                    message: 'Não foi possível preparar os dados do faturamento.',
                });
                return;
            }

            this.setSubmitting(true);

            $.ajax({
                url: action,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            })
                .done(this.handleSubmitSuccess.bind(this))
                .fail(this.handleSubmitError.bind(this))
                .always(
                    function () {
                        this.setSubmitting(false);
                        this.updateTotals();
                    }.bind(this)
                );
        },

        handleSubmitSuccess: function (response) {
            var message = response && response.message ? response.message : 'Faturamento salvo com sucesso.';
            var orderData = response && response.ordem_servico ? response.ordem_servico : null;

            if (response && response.billing && response.billing.totals) {
                var totals = response.billing.totals;

                if (totals.services !== undefined) {
                    $('.vet-billing-services-total').text('R$ ' + totals.services);
                }

                if (totals.products !== undefined) {
                    $('.vet-billing-products-total').text('R$ ' + totals.products);
                }

                if (totals.grand_total !== undefined) {
                    $('.vet-billing-grand-total').text('R$ ' + totals.grand_total);
                }
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                var swalConfig = {
                    icon: 'success',
                    title: 'Tudo certo!',
                    text: message,
                };

                if (orderData && orderData.url) {
                    swalConfig.showCancelButton = true;
                    swalConfig.confirmButtonText = 'Abrir OS';
                    swalConfig.cancelButtonText = 'Fechar';
                } else {
                    swalConfig.confirmButtonText = 'Ok';
                }

                window.Swal.fire(swalConfig).then(function (result) {
                    if (result.isConfirmed && orderData && orderData.url) {
                        window.location.href = orderData.url;
                    }
                });

                return;
            }

            window.alert(message);

            if (orderData && orderData.url) {
                window.location.href = orderData.url;
            }
        },

        handleSubmitError: function (jqXHR) {
            var defaultMessage = 'Não foi possível salvar o faturamento do atendimento.';
            var message = defaultMessage;
            var details = [];

            if (jqXHR && jqXHR.responseJSON) {
                if (jqXHR.responseJSON.message) {
                    message = jqXHR.responseJSON.message;
                }

                if (jqXHR.responseJSON.errors) {
                    $.each(jqXHR.responseJSON.errors, function (_, errorMessages) {
                        if (Array.isArray(errorMessages)) {
                            errorMessages.forEach(function (errorMessage) {
                                details.push(errorMessage);
                            });
                        }
                    });
                }
            }

            var fullMessage = message;

            if (details.length) {
                fullMessage += '\n\n' + details.join('\n');
            }

            this.notify({
                type: 'error',
                title: 'Ops!',
                message: fullMessage,
            });
        },

        notify: function (options) {
            var opts = options || {};
            var type = opts.type || 'info';
            var title = opts.title || '';
            var message = opts.message || '';

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: type,
                    title: title || undefined,
                    text: message || undefined,
                    confirmButtonText: 'Ok',
                });
                return;
            }

            var composed = title || '';

            if (message) {
                composed = composed ? composed + '\n' + message : message;
            }

            if (composed) {
                window.alert(composed);
            }
        },

        updateProductSubtotal: function ($row) {
            var quantityRaw = $row.find('.vet-billing-product-quantity').val();
            var quantity = parseFloat((quantityRaw || '0').replace(',', '.'));

            if (isNaN(quantity) || quantity < 0) {
                quantity = 0;
            }

            var unitValue = convertMoedaToFloat($row.find('.vet-billing-product-unit').val());
            var subtotal = quantity * unitValue;

            if (subtotal > 0) {
                $row.find('.vet-billing-product-subtotal').val('R$ ' + convertFloatToMoeda(subtotal));
            } else {
                $row.find('.vet-billing-product-subtotal').val('');
            }

            this.updateTotals();
        },

        updateTotals: function () {
            var servicesTotal = 0;
            var productsTotal = 0;

            $('.vet-billing-service-value').each(function () {
                servicesTotal += convertMoedaToFloat($(this).val());
            });

            $('.vet-billing-product-subtotal').each(function () {
                productsTotal += convertMoedaToFloat($(this).val());
            });

            $('.vet-billing-services-total').text('R$ ' + convertFloatToMoeda(servicesTotal));
            $('.vet-billing-products-total').text('R$ ' + convertFloatToMoeda(productsTotal));
            $('.vet-billing-grand-total').text('R$ ' + convertFloatToMoeda(servicesTotal + productsTotal));

            this.updateSaveButton();
        },
    };

    $(function () {
        VetBilling.init();
    });
})(jQuery);