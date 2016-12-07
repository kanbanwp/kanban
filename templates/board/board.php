<div class="board tab-pane <?php echo $board->settings['hide_time_tracking'] == 1 ? 'hide_time_tracking' : '' ?>" id="board-<?php echo $board_id ?>" data-id="<?php echo $board_id ?>" data-title="<?php echo $board->title ?>">
<?php /*
	<div class="row row-title text-center">
		<?php echo $board->title ?>
	</div>
*/ ?>

<?php if ( count($board->statuses) > 0 ) : ?>


	
	<div class="row-statuses-wrapper">
		<div class="row row-statuses">
<?php foreach ( $board->statuses as $status_id => $status ) : ?>
			<?php include Kanban_Template::find_template( 'board/col-status' ); ?>
<?php endforeach; // statuses ?>
		</div><!-- row -->
	</div>



	<div class="row-tasks-wrapper">
		<div class="row row-tasks">
<?php foreach ( $board->statuses as $status_id => $status ) :  ?>
			<div class="col col-sm-3 col-tasks col_percent_w" id="status-<?php echo $status_id ?>-tasks" data-status-id="<?php echo $status_id ?>">
			</div><!-- col -->
<?php endforeach // statuses ?>
		</div><!-- row -->
	</div><!-- row task wrapper -->



	<div class="modal fade modal-task-move" id="modal-task-move-<?php echo $board_id ?>" data-board-id="<?php echo $board_id ?>">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">

					<button type="button" class="close" data-dismiss="modal">&times;</button>

					<p class="h3"><?php echo sprintf(__('Move "%s" to:', 'kanban'), '<span class="task-title"></span>') ?></p>

					<input type="hidden" class="task-id" value="">

					<?php echo apply_filters( 'kanban_task_move_modal_header', '', $board_id ); ?>

					<div class="list-group task-move-list-board-statuses task-move-list-board-statuses-<?php echo $board_id ?>" data-board-id="<?php echo $board_id ?>">
<?php foreach ( $board->statuses as $status_id => $status ) : ?>
						<a href="#" class="list-group-item" data-status-id="<?php echo $status_id ?>" data-board-id="<?php echo $board_id ?>" data-dismiss="modal">
							<h3 class="h4"><?php echo $status->title ?></h3>
							<div class="task-handle" style="background-color: <?php echo $status->color_hex ?>"></div>
						</a><!-- list-group-item -->
<?php endforeach // statuses ?>
					</div><!-- list-group -->

					<?php echo apply_filters( 'kanban_task_move_modal_footer', '', $board_id ); ?>
				</div><!-- body -->
			</div><!-- content -->
		</div><!-- dialog -->
	</div><!-- modal-task-move -->



	<div class="modal fade modal-filter" id="modal-filter-<?php echo $board_id ?>" data-board-id="<?php echo $board_id ?>">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">

					<button type="button" class="close" data-dismiss="modal">&times;</button>

					<p class="h3"><?php echo __('Filters:', 'kanban') ?></p>

					<div class="form-group">
						<select class="form-control input-lg select-projects" data-field="project_id">
							<option value="">-- <?php echo __( 'Project', 'kanban' ); ?> --</option>
							<option value="0"><?php echo __( '(No project assigned)', 'kanban') ?></option>
						</select>
					</div>
					<div class="form-group">
						<select class="form-control input-lg" data-field="user_id_assigned">
							<option value="">-- <?php echo __( 'User', 'kanban' ); ?> --</option>
	<?php foreach ($board->allowed_users as $user_id => $user) : $user = (object) $user; ?>
							<option value="<?php echo $user_id ?>"><?php echo $user->long_name_email ?></option>
	<?php endforeach // users ?>
							<option value="0"><?php echo __( '(No user assigned)', 'kanban' ); ?></option>
						</select>
					</div>

					<?php echo apply_filters( 'kanban_filter_modal_select_after', '', $board ); ?>
					
					<button type="button" class="btn btn-warning btn-block btn-filter-reset" style="display: none;">
						<?php echo __('Reset', 'kanban') ?>
					</button>
				</div><!-- body -->
			</div><!-- content -->
		</div><!-- dialog -->
	</div><!-- modal filter -->



	<?php if ( count($board->statuses) > 2 ) : ?>
	<?php $status = reset($board->statuses); ?>
	<div class="col-tasks-sidebar col-tasks-sidebar-left" data-left="0" data-right="-<?php echo $board->status_w ?>%" style="background-color: <?php echo $status->color_hex ?>">
		<div class="col-tasks-sidebar-label">
			<div class="col-tasks-sidebar-label-inner">
				<?php echo $status->title ?>
			</div>
		</div>
		<div class="col-tasks-sidebar-arrow" style="border-left-color: <?php echo $status->color_hex ?>"></div>
	</div>



	<?php $status = end($board->statuses) ?>
	<div class="col-tasks-sidebar col-tasks-sidebar-right" data-left="-<?php echo $board->status_w*2 ?>%" data-right="-<?php echo $board->status_w ?>%" style="background-color: <?php echo $status->color_hex ?>">
		<div class="col-tasks-sidebar-label">
			<div class="col-tasks-sidebar-label-inner">
				<?php echo $status->title ?>
			</div>
		</div>
		<div class="col-tasks-sidebar-arrow" style="border-right-color: <?php echo $status->color_hex ?>"></div>
	</div>
	<?php endif // count $board->statuses ?>



<?php else: // count statuses ?>



	<div class="board-no-statuses">
		<a href="<?php echo admin_url('admin.php?page=kanban_settings&board_id=' . $board->id) ?>" class="btn btn-primary">
			<?php echo __( 'Please visit the Kanban settings page to setup this board.', 'kanban' ); ?>
		</a>
	</div>



<?php endif // count statuses ?>



</div><!-- board -->