var Field = require('../Field')
var functions = require('../functions')

function Field_Tags(record) {

	Field.call(this,record);

	this._self.options = $.extend(
		this._self.options,
		{
			select_multiple: true,
			add_new_on_field: false,
			tags: []
		});

	this.getTags = function () {
		var self = this;

		var tags = self.options().tags;

		if ( !Array.isArray(self.options().tags) ) {
			return [];
		}

		return tags;
	};

	// this._self.tagsById = {};
	// var tags = this.tags();
	// for ( var i in tags ) {
	// 	var tag = tags[i];
	// 	this._self.tagsById[tag.id] = tag;
	// }
	//
	// this.tagsById = function () {
	// 	return functions.cloneObject(this._self.tagsById);
	// };
	//
	// this.tagById = function (id) {
	// 	if ( 'undefined' === typeof this._self.tagsById[id] ) {
	// 		return {};
	// 	}
	//
	// 	return functions.cloneObject(this._self.tagsById[id]);
	// };

	this.render = function (fieldvalue, card) {
		var self = this;

		if ('undefined' === typeof fieldvalue) {
			fieldvalue = {};
		}

		if ('undefined' === typeof card) {
			return false;
		}

		var cardRecord = 'undefined' === typeof card.record ? {} : card.record();
		var fieldRecord = self.record();		
		
		var fieldvalueRecord = {};
		if ( 'undefined' !== typeof fieldvalue.record ) {
			fieldvalueRecord = fieldvalue.record();
		}
 	
		return kanban.templates['field-tags'].render({
			field: fieldRecord,
			fieldvalue: fieldvalueRecord,
			card: cardRecord,
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	this.addFunctionality = function ($field) {
		var self = this;

		var items = [];
		var fieldvalueId = $field.attr('data-fieldvalue-id');

		if ( 'undefined' !== typeof kanban.fieldvalues[fieldvalueId] ) {
			var fieldvalue = kanban.fieldvalues[fieldvalueId];
			items = fieldvalue.content();
		}
		console.log(self.options().add_new_on_field);

		var selectizeOptions = {
			// delimiter: ',',
			valueField: 'id',
			labelField: 'content',
			searchField: ['content'],
			persist: false,
			options: self.getTags(),
			items: items,
			maxItems: self.options().select_multiple ? null : 1,
			onFocus: function () {
				$field.addClass('is-editing');
				$field.closest('.card').addClass('is-editing');
				$field.closest('.lane').addClass('is-editing');
			},
			onBlur: function () {
				$field.removeClass('is-editing');
				$field.closest('.card').removeClass('is-editing');
				$field.closest('.lane').removeClass('is-editing');
			},
			onChange: function (value) {

				var content = value.split(',');

				self.updateValue($field, content);
			}
		};

		if ( self.options().add_new_on_field ) {
			selectizeOptions.create = function (input) {

				var tag = {
					id: Date.now(),
					content: input
				};

				// Get tags and add the new one.
				var tags = self.getTags();
				tags.push(tag);

				//Update the tags.
				self.optionUpdate('tags', tags);

				return tag;
			};
		}

		var $selectize =  $('.field-tags-form-control', $field).selectize(selectizeOptions);

	}; // addFunctionality
	
	// this.getSelectizeCreate = function() {
	// 	var self = this;
	//
	// 	var fieldRecord = this.record();
	// 	var canCreate = fieldRecord.options.add_new_on_field == 'true';
	// 	if (!canCreate) {
	// 		return false;
	// 	} else {
	// 		return function(input) {
	// 			return {
	// 				value: input,
	// 				text: input
	// 			}
	// 		}
	// 	}
	// }


	this.getValue = function ($field) {
		return $('.field-tags-form-control', $field).get(0).selectize.getValue();
	}

	this.optionsRender = function () {
		var self = this;

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

	this.optionsAddTag = function() {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var timeId = Date.now();
		var fieldRecord = self.record();

		var optionHtml = kanban.templates['board-modal-field-tags-tag'].render({
			tag: {
				id: timeId
			},
			field_id: self.id()
		});

		$('#board-modal-field-' + self.id() + ' .board-modal-field-tags-list' ).append(optionHtml);

	}; // optionsAddTag

	this.optionsDeleteTag = function(el) {
		$(el).closest('.board-modal-field-tag').remove();
		this.optionsTagsUpdate();
	}; // optionsDeleteTag

	this.optionsTagsUpdate = function () {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var tags = [];
		$('#board-modal-field-' + self.id() + ' .board-modal-field-tag' ).each(function () {
			$tag = $(this);
			var id = $tag.attr('data-tag-id');
			var value = $.trim($('.form-control', $tag).val());
			
			if ( '' == value ) {
				return true;
			}

			tags.push({
				id: id,
				content: value
			});
		});

		self.optionUpdate('tags', tags);

	}; // optionsTagOnBlur

	this.optionsTagOnblur = function (el) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		self.optionsTagsUpdate();

	}; // optionsTagBlur

	this.optionsTagOnfocus = function (el) {
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

	this.optionsTagOnkeydown = function (el, e) {
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

	this.addTag = function (tag) {
		var self = this;

		// If the field does not allow adding new tags, return.
		if ( self.record().options.add_new_on_field !== true ) {
			return false;
		}

		$.ajax({
			data: {
				type: 'field_tags',
				action: 'add_field',
				field_id: self.id(),
				tag: tag
			}
		});

	}; // addTag

	this.formatContentForComment = function (content) {
		var self = this;
		var tags = self.getTags();

		var toReturn = [];
		for(var i = 0; i < tags.length; i++){
			if (content.indexOf(tags[i].id) !== -1 ) {
				toReturn.push(tags[i].content);
			}
		}
		
		return toReturn.join(', ');
	}; // formatContentForComment

} // Field_Tags

Field_Tags.prototype = Object.create(Field.prototype);
Field_Tags.prototype.constructor = Field_Tags;

module.exports = Field_Tags