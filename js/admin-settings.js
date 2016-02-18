// (re)set positions with padding
function set_positions ()
{
	jQuery('.sortable').each(function()
	{
		jQuery('li', this).each(function(i)
		{
			// add zeros
			// @link http://stackoverflow.com/a/1699980
			var s = i+"";
			while ( s.length < 3 )
			{
				s = "0" + s;
			}

			// set position
			jQuery('.position', this).val(s);
		});
	});
}



jQuery(function($)
{
	function toggle_tabs (tab_id)
	{
		$('.nav-tab[href=' + tab_id + ']').addClass('nav-tab-active');
		$('.nav-tab').not('[href=' + tab_id + ']').removeClass('nav-tab-active');
		$('.tab').not(tab_id).hide();
		$(tab_id).show();
	}



	$('.nav-tab-wrapper a').on(
		'click',
		function()
		{
			var $a = $(this);
			var tab_id = $a.attr('href');

			toggle_tabs(tab_id);

			// update url hash without jump
			// @link http://stackoverflow.com/a/2598160/38241
			var id = tab_id.replace(/^.*#/, ''),
			elem = document.getElementById(id);
			elem.id = id+'-tmp';
			window.location.hash = tab_id;
			elem.id = id;

			return false;
		}
	);



	if ( typeof window.location.hash !== 'undefined' )
	{
		if ( window.location.hash !== '' )
		{
			toggle_tabs(window.location.hash);
		}
	}



	$('.color-picker').wpColorPicker();



	var t_status = new t($('#t-status').html());

	$('#add-status').on(
		'click',
		function()
		{
			// get count of new statuses
			var new_count = $('#list-statuses li.new').length;

			// render the new status
			var html = t_status.render();

			// add the status count
			html = html.replace(/\[count\]/g, '[' + new_count + ']');

			// append it
			var $html = $(html).addClass('new').appendTo('#list-statuses');

			// replace the names
			$('[data-name]', $html).each(function()
			{
				$(this).attr('name', $(this).attr('data-name') );
			});

			// activate color pickers
			$('.color-picker', $html).wpColorPicker();

			set_positions();
		}
	);

	$('#list-statuses').on(
		'click',
		'.delete',
		function()
		{
			$(this)
			.closest('li')
			.slideUp(
				'fast',
				function()
				{
					$(this).remove();
				}
			);
		}
	);



	var t_estimate = new t($('#t-estimate').html());

	$('#add-estimate').on(
		'click',
		function()
		{
			// get count of new estimates
			var new_count = $('#list-estimates li.new').length;

			// render the new estimate
			var html = t_estimate.render();

			// add the estimate count
			html = html.replace(/\[count\]/g, '[' + new_count + ']');

			// append it
			var $html = $(html).addClass('new').appendTo('#list-estimates');

			// replace the names
			$('[data-name]', $html).each(function()
			{
				$(this).attr('name', $(this).attr('data-name') );
			});

			// activate color pickers
			$('.color-picker', $html).wpColorPicker();

			set_positions();
		}
	);

	$('#list-estimates').on(
		'click',
		'.delete',
		function()
		{
			$(this)
			.closest('li')
			.slideUp(
				'fast',
				function()
				{
					$(this).remove();
				}
			);
		}
	);




	$('.sortable').sortable({
		// forceHelperSize: true,
		helper: 'clone', // fixHelper,
		items: 'li',
		handle: '.handle',
		stop: function()
		{
			set_positions ();
		}
	});
	// .disableSelection();


	// $('.group-order').each(function()
	// {
	// 	$('tr', this).each(function(i)
	// 	{
	// 		var $tr = $(this);
	// 		var $th = $('th', $tr);
	// 		var $input = $('input', $tr);
	// 		var val;
	// 		if ( $input.val() !== '' )
	// 		{
	// 			val = parseInt($('input', $tr).val());
	// 		}
	// 		else
	// 		{
	// 			val = i;
	// 			$input.val(i);
	// 		}

	// 		$('<span class="count"/>').text(val+1).appendTo($th);
	// 	}); // tr
	// }); // group-order
});