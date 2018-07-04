// var Card = require('./Card')
var timeago = require('timeago.js')
var functions = require('./functions')
// require('at.js')
// var Dropzone = require('dropzone')
// var MediumEditor = require('medium-editor')

function Comment(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['content'];

	// Add the comment to the parent card, just in case.
	if ( 'undefined' !== typeof kanban.cards[_self.record.card_id] ) {
		var card = kanban.cards[_self.record.card_id];
		card.commentAddToRecord(_self.record.id);
	}

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.card_id = function () {
		return _self.record.card_id;
	}; // id

	this.allowedFields = function () {
		return _self.allowedFields.slice();
	}; // allowedFields

	this.render = function (isEditing) {

		var self = this;

		isEditing === true ? true : false;

		var commentRecord = self.record();

		var card = kanban.cards[commentRecord.card_id];

		if ( 'undefined' === typeof card ) {
			return false;
		}

		// Get now in mysql format.
		var nowUTC = Date.prototype.dateJsToMysql(new Date());

		// Set timeago to now utc, since all dates in db are utc.
		var timeagoInstance = timeago(nowUTC);

		// Format created.
		commentRecord.created_dt = timeagoInstance.format(commentRecord.created_dt_gmt);

		// FOrmat modified, if different.
		if ( commentRecord.created_dt_gmt != commentRecord.modified_dt_gmt ) {
			commentRecord.modified_dt = timeagoInstance.format(commentRecord.modified_dt_gmt);
		}

		var isAuthor = commentRecord.created_user_id == kanban.app.current_user_id();

		if ( !isAuthor ) {
			isEditing = false;
		}

		if ( !isEditing ) {
			commentRecord.content = commentRecord.content.formatForApp();
		}

		return kanban.templates['card-modal-comment'].render({
			cardId: self.card_id(),
			comment: commentRecord,
			isAuthor: commentRecord.created_user_id == kanban.app.current_user_id(),
			author: kanban.users[commentRecord.created_user_id].record(),
			isEditing: isEditing,
			isSystem: commentRecord.comment_type == 'system' ? true : false
		});
	}; // render

	this.rerenderEditable = function (el) {

		var self = this;

		var commentRecord = self.record();

		if ( commentRecord.created_user_id != kanban.app.current_user_id() ) {
			return false;
		}

		var commentHtml = self.render(true);

		var $comment = $(el).closest('.comment').replaceWith(commentHtml);

		self.addFunctionality();

		return true;

	}; // rerenderEditable

	this.rerenderNotEditable = function (el) {

		var self = this;

		self.$el().removeClass('is-editing');

		self.rerender();

	}; // rerenderNotEditable

	this.rerender = function (el) {
		// console.log('comment rerender');
		var self = this;

		var $el = self.$el();

		// If the comment doesn't exist in the dom, try rerendering all comments.
		if ( $el.length == 0 ) {
			return kanban.cards[self.card_id()].modal.commentRerenderAll();
		}

		if ( $el.hasClass('is-editing') ) {
			return false;
		}

		var commentHtml = self.render();

		$el.replaceWith(commentHtml);

		// Reget the el, since we replaced it.
		var $el = self.$el();
		self.addFunctionality($el);

	}; // rerender

	this.addFunctionality = function() {
		var self = this;

		var $el = self.$el();

		kanban.app.prepareContenteditable($el);

		$el.one(
			'mouseover',
			function () {
				$('.attachment', $el).on(
					'click',
					function () {
						var href = $(this).attr('data-href');

						window.open(href);

						return false;
					}
				);
			}
		);


	}; // addFunctionality

	this.replace = function (data) {
		var self = this;

		if (!kanban.app.current_user().hasCap('comment-write')) {
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
			type: 'comment',
			action: 'replace',
			board_id: kanban.app.current_board_id(),
			comment_id: self.id()
		};

		// Only send the data that was updated.
		ajaxDate = $.extend(data, ajaxDate);

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ) {
				kanban.app.notify(kanban.strings.comment.updated_error);
				return false;
			}

			// @todo replace comment record?
			_self.record = response.data;

			self.rerenderNotEditable();
		});

	}; // replace

	this.update = function (el) {
		var self = this;

		var $el = $(el);
		var $comment = $el.closest('.comment');

		var $input = $('.card-modal-comment-input', $comment );
		var content = $input.html();
		content = content.formatForDb();

		if ( '' == content ) {
			return false;
		}

		$comment.addClass('loading');
		$input.attr('contenteditable', false);

		var $btn = $('button', $comment).prop('disabled', true);

		self.replace({
			content: content
		});

	}; // commentAdd

	this.delete = function () {
		var self = this;

		var confirmed = confirm('Are you sure you want to delete this comment?');

		if (confirmed) {

			var commentRecord = self.record();

			if (commentRecord.created_user_id != kanban.app.current_user_id()) {
				return false;
			}

			$.ajax({
				data: {
					type: 'comment',
					action: 'delete',
					comment_id: self.id()
				}
			})
			.done(function (response) {

				self.remove();

			});
		};

	}; // delete

	this.remove = function () {
		// console.log('comment remove');
		var self = this;

		var $el = self.$el();

		if ( $el.hasClass('is-editing') ) {
			return false;
		}

		$el.slideUp('fast', function () {
			$el.remove();

			delete kanban.comments[self.id()];

			kanban.cards[self.card_id()].commentUpdateCount(-1);
		});
	}; // remove

	this.$el = function () {
		var self = this;
		return $('.comment-' + self.id());
	}; // $el

}; // comment




module.exports = Comment