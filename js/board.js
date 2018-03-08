function Board( board, delay ) {

	// Make sure there's a delay set.
	if ( null == delay || isNaN( delay ) ) {
		delay = 50;
	}

	$( document ).trigger( '/board/init/', board );

	this.record = board;
	this.$el = $( '#board-{0}'.sprintf( board.id() ) );

	// build current user
	var board_current_user = new User( this.record.allowed_users()[this.record.current_user_id()] );

	this.current_user = function () {
		return board_current_user;
	};

	this.update_UI_done = true;

	// Set up dom elements.
	this.dom();

	var self = this;
	setTimeout(
		function () {
			// put in order by vertical position
			var tasks_by_position = obj_order_by_prop( self.record.tasks, 'position', true );

			for ( var i in tasks_by_position ) {
				var task_record = tasks_by_position[i];
				var task = self.record.tasks[task_record.id] = new Task( self.record.tasks[task_record.id] );
				task.add_to_board();
			}

			self.update_UI();

			$( document ).trigger( '/board/tasks/done/', self );

		},
		delay
	);
}



Board.prototype.dom = function () {

	var self = this;



	$( document ).trigger( '/board/dom/', self.$el );



	self.$el.on(
		'click',
		'.col-tasks-sidebar',
		function ( e ) {
			if ( e.type == 'click' && kanban.is_dragging ) {
				return false;
			}

			var $sidebar = $( this );
			var $rows = $( '.row-statuses, .row-tasks', self.$el );

			if ( $rows.is( ':animated' ) ) {
				return false;
			}

			var left = $sidebar.attr( 'data-left' );
			var right = $sidebar.attr( 'data-right' );

			if ( $sidebar.hasClass( 'opened' ) ) {
				$sidebar.removeClass( 'opened' );
				left = right;
			}
			else {
				$sidebar.addClass( 'opened' );
			}

			// clear other sidebars
			$( '.col-tasks-sidebar', self.$el ).not( $sidebar ).removeClass( 'opened' );

			$rows.animate(
				{ marginLeft: left },
				300
			);


			return false;
		}
	);



	// filter
	self.$el.on(
		'change',
		'.modal-filter select',
		function () {
			var $select = $( this );
			// var $modal = $select.closest('.modal-filter');
			// var $tasks = $('.task', self.$el);
			// var $btn_reset = $('.btn-filter-reset').show();


			var field = $select.attr( 'data-field' );
			var val = $select.val();

			self.record.filters[field] = val;

			self.apply_filters();

			return false;
		}
	)
	.on(
		'show.bs.modal',
		'.modal-filter',
		function () {
			// populate projects
			var $modal = $( this );
			var $select = $( '.select-projects', $modal );
			var $first_option = $( 'option:first', $select );
			var $last_option = $( 'option:last', $select );

			// empty
			$( 'option', $select ).not( $first_option ).not( $last_option ).remove();

			for ( var project_id in self.record.project_records ) {
				var project = self.record.project_records[project_id];

				var project_html = kanban.templates[self.record.id()]['t-option-project'].render( project );

				$( project_html ).insertAfter( $first_option );

				// make sure project is selected
				self.apply_filters();
			}
		}
	); // filter



	// mobile buttons
	self.$el.on(
		'click',
		'.btn-status-toggle',
		function () {
			var $btn = $( this );
			var $col = $btn.closest( '.col' );
			var $cols = $col.siblings().andSelf();

			var operator = parseInt( $btn.attr( 'data-operator' ) );
			var col_index = $col.index() + operator;

			if ( col_index < 0 ) {
				col_index = $cols.length - 1;
			}

			if ( col_index >= $cols.length ) {
				col_index = 0;
			}

			self.status_cols_toggle( col_index );
		}
	);



	if ( !self.current_user().has_cap( 'write' ) ) {
		return false;
	}



	$( '.col-tasks', self.$el )
	.sortable( {
		connectWith: '.col-tasks',
		handle: ".task-handle",
		forcePlaceholderSize: true,
		forceHelperSize: true,
		placeholder: "task-placeholder",
		containment: $( '.row-tasks-wrapper', self.$el ),
		appendTo: "body",
		scroll: false,
		helper: "clone",
		start: function ( e, ui ) {
			$( '.dropdown.open', self.$el ).removeClass( 'open' ).closest( '.col-tasks.active' ).removeClass( 'active' );

			$( '.col-tasks-sidebar' ).css( {
				'left': '',
				'right': ''
			} );
			kanban.is_dragging = true;
		},
		// over: function (e, ui)
		// {
		// 	ui.placeholder.closest('.col-tasks').addClass('hover');
		// },
		// out: function (e, ui)
		// {
		// 	ui.placeholder.closest('.col-tasks').removeClass('hover');
		// },
		stop: function ( e, ui ) {
			self.update_task_positions( ui.item );
			kanban.is_dragging = false;
		},
		// update: function (e, ui)
		// {
		// },
		receive: function ( e, ui ) {
			var $col = ui.item.closest( '.col-tasks' );

			var task_id = ui.item.attr( 'data-id' );
			var task = self.record.tasks[task_id];

			var status_id_old = task.record.status_id;
			var status_old = self.record.status_records()[status_id_old];

			var status_id_new = $col.attr( 'data-status-id' );
			var status_new = self.record.status_records()[status_id_new];



			// just in case, use 0
			var wip_task_limit = 0;
			if ( 'undefined' !== typeof status_new.wip_task_limit ) {
				wip_task_limit = status_new.wip_task_limit;
			}

			if ( wip_task_limit > 0 ) {
				var status_task_count = $( '#status-' + status_id_new + ' .status-task-count' ).text();

				if ( status_task_count >= wip_task_limit ) {
					$( ui.sender ).sortable( 'cancel' );
					notify( kanban.text.status_wip_task_limit_error, 'warning' );
					return false;
				}
			}



			var comment = kanban.text.task_moved_to_status.sprintf(
				self.current_user().record().short_name,
				status_new.title
			);

			comment += kanban.text.task_moved_to_status_previous.sprintf(
				status_old.title
			);



			// if assigned to first is set
			if ( typeof self.record.settings().default_assigned_to_first !== 'undefined' ) {
				if ( self.record.settings().default_assigned_to_first == 1 && ( typeof task.record.user_id_assigned === 'undefined' || task.record.user_id_assigned == 0 ) ) {
					task.record.user_id_assigned = self.current_user().record().ID;

					task.update_assigned_to( self.current_user().record().ID );
				}
			}



			task.record.status_id = status_id_new;
			task.save( comment );

			self.record.tasks[task_id].update_status( status_id_new );

			self.update_UI();

		} // receive
	} );



	self.$el
	.on(
		'mouseenter',
		'.col-tasks',
		function () {
			var $col = $( this );
			var status_id = $col.attr( 'data-status-id' );
			$( '#status-' + status_id ).trigger( 'mouseenter' );

			return false;
		}
	)
	.on(
		'mouseleave',
		'.col-tasks',
		function () {
			var $col = $( this );
			var status_id = $col.attr( 'data-status-id' );
			$( '#status-' + status_id ).trigger( 'mouseleave' );

			return false;
		}
	)
	.on(
		'shown.bs.dropdown',
		'.col-tasks .dropdown',
		function () {
			var $dropdown = $( this );
			var $col = $dropdown.closest( '.col-tasks' );
			$col.addClass( 'active' );
			return false;
		}
	).on(
		'hidden.bs.dropdown',
		'.col-tasks .dropdown',
		function () {
			var $dropdown = $( this );
			var $col = $dropdown.closest( '.col-tasks' );
			$col.removeClass( 'active' );
			return false;
		}
	);



	self.$el
	.on(
		'mouseenter',
		'.col-status',
		function () {
			var $col = $( this );
			var status_id = $col.attr( 'data-id' );
			// $('#status-' + status_id + '-tasks').addClass('hover');

			// $col.addClass('hover');
			$( '.btn-group-status-actions', $col ).show();
			return false;
		}
	)
	.on(
		'mouseleave',
		'.col-status',
		function () {

			var $col = $( this );
			var status_id = $col.attr( 'data-id' );
			// $('#status-' + status_id + '-tasks').removeClass('hover');

			// $col.removeClass('hover');
			$( '.btn-group-status-actions', $col ).hide();
			return false;
		}
	);



	self.$el.on(
		'click',
		'.btn-task-new',
		function () {
			var $btn = $( this );

			// show spinner
			$( '.glyphicon', $btn ).toggle();

			// get status we're going to add it to
			var status_id = $btn.attr( 'data-status-id' );



			var status = self.record.status_records()[status_id];

			// just in case, use 0
			var wip_task_limit = 0;
			if ( 'undefined' !== typeof status.wip_task_limit ) {
				wip_task_limit = status.wip_task_limit;
			}

			if ( wip_task_limit > 0 ) {
				var status_task_count = $( '#status-' + status_id + ' .status-task-count' ).text();
				if ( status_task_count >= wip_task_limit ) {
					notify( kanban.text.status_wip_task_limit_error, 'warning' );
					return false;
				}
			}



			// start building placeholder task
			var task_data = {
				task: {
					status_id: status_id,
					board_id: self.record.id()
				},
				comment: '{0} added the task'.sprintf(
					self.current_user().record().short_name
				)
			};



			// if default estimate is set
			if ( typeof self.record.settings().default_estimate !== 'undefined' ) {
				// and default estimate exists 
				if ( typeof self.record.estimate_records()[self.record.settings().default_estimate] !== 'undefined' ) {
					task_data.task.estimate_id = self.record.settings().default_estimate;
				}
			}



			// if assigned to creator is set
			try {
				if ( self.record.settings().default_assigned_to_creator == 1 ) {
					task_data.task.user_id_assigned = self.current_user().record().ID;
				}
			}
			catch ( err ) {
			}



			// if default assigned to is set
			if ( typeof self.record.settings().default_assigned_to !== 'undefined' ) {
				// and default assigned to exists
				if ( typeof self.record.allowed_users()[self.record.settings().default_assigned_to] !== 'undefined' ) {
					var set = true;

					// don't set it if any of the other settings are set
					try {
						if ( self.record.settings().default_assigned_to_creator == 1 || self.record.settings().default_assigned_to_first == 1 ) {
							set = false;
						}
					}
					catch ( err ) {
					}

					if ( set ) {
						task_data.task.user_id_assigned = self.record.settings().default_assigned_to;
					}
				}
			}



			task_data.action = 'save_task';
			task_data.kanban_nonce = $( '#kanban_nonce' ).val();



			$.ajax( {
				method: "POST",
				url: kanban.ajaxurl,
				data: task_data
			} )
			.done( function ( response ) {
				$( '.glyphicon', $btn ).toggle();


				// just in case
				try {
					if ( !response.success ) {
						notify( kanban.text.task_added_error );
						return false;
					}


					if ( Object.keys( self.record.tasks ).length === 0 ) {
						self.record.tasks = {};
					}

					// add the task
					var task = self.record.tasks[response.data.task.id] = new Task( response.data.task );
					task.add_to_board();

					self.update_UI();

					self.update_task_positions();

					// Put the focus on the title.
					$( '.task-title', self.record.tasks[response.data.task.id].$el ).trigger( 'click' );
				}
				catch ( err ) {
				}


			} ); // done



			return false;
		}
	); // task_new



	self.$el.on(
		'click',
		'.btn-status-empty',
		function () {
			var r = window.confirm( kanban.text.status_empty_confirm );
			if ( r ) {
				var $btn = $( this );
				var status_id = $btn.attr( 'data-status-id' );

				// show spinner
				$( '.glyphicon', $btn ).toggle();

				for ( var task_id in self.record.tasks ) {
					var task = self.record.tasks[task_id];

					if ( task.record.status_id != status_id ) {
						continue;
					}

					task.delete();
				}

				// fake it
				setTimeout( function () {
					$( '.glyphicon', $btn ).toggle();
				}, 2000 );
			}
			return false;
		}
	);



	self.$el.on(
		'click',
		'.modal-task-move .list-group-item',
		function () {
			var $a = $( this );
			var $modal = $a.closest( '.modal' );
			var $task_id = $( 'input.task-id', $modal );
			var task_id = $task_id.val();
			var task = self.record.tasks[task_id];



			var status_id = $a.attr( 'data-status-id' );

			// If no change, return to close the modal.
			if ( task.record.status_id == status_id ) {
				return;
			}

			task.record.status_id = status_id;



			var board_id = $a.attr( 'data-board-id' );

			// If moved between boards.
			if ( task.record.board_id != board_id ) {
				// Store old board id for removing.
				var board_id_old = parseInt( task.record.board_id + '' );

				// Update task record.
				task.record.board_id = board_id;

				// Move task to new board.
				boards[board_id].record.tasks[task_id] = task;

				// Remove from old board.
				delete boards[board_id_old].record.tasks[task_id];
			}


			// Save updates to task.
			task.save();



			var $task = $( '#task-' + task_id );
			setTimeout( function () {
				$task.slideUp(
					'fast',
					function () {
						task.update_status( status_id );
						$( this ).prependTo( '#status-' + status_id + '-tasks' ).slideDown( 'fast', function () {

							// Update link to move modal, in case board changed.
							$( '.btn-task-move', this ).attr( 'data-target', '#modal-task-move-' + board_id );

							// Update col counts.
							self.update_UI();
						} );
					}
				);
			}, 300 );

			self.match_col_h();
			self.updates_status_counts();

			// return false;
		}
	);



	// prevent sortable on mobile
	$( window ).resize( function () {
		$( '.col-tasks', self.$el ).sortable( ( screen_size == 'xs' ? "disable" : "enable" ) );
	} );

	$( '.col-tasks', self.$el ).sortable( ( screen_size == 'xs' ? "disable" : "enable" ) );



}; // dom



// update the task count in each column header
Board.prototype.updates_status_counts = function () {
	// loop over each column
	$( '.col-tasks', this.$el ).each( function () {
		var $col = $( this );

		// get count of tasks
		var count = $( '.task', $col ).length;

		// get the status id
		var status_id = $col.attr( 'data-status-id' );

		// update count
		$( '#status-' + status_id + ' .status-task-count' ).text( count );
	} );
}; // updates_status_counts


/**
 * @link http://www.onemoretake.com/2009/02/25/sorting-elements-with-jquery/
 */
Board.prototype.update_card_order = function () {
	var self = this;

	$( '.col-tasks', this.$el ).each( function () {

		var mylist = $( this );
		var listitems = mylist.children('.task').get();
		listitems.sort(function(a, b) {
			var a_id = $(a).attr('data-id');
			var a_task = self.record.tasks[a_id];
			var a_pos = parseInt(a_task.record.position);

			var b_id = $(b).attr('data-id');
			var b_task = self.record.tasks[b_id];
			var b_pos = parseInt(b_task.record.position);

			return (a_pos < b_pos) ? -1 : (a_pos > b_pos) ? 1 : 0;
		})
		$.each(listitems, function(idx, itm) { mylist.append(itm); });

	} );
}; // updates_status_counts



Board.prototype.update_task_positions = function ( $el_moved ) {
	var self = this;

	$( '.col-tasks', this.$el ).each( function () {
		var $col = $( this );
		$( '.task', $col ).each( function ( i ) {
			var $task = $( this );

			var do_comment = false;
			if ( $task.is( $el_moved ) ) {
				do_comment = true;
			}

			var task_id = $task.attr( 'data-id' );
			var task = self.record.tasks[task_id];

			// Stagger the requests.
			setTimeout(function () {
				task.update_position( ("00000" + i).slice( -5 ), do_comment );
				},
				i * 100
			);
		} );
	} );

	// growl('Order updated');
}; // updates_status_counts



Board.prototype.match_col_h = function () {
	$( '.col-tasks', this.$el ).matchHeight( {
		minHeight: window_h
	} );
}; // match_col_h



Board.prototype.apply_filters = function () {
	var self = this;

	var selector = [];

	var show_reset = false;

	// build selector and hash for TASKS TO HIDE, to support multiple filtesr
	for ( var field in this.record.filters ) {
		var filter_id = this.record.filters[field];

		if ( filter_id === null || filter_id === '' ) {
			continue;
		}

		show_reset = true;

		for ( var task_id in this.record.tasks ) {
			var task = this.record.tasks[task_id];
			if ( task.record[field] != filter_id ) // NOT, so we're hiding tasks
			{
				selector.push( '#task-' + task_id );
			}
		}

		// make sure select is selected
		$( '.modal-filter select[data-field="{0}"]'.sprintf( field ), this.$el ).val( filter_id );
	}

	if ( show_reset ) {
		$( '.btn-filter-reset' ).show();
	}
	else {
		$( '.btn-filter-reset' ).hide();
	}

	var $tasks_to_hide = $( selector.join( ',' ) );

	// show/hide tasks
	$tasks_to_hide.slideUp( 'fast' );
	$( '.task' ).not( $tasks_to_hide ).slideDown( 'fast' );

	$( '.task', this.$el ).promise().done( function () {
		self.match_col_h();
	} );

	kanban.url_params = $.extend( kanban.url_params, { filters: this.record.filters } );
	update_url();
}; // apply_filters



Board.prototype.project_update_counts = function () {
	// build counts of projects
	var counts = [];

	for ( var task_id in this.record.tasks ) {
		var task = this.record.tasks[task_id];

		if ( typeof task.record.project_id === 'undefined' ) {
			continue;
		}

		if ( typeof counts[task.record.project_id] === 'undefined' ) {
			counts[task.record.project_id] = 0;
		}

		counts[task.record.project_id]++;
	}

	// populate projects
	for ( var project_id in this.record.project_records ) {
		if ( typeof counts[project_id] !== 'undefined' ) {
			this.record.project_records[project_id].task_count = counts[project_id];
		}
		else {
			this.record.project_records[project_id].task_count = 0;
		}
	}
}; // project_update_counts



Board.prototype.update_UI = function () {
	var self = this;

	if ( self.update_UI_done ) {
		self.update_UI_done = false;

		this.update_card_order();
		this.updates_status_counts();
		this.match_col_h();

		setTimeout(function() {
			self.update_UI_done = true;
		}, 5000);
	};
}; // update_UI



Board.prototype.status_cols_toggle = function ( col_index ) {
	kanban.url_params.col_index = col_index;
	update_url();

	$( '.row-statuses, .row-tasks', this.$el ).each( function () {
		var $row = $( this );
		var $cols = $( '> .col', $row );
		var $col_to_show = $cols.eq( col_index ).show();
		$cols.not( $col_to_show ).hide();
	} );
}; // status_cols_toggle




Board.prototype.status_cols_show_all = function ( ) {
	$( '.row-statuses, .row-tasks', this.$el ).each( function () {
		var $row = $( this );
		var $cols = $( '> .col', $row );
		$cols.show();
	} );
}; // status_cols_toggle


Board.prototype.get_current_board_id = function () {
	return parseInt( current_board_id );
}; // get_current_board_id




Board.prototype.get_current_board = function () {
	var board_id = Board.prototype.get_current_board_id();

	if ( typeof boards[board_id] !== 'undefined') {
		return boards[board_id];
	}

	return false;
}; // get_current_board
