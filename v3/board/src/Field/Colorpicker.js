var Field = require('../Field')

function Field_Colorpicker(record) {

	Field.call(this, record);

	this._self.options = $.extend(
		this._self.options,
		{
			view_card_corner: false
		}
	);

	this.render = function (fieldvalue, card) {
		var self = this;

		if ('undefined' === typeof fieldvalue) {
			fieldvalue = {};
		}

		if ('undefined' === typeof card) {
			return false;
		}

		var fieldOptions = self.options();

		return kanban.templates['field-colorpicker'].render({
			field: this.record(),
			fieldvalue: 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record(),
			fieldOptions: fieldOptions,
			card: 'undefined' === typeof card.record ? {} : card.record(),
			// colors: kanban.app.colors,
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	// this.getValue = function ($field) {
	// 	// console.log('Field_Title.prototype.getValue');
	//
	// 	var self = this;
	//
	// 	var $input = $('[data-name="color"]', $field);
	//
	// 	return $input.val();
	// }; // getValue

	this.onClick = function (el) {

		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field');
		var $dropdown = $el.closest('.dropdown');
		var $dropdownMenu = $('.dropdown-menu', $dropdown);

		var $cp = kanban.app.getColorPicker();

		$dropdownMenu.html($cp);

		$cp.one(
			'click',
			function (e) {

				// Get the color.
				var color = kanban.app.colorPickerOnclick(e);

				// Put the color picker back.
				$cp.appendTo('body');

				// Set the button color.
				$el.css('background', color);

				// Save the lane color.
				self.updateValue($field, color);
			}
		);

	}; // onClick

	// this.onColorSelect = function (el) {
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $field = $el.closest('.field').removeClass('is-editing');
	// 	var $dropdown = $el.closest('.dropdown');
	// 	var $input = $('[data-name="color"]', $field);
	// 	var $btn = $('.btn-color', $dropdown);
	//
	// 	var colorKey = $el.attr('data-color-key');
	// 	var color = kanban.app.colors[colorKey];
	//
	// 	$btn.css('background', color);
	// 	$input.val(color);
	//
	// 	this.updateValue($field);
	// }; // onColorSelect

	// this.optionsRender = function () {
	//
	// 	var self = this;
	//
	// 	var fieldTemplate = 'board-modal-field-' + self.record().field_type;
	//
	// 	return kanban.templates[fieldTemplate].render({
	// 		board: self.board().record(),
	// 		field: self.record(),
	// 		colors: kanban.app.colors
	// 	});
	// }; // optionsRender

	// this.settingsOnColorSelect = function (el) {
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $field = $el.closest('.field');
	// 	var $input = $('input', $field);
	// 	var $btn = $('.btn-color', $field);
	//
	// 	var colorKey = $el.attr('data-color-key');
	// 	var color = kanban.app.colors[colorKey];
	//
	// 	$btn.css('background', color);
	// 	$input.val(color);
	//
	// 	self.settingsSave();
	// }; // settingsOnColorSelect

	this.optionColorOnclick = function (el) {

		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field');
		var $dropdown = $el.closest('.dropdown');
		var $dropdownMenu = $('.dropdown-menu', $dropdown);

		var $cp = kanban.app.getColorPicker();

		$dropdownMenu.html($cp);

		$cp.one(
			'click',
			function (e) {

				// Get the color.
				var color = kanban.app.colorPickerOnclick(e);

				// Put the color picker back.
				$cp.appendTo('body');

				// Set the button color.
				$el.css('background', color);

				// Save the color as default color.
				self.optionUpdate('default_content', color);
			}
		);

	}; // optionColorOnclick

	this.getValue = function ($field) {
		// console.log('getValue');

		var self = this;

		var $btn = $('.btn-color', $field);
		var color = $btn.css('background-color');

		return color;
	}; // getValue

	this.formatContentForComment = function (content) {
		var self = this;

		return content.formatForApp();
	}; // formatContentForComment

} // Field_Colorpicker

// inherit Field
Field_Colorpicker.prototype = Object.create(Field.prototype);

// correct the constructor pointer because it points to Field
Field_Colorpicker.prototype.constructor = Field_Colorpicker;

module.exports = Field_Colorpicker