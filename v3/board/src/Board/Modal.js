var Lane = require('../Lane');
var dragula = require('dragula')
var functions = require('../functions')
var Field = require('../Field');
var User = require('../User');
// var fieldTypes = require('../Field/index')
var dragula = require('dragula')


Board_Modal = function (board) {

	var _self = {};
	_self.board = board;

	_self.drake = {
		lanes: null,
		fields: null
	};

	this.board = function () {
		return _self.board;
	}; // board

	this.drake = function () {
		return _self.drake;
	}; // drake

	this.show = function (el, tab) {
		// console.log('Board.modal.show');
		var self = this;

		// board-users includes board, so we only need to test for the lowest cap, or board-users.
		if (!kanban.app.current_user().hasCap('board-users')) {
			return false;
		}

		var boardRecord = self.board().record();

		var lanesHtml = '';
		for (var i in boardRecord.lanes_order) {

			var laneId = boardRecord.lanes_order[i];

			if ('undefined' === typeof kanban.lanes[laneId]) {
				continue;
			}

			var lane = kanban.lanes[laneId];
			var optionsLane = functions.optionsFormat(lane.options());

			lanesHtml += kanban.templates['board-modal-lane'].render({
				board_id: self.board().id(),
				lane: lane.record(),
				optionsLane: optionsLane,
				colors: kanban.app.colors
			});
		}

		var fieldsHtml = '';
		for (var i in boardRecord.fields_order) {

			var fieldId = boardRecord.fields_order[i];

			if ('undefined' === typeof kanban.fields[fieldId]) {
				continue;
			}

			var field = kanban.fields[fieldId];

			var fieldTemplate = 'board-modal-field';

			if ('undefined' !== typeof kanban.templates[fieldTemplate + '-' + field.record().field_type]) {
				fieldTemplate = fieldTemplate + '-' + field.record().field_type;
			}

			fieldsHtml += field.optionsRender(self.board());
		}

		var optionsBoard = self.board().options();
		optionsBoard.is_public = boardRecord.is_public;
		optionsBoard = functions.optionsFormat(optionsBoard);

		var optionsUser = functions.optionsFormat(kanban.app.current_user().optionsBoard());

		var modalBoardHtml = kanban.templates['board-modal'].render({
			board: boardRecord,
			lanesHtml: lanesHtml,
			fieldsHtml: fieldsHtml,
			optionsBoard: optionsBoard,
			optionsUser: optionsUser,
			isBoardAdmin: kanban.app.current_user().hasCap('board')
		});

		$('#modal').html(modalBoardHtml);

		$('#modal').modal({
			backdrop: 'static',
			keyboard: false,
			show: true
		});

		// Attempt to reload tab
		if ('undefined' !== typeof tab && '' != tab) {
			$('#modal-tab-' + tab).trigger('click');
		}
		else if ('undefined' !== typeof kanban.app.url().params['tab']) {
			$('#modal-tab-' + kanban.app.url().params['tab']).trigger('click');
		}

		// Add drag and drop to lanes.
		_self.drake.lanes = dragula(
			[document.getElementById('board-modal-lanes-accordion')],
			{
				moves: function (el, container, handle) {
					return handle.classList.contains('board-modal-lane-handle');
				}
			}
		)
		.on('drop', function (el) {
			self.lanesSaveOrder();
		});

		// Add drag and drop to fields.
		_self.drake.fields = dragula(
			[document.getElementById('board-modal-fields-accordion')],
			{
				moves: function (el, container, handle) {
					return handle.classList.contains('board-modal-field-handle');
				}
			}
		)
		.on('drop', function (el) {
			self.fieldsSaveOrder();
		});

		// Get all board users 
		// Get all users for searching
		$.when($.ajax({
			data: {
				type: 'user_cap',
				action: 'get_by_board',
				board_id: self.board().id()
			}
		}), $.ajax({
			data: {
				type: 'user',
				action: 'get_wp_users'
			}
		}))
		.done(function (response_board_users, response_wp_users) {

			if ('undefined' === typeof response_board_users || 'undefined' === typeof response_board_users[0] || 'undefined' === typeof response_board_users[0].data) {
				return false;
			}

			if ('undefined' === typeof response_wp_users || 'undefined' === typeof response_wp_users[0] || 'undefined' === typeof response_wp_users[0].data) {
				return false;
			}

			var response_board_users = response_board_users[0].data;
			var response_wp_users = response_wp_users[0].data;

			// Build board users html.
			var usersHtml = '';
			for (var userId in response_board_users) {

				var userRecord = response_board_users[userId];

				// Replace to ensure they have caps.
				var user = kanban.users[userId] = new User(userRecord);

				usersHtml += self.userRender(user);
			}

			// Populate board users html.
			$('#board-modal-users-accordion').html(usersHtml);


			var values = [];
			for (var userId in response_wp_users) {

				// Skip users already added.
				if ('undefined' !== typeof response_board_users[userId]) {
					continue;
				}

				if ('undefined' === typeof kanban.users[userId]) {
					kanban.users[userId] = response_wp_users[userId];
				}

				values.push(response_wp_users[userId]);
			}

			$('#board-modal-user-find-control').selectize({
				placeholder: 'Enter a name or email address to find a user',
				options: values,
				valueField: 'id',
				searchField: ['display_name', 'user_email'],
				closeAfterSelect: true,
				render: {
					option: function (item, escape) {
						var label = item.display_name || item.user_email;
						var caption = item.display_name ? item.user_email : null;
						return '<div>' +
							'<span class="b">' + escape(label) + '</span> ' +
							(caption ? '(' + escape(caption) + ')' : '') +
							'</div>';
					}
				},
				onChange: function (value) {
					self.userAdd(value);
					this.removeOption(value, true);
					this.clear(true);
				}
			});

			$('#modal-tab-pane-users').removeClass('loading');

		});

		// Update the url.
		kanban.app.urlParamUpdate('modal', 'board');
		kanban.app.urlReplace();

		return false;
	}; // modalShow

	// this.settingsSave = function (el) {
	//
	// 	var self = this;
	//
	// 	var $panel = $('#board-modal #modal-tab-pane-details');
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
	// 		var value = $input.val();
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
	// 	self.board().replace(data);
	// 	self.board().show();
	//
	// }; // settingsSave

	this.optionOnChange = function (el) {

		var self = this;

		if (!kanban.app.current_user().hasCap('board')) {
			return false;
		}

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		self.board().optionUpdate(key, value);

	}; // optionOnChange

	this.optionUserOnChange = function (el) {

		var self = this;

		// No cap check, because users can always update their own options.

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		kanban.app.current_user().optionBoardUpdate(key, value);

		// Give option time to save.
		setTimeout(function () {
			self.board().rerender();
		}, 1000);


	}; // optionUserUpdate

	this.titleOnfocus = function (el) {
		// console.log('modalTitleOnfocus');

		var self = this;

		var $el = $(el);

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);

	}; // titleOnfocus

	this.titleOnkeydown = function (el, e) {
		var self = this;

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

		var $el = $(el);

		var label = $el.val();
		label = label.formatForDb();

		var data = {
			label: label
		};

		self.board().replace(data);

		var headerTabsHtml = kanban.templates['header-board-tab'].render({
			board: self.board().record(),
			isActive: true
		});

		$('#header-board-tab-' + self.board().id()).closest('li').replaceWith(headerTabsHtml);

	}; // titleOnblur

	this.lanesSaveOrder = function () {
		// console.log('modalLanesSaveOrder');

		var self = this;

		var arrayOfIds = $.map(
			$('#board-modal-lanes-accordion .panel'),
			function (n, i) {
				return n.getAttribute('data-lane-id');
			}
		);

		self.board().replace({
			lanes_order: arrayOfIds
		});

		// Give the replace time to update the lanes order.
		setTimeout(function () {
			self.board().show();
		}, 500);

	}; // modalLanesSaveOrder

	this.laneAdd = function (el) {
		// console.log('modalLaneAdd');

		var self = this;

		var $el = $(el).addClass('loading');
		var $tabPane = $el.closest('.tab-pane');

		var ajaxDate = {
			type: 'lane',
			action: 'add',
			board_id: self.board().id()
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.lane.added_error);
				return false;
			}

			$tabPane.removeClass('loading');

			var laneId = response.data.id;
			var laneRecord = response.data;
			var lane = kanban.lanes[laneId] = new Lane(laneRecord);

			var laneModalHtml = kanban.templates['board-modal-lane'].render({
				lane: lane.record(),
				colors: kanban.app.colors
			});

			$(laneModalHtml)
			.appendTo('#board-modal-lanes-accordion')
			.find('.panel-title')
			.trigger('click');

			var lanesOrder = self.board().lanesOrder();

			lanesOrder.push(laneId);

			self.board().replace({
				lanes_order: lanesOrder
			});

			self.board().show();

			$el.removeClass('loading');
		});

	}; // modalLaneAdd

	this.laneDelete = function (el) {
		var self = this;

		var confirmed = confirm('Are you sure you want to delete this lane?');

		if (confirmed) {
			var $el = $(el);
			var $panel = $el.closest('.panel');

			var laneId = $panel.attr('data-lane-id');

			$panel.slideUp('fast', function () {
				$panel.remove();

				$.ajax({
					data: {
						type: 'lane',
						action: 'delete',
						lane_id: laneId
					}
				})
				.done(function () {
					delete kanban.lanes[laneId];
				});

				self.lanesSaveOrder();
			});
		}

	}; // laneDelete

	this.fieldsSaveOrder = function () {
		// console.log('modalFieldsSaveOrder');

		var self = this;

		var arrayOfIds = $.map(
			$('#board-modal-fields-accordion .panel'),
			function (n, i) {
				return n.getAttribute('data-field-id');
			}
		);

		self.board().replace({
			fields_order: arrayOfIds
		});

		// Give the replace time to update the lanes order.
		setTimeout(function () {
			self.board().show();
		}, 500);
	}; // fieldsSaveOrder

	this.fieldAdd = function (el) {
		// console.log('modalFieldAdd');

		var self = this;

		var $el = $(el);
		var $dropdown = $el.closest('.dropdown').addClass('loading');
		var type = $el.attr('data-field-type');
		// var $tabPane = $el.closest('.tab-pane');

		var ajaxDate = {
			type: 'field',
			action: 'add',
			// label: '',
			field_type: type,
			board_id: self.board().id()
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			// $tabPane.removeClass('loading');

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.field.added_error);
				return false;
			}

			var fieldId = response.data.id;
			var fieldRecord = response.data;

			// See if field type has it's own class.
			var fieldClass = 'Field';

			if (fieldRecord.field_type === null || 'undefined' === typeof fieldRecord.field_type) {
				return false;
			}

			// Build a "class" name to search for e.g. Field_Tags
			var fieldType = 'Field_' + fieldRecord.field_type.charAt(0).toUpperCase() + fieldRecord.field_type.slice(1);

			// See if "class" exists.
			if (typeof kanban.fieldTypes[fieldType] === 'function') {
				fieldClass = fieldType;
			}

			// Create field using "class" based on type.
			var field = kanban.fields[fieldId] = new kanban.fieldTypes[fieldClass](fieldRecord);

			var fieldHtml = field.optionsRender(self.board());

			$(fieldHtml).appendTo('#board-modal-fields-accordion').find('.panel-title').trigger('click');

			// Add field to board.
			self.fieldsSaveOrder();

			$dropdown.removeClass('loading');
		}); // done

	}; // modalFieldAdd

	this.fieldDelete = function (el) {
		var self = this;

		var confirmed = confirm('Are you sure you want to delete this field?');

		if (confirmed) {
			var $el = $(el);
			var $panel = $el.closest('.panel');

			var fieldId = $panel.attr('data-field-id');

			$panel.slideUp('fast', function () {
				$panel.remove();

				$.ajax({
					data: {
						type: 'field',
						action: 'delete',
						field_id: fieldId
					}
				});

				self.fieldsSaveOrder();
			});

		}

	}; // modalLaneDelete

	// this.userAdd = function (userId) {
	// 	var self = this;
	//
	// 	$.ajax({
	// 		data: {
	// 			type: 'user_cap',
	// 			action: 'add',
	// 			user_id: userId,
	// 			board_id: kanban.app.current_board_id()
	// 		}
	// 	})
	// 	.done(function (response) {
	//
	// 		var userId = response.data.id;
	// 		var userRecord = response.data;
	//
	// 		var userHtml = self.userRender(userRecord);
	//
	// 		$(userHtml).appendTo('#board-modal-users-accordion').find('.panel-title').trigger('click');
	//
	// 	});
	// }; // userAdd


	this.userAdd = function (userId) {
		var self = this;

		$.ajax({
			data: {
				type: 'user_cap',
				action: 'add',
				user_id: userId,
				board_id: kanban.app.current_board_id()
			}
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.user.updated_error);
				return false;
			}

			var userId = response.data.id;
			var userRecord = response.data;
			var user = kanban.users[userId] = new User(userRecord);

			var userHtml = self.userRender(user);
			$(userHtml).appendTo('#board-modal-users-accordion').find('.panel-title').trigger('click');
		});
	}; // userAdd


	// this.userRender = function (userRecord) {
	//
	// 	var self = this;
	//
	// 	var usersHtml = '';
	// 	var userId = userRecord.id;
	// 	var user = kanban.users[userId];
	//
	// 	var caps = self.board().caps();
	//
	// 	var isAdmin = false;
	// 	var titleAdmin = false;
	//
	// 	var boardRecord = self.board().record();
	//
	// 	for (var capName in caps) {
	// 		var cap = caps[capName];
	// 		var userCaps = user.capsAdmin().concat(user.capsBoard(boardRecord.id));
	//
	// 		var isChecked = userCaps.indexOf(capName);
	// 		cap.is_checked = isChecked === -1 ? false : true;
	//
	// 		cap.classes = '';
	//
	// 		if (capName == 'admin' && cap.is_checked) {
	// 			isAdmin = true;
	// 			continue; // Don't apply the rest of the logic to the admin cap.
	// 		}
	//
	// 		if (isAdmin) {
	// 			cap.classes += ' hide-admin ';
	// 		}
	//
	// 		if (cap.is_title) {
	// 			titleAdmin = cap.is_checked ? true : false;
	// 		}
	//
	// 		if (!cap.is_title && titleAdmin) {
	// 			cap.classes += ' hide-section ';
	// 		}
	//
	// 		if (capName == 'card-read') {
	// 			cap.is_checked = true;
	// 			cap.is_readonly = true;
	// 		}
	// 	}
	//
	// 	return kanban.templates['board-modal-user'].render({
	// 		caps: caps,
	// 		user: user.record(),
	// 		board: boardRecord
	// 	});
	//
	// }; // userRender

	this.userRender = function (user) {

		var self = this;

		var usersHtml = '';

		var caps = self.board().capsForUser(user);

		return kanban.templates['board-modal-user'].render({
			caps: caps,
			user: user.record(),
			board: self.board().record(),
			allowDelete: caps.board.is_readonly && user.id() == kanban.app.current_user_id() ? false : true
		});

	}; // userRender

	this.userSectionToggle = function (el, action) {

		var self = this;

		var $el = $(el);
		var $formGroup = $el.closest('.form-group');
		var $wrapperFormGroup = $el.closest('.wrapper-form-group');

		var cap = $formGroup.attr('data-cap');

		if (cap == 'admin') {
			var $formGroups = $('.form-group', $wrapperFormGroup).not($formGroup);

			if (action == 'show') {
				$formGroups.removeClass('hide-admin');
			}

			if (action == 'hide') {
				$formGroups.addClass('hide-admin');
			}
		} else {
			var $formGroups = $('.form-group[data-cap^="' + cap + '-"]', $wrapperFormGroup);

			if (action == 'show') {
				$formGroups.removeClass('hide-section');
			}

			if (action == 'hide') {
				$formGroups.addClass('hide-section');
			}
		}

	}; // userSectionToggle


	this.userSave = function (el) {

		var self = this;

		var $el = $(el);
		var $panel = $el.closest('.panel');
		var userId = $panel.attr('data-user-id');

		var capsArr = $('[data-name="capabilities"]:checked', $panel).map(function () {
			return $(this).val();
		}).get();

		// Ajax won't send empty array, so send empty string instead.
		if (capsArr.length == 0) {
			capsArr = '';
		}

		$.ajax({
			data: {
				type: 'user_cap',
				action: 'replace',
				user_id: userId,
				board_id: self.board().id(),
				capabilities: capsArr
			}
		});

	}; // userSave

	this.userDelete = function (el) {
		var self = this;
		var confirmed = confirm('Are you sure you want to delete this user?');

		if (confirmed) {
			var userId = $(el).attr('data-user-id');
			var ajaxDate = {
				type: 'user_cap',
				action: 'delete',
				board_id: self.board().id(),
				user_id: userId
			};

			$.ajax({
				data: ajaxDate
			})
			.done(function (response) {
				$(el).closest('div[data-user-id=' + userId + ']').remove();
			});

			//add back deleted user to selectize options
			if ('undefined' !== typeof kanban.users[userId]) {
				$('#board-modal-user-find-control')[0].selectize.addOption(kanban.users[userId].record());
			}
		}
	}; // userDelete

	this.userSelectAll = function (el) {

		$('#board-modal .board-modal-user-checkbox').prop('checked', true);
	}; // userSelectall

	this.userSelectNone = function (el) {

		$('#board-modal .board-modal-user-checkbox').prop('checked', false);
	}; // userSelectNone


	this.boardDelete = function (el) {
		var confirmed = confirm('Are you sure you want to delete this board?');

		if (confirmed) {
			var ajaxDate = {
				type: 'board',
				action: 'delete',
				board_id: this.board().id()
			};

			$.ajax({
				data: ajaxDate
			})
			.done(function (response) {
				kanban.app.modal.close();
				kanban.app.browserReload();
			});
		}
	}; // boardDelete

}; // Board.modal

module.exports = Board_Modal