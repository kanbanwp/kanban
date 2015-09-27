(function( $ ) {
$.fn.board_filter = function(task)
{
    return this.each(function()
    {
    	var $wrapper = $(this);





		$('#filter-projects-dropdown', $wrapper).on(
			'click',
			'a',
			function()
			{
				var $a = $(this);
				var project_id = $a.attr('data-id');
				var project = project_records[project_id];

				var $dropdown = $a.closest('.dropup');
				var $label = $('.btn-label', $dropdown);

				if ( typeof $label.attr('data-orig') === 'undefined' )
				{
					$label.attr('data-orig', $label.text());
				}

				$label
				.text(project.post_title)
				.attr('data-id', project_id);
			}
		);



		$('#filter-users-dropdown', $wrapper).on(
			'click',
			'a',
			function()
			{
				var $a = $(this);
				var user_id = $a.attr('data-id');
				var user = allowed_users[user_id];

				var $dropdown = $a.closest('.dropup');
				var $label = $('.btn-label', $dropdown);

				if ( typeof $label.attr('data-orig') === 'undefined' )
				{
					$label.attr('data-orig', $label.text());
				}

				$label
				.text(user.data.long_name_email)
				.attr('data-id', user_id);
			}
		);



		$('#btn-filter-apply', $wrapper).on(
			'click',
			function()
			{
				$('#btn-filter-reset').show();

				var project_id = $('#filter-projects .btn-label').attr('data-id');
				var selector = '';
				var hash = '';

				if ( typeof project_id !== 'undefined' && project_id != '' )
				{
					selector += '[data-project-id=' + project_id + ']';
					hash += 'filter_project=' + project_id + '&';
				}

				var user_id = $('#filter-users .btn-label').attr('data-id');
				if ( typeof user_id !== 'undefined' && user_id != '' )
				{
					selector += '[data-assigned-to=' + user_id + ']';
					hash += 'filter_user=' + user_id + '&';
				}
				var $tasks_to_show = $('.task' + selector);

				// update url for deep linking
				window.location.hash = hash;

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
					.attr('data-id', '')
					.text( $label.attr('data-orig') );
				});

				window.location.hash = '';
			}
		);




	}); // each search
}; // $.fn.search
})( jQuery );

