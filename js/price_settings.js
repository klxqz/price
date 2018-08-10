(function ($) {
    $.price_plugin = {
        options: {
            categories: {}
        },
        init: function (options) {
            this.options = options;
            this.initButtons();
            this.initSort();
            $(window).resize(function () {
                if ($('.price-table').width() + 170 > $('#wa-plugins-content').width()) {
                    $('.scroll-table').width($('#wa-plugins-content').width() - 170);
                } else {
                    $('.scroll-table').width('auto');
                }
            }).resize();
        },
        initButtons: function () {
            var self = this;
            $('#ibutton-status').iButton({
                labelOn: "Вкл", labelOff: "Выкл"
            }).change(function () {
                var self = $(this);
                var enabled = self.is(':checked');
                if (enabled) {
                    self.closest('.field-group').siblings().show(200);
                } else {
                    self.closest('.field-group').siblings().hide(200);
                }
            });
            $('[name*=shop_price]').change(function () {
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });
            $(document).on('click', '.add-row', function () {
                var table = $(this).closest('.field').find('table.price-table');
                var tmp_data = {
                    price: {
                        id: 0,
                        name: '',
                        route_hash: [],
                        category_id: []
                    },
                    route_hashs: self.options.route_hashs,
                    categories: self.options.categories,
                    currencies: self.options.currencies
                };
                if (table.length) {
                    $('#price-tmpl-edit').tmpl(tmp_data).appendTo(table.find('tbody'));
                }
                return false;
            });
            $(document).on('change', '[name="price[route_hash][]"]', function () {
                var val = $(this).val();
                if ($(this).is(':checked')) {
                    if (val == '0') {
                        $(this).closest('ul').find('[name="price[route_hash][]"][value!=' + val + ']').removeAttr('checked');
                    } else {
                        $(this).closest('ul').find('[name="price[route_hash][]"][value=0]').removeAttr('checked');
                    }
                }
            });

            $(document).on('change', '[name="price[category_id][]"]', function () {
                var val = $(this).val();
                if ($(this).is(':checked')) {
                    if (val == '0') {
                        $(this).closest('ul').find('[name="price[category_id][]"][value!=' + val + ']').removeAttr('checked');
                    } else {
                        $(this).closest('ul').find('[name="price[category_id][]"][value=0]').removeAttr('checked');
                    }
                }
            });

            $(document).on('click', '.edit-row', function () {
                var button = $(this);
                var loading = $('<i class="icon16 loading"></i>');
                button.hide().siblings('a').hide();
                button.after(loading);
                $.ajax({
                    url: '?plugin=price&module=settings&action=getPrice',
                    type: 'POST',
                    data: {
                        id: button.closest('tr').data('id')
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            var tmp_data = {
                                price: data.data.price,
                                categories: self.options.categories,
                                route_hashs: self.options.route_hashs,
                                currencies: self.options.currencies
                            };
                            button.closest('tr').replaceWith($('#price-tmpl-edit').tmpl(tmp_data));
                        } else {
                            button.show();
                            loading.remove();
                            alert(data.errors.join(' '));
                        }
                    },
                    error: function (jqXHR) {
                        button.show().siblings('a').show();
                        loading.remove();
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
            $(document).on('click', '.delete-row', function () {
                if ($(this).closest('tr').data('id')) {
                    if (!confirm("Внимание! При удалении выбранной мультицены будут удалены все мультицены данного типа, установленные для товаров. Продолжить?")) {
                        return false;
                    }
                }
                var button = $(this);
                var inputs = button.closest('tr').find('input,select');
                inputs.attr('disabled', true);
                var loading = $('<i class="icon16 loading"></i>');
                inputs.attr('disabled', true);
                button.hide().siblings('a').hide();
                button.after(loading);
                if (button.closest('tr').data('id')) {
                    var self = this;
                    $.ajax({
                        url: '?plugin=price&module=settings&action=deletePrice',
                        type: 'POST',
                        data: {
                            id: button.closest('tr').data('id')
                        },
                        success: function (data, textStatus) {
                            button.closest('tr').remove();
                        }, error: function (jqXHR) {
                            inputs.removeAttr('disabled');
                            button.show().siblings('a').show();
                            loading.remove();
                            alert(jqXHR.responseText);
                        }
                    });
                } else {
                    button.closest('tr').remove();
                }
                return false;
            });
            $(document).on('click', '.save-row', function () {
                var button = $(this);
                var inputs = button.closest('tr').find('input,select');
                var data = inputs.serialize();
                var loading = $('<i class="icon16 loading"></i>');
                inputs.attr('disabled', true);
                button.hide().siblings('a').hide();
                button.after(loading);
                $.ajax({
                    url: '?plugin=price&module=settings&action=savePrice',
                    type: 'POST',
                    data: data,
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            var tmp_data = {
                                price: data.data.price,
                                categories: self.options.categories,
                                route_hashs: self.options.route_hashs
                            };
                            button.closest('tr').replaceWith($('#price-tmpl').tmpl(tmp_data));
                        } else {
                            inputs.removeAttr('disabled');
                            button.show().siblings('a').show();
                            loading.remove();
                            alert(data.errors.join(' '));
                        }
                    },
                    error: function (jqXHR) {
                        inputs.removeAttr('disabled');
                        button.show().siblings('a').show();
                        loading.remove();
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });

        },
        initSort: function () {
            var self = this;
            $('.price-table').sortable({
                distance: 5,
                opacity: 0.75,
                items: 'tbody tr',
                axis: 'y',
                containment: 'parent',
                update: function (event, ui) {
                    var breaksort = false;
                    var id = parseInt($(ui.item).data('id'));
                    if (!id) {
                        breaksort = true;
                    }
                    var after_id = $(ui.item).prev().data('id');
                    if (after_id === undefined) {
                        after_id = 0;
                    } else {
                        after_id = parseInt(after_id);
                        if (!after_id) {
                            breaksort = true;
                        }
                    }
                    if (!breaksort) {
                        self.sort(id, after_id, $(this));
                    }

                }
            });
        },
        sort: function (id, after_id, $list) {
            $.post('?plugin=price&module=settings&action=sort', {
                id: id,
                after_id: after_id
            }, function (response) {
                if (response.error) {
                    $list.sortable('cancel');
                }
            }, function (response) {
                $list.sortable('cancel');
            });
        }
    };
})(jQuery);