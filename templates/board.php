<?php

do_action( 'kanban_board_template_before', $wp_query->query_vars['kanban']->board );

include Kanban_Template::find_template( 'inc/header' ); ?>



<?php include Kanban_Template::find_template( 'inc/board-header' ); ?>



<div id="wrapper-board" class="<?php echo $wp_query->query_vars['kanban']->board->settings['show_all_cols'] ? 'show_all_cols' : ''; ?> <?php echo $wp_query->query_vars['kanban']->board->settings['hide_progress_bar'] ? 'hide_progress_bar' : ''; ?>">

	<div class="row" id="row-statuses">
	</div><!-- row -->

	<div class="row" id="row-tasks">
	</div><!-- row -->
</div><!-- wrapper-board -->



<?php include Kanban_Template::find_template( 'inc/board-footer' ); ?>



<?php include Kanban_Template::find_template( 'inc/board-modal-projects' ); ?>
<?php include Kanban_Template::find_template( 'inc/board-modal-archive' ); ?>



<?php wp_nonce_field( 'kanban-save', Kanban_Utils::get_nonce() ); ?>



<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

var alert = "<?php echo addslashes($wp_query->query_vars['kanban']->board->alert); ?>";

var board = {
	settings: function ()
	{
		return <?php echo json_encode( $wp_query->query_vars['kanban']->board->settings ); ?>;
	},
	filters: <?php echo json_encode( $wp_query->query_vars['kanban']->board->filters ); ?>,
	status_records: function ()
	{
		return <?php echo json_encode( $wp_query->query_vars['kanban']->board->statuses ); ?>;
	},
	task_records: <?php echo json_encode( $wp_query->query_vars['kanban']->board->tasks ); ?>,
	project_records: <?php echo json_encode( $wp_query->query_vars['kanban']->board->projects ); ?>,
	allowed_users: function ()
	{
		return <?php echo json_encode( $wp_query->query_vars['kanban']->board->allowed_users ); ?>;
	},
	estimate_records: function ()
	{
		return <?php echo json_encode( $wp_query->query_vars['kanban']->board->estimates ); ?>;
	},
	current_user: function ()
	{
		return <?php echo json_encode( $wp_query->query_vars['kanban']->board->current_user ); ?>;
	}
	<?php echo apply_filters( 'kanban_board_js_onpage', '' ); ?>
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



<?php do_action( 'kanban_board_render_js_templates' ); ?>



<?php

include Kanban_Template::find_template( 'inc/footer' );

do_action( 'kanban_board_template_after', $wp_query->query_vars['kanban']->board );
