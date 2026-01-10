<script type="text/javascript">
    (function ($) {
        function sanitizeQuantity(value) {
            var numeric = parseFloat(value);

            if (Number.isNaN(numeric)) {
                return '';
            }

            if (Number.isInteger(numeric)) {
                return String(numeric);
            }

            return numeric.toFixed(3).replace(/\.0+$/, '').replace(/0+$/, '').replace(/\.$/, '');
        }

        function aggregateStock(product) {
            var stocks = [];

            if (Array.isArray(product?.estoque_locais) && product.estoque_locais.length) {
                stocks = product.estoque_locais;
            } else if (product?.estoque) {
                stocks = [product.estoque];
            }

            return stocks.reduce(function (totals, stock) {
                var current = parseFloat(stock?.quantidade ?? 0) || 0;
                totals.current += current;
                return totals;
            }, { current: 0 });
        }

        function determineStockStatus(current, minimum) {
            if (current <= 0) {
                return 'Sem estoque';
            }

            if (minimum > 0 && current <= minimum) {
                return 'Estoque baixo';
            }

            return 'Disponível';
        }

        function updateInventoryFields(fields, defaults, product) {
            if (!product) {
                resetInventoryFields(fields, defaults);
                return;
            }

            var aggregated = aggregateStock(product);
            var minimumStock = parseFloat(product?.estoque_minimo ?? 0) || 0;
            var status = determineStockStatus(aggregated.current, minimumStock);

            fields.current.val(sanitizeQuantity(aggregated.current));
            fields.minimum.val(sanitizeQuantity(minimumStock));
            fields.status.val(status).trigger('change');
        }

        function resetInventoryFields(fields, defaults) {
            fields.current.val(defaults.current);
            fields.minimum.val(defaults.minimum);
            fields.status.val(defaults.status).trigger('change');
        }

        function fetchProduct(productId, fields, defaults) {
            if (!productId) {
                resetInventoryFields(fields, defaults);
                return;
            }

            $.get(path_url + 'api/produtos/findId/' + productId)
                .done(function (response) {
                    updateInventoryFields(fields, defaults, response || null);
                })
                .fail(function () {
                    resetInventoryFields(fields, defaults);
                });
        }

        function ensureProductSelect() {
            if (typeof initProductSelect === 'function') {
                return initProductSelect();
            }

            var $select = $('#inp-produto_id');

            if ($select.length && $.fn.select2 && !$select.data('select2')) {
                var placeholder = $select.data('placeholder') || 'Digite para buscar o produto';

                $select.select2({
                    minimumInputLength: 2,
                    language: 'pt-BR',
                    placeholder: placeholder,
                    width: '100%',
                    ajax: {
                        cache: true,
                        url: path_url + 'api/produtos',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                pesquisa: params.term,
                                empresa_id: $('#empresa_id').val(),
                                usuario_id: $('#usuario_id').val()
                            };
                        },
                        processResults: function (response) {
                            var results = [];

                            $.each(response || [], function (_, product) {
                                if (typeof parseProduto === 'function') {
                                    results.push(parseProduto(product, 0));
                                } else {
                                    results.push({
                                        id: product.id,
                                        text: product.nome
                                    });
                                }
                            });

                            return { results: results };
                        }
                    }
                });
            }

            return $select;
        }

        function initCustomizableSelects() {
            $('[data-allow-custom]').each(function () {
                var $select = $(this);
                var customValue = String($select.data('custom-option') ?? '');
                var fieldName = $select.data('custom-field');
                var $wrapper = $select.closest('[data-custom-wrapper]');

                if ($wrapper.length && fieldName && $wrapper.data('custom-wrapper') !== fieldName) {
                    $wrapper = $('[data-custom-wrapper="' + fieldName + '"]');
                }

                var $input = ($wrapper.length ? $wrapper : $(document)).find('[data-custom-input="' + fieldName + '"]');

                if (!customValue || !$input.length) {
                    return;
                }

                var $inputWrapper = $wrapper.find('[data-custom-input-wrapper]');
                var $selectWrapper = $wrapper.find('[data-custom-select-wrapper]');
                var $resetTrigger = $wrapper.find('[data-custom-reset="' + fieldName + '"]');
                var originalName = $select.data('original-name') || $select.attr('name');

                if (!originalName) {
                    return;
                }

                $select.data('original-name', originalName);

                var wasRequired = $select.prop('required');
                var select2SyncAttempts = 0;

                function toggleSelect2Container(forceHidden) {
                    var instance = $select.data('select2');

                    if (!instance || !instance.$container) {
                        return false;
                    }

                    if (forceHidden) {
                        instance.$container.addClass('d-none');
                    } else {
                        instance.$container.removeClass('d-none');
                    }

                    return true;
                }

                function hideSelectControl() {
                    if (!toggleSelect2Container(true) && !$selectWrapper.length) {
                        $select.addClass('d-none');
                    }

                    if ($selectWrapper.length) {
                        $selectWrapper.addClass('d-none');
                    }
                }

                function showSelectControl() {
                    if (!toggleSelect2Container(false) && !$selectWrapper.length) {
                        $select.removeClass('d-none');
                    }

                    if ($selectWrapper.length) {
                        $selectWrapper.removeClass('d-none');
                    }
                }

                function showInputControl() {
                    if ($inputWrapper.length) {
                        $inputWrapper.removeClass('d-none');
                    } else {
                        $input.removeClass('d-none');
                    }
                }

                function hideInputControl() {
                    if ($inputWrapper.length) {
                        $inputWrapper.addClass('d-none');
                    } else {
                        $input.addClass('d-none');
                    }
                }

                function focusCustomInput() {
                    window.setTimeout(function () {
                        $input.trigger('focus');
                    }, 50);
                }

                function focusSelectControl() {
                    window.setTimeout(function () {
                        var instance = $select.data('select2');

                        if (instance && instance.$container) {
                            instance.$container.trigger('focus');
                            return;
                        }

                        $select.trigger('focus');
                    }, 50);
                }

                function enableCustom(shouldFocus) {
                    $select.data('custom-active', true);
                    hideSelectControl();
                    $select.removeAttr('name');
                    if (wasRequired) {
                        $select.prop('required', false);
                    }

                    $input.attr('name', originalName);
                    $input.prop('disabled', false);
                    $input.prop('required', true);
                    showInputControl();
                    if (shouldFocus) {
                        focusCustomInput();
                    }
                }

                function disableCustom(clearInput, shouldFocus) {
                    $select.data('custom-active', false);
                    showSelectControl();
                    if (!$select.attr('name')) {
                        $select.attr('name', originalName);
                    }

                    $select.prop('required', wasRequired);
                    $input.removeAttr('name');
                    $input.prop('disabled', true);
                    $input.prop('required', false);
                    if (clearInput) {
                        $input.val('');
                    }
                    hideInputControl();
                    if (shouldFocus) {
                        focusSelectControl();
                    }
                }

                function applyState(options) {
                    var focusCustom = Boolean(options && options.focusCustom);
                    var focusSelect = Boolean(options && options.focusSelect);

                    if (String($select.val()) === customValue) {
                        enableCustom(focusCustom);
                    } else {
                        disableCustom(false, focusSelect);
                    }
                }

                function ensureSelect2Sync() {
                    if (select2SyncAttempts > 20) {
                        return;
                    }

                    select2SyncAttempts += 1;

                    if (toggleSelect2Container($select.data('custom-active'))) {
                        if ($selectWrapper.length && !$selectWrapper.hasClass('d-none') && $select.data('custom-active')) {
                            $selectWrapper.addClass('d-none');
                        }
                        return;
                    }

                    window.setTimeout(ensureSelect2Sync, 150);
                }

                $select.off('change.customOption').on('change.customOption', function () {
                    applyState({ focusCustom: true });
                });

                if ($resetTrigger.length) {
                    $resetTrigger.off('click.customOption').on('click.customOption', function () {
                        disableCustom(true, true);
                        $select.val('').trigger('change');
                    });
                }

                applyState();
                ensureSelect2Sync();
            });
        }

        $(function () {
            initCustomizableSelects();

            var $productSelect = ensureProductSelect();

            if (!$productSelect || !$productSelect.length) {
                return;
            }

            var fields = {
                current: $('#inp-medicine_current_stock'),
                minimum: $('#inp-medicine_minimum_stock'),
                status: $('#inp-medicine_stock_status')
            };

            var defaults = {
                current: fields.current.val(),
                minimum: fields.minimum.val(),
                status: fields.status.val()
            };

            $productSelect.off('change.medicineInventory').on('change.medicineInventory', function () {
                fetchProduct($(this).val(), fields, defaults);
            });

            var initialValue = $productSelect.val();
            if (initialValue) {
                fetchProduct(initialValue, fields, defaults);
            } else {
                resetInventoryFields(fields, defaults);
            }
        });
    })(jQuery);
</script>