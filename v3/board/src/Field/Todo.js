var Field = require('../Field')

// using prototypal inheritance: https://stackoverflow.com/questions/15192722/javascript-extending-class
// To do: refactor to ES6 `class Field_Text extends Field` as prototype syntax is more verbose

function Field_Todo(record) {

	Field.call(this, record);

	this._self.options = $.extend(this._self.options, {
	});

	this.render = function (fieldvalue, card) {
		var self = this;

		if ( 'undefined' === typeof fieldvalue ) {
			fieldvalue = {};
		}

		if ( 'undefined' === typeof card ) {
			return false;
		}

		var fieldvalueRecord = 'undefined' === typeof fieldvalue.record ? {} : fieldvalue.record();

		if ( 'undefined' === typeof fieldvalueRecord.content ) {
			fieldvalueRecord.content = [];
		}

		var todosHtml = "";
		for(var i = 0; i < fieldvalueRecord.content.length; i++) {
			var todoItem  = fieldvalueRecord.content[i];

			//string boolean values coming from backend, JSON.parse will convert it to true boolean
			todoItem.is_checked = JSON.parse(todoItem.is_checked);

			todoItem.index = i + 1;
			todosHtml += kanban.templates['field-todo-task'].render({
				todo: todoItem,
				field: self.record(),
				card_id: card.id()
			});
		}

		return kanban.templates['field-todo'].render({
			field: self.record(),
			fieldvalue: fieldvalueRecord,
			card: 'undefined' === typeof card.record ? {} : card.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write'),
			todosHtml: todosHtml
		});
	}; // render

	this.addFunctionality = function ($field) {
		var self = this;

		if ( $field.hasClass('func-added') ) {
			return false;
		}

		$field.on(
			'mouseover',
			function () {

				// We don't need a placeholder, so it can be late added.
				kanban.app.prepareContenteditable($field);

				$('.attachment', $field)
				.on(
					'click',
					function () {
						var href = $(this).attr('data-href');

						window.open(href);

						return false;
					}
				);
			}
		);

		$field.addClass('func-added');

	}; // addFunctionality

	this.onCheck = function (el, e) {
		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field');
		var content = self.getValue($field);

		self.updateValue($field, content);

	}; // onCheck

	this.onBlur = function (el, e) {

		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field').removeClass('is-editing');
		var $card = $el.closest('.card').removeClass('is-editing');
		var $lane = $field.closest('.lane').removeClass('is-editing');
		var content = self.getValue($field);

		self.updateValue($field, content);

	}; // onBlur

	this.onFocus = function (el) {

		var self = this;

		var $el = $(el);
		var $field = $el.closest('.field').addClass('is-editing');
		var $card = $el.closest('.card').addClass('is-editing');
		var $lane = $field.closest('.lane').addClass('is-editing');

		// Save the current value for restoring.
		var value = self.getValue($field);
		$el.data('prevValue', value);

		// Unlink links.
		$('a', $el).each(function () {
			var $this = $(this);
			$this.replaceWith($this.text());
		});

	}; // onFocus

	this.onKeydown = function (el, e) {
		// console.log('Field_Title.onKeydown');
		var self = this;

		switch (e.keyCode) {
			case 13: // enter

				// If not shift + enter, save it.
				if ( !e.shiftKey ) {
					el.blur();
					return false;
				}

				// If just enter, save it.
				break;

			case 27: // escape

				var prevValue = $(el).data('prevValue');
				var $contenteditable = $(el);
				$contenteditable.html( prevValue );

				el.blur();

				break;
		}
	}; // onKeydown

	this.getValue = function ($field) {
		// console.log('getValue');

		var self = this;

		var todos = [];
		$field.find('.list-group-item').not('.add-new-todo').each(function(){
			var $task = $(this);
			var todoText = $('.task-content', $task).html();
			if (todoText != "") {
				todos.push({
					is_checked: $('.task-checkbox', $task).get(0).checked,
					content: $.trim(todoText)
				});
			}
		});

		var $newField = $field.find('.list-group-item.add-new-todo');
		var todoText = $('.task-content', $newField).html();

		if (todoText != "") {
			todos.push({
				is_checked: false,
				content: $.trim(todoText)
			});
		}

		return todos;
	}; // getValue

	this.formatContentForComment = function (content) {
		var self = this;
		
		var contentArr = [];
		for(var i = 0; i < content.length; i++) {
			contentArr.push(content[i].content);
		}

		return contentArr.join(', ');
	}; // formatContentForComment

	this.applyFilter = function(fieldValue, filterElement) {
		var fieldContent = fieldValue.content();
		var filterContent = filterElement.value.split(',');
				
		switch (filterElement.operator) {
			//Operator: includes
			case "6":
				for (filterTodo of filterContent) {
					var found = false;
					filterTodo = $.trim(filterTodo);
					for(fieldTodo of fieldContent) {
						if ($.trim($(fieldTodo.content).text()) == filterTodo) {
							found = true;
							break;
						}
					}
					if (!found) {
						return false;
					}
				}
				return true;
				
			//Operator: does not include
			case "7":
				for (filterTodo of filterContent) {
					var found = false;
					filterTodo = $.trim(filterTodo);
					for(fieldTodo of fieldContent) {
						if ($.trim($(fieldTodo.content).text()) == filterTodo) {
							found = true;
							break;
						}
					}
					if (found) {
						return false;
					}
				}
				return true;
		}
	}

} // Field_Todo

// inherit Field
Field_Todo.prototype = Object.create(Field.prototype);

Field_Todo.prototype.constructor = Field_Todo;

module.exports = Field_Todo