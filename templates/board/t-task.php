<div class="task" id="task-{{=task.id}}" data-id="{{=task.id}}" data-project-id="{{=task.project_id}}" data-user_id-assigned="{{=task.user_id_assigned}}">
	<div class="task-menu">
		<?php echo apply_filters( 'kanban_task_id_before', '' ); ?>

		<div class="dropdown">
			<a href="#" class="task-id btn btn-xs btn-default btn-block" data-toggle="dropdown">
				{{=task.id}}
			</a>
			<ul class="dropdown-menu dropdown-menu-right">
				<?php echo apply_filters( 'kanban_task_id_dropdown_after', '' ); ?>

				<li>
					<a href="#" class="btn-task-move" data-toggle="modal" data-target="#modal-task-move-{{=task.board_id}}" data-task-id="{{=task.id}}">
						Move this task
					</a>
				</li>

				<li>
					<a href="#" class="btn-task-delete" data-task-id="{{=task.id}}">
						Delete this task
						<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
					</a>
				</li>

				<?php echo apply_filters( 'kanban_task_id_dropdown_after', '' ); ?>
			</ul>
		</div><!-- task-id-menu -->

		<?php echo apply_filters( 'kanban_task_id_after', '' ); ?>
	</div><!-- task-menu -->

	<div class="task-project dropdown">
		<div contenteditable="true" data-toggle="dropdown" placeholder="Add a project">{{=task.project.title}}</div>
		<ul class="dropdown-menu">
		</ul>
	</div><!-- task-id-menu -->

	<div class="task-title" contenteditable="true" placeholder="Add a title">{{=task.title}}</div>

	<div class="row row-task-actions">
		<div class="col col-xs-4 col-task-hours">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button" data-toggle="dropdown">
					<small>Hours</small>
					<span class="task-hours">
						{{=task.hour_count_formatted}}
					</span>
				</button>
				<ul class="dropdown-menu">
						<li class="btn-group">
							<button type="button" class="btn btn-default btn-task-hour" data-operator="+">
								<span class=" glyphicon glyphicon-plus"></span>
							</button>
							<button type="button" class="btn btn-default btn-task-hour" data-operator="-">
								<span class=" glyphicon glyphicon-minus"></span>
							</button>
						</li>
				</ul>
			</div><!-- dropdown -->
		</div><!-- col -->

		<div class="col col-xs-4 col-task-estimate">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button" data-toggle="dropdown">
					<small>Estimate</small>
					<span class="task-estimate">
{{task.estimate.title}}
						{{=task.estimate.title}}
{{:task.estimate.title}}
						--
{{/task.estimate.title}}
					</span>
				</button>
				<ul class="dropdown-menu">
{{@estimate_records}}
					<li>
						<a href="#" class="btn-task-estimate" data-id="{{=_key}}">
							{{=_val.title}}
						</a>
					</li>
{{/@estimate_records}}
				</ul>
			</div><!-- dropdown -->
		</div><!-- col -->

		<div class="col col-xs-4 col-task-assigned">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button" data-toggle="dropdown">
					<span class="task-assigned-initials {{!task.user_assigned.short_name}}empty{{/!task.user_assigned.short_name}}">
{{task.user_assigned.avatar}}
						{{=task.user_assigned.avatar}}
{{:task.user_assigned.avatar}}
						{{=task.user_assigned.initials}}
{{/task.user_assigned.avatar}}
					</span>

					<small class="task-assigned-name">
{{task.user_assigned.short_name}}
						{{=task.user_assigned.short_name}}
{{:task.user_assigned.short_name}}
						--
{{/task.user_assigned.short_name}}
					</small>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
{{@allowed_users}}
					<li>
						<a href="#" class="btn-task-assigned" data-id="{{=_key}}">
							{{=_val.long_name_email}}
						</a>
					</li>
{{/@allowed_users}}
				</ul>
			</div><!-- dropdown -->
		</div><!-- col -->
	</div><!-- row -->

	<div class="progress task-progress">
		<div class="progress-bar progress-bar-success" style="width: 50%;"></div>
	</div>

	<div class="task-handle" style="background-color: {{=task.status.color_hex}}"></div>

	<?php echo apply_filters( 'kanban_task_after', '' ); ?>
</div>