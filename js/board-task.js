(function( $ ) {
$.fn.board_task = function(task)
{
    return this.each(function()
    {
    	var $task = $(this);
    	// $task.task = task;
    	$task.timers = [];
		$task.skip_first_save = true;



		// save the task
		$task.on(
			'save',
			function (e)
			{
				if ( $task.skip_first_save )
				{
					$task.skip_first_save = false;
					return false;
				}

				var task_data = tasks[task.ID].__export();
				task_data.action = 'save_task';
				task_data.kanban_nonce = $('#kanban_nonce').val();

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

				$('.col-tasks').same_height();
			}
		); // save



		$task.on(
			'delete',
			function (e)
			{
				var task_data = tasks[task.ID].__export();
				task_data.action = 'delete_task';
				task_data.kanban_nonce = $('#kanban_nonce').val();

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: task_data
				})
				.done(function(response )
				{
					delete tasks[task_data.task.ID];

					$task.slideUp('fast', function()
					{
						$task.remove();
						$('.col-tasks').same_height();
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
			'add_comment',
			function (e, comment_content)
			{
				if ( typeof comment_content === 'undefined' || comment_content == '' )
				{
					return false;
				}

				var data = {
					id: task.ID,
					action: 'save_comment',
					post_type: 'task',
					kanban_nonce: $('#kanban_nonce').val(),
					comment_content: comment_content
				};

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: data
				})
				// .done(function(response )
				// {
				// })
				// .always(function(response)
				// {
				// 	show_growl_msg(response);
				// });
			}
		);


		$task.on(
			'status_change',
			function(e, status_id_old, status_id_new)
			{
				var data = {
					'action': 'add_status_change',
					'kanban_nonce': $('#kanban_nonce').val(),
					'task_id': task.ID,
					'status_id_old': status_id_old,
					'status_id_new': status_id_new
				};

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: data
				});
			} // function
		);




		var add_work_hour = function(operator)
		{
			var task_data = tasks[task.ID].__export();

			task_data.action = 'add_work_hour';
			task_data.kanban_nonce = $('#kanban_nonce').val();
			task_data.operator = operator;

			$.ajax({
				method: "POST",
				url: ajaxurl,
				data: task_data
			});
			// .done(function(response )
			// {

			// })
			// .always(function(response)
			// {
			// 	show_growl_msg(response);
			// });
		} // add_work_hour



		$task.on(
			'click',
			'.editable-input',
			function ()
			{
				var $input = $(this);
				if ( !$input.prop('readonly') )
				{
					return;
				}

				$('.editable-input').not($input).prop('readonly', true);

				$input.data('orig', $input.val());

				$input.prop('readonly', false);

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
				if(e.keyCode==13)
				{
					$input.trigger('blur');
				}

				// escape
				if(e.keyCode==27)
				{
					var orig = $input.data('orig');
					$input
					.val(orig)
					.prop('readonly', true);
				}
			}
		); // keyup



		$task.on(
			'blur',
			'.editable-input',
			function(e)
			{
				var $input = $(this);
				$input.prop('readonly', true);
			}
		);



		$task.on(
			'click',
			'.delete-task',
			function ()
			{
				var task_data = tasks[task.ID].__export();
				$task.trigger('delete', [task_data]);

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} deleted the task'.sprintf (
							current_user.short_name
						)
					]

				);

				return false;
			}
		)



		// https://github.com/AndrewDryga/jQuery.Textarea.Autoresize
		$('textarea.resize', $task).autoresize({
			onResize: function()
			{
				$(this).addClass('autoresize');
			}
		})
		.trigger('keydown');



		$task.on(
			'click',
			'.dropdown-menu-allowed-users .btn',
			function()
			{
				var $btn = $(this);
				var user_id = $btn.val();

				task.postmeta.kanban_task_user_id_assigned = user_id;
				$task.attr('data-assigned-to', user_id);

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} assigned the task to {1}'.sprintf (
							current_user.short_name,
							$('.task-assigned-to-short_name', $task).text()
						)
					]
				);
			}
		);



		$task.on(
			'click',
			'.dropdown-menu-estimates .btn',
			function()
			{
				var $btn = $(this);
				var term_id = $btn.val();

				task.terms.kanban_task_estimate = [term_id]; // ensure it's only ever 1

				for ( var i in estimates )
				{
					if ( estimates[i].term_taxonomy_id == term_id )
					{
						var estimate = estimates[i];
						break;
					}
				}

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} set the task estimate to {1}'.sprintf (
							current_user.short_name,
							estimate.name
						)
					]
				);
			}
		);



		$task.on(
			'click',
			'.btn-work-hours',
			function()
			{
				$('.work-hours-operators', $task).toggleClass('active');
				return false;
			}
		);



		$task.on(
			'click',
			'.work-hours-operators .btn',
			function()
			{
				var $btn = $(this);
				var operator = $btn.val();

				var current = parseInt(task.postmeta.kanban_task_work_hour_count);

				// increase/decrease hours
				current = eval(current + operator);

				if ( current < 0 )
				{
					current = 0;
				}

				task.postmeta.kanban_task_work_hour_count = current;

				add_work_hour(operator);



				var comment = operator == '+1' ? ' logged an hour of work' : ' subtracted an hour of work';

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} {1}'.sprintf (
							current_user.short_name,
							comment
						)
					]
				);


				return false;
			}
		);



		// populate project
		var project = {};

		try
		{
			if ( typeof project_records[task.postmeta.kanban_task_project_id] !== 'undefined' )
			{
				project = project_records[task.postmeta.kanban_task_project_id];
				$task.attr('data-project-id', task.postmeta.kanban_task_project_id);
			}
		}
		catch (err) {}

		var $project = $(t_card_project.render(project));
		$('.wrapper-task-project', $task).append($project);



		$('.project_title', $project)
		.autocomplete({
			minLength: 0,
			focus: function(e, ui)
			{
				$(e.target).val(ui.item.label);
				return false;
			},
			select: function(e, ui)
			{
				task.postmeta.kanban_task_project_id = ui.item.ID;
				$(e.target)
				.val(ui.item.label)
				// .attr('data-id', ui.item.value)
				.trigger('blur');
				$task.attr('data-project-id', ui.item.ID);

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} added the task to the project "{1}"'.sprintf (
							current_user.short_name,
							ui.item.label
						)
					]
				);

				return false;
			},
			source: function (request, response)
			{
				response($.map(
					projects.projects,
					function (value, key)
					{
						return {
							label: value.post_title,
							ID: value.ID
						}
					}
				));
			}
		})
		.on('focus', function()
		{
			$(this).autocomplete("search");
		})
		.on(
			'keyup',
			function(e)
			{
				var $input = $(this);

				// enter
				if(e.keyCode==13)
				{
					$input.autocomplete('close');

					var data = {
						action: 'save_project',
						kanban_nonce: $('#kanban_nonce').val(),
						project: {
							post_title: $input.val()
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
						if ( typeof response.data !== 'undefined' )
						{
							if ( typeof response.data.project !== 'undefined' )
							{
								// assign new project id to task
								task.postmeta.kanban_task_project_id = response.data.project.ID;

								// update UI
								$input.attr('data-id', response.data.project.ID);
								$input.val(response.data.project.post_title);

								// add project to available projects
								projects.projects.push(response.data.project);

								// add comment for log
								$task.trigger(
									'add_comment',
									[
										'{0} added the task to the project "{1}"'.sprintf (
											current_user.short_name,
											response.data.project.post_title
										)
									]
								);
							}
						}
					})
					.always(function(response)
					{
						show_growl_msg(response);
					});
				}

				// escape
				if(e.keyCode==27)
				{
					$input.autocomplete('close');
				}
			}
		); // keyup



		// prevent enter in task title
		$('.task_title', $task)
		.on(
			'keydown',
			function(e)
			{
				var $input = $(this);

				// enter
				if(e.keyCode==13)
				{
					return false;
				}
			}
		) // keyup
		.on(
			'blur',
			function(e)
			{
				var $input = $(this);
				task.post_title = $input.val();

				// add comment for log
				$task.trigger(
					'add_comment',
					[
						'{0} updated the task title'.sprintf (
							current_user.short_name
						)
					]

				);
			}
		); // blur





		// populate allowed user dropdowns
		for ( var i in allowed_users )
		{
			var user = allowed_users[i];
			var $user = $(t_allowed_users.render(user));
			$('.dropdown-menu-allowed-users', $task).append($user);
		}



		// populate estimate dropdowns
		for ( var i in estimates )
		{
			var estimate = estimates[i];
			var $estimate = $(t_estimates.render(estimate));
			$('.dropdown-menu-estimates', $task).append($estimate);
		}



		tasks[task.ID] = new Bind(
			{
				task: task
			},
			{
				task: {
					callback: function ()
					{
						// update progress bar
						var progress_percent = 0;
						if ( typeof this.task.terms.kanban_task_estimate[0] !== 'undefined' && typeof this.task.postmeta.kanban_task_work_hour_count !== 'undefined' )
						{
							for ( var i in estimates )
							{
								if ( estimates[i].term_taxonomy_id == this.task.terms.kanban_task_estimate[0] )
								{
									var estimate = estimates[i];
									break;
								}
							}

							if ( typeof estimate !== 'undefined' )
							{
								progress_percent = (parseInt(this.task.postmeta.kanban_task_work_hour_count)*100)/parseInt(estimate.slug);
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

						$('#task-' + this.task.ID + ' .progress-bar')
						.css({
							width: progress_percent + '%'
						})
						.removeClass('progress-bar-success progress-bar-warning progress-bar-danger')
						.addClass('progress-bar-' + progress_type);

						clearTimeout($task.timers['save']);
						// var task_data = this.__export();
						$task.timers['save'] = setTimeout(function()
						{
							$task.trigger('save');
						}, 100);
					},
				},
				'task.status_color': function (status_color)
				{
					var $card = $('#task-' + this.task.ID);
					$('.task-handle', $card).css({
						background: status_color
					});
				},
				'task.terms.kanban_task_estimate.0':
				{
					dom: '#task-' + task.ID + ' .btn-estimate',
					transform: function (v)
					{
						for ( var i in estimates )
						{
							if ( estimates[i].term_taxonomy_id == v )
							{
								var estimate = estimates[i];
								break;
							}
						}

						if ( typeof estimate === 'undefined' )
						{
							var label = '--';
						}
						else
						{
							var label = format_hours(estimate.slug);
						}

						var data = {
							kanban_task_estimate: label
						};

						var $estimate = $(t_card_estimate.render(data));
						return $estimate.html();

					}
				},
				'task.postmeta.kanban_task_user_id_assigned':
				{
					dom: '#task-' + task.ID + ' .btn-assigned-to',
					transform: function (v)
					{
						var user;
						if ( typeof allowed_users[v] === 'undefined' )
						{
							user = {
								data: {
									short_name: '-- Assign --',
									initials: '--'
								}
							};
						}
						else
						{
							user = allowed_users[v];
							$task.attr('data-assigned-to', v);
						}
						var $user = $(t_card_user_assigned_to.render(user));
						return $user.html();
					}
				},
				'task.postmeta.kanban_task_work_hour_count':
				{
					dom: '#task-' + task.ID + ' .btn-work-hours',
					transform: function (v)
					{
						if ( v < 0 )
						{
							v = 0;
						}

						var label;
						label = format_hours(v);

						var data = {
							kanban_task_work_hour_count: label
						};

						var $work_hours = $(t_card_work_hours.render(data));
						return $work_hours.html();
					}
				}
			}
		); // bound



	}); // each task
}; // $.fn.task
})( jQuery );

