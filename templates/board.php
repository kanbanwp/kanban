<?php include Kanban_Template::find_template('inc/header') ?>

<div id="wrapper-board">
	<div class="row" id="row-statuses">
	</div><!-- row -->

	<div class="row" id="row-tasks">
	</div><!-- row -->
</div><!-- wrapper-board -->



<div id="wrapper-footer">
	<div id="filter-wrapper">

		<span class="dropup" id="filter-projects">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __('Project', Kanban::$instance->settings->file) ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-projects-dropdown">
			</ul>
		</span>

		<span class="dropup" id="filter-users">
			<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				<span class="btn-label" data-id="">
					-- <?php echo __('User', Kanban::$instance->settings->file) ?> --
				</span>
			</button>
			<ul class="dropdown-menu" id="filter-users-dropdown">
			</ul>
		</span>

		<button type="button" class="btn btn-primary" id="btn-filter-apply">
			<?php echo __('Filter', Kanban::$instance->settings->file) ?>
		</button>
		<button type="button" class="btn btn-default" id="btn-filter-reset" style="display: none;">
			<?php echo __('Show All', Kanban::$instance->settings->file) ?>
		</button>

	</div><!-- filter-wrapper -->

	<div class="form-inline" id="search-wrapper">
		<input type="search" id="board-search" class="form-control" placeholder="<?php echo __('Search', Kanban::$instance->settings->file) ?>">
		<button type="button" class="btn btn-default" id="board-search-reset" style="display: none;">
			<?php echo __('Show All', Kanban::$instance->settings->file) ?>
		</button>
	</div><!-- form-inline -->


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

	<a href="/wp-admin/admin.php?page=<?php echo Kanban::$instance->settings->basename ?>" class="btn btn-default" target="_blank">
		<?php echo __('Settings', Kanban::$instance->settings->file) ?>
	</a>

</div>

<div id="screen-size">
	<div class="visible-xs" data-size="xs"></div>
	<div class="visible-sm" data-size="sm"></div>
	<div class="visible-md" data-size="md"></div>
	<div class="visible-lg" data-size="lg"></div>
</div>



<?php wp_nonce_field( sprintf('%s-save', Kanban::$instance->settings->basename), Kanban_Utils::get_nonce() ); ?>



<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

var status_records = <?php echo json_encode($wp_query->query_vars['kanban']->board->statuses) ?>;
var status_colors = <?php echo json_encode($wp_query->query_vars['kanban']->board->status_colors) ?>;
var task_records = <?php echo json_encode($wp_query->query_vars['kanban']->board->tasks) ?>;
var project_records = <?php echo json_encode($wp_query->query_vars['kanban']->board->projects) ?>;
var allowed_users = <?php echo json_encode($wp_query->query_vars['kanban']->board->allowed_users) ?>;
var estimates = <?php echo json_encode($wp_query->query_vars['kanban']->board->estimates) ?>;
var current_user = <?php echo json_encode($wp_query->query_vars['kanban']->board->current_user->data) ?>;

var tasks = [];
var statuses = [];
var projects = {projects: []};
var users = [];

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



<?php include Kanban_Template::find_template('inc/footer') ?>