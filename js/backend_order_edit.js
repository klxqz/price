$(function () {
    $('#price-buttons').appendTo('#order-edit-form .content > .block').show();
    $('#price-buttons a').click(function () {
        $('#price-buttons a').removeClass('green').addClass('grey');
        $(this).addClass('green');
        var loading = $('<i class="icon16 loading"></i>');
        $(this).append(loading);
        $('[name=price_id]').val($(this).data('price-id'));
        var table = $('#order-items');
        var price_edit = true;
        $.ajax({
            url: '?plugin=price&action=getProducts',
            type: 'POST',
            data: $('#order-edit-form').serialize() + '&order_id=' + $.order_edit.id,
            success: function (data, textStatus) {
                loading.remove();
                if (data.status == 'ok') {
                    table.find('.s-order-item').each(function () {
                        var product_id = $(this).data('product-id');
                        if (data.data[product_id] === undefined) {
                            return false;
                        }
                        var r = data.data[product_id];
                        var index = $(this).data('index');
                        var price_name = $(this).find('[name*="quantity"]').attr('name');
                        var match = price_name.match(/quantity\[([^\]]+)\]\[([^\]]+)\]/i);
                        console.log(match);
                        var mode = match[1];
                        var item_id = match[2];
                        var sku_id = $(this).find('[name="sku[' + mode + '][' + item_id + ']"]:checked').length ? $(this).find('[name="sku[' + mode + '][' + item_id + ']"]:checked').val() : null;
                        var stock = $(this).find('.s-orders-sku-stock-select').length ? $(this).find('.s-orders-sku-stock-select').val() : null;
                        var quantity = $(this).find('[name*="quantity[' + mode + '][' + item_id + ']"]').val();
                        var defaults = {
                            'mode': mode,
                            'item_id': item_id,
                            'sku_id': sku_id,
                            'quantity': quantity,
                            'stock': stock
                        };
                        var tmp = mode == 'edit' ? 'template-order-edit-priceplugin' : 'template-order-add-priceplugin';
                        console.log(defaults);
                        $(this).replaceWith(
                            tmpl(tmp, {
                                data: r,
                                options: {
                                    index: index,
                                    currency: $.order_edit.options.currency,
                                    stocks: $.order_edit.stocks,
                                    price_edit: price_edit,
                                    defaults: defaults
                                }
                            }));
                        updateStockIcon($(this));
                    });
                    $.order_edit.updateTotal();
                }
            }
        });
        return false;
    });
    var updateStockIcon = function (order_item) {
        var select = order_item.find('.s-orders-sku-stock-select');
        var option = select.find('option:selected');
        var sku_item = order_item.find('.s-orders-skus').find('input[type=radio]:checked').parents('li:first');

        order_item.find('.s-orders-stock-icon-aggregate').show();
        order_item.find('.s-orders-stock-icon').html('').hide();

        // choose item to work with
        var item = sku_item.length ?
            sku_item : // sku case
            order_item;  // product case (one sku)

        if (option.attr('data-icon')) {
            item.find('.s-orders-stock-icon-aggregate').hide();
            item.find('.s-orders-stock-icon').html(
                option.attr('data-icon')
            ).show();
            order_item.find('.s-orders-stock-icon .s-stock-left-text').show();
            item.find('.s-orders-stock-icon .s-stock-left-text').hide();
        }
    };
    setTimeout(function () {
        var price_edit = true;
        var add_order_input = $("#orders-add-autocomplete");
        add_order_input.autocomplete("destroy");
        add_order_input.autocomplete({
            source: '?action=autocomplete&with_counts=1',
            minLength: 3,
            delay: 300,
            select: function (event, ui) {

                $('.s-order-errors').empty();
                var storefront = $('#order-storefront').val();
                if (storefront) {
                    if (storefront.indexOf('/', storefront.length - 1) == -1) {
                        storefront += '/';
                    }
                    storefront += '*';
                }

                var url = '?plugin=price&action=getProduct&product_id=' + ui.item.id + '&customer_id=' + $('#s-customer-id').val() + '&storefront=' + storefront;
                if ($('[name=price_id]').val()) {
                    url += '&price_id=' + $('[name=price_id]').val();
                }
                $.getJSON(url + ($.order_edit.id ? '&order_id=' + $.order_edit.id : '&currency=' + $.order_edit.options.currency), function (r) {
                    var table = $('#order-items');
                    var index = parseInt(table.find('.s-order-item:last').attr('data-index'), 10) + 1 || 0;
                    var product = r.data.product;
                    if (product.sku_id && product.skus[product.sku_id]) {
                        product.skus[product.sku_id].checked = true;
                    }

                    if ($('#order-currency').length && !$('#order-currency').attr('disabled')) {
                        $('<input type="hidden" name="currency">').val($('#order-currency').val()).insertAfter($('#order-currency'));
                        $('#order-currency').attr('disabled', 'disabled');
                    }

                    var add_row = $('#s-orders-add-row');
                    add_row.before(tmpl('template-order', {
                        data: r.data,
                        options: {
                            index: index,
                            currency: $.order_edit.options.currency,
                            stocks: $.order_edit.stocks,
                            price_edit: price_edit
                        }
                    }));
                    var item = add_row.prev();
                    //item.find('.s-orders-services .s-orders-service-variant').trigger('change');

                    $('#s-order-comment-edit').show();
                    $.order_edit.updateTotal();
                    updateStockIcon(item);
                });
                add_order_input.val('');
                return false;
            }
        });
        $('#s-content').off('change', '.s-orders-skus input[type=radio]').on('change', '.s-orders-skus input[type=radio]',
            function () {
                var self = $(this);
                var tr = self.parents('tr:first');
                var li = self.closest('li');
                var sku_id = this.value;
                var product_id = tr.attr('data-product-id');
                var index = tr.attr('data-index');
                var mode = $.order_edit.id ? 'edit' : 'add';
                var item_id = null;
                if (mode == 'edit') {
                    item_id = parseInt(self.attr('name').replace('sku[edit][', ''), 10);
                }

                var storefront = $('#order-storefront').val();
                if (storefront) {
                    if (storefront.indexOf('/', storefront.length - 1) == -1) {
                        storefront += '/';
                    }
                    storefront += '*';
                }

                var url = '?plugin=price&action=getProduct&product_id=' + product_id + '&sku_id=' + sku_id + '&customer_id=' + $('#s-customer-id').val() + '&storefront=' + storefront;
                if ($('[name=price_id]').val()) {
                    url += '&price_id=' + $('[name=price_id]').val();
                }
                $.getJSON(url + ($.order_edit.id ? '&order_id=' + $.order_edit.id : '&currency=' + $.order_edit.options.currency), function (r) {
                    var ns;
                    if (tr.find('input:first').attr('name').indexOf('add') !== -1) {
                        ns = 'add';
                    } else {
                        ns = 'edit';
                    }

                    tr.find('.s-orders-services').replaceWith(
                        tmpl('template-order-services-' + ns, {
                            services: r.data.sku.services,
                            service_ids: r.data.service_ids,
                            product_id: product_id,
                            options: {
                                price_edit: price_edit,
                                index: index,
                                currency: $.order_edit.options.currency,
                                stocks: $.order_edit.stocks
                            }
                        })
                    );
                    tr.find('.s-orders-product-price').find('input').val(r.data.sku.price);

                    tr.find('.s-orders-sku-stock-place').empty();
                    li.find('.s-orders-sku-stock-place').html(
                        tmpl('template-order-stocks-' + ns, {
                            sku: r.data.sku,
                            index: index,
                            stocks: $.order_edit.stocks,
                            item_id: item_id   // use only for edit namespace
                        })
                    );

                    updateStockIcon(tr);
                    $.order_edit.updateTotal();
                });
            }
        );
    }, 0);
});
