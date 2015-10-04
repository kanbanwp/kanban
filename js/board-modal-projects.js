(function( $ ) {
$.fn.modal_projects = function()
{
    return this.each(function()
    {
    	var $modal = $(this);



		$modal.on(
			'show.bs.modal',
			function ()
			{
				$('#accordion-projects').empty();

				for ( var i in project_records )
				{
					var project = project_records[i];

					project['task_count'] = 0;

					for ( var j in task_records )
					{
						try
						{
							if ( task_records[j].postmeta.kanban_task_project_id == project.ID )
							{
								project['task_count']++;
							}
						}
						catch (err) {}
					}

					var $project = $(t_modal_projects_panel.render(project));
					$project.appendTo('#accordion-projects');

				}
			}
		);



		$modal.on(
			'click',
			'.btn-delete',
			function()
			{
				var $btn = $(this);
				var $panel = $btn.closest('.panel');
				var project_id = $panel.attr('data-id');

				var data = JSON.parse(JSON.stringify(project_records[project_id]));
				data.action = 'delete_project';
				data.kanban_nonce = $('#kanban_nonce').val();

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: data
				})
				.done(function(response )
				{
					// remove project from projects
					delete project_records[project_id];

					// remove project from tasks
					for ( var i in task_records )
					{
						if ( task_records[i].postmeta.kanban_task_project_id == project_id )
						{
							task_records[i].postmeta.kanban_task_project_id = 0;
							$('#task-' + task_records[i].ID)
							.trigger('save')
							.trigger('populate_project');
						}
					}

					// remove from modal
					$panel.remove();

				})
				// .fail(function() {
				// })
				.always(function(response)
				{
					show_growl_msg(response);
				});
			}
		);



		$modal.on(
			'focus',
			'.project_title',
			function()
			{
				var $input = $(this);
				$input.data('orig', $input.val());
			}
		);



		$modal.on(
			'keyup',
			'.project_title',
			function(e)
			{
				var $input = $(this);

				// enter
				if(e.keyCode==13)
				{
					$input.trigger('blur');
					return false;
				}

				// escape
				if(e.keyCode==27)
				{
					var orig = $input.data('orig');
					$input.val(orig).trigger('blur');
					return false;
				}
			}
		);



		$modal.on(
			'blur',
			'.project_title',
			function()
			{
				var $input = $(this);

				var post_title = $input.val();
				var $panel = $input.closest('.panel');
				var project_id = $panel.attr('data-id');

				project_records[project_id].post_title = post_title;

				var data = JSON.parse(JSON.stringify(project_records[project_id]));
				data.action = 'save_project';
				data.kanban_nonce = $('#kanban_nonce').val();

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: data
				})
				.done(function(response )
				{
					// update task projects
					for ( var i in task_records )
					{
						$('#task-' + task_records[i].ID).trigger('populate_project');
					}

					var project = project_records[project_id];
					var $project = $(t_modal_projects_panel.render(project));
					$panel.replaceWith($project);
					$('.collapse', $project).addClass('in');
					$('.project_title', $project).focus();

				})
				// .fail(function() {
				// })
				.always(function(response)
				{
					show_growl_msg(response);
				});

			}
		);


	}); // each modal_projects
}; // $.fn.modal_projects
})( jQuery );









