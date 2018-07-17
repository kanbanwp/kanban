require('../functions.js')
var Field = require('../Field')

function Field_Date(record) {

	Field.call(this, record);

	this._self.options = $.extend(
		this._self.options,
		{
			show_datecount: false,
			is_date_range: true
		}
	);

	this.render = function (fieldvalue, card) {
		var self = this;

		if ('undefined' === typeof fieldvalue) {
			fieldvalue = {};
		}

		if ('undefined' === typeof card) {
			return false;
		}

		var fieldRecord = self.record();
		var fieldOptions = self.options();

		var userAppOptions = kanban.app.current_user().optionsApp();

		var fieldvalueRecord = {};
		if ( 'undefined' !== typeof fieldvalue.record ) {
			fieldvalueRecord = fieldvalue.record();

			var jsDate = Date.prototype.dateMysqlToJs(fieldvalue.content().start);

			if ( jsDate ) {
				fieldvalueRecord.content.start = Date.prototype.formatDate(jsDate, userAppOptions.date_view_format);
			}

			var jsDate = Date.prototype.dateMysqlToJs(fieldvalue.content().end);

			if ( jsDate ) {
				fieldvalueRecord.content.end = Date.prototype.formatDate(jsDate, userAppOptions.date_view_format);
			}
			
			if (fieldRecord.options.show_datecount == 1) {
				fieldRecord.timeago_dt_gmt = jsDate;
				fieldRecord.timeago_dt = fieldRecord.timeago_dt_gmt.getDateTimeago();				
			}			
		}

		// fieldRecord.options.showCount = fieldRecord.options.show_datecount == 1;

		return kanban.templates['field-date'].render({
			field: fieldRecord,
			fieldvalue: fieldvalueRecord,
			card: 'undefined' === typeof card.record ? {} : card.record(),
			isCardWrite: kanban.app.current_user().hasCap('card-write')
		});
	}; // render

	this.addFunctionality = function ($field) {
		var self = this;

		if ( $field.hasClass('func-added') ) {
			return false;
		}

		$field.one(
			'mouseover',
			function () {
				var userAppOptions = kanban.app.current_user().optionsApp();
				var weekStart = userAppOptions.first_day_of_week == "sunday" ? 0 : 1;

				$('.form-control', $field)
				.datepicker({
					weekStart: weekStart,
					todayHighlight: true,
					format: userAppOptions.date_view_format
				})
				.on('show', function(e) {
					var $el = $(this);
					var $field = $el.closest('.field').addClass('is-editing');
					var $card = $el.closest('.card').addClass('is-editing');
					var $lane = $field.closest('.lane').addClass('is-editing');
				})
				.on('hide', function(e) {
					var $el = $(this);
					var $field = $el.closest('.field').removeClass('is-editing');
					var $card = $el.closest('.card').removeClass('is-editing');
					var $lane = $field.closest('.lane').removeClass('is-editing');
				})
				.on('changeDate', function(e) {
				    var $el = $(this).datepicker('hide');
					self.updateValue($field);	
					
					//update timeago if visible
					if (self.record().options.show_datecount == 1) {						
						var dateValue = self.getValue($field);
						var jsDate = Date.prototype.dateMysqlToJs(dateValue);
						var timeagoFormatted = jsDate.getDateTimeago();	
						
						$timeago = $field.find('span.datetimeago');
						$timeago.attr("data-datetime", jsDate);
						$timeago.html(timeagoFormatted);						
					}
				});
			}
		);

		$field.addClass('func-added');

	}; // addFunctionality

	this.onChange = function (el) {

		// var self = this;
		// var $input = $(el); // .datepicker('hide');
		// self.updateValue($field);

	}; // onChange

	// this.optionsRender = function () {
	//
	// 	var self = this;
	//
	// 	var fieldRecord = self.record();
	//
	// 	if ( 'undefined' === typeof fieldRecord.options.format || '' == fieldRecord.options.format )
	// 	{
	// 		fieldRecord.options.format = 'mm/dd/yyyy';
	// 	}
	//
	// 	switch (fieldRecord.options.format) {
	// 		case 'mm/dd/yyyy':
	// 			fieldRecord.options['format-mmddyyyy'] = true;
	// 			break;
	// 		case 'dd/mm/yyyy':
	// 			fieldRecord.options['format-ddmmyyyy'] = true;
	// 			break;
	// 		case 'yyyy-mm-dd':
	// 			fieldRecord.options['format-yyyymmdd'] = true;
	// 			break;
	// 		case 'M d, yyyy':
	// 			fieldRecord.options['format-Mdyyyy'] = true;
	// 			break;
	// 	}
	//
	// 	if ( 'undefined' === typeof fieldRecord.options.show_datecount || '' == fieldRecord.options.show_datecount )
	// 	{
	// 		fieldRecord.options.show_datecount = '0';
	// 	}
	//
	// 	switch (fieldRecord.options.show_datecount) {
	// 		case '0':
	// 			fieldRecord.options['show_datecount'] = false;
	// 			break;
	// 		case '1':
	// 			fieldRecord.options['show_datecount'] = true;
	// 			break;
	// 	}
	//
	// 	return kanban.templates['board-modal-field-date'].render({
	// 		board: self.board().record(),
	// 		field: fieldRecord
	// 	});
	// }; // optionsRender

	this.getValue = function ($field) {
		// console.log('getValue');

		var self = this;

		var $inputs = $('.form-control', $field);

		var content = {
			start: '',
			end: ''
		};
		$inputs.each(function() {
			var $input = $(this);
			var dateName = $input.attr('data-name');

			// Use local (not gmt) to get actual date selected.
			content[dateName] = $input.datepicker('getUTCDate');
			if ( null !== content[dateName] ) {
				content[dateName] = Date.prototype.dateJsToMysql(content[dateName], 'date');
			}
		});

		// Convert to mysql date.
		return content;
	}; // getValue

	this.formatContentForComment = function (content) {
		var self = this;

		var userAppOptions = kanban.app.current_user().optionsApp();
		var fieldOptions = self.options();

		var formatted_content = '';

		var jsDate = Date.prototype.dateMysqlToJs(content.start);

		if ( jsDate ) {
			formatted_content += Date.prototype.formatDate(jsDate, userAppOptions.date_view_format);
		}

		if ( fieldOptions.is_date_range ) {
			formatted_content += ' -> ';

			var jsDate = Date.prototype.dateMysqlToJs(content.end);

			if (jsDate) {
				formatted_content += Date.prototype.formatDate(jsDate, userAppOptions.date_view_format);
			}
		}

		return formatted_content;
	}; // formatContentForComment

	this.applyFilter = function(fieldValue, filterElement) {		
		var jsDate = Date.prototype.dateMysqlToJs(fieldValue.content());
		var fieldDateValue = jsDate.getTime();
		var filterDateValue = filterElement.value.getTime();
		
		switch (filterElement.operator) {
			//Operator: =
			case "0":
				return fieldDateValue === filterDateValue;
			//Operator: !=
			case "1":
				return fieldDateValue !== filterDateValue;
			//Operator: <
			case "2":
				return fieldDateValue < filterDateValue;
			//Operator: <=
			case "3":
				return fieldDateValue <= filterDateValue;
			//Operator: >
			case "4":
				return fieldDateValue > filterDateValue;
			//Operator: >=
			case "5":
				return fieldDateValue >= filterDateValue;			
		}
	}
} // Field_Date

// inherit Field
Field_Date.prototype = Object.create(Field.prototype);

// correct the constructor pointer because it points to Field
Field_Date.prototype.constructor = Field_Date;

module.exports = Field_Date