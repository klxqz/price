(function ($) {
    $.price_plugin = {
        options: {
            categories: {}
        },
        init: function (options) {
            this.options = options;
            this.initButtons();
            this.initRouteSelector();
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
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });
            $(document).on('click', '.add-row', function () {
                var table = $(this).closest('.field').find('table.price-table');
                var data = {
                    categories: self.options.categories,
                    route_hash: $('#route-selector').val(),
                    id: 0,
                    category_id: 0
                };
                if (table.length) {
                    $('#price-tmpl').tmpl(data).appendTo(table.find('tbody'));
                }
                return false;
            });
            $(document).on('click', '.delete-row', function () {
                if ($(this).closest('tr').data('id')) {
                    if (!confirm("Внимание! При удалении выбранной мультицены будут удалены все мультицены данного типа, установленные для товаров. Продолжить?")) {
                        return false;
                    }
                }
                var inputs = $(this).closest('tr').find('input,select');
                inputs.attr('disabled', true);
                $(this).hide().siblings('a').hide();
                $(this).after('<i class="icon16 loading"></i>');
                if ($(this).closest('tr').data('id')) {
                    var self = this;
                    $.ajax({
                        url: '?plugin=price&module=settings&action=deletePrice',
                        type: 'POST',
                        data: {
                            id: $(this).closest('tr').data('id')
                        },
                        success: function (data, textStatus) {
                            $(self).closest('tr').remove();
                        }
                    });
                } else {
                    $(this).closest('tr').remove();
                }
                return false;
            });
            $(document).on('click', '.save-row', function () {
                var button = this;
                var inputs = $(this).closest('tr').find('input,select');
                var data = inputs.serialize();
                inputs.attr('disabled', true);
                $(this).hide().siblings('a').hide();
                $(this).after('<i class="icon16 loading"></i>');
                $.ajax({
                    url: '?plugin=price&module=settings&action=savePrice',
                    type: 'POST',
                    data: data,
                    success: function (data, textStatus) {
                        data.data.price.categories = self.options.categories;
                        $(button).closest('tr').replaceWith($('#price-tmpl').tmpl(data.data.price));
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
        },
        initRouteButtons: function () {
            $('.route-container .ibutton').iButton({
                labelOn: "Вкл",
                labelOff: "Выкл",
                className: 'mini'
            }).change(function () {
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });
            this.initSort();
        },
        initRouteSelector: function () {
            var self = this;
            $('#route-selector').change(function () {
                var $route_selector = $(this);
                var loading = $('<i class="icon16 loading"></i>');
                $(this).attr('disabled', true);
                $(this).after(loading);
                $('.route-container').find('input,select,textarea').attr('disabled', true);
                $('.route-container').slideUp('slow');
                $.get('?plugin=price&module=settings&action=route&route_hash=' + $(this).val(), function (response) {
                    $('.route-container').html(response);
                    loading.remove();
                    $route_selector.removeAttr('disabled');
                    $('.route-container').slideDown('slow');
                    self.initRouteButtons();
                });
                return false;
            }).change();
        }
    };
})(jQuery);