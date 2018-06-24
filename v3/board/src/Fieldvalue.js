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
	}; // fieldId

	this.field = function () {
		return kanban.fields[_self.record.field_id];
	}; // field

	this.cardId = function () {
		return _self.record.card_id + 0;
	}; // cardId

	this.card = function () {
		return kanban.cards[_self.record.card_id];
	}; // card

	this.allowedFields = function () {
		return functions.cloneArray(_self.allowedFields);
	}; // allowedFields

	this.rerenderField = function () {
		console.log('fieldvalue.rerenderField');

		var self = this;

		if ( 'undefined' === typeof kanban.fields[self.fieldId()] ) {
			return false;
		}

		if ( 'undefined' === typeof kanban.cards[self.cardId()] ) {
			return false;
		}

		var field = kanban.fields[self.fieldId()];
		var card = kanban.cards[self.cardId()];

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

		var prevContent;
		if (self.field() != undefined) {
			prevContent = self.field().formatContentForComment(self.content());
		} else {
			prevContent = self.content();
		}


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
			action: 'replace'
		};

		if ( !isNaN(self.id()) ) {
			ajaxDate.fieldvalue_id = self.id()
		}

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

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.field.updated_error);
				return false;
			}

			// Replace the whole record.
			var fieldvalueId = response.data.id;
			var fieldvalueRecord = response.data;

			var fieldvalue = kanban.fieldvalues[fieldvalueId] = new Fieldvalue(fieldvalueRecord);

			self.field().rerender(self, self.card());

			// Just in case, add fieldvalue id to field.
			fieldvalue.addIdTo$field();

			var card = kanban.cards[fieldvalue.cardId()];
			card.fieldvalueAdd(fieldvalueId);

			var content = self.field().formatContentForComment(fieldvalueRecord.content);

			var comment = kanban.templates['field-comment-updated'].render({
				label: self.field().label(),
				content: content,
				prevContent: prevContent
			});

			card.commentAdd(
				comment
			);

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