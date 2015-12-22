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
				}
				else
				{
					$('body').removeClass('view-compact');
				}
			}
		);

	}); // each view
}; // $.fn.view
})( jQuery );

