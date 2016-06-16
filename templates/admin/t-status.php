<li>
	<small class="handle"><?php echo __( 'Reorder', 'kanban' ); ?></small>
	<label class="status-name">
		<?php echo __( 'Name', 'kanban' ); ?>:
		<input type="text" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][title]" data-name="statuses[new][count][title]" value="<?php echo isset($status->title) ? $status->title : ''; ?>">
	</label>
	<label class="status-color_hex">
		<input type="text" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][color_hex]" data-name="statuses[new][count][color_hex]" class="color-picker" value="<?php echo isset($status->color_hex) ? strtolower($status->color_hex) : '#CCCCCC'; ?>"  data-default-color="#CCCCCC">
	</label>
<?php if ( isset($status->id) ) : ?>
	<label class="status_auto_archive">
		<input type="checkbox" name="settings[status_auto_archive][]" value="<?php echo isset($status->id) ? $status->id : ''; ?>" <?php echo isset($settings['status_auto_archive']) && in_array($status->id, $settings['status_auto_archive']) ? 'checked' : '' ?>> <?php echo __( 'Auto-archive', 'kanban' ); ?>
	</label>
<?php endif ?>
	<input type="hidden" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][position]" data-name="statuses[new][count][position]" value="<?php echo isset($status->position) ? sprintf('%03d', $status->position) : ''; ?>" class="position">
	<button type="button" class="delete"><?php echo __( 'Delete', 'kanban' ); ?></button>
</li>
