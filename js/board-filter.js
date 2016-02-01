(function( $ ) {
$.fn.board_filter = function(task)
{
    return this.each(function()
    {
    	var $wrapper = $(this);

    	var t_filter_project = new t($('#t-filter-project').html());
    	var t_filter_user = new t($('#t-filter-user').html());

    	var $projects_dropdown = $('#filter-projects-dropdown', $wrapper);
    	var $users_dropdown = $('#filter-users-dropdown', $wrapper);



    	// populate projects live from data
    	$('#filter-projects', $wrapper).on(
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

				var project = board.project_records[project_id];

				// if not found
				if ( typeof project === 'undefined' )
				{
					project = {
						title: $a.text()
					};
				}

				var $dropdown = $a.closest('.dropup');
				var $label = $('.btn-label', $dropdown);

				// save orig
				if ( typeof $label.attr('data-orig') === 'undefined' )
				{
					$label.attr('data-orig', $label.text());
				}

				$label.text(project.title);
				// $dropdown.attr('data-id', project_id);



				if ( typeof project_id !== 'undefined' && project_id != '' )
				{
					board.filters.project.selector = '[data-project-id=' + project_id + ']';
					board.filters.project.hash = 'filter_project=' + project_id + '&';

					$('#btn-filter-apply', $wrapper).trigger('click');
				}



				return false;
			}
		);



		// populate users from live data
    	$('#filter-users', $wrapper).on(
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
				var user = board.allowed_users()[user_id];

				// if not found
				if ( typeof user === 'undefined' )
				{
					user = {
						long_name_email: $a.text()
					};
				}

				var $dropdown = $a.closest('.dropup');
				var $label = $('.btn-label', $dropdown);

				if ( typeof $label.attr('data-orig') === 'undefined' )
				{
					$label.attr('data-orig', $label.text());
				}

				$label.text(user.long_name_email);
				// $dropdown.attr('data-id', user_id);



				if ( typeof user_id !== 'undefined' && user_id != '' )
				{
					board.filters.user.selector = '[data-assigned-to=' + user_id + ']';
					board.filters.user.hash = 'filter_user=' + user_id + '&';

					$('#btn-filter-apply', $wrapper).trigger('click');

				}



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

				// build selector and hash
				for ( var filter in board.filters )
				{
					if ( typeof board.filters[filter].selector !== 'undefined' )
					{
						selector += board.filters[filter].selector;
					}

					if ( typeof board.filters[filter].hash !== 'undefined' )
					{
						hash += board.filters[filter].hash;
					}
				}

				var $tasks_to_show = $('.task' + selector);

				location.hash = hash;

				$('.task').not($tasks_to_show).slideUp('fast');

				$tasks_to_show.slideDown('fast');
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

