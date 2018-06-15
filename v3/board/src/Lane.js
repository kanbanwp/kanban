var Card = require('./Card')
var functions = require('./functions')

function Lane(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['cards_order', 'board_id', 'label', 'options'];

	_self.options = {
		color: ''
	};

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.options = function () {
		return $.extend({}, _self.options, _self.record.options);
	}; // options


	this.board_id = function () {
		return _self.record.board_id;
	}; // id

	this.label = function () {
		// @todo make 'New lane' a translatable string
		return ( '' == _self.record.label ) ? 'New lane' : _self.record.label + '';
	}; // id

	this.cardsOrder = function () {
		return _self.record.cards_order.slice();
	}; // cardsOrder

	this.allowedFields = function () {
		return _self.allowedFields.slice();
	}; // allowedFields

	this.replace = function (data) {
		var self = this;

		if ( !kanban.app.current_user().hasCap('board') ) {
			return false;
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
			type: 'lane',
			action: 'replace',
			lane_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		})
		.done(function() {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.lane.updated_error);
				return false;
			}

			self.rerender();
		});


	}; // replace


	this.$el = function () {
		return $('#lane-{0}'.sprintf(this.id()));
	}; // $el

	this.updateCardsOrder = function () {

		var self = this;
		var $wrapperCards = $('.wrapper-cards', self.$el() );

		var arrayOfIds = $.map(
			$('.card', $wrapperCards),
			function(n, i){
				return n.getAttribute('data-id');
			}
		);

		_self.record.cards_order = arrayOfIds;

		// Ajax won't send empty array, so send empty string instead.
		if ( arrayOfIds.length == 0 ) {
			arrayOfIds = '';
		}

		self.$el().find('sup.lane-header-card-count').text(arrayOfIds.length);

		var ajaxDate = {
			type: 'lane',
			action: 'cards_order',
			cards_order: arrayOfIds,
			lane_id: self.id()
		};

		$.ajax({
			data: ajaxDate
		});
	}; // updateCardsOrder

	
	/**
	 * save current cards_order 
	 */ 
	this.saveCardsOrder = function() {
		var self = this;
		var cardsOrder = _self.record.cards_order;

		// Ajax won't send empty array, so send empty string instead.
		if ( cardsOrder.length == 0 ) {
			cardsOrder = '';
		}

		var ajaxDate = {
			type: 'lane',
			action: 'cards_order',
			cards_order: cardsOrder,
			lane_id: self.id()
		};

		$.ajax({
			data: ajaxDate
		});
	}; // saveCardsOrder

	/**
	 * add new card(s) to cards_order 
	 * @param elementsToAdd - array of card ids (Numbers) to be added
	 * @param startPos - index where new item will be added (not mandatory will be 0 by default)
	 */ 
	this.cardOrderAdd = function(startPos, elementsToAdd) {
		var self = this;
		var cardsOrder = _self.record.cards_order;

		//add elements
		if (elementsToAdd != undefined && Array.isArray(elementsToAdd) && elementsToAdd.length > 0) {
			if (startPos == undefined) {
				startPos = 0;
			}
			var args = [startPos, 0].concat(elementsToAdd);
			Array.prototype.splice.apply(cardsOrder, args);
		}

		this.saveCardsOrder();
	}

	/**
	 * remove card(s) from cards_order 
	 * @param elementsToRemove - array of card ids (Numbers) to be removed (not mandatory)	
	 */ 
	this.cardOrderRemove = function(elementsToRemove) {
		var self = this;
		var cardsOrder = _self.record.cards_order;

		//remove elements
		if (elementsToRemove != undefined && Array.isArray(elementsToRemove) && elementsToRemove.length > 0) {
			for (var i = cardsOrder.length - 1; i >= 0; i--) {
				if (elementsToRemove.indexOf(Number(cardsOrder[i])) > -1) {
					cardsOrder.splice(i, 1);
				}
			}
		}

		this.saveCardsOrder();
	}; // cardOrderRemove

	this.render = function (board) {
		// console.log('lane.render');
		if ( 'undefined' === typeof board ) {
			return false;
		}

		var self = this;

		var cardHtml = '';

		var laneRecord = self.record();

		// Build cards in lane in order from lanes card_order.
		for (var i in laneRecord.cards_order) {

			// Get the card id.
			var cardId = laneRecord.cards_order[i];

			// Make sure there's a corresponding card record.
			if ( 'undefined' === typeof kanban.cards[cardId] ) {
				continue;
			}

			// Get the card.
			var card = kanban.cards[cardId];

			// Get the card html.
			cardHtml += card.render(self, board);
		}

		var sidebar = false;

		var boardRecord = board.record();

		if ( boardRecord.lanes_order.length > 2 ) {

			if (boardRecord.lanes_order[0] == self.id()) {
				sidebar = 'left';
			}
			else if (boardRecord.lanes_order[boardRecord.lanes_order.length - 1] == self.id()) {
				sidebar = 'right';
			}
		}

		var userBoardOptions = kanban.app.current_user().optionsBoard();

		if ( userBoardOptions.view.indexOf('all-lanes') > -1 ) {
			sidebar = false;
		}

		// Make first lane "active" for mobile.
		var active = false;
		if ( boardRecord.lanes_order[0] == self.id() ) {
			active = true;
		}

		return kanban.templates['lane'].render({
			board: {
				ui: board.ui()
			},
			cards: cardHtml,
			cardCount: laneRecord.cards_order.length,
			lane: self.record(),
			sidebar: sidebar,
			active: active,
			isCreateCard: kanban.app.current_user().hasCap('card-create')
		});

	}; // render

	this.addFunctionality = function () {
		var self = this;

		// this.$el().one(
		// 	'mouseover',
		// 	function () {
		//
		// 	}
		//
		// );


		setTimeout(function () {
			for (var i in self.cardsOrder()) {
				var cardId = self.cardsOrder()[i];
				if ( 'undefined' !== typeof kanban.cards[cardId] ) {
					kanban.cards[cardId].addFunctionality();
				}
			}
		}, 10);
	}; // addFunctionality

	this.rerender = function () {
		// console.log('Lane.rerender');
		var self = this;

		var $lane = $('#lane-' + self.id());

		if ( $lane.hasClass('is-editing') ) {
			return false;
		}

		var board = kanban.boards[self.record().board_id];

		var laneHtml = self.render(board);
		$lane.replaceWith(laneHtml);

		self.addFunctionality();

		board.addDragAndDrop();

		return true;
	}; // rerender

	this.cardAdd = function (el) {
		var self = this;

		if ( !kanban.app.current_user().hasCap( 'card-create') ) {
			return false;
		}

		var $btn = $(el);

		$btn.addClass('loading');

		var ajaxDate = {
			type: 'card',
			action: 'add',
			lane_id: self.id(),
			board_id: self.board_id()
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.card.added_error);
				return false;
			}

			var cardId = response.data.id;
			var cardRecord = response.data;

			var card = kanban.cards[cardId] = new Card(cardRecord);

			var board = kanban.boards[self.board_id()];

			// @todo focus on first field. Not sure why this doesn't work.
			// setTimeout(function () {
			// 	var firstInput = $('input, [contenteditable]', $card).get(0);
			// 	if ('undefined' !== typeof firstInput) {
			// 		firstInput.focus();
			// 	}
			// }, 100);

			$btn.removeClass('loading');

			card.commentAdd(
				kanban.templates['card-comment-added'].render()
			);

			//add card to lane's card_order and rerender lane
			self.cardOrderAdd(0, [cardId]);
			self.rerender();
		})
		.always(function () {
			$btn.removeClass('loading');
		});
	}; // cardAdd


	this.colorOnclick = function (el) {
		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
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
				self.optionUpdate('color', color);
			}
		);

	}; // colorOnclick

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

	this.optionUpdate = function (option, value) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var newOption = {};
		newOption[option] = value;

		var options = $.extend({}, self.options(), newOption);

		self.replace({
			options: options
		});

	}; // optionUpdate

}; // Lane




module.exports = Lane