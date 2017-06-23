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



	$('.nav-tab').on(
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

	$('.slide-toggle').on(
		'click',
		function () {
			var id = $(this).attr('href');
			$(id).slideToggle('fast');
			return false;
		}
	);



	$('.button-sortable-add').on(
		'click',
		function()
		{
			var $button = $(this);
			var template = $button.attr('data-t');

			var t_template = new t($('#' + template).html());

			var $tab = $button.closest('.tab');
			var $list = $('.sortable', $tab);

			// get count of new estimates
			var new_count = $('li.new', $list).length;

			// render the new estimate
			var html = t_template.render();

			// add the estimate count
			html = html.replace(/\[count\]/g, '[' + new_count + ']');

			// append it
			var $html = $(html).addClass('new').appendTo($list);

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



	$('.sortable').on(
		'click',
		'.delete',
		function()
		{
			var $button = $(this);

			var confirm_msg = $button.attr('data-confirm');
			var r = false;
			if ( 'undefined' !== typeof confirm_msg && '' !== confirm_msg )
			{
				var r = confirm(confirm_msg);
			}

			if ( 'undefined' === typeof confirm_msg || '' == confirm_msg || r == true ) {
				$button
				.closest( 'li' )
				.slideUp(
					'fast',
					function () {
						$( this ).remove();
					}
				);
			}
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


	$('.check-list-wrapper').each(function () {
		new List(this, {
			valueNames: ['user_name'],
			page: 20,
			pagination: {
				name: "list-pagination",
				paginationClass: "list-pagination",
				innerWindow: 3
			}
		})
	});

	$('.check-list').on(
		'change',
		'.check-list-input',
		function(e) {
			var $input = $(this);
			var $list = $(e.delegateTarget);
			var target = $list.attr('data-target');
			var name = $list.attr('data-name');
			var value = $input.val();
			var $target = $(target);

			var id = $input.val();

			if ( $input.is(':checked') ) {
				var html = $('<input type="hidden">');

				$( html )
					.attr('name', name)
					.val(value)
					.appendTo($target);
			}
			else {
				$('[value="' + id + '"]', $target).remove();
			}
		}
	);



	//
	//
	// $('.users-filter').on(
	// 	'keyup',
	// 	function()
	// 	{
	// 		var $input = $(this);
	//
	// 		var value = $input.val();
	// 		var valueLower = $.trim( value.toLowerCase() );
	//
	// 		var $fieldset = $input.closest('fieldset');
	//
	// 		if ( valueLower == '' )
	// 		{
	// 			$('label:hidden', $fieldset).slideDown('fast');
	// 			return false;
	// 		}
	//
	// 		$('label', $fieldset).each(function()
	// 		{
	// 			var $label = $(this);
	// 			var text = $label.text();
	// 			var textLower = $.trim(text.toLowerCase() );
	//
	// 			if ( textLower.search(valueLower) > -1 ) //  && $label.is(':hidden')
	// 			{
	// 				$label.slideDown('fast');
	// 			}
	// 			else if ( $label.is(':visible') )
	// 			{
	// 				$label.slideUp('fast');
	// 			}
	// 		});
	// 	}
	// );



	$('.button-add-user').on(
		'click',
		function ()
		{
			var $form = $('#form-new-user');



			$('.error', $form).remove();

			var data = {
				'action': 'kanban_register_user'
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



	$('#default_assigned_switch').on(
		'change',
		'[type="checkbox"]',
		function(e)
		{
			var $switch = $(e.delegateTarget);
			$(':checked', $switch).not(this).prop('checked', false);

			if ( $('#default_assigned_to').is(':checked') && $('#tr-default_assigned_to').not(':visible') )
			{
				$('#tr-default_assigned_to').show();
			}
			else if ( $('#tr-default_assigned_to').is(':visible') )
			{
				$('#tr-default_assigned_to').hide();
				$('#default_assigned_to_select').val($("#default_assigned_to_select option:last").val());
			}

		}
	);


	$('#button-load-diagnostic-info').on(
		'click',
		function ()
		{
			var data = {
				'action': 'kanban_diagnostic_info'
			};

			$.get(
				ajaxurl,
				data
			)
			.done(function(response)
			{
				$('#kanban-diagnostic-info').val('Sent!' + "\n\n" + response);

				var data = {
					request: 'diagnostic info for ' + kanban.url_board,
					message: 'diagnostic info:' + "\n\n" + response,
					kanban_nonce: kanban.kanban_nonce
				};

				$.ajax({
					method: "POST",
					url: kanban.url_contact,
					data: data
				})
			});

			return false;
		}
	);



});


