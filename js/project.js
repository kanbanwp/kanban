function Project (project)
{
	this.record = function()
	{
		return project;
	}
}




Project.prototype.add = function(board_id, project_title, callback)
{
	if ( typeof callback !== 'function') {
		callback = function () {};
	}

	var comment = kanban.text['project_added'].sprintf(
		boards[board_id].current_user().record().short_name,
		project_title
	);


	var data = {
		action: 'save_project',
		post_type: 'kanban_project',
		kanban_nonce: $( '#kanban_nonce' ).val(),
		project: {
			title: project_title,
			board_id: board_id
		},
		comment: comment
	};

	// save new project
	$.ajax( {
		method: "POST",
		url: kanban.ajaxurl,
		data: data
	} )
	.done( function ( response ) {

		try {
			// add project to available projects
			boards[board_id].record.project_records[response.data.project.id] = response.data.project;
		} catch (err) {}

		// add project to task
		callback.call( response );
	} );
}
