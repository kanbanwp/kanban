function Task( task ) {
	this.record = task;
	this.build_el();
}


Task.prototype.board = function () {
	return boards[this.record.board_id];
};


Task.prototype.add_to_board = function () {
	var $col = $( '#status-{0}-tasks'.sprintf( this.record.status_id ) );
	this.$el.prependTo( $col );
}


Task.prototype.build_el = function () {
	this.record.status = this.board().record.status_records()[this.record.status_id] ? this.board().record.status_records()[this.record.status_id] : {};
	this.record.project = this.board().record.project_records[this.record.project_id] ? this.board().record.project_records[this.record.project_id] : {};
	this.record.estimate = this.board().record.estimate_records()[this.record.estimate_id] ? this.board().record.estimate_records()[this.record.estimate_id] : {};
	this.record.user_assigned = this.board().record.allowed_users()[this.record.user_id_assigned] ? this.board().record.allowed_users()[this.record.user_id_assigned] : {};
	this.record.hour_count_formatted = format_hours( this.record.hour_count );

	var task_html = kanban.templates[this.board().record.id()]['t-task'].render( {
		task: this.record,
		estimate_records: obj_order_by_prop( this.board().record.estimate_records(), 'position' ),
		project_records: this.board().record.project_records,
		allowed_users: this.board().record.allowed_users(),
		current_user_can_write: this.board().current_user().has_cap( 'write' ),
		show_task_ids: this.board().record.settings().show_task_ids == 1 ? true : false
	} );


	this.$el = $( task_html );

	encode_urls_emails( $( '.task-title', this.$el ) );

	this.dom();

	$( document ).trigger( '/task/added/', this );
}; // add


Task.prototype.dom = function () {
	var self = this;

	self.update_progress();


	if ( !self.board().current_user().has_cap( 'write' ) ) {
		return false;
	}


	self.$el.on(
		'click',
		'.btn-task-delete',
		function () {
			var $btn = $( this );
			$( '.glyphicon', $btn ).show();

			self.delete();

			return false;
		}
	);



	self.$el.on(
		'click',
		'.btn-task-copy',
		function () {
			var $btn = $( this );

			$btn.closest('.dropdown').removeClass('open');

			// get status we're going to add it to
			var status_id = self.record.status_id;

			var status = self.board().record.status_records()[status_id];

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

			var task_data = {
				task: self.record,
				action: 'copy_task',
				kanban_nonce: $( '#kanban_nonce' ).val()
			};

			$.ajax( {
				method: "POST",
				url: kanban.ajaxurl,
				data: task_data
			} )
			.done( function ( response ) {
				// just in case
				try {
					if ( !response.success ) {
						notify( kanban.text.task_added_error );
						return false;
					}

					if ( Object.keys( self.board().record.tasks ).length === 0 ) {
						self.board().record.tasks = {};
					}

					// add the task
					var task = self.board().record.tasks[response.data.task.id] = new Task( response.data.task );

					task.add_to_board();
					task.board().update_UI();
					task.board().update_task_positions();
				}
				catch ( err ) {
				}

			} ); // done



			return false;
		}
	); // task_copy



	self.$el
	.on(
		'shown.bs.dropdown',
		'.dropdown',
		function () {
			var $dropdown = $( this );
			$( '.task.active', self.board().$el ).not( self.$el ).removeClass( 'active' );
			self.$el.addClass( 'active' );
		}
	)
	.on(
		'hidden.bs.dropdown',
		function () {
			self.$el.removeClass( 'active' );
		}
	);



	self.$el
	.on(
		'show.bs.dropdown',
		'.task-project',
		function () {
			var $project = $( this );
			var $dropdown = $( '.dropdown-menu', $project ).empty();

			var project_count = 0;
			for ( var project_id in self.board().record.project_records ) {
				var project = self.board().record.project_records[project_id];

				var project_html = kanban.templates[self.board().record.id()]['t-task-project-dropdown'].render( project );

				$( project_html ).prependTo( $dropdown );

				project_count++;
			}

			if ( project_count == 0 ) {
				return false;
			}
		}
	)
	.on(
		'hide.bs.dropdown',
		'.task-project',
		function () {
			var $project = $( this );
			var $contenteditable = $( '[contenteditable]', $project );
			if ( $contenteditable.is( ':focus' ) ) {
				return false;
			}
		}
	)
	.on(
		'keydown',
		'.task-project [contenteditable]',
		function ( e ) {
			var $div = $( this );
			var $dropdown = $div.closest( '.dropdown' );

			// escape
			if ( e.keyCode === 27 ) {
				// get prev value
				var orig = $div.data( 'orig' );

				// restore prev value
				$div.html( orig );

				// trigger save
				$div.blur();

				// prevent save
				clearTimeout( $div.data( 'save_timer' ) );

				if ( $dropdown.hasClass( 'open' ) ) {
					$dropdown.removeClass( 'open' )
				}

				return;
			}


			// enter
			if ( e.keyCode === 13 ) {
				$div.blur();

				if ( $dropdown.hasClass( 'open' ) ) {
					$dropdown.removeClass( 'open' )
				}

				return;
			}


			// allow space (undermine bootstrap behavior)
			if ( e.keyCode === 32 ) {
				$div.html( $div.html() + '&nbsp;' );
				placeCaretAtEnd( $div.get( 0 ) );
				return false;
			}


			// allow arrow keys to select project
			// if (e.keyCode === 40 || e.keyCode === 38)
			// {
			// 	placeCaretAtEnd($div.get(0));
			// 	e.preventDefault();
			// }


		}
	)
	// .on(
	// 	'keyup',
	// 	'.task-project [contenteditable]',
	// 	function (e)
	// 	{
	// 		var $div = $(this);
	//
	// 		var $task_project = $div.closest('.task-project');
	// 		var $list = $('.dropdown-menu li', $task_project);
	// 		var valueLower = $.trim($div.text().toLowerCase());
	//
	//
	//
	// 		$list.each(function()
	// 		{
	// 			var $li = $(this);
	//
	// 			var textLower = $.trim($li.text().toLowerCase());
	//
	// 			if ( textLower.search(valueLower) > -1 ) //  && $li.is(':visible')
	// 			{
	// 				console.log(valueLower, textLower, 'found');
	// 				$li.slideDown('fast');
	// 			}
	// 			else // if ( !$li.is(':visible') )
	// 			{
	// 				console.log(valueLower, textLower, 'not found');
	// 				$li.slideUp('fast');
	// 			}
	// 		});
	//
	//
	// 		placeCaretAtEnd($div.get(0));
	// 	}
	// )
	.on(
		'blur',
		'.task-project [contenteditable]',
		function () {
			window.getSelection().removeAllRanges();

			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			var save_timer = setTimeout( function () {
				self.parse_project();
			}, 100 );

			$( this ).data( 'save_timer', save_timer );
		}
	)
	.on(
		'focus',
		'.task-project [contenteditable]',
		function () {
			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			var $div = $( this );

			$div.data( 'orig', $div.text() );
		}
	); // task-project;



	self.$el.on(
		'click',
		'.btn-project-assign',
		function () {
			var $btn = $( this );
			var $dropdown = $btn.closest( '.dropdown' );
			var $contenteditable = $( '[contenteditable]', $dropdown );

			//prevent save on blur
			clearTimeout( $contenteditable.data( 'save_timer' ) );

			var project_id = $btn.attr( 'data-id' );
			self.project_save( project_id );
		}
	);



	self.$el
	.on(
		'click',
		'.task-title',
		function ( e ) {

			// Let a link be a link.
			if ( $( e.target ).is( 'a' ) ) {
				return;
			}

			// If they can't edit, return.
			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			// Get the field.
			var $div = $( this );

			// Make it editable. Maybe.
			$div.attr( 'contenteditable', $div.attr( 'data-contenteditable' ) );

			// If it's not editable.
			if ( $div.attr( 'contenteditable' ) != 'true' ) {
				return false;
			}

			// Store the original for restoring.
			$div.data( 'orig', $div.html() );

			// Remove html.
			strip_tags( $div );

			// Focus on it.
			$div.focus();
		}
	)
	.on(
		'keydown',
		'.task-title',
		function ( e ) {
			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			var $div = $( this );

			// escape
			if ( e.keyCode === 27 ) {
				// get prev value
				var orig = $div.data( 'orig' );

				// restore prev value
				$div.html( orig );

				// trigger save
				$div.blur();

				return;
			}

			// enter
			if ( e.keyCode === 13 && !e.shiftKey ) {
				$div.blur();
				return;
			}
		}
	)
	.on(
		'keyup',
		'.task-title',
		function () {
			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			var $div = $( this );

			// delete prev timer
			clearTimeout( $div.data( 'save_timer' ) );

			// set new timer
			var save_timer = setTimeout( function () {
				self.save_title();
			}, 3000 );

			$div.data( 'save_timer', save_timer );
		}
	)
	.on(
		'blur',
		'.task-title',
		function () {
			window.getSelection().removeAllRanges();

			var $div = $( this );

			// (Re)encode links.
			encode_urls_emails( $div );

			// Make it non-editable.
			$div.removeAttr( 'contenteditable' );

			if ( !self.board().current_user().has_cap( 'write' ) ) {
				return false;
			}

			self.save_title();
		}
	); // task-title



	self.$el
	.on(
		'mouseenter',
		'.btn-task-action',
		function () {
			var $btn = $( this );
			var $dropdown = $btn.closest( '.dropdown' );

			if ( $dropdown.is( '.open' ) ) {
				return false;
			}

			var timer_open = setTimeout( function () {
				if ( $dropdown.is( '.open' ) ) {
					return false;
				}

				$btn.trigger( 'click' );
			}, 500 );

			$btn.data( 'timer-open', timer_open );
		}
	)
	.on(
		'mouseleave',
		'.btn-task-action',
		function () {
			var $btn = $( this );
			var $dropdown = $btn.closest( '.dropdown' );

			clearTimeout( $btn.data( 'timer-open' ) );

			if ( $dropdown.is( '.open' ) ) {
				var timer_close = setTimeout( function () {
					$dropdown.removeClass( 'open' ); // dropdown('toggle');

				}, 500 );
				$dropdown.data( 'timer-close', timer_close );
			}
		}
	).on(
		'mouseenter',
		'.dropdown-menu',
		function () {
			var $menu = $( this );
			var $dropdown = $menu.closest( '.dropdown' );
			clearTimeout( $dropdown.data( 'timer-close' ) );
		}
	);


	self.$el
	.on(
		'show.bs.dropdown',
		'.row-task-actions .dropdown',
		function ( e ) {
			var $relatedTarget = $( e.relatedTarget );
			var $dropdown = $relatedTarget.closest( '.dropdown' );
			var $menu = $( '.dropdown-menu', $dropdown );

			$menu.css( 'maxWidth', self.$el.outerWidth() );
		}
	);


	self.$el.on(
		'click',
		'.btn-task-move',
		function () {
			var $btn = $( this );

			var modal = $btn.attr( 'data-target' );
			var $modal = $( modal );

			$( '.task-id', $modal ).val( self.record.id );
			$( '.task-title', $modal ).html( self.record.title );

		}
	);


	self.$el.on(
		'click',
		'.btn-task-estimate',
		function () {
			var $a = $( this );

			var estimate_id = $a.attr( 'data-id' );
			var estimate = self.board().record.estimate_records()[estimate_id];


			// build comment
			var comment = kanban.text['task_estimate_updated'].sprintf(
				self.board().current_user().record().short_name,
				estimate.title
			);

			if ( typeof self.board().record.estimate_records()[self.record.estimate_id] !== 'undefined' ) {
				var old_estimate = self.board().record.estimate_records()[self.record.estimate_id];

				// don't save if same as it was
				if ( estimate_id == self.record.estimate_id ) {
					return true;
				}

				comment += kanban.text['task_estimate_updated_previous'].sprintf(
					old_estimate.title
				);
			}


			// update and  save with comment
			self.record.estimate_id = estimate_id;
			self.save( comment );


			// update UI
			$( '.task-estimate', self.$el ).html( estimate.title );
			self.update_progress();
		}
	);


	self.$el.on(
		'click',
		'.btn-task-assigned',
		function () {
			var $a = $( this );

			var user_id = $a.attr( 'data-id' );

			// don't save if same user
			if ( user_id == self.record.user_id_assigned ) {
				return true;
			}

			var user = self.board().record.allowed_users()[user_id];


			// build comment
			var comment = kanban.text['task_assigned_to'].sprintf(
				self.board().current_user().record().short_name,
				user.short_name
			);

			if ( typeof self.board().record.allowed_users()[self.record.user_id_assigned] !== 'undefined' ) {
				var old_user = self.board().record.allowed_users()[self.record.user_id_assigned];

				// don't save if assigned to the same person
				if ( user_id == self.record.user_id_assigned ) {
					return true;
				}

				comment += kanban.text['task_assigned_to_previous'].sprintf(
					old_user.short_name
				);
			}


			// update and  save with comment
			self.record.user_id_assigned = user_id;
			self.save( comment );


			self.update_assigned_to( user_id );
		}
	);


	self.$el.on(
		'click',
		'.btn-task-hour',
		function () {
			var $btn = $( this );

			var operator = $btn.attr( 'data-operator' );

			if ( operator != '+' && operator != '-' ) {
				return false;
			}

			var current = typeof self.record.hour_count !== 'undefined' ? parseFloat( self.record.hour_count ) : 0;
			var interval = self.board().record.settings().hour_interval;

			// increase/decrease hours
			current = eval( current + operator + interval );

			// round to thousandth place
			// @link http://stackoverflow.com/a/11832950/38241
			current = Math.round( current * 1000 ) / 1000;

			if ( current < 0 ) {
				current = 0;
			}

			// update the total count
			self.record.hour_count = current;

			$( '.task-hours', self.$el ).html( format_hours( self.record.hour_count ) );

			self.update_progress();
			self.log_work_hour( operator );

			return false;
		}
	);


	self.$el
	.on(
		'mousedown',
		'.btn-task-hour',
		function () {
			var $div = $( this );

			// wait 500 ms
			var timer = setInterval( function () {
				// clear 500ms
				clearInterval( $div.data( 'click_timer' ) );

				// click every 100ms
				var timer = setInterval( function () {
					$div.trigger( 'click' );
				}, 100 );

				$div.data( 'click_timer', timer );

			}, 500 );

			$div.data( 'click_timer', timer );
		}
	)
	.on(
		'mouseup mouseleave',
		'.btn-task-hour',
		function () {
			var $div = $( this );
			clearInterval( $div.data( 'click_timer' ) );
		}
	);

}; // dom


Task.prototype.update_progress = function () {
	// update progress bar
	var progress_percent = 0;
	if ( typeof this.record.estimate_id === 'undefined' && typeof this.record.hour_count === 'undefined' ) {
		return;
	}

	var estimate = this.board().record.estimate_records()[this.record.estimate_id];

	if ( typeof estimate === 'undefined' ) {
		return;
	}

	progress_percent = (parseFloat( this.record.hour_count ) * 100) / parseFloat( estimate.hours );

	var progress_type = 'success';
	if ( progress_percent > 133 ) {
		progress_type = 'danger';
	}
	else if ( progress_percent > 100 ) {
		progress_type = 'warning';
	}

	if ( progress_percent > 100 ) {
		progress_percent = 100;
	}

	$( '.progress-bar', this.$el )
	.css( {
		width: progress_percent + '%'
	} )
	.removeClass( 'progress-bar-success progress-bar-warning progress-bar-danger' )
	.addClass( 'progress-bar-' + progress_type );
}



Task.prototype.save = function ( comment, do_growl ) {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}

	var self = this;

	var record = JSON.parse(JSON.stringify(this.record));
	delete record.comments;

	var task_data = {
		task: record,
		action: 'save_task',
		kanban_nonce: $( '#kanban_nonce' ).val()
	};


	if ( typeof comment !== 'undefined' ) {
		if ( comment !== null && comment !== '' ) {
			task_data.comment = comment;
		}
	}


	// If growl is not set to true, then ajax will not return a message to growl
	if ( typeof do_growl === 'undefined' ) {
		do_growl = true;
	}

	task_data.message = do_growl;



	$.ajax( {
		method: "POST",
		url: kanban.ajaxurl,
		data: task_data
	} )
	.done( function ( response ) {
		if ( !response.success ) {
			notify( kanban.text.task_save_error );
			return false;
		}

		self.board().update_UI();
	} );

}; // save



Task.prototype.delete = function () {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}

	var self = this;
	this.record.is_active = 0;

	var task_data = {
		task: this.record,
		action: 'delete_task',
		kanban_nonce: $( '#kanban_nonce' ).val(),
		comment: kanban.text['task_deleted'].sprintf(
			self.board().current_user().record().short_name
		)
	};

	$.ajax( {
		method: "POST",
		url: kanban.ajaxurl,
		data: task_data
	} )
	.done( function ( response ) {
		if ( !response.success ) {
			notify( kanban.text.task_delete_error );
			return false;
		}

		$( document ).trigger( '/task/deleted/', self );
	} );

	self.delete_el();

}; // delete



Task.prototype.delete_el = function ( do_undelete ) {

	if ( typeof do_undelete === 'undefined' ) {
		do_undelete = true;
	}

	var self = this;
	self.$el.slideUp( 'fast', function () {

		if ( do_undelete ) {
			self.undelete_el();
		}

		self.$el.remove();

		self.board().update_UI();
		self.board().update_task_positions();
		delete self.board().record.tasks[self.record.id];
	} );

};



Task.prototype.undelete_el = function () {
	var self = this;

	var $alert = $(
		'<div class="alert alert-warning alert-task-restore" id="task-{0}-restore" role="alert" data-id="{1}">{2}</div>'.sprintf(
			self.record.id,
			self.record.id,
			kanban.text.task_restore
		)
	)
	.insertAfter( self.$el );

	setTimeout( function () {
		$alert.slideUp( 'fast', function () {
			$alert.remove();
		} );
	}, 8000 );

	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}

	$alert.on(
		'click',
		function () {

			var task_id = $alert.attr( 'data-id' );

			// Replace undo with spinner.
			$alert.html('<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>');

			var task_data = {
				task: {
					id: task_id
				},
				action: 'undelete_task',
				kanban_nonce: $( '#kanban_nonce' ).val(),
				comment: kanban.text['task_undeleted'].sprintf(
					self.board().current_user().record().short_name
				)
			};

			$.ajax( {
				method: "POST",
				url: kanban.ajaxurl,
				data: task_data
			} )
			.done( function ( response ) {
				if ( !response.success ) {
					// notify( kanban.text.task_delete_error );
					return false;
				}

				// force refresh
				kanban.updates_task();

				// If it hasn't happened already, remove it now.
				$alert.remove();

				$( document ).trigger( '/task/undeleted/', task_id );

				// self.delete_el();
			} );
		}
	);
};


// Task.prototype.update_board = function()
// {
// 	this.board().updates_status_counts();
// 	this.board().match_col_h ();
// };



Task.prototype.update_status = function ( status_id ) {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}

	var status = this.board().record.status_records()[status_id];

	$( '.task-handle', this.$el ).css( {
		'background-color': status.color_hex
	} );
}



Task.prototype.update_position = function ( position, do_comment ) {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}



	if ( position == this.record.position ) {
		return false;
	}



	// make sure it's a bool
	do_comment = do_comment === 'true' || do_comment === true ? true : false;



	position = parseInt( position );



	var comment = null;

	if ( do_comment ) {

		// store prev pos
		var prev_pos = this.record.position + '';

		var comment = kanban.text['task_moved_to_position'].sprintf(
			this.board().current_user().record().short_name,
			position
		);

		if ( prev_pos !== '' ) {
			comment += kanban.text['task_moved_to_position_previous'].sprintf(
				parseInt( prev_pos )
			);
		}
	}



	this.record.position = position;
	this.save( comment, false );
}



Task.prototype.update_assigned_to = function ( user_id ) {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}


	var self = this;


	var user = self.board().record.allowed_users()[user_id];


	// update UI
	self.$el.attr( 'data-user_id-assigned', user_id );

	var $initials = $( '.task-assigned-initials', self.$el ).removeClass( 'empty' );

	if ( typeof user.avatar !== 'undefined' ) {
		$initials.html( user.avatar );
	}
	else {
		$initials.text( user.initials );
	}

	$( '.task-assigned-name', self.$el ).text( user.short_name );
}



Task.prototype.save_title = function () {
	var $div = $( '.task-title', this.$el );

	// prevent saving twice, if they blur instead of timeout
	clearTimeout( $div.data( 'save_timer' ) );

	// store prev title
	var prev_title = this.record.title + '';



	// clean up html
	sanitize( $div );
	var new_title = $div.html().replace( /\\/gi, '&#92;' ).replace( /&nbsp;/gi, ' ' );



	// now that we have the title
	// don't encode if we're focused on an input
	if ( !$div.is( ':focus' ) ) {

		// (Re)encode links.
		encode_urls_emails( $div );

		// Make it non-editable.
		$div.removeAttr( 'contenteditable' );
	}



	// don't save if there's no change
	if ( new_title === prev_title ) {
		return false;
	}



	var comment = kanban.text['task_title_updated'].sprintf(
		this.board().current_user().record().short_name,
		new_title
	);

	if ( prev_title !== '' ) {
		comment += kanban.text['task_title_updated_previous'].sprintf(
			prev_title
		);
	}

	if ( new_title === '' ) {
		comment = null;
	}

	this.record.title = new_title;
	this.save( comment );

}; // save_title



Task.prototype.project_update_title = function ( title ) {
	$( '.task-project [contenteditable]', this.$el ).text( title );
}



Task.prototype.project_save = function ( project_id ) {

	// Make sure we have a project id.
	if ( isNaN(project_id) ) {
		project_id = 0;
	}

	// Set project id attribute.
	this.$el.attr( 'data-project-id', project_id );

	// Get project.
	var project = this.board().record.project_records[project_id];


	// Set project title.
	if ( typeof project === 'undefined' ) {
		this.project_update_title( '' );
	}
	else {
		this.project_update_title( project.title );
	}

	// Don't save if not changed.
	if ( project_id == this.record.project_id ) {
		return;
	}

	// Build comment.
	if ( typeof project === 'undefined' ) {
		var comment = kanban.text['task_removed_from_project'].sprintf(
			this.board().current_user().record().short_name
		);
	}
	else {
		var comment = kanban.text['task_added_to_project'].sprintf(
			this.board().current_user().record().short_name,
			project.title
		);
	}

	// Get the previous project id.
	var prev_id = this.record.project_id;
	var prev_project = this.board().record.project_records[prev_id];

	if ( typeof prev_project !== 'undefined' ) {
		comment += kanban.text['task_added_to_project_previous'].sprintf(
			prev_project.title
		);
	}

	// Update task record and save it.
	this.record.project_id = project_id;
	this.save( comment );
};



Task.prototype.parse_project = function () {

	var self = this;

	var $div = $( '.task-project [contenteditable]', this.$el );

	// clean up html
	sanitize( $div );

	// strip all tags
	strip_tags( $div, [] );

	// Get the title.
	var project_title = $.trim($div.html());

	// Replace backslashes and spaces.
	project_title = project_title.replace( /\\/gi, '|' ).replace( /&nbsp;/gi, ' ' );

	// Sanity check.
	if ( typeof project_title === 'undefined' || project_title === null ) {
		return;
	}

	if ( '' === project_title ) {
		this.project_save( 0 );
		return;
	}

	// see if typed value matches existing project
	var project_id = null;
	for ( var i in this.board().record.project_records ) {
		var project = this.board().record.project_records[i];

		// it is NOT a new project
		if ( project_title === $.trim( project.title ) ) {
			project_id = project.id;
			break;
		}
	}

	// If we found a project, save it to the task and return.
	if ( project_id !== null ) {
		this.project_save( project_id );
		return;
	}



	// add project
	Project.prototype.add(
		self.board().record.id(),
		project_title,
		function() {
			self.project_save( this.data.project.id );
		}
	);


}; // parse_project



Task.prototype.log_work_hour = function ( operator ) {
	if ( !this.board().current_user().has_cap( 'write' ) ) {
		return false;
	}

	var task_data = {
		task: this.record,
		action: 'add_task_hour',
		kanban_nonce: $( '#kanban_nonce' ).val(),
		operator: operator
	};

	$.ajax( {
		method: "POST",
		url: kanban.ajaxurl,
		data: task_data
	} );
}; // add_work_hour


