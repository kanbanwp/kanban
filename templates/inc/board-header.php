<?php echo apply_filters( 'kanban_board_before', '' ); ?>


<?php echo apply_filters( 'kanban_board_header_before', '' ); ?>



<div class="board-filter">
	<?php echo __( 'Filter by', 'kanban' ); ?>:
	<span class="filter dropup filter-project_id">
		<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
			<span class="btn-label" data-id="">
				-- <?php echo __( 'Project', 'kanban' ); ?> --
			</span>
		</button>
		<ul class="dropdown-menu filter-project_id-dropdown">
			<li class="divider"></li>
			<li>
				<a href="#" data-id="0">
					<?php echo __( 'No project assigned', 'kanban' ); ?>
				</a>
			</li>
		</ul>
	</span>

	<span class="filter dropup filter-user_id_assigned">
		<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
			<span class="btn-label" data-id="">
				-- <?php echo __( 'User', 'kanban' ); ?> --
			</span>
		</button>
		<ul class="dropdown-menu filter-user_id_assigned-dropdown">
			<li class="divider"></li>
			<li>
				<a href="#" data-id="0">
					<?php echo __( 'No user assigned', 'kanban' ); ?>
				</a>
			</li>
		</ul>
	</span>

	<?php echo apply_filters( 'kanban_board_filter_html', '' ); ?>

	<button type="button" class="btn btn-primary btn-filter-apply">
		<?php echo __( 'Filter', 'kanban' ); ?>
	</button>
	<button type="button" class="btn btn-warning btn-filter-reset" style="display: none;">
		<?php echo __( 'Show All', 'kanban' ); ?>
	</button>

</div><!-- filter-wrapper -->



<?php echo apply_filters( 'kanban_board_header_after', '' ); ?>
