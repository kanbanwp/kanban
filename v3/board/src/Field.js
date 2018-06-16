var Fieldvalue = require('./Fieldvalue')
var functions = require('./functions')

function Field(record) {

	this._self = {};
	this._self.record = record;

	if ( 'undefined' === typeof this._self.record.options || '' == this._self.record.options )
	{
		this._self.record.options = {};
	}

	this._self.allowedFields = ['label', 'options', 'field_type'];

	this._self.options = {
		placeholder: '',
		is_hidden: false,
		default_content: ''
	};

	this.record = function () {
		return functions.cloneObject(this._self.record);
	}; // record

	this.id = function () {
		return this._self.record.id + 0;
	}; // id

	this.boardId = function () {
		return this._self.record.board_id + 0;
	}; // id

	this.isHidden = function () {
		return this._self.record.options.is_hidden === true ? true : false;
	}; // id

	this.label = function () {

		if ( '' !== this._self.record.label ) {
			return this._self.record.label;
		}

		return this.fieldType();
	} ; // fieldType

	this.fieldType = function () {
		return this._self.record.field_type;
	} ; // fieldType

	this.fieldClass = function () {
		return this.constructor.name;
	}

	this.allowedFields = function () {
		return this._self.allowedFields.slice();
	}; // allowedFields

	this.options = function () {
		return $.extend({}, this._self.options, this._self.record.options);
	}; // options

	this.board = function () {
		if ( 'undefined' === typeof kanban.boards[this._self.record.board_id] ) {
			return {};
		}

		return kanban.boards[this._self.record.board_id];
	}; // board

	this.render = function (fieldvalue, card) {

		if ( 'undefined' === typeof fieldvalue ) {
			fieldvalue = {};
		}

		if ( 'undefined' === typeof card ) {
			return false;
		}

		var self = this;

		var fieldRecord = self.record();

		var template = 'field';
		var template_name = 'field-' + fieldRecord.field_type;
		if ('undefined' !== typeof kanban.templates[template_name]) {
			template = template_name;
		}

		return kanban.templates[template].render({
			field: fieldRecord,
			fieldvalue: 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record(),
			card: 'undefined' === typeof card.record ? {} : card.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	// Catch-all, placeholder.
	this.addFunctionality = function ($field) {
		var self = this;

		if ( $field.hasClass('func-added') ) {
			return false;
		}

		// $field.one(
		// 	'mouseover',
		// 	function () {
		// 	}
		// );

		$field.addClass('func-added');

	}; // addFunctionality

	this.rerender = function (fieldvalue, card) {
		var self = this;

		// Find instances of field using class because it could be on card or modal.
		var $field = $('.field-' + card.id() + '-' + self.id());

		if ( $field.length == 0 || $field.hasClass('is-editing') ) {
			return false;
		}

		// var fieldRecord = self.record();
		// var card = kanban.cards[fieldvalue.cardId()];
		// var fieldvaluesByField = card.fieldvaluesByField();
		// var fieldvalueId = fieldvaluesByField[self.id()];
		// var fieldvalue = kanban.fieldvalues[fieldvalueId];

		var fieldHtml = self.render(fieldvalue, card);

		$field.replaceWith(fieldHtml);
		self.addFunctionality($field);
	}; // rerender

	this.updateValue = function ($field, newContent) {
		// console.log('updateValue');

		var self = this;

		if ( !kanban.app.current_user().hasCap('card-write') ) {
			return false;
		}

		var fieldvalueId = $field.attr('data-fieldvalue-id');
		var cardId = $field.attr('data-card-id');

		if ( ('' === fieldvalueId || 'undefined' === typeof fieldvalueId) && ('' === cardId || 'undefined' === typeof cardId) ) {
			// 	// @todo throw error, notice?
				return false;
		}

		// @todo Legacy. Get rid of this.
		if ( 'undefined' === typeof newContent )
		{
			newContent = self.getValue($field);
		}

		// var prevContent = '';
		if ( 'undefined' !== typeof fieldvalueId && 'undefined' !== typeof kanban.fieldvalues[fieldvalueId]) {
			var fieldvalue = kanban.fieldvalues[fieldvalueId];
			var prevContent = self.formatContentForComment(fieldvalue.content());
		}

		var data = {
			content: newContent,
			field_id: self.id()
		};

		if ( '' !== cardId && 'undefined' !== typeof cardId ) {
			data.card_id = cardId;
		}

		if ('' !== fieldvalueId && 'undefined' !== typeof fieldvalueId ) {
			data.fieldvalue_id = fieldvalueId;
		}

		// Build the data for the new fieldvalue.
		var ajaxDate = {
			type: 'fieldvalue',
			action: 'replace'
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(true, data, ajaxDate);

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.field.updated_error);
				return false;
			}

			// Replace the whole record.
			var fieldvalueId = response.data.id;
			var fieldvalueRecord = response.data;

			var fieldvalue = kanban.fieldvalues[fieldvalueId] = new Fieldvalue(fieldvalueRecord);

			// Just in case, add fieldvalue id to field.
			fieldvalue.addIdTo$field();

			var card = kanban.cards[fieldvalue.cardId()];
			card.fieldvalueAdd(fieldvalueId);

			var content = self.formatContentForComment(data.content);

			var comment = kanban.templates['field-comment-updated'].render({
				label: self.label(),
				content: content,
				prevContent: prevContent
			});

			card.commentAdd(
				comment
			);

		});

	}; // updateValue



	// Placeholder for fiel type specific fields.
	// this.formatValue = function (content) {
	// 	return content;
	// }; // formatValue

	this.replace = function (data) {
		var self = this;

		if ( !kanban.app.current_user().hasCap('card-write') ) {
			return false;
		}

		// Removed fields that aren't allowed.
		for (var field in data ) {
			if ( self.allowedFields().indexOf(field) == -1 ) {
				delete data[field];
			}
		}

		// Update the record.
		$.extend(self._self.record, data);

		var ajaxDate = {
			type: self.fieldClass(),
			action: 'replace',
			field_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(true, data, ajaxDate);

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.field.updated_error);
				return false;
			}

			var fieldId = response.data.id;
			var fieldRecord = response.data;

			var field = kanban.fields[fieldId] = new Field(fieldRecord);

			var board = kanban.boards[self.boardId()];

			board.show();
		}); // done

		// $(document).trigger('/field/replace/', this.record());
	}; // replace


	// this.settingsRender = function (board) {
	//
	// 	var self = this;
	//
	// 	var fieldRecord = self.record();
	//
	// 	var fieldTemplate = 'board-modal-field';
	//
	// 	if ( 'undefined' !== typeof kanban.templates[fieldTemplate + '-' + fieldRecord.field_type] ) {
	// 		fieldTemplate = fieldTemplate + '-' + fieldRecord.field_type;
	// 	}
	//
	// 	return kanban.templates[fieldTemplate].render({
	// 		board: board.record(),
	// 		field: fieldRecord
	// 	});
	// }; // settingsRender

	//settingset instance method transformed to prototype method
	/* this.settingsGet = function () {

		var self = this;

		var $panel = $('#board-modal-field-' + self.id());

		var data = {};

		$('[data-name]', $panel).each(function (n) {
			var $input =  $(this);

			if ( $input.is('[type="radio"]') && !$input.is(':checked') ) {
				return true;
			}

			var name = $input.attr('data-name');

			var value;
			if ($input.is('[type="checkbox"]')) {
				value = $input.is(':checked');
			} else {
				value = $input.val();
			}			

			if ( name.substring(0, 7) == 'options' ) {
				var nameParts = name.split(':');

				if ( nameParts.length != 2 ) {
					return false;
				}

				name = nameParts[1];

				if ( 'undefined' === typeof data.options ) {
					data.options = {};
				}

				// Set field value as part of options.
				data.options[name] = value;

				// Then set value to all of options.
				value = data.options;

				// Now set name at options.
				name = 'options';

			}

			data[name] = value;
		});

		return data;

	}; // settingsGet */

	// this.settingsSave = function () {
	// 	// console.log('modalFieldSave');
	//
	// 	var self = this;
	//
	//
	// 	var data = self.settingsGet();
	//
	// 	// @todo make sure data has actually changed
	//
	// 	self.replace(data);
	//
	// }; // modalFieldSave

	this.titleOnfocus = function (el) {
		// console.log('modalLaneOnfocus');

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);
	}; // titleOnfocus

	this.titleOnkeydown = function (el, e) {
		// console.log('modalLaneOnkeydown');

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
	}; // titleOnkeydown

	this.titleOnblur = function (el) {
		// console.log('modalLaneOnblur');
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
		var label = $.trim($el.val());

		self.replace({
			label: label
		});

		// Update the panel title.
		var $panel = $el.closest('.panel');
		var $panelTitle = $('.panel-title', $panel);

		if (label == '') {
			label = '<span class="text-muted">New lane</span>';
		}

		$panelTitle.html(label);

	}; // titleOnblur

	this.placeholderOnfocus = function (el) {
		// console.log('modalLaneOnfocus');

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);
	}; // placeholderOnfocus

	this.placeholderOnkeydown = function (el, e) {
		// console.log('modalLaneOnkeydown');

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
	}; // placeholderOnkeydown

	this.placeholderOnblur = function (el) {
		// console.log('modalLaneOnblur');
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
		var placeholder = $.trim($el.val());

		self.optionUpdate('placeholder', placeholder);

	}; // placeholderOnblur

	this.optionsRender = function () {

		var self = this;

		var fieldTemplate = 'board-modal-field';

		if ('undefined' !== typeof kanban.templates[fieldTemplate + '-' + self.fieldType()]) {
			fieldTemplate = fieldTemplate + '-' + self.fieldType();
		}

		var fieldOptions = functions.optionsFormat(self.options());

		return kanban.templates[fieldTemplate].render({
			board: self.board().record(),
			field: self.record(),
			fieldOptions: fieldOptions
		});

	}; // optionsRender

	this.optionOnChange = function (el) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		self.optionUpdate(key, value);

	}; // optionOnChange

	this.optionUpdate = function (option, value) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var newOption = {};
		newOption[option] = value;

		var options = $.extend(self.options(), newOption);

		self.replace({
			options: options
		});

	}; // optionUpdate

	this.formatContentForComment = function (content) {

		var self = this;

		// Example: "the content was updated".
		return '';

	}; // getValue

} // Field

//settingsGet needs to be prototype method, Tags field for example will extend and use this parent method
// Field.prototype.settingsGet = function () {
//
// 	var self = this;
//
// 	var $panel = $('#board-modal-field-' + self.id());
//
// 	var data = {};
//
// 	$('[data-name]', $panel).each(function (n) {
// 		var $input =  $(this);
//
// 		if ( $input.is('[type="radio"]') && !$input.is(':checked') ) {
// 			return true;
// 		}
//
// 		var name = $input.attr('data-name');
//
// 		var value;
// 		if ($input.is('[type="checkbox"]')) {
// 			value = $input.is(':checked');
// 		} else {
// 			value = $input.val();
// 		}
//
// 		if ( name.substring(0, 7) == 'options' ) {
// 			var nameParts = name.split(':');
//
// 			if ( nameParts.length != 2 ) {
// 				return false;
// 			}
//
// 			name = nameParts[1];
//
// 			if ( 'undefined' === typeof data.options ) {
// 				data.options = {};
// 			}
//
// 			// Set field value as part of options.
// 			data.options[name] = value;
//
// 			// Then set value to all of options.
// 			value = data.options;
//
// 			// Now set name at options.
// 			name = 'options';
//
// 		}
//
// 		data[name] = value;
// 	});
//
// 	return data;
//
// }; // settingsGet

module.exports = Field
