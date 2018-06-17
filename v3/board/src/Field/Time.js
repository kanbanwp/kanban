var Field = require('../Field')
var functions = require('../functions')

// using prototypal inheritance: https://stackoverflow.com/questions/15192722/javascript-extending-class
// To do: refactor to ES6 `class Field_Text extends Field` as prototype syntax is more verbose

function Field_Time(record) {

	Field.call(this, record);

	this._self.options = $.extend(
		this._self.options,
		{
			step: 1,
			show_estimate: false
		}
	);

	this.timerSave;

	this.stringToFloat = function (s) {
		if ( isNaN(s) ) {
			s = 0;
		}

		return parseFloat(s);
	}; // stringToFloat

	this.render = function (fieldvalue, card) {
		var self = this;

		if ( 'undefined' === typeof fieldvalue ) {
			fieldvalue = {};
		}

		if ( 'undefined' === typeof card ) {
			return false;
		}

		var fieldvalueRecord = 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record();

		var fieldOptions = self.options();

		return kanban.templates['field-time'].render({
			field: self.record(),
			fieldvalue: fieldvalueRecord,
			fieldOptions: fieldOptions,
			card: 'undefined' === typeof card.record ? {} : card.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	this.optionsRender = function () {
		var self = this;
		var fieldRecord = self.record();
		var fieldOptions = functions.optionsFormat(self.options());

		return kanban.templates['board-modal-field-time'].render({
			board: self.board().record(),
			field: fieldRecord,
			fieldOptions: fieldOptions
		});

		var fieldOptions = functions.optionsFormat(self.options());

		var tagsHtml = "";
		for(var tagId in fieldOptions.tags) {
			var tag = fieldOptions.tags[tagId];
			tagsHtml += kanban.templates['board-modal-field-tags-tag'].render({
				tag: tag,
				field_id: self.id()
			});
		}

		return kanban.templates['board-modal-field-tags'].render({
			board: self.board().record(),
			field: self.record(),
			fieldOptions: fieldOptions,
			tagsHtml: tagsHtml
		});
	}; // optionsRender

	this.addFunctionality = function($field) {
		var self = this;

	}; // addFunctionality


	/*
	*   modal step field events
	*/
	this.stepOnfocus = function(el){
		var self = this;
		var $el = $(el);

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);
	}

	this.stepOnkeydown =  function (el, e) {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
		var keyCode = e.keyCode;

		switch (keyCode) {
			case 13: // enter

				// Save it.
				el.blur();

				return false;
				break;

			case 27: // escape
				var prevValue = $el.data('prevValue');
				$el.val(prevValue);

				el.blur();
				break;
		}
	}; // stepOnfocus

	this.stepOnblur = function(el){
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		this.optionUpdate('step', $(el).val());
	}; // stepOnblur


	/*
	*	card field events
	*/
	this.onClickbutton = function (el, e) {
		var self = this;

		clearTimeout(self.timerSave);

		var $el = $(el);
		var $field = $el.closest('.field');
		var input = $el.attr('data-input');
		var operator = $el.attr('data-operator');

		var $input = $('.form-control-' + input, $field);
		var step = self.options().step;

		var val = $input.val();

		val = self.stringToFloat(val);

		val += (step * operator);

		if ( val < 0 ) {
			val = 0;
		} else {
			val = Math.round(val * 1000) / 1000;
		}

		$input.val(val);

		self.timerSave = setTimeout(function () {
			self.updateValue($field);
		}, 1000);


	}; // onClickbutton

	this.onFocus = function (el) {
		var self = this;

		clearTimeout(self.timerSave);

		var $el = $(el);
		var $field = $el.closest('.field').addClass('is-editing');
		var $card = $el.closest('.card').addClass('is-editing');
		var $lane = $field.closest('.lane').addClass('is-editing');

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);

	}; // onFocus

	this.onBlur = function (el, e) {
		var self = this;

		var $el = $(el);

		var $field = $el.closest('.field').removeClass('is-editing');
		var $card = $el.closest('.card').removeClass('is-editing');
		var $lane = $field.closest('.lane').removeClass('is-editing');

		self.updateValue($field);

	}; // onBlur

	this.onKeydown = function (el, e) {
		var self = this;

		switch (e.keyCode) {
			case 13: // enter

				el.blur();
				return false;

				// If just enter, save it.
				break;

			case 27: // escape
				el.blur();

				var $el = $(el);

				var prevValue = $el.data('prevValue');
				$el.val( prevValue );

				break;
		}
	}; // onKeydown

	// this.onChange = function (el, e) {
	// 	// console.log('Field_Title.onBlur');
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $field = $el.closest('.field').addClass('is-editing');
	// 	var isEstimate = $el.parent('.horizContainer').hasClass('is-estimate');
	// 	self.delayedUpdateValue($field, isEstimate);
	// }; // onChange
	//
	// this.onFocus = function (el) {
	// 	// console.log('Field_Title.prototype.onFocus');
	//
	// 	var self = this;
	//
	// 	clearTimeout(self.timerSave);
	//
	// 	var $el = $(el);
	// 	var $field = $el.closest('.field').addClass('is-editing');
	// 	var $card = $el.closest('.card').addClass('is-editing');
	// 	var $lane = $field.closest('.lane').addClass('is-editing');
	//
	// 	// Save the current value for restoring.
	// 	var isEstimate = $el.parent('.horizContainer').hasClass('is-estimate');
	// 	var value = self.getValue($field, isEstimate);
	// 	$el.data('prevValue', value);
	// }; // onFocus
	//
	// this.onKeydown = function (el, e) {
	// 	// console.log('Field_Title.onKeydown');
	//
	// 	var self = this;
	//
	// 	switch (e.keyCode) {
	// 		case 13: // enter
	//
	// 			// If not shift + enter, save it.
	// 			if ( !e.shiftKey ) {
	// 				el.blur();
	// 				return false;
	// 			}
	//
	// 			// If just enter, save it.
	// 			break;
	//
	// 		case 27: // escape
	// 			var prevValue = $(el).data('prevValue');
	// 			$(el).val( prevValue );
	// 			el.blur();
	//
	// 			break;
	// 	}
	// }; // onKeydown

	this.getValue = function ($field) {

		var self = this;

		var $hours = $('.form-control-hours', $field);
		var $estimate = $('.form-control-estimate', $field);

		var hours = $hours.val();
		hours = self.stringToFloat(hours);

		var estimate = 0;
		if ( $estimate.length == 1 ) {
			var estimate = $estimate.val();
			estimate = self.stringToFloat(estimate);
		}

		return{
			hours: hours,
			estimate: estimate
		};

	}; // getValue

} // Field_Time

// inherit Field
Field_Time.prototype = Object.create(Field.prototype);

// correct the constructor pointer because it points to Field
Field_Time.prototype.constructor = Field_Time;

module.exports = Field_Time