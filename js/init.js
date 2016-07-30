$(function()
{
	// make sure all ajax requests growl
	$(document).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions)
	{
		try
		{
			var response = $.parseJSON(XMLHttpRequest.responseText);
			growl_response_message(response);
		}
		catch (err) {}
	});



	//set scrollbar width
    parent = $('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo('body');
    child=parent.children();
    scrollbar_w=child.innerWidth()-child.height(99).innerWidth();
    parent.remove();

    // add scrollbar width to el
    $('.row-statuses-wrapper').css('marginRight', scrollbar_w);



    // init dom elements
    new Modal_Projects( $('#modal-projects') );



    // re-add previous view settings
	var view_classes = Cookies.get('view');
	if ( view_classes !== 'undefined' )
	{
		$('body').addClass(view_classes);
	}



	// when boards are done
	var populated_board_count = 0;

	$(document).bind(
		'/board/tasks/done/',
		function(event)
		{
			populated_board_count++;

			// boards popualted!
			if ( populated_board_count == Object.keys(boards).length )
			{
				// add board to url
				update_url();

				// hide loader
				$('#page-loading').remove();

				// show first board
				boards[current_board_id].$el.addClass('active');

				if ( typeof url_params['search'] !== 'undefined' )
				{
					$('#board-search').val(url_params['search']).trigger('keyup');
				}

				if ( typeof url_params['filters'] !== 'undefined' )
				{
					boards[current_board_id].record.filters = url_params['filters'];
					boards[current_board_id].apply_filters();
				}

			}
		}
	);



    // store all templates
	$('.template').each(function ()
	{
		var $t = $(this);
		var board_id = $t.attr('data-board-id');
		var basename = $t.attr('data-basename');

		if ( typeof templates[board_id] === 'undefined' )
		{
			templates[board_id] = {};
		}

		templates[board_id][basename] = new t( $t.html() );
	});



	// populate boards
	for ( var board_id in boards )
	{
		boards[board_id] = new Board(boards[board_id]);
	}



	// move to col
	if ( typeof url_params['col_index'] !== 'undefined' )
	{
		var board = boards[current_board_id];
		board.status_cols_toggle(url_params['col_index']);
	}



	$( window ).resize(function()
	{
		on_window_resize();
	});

	on_window_resize();



	// sidebar functionality
	var offset = 5; // minus offset to always show
	var col_tasks_sidebar_left = $('.col-tasks-sidebar-left').outerWidth() - offset;
	var col_tasks_sidebar_right = $('.col-tasks-sidebar-right').outerWidth() - offset;

	$(window).mousemove(function (e)
	{
		

		// to double the distance
		var clientX = Math.ceil((e.clientX/2) - (offset*2));

		if ( clientX < 0 )
		{
			clientX = 0;
		}

		if ( is_dragging )
		{
			$('.col-tasks-sidebar-left').css({
				left: '-' + col_tasks_sidebar_left + 'px',
				'opacity': '.618'
			});
		}
		else if ( clientX < (col_tasks_sidebar_left) )
		{
			$('.col-tasks-sidebar-left').css({
				left: -clientX + 'px',
				'opacity': '1'
			});
		}
		else if ( clientX > (col_tasks_sidebar_left) )
		{
			$('.col-tasks-sidebar-left').css({
				left: '-' + col_tasks_sidebar_left + 'px',
				'opacity': '.618'
			});
		}

		var clientX_r = window_w - e.clientX;
		var clientX_r = Math.ceil((clientX_r/2) - (offset*2));

		if ( clientX_r < 0 )
		{
			clientX_r = 0;
		}

		if ( is_dragging )
		{
			$('.col-tasks-sidebar-right').css({
				right: '-' + col_tasks_sidebar_right + 'px',
				'opacity': '.618'
			});
		}
		else if ( clientX_r < (col_tasks_sidebar_right) )
		{
			$('.col-tasks-sidebar-right').css({
				right: -clientX_r + 'px',
				'opacity': '1'
			});
		}
		else if ( clientX_r > (col_tasks_sidebar_right) )
		{
			$('.col-tasks-sidebar-right').css({
				right: '-' + col_tasks_sidebar_right + 'px',
				'opacity': '.618'
			});
		}

	});



	$('#btn-filter-modal-toggle').on(
		'click',
		function ()
		{
			$(this).attr('data-target', '#modal-filter-' + current_board_id);
		}
	);


	$('.btn-filter-reset')
	.on(
		'click',
		function ()
		{
			$('.btn-filter-reset').hide();

			var board = boards[current_board_id];

			// reset selects
			$('.modal-filter option', board.$el).prop('selected', function() {
				return this.defaultSelected;
			});

			for ( var field in board.record.filters )
			{
				board.record.filters[field] = null;
			}

			delete url_params.filters;
			update_url();

			$('.task', board.$el).slideDown();

			return false;
		}
	);


	$('#btn-view-compact').on(
		'click',
		function ()
		{
			$('body').toggleClass('board-view-compact');
			cookie_views();

			// make sure we redraw
			on_window_resize();
			return false;
		}
	);




	$('#btn-view-all-cols').on(
		'click',
		function ()
		{
			$('body').toggleClass('board-view-all-cols');
			cookie_views();
			return false;
		}
	);



	// http://stackoverflow.com/questions/1772035/filtering-a-list-as-you-type-with-jquery
	// http://stackoverflow.com/questions/177719/javascript-case-insensitive-search
	$('#board-search').on(
		'keyup',
		function()
		{
			var board = boards[current_board_id];

			var $input = $(this);
			var $list = $('.task', board.$el);

			var value = $input.val();
			var valueLower = $.trim( value.toLowerCase() );

			var $reset = $('#board-search-clear');

			// reset if search field is empty
			if ( valueLower.length === 0 )
			{
				delete url_params['search'];
				update_url();
				$list.slideDown('fast', function ()
				{
					all_match_col_h ();
					$reset.hide();
				});

				return false;
			}

			$reset.show();

			// update url
			url_params['search'] = valueLower;
			update_url();



			var $tasks_slideDown, $tasks_slideUp;

			$list.each(function()
			{
				var to_search = [];
				var $task = $(this);

				// loop over stored search selectors
				for ( var i in board.record.search )
				{
					to_search.push( eval(board.record.search[i]) );
				}

				var textLower = $.trim(to_search.join(' ').toLowerCase() );

				if ( textLower.search(valueLower) > -1 )
				{
					if ( typeof $tasks_slideDown == 'undefined' )
					{
						$tasks_slideDown = $task;
					}
					else
					{
						$tasks_slideDown = $tasks_slideDown.add($task);
					}
				}
				else
				{
					if ( typeof $tasks_slideUp == 'undefined' )
					{
						$tasks_slideUp = $task;
					}
					else
					{
						$tasks_slideUp = $tasks_slideUp.add($task);
					}
				}
			});

			if ( typeof $tasks_slideDown !== 'undefined' )
			{
				$tasks_slideDown.slideDown('fast');
			}

			if ( typeof $tasks_slideUp !== 'undefined' )
			{
				$tasks_slideUp.slideUp('fast');
			}

				$('.task').promise().done(function() {
					all_match_col_h ();
				});


			return false;
		}
	);

	$('#board-search-clear').on(
		'click',
		function ()
		{
			$('#board-search').val('').trigger('keyup');
			return false;
		}
	);



	$('#page-footer').on(
		'click',
		'.navbar-toggle',
		function ()
		{
			$('#page-footer').toggleClass('in');
		}
	);



	$("body").on(
		'keydown',
		function(e)
		{
			var $any_input = $('input:focus, textarea:focus, [contenteditable]:focus');

			var board = boards[current_board_id];

			// console.log(e.keyCode );



			// shift + <-: left
			if( e.keyCode === 37 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					if ( $('.col-tasks-sidebar-right', board.$el).hasClass('opened') )
					{
						$('.col-tasks-sidebar-right', board.$el).trigger('click');
					}
					else if ( !$('.col-tasks-sidebar-left', board.$el).hasClass('opened') )
					{
						$('.col-tasks-sidebar-left', board.$el).trigger('click');
					}
				}
			}

			// shift + ->: right
			if( e.keyCode === 39 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					if ( $('.col-tasks-sidebar-left', board.$el).hasClass('opened') )
					{
						$('.col-tasks-sidebar-left', board.$el).trigger('click');
					}
					else if ( !$('.col-tasks-sidebar-right', board.$el).hasClass('opened') )
					{
						$('.col-tasks-sidebar-right', board.$el).trigger('click');
					}
				}
			}



			// shift + c: toggle compact view
			if( e.keyCode === 67 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('#btn-view-compact').trigger('click');
					return false;
				}
			}



			// shift + a: toggle all cols
			if( e.keyCode === 65 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('#btn-view-all-cols').trigger('click');
					return false;
				}
			}



			// shift + s: jump to search
			if( e.keyCode === 83 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('#board-search').focus();
					return false;
				}
			}



			// shift + f: open filter modal
			if( e.keyCode === 70 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('.modal-filter', board.$el).modal('toggle');
					return false;
				}
			}



			// shift + p: open projects modal
			if( e.keyCode === 80 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('#modal-projects').modal('toggle');
					return false;
				}
			}



			// shift + k: open kaybord shortcuts modal
			if( e.keyCode === 75 && $any_input.length === 0 )
			{
				if ( e.shiftKey )
				{
					$('#modal-keyboard-shortcuts').modal('toggle');
					return false;
				}
			}
		}
	); // body keydown





	// if an alert is passed, show it
	if ( alert !== '' )
	{
		$.bootstrapGrowl(
			alert,
			{
				type: 'info',
				allow_dismiss: true
			}
		);
	}




	setInterval(function()
	{
		var data = {
			action: 'updates_task',
			datetime: js_date_to_mysql_dt(updates_dt),
			kanban_nonce: $('#kanban_nonce').val()
		};


		
		// now update time we last checked
		updates_dt = new Date();



		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function (response)
			{
				try
				{
					for ( var i in response.data.projects )
					{
						var project_record = response.data.projects[i];
						var board = boards[project_record.board_id];

						if ( project_record.is_active == 1 )
						{
							//add/update
							board.record.project_records[project_record.id] = project_record;

							// update the tasks
							for ( var i in board.record.tasks )
							{
								var task = board.record.tasks[i];

								if ( task.record.project_id == project_record.id )
								{
									// will only update the DOM
									task.project_save(project_record.id);
								}
							}

						}
						else
						{
							// remove project from projects
							delete board.record.project_records[project_record.id];

							// remove project from tasks
							for ( var i in board.record.tasks )
							{
								var task = board.record.tasks[i];
								if ( task.record.project_id == project_record.id )
								{
									task.project_update_title('');
								}
							}
						}
					}
				}
				catch (err) {}



				try
				{
					for ( var i in response.data.tasks )
					{
						var task_record = response.data.tasks[i];
						var board = boards[task_record.board_id];

						if ( task_record.is_active == 1 )
						{
							var task = board.record.tasks[task_record.id] = new Task(task_record);

							var $task = $('#task-{0}'.sprintf(task_record.id));

							// make sure they're not editing
							if ( $(':focus', $task).length > 0 )
							{
								continue;
							}

							// update existing
							if ( $task.length > 0 )
							{
								$('#task-{0}'.sprintf(task_record.id)).replaceWith(task.$el);
							}
							else 
							{
								// add new to the board
								task.add_to_board();
								board.update_UI();
							}
						}
						else 
						{
							var task = board.record.tasks[task_record.id];
							task.delete_el();
						}
					}
				}
				catch (err) {}
			}
		});

	}, 5000);


});




