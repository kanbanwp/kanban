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

	$(document).trigger('/tasks/added/', task);



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



	var view = Cookies.get('kanban-board-view');

	if ( board.settings().default_to_compact_view == 1 || view == 'compact' )
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

	for ( var filter_type in hash )
	{
		if ( filter_type.indexOf('filter_') !== 0 ) continue;

		var id = hash[filter_type];
		var type = filter_type.substring( 'filter_'.length );

		// store filters
		board_filter_store(type, id);

		// get filter
		var $filter = $('#filter-{0}'.sprintf(type) );

		// populate dropdown
		$filter.trigger('show.bs.dropdown');

		// get option
		var $a = $('[data-id={1}]'.sprintf(type, id), $filter );

		// propulate button label
		$a.trigger('populate_dropdown', [$a.text()]);

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


