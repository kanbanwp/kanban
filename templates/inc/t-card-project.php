<div class="project">
	<input type="text" class="editable-input project_title" data-toggle="dropdown" data-id="{{=id}}" value="{{=title}}" placeholder="<?php echo __( 'Task project', 'kanban' ); ?>" readonly>
	<ul class="list-group" style="display: none;">
		<li class="list-group-edit">
			<a href="#" class="btn btn-xs" data-toggle="modal" data-target="#modal-projects">
				<?php echo __( 'Edit', 'kanban' ); ?>
			</a>
		</li>
	</ul>
</div>
