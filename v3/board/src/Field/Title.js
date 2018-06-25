var Field = require('../Field')

// using prototypal inheritance: https://stackoverflow.com/questions/15192722/javascript-extending-class
// To do: refactor to ES6 `class Field_Title extends Field` as prototype syntax is more verbose

function Field_Title(record) {

	Field.call(this, record);

	this._self.options = $.extend(this._self.options, {
	});

	this.render = function (fieldvalue, card) {
		var self = this;

		if ( 'undefined' === typeof card ) {
			return false;
		}

		var fieldvalueRecord = 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record();

		if ( 'undefined' !== typeof fieldvalueRecord.content ) {
			fieldvalueRecord.content = fieldvalueRecord.content.formatForApp();
		}

		return kanban.templates['field-title'].render({
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

		$field.one(
			'mouseover',
			function () {
				kanban.app.prepareContenteditable($field);

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
				el.blur();

				var prevValue = $(el).data('prevValue');
				var $contenteditable = $(el);
				$contenteditable.html( prevValue );

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

	this.applyFilter = function(fieldValue, filterElement) {
		var fieldContent = fieldValue.field().formatContentForComment(fieldValue.content());
		switch (filterElement.operator) {
			case "includes":
				return fieldContent.toLowerCase().indexOf(filterElement.value) !== -1
			case "does not include":
				return fieldContent.toLowerCase().indexOf(filterElement.value) === -1
		}
	}

} // Field_Title

// inherit Field
Field_Title.prototype = Object.create(Field.prototype);





// correct the constructor pointer because it points to Field
Field_Title.prototype.constructor = Field_Title;

module.exports = Field_Title