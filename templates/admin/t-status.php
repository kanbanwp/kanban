<li>
	<small class="handle"><?php echo __( 'Reorder', 'kanban' ); ?></small>
	<label class="status-name">
		<?php echo __( 'Name', 'kanban' ); ?>:
		<input name="statuses[saved][<?php echo isset($id) ? $id : ''; ?>][title]" data-name="statuses[new][count][title]" value="<?php echo isset($title) ? $title : ''; ?>">
	</label>
	<label class="status-color_hex">
		<input name="statuses[saved][<?php echo isset($id) ? $id : ''; ?>][color_hex]" data-name="statuses[new][count][color_hex]" class="color-picker" value="<?php echo isset($color_hex) ? strtolower($color_hex) : '#CCCCCC'; ?>"  data-default-color="#CCCCCC">
	</label>
	<input type="hidden" name="statuses[saved][<?php echo isset($id) ? $id : ''; ?>][position]" data-name="statuses[new][count][position]" value="<?php echo isset($position) ? sprintf('%03d', $position) : ''; ?>" class="position">
	<button type="button" class="delete"><?php echo __( 'Delete', 'kanban' ); ?></button>
</li>
