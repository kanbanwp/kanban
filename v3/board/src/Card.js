var Fieldvalue = require('./Fieldvalue')
var fieldTypes = require('./Field/index')
var Comment = require('./Comment')
var Card_Modal = require('./Card/Modal')
var functions = require('./functions')

function Card(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['lane_id'];

	_self.isCommentsLoaded = false;

	this.modal = new Card_Modal(this);

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.laneId = function () {
		return functions.cloneNumber(_self.record.lane_id);
	}; // id

	this.fieldsOrder = function () {
		return this.board().fieldsOrder();
	}; // fieldsOrder

	this.fieldvalues = function () {
		return _self.record.fieldvalues.slice();
	}; // fieldvalues

	this.comments = function () {
		return _self.record.comments.sort(function (a, b) {
			return a - b;
		}).slice();
	}; // fieldvalues

	this.commentsCount = function (user) {

		user = user === true ? true : false;

		if ( user ) {
			return _self.record.comment_count_user;
		}

		return _self.record.comments.length;
	}; // fieldvalues

	this.allowedFields = function () {
		return _self.allowedFields.slice();
	}; // allowedFields

	this.isCommentsLoaded = function () {
		return _self.isCommentsLoaded === true ? true : false;
	}; // isCommentsLoaded

	this.setCommentsLoaded = function () {
		_self.isCommentsLoaded = true;
	}; // setCommentsLoaded

	// Create field/fieldvalue lookup
	this.fieldvaluesByField = function () {
		var self = this;

		var fieldvaluesByField = [];

		for (var i in _self.record.fieldvalues) {
			var fieldvalueId = _self.record.fieldvalues[i];

			if ('undefined' === typeof kanban.fieldvalues[fieldvalueId]) {
				continue;
			}

			var fieldvalue = kanban.fieldvalues[fieldvalueId];

			fieldvaluesByField[fieldvalue.record().field_id] = fieldvalueId;
		}

		return fieldvaluesByField;
	}; // fieldvaluesByField


	this.replace = function (data) {
		var self = this;
		var cardRecord = self.record();

		if (!kanban.app.current_user().hasCap('card-create')) {
			return false;
		}

		var isDifferent = false;
		for (var field in data) {
			// Removed fields that aren't allowed.
			if (self.allowedFields().indexOf(field) == -1) {
				delete data[field];
				continue;
			}

			// Check for changes.
			var val = data[field];
			if (val != cardRecord[field]) {
				isDifferent = true;
			}
		}

		// If nothing's changed, stop here.
		if (!isDifferent) {
			return;
		}

		// Update the record.
		$.extend(_self.record, data);

		var ajaxDate = {
			type: 'card',
			action: 'replace',
			card_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		});

		// $(document).trigger('/lane/replace/', this.record());
	}; // replace

	this.fieldvalueAdd = function (id, rerender) {
		var self = this;

		rerender = rerender === true ? true : false;

		_self.record.fieldvalues.push(id);

		if (rerender) {
			self.rerender();
		}
	}; // replaceCardsOrder

	this.render = function (lane, board) {

		if ('undefined' === typeof lane || 'undefined' === typeof board) {
			return '';
		}

		var self = this;

		var fieldHtml = '';
		var fieldHiddenHtml = '';

		// var boardRecord = board.record();
		var cardRecord = self.record();

		var fieldvaluesByField = self.fieldvaluesByField();

		for (var i in self.board().fieldsOrder()) {

			var field_id = self.board().fieldsOrder()[i];

			if ('undefined' === typeof kanban.fields[field_id]) {
				continue;
			}

			// Get field record from orig data.
			var field = kanban.fields[field_id];

			var fieldvalue = {};
			if ('undefined' !== typeof fieldvaluesByField[field_id]) {
				fieldvalue = kanban.fieldvalues[fieldvaluesByField[field_id]];
			}

			if (field.record().is_hidden == 1) {
				fieldHiddenHtml += field.render(fieldvalue, self);
			}
			else {
				fieldHtml += field.render(fieldvalue, self);
			}
		}

		var showCardIdClass = self.board().record().options.show_task_id === "true" ? "show-card-id" : ""; 

		return kanban.templates['card'].render({
			fields: fieldHtml,
			fields_hidden: fieldHiddenHtml,
			card: cardRecord,
			lane: lane.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write'),
			showCardIdClass: showCardIdClass,
			commentsCount: self.commentsCount(true) > 0 ? self.commentsCount(true) : null
		});
	}; // render

	this.addFunctionality = function () {

		var self = this;

		var $el = self.$el();
		var $lane = $el.closest('.lane');

		var fieldsOrder = self.fieldsOrder();
		for (var i in fieldsOrder) {
			var fieldId = fieldsOrder[i];

			var $field = $('.field-' + fieldId, $el);

			kanban.fields[fieldId].addFunctionality($field);
		}

		$el.one(
			'mouseover',
			function () {
				$('.card-edit', $el)
				.on(
					'mouseover',
					function () {
						$el.addClass('is-active');
						$lane.addClass('is-active');
					}
				)
				.on(
					'mouseout',
					function () {
						$el.removeClass('is-active');
						$lane.removeClass('is-active');
					}
				);
			}
		);

	}; // addFunctionality

	this.rerender = function () {
		// console.log('rerender');

		var self = this;

		var $el = self.$el();

		if ( $el.hasClass('is-editing') ) {
			return false;
		}

		var laneId = self.record().lane_id;
		var lane = kanban.lanes[laneId];

		var boardId = lane.record().board_id;
		var board = kanban.boards[boardId];

		var cardHtml = self.render(lane, board);

		$el.replaceWith(cardHtml);

		// If the modal is open.
		if ($('#card-modal').length == 1 && kanban.app.urlParamGet['card'] == self.id()) {

			// Rerender the modal.
			self.modal.show();
		}
	}; // rerender

	this.commentAddToRecord = function (commentId) {
		var self = this;

		// Add to array.
		_self.record.comments.unshift(commentId);

		// Loop over array and remove dupes.
		var unique = [];
		for (var i = 0; i < _self.record.comments.length; i++) {
			var int = parseInt(_self.record.comments[i]);

			if (unique.indexOf(int) == -1) {
				unique.push(int);
			}
		}

		_self.record.comments = unique;
	}; // commentAddToRecord

	/**
	 *
	 * @param modify int Positive or negative number to change count.
	 */
	this.commentUpdateCount = function (modifyCount) {
		var self = this;

		if ( 'undefined' !== typeof modifyCount ) {
			modifyCount = parseInt(modifyCount);

			if ( !isNaN(modifyCount) ) {
				_self.record.comment_count_user = parseInt(_self.record.comment_count_user) + modifyCount;
			}
		}

		var $count = $('.card-comments-count', self.$el());

		if (_self.record.comment_count_user == 0) {
			$count.hide().text('');
		} else {
			$count.show().text(
				parseInt(_self.record.comment_count_user)
			);
		}
	}; // commentUpdateCount

	this.commentAdd = function (content, comment_type) {
		var self = this;

		if ('undefined' === typeof content || '' == content) {
			return false;
		}

		content = content.formatForDb();

		var types = ['user', 'system'];

		var index = types.indexOf(comment_type);
		if (index == -1) {
			comment_type = 'system';
		}

		var ajaxDate = {
			type: 'comment',
			action: 'add',
			content: content,
			card_id: self.id(),
			// board_id: kanban.app.current_board_id(),
			comment_type: comment_type
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			var commentId = response.data.id;
			var commentRecord = response.data;
			var comment = kanban.comments[commentId] = new Comment(commentRecord);

			if ( comment_type == 'user' ) {
				self.commentUpdateCount(1);
			}

			if ($('#card-modal-comments-list').length > 0) {
				$('#card-modal-comments-placeholder').remove();
				var commentHtml = comment.render();
				$(commentHtml).appendTo('#card-modal-comments-list');
				self.modal.commentScrollBottom();
			}
		});

	}; // commentAdd

	this.toggleHiddenFields = function (id) {
		$('#' + id).toggle();
	}; // toggleHiddenFields

	this.lane = function () {
		var self = this;

		return kanban.lanes[_self.record.lane_id];
	}; // lane

	this.board = function () {
		var self = this;

		var lane = self.lane();
		return kanban.boards[lane.board_id()];
	}; // board

	this.copy = function (el) {
		var self = this;

		if (!kanban.app.current_user().hasCap('card-create')) {
			return false;
		}

		var $btn = $(el);

		$btn.addClass('loading');

		var ajaxDate = {
			type: 'card',
			action: 'copy',
			card_id: self.id()
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			// Since it's a successful request, update the last time we checked.
			kanban.app.updates().lastCheck = new Date().getTime();

			kanban.app.processNewData(response.data, true);

			$btn.removeClass('loading');
		})
		.always(function () {
			$btn.removeClass('loading');
		});
	}; // copy

	this.move = function () {

	}; // move

	this.delete = function (el) {
		var self = this;

		if (!kanban.app.current_user().hasCap('card-create')) {
			return false;
		}

		if (self.board().record().options.card_creator_delete_card === "true" && Number(self.record().created_user_id) != Number(kanban.app.current_user().id())) {
			alert('Only user who created this card is allowed to delete it');
			return false;
		}


		var $btn = $(el).addClass('loading');

		var cardId = self.id();
		var ajaxDate = {
			type: 'card',
			action: 'delete',
			card_id: cardId
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( response.success == true ) {
				self.commentAdd(kanban.templates['card-comment-deleted'].render());
			}

			return self.remove();
		});
	}; // delete

	this.remove = function () {

		var self = this;

		// Just in case.
		self.modal.close();

		var lane = self.lane();
		if ('undefined' !== typeof kanban.cards[self.id()]) {

			// remove card and rerender lane
			delete kanban.cards[self.id()];
		}

		//remove deleted card from lane's card_order
		lane.cardOrderRemove([self.id()]);

		return lane.rerender();
	};

	this.$el = function () {
		var self = this;
		return $('#card-' + self.id());
	}; // $el

}; // Card


module.exports = Card