jQuery(function ($) {
    // @link http://www.foliotek.com/devblog/make-table-rows-sortable-using-jquery-ui-sortable/
    var fixHelper = function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('.group[id$="_order"]').addClass('group-order');

    $('.group[id$="_order"] tbody').sortable({
        // forceHelperSize: true,
        helper: fixHelper,
        stop: function (event, ui) {
            var $container = ui.item.closest('.group-order');
            $('tr', $container).each(function (i) {
                var $tr = $(this);
                var $count = $('.count', $tr);
                $count.text(i + 1);
                var $input = $('input', $tr);
                $input.val(i);
            });
        }
    }).disableSelection();


    $('.group-order').each(function () {
        $('tr', this).each(function (i) {
            var $tr = $(this);
            var $th = $('th', $tr);
            var $input = $('input', $tr);
            var val;
            if ($input.val() !== '') {
                val = parseInt($('input', $tr).val());
            }
            else {
                val = i;
                $input.val(i);
            }

            $('<span class="count"/>').text(val + 1).appendTo($th);
        }); // tr
    }); // group-order
});