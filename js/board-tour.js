var board_tour = {
	$board: $('body'),
	current: 0,
	tour: [
		{
			el: '.col-status:eq(1)',
			content: "Each column, or swim lane, is a task status. We've added some sample statuses for you.",
			placement: 'bottom'
		},
		{
			el: '.col-tasks:eq(1) .task:first',
			content: "Tasks are the most important part of a kanban board. Let's look at how we interact with them.",
			placement: 'auto bottom'
		},
		{
			el: '.col-tasks:eq(1) .task:first .task-title',
			content: "Click on the task title to give your task a name. You can always click on the task title to update it.",
			placement: 'auto bottom'
		},
		{
			el: '.col-tasks:eq(1) .task:first .project_title',
			content: "To add your task to a project, click the project title. Type the name of a new project, and press enter. If you have previously added projects, you'll see a drop down you can choose from.",
			placement: 'auto bottom'
		}
	], // tour
	get_buttons: function()
	{
    	var tour_buttons = $('#t-tour-buttons').html();
    	var $tour_buttons = $(tour_buttons);
    	$('button', $tour_buttons).on(
    		'click',
    		function ()
    		{
    			var $btn = $(this);
    			var action = $btn.attr('data-action');

    			switch ( action )
    			{
    				case 'step':
	    				var direction = $btn.attr('data-direction');

	    				board_tour.step(direction);
	    				break;

	    			case 'hide':
	    				board_tour.hide();
	    				break;
    			}
    		}
    	);

    	return $tour_buttons;
	},
	show: function ()
	{

		var current = this.tour[this.current];

		if ( typeof current === 'undefined' )
		{
			this.current = 0;
			this.show();
			return false;
		}

		$(current.el)
		.popover({
			html: true,
			delay: 0,
			placement: current.placement,
			content: $('<p>'+ current.content + '</p>').add(this.get_buttons())
		})
		.popover('show');
	},
	step: function (direction)
	{
		var prev = this.tour[this.current];

		// hide previous
		$(prev.el).popover('destroy');

		this.current++;

		board_tour.show();
	},
	hide: function ()
	{
		var current = this.tour[this.current];
		$(current.el).popover('hide');
	}

}; // board_tour



