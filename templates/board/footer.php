<footer id="page-footer" class="navbar">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
					data-target="#page-footer-navbar">
				<span class="sr-only"><?php echo __( 'Toggle navigation', 'kanban' ) ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>

		<div id="page-footer-navbar" class="navbar-collapse collapse">
			<form class="navbar-form navbar-nav" id="page-search">
				<div class="form-group has-feedback">
					<input type="search" placeholder="<?php echo __( 'Search', 'kanban' ) ?>" class="form-control"
						   id="board-search">
					<span class="glyphicon glyphicon-remove form-control-feedback" id="board-search-clear"
						  style="display: none;"></span>
				</div>


				<div class="btn-group">
					<a href="#" class="btn btn-default" id="btn-filter-modal-toggle" data-toggle="modal">
						<?php echo __( 'Filter', 'kanban' ) ?>
					</a>
					<a href="#" class="btn btn-default btn-filter-reset" style="display: none;">
						<span class="glyphicon glyphicon-remove" id="board-filter-clear"></span>
					</a>
				</div><!-- btn-group -->

			</form>

			<ul class="nav navbar-nav navbar-right">
				<?php echo apply_filters( 'kanban_page_footer_menu_before', '' ); ?>
				<li class="dropup">
					<a href="#" class="dropdown-toggle" id="footer-settings-toggle" data-toggle="dropdown">
						<?php echo __( 'Settings', 'kanban' ); ?> <span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li class="hidden-xs">
							<a href="#" class="btn-view-toggle" id="btn-view-all-cols" title="shift + A">
								<span class="glyphicon glyphicon-ok"></span>
								<?php echo __( 'Show all columns', 'kanban' ) ?>
							</a>
						</li>

						<li>
							<a href="#" class="btn-view-toggle" id="btn-view-compact" title="shift + C">
								<span class="glyphicon glyphicon-ok"></span>
								<?php echo __( 'Compact View', 'kanban' ) ?>
							</a>
						</li>
						<li>
							<a href="#" class="btn-view-toggle" id="btn-view-fullscreen" title="shift + U">
								<span class="glyphicon glyphicon-ok"></span>
								<?php echo __( 'Full Screen', 'kanban' ) ?>
							</a>
						</li>
						<li class="divider"></li>
						<?php echo apply_filters( 'kanban_page_footer_menu_dropup', '' ); ?>

						<li>
							<a href="#" data-toggle="modal" data-target="#modal-projects" title="shift + P">
								<?php echo __( 'Edit projects', 'kanban' ); ?>
							</a>
						</li>

						<li class="hidden-xs">
							<a href="#" data-toggle="modal" data-target="#modal-keyboard-shortcuts" title="shift + K">
								<?php echo __( 'Keyboard shortcuts', 'kanban' ); ?>
							</a>
						</li>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<li>
								<a href="<?php echo admin_url( 'admin.php?page=kanban_settings' ) ?>">
									<?php echo __( 'Admin', 'kanban' ); ?>
								</a>
							</li>
						<?php endif ?>
						<?php if ( ! is_user_logged_in() ) : ?>
							<li>
								<a href="<?php echo site_url( '/kanban/login' ) ?>">
									<?php echo __( 'Login', 'kanban' ); ?>
								</a>
							</li>
						<?php endif ?>
					</ul>
				</li>
			</ul>
		</div><!--/.nav-collapse -->
	</div><!-- container -->
</footer>


<div id="screen-size">
	<div class="visible-xs" data-size="xs"></div>
	<div class="visible-sm" data-size="sm"></div>
	<div class="visible-md" data-size="md"></div>
	<div class="visible-lg" data-size="lg"></div>
</div>

<?php wp_nonce_field( 'kanban-save', Kanban_Utils::get_nonce() ); ?>

<?php Kanban_Template::add_script( $wp_query->query_vars[ 'kanban' ]->slug ); ?>


</body>
</html>
