var timeago = require('timeago.js')

Object.size = function(obj) {
	var size = 0, key;
	for (key in obj) {
		if (obj.hasOwnProperty(key)) size++;
	}
	return size;
};

String.prototype.sprintf = function () {
	var formatted = this;
	for (var arg in arguments) {
		formatted = formatted.replace("{" + arg + "}", arguments[arg]);
	}
	return formatted;
};


String.prototype.formatForDb = function () {

	return $.trim(sanitizeString(this));

}; // formatForDb

String.prototype.formatForApp = function () {

	// Encode urls, and add a space after for being able to put a cursor after a last link.
	return encode_urls(encode_emails(this)) + ' ';

}; // formatForApp

// Number.prototype.unique = function () {
// 	return new Date().valueOf() + (Math.floor((1 + Math.random()) * 0x10000));
// };

// Array.prototype.unique = function() {
// 	var unique = [];
// 	for (var i = 0; i < this.length; i++) {
// 		if (unique.indexOf(this[i]) === -1) {
// 			unique.push(this[i]);
// 		}
// 	}
// 	return unique;
// };

// DON"T USE - adds "item" (this function) to every array.
// Array.remove = function() {
// 	var what, a = arguments, L = a.length, ax;
// 	while (L && this.length) {
// 		what = a[--L];
// 		while ((ax = this.indexOf(what)) !== -1) {
// 			this.splice(ax, 1);
// 		}
// 	}
// 	return this;
// };

Date.prototype.dateJsToMysql = function (dateObj, format) {
	switch (format) {
		case 'date':
			return dateObj.toISOString().substring(0, 10);
			break;
		default:
			return dateObj.toISOString().substring(0, 19).replace('T', ' ');
	}

};

Date.prototype.dateMysqlToJs = function (dateStr) {

	if ( 'undefined' === typeof dateStr || dateStr.length == 0 ) {
		return false;
	}

	var dateArr = dateStr.split('-');

	//convert date string to UTC date
	var d = new Date(Date.UTC(dateArr[0], dateArr[1] - 1, dateArr[2], 0, 0, 0));

	// Test for invalid date obj.
	if ( d instanceof Date && !isNaN(d) ) {
		return d;
	}

	return false;
};

Date.prototype.parseFormat = function (format) {
	var validParts = /dd?|DD?|mm?|MM?|yy(?:yy)?/g;

	// IE treats \0 as a string end in inputs (truncating the value),
	// so it's a bad format delimiter, anyway
	var separators = format.replace(validParts, '\0').split('\0'),
		parts = format.match(validParts);
	if (!separators || !separators.length || !parts || parts.length === 0) {
		throw new Error("Invalid date format.");
	}
	return {separators: separators, parts: parts};
};

// Copied from Bootstrap Datepicker.
Date.prototype.formatDate = function (date, format, language) {

	if ('undefined' === typeof language) {
		language = 'en';
	}

	if ('undefined' === typeof format) {
		format = 'mm/dd/yyyy';
	}

	if (typeof format === 'string') {
		format = Date.prototype.parseFormat(format);
	}

	var dates = $.fn.datepicker.dates;

	var val = {
		d: date.getUTCDate(),
		D: dates[language].daysShort[date.getUTCDay()],
		DD: dates[language].days[date.getUTCDay()],
		m: date.getUTCMonth() + 1,
		M: dates[language].monthsShort[date.getUTCMonth()],
		MM: dates[language].months[date.getUTCMonth()],
		yy: date.getUTCFullYear().toString().substring(2),
		yyyy: date.getUTCFullYear()
	};

	val.dd = (val.d < 10 ? '0' : '') + val.d;
	val.mm = (val.m < 10 ? '0' : '') + val.m;
	date = [];

	var seps = $.extend([], format.separators);

	for (var i = 0, cnt = format.parts.length; i <= cnt; i++) {
		if (seps.length)
			date.push(seps.shift());

		date.push(val[format.parts[i]]);
	}
	return date.join('');
};

//format the timeago text for date fields, using day level and replacing just now text with today
Date.prototype.getDateTimeago = function () {
	var todayUTC = new Date();
	todayUTC.setUTCHours(0, 0, 0, 0);

	// Set timeago to today utc
	var timeagoInstance = timeago(todayUTC);

	// return timeago format for the date field
	return timeagoInstance.format(this).replace("just now", "today");
};


// "Replace parent `p' with its children.";
function usurp(p) {
	var last = p;
	for (var i = p.childNodes.length - 1; i >= 0; i--) {
		var e = p.removeChild(p.childNodes[i]);
		p.parentNode.insertBefore(e, last);
		last = e;
	}
	p.parentNode.removeChild(p);
};

// "Remove all tags from element `el' that aren't in the ALLOWED_TAGS list."
function sanitize(el) {
	var ALLOWED_TAGS = ["STRONG", "EM", "B", "I", "BR", "P", "IMG", "INPUT"];

	var tags = Array.prototype.slice.apply(el.getElementsByTagName("*"), [0]);

	for (var i = 0; i < tags.length; i++) {
		if (ALLOWED_TAGS.indexOf(tags[i].nodeName) == -1) {
			usurp(tags[i]);
		}
	}

	return el;
}

function sanitizeString(string) {
	// Replace nbsp with spaces.
	// string = string.replace(/&nbsp;/g, ' ');

	// Add br's before divs before we remove them, just in case.
	// string = string.replace(/<div/gi, '<br><div');

	// Remove <br> tags from beginning and end.
	// string = string.replace(/(^<br\s*\/?>+|<br\s*\/?>+$)/g, '');

	var div = document.createElement("div");
	div.innerHTML = string;
	sanitize(div);
	string = div.innerHTML;

	return string;
}


function encode_emails(str) {
	if (typeof str === 'undefined' || str === '' || str === null) {
		return str;
	}

	var urlRegex = /([a-zA-Z0-9+._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9]+)/gi;

	return $.trim(
		' ' + str.replace(
		urlRegex,
		function (ignore, url) {
			url = $.trim(url);
			return '<a href="mailto:' + url + '" contenteditable="false" target="_blank">' + url + '</a>';
		}
		) // replace
	); // trim
}

function encode_urls(str) {
	if (typeof str === 'undefined' || str === '' || str === null) {
		return str;
	}

	var urlRegex = /[^"']((http|ftp)s?:\/\/[^\s<>"']+)/gi;

	// Add a space before in case string starts with a url.
	return $.trim(
		(' ' + str).replace(
			urlRegex,
			function (ignore, url) {
				var toReturn = '';

				var first = arguments[0].substring(0, 1);

				var chars = ['"', "'"];

				if (chars.indexOf(first) == -1) {
					toReturn += first;
				}

				toReturn += '<a href="' + arguments[1] + '" contenteditable="false" target="_blank">' + arguments[1] + '</a>';

				return toReturn;
			}
		) // replace
	); // trim
}

module.exports = {
	clone: function (v) {
		if ( 'undefined' === typeof v ) {
			return v;
		}

		var c = v.constructor.toString();

		if (c.indexOf("Array") !== -1) {
			return this.cloneArray(v);
		}

		if (c.indexOf("Object") !== -1) {
			return this.cloneObject(v);
		}

		if (c.indexOf("Number") !== -1) {
			return this.cloneNumber(v);
		}

		if (c.indexOf("String") !== -1) {
			return this.cloneString(v);
		}
	},

	cloneArray: function (v) {
		return v.slice();
	},

	cloneObject: function (v) {
		return $.extend(true, {}, v);
	},

	cloneNumber: function (v) {
		return v - 0;
	},

	cloneString: function (v) {
		return v + '';
	},

	optionsFormat: function (options) {
		for (var key in options) {
			var val = options[key];
			options[key + '-' + (val + '').replace(/[^A-Z0-9]+/ig, "_")] = true;
		}

		return options;
	}
};

module.exports