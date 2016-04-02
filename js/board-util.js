// get templates
var t_col_status = new t($('#t-col-status').html());
var t_col_tasks = new t($('#t-col-tasks').html());
var t_card = new t($('#t-card').html());
var t_card_project = new t($('#t-card-project').html());
var t_card_user_assigned_to = new t($('#t-card-user-assigned-to').html());
var t_card_task_hours = new t($('#t-card-task-hours').html());
var t_card_estimate = new t($('#t-card-estimate').html());
var t_card_users_dropdown = new t($('#t-card-users-dropdown').html());
var t_card_estimates_dropdown = new t($('#t-card-estimates-dropdown').html());
var t_card_projects_dropdown = new t($('#t-card-projects-dropdown').html());
var t_modal_projects_panel = new t($('#t-modal-projects-panel').html());

var sidebar_w, $last_clicked;
var timers = [];



// @link http://stackoverflow.com/a/3492815/38241
String.prototype.sprintf = function()
{
    var formatted = this;
    for( var arg in arguments ) {
        formatted = formatted.replace("{" + arg + "}", arguments[arg]);
    }
    return formatted;
};


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

// @link http://stackoverflow.com/a/6700/38241
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};



function mysql_dt_to_js_date (dt)
{
	var t = dt.split(/[- :]/);
	return new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
}



// @link http://stackoverflow.com/a/11892228/38241
var ALLOWED_TAGS = ["STRONG", "EM", "BR"];

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



function sanitize(el)
{
	var tags = Array.prototype.slice.apply(el.getElementsByTagName("*"), [0]);
	for (var i = 0; i < tags.length; i++)
	{
		if (ALLOWED_TAGS.indexOf(tags[i].nodeName) == -1)
		{
			usurp(tags[i]);
		}
	}
}



function sanitizeString(string)
{
	var div = document.createElement("div");
	div.innerHTML = string;
	sanitize(div);
	return div.innerHTML;
}


function encode_emails (str)
{
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



function get_screen_size ()
{
	return $('#screen-size :visible').attr('data-size');
}



function obj_to_arr (obj)
{
	return $.map(obj, function(value, index) {
	    return [value];
	});
}



// friendly format hours
function format_hours (h)
{
	if ( h == 0 )
	{
		return '0<small>h</small>';
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
		to_return += '{0}<small>d</small>'.sprintf(days);
	}

	if ( hours > 0 )
	{
		to_return += '{0}<small>h</small>'.sprintf(hours);
	}

	if ( minutes > 0 )
	{
		to_return += '{0}<small>m</small>'.sprintf(minutes);
	}

	if ( to_return === '' )
	{
		to_return = format_hours(0);
	}

	return to_return;
}



function show_growl_msg (response)
{
	try
	{
		// https://github.com/ifightcrime/bootstrap-growl/
		$.bootstrapGrowl(
			response.data.message,
			{
				type: 'info',
  				allow_dismiss: true
			}
		);
	}
	catch (err) {}
}



function add_task_to_status_col (task)
{
	if ( typeof task.estimate_id === 'undefined' )
	{
		task.estimate_id = 0;
	}

	if ( typeof task.project_id === 'undefined' )
	{
		task.project_id = 0;
	}
	else if ( typeof board.project_records[task.project_id] === 'undefined' )
	{
		task.project_id = 0;
	}

	if ( typeof task.user_id_assigned === 'undefined' )
	{
		task.user_id_assigned = 0;
	}
	else if ( typeof board.allowed_users()[task.user_id_assigned] === 'undefined' )
	{
		task.user_id_assigned = 0;
	}

	if ( typeof task.status_id === 'undefined' )
	{
		task.status_id = 0;
	}

	if ( task.status_id == 0 )
	{
		if ( board.settings().show_all_cols == 0 )
		{
			var $col = $('#row-tasks .col-tasks:eq(1)');
		}
		else
		{
			var $col = $('#row-tasks .col-tasks:eq(0)');
		}

		var status_id = $col.attr('data-status-id');

		if ( typeof status_id !== 'undefined' && status_id != '' )
		{
			task.status_id = status_id;
		}
	}

	if ( typeof board.status_records()[task.status_id] !== 'undefined' )
	{
		task.status_color = board.status_records()[task.status_id].color_hex;
	}

	if ( typeof task.hour_count === 'undefined' )
	{
		task.hour_count = 0;
	}

	var $task = $(t_card.render({
		task: task,
		settings: board.settings(),
		current_user_can_write: current_user_has_cap('write')
	}));
	$task.prependTo('#status-{0}-tasks'.sprintf(task.status_id)).board_task(task);

	$('.col-tasks').matchHeight();

	update_task_count();

	return $task;
}



function update_task_count()
{
	$('.col-status').each(function()
	{
		var $col = $(this);
		var status_id = $col.attr('data-id');
		var count = $( '#status-{0}-tasks .task'.sprintf(status_id) ).length;

		$('.task-count', $col)
		.css({
			'visibility': ( count === 0 ? 'hidden' : 'visible')
		})
		.text(count);
	});
}



/**
 * test if current user has capability
 * @param  {string} cap the cap we're looking for
 * @return {bool}     if current user has cap or not
 */
function current_user_has_cap (cap)
{
	return user_has_cap(cap, board.current_user());
}



/**
 * test if a user has a capability
 * @param  {string} cap the cap we're looking for
 * @param  {object} user the user to test against
 * @return {bool}     if user has cap or not
 */
function user_has_cap (cap, user)
{
	try
	{
		return user.caps.indexOf(cap) < 0 ? false : true;
	}
	catch (err)
	{
		return false;
	}
}



/**
 * sort objects that have a position property by their position
 * @param  {object} obj the objects we want to sort
 * @return {array}     an array of the objects, sorted by position
 */
function records_by_position (obj)
{
	var obj_arr = $.map(obj, function(value, index) {
	    return [value];
	});

	obj_arr.sort(function(a, b)
	{
		return a.position - b.position;
	});

	return obj_arr;
}



