<li data-id="<?php echo isset($id) ? $id : '' ?>">
	<small class="handle"><?php echo __('Reorder', Kanban::get_text_domain()) ?></small>
	<label class="estimate-name">
		<?php echo __('Name', Kanban::get_text_domain()) ?>: 
		<input name="estimates[saved][<?php echo isset($id) ? $id : '' ?>][title]" data-name="estimates[new][count][title]" value="<?php echo isset($title) ? $title : '' ?>">
	</label>
	<label class="estimate-hours">
		<?php echo __('Hours', Kanban::get_text_domain()) ?>: 
		<input name="estimates[saved][<?php echo isset($id) ? $id : '' ?>][hours]" data-name="estimates[new][count][hours]" value="<?php echo isset($hours) ? strtolower($hours) : '1' ?>">
	</label>
	<input type="hidden" name="estimates[saved][<?php echo isset($id) ? $id : '' ?>][position]" data-name="estimates[new][count][position]" value="<?php echo isset($position) ? $position : '' ?>" class="position">
	<button type="button" class="delete"><?php echo __('Delete', Kanban::get_text_domain()) ?></button>
</li>