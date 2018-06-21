var Field = require('../Field')

// using prototypal inheritance: https://stackoverflow.com/questions/15192722/javascript-extending-class
// To do: refactor to ES6 `class Field_Text extends Field` as prototype syntax is more verbose

function Field_Todo(record) {

	Field.call(this, record);

	this._self.options = $.extend(this._self.options, {
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

		return kanban.templates['field-todo'].render({
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
			}
		);

		$field.addClass('func-added');

	}; // addFunctionality

	this.onBlur = function (el, e) {

		var self = this;

	}; // onBlur

	this.onFocus = function (el) {

		var self = this;


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



				el.blur();

				break;
		}
	}; // onKeydown

	this.getValue = function ($field) {
		// console.log('getValue');

		var self = this;

		// return content;
	}; // getValue

	this.formatContentForComment = function (content) {
		var self = this;
		

	}; // formatContentForComment

} // Field_Todo

// inherit Field
Field_Todo.prototype = Object.create(Field.prototype);

Field_Todo.prototype.constructor = Field_Todo;

module.exports = Field_Todo