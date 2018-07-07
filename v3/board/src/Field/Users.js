var Field = require('../Field')
var functions = require('../functions')

function Field_Users(record) {

	Field.call(this,record);

	this._self.options = $.extend(
		this._self.options,
		{
			select_multiple: true,
			available_users: 'wp'
		}
	);

	this.getUsers = function (format) {
		var self = this;

		var available_users = self.options().available_users;

		if ( available_users == 'wp' ) {
			return kanban.app.getUsers(format);
		}

		return self.board().getUsers(format);
	};

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

		return kanban.templates['field-users'].render({
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

		var $selectize =  $('.field-users-form-control', $field).selectize({
			// delimiter: ',',
			valueField: 'id',
			labelField: 'display_name',
			searchField: ['email', 'display_name'],
			persist: false,
			options: self.getUsers('array'),
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
			render: {
				item: function(item, escape) {
					return '<div class="selectize-item">' +
						'' + escape(item.display_name) + '' +
						'</div>';
				},
				option: function(item, escape) {
					var label = item.display_name || item.user_email;
					var caption = item.display_name ? item.user_email : null;
					return '<div>' +
						'' + escape(label) + '' +
						(caption ? ' (' + escape(caption) + ')' : '') +
						'</div>';
				}
			},
			onChange: function (value) {

				var content = value.split(',');

				self.updateValue($field, content);
			}
		});

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
		return $('.field-users-form-control', $field).get(0).selectize.getValue();
	}

	this.optionsRender = function () {
		var self = this;

		var fieldOptions = functions.optionsFormat(self.options());

		return kanban.templates['board-modal-field-users'].render({
			board: self.board().record(),
			field: self.record(),
			fieldOptions: fieldOptions
		});
	}; // optionsRender

	this.optionsAddUsers = function() {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var timeId = Date.now();
		var fieldRecord = self.record();

		var optionHtml = kanban.templates['board-modal-field-users-user'].render({
			user: {
				id: timeId
			},
			field_id: self.id()
		});

		$('#board-modal-field-' + self.id() + ' .board-modal-field-users-list' ).append(optionHtml);

	}; // optionsAddUsers

	this.optionsDeleteUsers = function(el) {
		$(el).closest('.board-modal-field-user').remove();
		this.optionsUserssUpdate();
	}; // optionsDeleteUsers

	this.optionsUserssUpdate = function () {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var users = [];
		$('#board-modal-field-' + self.id() + ' .board-modal-field-user' ).each(function () {
			$user = $(this);
			var id = $user.attr('data-user-id');
			var value = $.trim($('.form-control', $user).val());

			if ( '' == value ) {
				return true;
			}

			users.push({
				id: id,
				content: value
			});
		});

		self.optionUpdate('users', users);

	}; // optionsUsersOnBlur

	this.optionsUsersOnblur = function (el) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		self.optionsUserssUpdate();

	}; // optionsUsersBlur

	this.optionsUsersOnfocus = function (el) {
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

	this.optionsUsersOnkeydown = function (el, e) {
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

	this.formatContentForComment = function (content) {
		var self = this;

		var toReturn = [];
		for (var i in content ) {
			var userId = content[i];

			if ( 'undefined' === typeof kanban.users[userId] ) {
				continue;
			}

			toReturn.push(kanban.users[userId].display_name());
		}

		return toReturn.join(', ');
	}; // formatContentForComment

	this.applyFilter = function(fieldValue, filterElement) {
		var fieldUsers = fieldValue.content();
		var filterUsers = filterElement.value.split(',');		
		
		switch (filterElement.operator) {
			//Operator: =
			case "0":
				if (fieldUsers.length === filterUsers.length) {
					for (userId of fieldUsers) {
						if (filterUsers.indexOf(userId) === -1) {
							return false;
						}
					}
					return true;
				} else {
					return false;
				}

			//Operator: !=
			case "1":
				if (fieldUsers.length === filterUsers.length) {
					for (userId of fieldUsers) {
						if (filterUsers.indexOf(userId) === -1) {
							return true;
						}
					}
					return false;
				} else {
					return true;
				}
				
			//Operator: includes
			case "6":
				for (userId of filterUsers) {
					if (fieldUsers.indexOf(userId) === -1) {
						return false;
					}
				}
				return true;
			//Operator: does not include
			case "7":
				for (userId of filterUsers) {
					if (fieldUsers.indexOf(userId) !== -1) {
						return false;
					}
				}
				return true;

			//Operator: one of
			case "8":
				for (userId of filterUsers) {
					if (fieldUsers.indexOf(userId) !== -1) {
						return true;
					}
				}
				return false;
		}
	}

} // Field_Userss

Field_Users.prototype = Object.create(Field.prototype);
Field_Users.prototype.constructor = Field_Users;

module.exports = Field_Users