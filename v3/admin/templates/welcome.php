<div class="wrap">

	<?php $h2 = 'Kanban Boards for WordPress'; include Kanban::instance()->settings()->path . 'admin/templates/header.php'; ?>

	<p style="text-align: center;">
	<a href="<?php print wp_nonce_url(admin_url('options.php?page=kanban'), 'kanban3_to_2');?>" class="button">
		Switch back version 2
	</a>
	</p>


	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">


				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h3>
							<?php esc_attr_e( 'Getting started', 'kanban' ); ?>
							<a href="#">Hide</a>
						</h3>

						<div class="inside">
							<p><?php esc_attr_e(
									'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare purus. Ut eu ante odio. Interdum et malesuada fames ac ante ipsum primis in faucibus. Proin ac mi urna. Quisque maximus quam sit amet auctor laoreet. Aenean sed porttitor nisi. Aenean pretium turpis ante, et fringilla sapien congue eu.',
									'kanban'
								); ?></p>

							<hr>
						</div>

						<h3>
							<?php esc_attr_e( 'Additional help', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php esc_attr_e(
									'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare purus. Ut eu ante odio. Interdum et malesuada fames ac ante ipsum primis in faucibus. Proin ac mi urna. Quisque maximus quam sit amet auctor laoreet. Aenean sed porttitor nisi. Aenean pretium turpis ante, et fringilla sapien congue eu.',
									'kanban'
								); ?></p>

							<hr>
						</div>

						<h3>
							<?php esc_attr_e( 'Advanced tips and tricks', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php esc_attr_e(
									'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare purus. Ut eu ante odio. Interdum et malesuada fames ac ante ipsum primis in faucibus. Proin ac mi urna. Quisque maximus quam sit amet auctor laoreet. Aenean sed porttitor nisi. Aenean pretium turpis ante, et fringilla sapien congue eu.',
									'kanban'
								); ?></p>

						</div>

					</div>
				</div>

			</div>

			<aside id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h3><?php esc_attr_e(
									'Stay up-to-date', 'kanban'
								); ?></h3>

						<div class="inside">
							<p><?php esc_attr_e(
									'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare purus. Ut eu ante odio. Interdum et malesuada fames ac ante ipsum primis in faucibus. Proin ac mi urna. Quisque maximus quam sit amet auctor laoreet. Aenean sed porttitor nisi. Aenean pretium turpis ante, et fringilla sapien congue eu.',
									'kanban'
								); ?></p>

						</div>

					</div>

				</div>

			</aside>

		</div>

	</div>
</div>
