<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo sprintf(__('About %s', 'kanban'), Kanban::get_instance()->settings->pretty_name) ?>
	</h1>



	<?php if ( isset($_GET['activation']) ) : ?>
		<div class="updated notice">
			<p><?php echo __('Thanks for using Kanban for WordPress!', 'kanban') ?></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php echo __('Dismiss this notice.', 'kanban') ?></span>
			</button>
		</div>
	<?php endif ?>

	<p>
		<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ) ?>" class="button-primary" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ) ?>', 'kanbanboard'); return false;">
			<?php echo __('Go to your board', 'kanban') ?>
		</a>
		<a href="http://kanbanwp.com/documentation" class="button" target="_blank">
			<?php echo __('Documentation', 'kanban') ?>
			<?php echo __('(On kanbanwp.com)', 'kanban') ?>
		</a>
	</p>
	<h3>Intro to Kanban for WordPress</h3>
	<p>Get started with the plugin in 60 seconds.</p>
	<div class="video-wrapper" style="max-width: 1000px;">
		<iframe src="https://player.vimeo.com/video/145274368" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
	</div><!-- video-wrapper -->
<?php /*
	<h3>Intro to Kanban</h3>
	<p>New to kanban? Here's one of our favorite intrudctory videos.</p>
	<div class="video-wrapper">
		<iframe width="640" height="360" src="https://www.youtube-nocookie.com/embed/ueVXZUaWhYw?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
	</div><!-- video-wrapper -->
*/ ?>
</div><!-- wrap -->



