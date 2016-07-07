function Modal_Projects ($el)
{
	$(document).trigger('/modal-projects/init/', $el);

	this.$el = $el;

	this.dom();
}


Modal_Projects.prototype.board = function()
{
	return boards[current_board_id];
};



Modal_Projects.prototype.dom = function()
{

	var self = this;



	// populate with projects
	self.$el.on(
		'show.bs.modal',
		function ()
		{
			if ( !self.board().current_user().has_cap('write') )
			{
				return false;
			}

			self.board().project_update_counts();

			var $list = $('#accordion-projects').empty();

			for ( var project_id in self.board().record.project_records )
			{
				var project = self.board().record.project_records[project_id];
				var project_html = templates[self.board().record.id()]['t-modal-project'].render(project);
				$(project_html).appendTo($list);
			}
		}
	);



	self.$el.on(
		'click',
		'.btn-delete',
		function()
		{
			var $btn = $(this);
			var $panel = $btn.closest('.panel');
			var project_id = $btn.attr('data-id');
			var project = self.board().record.project_records[project_id];

			var data = {
				project: project,
				action: 'delete_project',
				kanban_nonce: $('#kanban_nonce').val()
			};

			$.ajax({
				method: "POST",
				url: ajaxurl,
				data: data
			})
			.done(function(response )
			{
				// remove project from tasks
				for ( var i in self.board().record.tasks )
				{
					var task = self.board().record.tasks[i];
					if ( task.record.project_id == project_id )
					{
						task.project_save(0);
					}
				}

				// remove project from projects
				delete self.board().record.project_records[project_id];

				// remove from modal
				$panel.remove();

			});
		}
	);



	self.$el.on(
		'focus',
		'.project-title',
		function()
		{
			var $input = $(this);
			$input.data('orig', $input.val());
		}
	);



	self.$el.on(
		'keyup',
		'.project-title',
		function(e)
		{
			var $input = $(this);

			// enter
			if(e.keyCode==13)
			{
				$input.trigger('blur');
				return false;
			}

			// escape
			if(e.keyCode==27)
			{
				var orig = $input.data('orig');
				$input.val(orig).trigger('blur');
				return false;
			}
		}
	);



	self.$el.on(
		'blur',
		'.project-title',
		function()
		{
			var $input = $(this);
			var $panel = $input.closest('.panel');

			var project_title = $input.val();



			var project_id = $input.attr('data-id');
			var project = self.board().record.project_records[project_id];

			project.title = project_title;

			var data = {
				project: project,
				action: 'save_project',
				kanban_nonce: $('#kanban_nonce').val()
			};

			$.ajax({
				method: "POST",
				url: ajaxurl,
				data: data
			})
			.done(function(response )
			{
				$('.label-project-title', $panel).text(project_title);

				// update the tasks
				for ( var i in self.board().record.tasks )
				{
					var task = self.board().record.tasks[i];
					if ( task.record.project_id == project_id )
					{
						// will only update the DOM
						task.project_save(project_id);
					}
				}
			});

		}
	);




}; // dom



