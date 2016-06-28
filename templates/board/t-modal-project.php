<div class="panel panel-default" id="modal-projects-{{=id}}" data-id="{{=id}}">
	<div class="panel-heading">
		<a class="panel-title" data-toggle="collapse" data-parent="#accordion-projects" href="#collapse-{{=id}}">
			<span class="label-project-title">{{=title}}</span>
			<sup>
				{{=task_count}}
			</sup>
		</a>
	</div>
	<div id="collapse-{{=id}}" class="panel-collapse collapse">
		<div class="panel-body">
			<p>
				<?php echo sprintf(__('There are %s tasks assigned to this project.', 'kanban'), '{{=task_count}}') ?>
			</p>
			<p class="form-group">
				<input type="text" class="form-control input-lg project-title" value="{{=title}}" data-id="{{=id}}">
			</p>
			<p class="text-right">
				<a href="#" class="text-danger btn-delete" data-id="{{=id}}">
					<?php echo __('Delete this project', 'kanban') ?>
				</a>
			</p>
		</div><!-- panel body -->
	</div>
</div>