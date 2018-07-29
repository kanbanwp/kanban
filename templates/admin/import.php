<div class="wrap">
	<h1>
	<?php echo __( sprintf( '%s Import', Kanban::get_instance()->settings->pretty_name ), 'kanban' ); ?>
	</h1>

	<p style="font-size: 1.382em; font-style: italic; font-weight: bold;">
	<?php if ( $backup ) : ?>
		<?php echo __( 'Backing up the current data and starting the download... ', 'kanban' ); ?>
		<iframe src="<?php echo add_query_arg(array(
				'page' => 'kanban_settings',
				'prefix' => 'backup',
				Kanban_Utils::get_nonce() => wp_create_nonce( 'export' )
		), admin_url( 'admin.php' )) ?>" style="height: 1px; width: 1px; visibility: hidden;"></iframe>
	<?php else: // $backup ?>
			<?php echo sprintf(
					__( '%s%% imported...', 'kanban' ),
					$percentage
			); ?>
	<?php endif // $backup ?>
	</p>

	<div style="background: white; padding: 2px; width: 90%;">
		<div style="background: limegreen; height: 40px; width: <?php echo $percentage ?>%;">
			&nbsp;
		</div>
	</div>

	<?php if ( $done ) : ?>
		<p>
			<a href="<?php echo $redirect ?>" class="button">
				<?php echo __( 'Continue to the settings page', 'kanban' ); ?>
			</a>
		</p>
	<?php else : // $done ?>
		<script>
			setTimeout(function() {
				document.location.replace('<?php echo $redirect ?>');
			}, 1000);
		</script>
	<?php endif ?>

</div><!-- wrap -->
