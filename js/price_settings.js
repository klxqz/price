(function ($) {
    $.price_plugin = {
        options: {},
        init: function (options) {
            this.options = options;
            this.initButtons();
        },
        initButtons: function () {
            var self = this;
            $('.add-row').click(function () {
                var table = $(this).prev('table#price-table');
                self.options.domain_hash = $(this).data('domain-hash');
                if (table.length) {
                    $('#price-tmpl').tmpl(self.options).appendTo(table.find('tbody'));
                }
                return false;
            });

            $(document).on('click', '.delete-row', function () {
                $(this).closest('tr').remove();
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
            });
        }
    };
})(jQuery);