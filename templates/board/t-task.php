<div class="task" id="task-{{=task.id}}" data-id="{{=task.id}}" data-project-id="{{=task.project_id}}"
	 data-user_id-assigned="{{=task.user_id_assigned}}">
	<div class="task-menu">
		<?php echo apply_filters( 'kanban_task_id_before', '', $board ); ?>

		<div class="dropdown">
			<a href="#" class="task-id btn btn-xs btn-default btn-block" data-toggle="dropdown"
			   title="Task #{{=task.id}}">
				{{show_task_ids}}
				{{=task.id}}
				{{:show_task_ids}}
				<span class="glyphicon glyphicon-menu-hamburger"></span>
				{{/show_task_ids}}
			</a>
			{{current_user_can_write}}
			<ul class="dropdown-menu dropdown-menu-right">
				<?php echo apply_filters( 'kanban_task_id_dropdown_before', '', $board ); ?>
				<li>
					<a href="#" class="btn-task-move" data-toggle="modal"
					   data-target="#modal-task-move-{{=task.board_id}}" data-task-id="{{=task.id}}">
						<?php echo __( 'Move this task', 'kanban' ); ?>
					</a>
				</li>
				<li>
					<a href="#" class="btn-task-copy" data-task-id="{{=task.id}}">
						<?php echo __( 'Copy this task', 'kanban' ); ?>
						<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate"
						      style="display: none;"></span>
					</a>
				</li>
				<li>
					<a href="#" class="btn-task-delete" data-task-id="{{=task.id}}">
						<?php echo __( 'Delete this task', 'kanban' ); ?>
						<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate"
							  style="display: none;"></span>
					</a>
				</li>

				<?php echo apply_filters( 'kanban_task_id_dropdown_after', '', $board ); ?>
			</ul>
			{{/current_user_can_write}}
		</div><!-- task-id-menu -->

		<?php echo apply_filters( 'kanban_task_id_after', '', $board ); ?>
	</div><!-- task-menu -->

	<div class="task-project dropdown">
		<div contenteditable="{{current_user_can_write}}true{{:current_user_can_write}}false{{/current_user_can_write}}"
			 data-toggle="dropdown" placeholder="<?php echo __( 'Add a project', 'kanban' ) ?>">{{=task.project.title}}</div>
		{{current_user_can_write}}
		<ul class="dropdown-menu">
		</ul>
		{{/current_user_can_write}}
	</div><!-- task-id-menu -->

	<div class="task-title"
		 data-contenteditable="{{current_user_can_write}}true{{:current_user_can_write}}false{{/current_user_can_write}}"
		 placeholder="<?php echo __( 'Add a title', 'kanban' ) ?>">{{=task.title}}</div>

	<?php echo apply_filters( 'kanban_task_title_after', '', $board ); ?>

	<div class="row row-task-actions">
		<div class="col col-xs-4 col-task-hours">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button"
						data-toggle="dropdown">
					<small><?php echo __( 'Hours', 'kanban' ) ?></small>
					<span class="task-hours">
						{{=task.hour_count_formatted}}
					</span>
				</button>
				{{current_user_can_write}}
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
				{{/current_user_can_write}}
			</div><!-- dropdown -->
		</div><!-- col -->

		<div class="col col-xs-4 col-task-estimate">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button"
						data-toggle="dropdown">
					<small><?php echo __( 'Estimate', 'kanban' ) ?></small>
					<span class="task-estimate">
{{task.estimate.title}}
						{{=task.estimate.title}}
{{:task.estimate.title}}
						--
{{/task.estimate.title}}
					</span>
				</button>
				{{current_user_can_write}}
				{{estimate_records.0}}
				<ul class="dropdown-menu">
					{{@estimate_records}}
					<li>
						<a href="#" class="btn-task-estimate" data-id="{{=_val.id}}">
							{{=_val.title}}
						</a>
					</li>
					{{/@estimate_records}}
				</ul>
				{{/estimate_records.0}}
				{{/current_user_can_write}}
			</div><!-- dropdown -->
		</div><!-- col -->

		<div class="col col-xs-4 col-task-assigned">
			<div class="dropdown">
				<button class="btn btn-default btn-block btn-task-action dropdown-toggle" type="button"
						data-toggle="dropdown">
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
				{{current_user_can_write}}
				<ul class="dropdown-menu dropdown-menu-right">
					{{@allowed_users}}
					{{_val.long_name_email}}
					<li>
						<a href="#" class="btn-task-assigned" data-id="{{=_key}}">
							{{=_val.long_name_email}}
						</a>
					</li>
					{{/_val.long_name_email}}
					{{/@allowed_users}}
				</ul>
				{{/current_user_can_write}}
			</div><!-- dropdown -->
		</div><!-- col -->
	</div><!-- row -->

	<div class="progress task-progress">
		<div class="progress-bar progress-bar-success" style="width: 0%;"></div>
	</div>

	<div class="task-handle" style="background-color: {{=task.status.color_hex}}"></div>

	<!--	<input type="text" class="position" value="{{=task.position}}">-->

	<?php echo apply_filters( 'kanban_task_after', '', $board ); ?>
</div>