/**
 * @link https://github.com/remy/bind.js
 */



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
String.prototype.sprintf = function() {
    var formatted = this;
    for( var arg in arguments ) {
        formatted = formatted.replace("{" + arg + "}", arguments[arg]);
    }
    return formatted;
};



// @link http://stackoverflow.com/a/6700/38241
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
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

	if ( to_return == '' )
	{
		to_return = format_hours(0);
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
				type: 'info',
  				allow_dismiss: true
			}
		);
	}
	catch (err) {}
};



var add_task_to_status_col = function(task)
{
	if ( typeof task.estimate_id === 'undefined' )
	{
		task.estimate_id = 0;
	}

	if ( typeof task.user_id_assigned === 'undefined' )
	{
		task.user_id_assigned = '';
	}

	if ( typeof board.status_records()[task.status_id] != 'undefined' )
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

	return $task;
};



/**
 * test if current user has capability
 * @param  {string} cap the cap we're looking for
 * @return {bool}     if current user has cap or not
 */
var current_user_has_cap = function (cap)
{
	return user_has_cap(cap, board.current_user());
}



/**
 * test if a user has a capability
 * @param  {string} cap the cap we're looking for
 * @param  {object} user the user to test against
 * @return {bool}     if user has cap or not
 */
var user_has_cap = function (cap, user)
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
var records_by_position = function (obj)
{
	var obj_arr = $.map(obj, function(value, index) {
	    return [value];
	});

	obj_arr.sort(function(a, b)
	{
		return a.position - b.position;
	});

	return obj_arr;
};



jQuery(function($)
{
	$('#filter-wrapper').board_filter();
	$('#search-wrapper').board_search();
	$('#btn-group-view-compact').board_view();
	$('#modal-projects').modal_projects();



	$('#btn-empty-status-tasks').on(
		'click',
		function ()
		{
			// set text
			$('#empty-archive-confirmation-label').text('Deleting...');

			var $btn = $(this).prop('disabled', true);

			// get passed status col id
			var status_id = $btn.attr('data-status-col-id');

			// start an interval
			timers.delete_task_interval = setInterval(function()
			{
				// see if there's a task to delete
				var $first_task = $('#{0} .task:first .delete-task'.sprintf(status_id));

				// if no first task
				if ( $first_task.length == 0 )
				{
					// stop the interval
					clearInterval(timers.delete_task_interval);

					// reset, hide modal
					$('#btn-empty-status-tasks').prop('disabled', false);
					$('#modal-empty-archive').modal('hide');
				}
				else
				{
					// otherwise trigger the delete
					$first_task.click();
				}
			}, 3000);
			// click()
		}
	);



	$('#btn-empty-status-tasks-cancel').on(
		'click',
		function()
		{
			clearInterval(timers.delete_task_interval);
		}
	);


	$(document).mousedown(function(e)
	{
		// The latest element clicked
		$last_clicked = $(e.target);
	});



	var status_records_arr = records_by_position(board.status_records());



	var i = 0;
	for ( var id in status_records_arr )
	{
		var status = status_records_arr[id];

		if ( i == 0 )
		{
			status.left_open = 0;
			status.left_close = '-{0}'.sprintf(sidebar_w);
		}

		if ( i == Object.size(status_records_arr)-1 )
		{
			status.left_open = '-{0}'.sprintf(sidebar_w*2);
			status.left_close = '-{0}'.sprintf(sidebar_w);
		}

		var $status = $(t_col_status.render({
			status: status,
			current_user_can_write: current_user_has_cap('write')
		}));
		$status.appendTo('#row-statuses').board_sidebar_header();

		var $status = $(t_col_tasks.render({
			status: status,
			current_user_can_write: current_user_has_cap('write')
		}));
		$status.appendTo('#row-tasks');

		i++;
	}



	for ( var id in board.task_records )
	{
		var task = board.task_records[id];
		add_task_to_status_col(task);
	}



	if ( current_user_has_cap('write') )
	{

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

				ui.item.trigger('status_change', [status_id_new]);

				$('.col-tasks').matchHeight();
			} // receive
		});
		// .disableSelection();
	}




	$("body").on(
		'keydown',
		function(e)
		{
			var $any_input = $('input:focus, textarea:focus, [contenteditable]:focus');



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



			// launch tour
			// if( e.keyCode === 84 && $any_input.length === 0 )
			// {
			// 	if ( e.shiftKey )
			// 	{
			// 		board_tour.show();
			// 	}
			// }
		}
	);



	if ( board.settings().default_to_compact_view == 1 )
	{
		$('#btn-group-view-compact .btn:has([name="view-compact"])').trigger('click');
	}


	var hash = [];
	if (location.hash)
	{
		var params = (location.hash.substr(1)).split("&");
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



	if ( current_user_has_cap('write') )
	{

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
	}



	$(window).load(function() {
		
		$('.col-tasks').matchHeight();
	});



	$(window).resize(function(){
		$('.col-tasks').matchHeight();
	});



	// if an alert is passed, show it
	if ( alert !== '' )
	{
		$.bootstrapGrowl(
			alert,
			{
				type: 'info',
  				allow_dismiss: true
			}
		);
	}


}); // jquery


