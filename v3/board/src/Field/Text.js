var Field = require('../Field')

// using prototypal inheritance: https://stackoverflow.com/questions/15192722/javascript-extending-class
// To do: refactor to ES6 `class Field_Text extends Field` as prototype syntax is more verbose

function Field_Text(record) {

	Field.call(this, record);

	this._self.options = $.extend(
		this._self.options,
		{
			allow_files: false
		});

	this.render = function (fieldvalue, card) {
		var self = this;

		if ( 'undefined' === typeof fieldvalue ) {
			fieldvalue = {};
		}

		if ( 'undefined' === typeof card ) {
			return false;
		}

		var fieldvalueRecord = 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record();

		if ( 'undefined' !== typeof fieldvalueRecord.content ) {
			fieldvalueRecord.content = fieldvalueRecord.content.formatForApp();
		}

		return kanban.templates['field-text'].render({
			field: self.record(),
			fieldvalue: fieldvalueRecord,
			card: 'undefined' === typeof card.record ? {} : card.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	this.addFunctionality = function ($field) {
		var self = this;

		if ( $field.hasClass('func-added') ) {
			return false;
		}

		// Not on hover, so we get the placeholder.
		kanban.app.prepareContenteditable($field);

		$field.one(
			'mouseover',
			function () {

				$('.attachment', $field)
				.on(
					'click',
					function () {
						var href = $(this).attr('data-href');

						window.open(href);

						return false;
					}
				);
			}
		);

		$field.addClass('func-added');

	}; // addFunctionality

	this.onBlur = function (el, e) {
		// console.log('Field_Title.onBlur');

		var self = this;

		// Wait 500ms to see if blur was caused by at.js
		setTimeout(function () {
			var $el = $(el);

			if ( !$el.is(':focus') ) {
				var $field = $el.closest('.field').removeClass('is-editing');
				var $card = $el.closest('.card').removeClass('is-editing');
				var $lane = $field.closest('.lane').removeClass('is-editing');

				var content = self.getValue($field);
				self.updateValue($field, content);


				$el.html(content.formatForApp());
			}
		}, 300);
	}; // onBlur

	this.onFocus = function (el) {
		// console.log('Field_Title.prototype.onFocus');

		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field').addClass('is-editing');
		var $card = $el.closest('.card').addClass('is-editing');
		var $lane = $field.closest('.lane').addClass('is-editing');

		// Save the current value for restoring.
		var value = self.getValue($field);
		$el.data('prevValue', value);

		// Unlink links.
		$('a', $el).each(function () {
			var $this = $(this);
			$this.replaceWith($this.text());
		});

	}; // onFocus

	this.onKeydown = function (el, e) {
		// console.log('Field_Title.onKeydown');
		var self = this;

		switch (e.keyCode) {
			case 13: // enter

				// If not shift + enter, save it.
				if ( !e.shiftKey ) {
					el.blur();
					return false;
				}

				// If just enter, save it.
				break;

			case 27: // escape

				// var $el = $(el);
				// var $field = $el.closest('.field').removeClass('is-editing');
				// var fieldvalueId = $field.attr('data-fieldvalue-id');
				// var cardId = $field.attr('data-card-id');
				//
				// if ( 'undefined' === typeof kanban.cards[cardId] ) {
				// 	return false;
				// }
				//
				// var card = kanban.cards[cardId];
				//
				// var fieldvalue = {};
				// if ( 'undefined' !== typeof kanban.fieldvalues[fieldvalueId] ) {
				// 	fieldvalue = kanban.fieldvalues[fieldvalueId];
				// }
				//
				// self.rerender(fieldvalue, card);

				var prevValue = $(el).data('prevValue');
				var $contenteditable = $(el);
				$contenteditable.html( prevValue );

				el.blur();

				break;
		}
	}; // onKeydown

	this.getValue = function ($field) {
		// console.log('getValue');

		var self = this;

		var $contenteditable = $('[contenteditable]', $field);
		var content = $contenteditable.html();
		content = content.formatForDb();

		return content;
	}; // getValue

	this.formatContentForComment = function (content) {
		var self = this;
		
		return $("<div>").html(content.formatForApp()).text();
	}; // formatContentForComment

} // Field_Text

// inherit Field
Field_Text.prototype = Object.create(Field.prototype);


// Field_Text.prototype.onBlur = function (el) {
// 	console.log('Field_Text.prototype.onBlur');
//
// 	Field.prototype.updateValue.call(this, el);
// }
//
// Field_Text.prototype.getValue = function (el) {
// 	console.log('Field_Text.prototype.getValue');
//
// 	var self = this;
//
// 	// Call parent
// 	// Field.prototype.onBlur.call(this, el);
//
// 	var $contenteditable = $(el);
// 	var content = $.trim($contenteditable.text());
// 	content = content.replace(/(\r\n|\n|\r)/g,"<br>");
//
// 	return content;
// }


// correct the constructor pointer because it points to Field
Field_Text.prototype.constructor = Field_Text;

module.exports = Field_Text