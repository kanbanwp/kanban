jQuery(function($)
{
	$(kanban.form_deactivate).appendTo('body');

	var $a = $('[data-slug="kanban"] .deactivate a');

	var url = $a.attr('href') + '';
	$a.attr('href', '#TB_inline?inlineId=kanban-deactivate-modal&modal=true').addClass('thickbox');



	$('body').on(
		'click',
		'.kanban-deactivate-remove',
		function ()
		{
			console.log('test');
			tb_remove();
		}
	);


	$('body').on(
		'click',
		'.kanban-deactivate-submit',
		function ()
		{
			var data = $('#kanban-deactivate-form').serialize();

			$(this).closest('form').get(0).reset();

			$.ajax({
				method: "POST",
				url: kanban.url_contact,
				data: data
			})
			.always(function(response)
			{
				window.location = url;
			});
		}
	);
	
});

