<?php

global $wp_query;

do_action( 'kanban_board_template_before' ); ?>



<?php include Kanban_Template::find_template( 'board/header' ); ?>

<?php echo apply_filters( 'kanban_page_boards_before', '' ); ?>

<div class="tab-content">
<div id="page-loading">
	<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
</div>
<?php foreach ( $wp_query->query_vars['kanban']->boards as $board_id => $board ) : ?>
<?php include Kanban_Template::find_template( 'board/board' ); ?>
<?php endforeach ?>
</div><!-- tab-content -->

<?php echo apply_filters( 'kanban_page_boards_after', '' ); ?>

<?php include Kanban_Template::find_template( 'board/modal-projects' ); ?>
<?php include Kanban_Template::find_template( 'board/modal-keyboard-shortcuts' ); ?>


<script type="text/javascript">
var kanban = {};

kanban.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
kanban.favicon = '<?php echo Kanban::get_instance()->settings->uri ?>img/notify-favicon-250.png';


kanban.alert = "<?php echo esc_js($wp_query->query_vars['kanban']->alert); ?>";
kanban.text = <?php echo json_encode( apply_filters( 'kanban_board_text', $wp_query->query_vars['kanban']->text) ); ?>;

kanban.templates = {};
var window_w, window_h, screen_size, scrollbar_w;
kanban.is_dragging = false;
//kanban.current_user = {
//	has_cap: function()
//	{
//		return false;
//	}
//};

kanban.url_params = {
	board_id: <?php echo $wp_query->query_vars['kanban']->current_board_id ?>
};

<?php if ( !empty($_GET) ) : foreach ($_GET as $key => $value) : ?>
<?php if ( is_array($value) ) : ?>
kanban.url_params['<?php echo esc_js($key) ?>'] = <?php echo json_encode( $value ) ?>;
<?php else : ?>
kanban.url_params['<?php echo esc_js($key) ?>'] = '<?php echo esc_js($value) ?>';
<?php endif ?>
<?php endforeach; endif ?>

var boards = [];
<?php foreach ( $wp_query->query_vars['kanban']->boards as $board_id => $board ) : ?>
boards[<?php echo $board_id ?>] = {
	id: function ()
	{
		return <?php echo $board_id ?>;
	},
	title: function ()
	{
		return '<?php echo $board->title ?>';
	},
	col_percent_w: function ()
	{
		return <?php echo $board->col_percent_w ?>;
	},
	status_w: function ()
	{
		return <?php echo $board->status_w ?>;
	},
	settings: function ()
	{
		return <?php echo json_encode( $board->settings); ?>;
	},
	filters: <?php echo json_encode( $board->filters); ?>,
	search: <?php echo json_encode( $board->search); ?>,
	status_records: function ()
	{
		return <?php echo (json_encode( $board->statuses)); ?>;
	},
	tasks: <?php echo Kanban_Utils::slashes(json_encode( $board->tasks)); ?>,
	project_records: <?php echo Kanban_Utils::slashes(json_encode( $board->projects)); ?>,
	allowed_users: function ()
	{
		return <?php echo Kanban_Utils::slashes(json_encode( $board->allowed_users)); ?>;
	},
	current_user_id: function ()
	{
		return <?php echo $wp_query->query_vars['kanban']->current_user_id ?>;
	},
	estimate_records: function ()
	{
		return <?php echo json_encode( $board->estimates); ?>;
	}
	<?php echo apply_filters( 'kanban_board_js_onpage', '' ); ?>
};
<?php endforeach // boards ?>

var current_board_id = <?php echo $wp_query->query_vars['kanban']->current_board_id ?>;
var updates_dt = new Date(<?php echo time() ?>000);

</script>




<style>
<?php foreach ( $wp_query->query_vars['kanban']->boards as $board_id => $board ) : ?>
#board-<?php echo $board_id ?> .col_percent_w {width: <?php echo $board->col_percent_w ?>%}
#board-<?php echo $board_id ?> .status_w {width: <?php echo $board->status_w ?>%}

#board-<?php echo $board_id ?> .row-tasks,
#board-<?php echo $board_id ?> .row-statuses {
	margin-left: -<?php echo $board->status_w ?>%;
	width: <?php echo 100+($board->status_w*2) ?>%;
}


<?php if ( !empty($board->settings['board_css']) ) : ?>
<?php echo stripslashes($board->settings['board_css']) ?>
<?php endif ?>

<?php endforeach // boards ?>
</style>



<?php foreach ( $wp_query->query_vars['kanban']->boards as $board_id => $board ) : ?>
<?php do_action( 'kanban_board_render_js_templates', $board ); ?>
<?php endforeach // boards ?>



<?php include Kanban_Template::find_template( 'board/footer' ); ?>