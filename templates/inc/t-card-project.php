<div class="project">
	<input type="text" class="editable-input project_title" data-toggle="dropdown" data-id="{{=id}}" value="{{=title}}" placeholder="<?php _e( 'Task project', Kanban::get_text_domain() ); ?>" readonly>
	<ul class="list-group" style="display: none;">
		<li class="list-group-edit">
			<a href="#" class="btn btn-xs" data-toggle="modal" data-target="#modal-projects">
				<?php _e( 'Edit', Kanban::get_text_domain() ); ?>
			</a>
		</li>
	</ul>
</div>

