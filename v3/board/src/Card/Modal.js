var Comment = require('../Comment')


Card_Modal = function (card) {

	var _self = {};
	_self.card = card;

	this.card = function () {
		return _self.card;
	}; // card

	this.$el = function () {
		var self = this;
		return $('#card-modal-' + self.id());
	}; // $el

	this.show = function (el, tab) {

		var self = this;

		var fieldHtml = '';
		var boardRecord = self.card().board().record();

		for (var i in boardRecord.fields_order) {

			var field_id = boardRecord.fields_order[i];

			if ('undefined' === typeof kanban.fields[field_id]) {
				continue;
			}

			// Get field record from orig data.
			var field = kanban.fields[field_id];

			var fieldvalue = {};
			if ('undefined' !== typeof self.card().fieldvaluesByField()[field_id]) {
				fieldvalue = kanban.fieldvalues[self.card().fieldvaluesByField()[field_id]];
			}

			fieldHtml += field.render(fieldvalue, self.card());
		}

		var commentFormHtml = kanban.templates['card-modal-comment'].render({
			cardId: self.card().id(),
			isAuthor: false,
			author: kanban.app.current_user().record(),
			isEditing: true,
			isForm: true
		});

		var lanesSelectorHtml = '';
		for (var i in boardRecord.lanes_order) {
			var laneId = boardRecord.lanes_order[i];

			if ('undefined' === typeof kanban.lanes[laneId]) {				
				continue;
			}

			var lane = kanban.lanes[laneId];
			var isActive = self.card().lane().id() == lane.id() ? " active" : "";
			lanesSelectorHtml += '<button type="button" onclick=kanban.cards[' + self.card().id() + '].modal.moveToLane(this) class="btn btn-default' + isActive + '" data-lane-id="' + lane.id() + '">' + lane.label() + '</button>';
		}

		var modalHtml = kanban.templates['card-modal'].render({
			fields: fieldHtml,
			commentForm: commentFormHtml,
			card: self.card().record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write'),
			currentUser: kanban.app.current_user().record(),
			lanesSelector: lanesSelectorHtml
		});

		$('#modal').html(modalHtml);

		$('#modal').modal({
			backdrop: 'static',
			keyboard: false,
			show: true
		});

		kanban.app.prepareContenteditable($('#card-modal-comment-form'));


		// Get all comments.
		if ( self.card().isCommentsLoaded() ) {
			self.commentsRerender();
		} else {

			$.ajax({
				data: {
					type: 'comment',
					action: 'get_by_card',
					card_id: self.card().id()
				}
			})
				.done(function (response) {

					if ( 'undefined' === typeof response.data ) {
						kanban.app.notify(kanban.strings.comment.retrieve_error);
						return false;
					}

					for (var commentId in response.data) {

						var commentRecord = response.data[commentId];
						var comment = kanban.comments[commentId] = new Comment(commentRecord);
					}

					self.commentsRerender();
					self.card().setCommentsLoaded();

				}); // done
		}


		// Attempt to reload tab
		if ('undefined' !== typeof tab && '' != tab) {
			$('#modal-tab-' + tab).trigger('click');
		}
		else if ('undefined' !== typeof kanban.app.url().params['tab']) {
			$('#modal-tab-' + kanban.app.url().params['tab']).trigger('click');
		}


		// Update the url.
		kanban.app.urlParamUpdate('modal', 'card');
		kanban.app.urlParamUpdate('card', self.card().id());
		kanban.app.urlReplace();

		return false;
	}; // modalShow

	this.commentsRerender = function () {
		var self = this;

		var commentHtml = '';
		var cardRecord = self.card().record();

		// Order comments newest last.
		cardRecord.comments = cardRecord.comments.sort(function (a, b) {
			return a - b;
		});

		for (var i in cardRecord.comments) {

			var comment_id = cardRecord.comments[i];

			if ('undefined' === typeof kanban.comments[comment_id]) {
				continue;
			}

			// Get field record from orig data.
			var comment = kanban.comments[comment_id];

			commentHtml += comment.render();
		}

		if ('' == commentHtml) {
			commentHtml = kanban.templates['card-modal-comment-placeholder'].render();
		}

		$('#card-modal-comments-list').html(commentHtml);

		for (var i in cardRecord.comments) {

			var comment_id = cardRecord.comments[i];

			if ('undefined' === typeof kanban.comments[comment_id]) {
				continue;
			}

			// Get field record from orig data.
			var comment = kanban.comments[comment_id];

			comment.addFunctionality();
		}

		self.commentScrollBottom();

	}; // commentsRerender

	/**
	 * Scroll comments to bottom
	 * @link https://stackoverflow.com/a/18614545/38241
	 */
	this.commentScrollBottom = function () {
		setTimeout(function () {
			var el = document.getElementById("card-modal-comments-list");
			el.scrollTop = el.scrollHeight;
		}, 100);
	}; // commentScrollBottom

	this.commentAdd = function (el) {
		var self = this;

		var $el = $(el);
		var $comment = $el.closest('.comment');

		var $input = $('.card-modal-comment-input', $comment);
		var content = $input.html();
		content = content.formatForDb();

		if ('' == content) {
			return false;
		}

		$comment.addClass('loading');
		$input.attr('contenteditable', false);

		var $btn = $('button', $comment).prop('disabled', true);

		self.card().commentAdd(content, 'user');

		setTimeout(function(){
			$input.attr('contenteditable', true).html('');
			$comment.removeClass('loading');
			$btn.prop('disabled', false);
		}, 500);

	}; // commentAdd


	// this.commentOnPaste = function (el, e) {
	// e.preventDefault();
	//
	// if (e.clipboardData) {
	// 	var content = (e.originalEvent || e).clipboardData.getData('text/plain');
	//
	// 	document.execCommand('insertText', false, content);
	// }
	// else if (window.clipboardData) {
	// 	var content = window.clipboardData.getData('Text');
	//
	// 	document.selection.createRange().pasteHTML(content);
	// }
	// }; // onPaste

	this.commentOnKeydown = function (el, e) {
	}; // commentOnKeydown

	// @todo insert new records instead of rerendering all
	this.commentRerenderAll = function () {
		// console.log('card modal commentRerenderAll');
		 var self = this;

		if ($('#card-modal').length == 0) {
			return false;
		}

		if ( $('#card-modal-comments-list .is-editing').length > 0 ) {
			return false;
		}

		var comments = self.card().comments();

		var commentHtml = '';

		for (var i in comments) {

			var comment_id = comments[i];

			if ('undefined' === typeof kanban.comments[comment_id]) {
				continue;
			}

			// Get field record from orig data.
			var comment = kanban.comments[comment_id];

			commentHtml += comment.render();
		}

		$('#card-modal-comments-list').html(commentHtml);

	}; // rerender

	this.close = function () {
		var self = this;

		kanban.app.urlParamRemove('card');
		kanban.app.modal.close();

	}; // modalClose

	this.moveToLane = function(el) {		
		//when clicking active lane nothing happens
		if ($(el).hasClass('active')) {
			return;
		}

		if (this.card().board().record().options.card_creator_move_card === "true" && Number(this.card().record().created_user_id) != Number(kanban.app.current_user().id())) {			
			alert('Only user who created this card is allowed to move it');			
			return false;
		}

		var cardId = this.card().id();
		var card = kanban.cards[cardId];

		var prevLaneId = this.card().laneId();
		var prevLane = kanban.lanes[this.card().laneId()];

		var newLaneId = $(el).attr('data-lane-id');
		var newLane = kanban.lanes[newLaneId];

		prevLane.cardOrderRemove([cardId]);
		newLane.cardOrderAdd(0, [cardId]);

		prevLane.rerender();
		newLane.rerender();

		card.replace({
			lane_id: newLaneId
		});

		var comment = kanban.templates['card-comment-moved-to-lane'].render({
			newLane: newLane.label(),
			prevLane: prevLane.label()
		});

		card.commentAdd(
			comment
		);

		//reload the modal
		this.show();
	}

}; // modal


module.exports = Card_Modal