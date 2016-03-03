<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo sprintf( __( 'About %s', 'kanban' ), Kanban::get_instance()->settings->pretty_name ); ?>
	</h1>



	<?php if ( isset($_GET['activation']) ) : ?>
		<div class="updated notice is-dismissible kanban-welcome-notice">
			<p><?php echo __('Thanks for using Kanban for WordPress!', 'kanban') ?></p>
		</div>
		<script>
			jQuery( document.body ).on( 'click', '.kanban-welcome-notice .notice-dismiss', function() {
				window.history.replaceState('Object', 'Title', '<?php echo esc_url( admin_url( 'admin.php?page=kanban_welcome' ) ); ?>' );
			});
		</script>
	<?php endif ?>



<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">

							<h2><span>
								<?php echo __( 'Kanban for WordPress', 'kanban' ); ?>
							</span></h2>

							<div class="inside">
								<p>
									<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>" class="button-primary" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>', 'kanbanboard'); return false;">
										<?php echo __( 'Go to your board', 'kanban' ); ?>
									</a>
									<a href="http://kanbanwp.com/documentation" class="button" target="_blank">
										<?php echo __( 'Documentation', 'kanban' ); ?>
										<?php echo __( '(On kanbanwp.com)', 'kanban' ); ?>
									</a>
									<a href="<?php echo admin_url('admin.php?page=kanban_settings'); ?>" class="button">Settings</a>
									<a href="<?php echo admin_url('admin.php?page=kanban_contact'); ?>" class="button">Contact us</a>
								</p>
							</div>

							<h2><span>
								<?php echo __( 'Intro to Kanban for WordPress', 'kanban' ); ?>
							</span></h2>

							<div class="inside">
								<p>Get started with the plugin in 60 seconds.</p>

								<div class="video-wrapper" style="max-width: 1000px;">
									<iframe src="https://player.vimeo.com/video/145274368" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
								</div><!-- video-wrapper -->

							</div>
						</div>
					</div>
				</div>



				<div id="postbox-container-1" class="postbox-container">
					<div class="meta-box-sortables">
						<div class="postbox">

							<h2><span>
							<?php echo __( 'Stay up to date', 'kanban' ); ?>
							</span></h2>

							<div class="inside">
								<form action="//gelform.us10.list-manage.com/subscribe/post?u=c69e4dc144a0f56c692d34515&amp;id=93b0fa6c8c" method="post" name="mc-embedded-subscribe-form" target="_blank">
									<p>
										<label for="mce-EMAIL">Subscribe to our mailing list for news and updates!
									</label>
									</p>

									<p>
										<input type="email" value="" name="EMAIL" class="large-text" id="mce-EMAIL" placeholder="Email address">
									</p>

									<p>
										<button class="button-secondary" type="submit">Subscribe</button>
									</p>

								</form>
							</div>

						</div>
					</div>
				</div>

			</div>
			<br class="clear">
		</div>




</div><!-- wrap -->
