/*
 * jQuery Textarea.Autoresize
 * https://github.com/AndrewDryga/jQuery.Textarea.Autoresize
 *
 * This plugin resizes textarea height to match it's content height.
 *
 * Usage:
 * <code>$('textarea').autoresize(params_object);</code>
 *
 * Params can also be passed via data-api:
 * <code><textarea data-default-height="min" data-animation="false"></textarea></code>
 *
 * Author: Andrew Dryga <andrew@dryga.com> <http://dryga.com>
 * License: MIT
 */

(function($) {
  "use strict";

  var mirrorred_styles = [
    'padding',
    'paddingTop',
    'paddingBottom',
    'paddingRight',
    'paddingLeft',
    'border',
    'borderTop',
    'borderBottom',
    'borderRight',
    'borderLeft',
    'borderTopWidth',
    'borderRightWidth',
    'borderBottomWidth',
    'borderLeftWidth',
    'fontFamily',
    'fontSize',
    'lineHeight',
    'box-sizing',
  ];

  var Obj = function(element, params) {
    this.$element = $(element);
    this.$element.data('autoresize-api', this);
    params = params || {};

    this.params = $.extend({
      minHeight: this.$element.height(),
      maxHeight: ~~parseInt(this.$element.css('max-height'), 10),
      defaultHeight: false,
      animation: true,
      heightCompensation: this.$element.outerHeight() - this.$element.height(),
      animationDuration: 'slow',
      animationEasing: 'swing',
      onResize: $.noop
    }, this.$element.data(), params);

    if(this.params.defaultHeight && this.params.defaultHeight == 'min') {
      this.params.defaultHeight = this.params.minHeight;
    }

    this.init();
  };

  Obj.prototype = {
    init: function() {
      var $self = this;
      var $element = $self.$element;
      var element = $element.get(0);

      if($element.prop("tagName").toLowerCase() !== 'textarea') {
        console.error('jQuery.Textarea.Autoresize works only on textarea tags, skipping...');
        console.log('Selected element is: ', $element);
        return;
      }

      $element.addClass('autoresize');
      if($self.params.maxHeight == false) {
        $element.css('overflow', 'hidden');
      }

      if($self.params.defaultHeight) {
        $element.on('focus.autoresize', function() {
          $self.setHeight($self.getContentHeight(), $self.params.animation);
        });

        $element.on('focusout.autoresize', function() {
          $self.setHeight($self.params.defaultHeight, $self.params.animation);
        });
      }

      $element.on('keydown.autoresize', function() { //cut paste drop
        setTimeout(function() {
          $self.setHeight($self.getContentHeight());
        }, 0);
      });

      $element.on('resize.autoresize', function() {
        setTimeout(function() {
          $self.getMirror().width($element.width());
          $self.setHeight($self.getContentHeight());
        }, 0);
      });
    },

    destroy: function() {
      this.getMirror().remove();
      this.$element.removeData('autoresize-api');
      this.$element.off('.autoresize');
      this.$element.removeClass('autoresize');
      this.$element.css('overflow', '');
      this.$element.css('height', '');
      this.$element.removeAttr('style');
    },

    getMirror: function() {
      var mirror_tag = this.$element.nextAll('.autoresize-mirror').first();
      if(!mirror_tag.length) {
        mirror_tag = $('<div/>').addClass('autoresize-mirror');
        for(var i = 0; i < mirrorred_styles.length; i++) {
          mirror_tag.css(mirrorred_styles[i], this.$element.css(mirrorred_styles[i]));
        }
        mirror_tag.width(this.$element.width());

        this.$element.after(mirror_tag);
      }

      mirror_tag.html(this.$element.val().replace(/&/g, '&amp;').
                                     replace(/"/g, '&quot;').
                                     replace(/'/g, '&#39;').
                                     replace(/</g, '&lt;').
                                     replace(/>/g, '&gt;').
                                     replace(/\n/g, '<br />') + '<br />');

      return mirror_tag;
    },

    getContentHeight: function() {
      return this.limitValue(this.getMirror().height(), this.params.minHeight, this.params.maxHeight);
    },

    setHeight: function(height, animation) {
    	var $this = this;
      if(animation) {
        this.$element.stop(true).animate({height: height + this.params.heightCompensation + 'px'}, this.params.animationDuration, this.params.animationEasing, function() {
        	$this.params.onResize($this.$element, {height: height});
        });
      } else {
        this.$element.height(height);
	      this.params.onResize(this.$element, {height: height});
      }
    },

    limitValue: function(value, min, max) {
      value = (min == false || value > min) ? value : min;
      value = (max == false || value < max) ? value : max;

      return value;
    }
  };

  $.fn.autoresize = function(params) {
    return this.each(function() {
      if(params == 'destroy')  {
        var api;
        if(api = $(this).data('autoresize-api')) {
          api.destroy();
        } else {
          console.error("Can't destroy autoresize api, it's not initialized.");
        }
      } else {
        new Obj(this, params);
      }
    });
  };

  $(function() {
    // Instert plugin styles
    $('html > head').append($('<style>.autoresize-mirror { display: none; word-wrap: break-word; white-space: pre-wrap; } .autoresize { resize: none; }</style>'));
  });
})(jQuery);
