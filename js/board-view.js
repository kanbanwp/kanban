(function( $ ) {
$.fn.board_view = function(task)
{
    return this.each(function()
    {
    	var $wrapper = $(this);


		$('[name=view-compact]', $wrapper).on(
			'change',
			function()
			{
				var $radio = $(this);
				if ( !$radio.is(':checked') )
				{
					return false;
				}

				if ( $radio.val() == 1)
				{
					$('body').addClass('view-compact');
					Cookies.set('kanban-board-view', 'compact', { expires: 30 });
				}
				else
				{
					$('body').removeClass('view-compact');
					Cookies.remove('kanban-board-view');
				}
			}
		);

	}); // each view
}; // $.fn.view
})( jQuery );

