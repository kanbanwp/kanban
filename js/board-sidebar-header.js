(function( $ ) {
$.fn.board_sidebar_header = function()
{
    return this.each(function()
    {
		var $sidebar = $(this);
		var $to_slide = $('#row-tasks, #row-statuses');
		var status_id = $sidebar.attr('data-id');



		$sidebar.on(
			'click',
			'.toggle-sidebar',
			function()
			{
				$sidebar.trigger('toggle');
			}
		);



    	$sidebar.on(
    		'toggle',
    		function()
    		{
				// if any sidebar is moving, don't do it
				if ( $to_slide.is(':animated') )
				{
					return false;
				}

				if ( $sidebar.hasClass('is-open') )
				{
					$sidebar.trigger('close');
				}
				else
				{
					$sidebar.trigger('open');
				}
    		}
    	);



		$sidebar.on(
			'open',
			function ()
			{

				if ( $sidebar.hasClass('is-open') )
				{
					return false;
				}
				var left = $sidebar.attr('data-open');
				$sidebar.trigger('doslide', [left]);

				$sidebar.addClass('is-open');
			}
		); // open



		$sidebar.on(
			'close',
			function ()
			{
				if ( !$sidebar.hasClass('is-open') )
				{
					return false;
				}
				var left = $sidebar.attr('data-close');
				$sidebar.trigger('doslide', [left]);

				$sidebar.removeClass('is-open');
			}
		); // close



		$sidebar.on(
			'doslide',
			function (e, left)
			{
				if( typeof left === undefined )
				{
					return false;
				}

				$to_slide.animate(
					{
						left: left
					},
					'fast'
				);
			}
		); // close




		$sidebar.on(
			'click',
			'.btn-new-task',
			function()
			{
				var $btn = $(this);
				$('.glyphicon', $btn).toggle();

				var task_data = {
					task: {'status_id': status_id},
					comment: 'Task added by {0}'.sprintf(current_user.short_name)
				};
				task_data.action = 'save_task';
				task_data.kanban_nonce = $('#kanban_nonce').val();

				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: task_data
				})
				.done(function(response )
				{
					$('.glyphicon', $btn).toggle();

					// just in case
					try
					{
						$task = add_task_to_status_col(response.data.task);

						$('.task_title', $task).trigger('click').focus();
					}
					catch (err) {}


				}); // done

			}
		);



		$sidebar.on(
			'click',
			'h2',
			function(e)
			{
				var screen_size = get_screen_size();
				if ( screen_size != 'sm' && screen_size != 'xs' )
				{
					return false;
				}

				var pWidth = $(this).innerWidth(); //use .outerWidth() if you want borders
				var pOffset = $(this).offset();
				var x = e.pageX - pOffset.left;

				$sidebar.hide();
				$('#status-{0}-tasks'.sprintf(status_id)).hide();

				var $sidebar_next = ('.col-status:first');

				if( pWidth/2 > x )
				{
					// left
					$sidebar_next = $sidebar.prev('.col-status');

					if ( $sidebar_next.length == 0 )
					{
						$sidebar_next = $('.col-status:last');
					}
				}
				else
				{
					// right
					var $sidebar_next = $sidebar.next('.col-status');

					if ( $sidebar_next.length == 0 )
					{
						$sidebar_next = $('.col-status:first');
					}
				}

				var next_status_id = $sidebar_next.attr('data-id');
				$sidebar_next.show();
				$('#status-{0}-tasks'.sprintf(next_status_id)).show();
				$('textarea.resize').autoresize({
					onResize: function()
					{
						$(this).addClass('autoresize');
					}
				})
				.trigger('keydown');
			}
		);




	}); // each sidebar
}; // $.fn.sidebar
})( jQuery );

