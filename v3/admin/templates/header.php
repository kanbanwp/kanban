
<header id="kanban-header">
	<?php if ( !Kanban::instance()->settings()->is_network ) : ?>
	<a href="<?php echo Kanban_Router::instance()->get_page_uri() ?>"
	   target="_blank"
	   id="kanban-app-open"
	   class="button">Open Kanban board</a>
	<?php endif // is_network ?>

	<h2><?php echo $h2; ?></h2>

	<img src="<?php echo Kanban_Admin::instance()->get_logo_svg('black') ?>" id="kanban-logo-white">
</header>

<?php if ( isset( $_GET['message'] ) ) : ?>
	<div class="notice notice-success">
		<p>
			<?php echo stripcslashes(sanitize_text_field($_GET['message'])); ?>
		</p>
	</div>
<?php endif // alert ?>