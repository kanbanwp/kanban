(function( $ ) {
$.fn.board_task = function(task)
{
    return this.each(function()
    {
    	var $task = $(this);

		$(document).trigger('/task/added/', task);

    	$task.timers = [];

		var $projects_dropdown = $('.project .list-group', $task);


		var estimate_records_arr = records_by_position(board.estimate_records());



		// save the task
		$task.on(
			'save',
			function (e, options)
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				if ( typeof options === 'undefined' )
				{
					options = [];
				}

				var task_data = {task: task};
				task_data.action = 'save_task';
				task_data.kanban_nonce = $('#kanban_nonce').val();

				if ( typeof options.comment !== 'undefined' )
				{
					task_data.comment = options.comment;
				}

				if ( typeof options.status_id_old !== 'undefined' )
				{
					task_data.status_id_old = options.status_id_old;
				}

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: task_data
				})
				// .done(function(response )
				// {
				// })
				// .fail(function() {
				// })
				.always(function(response)
				{
					show_growl_msg(response);
				});

				$('.col-tasks').matchHeight();
			}
		); // save



		$task.on(
			'delete',
			function ()
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				var task_data = {task: task};
				task_data.action = 'delete_task';
				task_data.kanban_nonce = $('#kanban_nonce').val();
				task_data.comment = 'task deleted by {0}'.sprintf(
					board.current_user().short_name
				);

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: task_data
				})
				.done(function()
				{
					delete board.task_records[task.ID];

					$task.slideUp('fast', function()
					{
						$task.remove();
						$('.col-tasks').matchHeight();
					});
				})
				// .fail(function() {
				// })
				.always(function(response)
				{
					show_growl_msg(response);
				});
			}
		); // save



		$task.on(
			'add_project',
			function ()
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				// delay, in case a project is selected
				setTimeout(function()
				{
					var $input = $('.project_title', $task);
					var project_title = $input.val();

					if ( typeof project_title === 'undefined' || project_title === '' )
					{
						return;
					}

					// see if typed value matches existing project
					var is_new_project = true;
					for ( var i in board.project_records )
					{
						var project = board.project_records[i];

						// it is NOT a new project
						if ( project_title === project.title )
						{
							is_new_project = false;
							break;
						}
					}

					// if it IS a new project
					if ( is_new_project )
					{
						var data = {
							action: 'save_project',
							post_type: 'kanban_project',
							kanban_nonce: $('#kanban_nonce').val(),
							project: {
								title: project_title
							}
						};

						// save new project
						$.ajax({
							method: "POST",
							url: ajaxurl,
							data: data
						})
						.done(function(response )
						{
							try
							{
								// add project to available projects
								board.project_records[response.data.project.id] = response.data.project;

								// assign new project id to task
								task.project_id = response.data.project.id;

								var comment = '{0} added the task to the project "{1}"'.sprintf (
									board.current_user().short_name,
									response.data.project.title
								);

								// save task
								$task
								.trigger('save', {comment: comment})
								.trigger('populate_project');
							}
							catch (err) {}
						})
						.always(function(response)
						{
							show_growl_msg(response);
						});
					} // is_new_project

				}, 500);

			} // function
		); // add project



		$task.on(
			'status_change',
			function(e, status_id_new)
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				var status_id_old = task.status_id + '';

				var comment = '{0} moved the task to "{1}"'.sprintf(
									board.current_user().short_name,
									board.status_records()[status_id_new].title
								);

				// update status and save
				task.status_id = status_id_new;
				$task.trigger('save', {comment: comment, status_id_old: status_id_old});



				// update color
				$('.task-handle', $task).css({
					background: board.status_records()[status_id_new].color_hex
				});
			} // function
		);



		var log_work_hour = function(operator)
		{
			if ( !current_user_has_cap('write') )
			{
				return false;
			}

			var task_data = {task: task};

			task_data.action = 'add_task_hour';
			task_data.kanban_nonce = $('#kanban_nonce').val();
			task_data.operator = operator;

			$.ajax({
				method: "POST",
				url: ajaxurl,
				data: task_data
			})
			// // .done(function(response )
			// // {

			// // })
			.always(function(response)
			{
				show_growl_msg(response);
			});
		}; // add_work_hour



		$task.on(
			'click',
			'.editable-input',
			function ()
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				var $input = $(this);

				// if already editing, ignore
				if ( !$input.prop('readonly') )
				{
					return;
				}

				// unfocus any others
				$('.editable-input').not($input).prop('readonly', true);

				// save the previous value for restoring
				$input.data('orig', $input.val());

				// enable input
				$input.prop('readonly', false);

				// give it a sec, and then focus
				setTimeout(function()
				{
					$input.focus().trigger('click').select();
				}, 100);
			}
		);



		$task.on(
			'keyup',
			'.editable-input',
			function(e)
			{
				var $input = $(this);

				// enter
				if (e.keyCode === 13 && !e.shiftKey)
				{
					// save it
					$input.trigger('blur');
				}

				// escape
				if(e.keyCode === 27)
				{
					// get prev value
					var orig = $input.data('orig');

					// restore prev value
					$input
					.val(orig)
					.prop('readonly', true);
				}
			}
		); // keyup



		$task.on(
			'blur',
			'.editable-input',
			function()
			{
				var $input = $(this);

				// disable it
				$input.prop('readonly', true);
			}
		);



		$task.on(
			'click',
			'.delete-task',
			function ()
			{
				$task.trigger('delete');

				return false;
			}
		);




	function save_contenteditable ($div)
	{
		// prevent saving twice, if they blur instead of timeout
		clearTimeout($div.data('save_timer'));

		encode_urls_emails ($div);



		var is_new_task = false;
		if ( task.title === '' )
		{
			is_new_task = true;
		}



		var val = $div.html();
		task.title = val;

		var comment = '{0} updated the task {1} to {2}'.sprintf (
			board.current_user().short_name,
			task.title,
			val
		);

		if ( is_new_task )
		{
			comment = '{0} added the task'.sprintf (
					board.current_user().short_name
				);
		}

		$task.trigger('save', {comment: comment});
	}



	$('.task-title', $task)
	.on(
		'focus',
		function(e)
		{
			if ( !current_user_has_cap('write') )
			{
				return false;
			}

			var $div = $(this);

			if ( $(e.target).is('a') )
			{
				return false;
			}

			$div.data('orig', $div.html());			

			$div.html( sanitizeString($div.html()) );
		}
	);

	$('.task-title', $task)
	.on(
		'keydown',
		function(e)
		{
			if ( !current_user_has_cap('write') )
			{
				return false;
			}

			var $div = $(this);

			// escape
			if(e.keyCode === 27)
			{
				// get prev value
				var orig = $div.data('orig');

				// restore prev value
				$div.html(orig);

				// trigger save
				$div.blur();

				return;
			}

			// enter
			if (e.keyCode === 13 && !e.shiftKey)
			{
				$div.blur();
			}
		}
	);

	$('.task-title', $task)
	.on(
		'keyup',
		function()
		{
			if ( !current_user_has_cap('write') )
			{
				return false;
			}

			var $div = $(this);

			// delete prev timer
			clearTimeout($div.data('save_timer'));

			// set new timer
			var save_timer = setTimeout(function()
			{
				save_contenteditable ($div);	
			}, 3000);
			
			$div.data('save_timer', save_timer);
		}
	);

	$('.task-title', $task)
	.on(
		'blur',
		function()
		{
			if ( !current_user_has_cap('write') )
			{
				return false;
			}

			var $div = $(this);
			save_contenteditable ($div);
			window.getSelection().removeAllRanges();
		}
	); // blur





		// https://github.com/AndrewDryga/jQuery.Textarea.Autoresize
		// $('textarea.resize', $task).autoresize({
		// 	onResize: function()
		// 	{
		// 		$(this).addClass('autoresize');
		// 	}
		// })
		// .trigger('keydown');



		// assign task to user
		$task.on(
			'click',
			'.dropdown-menu-allowed-users .btn',
			function()
			{
				var $btn = $(this);
				var user_id = $btn.val();

				var comment = '';
				if ( $('.task-assigned-to-short_name', $task).not('.empty') )
				{
					comment = '{0} assigned the task to {1}'.sprintf (
						board.current_user().short_name,
						$('.task-assigned-to-short_name', $task).text()
					);
				}

				task.user_id_assigned = user_id;
				$task.trigger('save', {comment: comment});

				$task.trigger('populate_user');
			}
		);



		// set task estimate
		$task.on(
			'click',
			'.dropdown-menu-estimates .btn',
			function()
			{
				var $btn = $(this);
				var estimate_id = $btn.val();

				task.estimate_id = estimate_id;

				var comment = '{0} set the task estimate to {1}'.sprintf (
					board.current_user().short_name,
					board.estimate_records()[estimate_id].title
				);

				$task.trigger('save', {comment: comment});
				$task.trigger('populate_estimate');
			}
		);



		// for mobile responsive view
		$task.on(
			'click',
			'.btn-task-hours',
			function()
			{
				$('.task-hours-operators', $task).toggleClass('active');
				return false;
			}
		);



		// add/remove work hour
		$task.on(
			'click',
			'.task-hours-operators .btn',
			function()
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				var $btn = $(this);
				var btn_val = $btn.val();

				if ( btn_val.substring(0, 1) != '+' && btn_val.substring(0, 1) != '-' )
				{
					return false;
				}

				var operator = parseFloat(btn_val);

				if ( isNaN(operator) )
				{
					return false;
				}

				var current = parseFloat(task.hour_count);

				// increase/decrease hours
				current = eval(current + operator);

				// round to thousandth place
				// @link http://stackoverflow.com/a/11832950/38241
				current = Math.round(current * 1000) / 1000;

				if ( current < 0 )
				{
					current = 0;
					return;
				}

				// update the total count
				task.hour_count = current;

				$task.trigger('populate_task_hours');

				// save the action for later
				log_work_hour(operator);

				return false;
			}
		);



		$task.on(
			'populate_project',
			function ()
			{
				var project = board.project_records[task.project_id];

				if ( typeof project === 'undefined' )
				{
					project = {
						id: 0,
						title: ''
					};
				}

				$task.attr('data-project-id', project.id);

				$('.project_title', $task)
				.val(project.title)
				.attr('data-id', project.id);
			}
		)
		.trigger('populate_project');



		$task.on(
			'keyup',
			'.project_title',
			function(e)
			{
				var $input = $(this);



				// enter
				if(e.keyCode === 13)
				{
					$task.trigger('projects_dropdown_hide');
					return;
				}



				// escape
				if(e.keyCode === 27)
				{
					$task.trigger('projects_dropdown_hide');
					return;
				}



				$task.trigger('projects_dropdown_show');

				$('.list-group-item', $task).remove();

				var value = $input.val();
				var valueLower = $.trim( value.toLowerCase() );

				for ( var id in board.project_records )
				{
					var project = board.project_records[id];

					var text = project.title;
					var textLower = $.trim(text.toLowerCase() );

					if ( textLower.search(valueLower) > -1 )
					{
						$(t_card_projects_dropdown.render(project)).appendTo($projects_dropdown);
					}
				}

				if ( $('.list-group-item', $task).length === 0 )
				{
					$task.trigger('projects_dropdown_hide');
				}
			} // function
		);



		$task.on(
			'blur',
			'.project_title',
			function()
			{
				var $btn_project_edit = $('.btn-edit-projects', $task);

				// don't save if "edit projects" was clicked
				if ( $last_clicked[0] === $btn_project_edit[0] )
				{
					// trigger escape
					var e = jQuery.Event("keyup");
					e.which = 27; //choose the one you want
					e.keyCode = 27;
					$(this).trigger(e);

					// show modal, if it didn't
					var target_id = $btn_project_edit.attr('data-target');
					$(target_id).modal('show');
				}
				else
				{
					$task.trigger('add_project');
				}
			}
		);



		$task.on(
			'focus',
			'.project_title',
			function()
			{
				if ( !current_user_has_cap('write') )
				{
					return false;
				}

				var $input = $(this);
				$input.trigger('keyup');
			}
		);



		$task.on(
			'click',
			'.project .list-group-item',
			function()
			{
				$task.trigger('projects_dropdown_hide');

				var project_id = $(this).attr('data-id');

				task.project_id = project_id;
				// $task.attr('data-project-id', project_id);
				// $('.project_title', $task).attr('data-id', project_id);

				$task
				.trigger('save')
				.trigger('populate_project');



				var project = board.project_records[project_id];

				if ( typeof project === 'undefined' )
				{
					return false;
				}



				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} added the task to the project "{1}"'.sprintf (
							board.current_user().short_name,
							project.post_title
						)
					]
				);
			}
		);



		$task.on(
			'click',
			'.btn-edit-projects',
			function()
			{
				$task.trigger('projects_dropdown_hide');
			}
		);



		var update_progress_bar = function ()
		{
			// update progress bar
			var progress_percent = 0;
			if ( typeof task.estimate_id !== 'undefined' && typeof task.hour_count !== 'undefined' )
			{
				var estimate = board.estimate_records()[task.estimate_id];

				if ( typeof estimate !== 'undefined' )
				{
					progress_percent = (parseFloat(task.hour_count)*100)/parseFloat(estimate.hours);
				}
			}

			var progress_type = 'success';
			if ( progress_percent > 133)
			{
				progress_type = 'danger';
			}
			else if ( progress_percent > 100 )
			{
				progress_type = 'warning';
			}

			if ( progress_percent > 100 )
			{
				progress_percent = 100;
			}

			$('.progress-bar', $task)
			.css({
				width: progress_percent + '%'
			})
			.removeClass('progress-bar-success progress-bar-warning progress-bar-danger')
			.addClass('progress-bar-' + progress_type);
		};



		// prevent enter in task title
		// $('.task-title', $task)
		// .on(
		// 	'keydown',
		// 	function(e)
		// 	{
		// 		// enter
		// 		if (e.keyCode == 13 && !e.shiftKey)
		// 		{
		// 			return false;
		// 		}
		// 	}
		// ) // keyup
		// .on(
		// 	'blur',
		// 	function()
		// 	{
		// 		var is_new_task = false;
		// 		if ( task.title === '' )
		// 		{
		// 			is_new_task = true;
		// 		}

		// 		var $input = $(this);
		// 		task.title = $input.val();

		// 		var comment = '{0} updated the task title'.sprintf (
		// 			board.current_user().short_name
		// 		);

		// 		if ( is_new_task )
		// 		{
		// 			comment = '{0} added the task'.sprintf (
		// 					board.current_user().short_name
		// 				);
		// 		}

		// 		$task.trigger('save', {comment: comment});
		// 	}
		// ); // blur



		// populate estimate dropdowns
		if ( current_user_has_cap('write') )
		{
			for ( var i in estimate_records_arr )
			{
				var estimate = estimate_records_arr[i];

				var $estimate = $(t_card_estimates_dropdown.render(estimate));
				$('.dropdown-menu-estimates', $task).append($estimate);
			}
		}



		$task.on(
			'populate_estimate',
			function ()
			{
				// populate estimate
				var estimate = board.estimate_records()[task.estimate_id];

				var label = '--';
				if ( typeof estimate !== 'undefined' )
				{
					label = estimate.title;
				}

				var data = {
					estimate: label
				};

				var $estimate = $(t_card_estimate.render(data));
				$('.btn-estimate', $task).replaceWith($estimate);

				update_progress_bar();
			}
		)
		.trigger('populate_estimate');



		if ( current_user_has_cap('write') )
		{
			// populate user dropdowns
			for ( var i in board.allowed_users() )
			{
				var user = board.allowed_users()[i];

				if ( !user_has_cap('write', user) )
				{
					continue;
				}

				var $user = $(t_card_users_dropdown.render(user));
				$('.dropdown-menu-allowed-users', $task).append($user);
			}
		}



		$task.on(
			'populate_user',
			function ()
			{
				var user;
				try
				{
					user = board.allowed_users()[task.user_id_assigned];
					$task.attr('data-assigned-to', task.user_id_assigned);
				}
				catch (err) {}

				if ( typeof user === 'undefined' )
				{
					user = {
						short_name: '-- Assign --',
						initials: '--'
					};
				}

				var $user = $(t_card_user_assigned_to.render(user));
				$('.btn-assigned-to', $task).replaceWith($user);
			}
		)
		.trigger('populate_user');



		$task.on(
			'populate_task_hours',
			function ()
			{
				task.hour_count = parseFloat(task.hour_count);

				if ( task.hour_count < 0 )
				{
					task.hour_count = 0;
				}

				var label;
				label = format_hours(task.hour_count);

				var data = {
					hour_count: label
				};

				var $work_hours = $(t_card_task_hours.render(data));
				$('.btn-task-hours', $task).replaceWith($work_hours);

				update_progress_bar();
			}
		)
		.trigger('populate_task_hours');



		$task.on(
			'projects_dropdown_show',
			function ()
			{
				$projects_dropdown.show();
				$task.addClass('active');
			}
		);



		$task.on(
			'projects_dropdown_hide',
			function ()
			{
				$projects_dropdown.hide();
				$task.removeClass('active');
			}
		);



		$('.col-assigned-to', $task).on(
			'show.bs.dropdown',
			function ()
			{
				$task.addClass('active');
			}
		);



		$('.col-assigned-to', $task).on(
			'hide.bs.dropdown',
			function ()
			{
				$task.removeClass('active');
			}
		);



		$('.col-estimate', $task).on(
			'show.bs.dropdown',
			function ()
			{
				$task.addClass('active');
			}
		);



		$('.col-estimate', $task).on(
			'hide.bs.dropdown',
			function ()
			{
				$task.removeClass('active');
			}
		);

	}); // each task
}; // $.fn.task
})( jQuery );

