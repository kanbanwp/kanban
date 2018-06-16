var functions = require('./functions')

function User(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['capabilities', 'options'];

	_self.optionsApp = {
		first_day_of_week: 'sunday',
		live_updates_check_interval: 10,
		do_live_updates_check: true,
		date_view_format: 'M d, yyyy',
		current_board: null
	};

	_self.optionsBoard = {
		view: [],
		show_task_id: true
	};

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.display_name = function () {
		return functions.cloneString(_self.record.display_name);
	}; // display_name

	this.user_email = function () {
		return functions.cloneString(_self.record.user_email);
	}; // display_name

	this.display_name_long = function () {
		return functions.cloneString(_self.record.display_name_long);
	}; // display_name_long

	this.display_name_short = function () {
		return functions.cloneString(_self.record.display_name_short);
	}; // display_name_short

	this.currentBoardId = function () {

		if ( 'undefined' === typeof _self.record.options.app.current_board ) {
			return null;
		}

		if ( 'undefined' === typeof kanban.boards[_self.record.options.app.current_board] ) {
			return null;
		}

		return _self.record.options.app.current_board;
	}; // currentBoardId

	this.currentBoard = function () {
		var self = this;

		var boardId = self.currentBoardId();

		if ( boardId == null ) {
			return null;
		}

		return kanban.boards[boardId];
	}; // currentBoardId

	this.allowedFields = function () {
		return functions.cloneArray(_self.allowedFields);
	}; // allowedFields

	this.optionBoardUpdate = function (option, value, boardId) {
		var self = this;

		if ( 'undefined' === typeof boardId ) {
			boardId = kanban.app.current_board_id();
		}

		// if ( 'undefined' === typeof _self.record.options.boards[boardId] ) {
		// 	_self.record.options.boards[boardId] = [];
		// }
		//
		// _self.record.options.boards[boardId][option] = value;

		if ( Array.isArray(value) && value.length == 0 ) {
			value = '';
		}

		$.ajax({
			data: {
				type: 'user_option',
				action: 'replace',
				option: option,
				value: value,
				board_id: boardId
			}
		})
		.done(function (response) {
			if ( 'undefined' === typeof response.data ) {
				kanban.app.notify(kanban.strings.board.retrieve_error);
				return false;
			}

			var boardId = response.data.board_id;
			var options = response.data.options;

			_self.record.options.boards[boardId] = options;
		});
	}; // optionsUpdate

	this.optionAppUpdate = function (option, value) {
		var self = this;

		if ( 'undefined' === typeof _self.optionsApp[option] ) {
			return false;
		}

		// _self.record.options.app[option] = value;

		if ( Array.isArray(value) && value.length == 0 ) {
			value = '';
		}

		$.ajax({
			data: {
				type: 'user_option',
				action: 'replace_app',
				option: option,
				value: value
			}
		})
		.done(function (response) {
			if ( 'undefined' === typeof response.data ) {
				kanban.app.notify(kanban.strings.board.retrieve_error);
				return false;
			}

			var options = response.data.options;

			_self.record.options.app = options;
		});

		kanban.app.current_board().rerender();

	}; // optionAppUpdate

	// this.optionsAppUpdate = function (options) {
	// 	var self = this;
	//
	// 	_self.record.options.app = $.extend(true, _self.record.options.app, options);
	//
	// 	$.ajax({
	// 		data: {
	// 			type: 'user_option',
	// 			action: 'replace_app_all',
	// 			options: _self.record.options.app
	// 		}
	// 	});
	//
	// 	if (Boolean(options.do_live_updates_check) == false) {
	// 		clearInterval(kanban.app.timers().updates);
	// 	} else {
	// 		kanban.app.updatesInit();
	// 	}
	//
	// 	kanban.app.current_board().show();
	// }; // optionsAppUpdate

	this.optionsBoard = function () {
		var self = this;

		return $.extend(true, {}, _self.optionsBoard, _self.record.options.boards[self.currentBoardId()]);
	}; // options

	this.optionsApp = function () {
		return $.extend(true, {}, _self.optionsApp, _self.record.options.app);
	}; // options

	this.capsAdmin = function () {
		var self = this;
		return _self.record.capabilities.admin.slice();
	}; // caps

	this.capsBoard = function (boardId) {
		var self = this;

		if ( 'undefined' === typeof _self.record.capabilities.boards[boardId] ) {
			return [];
		}

		var capsBoard = _self.record.capabilities.boards[boardId].slice();

		return this.capsAdmin().concat(capsBoard);
	}; // caps

	this.hasCap = function (cap) {

		var self = this;

		if ( self.id() == 0 ) {
			return false;
		}

		var userCaps = self.capsAdmin();

		// Check for admin.
		if ( userCaps.includes('admin') ) {
			return true;
		}

		// Check for cap.
		if ( userCaps.includes(cap) ) {
			return true;
		}

		// If they got this far and no boardId, try to load it.
		if ( 'undefined' === typeof boardId || isNaN(boardId) ) {
			boardId = kanban.app.current_board_id();
		}

		// If they got this far and no boardId, false.
		if ( 'undefined' === typeof boardId || isNaN(boardId) ) {
			return false;
		}

		// Break it up.
		var capArr = cap.split('-');

		var capSection = capArr[0];

		// If user created the board, treat them like a board admin.
		if ( 'undefined' !== typeof kanban.app.current_board() && kanban.app.current_board() != null ) {
			var board = kanban.app.current_board();

			if ( board.record().created_user_id == self.id() && capSection == 'board' ) {
				return true;
			}
		}

		var userBoardCaps = self.capsBoard(boardId);

		// Check for section admin.
		if ( userBoardCaps.includes(capSection) ) {
			return true;
		}

		// Check for cap.
		if ( userBoardCaps.includes(cap) ) {
			return true;
		}
		
		// Assume false.
		return false;
	}; // hasCap

	this.renderMention = function () {
		var self = this;
		var mentionHtml = kanban.templates['user-mention'].render({
			user_id: self.id(),
			name: self.display_name_short()
		});

		return mentionHtml;
	}; // renderMention

	// this.hasBoardCap = function (cap) {
	//
	// 	var self = this;
	//
	// 	var userCaps = self.capsBoard(kanban.app.current_board());
	//
	// 	// Check for admin.
	// 	if ( userCaps.includes('admin') ) {
	// 		return true;
	// 	}
	//
	// 	// Break it up.
	// 	var capArr = cap.split('-');
	//
	// 	// Check for section admin.
	// 	var capSection = capArr[0];
	// 	if ( userCaps.indexOf(capSection) !== -1 ) {
	// 		// console.log('capSection', capSection);
	// 		return true;
	// 	}
	//
	// 	// Check for cap.
	// 	if ( userCaps.indexOf(cap) !== -1 ) {
	// 		// console.log('cap', capSection);
	// 		return true;
	// 	}
	//
	// 	// If user created the board, treat them like an admin.
	// 	if ( 'undefined' !== typeof kanban.app.current_board() ) {
	// 		var board = kanban.app.current_board();
	//
	// 		if (board.record().created_user_id == self.id()) {
	// 			return true;
	// 		}
	// 	}
	//
	// 	// Assume false.
	// 	return false;
	// }; // hasCap

} // User


module.exports = User