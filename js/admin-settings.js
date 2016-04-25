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
		$('.nav-tab[href="' + tab_id + '"]').addClass('nav-tab-active');
		$('.nav-tab').not('[href="' + tab_id + '"]').removeClass('nav-tab-active');
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



	$('.users-filter').on(
		'keyup',
		function()
		{
			var $input = $(this);

			var value = $input.val();
			var valueLower = $.trim( value.toLowerCase() );

			var $fieldset = $input.closest('fieldset');

			if ( valueLower == '' )
			{
				$('label:hidden', $fieldset).slideDown('fast');
				return false;
			}

			$('label', $fieldset).each(function()
			{
				var $label = $(this);
				var text = $label.text();
				var textLower = $.trim(text.toLowerCase() );

				if ( textLower.search(valueLower) > -1 ) //  && $label.is(':hidden')
				{
					$label.slideDown('fast');
				}
				else if ( $label.is(':visible') )
				{
					$label.slideUp('fast');
				}
			});
		}
	);



	$('.button-add-user').on(
		'click',
		function ()
		{
			var $form = $('#form-new-user');



			$('.error', $form).remove();

			var data = {
				'action': 'kanban_register_user',
			};

			$('input', $form).each(function()
			{
				var $input = $(this);
				data[$input.attr('id')] = $input.val();
			});



			$.post(
				ajaxurl,
				data
			)
			.done(function(response)
			{
				var form_class = 'error';
				var form_message = '';

				try
				{
					if ( response.success )
					{
						form_class = 'updated';
						form_message = 'User created and emailed.';
					}
					else
					{
						form_message = response.data.error;
					}
				}
				catch (err)
				{
					form_message = 'There was an error creating the user.';
				}

				if ( form_message != '' )
				{
					$('<div class="' + form_class + '"><p>' + form_message + '</p></div>').prependTo($form);
				}

			})
			.fail(function(jqXHR)
			{
				var form_class = 'error';
				var form_message = 'There was an error creating the user.';

				$('<div class="' + form_class + '"><p>' + form_message + '</p></div>').prependTo($form);
			});



			return false;
		}
	);

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