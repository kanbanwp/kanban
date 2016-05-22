

<div id="page-footer">

	<?php echo apply_filters( 'kanban_page_footer_before', '' ); ?>

	<div class="form-inline search-wrapper">
		<input type="search board-search" class="form-control" placeholder="<?php echo __('Search', 'kanban'); ?>">
		<button type="button" class="btn btn-warning board-search-reset" style="display: none;">
			<?php echo __( 'Show All', 'kanban' ); ?>
		</button>
	</div><!-- search-wrapper -->



	<div class="btn-group btn-group-view-compact" data-toggle="buttons">
		<button type="button" class="btn btn-default active">
			<input type="radio" name="view-compact" value="0" autocomplete="off" checked>
			<span class="glyphicon glyphicon-th-large"></span>
		</button>
		<button type="button" class="btn btn-default">
			<input type="radio" name="view-compact" value="1" autocomplete="off">
			<span class="glyphicon glyphicon-align-justify"></span>
		</button>
	</div>



<?php if ( in_array( 'write', $wp_query->query_vars['kanban']->current_user->caps ) ) : ?>
	<a href="<?php echo admin_url( sprintf('admin.php?page=%s_settings', Kanban::$instance->settings->basename) ); ?>" class="btn btn-default" target="_blank">
		<?php echo __( 'Settings', 'kanban' ); ?>
	</a>
<?php endif ?>

	<?php echo apply_filters( 'kanban_page_footer_after', '' ); ?>

</div><!-- footer -->



<?php Kanban_Template::add_script(); ?>

<?php // wp_footer(); ?>


<div id="screen-size">
	<div class="visible-xs" data-size="xs"></div>
	<div class="visible-sm" data-size="sm"></div>
	<div class="visible-md" data-size="md"></div>
	<div class="visible-lg" data-size="lg"></div>
</div>


</body>
</html>
