(function ($) {
    $.fn.same_height = function () {
        var child_h = 0, h = 0, pb = 0, pt = 0;
        this.each(function () {
            var $el = $(this);
            pb = $el.css('paddingBottom');
            pt = $el.css('paddingTop');

            try {
                pb = parseInt(pb);
                pt = parseInt(pt);
            }
            catch (err) {
            }

            child_h = 0;

            $('> *', $el).each(function () {
                var $child = $(this);
                child_h += $child.outerHeight();
            });

            child_h = child_h + pb + pt;

            if (child_h > h) {
                h = child_h;
            }
        });

        var window_h = $(window).height();
        if (window_h > h) {
            h = window_h;
        }

        $(this).innerHeight(h - 20);

        return this;
    };
})(jQuery);
