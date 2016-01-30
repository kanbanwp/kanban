<?php

do_action('kanban_board_template_before', $wp_query->query_vars['kanban']->board);

include Kanban_Template::find_template('inc/header') ?>



<div id="wrapper-board" class="<?php echo $wp_query->query_vars['kanban']->board->settings['show_all_cols'] ? 'show_all_cols' : '' ?>">
	<div class="row" id="row-statuses">
	</div><!-- row -->

	<div class="row" id="row-tasks">
	</div><!-- row -->
</div><!-- wrapper-board -->



<div id="wrapper-footer">
	<div id="filter-wrapper">
		<?php echo __('Filter by', 'kanban') ?>:
		<span class="dropup" id="filter-projects">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __('Project', 'kanban') ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-projects-dropdown">
				<li class="divider"></li>
				<li>
					<a href="#" data-id="0">
						<?php echo __('No project assigned', 'kanban') ?>
					</a>
				</li>
			</ul>
		</span>

		<span class="dropup" id="filter-users">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __('User', 'kanban') ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-users-dropdown">
				<li class="divider"></li>
				<li>
					<a href="#" data-id="0">
						<?php echo __('No user assigned', 'kanban') ?>
					</a>
				</li>
			</ul>
		</span>

		<button type="button" class="btn btn-primary" id="btn-filter-apply">
			<?php echo __('Filter', 'kanban') ?>
		</button>
		<button type="button" class="btn btn-warning" id="btn-filter-reset" style="display: none;">
			<?php echo __('Show All', 'kanban') ?>
		</button>

	</div><!-- filter-wrapper -->



	<div class="form-inline" id="search-wrapper">
		<input type="search" id="board-search" class="form-control" placeholder="<?php echo __('Search', 'kanban') ?>">
		<button type="button" class="btn btn-warning" id="board-search-reset" style="display: none;">
			<?php echo __('Show All', 'kanban') ?>
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



<?php if ( in_array('write', $wp_query->query_vars['kanban']->board->current_user->caps) ) : ?>
	<a href="<?php echo admin_url( sprintf('admin.php?page=%s_settings', Kanban::$instance->settings->basename) ) ?>" class="btn btn-default" target="_blank">
		<?php echo __('Settings', 'kanban') ?>
	</a>
<?php endif ?>



</div>




<div class="modal fade" id="modal-projects" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class=" btn btn-default btn-close" data-dismiss="modal">
					<?php echo __('Close', 'kanban') ?>
				</button>

				<div class="panel-group" id="accordion-projects">
				</div><!-- panel-group -->


			</div><!-- body -->
		</div><!-- content -->
	</div><!-- dialog -->
</div><!-- modal -->



<div class="modal fade" id="modal-empty-archive" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<p class="lead text-center" id="empty-archive-confirmation-label" data-label="<?php echo __('Delete all tasks in &quot;{0}&quot;?', 'kanban') ?>">
				</p>
				<p class="text-center">
					<button type="button" class="btn btn-default" id="btn-empty-status-tasks-cancel" data-dismiss="modal">
						<?php echo __('Cancel', 'kanban') ?>
					</button>
					<button type="button" class="btn btn-primary" id="btn-empty-status-tasks" data-status-col-id="">
						<?php echo __('Archive', 'kanban') ?>
					</button>
				</p>
			</div>
		</div><!-- content -->
	</div><!-- dialog -->
</div><!-- modal -->




<div id="screen-size">
	<div class="visible-xs" data-size="xs"></div>
	<div class="visible-sm" data-size="sm"></div>
	<div class="visible-md" data-size="md"></div>
	<div class="visible-lg" data-size="lg"></div>
</div>



<?php wp_nonce_field( 'kanban-save', Kanban_Utils::get_nonce() ); ?>



<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

var alert = "<?php echo addslashes($wp_query->query_vars['kanban']->board->alert) ?>";

var board = {
	settings: function ()
	{
		return <?php echo json_encode($wp_query->query_vars['kanban']->board->settings) ?>;
	},
	status_records: function ()
	{
		return <?php echo json_encode($wp_query->query_vars['kanban']->board->statuses) ?>;
	},
	task_records: <?php echo json_encode($wp_query->query_vars['kanban']->board->tasks) ?>,
	project_records: <?php echo json_encode($wp_query->query_vars['kanban']->board->projects) ?>,
	allowed_users: function ()
	{
		return <?php echo json_encode($wp_query->query_vars['kanban']->board->allowed_users) ?>;
	},
	estimate_records: function ()
	{
		return <?php echo json_encode($wp_query->query_vars['kanban']->board->estimates) ?>;
	},
	current_user: function ()
	{
		return <?php echo json_encode($wp_query->query_vars['kanban']->board->current_user) ?>;
	}
};

var col_percent_w = <?php echo $wp_query->query_vars['kanban']->board->col_percent_w ?>;
var sidebar_w = <?php echo $wp_query->query_vars['kanban']->board->sidebar_w ?>;
</script>

<style>
.col_percent_w {width: <?php echo $wp_query->query_vars['kanban']->board->col_percent_w ?>%}
.sidebar_w {width: <?php echo $wp_query->query_vars['kanban']->board->sidebar_w ?>%}

#row-tasks, #row-statuses {
	left: -<?php echo $wp_query->query_vars['kanban']->board->sidebar_w ?>%;
	width: <?php echo 100+($wp_query->query_vars['kanban']->board->sidebar_w*2) ?>%;
}
</style>



<?php

// Automatically load router files
$js_templates = glob(Kanban::$instance->settings->path . '/templates/inc/t-*.php');
foreach ($js_templates as $js_template) : ?>
<script type="text/html" id="<?php echo basename($js_template, '.php') ?>">

<?php include $js_template ?>

</script>
<?php endforeach ?>



<?php

include Kanban_Template::find_template('inc/footer');

do_action('kanban_board_template_after', $wp_query->query_vars['kanban']->board);

?>
