<li data-id="<?php echo isset($id) ? $id : ''; ?>">
	<small class="handle"><?php echo __( 'Reorder', 'kanban' ); ?></small>
	<label class="estimate-name">
		<?php echo __( 'Name', 'kanban' ); ?>:
		<input name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][title]" data-name="estimates[new][count][title]" value="<?php echo isset($title) ? $title : ''; ?>">
	</label>
	<label class="estimate-hours">
		<?php echo __( 'Hours', 'kanban' ); ?>:
		<input name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][hours]" data-name="estimates[new][count][hours]" value="<?php echo isset($hours) ? strtolower($hours) : '1'; ?>">
	</label>
	<input type="hidden" name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][position]" data-name="estimates[new][count][position]" value="<?php echo isset($position) ? sprintf('%03d', $position) : ''; ?>" class="position">
	<button type="button" class="delete"><?php echo __( 'Delete', 'kanban' ); ?></button>
</li>
