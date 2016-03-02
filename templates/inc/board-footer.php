<div id="wrapper-footer">
	<div id="filter-wrapper">
		<?php echo __( 'Filter by', 'kanban' ); ?>:
		<span class="filter dropup" id="filter-project_id">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __( 'Project', 'kanban' ); ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-project_id-dropdown">
				<li class="divider"></li>
				<li>
					<a href="#" data-id="0">
						<?php echo __( 'No project assigned', 'kanban' ); ?>
					</a>
				</li>
			</ul>
		</span>

		<span class="filter dropup" id="filter-user_id_assigned">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __( 'User', 'kanban' ); ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-user_id_assigned-dropdown">
				<li class="divider"></li>
				<li>
					<a href="#" data-id="0">
						<?php echo __( 'No user assigned', 'kanban' ); ?>
					</a>
				</li>
			</ul>
		</span>

		<?php echo apply_filters( 'kanban_board_filter_html', '' ); ?>

		<button type="button" class="btn btn-primary" id="btn-filter-apply">
			<?php echo __( 'Filter', 'kanban' ); ?>
		</button>
		<button type="button" class="btn btn-warning" id="btn-filter-reset" style="display: none;">
			<?php echo __( 'Show All', 'kanban' ); ?>
		</button>

	</div><!-- filter-wrapper -->



	<div class="form-inline" id="search-wrapper">
		<input type="search" id="board-search" class="form-control" placeholder="<?php echo __('Search', 'kanban'); ?>">
		<button type="button" class="btn btn-warning" id="board-search-reset" style="display: none;">
			<?php echo __( 'Show All', 'kanban' ); ?>
		</button>
	</div><!-- search-wrapper -->



	<div class="btn-group" data-toggle="buttons" id="btn-group-view-compact">
		<button type="button" class="btn btn-default active">
			<input type="radio" name="view-compact" value="0" autocomplete="off" checked>
			<span class="glyphicon glyphicon-th-large"></span>
		</button>
		<button type="button" class="btn btn-default">
			<input type="radio" name="view-compact" value="1" autocomplete="off">
			<span class="glyphicon glyphicon-align-justify"></span>
		</button>
	</div>



<?php if ( in_array( 'write', $wp_query->query_vars['kanban']->board->current_user->caps ) ) : ?>
	<a href="<?php echo admin_url( sprintf('admin.php?page=%s_settings', Kanban::$instance->settings->basename) ); ?>" class="btn btn-default" target="_blank">
		<?php echo __( 'Settings', 'kanban' ); ?>
	</a>
<?php endif ?>


</div><!-- footer -->
