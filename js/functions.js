// @link http://stackoverflow.com/a/3492815/38241
String.prototype.sprintf = function()
{
    var formatted = this;
    for( var arg in arguments ) {
        formatted = formatted.replace("{" + arg + "}", arguments[arg]);
    }
    return formatted;
};

// @link http://stackoverflow.com/a/6700/38241
// Object.size = function(obj) {
//     var size = 0, key;
//     for (key in obj) {
//         if (obj.hasOwnProperty(key)) size++;
//     }
//     return size;
// };


// @link http://phpjs.org/functions/stripslashes/
String.prototype.stripslashes = function()
{
	return (this + '')
    .replace(/\\(.?)/g, function(s, n1)
    {
      switch (n1) {
        case '\\':
          return '\\';
        case '0':
          return '\u0000';
        case '':
          return '';
        default:
          return n1;
      }
    });
};

// @link http://stackoverflow.com/a/3605602/38241
Number.prototype.padZero = function(len)
{
 var s= String(this), c= '0';
 len= len || 2;
 while(s.length < len) s= c + s;
 return s;
}

function mysql_dt_to_js_date (dt)
{
	var t = dt.split(/[- :]/);
	return new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
}

function js_date_to_mysql_dt (dt)
{
	return dt.getUTCFullYear() + "-" + (1 + dt.getUTCMonth()).padZero(2) + "-" + (dt.getUTCDate()).padZero(2) + " " + (dt.getUTCHours()).padZero(2) + ":" + (dt.getUTCMinutes()).padZero(2) + ":" + (dt.getUTCSeconds()).padZero(2);
};

function get_screen_size ()
{
	return $('#screen-size div:visible:first').attr('data-size');
}

function on_window_resize ()
{
	window_w = $('body').width();
	window_h = $('body').height();
	screen_size = get_screen_size();

	all_match_col_h ();
}


function cookie_views ()
{
	var class_str = $('body').attr('class');
	var class_arr = class_str.split(' ');
	var view_classes = [];

	for ( var i in class_arr )
	{
		if ( class_arr[i].startsWith('board-view-') )
		{
			view_classes.push(class_arr[i]);
		}
	}

	Cookies.set('view', view_classes.join(' '));
}

function all_match_col_h ()
{
	for ( var board_id in boards )
	{
		var board = boards[board_id];
		board.match_col_h();
	}
}

function build_url ()
{
	var url_current = window.location.href;
	var url_arr = url_current.split('?');
	var url = url_arr[0] + '?' + decodeURIComponent($.param(url_params));
	return url;
}

function update_url ()
{
	var url = build_url ();
	window.history.replaceState('', '', url);

	update_page_title();
}

function update_page_title ()
{
	if ( typeof url_params['board_id'] !== 'undefined' )
	{
		var board = boards[url_params['board_id']];

		if ( typeof board === 'undefined' )
		{
			return false;
		}

		document.title = '{0} | {1}'.sprintf(board.record.title(), text.kanban);
	}

}


// @link http://stackoverflow.com/a/11892228/38241
function usurp (p)
{
	var last = p;
	for (var i = p.childNodes.length - 1; i >= 0; i--)
	{
		var e = p.removeChild(p.childNodes[i]);
		p.parentNode.insertBefore(e, last);
		last = e;
	}
	p.parentNode.removeChild(p);
}



function strip_tags(el, allowed_tags)
{
	if (  typeof allowed_tags === 'undefined')
	{
		allowed_tag = ["B", "I", "STRONG", "EM", "BR"];
	}

	$('*', el).each(function()
	{
		if (allowed_tag.indexOf(this.nodeName) == -1)
		{
			usurp(this);
		}
	});
}

function remove_attributes_from_tags (el)
{
	$('*', el).each(function()
	{
		var attributes = this.attributes;
		var i = attributes.length;
		while( i-- ){
			this.removeAttributeNode(attributes[i]);
		}
	});

}


function sanitize($div)
{
	strip_tags($div);
	remove_attributes_from_tags ($div);
	// $div.html( $.trim( $div.html().replace(/&nbsp;/gi,' ') ));
}


// function sanitize_string(string)
// {
// 	var div = document.createElement("div");
// 	div.innerHTML = string;
// 	sanitize(div);
// 	remove_attributes_from_tags(div);
//
// 	return $.trim( div.innerHTML.replace(/&nbsp;/gi,' ') );
// }



function encode_emails (str)
{
	if ( typeof str === 'undefined' )
	{
		return str;
	}

	if ( str == '' )
	{
		return str;
	}

	var rex = /(<a href(?:(?!<\/a\s*>).)*)?([\w.-]+@[\w.-]+\.[\w.-]+)/gi;

	return str.replace(
		rex, 
		function ( $0, $1 )
		{
			return $1 ? $0 : '<a href="mailto:' + $0 + '"  contenteditable="false">' + $0 + '</a>';
		}
	);
}

function encode_urls (str)
{
	if ( typeof str === 'undefined' )
	{
		return str;
	}

	if ( str == '' )
	{
		return str;
	}

	var rex = /(<a href(?:(?!<\/a\s*>).)*)?(http[^\s\<]+)/gi;

	return str.replace(
		rex, 
		function ( $0, $1 )
		{
			$0 = $0.replace('&nbsp;', '');
			return $1 ? $0 : '<a href="' + $.trim($0) + '"  contenteditable="false" target="_blank">' + $.trim($0) + '</a>';
		}
	);
}

function encode_urls_emails ($div)
{
	$div.html( encode_emails($div.html()) );
	$div.html( encode_urls($div.html()) );
}



// http://stackoverflow.com/a/4238971
function placeCaretAtEnd(el) {
	el.focus();
	if (typeof window.getSelection != "undefined"
		&& typeof document.createRange != "undefined") {
		var range = document.createRange();
		range.selectNodeContents(el);
		range.collapse(false);
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
	} else if (typeof document.body.createTextRange != "undefined") {
		var textRange = document.body.createTextRange();
		textRange.moveToElementText(el);
		textRange.collapse(false);
		textRange.select();
	}
}



function growl_response_message (response)
{
	try
	{
		growl(response.data.message);
	}
	catch (err) {}
}



function growl (message)
{
	if ( typeof message === 'undefined' )
	{
		return;
	}

	if ( message == '' )
	{
		return;
	}

	// https://github.com/ifightcrime/bootstrap-growl/
	$.bootstrapGrowl(
		message,
		{
			type: 'info',
				allow_dismiss: true
		}
	);
}




// friendly format hours
// white space matters!
function format_hours (h)
{
	if ( h <= 0 )
	{
		return '0 <sub>h</sub> ';
	}

    var min = Math.round(h*60);

    var days = Math.floor(min / (60  * 8));

    var divisor_for_hours = min % (60  * 8);
    var hours = Math.floor(divisor_for_hours / 60);

    var divisor_for_minutes = divisor_for_hours % (60 );
    var minutes = Math.floor(divisor_for_minutes );



	var to_return = '';

	if ( days > 0 )
	{
		to_return += '{0} <sub>d</sub> '.sprintf(days);
	}

	if ( hours > 0 )
	{
		to_return += '{0} <sub>h</sub> '.sprintf(hours);
	}

	if ( minutes > 0 )
	{
		to_return += '{0} <sub>m</sub> '.sprintf(minutes);
	}

	if ( to_return === '' )
	{
		to_return = format_hours(0);
	}

	return to_return;
}


function obj_order_by_key(obj, reverse)
{
	if ( typeof reverse === 'undefined')
	{
		reverse = false;
	}

	var arr = Object.keys(obj);
	arr.sort(function(a, b)
	{
		a = parseInt(a);
		b = parseInt(b);
	    if(a < b) return -1;
	    if(a > b) return 1;
	    return 0;
	});

	var re = [];
	for ( var i in  arr )
	{
		re[arr[i]] = obj[arr[i]];
	}

	if ( reverse )
	{
		re.reverse();
	}

	return re;
}



/**
 * sort objects that have a position property by their position
 * @param  {object} obj the objects we want to sort
 * @return {array}     an array of the objects, sorted by position
 */
function obj_order_by_prop (obj, prop, reverse)
{
	if ( typeof reverse === 'undefined')
	{
		reverse = false;
	}

	var obj_arr = $.map(obj, function(value, index) {
		return [value];
	});

	obj_arr.sort(function(a, b)
	{
		return a[prop] - b[prop];
	});

	if ( reverse )
	{
		obj_arr.reverse();
	}

	return obj_arr;
}



