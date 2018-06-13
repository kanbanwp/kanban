var functions = require('./functions')

function Fieldvalue(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['content', 'card_id', 'field_id'];

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.content = function () {
		return functions.clone(_self.record.content);
	}; // content

	this.fieldId = function () {
		return _self.record.field_id;
	}; // id

	this.field = function () {
		return kanban.fields[_self.record.field_id];
	}; // id

	this.cardId = function () {
		return _self.record.card_id;
	}; // id

	this.allowedFields = function () {
		return functions.cloneArray(_self.allowedFields);
	}; // allowedFields

	this.rerenderField = function () {
		var self = this;

		if ( 'undefined' === typeof kanban.fields[self.fieldId()] ) {
			return false;
		}

		var field = kanban.fields[self.fieldId()];
		var card = kanban.fields[self.cardId()];

		return field.rerender(self, card);

	}; // rerender

	this.addIdTo$field = function () {
		var self = this;

		self.$field().attr('data-fieldvalue-id', self.id());
	}; // addIdTo$field

	this.replace = function (data) {
		// console.log('replace');
		var self = this;

		if (!kanban.app.current_user().hasCap('card-write')) {
			return false;
		}

		var prevContent = self.content();

		// Removed fields that aren't allowed.
		for (var field in data ) {
			if ( self.allowedFields().indexOf(field) == -1 ) {
				delete data[field];
			}
		}

		// Update the record.
		$.extend(_self.record, data);

		var ajaxDate = {
			type: 'fieldvalue',
			action: 'replace',
			fieldvalue_id: self.id()
		};

		// Ajax won't send empty array, so send empty string instead.
		if ( 'undefined' !== typeof data.content && Array.isArray(data.content) && data.content.length == 0 ) {
				data.content = '';
		}

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			// Replace the whole record.
			var fieldvalueId = response.data.id;
			var fieldvalueRecord = response.data;

			var fieldvalue = kanban.fieldvalues[fieldvalueId] = new Fieldvalue(fieldvalueRecord);

			var field = self.field();

			if ( data.content != prevContent ) {
				var card = kanban.cards[fieldvalueRecord.card_id];
				var comment = kanban.strings.fieldvalue.updated.sprintf(
					field.label(),
					data.content
				);

				if ('undefined' !== typeof prevContent) {
					comment += kanban.strings.fieldvalue.updated_previous.sprintf(
						prevContent
					);
				}

				card.commentAdd(
					comment
				);
			}

		});

	}; // replace

	this.$field = function () {
		var self = this;
		return $('.field-' + self.cardId() + '-' + self.fieldId());
	}; // $field

	// $(document).trigger('/fieldvalue/init/', this.record());

} // Fieldvalue






// Fieldvalue.prototype.onBlur = function (el) {
//
// 	var self = this;
//
// 	var value = kanban.fields[self.record().field_id].getValue(el);
//
// 	self.replace({
// 		content: value
// 	});
// } // onBlur

module.exports = Fieldvalue