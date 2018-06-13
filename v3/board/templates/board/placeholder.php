<script class="template" type="t/template" data-id="board-placeholder">

	{{isCreateBoards}}
	<h3><?php _e( 'Let\'s get started!', 'kanban' ); ?></h3>
	<p class="lead">
		<?php _e( sprintf(
			'Create your first board by clicking the %s above.',
			'<i class="ei ei-plus" style="vertical-align: baseline;"></i>'
		), 'kanban' ); ?>
	</p>
	<p>
		<?php _e('Or', 'kanban') ?>
		<button type="button"
		   onclick="kanban.app.presetsToggleModal(this);"
		   data-add="board, lanes and fields"
		   class="btn btn-primary">
			<?php _e('Choose from a preset', 'kanban') ?>
		</button>
	</p>
	{{:isCreateBoards}}
	{{isAdmin}}

	<h3><?php _e( 'Let\'s get started!', 'kanban' ); ?></h3>
	<p class="lead">
		<?php _e( sprintf(
			'See what you can do by using the %s menu below.',
			'<i class="ei ei-cog"  style="vertical-align: baseline;"></i>'
		), 'kanban' ); ?>
	</p>

	{{:isAdmin}}

	{{isLoggedIn}}

	<h3><?php _e( 'Whoops!', 'kanban'); ?></h3>
	<p class="lead">
		<?php _e( 'Looks like you haven\'t been added to the app yet.
		Talk to the site admin to get access.', 'kanban'); ?>
	</p>

	{{:isLoggedIn}}

	<h3><?php _e( 'Please sign in!', 'kanban'); ?></h3>
	<p class="lead">
		<?php _e( 'Sign into WordPress to get started.
		If you do not have an account, talk to the site admin to get access.', 'kanban'); ?>
	</p>

	{{/isLoggedIn}}

	{{/isAdmin}}
	{{/isCreateBoards}}

</script>