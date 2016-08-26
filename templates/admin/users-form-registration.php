<fieldset id="form-new-user">
	<p>
		<label for="new-user-Login"><?php _e('Username'); ?></label><br>
		<input type="text" id="new-user-login" class="large-text required">
	</p>
	<p>
		<label for="new-user-email"><?php _e('Email'); ?></label><br>
		<input type="email" id="new-user-email" class="large-text required">
	</p>
	<p>
		<label for="new-user-first"><?php _e('First Name'); ?></label><br>
		<input type="text" id="new-user-first" class="large-text required">
	</p>
	<p>
		<label for="new-user-last"><?php _e('Last Name'); ?></label><br>
		<input type="text" id="new-user-last" class="large-text required">
	</p>
		<input type="hidden" id="board_id" value="<?php echo $board->id ?>">
		<input type="hidden" id="<?php echo Kanban_Utils::get_nonce() ?>" value="<?php echo wp_create_nonce('kanban-new-user'); ?>">
		<?php submit_button(
			__( 'Add a user', 'kanban' ),
				'primary button-add-user',
				'button'
		); ?>
</fieldset>

