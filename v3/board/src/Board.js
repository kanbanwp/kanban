var Board_Modal = require('./Board/Modal')
var Lane = require('./Lane');
var Field = require('./Field');
var User = require('./User');
// var fieldTypes = require('./Field/index')
var dragula = require('dragula')
var functions = require('./functions')
require('selectize')

// var MediumEditor = require('medium-editor')

function Board(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['label', 'lanes_order', 'fields_order', 'options', 'is_public'];
	_self.users = null;
	_self.usersAsArray = null;
	_self.filters = [];

	_self.isLanesLoaded = false;

	this.isLanesLoaded = function () {
		return _self.isLanesLoaded === true ? true : false;
	}; // isLanesLoaded

	this.setLanesLoaded = function () {
		_self.isLanesLoaded = true;
	}; // setLanesLoaded

	_self.drake = dragula({
		moves: function (el, container, handle) {
			// card-move-handle for FF
			return handle.classList.contains('ei-menu') || handle.classList.contains('card-move-handle');
		}
	})
	.on('drop', function (el, newCardWrapper, prevCardWrapper) {
		var cardId = el.getAttribute('data-id')
		var card = kanban.cards[cardId];

		if (_self.record.options.card_creator_move_card === "true" && Number(card.record().created_user_id) != Number(kanban.app.current_user().id())) {
			_self.drake.cancel(true);
			alert('Only user who created this card is allowed to move it');
			return false;
		}

		var prevLaneId = prevCardWrapper.getAttribute('data-lane-id');
		var prevLane = kanban.lanes[prevLaneId];
		prevLane.updateCardsOrder();

		var newLaneId = newCardWrapper.getAttribute('data-lane-id');

		if (newLaneId != prevLaneId) {
			var newLane = kanban.lanes[newLaneId];
			newLane.updateCardsOrder();

			var comment = kanban.templates['card-comment-moved-to-lane'].render({
				newLane: newLane.label(),
				prevLane: prevLane.label()
			});

		} else {
			var comment = kanban.templates['card-comment-moved-to-position'].render();
		}

		card.commentAdd(
			comment
		);

		card.replace({
			lane_id: newLaneId
		});
	});

	_self.caps = {
		'board': {
			'label': 'Board',
			'description': 'Change anything on a board',
			'is_title': true,
			// 'is_public': false,
			// 'options': {show_task_id: 0},
		},
		'board-users': {
			'label': 'Edit users',
			'description': 'Add and remove users'
		},
		'card': {
			'label': 'Cards',
			'description': 'Do anything on all cards',
			'is_title': true
		},
		'card-read': {
			'label': 'View cards',
			'description': 'View all cards on the board. Any user added to the board can always view cards.'
		},
		'card-create': {
			'label': 'Create cards',
			'description': 'Create new cards on the board'
		},
		'card-read-hidden': {
			'label': 'Read hidden card fields',
			'description': 'Read hidden fields on cards'
		},
		'card-write': {
			'label': 'Edit cards and card fields',
			'description': 'Update card data, reorder cards'
		},
		'comment': {
			'label': 'Comments',
			'description': 'Do anything to comments',
			'is_title': true
		},
		'comment-read': {
			'label': 'Read comments',
			'description': 'Read comments'
		},
		'comment-write': {
			'label': 'Add comments',
			'description': 'Create comments'
		}
	};

	_self.options = {
		card_creator_delete_card: false,
		card_creator_move_card: false,
		users_list_mention: 'wp'
	};

	this.modal = new Board_Modal(this);

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.lanesOrder = function () {
		var laneIds = [];
		for (var i in _self.record.lanes_order) {

			var laneId = _self.record.lanes_order[i];

			if ('undefined' === typeof kanban.lanes[laneId]) {
				continue;
			}

			laneIds.push(laneId);
		}

		return laneIds;
	}; // lanesOrder

	this.fieldsOrder = function () {
		var fieldIds = [];
		for (var i in _self.record.fields_order) {

			var fieldId = _self.record.fields_order[i];

			if ('undefined' === typeof kanban.fields[fieldId]) {
				continue;
			}

			fieldIds.push(fieldId);
		}
		
		return fieldIds;
	}; // fieldsOrder

	this.label = function () {
		if ('' == _self.record.label) {
			return 'New Board';
		}

		return _self.record.label + '';
	}; // label

	// this.view = function () {
	// 	return _self.view;
	// };

	this.caps = function () {
		return $.extend(true, {}, _self.caps);
	}; // caps

	this.options = function () {
		return $.extend(true, {}, _self.options, _self.record.options);
	}; // options

	this.allowedFields = function () {
		return _self.allowedFields.slice();
	}; // allowedFields

	this.drake = function () {
		return _self.drake;
	}; // drake

	this.ui = function () {
		var self = this;

		var ui = {};
		ui.lane_count = 'undefined' === typeof self.lanesOrder() ? 0 : self.lanesOrder().length;
		ui.lane_count_show = ui.lane_count < 3 ? 2 : ui.lane_count - 2;

		ui.lane_percent = ui.lane_count < 3 ? 50 : 100 / ui.lane_count;
		ui.lane_show_percent = ui.lane_count < 3 ? 0 : 100 / ui.lane_count_show;
		ui.lane_header_percent = ui.lane_count < 3 ? 50 : 100 / ui.lane_count_show;

		ui.lane_width = ui.lane_count < 3 ? 100 : (ui.lane_show_percent * 2) + 100;

		if (kanban.app.current_user().optionsBoard().view.indexOf('all-lanes') !== -1) {
			ui.lane_show_percent = 0;
			ui.lane_width = 100;
			ui.lane_header_percent = ui.lane_count < 3 ? 50 : 100 / ui.lane_count;
		}

		return ui;
	};

	this.$el = function () {
		return $('#board-{0}'.sprintf(this.id()));
	};

	this.render = function (app) {
		// console.log('render');

		if ('undefined' === typeof app) {
			return false;
		}

		var self = this;

		var laneHtml = '';
		var boardRecord = self.record();

		for (var i in boardRecord.lanes_order) {

			var laneId = boardRecord.lanes_order[i];

			if ('undefined' === typeof kanban.lanes[laneId]) {
				// self.laneOrderRemove([laneId]);
				continue;
			}

			var lane = kanban.lanes[laneId];

			laneHtml += lane.render(self);
		}

		var view = kanban.app.current_user().optionsBoard().view;

		return kanban.templates['board'].render({
			board: boardRecord,
			lanes: laneHtml,
			ui: self.ui(),
			isActive: kanban.app.current_board_id() == self.id() ? true : false,
			view: view.join(' ')
		});

	}; // render

	this.rerender = function () {
		var self = this;

		var isActive = false;
		if (kanban.app.current_board_id() == self.id()) {
			self.show();
			isActive = true;
		}

		var headerTabsHtml = kanban.templates['header-board-tab'].render({
			board: self.record(),
			isActive: isActive
		});

		$('#header-board-tab-' + self.id()).closest('li').replaceWith(headerTabsHtml);

	}; // rerender

	this.tabToBoard = function (el) {
		var self = this;

		if (kanban.app.current_board_id() !== self.id()) {
			self.show(true);
		}

		var $el = $(el);
		var $dropdown = $el.closest('.dropdown');

		// If board is in the "more boards" dropdown, move it out.
		if ( $dropdown.length == 1 ) {
			$el.closest('li').insertBefore($dropdown);
		}

	}; // tabToBoard

	this.show = function (isBoardChange) {
		console.log('board.show');

		var self = this;

		//reset search when board is changed by user
		if (isBoardChange === true) {
			$('#footer input[type=search]').val('');
			kanban.app.urlParamRemove('search');
		}

		if (!self.isLanesLoaded()) {
			// $('#wrapper-board').html(`<div class="container" id="board-placeholder">
			// 	<i class="ei ei-loading"></i>
			// </div>`);

			kanban.app.notify('Loading...');

			$.ajax({
				data: {
					type: 'board',
					action: 'get_data',
					board_id: self.id()
				}
			})
			.done(function (response) {

				if ( 'undefined' === typeof response.data ) {
					kanban.app.notify(kanban.strings.board.retrieve_error);
					return false;
				}

				// kanban.new_data = $.extend(true, kanban.new_data, response.data);
				kanban.app.processNewData(response.data);
				self.setLanesLoaded();
				self.show();
				return;

			}); // done
		} else {

			var isEditing = false;

			// if ( $('.lane.is-editing').length > 0 ) {
			// 	isEditing = true;
			// }

			// @todo confirm might be overthinking it.
			var confirmed = isEditing ? confirm('Looks like you\'re editing something. Refresh the current board?') : true;

			if (confirmed) {

				if (kanban.app.current_board_id() != self.id()) {
					kanban.app.current_user().optionAppUpdate('current_board', self.id());
				}

				var boardHtml = self.render(kanban.app);

				$('#wrapper-board').html(boardHtml);

				self.addFunctionality();

				// Copy the current board into the mobile dropdown toggle.
				var label = self.label() == '' ? 'New Board' : self.label();
				$('#header-board-toggle-label').text(label);

				// Set as active.
				var $li = $('#header-board-tab-' + self.id()).closest('li').addClass('active');
				$('#header-board-tabs li').not($li).removeClass('active');

				kanban.app.urlParamUpdate('board', self.id());
				kanban.app.urlReplace();

				document.title = self.label();

				if ($('#board-' + self.id()).hasClass('all-lanes')) {
					$('#footer-menu-board-view-toggle-all-lanes').addClass('active');
				} else {
					$('#footer-menu-board-view-toggle-all-lanes').removeClass('active');
				}

				if ($('#board-' + self.id()).hasClass('compact')) {
					$('#footer-menu-board-view-toggle-compact').addClass('active');
				} else {
					$('#footer-menu-board-view-toggle-compact').removeClass('active');
				}

				// For mobile, hide dropdown
				$('#header-navbar').removeClass('in');
			} // confirmed

			return confirmed;
		}


	}; // show

	this.addFunctionality = function () {
		var self = this;

		self.addDragAndDrop();

		setTimeout(function () {
			var lanesOrder = self.lanesOrder();
			for (var i in lanesOrder) {
				var laneId = lanesOrder[i];

				if ('undefined' !== typeof kanban.lanes[laneId]) {
					kanban.lanes[laneId].addFunctionality();
				}
			}
		}, 10);

	}; // addFunctionality

	this.addDragAndDrop = function () {

		var self = this;

		// Returns htmlcollection.
		var lanesCollection = document.getElementById('board-' + self.id()).getElementsByClassName('wrapper-cards');

		// Convert to array.
		var lanesArr = Array.prototype.slice.call(lanesCollection);

		// Add lanes to drake.
		self.drake().containers = lanesArr;

	}; // addDragAndDrop

	this.replace = function (data) {

		var self = this;

		if (!kanban.app.current_user().hasCap('admin-board-create') && !kanban.app.current_user().hasCap('board')) {
			return false;
		}

		// Removed fields that aren't allowed.
		for (var field in data) {
			if (self.allowedFields().indexOf(field) == -1) {
				delete data[field];
			}
		}

		// Update the record.
		$.extend(_self.record, data);

		var ajaxDate = {
			type: 'board',
			action: 'replace',
			board_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		});

		self.usersListMention(true); // rebuild.

	}; // replace

	/**
	 * remove lane(s) from lanes_order
	 * @param elementsToRemove - array of lane ids (Numbers) to be removed (not mandatory)
	 */
	this.laneOrderRemove = function (elementsToRemove) {
		var self = this;
		var lanesOrder = _self.record.lanes_order;

		//remove elements
		if (elementsToRemove != undefined && Array.isArray(elementsToRemove) && elementsToRemove.length > 0) {
			for (var i = lanesOrder.length - 1; i >= 0; i--) {
				if (elementsToRemove.indexOf(lanesOrder[i]) > -1) {
					lanesOrder.splice(i, 1);
				}
			}
		}

		self.replace({
			lanes_order: _self.record.lanes_order
		});
	}; // cardOrderRemove

	this.toggleFilterModal = function () {

		var self = this;

		var fieldIds = self.fieldsOrder();

		var fieldHtml = '';
		for ( var i in fieldIds ) {
			if (Object.prototype.hasOwnProperty.call(fieldIds, i)) {
				var fieldId = fieldIds[i];
				var field = kanban.fields[fieldId];

				if ( 'undefined' === typeof kanban.templates['filter-' + field.fieldType()] ) {
					continue;
				}

				var filterValue = "";
				var filterOperator = "";
				for(var j in _self.filters) {
					if (_self.filters[j].fieldId == fieldId) {
						filterValue = _self.filters[j].value;
						filterOperator = _self.filters[j].operator;
					}
				}

				if (filterValue && field.fieldType() === "date") {
					var userAppOptions = kanban.app.current_user().optionsApp();
					filterValue = Date.prototype.formatDate(filterValue, userAppOptions.date_view_format);
				}

				var storedFilter = {};
				storedFilter.filterValue = filterValue;
				storedFilter["filterOperator" + filterOperator] = true;

				var fieldOptions = field.options();

				fieldHtml += kanban.templates['filter-' + field.fieldType()].render({
					fieldId: fieldId,
					storedFilter: storedFilter,
					fieldOptions: fieldOptions
				});
			}
		}

		var modalHtml = kanban.templates['filter-modal'].render({
			fieldHtml: fieldHtml
		});

		$('#modal').html(modalHtml);

		$('#modal').modal({
			backdrop: 'static',
			keyboard: false,
			show: true
		});

		//for date filter add datepicker
		$('#modal').find('.date-filter-value').one(
			'mouseover',
			function () {
				var userAppOptions = kanban.app.current_user().optionsApp();
				var weekStart = userAppOptions.first_day_of_week == "sunday" ? 0 : 1;
				$(this).datepicker({
					weekStart: weekStart,
					todayHighlight: true,
					format: userAppOptions.date_view_format
				});
			});

		//for users filter add selectize with the available users of the field
		$('#modal').find('.users-filter-value').each(function() {
			var available_users = $(this).attr('data-available-users');
			var users = [];
			if ( available_users == 'wp' ) {
				users = kanban.app.getUsers('array');
			} else {
				users = self.getUsers('array');
			}

			var $selectize =  $(this).selectize({
				valueField: 'id',
				labelField: 'display_name',
				searchField: ['email', 'display_name'],
				persist: false,
				options: users,
				items: [],
				maxItems: null,					
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
				}
			});		
		});

	}; // toggleFilterModal

	this.applyFilters = function(filters){			
		_self.filters = filters;

		var currentLanes = this.lanesOrder();
		showCards = [];
		for(var i = 0; i < currentLanes.length; i++) {
			var laneId = currentLanes[i];

			// Make sure there's a corresponding lane record.
			if ('undefined' === typeof kanban.lanes[laneId]) {						
				continue;
			}

			var lane = kanban.lanes[laneId];
			var laneCards = lane.cardsOrder();
			for (var j = 0; j < laneCards.length; j++) {
				var cardId = laneCards[j];

				// Make sure there's a corresponding card record.
				if ( 'undefined' === typeof kanban.cards[cardId] ) {
					continue;
				}
				
				var card = kanban.cards[cardId];

				var fieldValues = card.fieldvalues();
				var fieldvaluesByField = card.fieldvaluesByField();
				var cardMatches = true;
				var filteredFieldCount = 0;
				//check if all filter conditions are fulfilled by the fieldvalues of the card
				for(var l = 0; l < filters.length && cardMatches; l++) {
					if (!filters[l].value) {
						filteredFieldCount++;
						continue;
					}
					for(var k = 0; k < fieldValues.length; k++) {						
						if ('undefined' === typeof kanban.fieldvalues[fieldValues[k]]) {
							continue;
						}

						var fieldvalue = kanban.fieldvalues[fieldValues[k]];

						if ('undefined' === typeof kanban.fields[fieldvalue.fieldId()]) {
							continue;
						}

						if (filters[l].fieldId == fieldvalue.fieldId()) {
							filteredFieldCount++;							
							cardMatches = fieldvalue.field().applyFilter(fieldvalue, filters[l]);
							break;
						}
						
					}
				}
				//store card id if all conditions matched
				if (cardMatches && filters.length == filteredFieldCount) {
					showCards.push(card.id());
				}
			
			}
		}

		this.showSelectedCardsOnly(showCards);
	}

	this.showSelectedCardsOnly = function(showCards){
		//hide cards that are visible and not in the array of matching cards 
		$("#board-" + this.id() + ' .card:visible').each(function () {
			if (showCards.indexOf(Number($(this).attr('data-id'))) == -1) {
				$(this)
				.stop(true, false)
				.animate({
					height: "toggle",
					opacity: "toggle"
				}, 200);
			}
		});

		//show invisible matching cards
		for (var i = 0; i < showCards.length; i++) {
			if (!$('#card-' + showCards[i]).is(':visible')) {
				$('#card-' + showCards[i])
				.stop(true, false)
				.animate({
					height: "toggle",
					opacity: "toggle"
				}, 200);
			}
		}
	}

	this.showAllCards = function(){
		_self.filters = [];

		$("#board-" + this.id()).find('.card:not(:visible)')
				.stop(true, false)
				.animate({
					height: "toggle",
					opacity: "toggle"
				}, 200);
	}

	this.usersListMention = function (format) {
		var self = this;

		if (self.options().users_list_mention == 'wp') {
			return kanban.app.getUsers(format);
		} else {
			return self.getUsers(format);
		}

	}; // usersListMention

	this.capsForUser = function (user) {
		var self = this;

		var userRecord = user.record();

		// Copy caps (Use jQuery for deep copy)
		var caps = self.caps();

		var boardRecord = self.record();
		var isBoardCreator = boardRecord.created_user_id == user.id() ? true : false;

		if (isBoardCreator) {
			userRecord.capabilities.boards[self.id()] = ['board'];
		}

		var isAdmin = false;
		var titleAdmin = false;

		for (var capName in caps) {
			var cap = caps[capName];

			if ( 'undefined' === typeof userRecord.capabilities.boards[self.id()] ) {
				continue;
			}

			var isChecked = userRecord.capabilities.boards[self.id()].indexOf(capName);
			cap.is_checked = isChecked === -1 ? false : true;
			cap.is_readonly = false;

			cap.classes = '';

			// Every user can read cards. That's the point.
			if (capName == 'card-read') {
				cap.is_checked = true;
				cap.is_readonly = true;
			}

			if (capName == 'board' && cap.is_checked) {
				isAdmin = true;

				if (isBoardCreator) {
					cap.is_readonly = true;
					cap.label += ' (Creator)';
				}

				continue; // Don't apply the rest of the logic to the admin cap.
			}

			if (isAdmin) {
				cap.classes += ' hide-admin ';
			}

			if (cap.is_title) {
				titleAdmin = cap.is_checked ? true : false;
			}

			if (!cap.is_title && titleAdmin) {
				cap.classes += ' hide-section ';
			}
		}

		return caps;
	}; // capsForUser

	this.getUsers = function (format, rebuild) {

		if (_self.users == null || rebuild) {

			_self.users = [];
			_self.usersAsArray = [];

			for ( var userId in _self.record.users ) {
				if ( 'undefined' !== typeof kanban.users[userId] ) {
					_self.users[userId] = kanban.users[userId];
					_self.usersAsArray.push(kanban.users[userId].record());
				}
			}
		}

		if (format == 'array') {
			return _self.usersAsArray;
		}

		return _self.users;
	};

	this.sidebarToggle = function (el) {

		var self = this;

		var $toggle = $(el);

		var $row = $('.row-board', self.$el());
		var direction = $toggle.attr('data-direction');
		var row_offset = parseInt($row.attr('data-offset'));
		var row_left = parseInt($row.attr('data-left'));

		if (direction == 'right') {
			row_left = row_left !== -row_offset ? -row_offset : -(row_offset * 2);
		}

		if (direction == 'left') {
			row_left = row_left !== -row_offset ? -row_offset : 0;
		}


		$('.row-board', self.$el()).animate(
			{
				'margin-left': row_left + '%'
			},
			250,
			function () {
				$row.attr('data-left', row_left)
			}
		);

	}; // sidebarToggle

	// this.tabChange = function () {
	// 	var self = this;
	//
	// 	var label = self.record().label;
	// 	$('#header-board-dropdown-toggle-label').text(label);
	//
	// 	kanban.app.urlParamUpdate('board', self.id());
	//
	// 	kanban.app.urlReplace();
	// }; // tabChange

	this.viewToggleCompact = function (el) {
		var self = this;

		var $el = $(el);

		var view = kanban.app.current_user().optionsBoard().view;

		if (!Array.isArray(view)) {
			view = [];
		}

		var i = view.indexOf('compact');

		if (i === -1) {
			view.push('compact');
			$el.addClass('active');
		} else {
			view.splice(i, 1);
			$el.removeClass('active');
		}

		kanban.app.current_user().optionBoardUpdate('view', view, self.id());

		self.show();

	}; // viewToggleCompact

	this.viewToggleAllLanes = function (el) {
		var self = this;

		var $el = $(el);

		var view = kanban.app.current_user().optionsBoard().view;

		if (!Array.isArray(view)) {
			view = [];
		}

		var i = view.indexOf('all-lanes');

		if (i === -1) {
			view.push('all-lanes');
			$el.addClass('active');
		} else {
			view.splice(i, 1);
			$el.removeClass('active');
		}

		kanban.app.current_user().optionBoardUpdate('view', view, self.id());

		self.show();

	}; // viewToggleAllLanes

	this.optionUpdate = function (option, value) {

		var self = this;

		if (!kanban.app.current_user().hasCap('board')) {
			return false;
		}

		_self.record.options[option] = value;

		if (Array.isArray(value) && value.length == 0) {
			value = '';
		}

		$.ajax({
			data: {
				type: 'board',
				action: 'replace_option',
				board_id: self.id(),
				option: option,
				value: value
			}
		});

	}; // optionUpdate

}; // Board

module.exports = Board;