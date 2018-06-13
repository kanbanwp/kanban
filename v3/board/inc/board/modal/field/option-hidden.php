<div class="form-group form-group-toggle col col-sm-12">
	<label><?php _e( 'Hide it:', 'kanban'); ?></label>

	<div class="btn-group">
		<input type="radio"
		       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
		       data-name="is_hidden"
		       name="board-{{%field.id}}-is_hidden"
		       id="board-{{%field.id}}-is_hidden-false"
		       autocomplete="off"
		       {{!fieldOptions.is_hidden}}checked{{/!fieldOptions.is_hidden}}
		value="false">
		<label for="board-{{%field.id}}-is_hidden-false"
		       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

		<input type="radio"
		       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
		       data-name="is_hidden"
		       name="board-{{%field.id}}-is_hidden"
		       id="board-{{%field.id}}-is_hidden-true"
		       autocomplete="off"
		       {{fieldOptions.is_hidden}}checked{{/fieldOptions.is_hidden}}
		value="true">
		<label for="board-{{%field.id}}-is_hidden-true"
		       class="btn"><?php _e( 'No', 'kanban'); ?></label>
	</div>
</div><!--form-group -->
	
	