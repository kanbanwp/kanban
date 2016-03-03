<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo sprintf( __( 'Add-ons for %s', 'kanban' ), Kanban::get_instance()->settings->pretty_name ); ?>
		<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>" class="page-title-action" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>', 'kanbanboard'); return false;">
			<?php echo __( 'Go to your board', 'kanban' ); ?>
		</a>
	</h1>



<?php if ( ! empty( $addons ) ) : ?>

	<div id="row-addons" class="row-masonry">
<?php foreach ( $addons as $addon ) : ?>
		<div class="addon col">
			<div class="addon-header">
<?php if ( ! empty( $addon->post_img ) ) : ?>
				<img src="<?php echo $addon->post_img; ?>">
<?php else: // post_img ?>
				<h3><?php echo $addon->post_title; ?></h3>
<?php endif // post_img ?>
			</div><!-- header -->
			<div class="addon-body">
				<p>
					<?php echo $addon->post_excerpt; ?>
				</p>
				<p>
					<a href="<?php echo $addon->post_link; ?>?utm_medium=product&amp;utm_source=<?php echo site_url(); ?>&amp;utm_campaign=addons+page" target="_blank" class="button button-primary">
						<?php echo __( 'More info', 'kanban' ); ?>
					</a>
				</p>
			</div><!-- body -->
		</div>
<?php endforeach ?>
	</div><!-- addons -->

<?php else : // addons ?>
	<p>
		<?php echo __( 'Want to get the most out of Kanban for WordPress?', 'kanban' ); ?>
	</p>
	<p>
		<a href="https://kanbanwp.com/addons" target="_blank" class="button button-primary">
			<?php echo __( 'Check out our add-ons on KanbanWP.com', 'kanban' ); ?>
		</a>
	</p>
<?php endif // addons ?>
</div><!-- wrap -->



<script>
jQuery( function( $ )
{
	$( window ).load( function() {
		$( '.row-masonry' ).masonry( {
			itemSelector: '.col',
			transitionDuration: 0
		} );
	} );
} );
</script>
