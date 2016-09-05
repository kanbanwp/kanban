<li data-id="<?php echo isset($id) ? $id : ''; ?>">
	<label>
		<small class="handle"><?php echo __( 'Reorder', 'kanban' ); ?></small>
	</label>
	<label class="estimate-name">
		<small><?php echo __( 'Name', 'kanban' ); ?>:</small>
		<input type="text" name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][title]" data-name="estimates[new][count][title]" value="<?php echo isset($title) ? $title : ''; ?>">
	</label>
	<label class="estimate-hours">
		<small><?php echo __( 'Hours', 'kanban' ); ?>:</small>
		<input type="text" name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][hours]" data-name="estimates[new][count][hours]" value="<?php echo isset($hours) ? strtolower($hours) : '1'; ?>">
	</label>
	<label style="float: right; margin: .236em .5em;">
		<button type="button" class="delete">
			<?php echo __( 'Delete', 'kanban' ); ?>
		</button>
	</label>
	<input type="hidden" name="estimates[saved][<?php echo isset($id) ? $id : ''; ?>][position]" data-name="estimates[new][count][position]" value="<?php echo isset($position) ? sprintf('%03d', $position) : ''; ?>" class="position">
</li>
