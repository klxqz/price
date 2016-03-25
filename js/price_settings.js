(function ($) {
    $.price_plugin = {
        options: {
            categories: {}
        },
        init: function (options) {
            this.options = options;
            this.initButtons();
            this.initSort();
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
                    var id = parseInt($(ui.item).data('id'));
                    var after_id = $(ui.item).prev().data('id');
                    if (after_id === undefined) {
                        after_id = 0;
                    } else {
                        after_id = parseInt(after_id);
                    }
                    self.sort(id, after_id, $(this));
                }
            });
        },
        sort: function (id, after_id, $list) {
            $.post('?plugin=price&action=sort', {
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
        initButtons: function () {
            var self = this;
            $('.add-row').click(function () {
                var table = $(this).closest('.field').find('table.price-table');
                var data = {
                    categories: self.options.categories,
                    domain_hash: $(this).data('domain-hash'),
                    id: 0,
                    category_id: 0
                };
                if (table.length) {
                    $('#price-tmpl').tmpl(data).appendTo(table.find('tbody'));
                }
                return false;
            });

            $(document).on('click', '.delete-row', function () {
                var inputs = $(this).closest('tr').find('input,select');
                inputs.attr('disabled', true);
                $(this).hide().siblings('a').hide();
                $(this).after('<i class="icon16 loading"></i>');
                if ($(this).closest('tr').data('id')) {
                    var self = this;
                    $.ajax({
                        url: '?plugin=price&action=deletePrice',
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
                    url: '?plugin=price&action=savePrice',
                    type: 'POST',
                    data: data,
                    success: function (data, textStatus) {
                        data.data.price.categories = self.options.categories;
                        $(button).closest('tr').replaceWith($('#price-tmpl').tmpl(data.data.price));
                    }
                });
                return false;
            });
            $('#ibutton-status').iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
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
            $('.ibutton-status-parent').iButton({labelOn: "", labelOff: "", className: 'mini'}).change(function () {
                var self = $(this);
                var enabled = self.is(':checked');
                if (enabled) {
                    self.closest('.field').siblings().show(200);
                } else {
                    self.closest('.field').siblings().hide(200);
                }
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });
        }
    };
})(jQuery);