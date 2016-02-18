function board_filter_store (type, id)
{
	board.filters[type] = {
		// selector: '[data-' + type + '-id=' + id + ']',
		hash: 'filter_' + type + '=' + id + '&',
		id: id
	};
}



(function( $ ) {
$.fn.board_filter = function(task)
{
    return this.each(function()
    {
    	var $wrapper = $(this);

    	var t_filter_project = new t($('#t-filter-project').html());
    	var t_filter_user = new t($('#t-filter-user').html());

    	var $projects_dropdown = $('#filter-project_id-dropdown', $wrapper);
    	var $users_dropdown = $('#filter-user_id_assigned-dropdown', $wrapper);



		$('.filter', $wrapper)
		.on(
    		'populate_dropdown',
    		'a',
    		function(e, title)
    		{
    			var $a = $(this);
				var $dropdown = $a.closest('.dropup');
				var $label = $('.btn-label', $dropdown);

				if ( typeof title === 'undefined' )
				{
					title = $a.text();
				}

				// save orig
				if ( typeof $label.attr('data-orig') === 'undefined' )
				{
					$label.attr('data-orig', $label.text());
				}

				$label.text(title);
			}
		);



    	// populate projects live from data
    	$('#filter-project_id', $wrapper).on(
    		'show.bs.dropdown',
    		function()
    		{
    			$('.project', $projects_dropdown).remove();

				for ( var i in board.project_records )
				{
					var $project = $(t_filter_project.render(board.project_records[i]));
					$project.prependTo($projects_dropdown);
				}

    		}
    	);



    	// project dropdown behavior
		$projects_dropdown.on(
			'click',
			'a',
			function()
			{
				var $a = $(this);
				var project_id = $a.attr('data-id');

				if ( typeof board.project_records[project_id] !== 'undefined' )
				{
					var project = board.project_records[project_id];
					var title = project.title;
				}



				$a.trigger('populate_dropdown', [title]);



				board_filter_store('project_id', project_id);

				$('#btn-filter-apply', $wrapper).trigger('click');



				return false;
			}
		);



		// populate users from live data
    	$('#filter-user_id_assigned', $wrapper).on(
    		'show.bs.dropdown',
    		function()
    		{
    			$('.user', $users_dropdown).remove();

				for ( var i in board.allowed_users() )
				{
					var user = board.allowed_users()[i];

					if ( !user_has_cap('write', user) )
					{
						continue;
					}

					var $user = $(t_filter_user.render(user));
					$user.prependTo($users_dropdown);
				}
    		}
    	);



		$users_dropdown.on(
			'click',
			'a',
			function()
			{
				var $a = $(this);
				var user_id = $a.attr('data-id');

				if ( typeof board.allowed_users()[user_id] !== 'undefined' )
				{
					var user = board.allowed_users()[user_id];
					var title = user.long_name_email;
				}



				$a.trigger('populate_dropdown', [title]);



				board_filter_store('user_id_assigned', user_id);
				$('#btn-filter-apply', $wrapper).trigger('click');



				return false;
			}
		);



		$('#btn-filter-apply', $wrapper).on(
			'click',
			function()
			{
				$('#btn-filter-reset').show();

				var selector = '';
				var hash = '#';



				// build selector and hash for TASKS TO HIDE, to support multiple filtesr
				for ( var filter in board.filters )
				{
					if ( typeof board.filters[filter].id === 'undefined' )
					{
						continue;
					}

					if ( typeof board.filters[filter].hash !== 'undefined' )
					{
						hash += board.filters[filter].hash;
					}

					var filter_id = board.filters[filter].id;

					for ( var task_id in board.task_records )
					{
						var task = board.task_records[task_id];
						if ( task[filter] != filter_id ) // NOT, so we're hiding tasks
						{
							selector += '#task-' + task_id + ',';
						}
					}
				}



				// trim trailing comma
				selector = selector.replace(/(^,)|(,$)/g, "");
				var $tasks_to_hide = $(selector);



				// update hash
				location.hash = hash;



				// show/hide tasks
				$tasks_to_hide.slideUp('fast');
				$('.task').not($tasks_to_hide).slideDown('fast');

			}
		);



		$('#btn-filter-reset', $wrapper).on(
			'click',
			function()
			{
				$(this).hide();
				$('.task:not(:visible)').slideDown('fast');

				$('#filter-wrapper .btn-label').each(function()
				{
					var $label = $(this);
					$label
					.text( $label.attr('data-orig') );
				});

				// clear filters
				for ( var filter in board.filters )
				{
					board.filters[filter] = {};
				}

				location.hash = '#';
			}
		);




	}); // each search
}; // $.fn.search
})( jQuery );

