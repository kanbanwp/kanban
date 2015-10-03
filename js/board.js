/**
 * @link https://github.com/remy/bind.js
 */



// get templates
var t_col_status = new t($('#t-col-status').html());
var t_col_tasks = new t($('#t-col-tasks').html());
var t_task = new t($('#t-card').html());
var t_card_project = new t($('#t-card-project').html());
var t_card_user_assigned_to = new t($('#t-card-user-assigned-to').html());
var t_card_work_hours = new t($('#t-card-work-hours').html());
var t_card_estimate = new t($('#t-card-estimate').html());
var t_allowed_users = new t($('#t-dropdown-allowed-users').html());
var t_estimates = new t($('#t-dropdown-estimates').html());

var sidebar_w;



// @link http://stackoverflow.com/a/3492815/38241
String.prototype.sprintf = function() {
    var formatted = this;
    for( var arg in arguments ) {
        formatted = formatted.replace("{" + arg + "}", arguments[arg]);
    }
    return formatted;
};



var get_screen_size = function ()
{
	return $('#screen-size :visible').attr('data-size');
};


var obj_to_arr = function (obj)
{
	return $.map(obj, function(value, index) {
	    return [value];
	});
};


// friendly format hours
var format_hours = function (h)
{
	var to_return;
	if ( h < 8 )
	{
		to_return = h + 'h';
	}
	else
	{
		to_return = Math.floor(h/8) + 'd';

		var remainder = Math.floor(h % 8);

		if ( remainder > 0 )
		{
			to_return += ' ' + remainder + 'h';
		}
	}

	return to_return;
};



var show_growl_msg = function(response)
{
	try
	{
		// https://github.com/ifightcrime/bootstrap-growl/
		$.bootstrapGrowl(
			response.data.message,
			{
				type: 'info'
			}
		);
	}
	catch (err) {}
};



var add_task_to_status_col = function(task, status_id)
{
	try
	{
		task.status_color = status_colors[task.terms.kanban_task_status[0]];
	}
	catch (err) {}

	if ( typeof task.terms.kanban_task_estimate === 'undefined' )
	{
		task.terms.kanban_task_estimate = 0;
	}

	if ( typeof task.postmeta.kanban_task_user_id_assigned === 'undefined' )
	{
		task.postmeta.kanban_task_user_id_assigned = '';
	}

	var $task = $(t_task.render(task));
	$task.prependTo('#status-{0}-tasks'.sprintf(status_id)).board_task(task);

	$('.col-tasks').same_height();

	return $task;
};



jQuery(function($)
{
	$('#filter-wrapper').board_filter();
	$('#search-wrapper').board_search();
	$('#btn-group-view-compact').board_view();



	var project_records_array = obj_to_arr(project_records);

	projects = new Bind(
		{
			projects: project_records_array
		},
		{
			'projects': {
				dom: '#filter-projects-dropdown',
				transform: function (project) {
					return '<li><a href="#" data-id="' + project.ID + '">' + this.safe(project.post_title) + '</a></li>';
				}
			},
		}
	); // bind



	var allowed_users_array = obj_to_arr(allowed_users);

	users = new Bind(
		{
			users: allowed_users_array
		},
		{
			'users': {
				dom: '#filter-users-dropdown',
				transform: function (user) {
					return '<li><a href="#" data-id="' + user.data.ID + '">' + this.safe(user.data.long_name_email) + '</a></li>';
				}
			},
		}
	); // bind



	// var col_percent_w = 100/(status_records.length);
	// var sidebar_w = 100/(status_records.length-2);

	for ( var i in status_records )
	{
		var status = status_records[i];

		try
		{
			status.color = status_colors[status.term_id];
		}
		catch (err) {}

		if ( i == 0 )
		{
			status.left_open = 0;
			status.left_close = '-{0}'.sprintf(sidebar_w);
		}

		if ( i == status_records.length-1 )
		{
			status.left_open = '-{0}'.sprintf(sidebar_w*2);
			status.left_close = '-{0}'.sprintf(sidebar_w);
		}

		var $status = $(t_col_status.render(status));
		$status.appendTo('#row-statuses').board_sidebar_header();

		var $status = $(t_col_tasks.render(status));
		$status.appendTo('#row-tasks');

		statuses[status.term_id] = status;
	}



	for ( var i in task_records )
	{
		var task = task_records[i];

		if ( typeof task.terms.kanban_task_status[0] === 'undefined' || task.terms.kanban_task_status[0] == 0 || typeof statuses[task.terms.kanban_task_status[0]] === 'undefined' )
		{
			var status_id = $('#row-statuses .col').eq(1).attr('data-id');
			task.terms.kanban_task_status[0] = status_id;
		}

		add_task_to_status_col(task, task.terms.kanban_task_status[0]);
	}



	$('.col-tasks').sortable({
		connectWith: '.col-tasks',
		handle: ".task-handle",
		forcePlaceholderSize: true,
		forceHelperSize: true,
		// tolerance: "pointer",
		placeholder: "task-placeholder",
		over: function (e, ui)
		{
			ui.placeholder.closest('.col-tasks').addClass('hover');
		},
		out: function (e, ui)
		{
			ui.placeholder.closest('.col-tasks').removeClass('hover');
		},
		stop: function (e, ui)
		{
			$('.col-tasks.hover').removeClass('hover');
		},
		receive: function(e, ui)
		{
			var task_id = ui.item.attr('data-id');
			var $col = ui.item.closest('.col-tasks');
			var status_id_new = $col.attr('data-status-id');
			var status_color = $col.attr('data-color');
			var status_id_old = tasks[task_id].task.terms.kanban_task_status + '';

			ui.item.trigger('status_change', [status_id_old, status_id_new]);

			tasks[task_id].task.status_color = status_color;
			tasks[task_id].task.terms.kanban_task_status = [status_id_new]; // make sure there's only ever one

			ui.item.trigger(
				'add_comment',
				[
					'{0} moved the task to "{1}"'
					.sprintf(
						current_user.short_name,
						$('.status_name', $col).text()
					)
				]
			);

			$('.col-tasks').same_height();

		} // receive
	}).disableSelection();



	$("body").on(
		'keydown',
		function(e)
		{
			var $any_input = $('input:focus, textarea:focus');



			// left
			if( e.keyCode === 37 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					if ( $('.col-status:last').hasClass('is-open') )
					{
						$('.col-status:last').trigger('close');
					}
					else if ( !$('.col-status:first').hasClass('is-open') )
					{
						$('.col-status:first').trigger('open');
					}
				}
			}

			// right
			if( e.keyCode === 39 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					if ( $('.col-status:first').hasClass('is-open') )
					{
						$('.col-status:first').trigger('close');
					}
					else if ( !$('.col-status:last').hasClass('is-open') )
					{
						$('.col-status:last').trigger('open');
					}
				}
			}



			// toggle compact view
			if( e.keyCode === 9 && $any_input.length === 0 )
			{
				$('#btn-group-view-compact .btn:has(input:not(:checked))').trigger('click');
				return false;
			}



			// jump to search
			if( e.keyCode === 83 && $any_input.length === 0 )
			{
				$('#board-search').focus();
				return false;
			}
		}
	);



	var hash = [];
	if (window.location.hash)
	{
		var params = (window.location.hash.substr(1)).split("&");
		for ( var i in params )
		{
			var param = params[i].split("=");
			if ( typeof param[1] === 'undefined' )
			{
				continue;
			}
			hash[param[0]] = param[1];
		}
	}



	var is_filtered = false;
	if ( typeof hash.filter_project !== 'undefined' )
	{
		$('#filter-projects-dropdown [data-id=' + hash.filter_project + ']').click();
		is_filtered = true;
	}

	if ( typeof hash.filter_user !== 'undefined' )
	{
		$('#filter-users-dropdown [data-id=' + hash.filter_user + ']').click();
		is_filtered = true;
	}

	if ( is_filtered )
	{
		$('#btn-filter-apply').trigger('click');
	}



	$('.col-tasks')
	.on(
		'mouseenter',
		function()
		{
			var status_id = $(this).attr('data-status-id');
			$('#status-{0}'.sprintf(status_id)).addClass('hover');
		}
	)
	.on(
		'mouseleave',
		function()
		{
			var status_id = $(this).attr('data-status-id');
			$('#status-{0}'.sprintf(status_id)).removeClass('hover');
		}
	);



	$(window).load(function() {
		$('.col-tasks').same_height();
	});



	$(window).resize(function(){
		$('.col-tasks').same_height();
	});



}); // jquery


