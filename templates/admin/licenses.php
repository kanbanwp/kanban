<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo __( sprintf( '%s Add-on Licenses', Kanban::get_instance()->settings->pretty_name ), 'kanban' ); ?>
		<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>" class="page-title-action" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>', 'kanbanboard'); return false;">
			<?php echo __( 'Go to your board', 'kanban' ); ?>
		</a>
	</h1>



<?php if ( isset( $_GET['message'] ) ) : ?>
	<div class="updated">
		<p><?php echo $_GET['message']; ?></p>
	</div>
<?php endif // message ?>



	<form action="" method="post">

		<?php echo __( 'Add your purchased add-on licenses below.', 'kanban' ); ?>
		<table class="form-table">
			<tbody>
				<?php echo apply_filters( 'kanban_licenses_licenses', '' ); ?>
			</tbody>
		</table>

		<?php submit_button(
			__( 'Save your Licenses', 'kanban' ),
				'primary',
				'submit'
		); ?>

		<?php wp_nonce_field( 'kanban-licenses', Kanban_Utils::get_nonce() ); ?>

	</form>



</div><!-- wrap -->

