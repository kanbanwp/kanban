<div class="panel panel-default card task" id="task-{{=ID}}" data-id="{{=ID}}">
	<div class="panel-body">
		<div class="pull-right text-muted task-id">
			<div class="badge" data-toggle="dropdown">
				{{=ID}}
			</div>
			<ul class="dropdown-menu">
				<li>
					<a href="#" class="delete-task text-danger">
						<span class="glyphicon glyphicon-remove text-danger"></span>
						<?php _e( 'Delete this task:', Kanban::get_text_domain() ); ?>
					</a>
				</li>
			</ul>
		</div>
		<p class="wrapper-task-project">
		</p>
		<h3 class="h4 wrapper-task-title">
			<textarea class="editable-input task_title resize" rows="1" placeholder="<?php _e( 'Task name', Kanban::get_text_domain() ); ?>" readonly>{{=post_title}}</textarea>
		</h3>

		<div class="row row-worked">
			<div class="col col-xs-4 col-work-hours">
				<div class="btn btn-default btn-work-hours">
				</div><!-- btn -->
				<div class="btn-group work-hours-operators">
					<button type="button" class="btn btn-default btn-sm" value="+1">
						<span class="glyphicon glyphicon-plus"></span>
					</button>
					<button type="button" class="btn btn-default btn-sm" value="-1">
						<span class="glyphicon glyphicon-minus"></span>
					</button>
				</div>
			</div><!-- col -->

			<div class="col col-xs-4 col-assigned-to">
				<div class="btn btn-default btn-assigned-to" data-toggle="dropdown">
				</div>
				<ul class="dropdown-menu dropdown-menu-allowed-users">
				</ul>
			</div><!-- col -->

			<div class="col col-xs-4 col-estimate">
				<div class="btn btn-default btn-estimate" data-toggle="dropdown">
				</div>
				<ul class="dropdown-menu dropdown-menu-estimates">
				</ul>
			</div><!-- col -->
		</div><!-- row -->
		<div class="progress">
			<div class="progress-bar  progress-bar-success" style="width: 0%;">
			</div><!-- progress bar -->
		</div><!-- progress -->
	</div><!-- panel body -->
	<div class="task-handle" style="background: {{=status_color}};">
	</div>
</div>