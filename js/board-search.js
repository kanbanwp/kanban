(function( $ ) {
$.fn.board_search = function()
{
    return this.each(function()
    {
    	var $wrapper = $(this);



		// http://stackoverflow.com/questions/1772035/filtering-a-list-as-you-type-with-jquery
		// http://stackoverflow.com/questions/177719/javascript-case-insensitive-search
		$('#board-search', $wrapper).on(
			'keyup',
			function()
			{
				var $input = $(this);

				var value = $input.val();
				var valueLower = $.trim( value.toLowerCase() );

				var $list = $('.task');
				var $reset = $('#board-search-reset');

				if ( valueLower.length === 0 )
				{
					$list.slideDown('fast');
					$reset.hide();

					return false;
				}

				$reset.show();

				$list.each(function()
				{
					var $task = $(this);
					var text = $('.wrapper-task-title .task_title', $task).val();
					var textLower = $.trim(text.toLowerCase() );

					var task_id = $task.attr('data-id');

					if ( textLower.search(valueLower) > -1 || task_id == valueLower )
					{
						$task.slideDown('fast');
					}
					else
					{
						$task.slideUp('fast');
					}
				});

				return false;
			}
		);




		$('#board-search-reset', $wrapper).on(
			'click',
			function()
			{
				$('#board-search').val('').trigger('keyup');
			}
		);


	}); // each search
}; // $.fn.search
})( jQuery );

