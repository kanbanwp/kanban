<div class="panel panel-default panel-project" id="modal-projects-{{=project.id}}" data-id="{{=project.id}}">
	<div class="panel-heading">
		<a class="panel-title" data-toggle="collapse" data-parent="#accordion-projects" href="#collapse-{{=project.id}}">
			<span class="label-project-title">{{=project.title}}</span>
			<sup>
				{{=project.task_count}}
			</sup>
		</a>
	</div>
	<div id="collapse-{{=project.id}}" class="panel-collapse collapse">
		<div class="panel-body">
			<p>
				<?php echo sprintf(__('There are %s tasks assigned to this project.', 'kanban'), '{{=project.task_count}}') ?>
			</p>
			<p>
				<label for="modal-project-rename-{{=project.id}}">
					<?php echo __( 'Rename the project', 'kanban' ); ?>:
				</label>
				<span class="input-group">
					<input type="text" class="form-control input-lg project-title" value="{{=project.title}}" id="modal-project-rename-{{=project.id}}" data-id="{{=project.id}}">
					<span class="input-group-btn">
						<button class="btn btn-default btn-lg" type="button">
							<?php echo __('Save') ?>
						</button>
					</span>
				</span>
			</p>
			<p>
				<label for="modal-project-reset-{{=project.id}}">
					<?php echo __('Reset all tasks in this project to') ?>:
				</label>
				<span class="input-group">
					<select class="form-control select-project-reset" id="modal-project-reset-{{=project.id}}" data-id="{{=project.id}}">
						<option value="">-- <?php echo __('Choose a status', 'kanban') ?> --</option>
	{{@statuses}}
						<option value="{{=_key}}">{{=_val.title}}</option>
	{{/@statuses}}
					</select>
					<span class="input-group-btn">
						<button class="btn btn-default btn-project-reset" type="button"><?php echo __('Reset') ?></button>
					</span>
				</span>
				<small class="help-block">
					<?php echo __('All tasks in this project will be moved back to the selected status, and work time reset to 0.') ?>
				</small>
			</p>
			<p class="text-center">
				<a href="#" class="btn btn-sm btn-default btn-project-delete" data-id="{{=project.id}}">
					<span class="text-danger">
						<?php echo __('Delete this project', 'kanban') ?>
					</span>
				</a>
			</p>
		</div><!-- panel body -->
	</div>
</div>