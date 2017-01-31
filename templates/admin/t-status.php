<li>
	<label>
		<small class="handle"><?php echo __( 'Reorder', 'kanban' ); ?></small>
	</label>
	<label class="status-name">
		<small><?php echo __( 'Name', 'kanban' ); ?>:</small>
		<input type="text" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][title]" data-name="statuses[new][count][title]" value="<?php echo isset($status->title) ? esc_attr($status->title) : ''; ?>">
	</label>
	<label class="status-color_hex">
		<input type="text" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][color_hex]" data-name="statuses[new][count][color_hex]" class="color-picker" value="<?php echo isset($status->color_hex) ? esc_attr(strtolower($status->color_hex)) : '#CCCCCC'; ?>"  data-default-color="#CCCCCC">
	</label>
	<label class="status-wip_task_limit">
		<small><?php echo __( 'WIP', 'kanban' ); ?>:</small>
		<input type="number" min="0" step="1" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][wip_task_limit]" width="3" maxlength="3" data-name="statuses[new][count][wip_task_limit]" value="<?php echo isset($status->wip_task_limit) ? esc_attr($status->wip_task_limit) : ''; ?>" placeholder="<?php echo __( 'WIP', 'kanban' ); ?>" style="width: 4em;">
	</label>
<?php if ( isset($status->id) ) : ?>
	<label class="status_auto_archive">
		<small><?php echo __( 'Auto-archive', 'kanban' ); ?>:</small>
		<input type="checkbox" name="settings[status_auto_archive][]" value="<?php echo isset($status->id) ? $status->id : ''; ?>" <?php echo isset($settings['status_auto_archive']) && is_array($settings['status_auto_archive']) && in_array($status->id, $settings['status_auto_archive']) ? 'checked' : '' ?>>
	</label>
<?php endif ?>
	<label style="float: right; margin: .5em;">
		<button type="button" class="delete">
			<?php echo __( 'Delete', 'kanban' ); ?>
		</button>
	</label>
	<input type="hidden" name="statuses[saved][<?php echo isset($status->id) ? $status->id : ''; ?>][position]" data-name="statuses[new][count][position]" value="<?php echo isset($status->position) ? sprintf('%03d', $status->position) : ''; ?>" class="position">
</li>
