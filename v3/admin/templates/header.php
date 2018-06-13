
<header id="kanban-header">
	<?php if (!is_network_admin()) : ?>
	<a href="<?php echo Kanban_Router::instance()->get_page_uri() ?>"
	   target="_blank"
	   id="kanban-app-open"
	   class="button">Open Kanban board</a>
	<?php endif // is_network_admin ?>
	<?php if ( Kanban::instance()->settings()->is_network ) : ?>
		<?php if (is_network_admin()) : ?>
		<span class="kanban-notice">
			<?php _e('Kanban is network activated.', 'kanban') ?>
			<?php _e('To access your boards, please visit one of your subsites.', 'kanban') ?>
		</span>
		<?php endif // is_network_admin ?>
	<?php endif // is_network ?>

	<img src="<?php echo Kanban_Admin::instance()->get_logo_svg('black') ?>" id="kanban-logo">

	<h2><?php echo $h2; ?></h2>

</header>

<?php if ( isset( $_GET['message'] ) ) : ?>
	<div class="notice notice-success">
		<p>
			<?php echo stripcslashes(sanitize_text_field($_GET['message'])); ?>
		</p>
	</div>
<?php endif // alert ?>
