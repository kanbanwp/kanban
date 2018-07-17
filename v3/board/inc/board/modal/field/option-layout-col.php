
<div class="form-group form-group-radio">
	<label><?php _e( 'Width on the card:', 'kanban'); ?></label>

	<div class="btn-group-vertical">
		<input type="radio"
		       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
		       data-name="view_layout_width"
		       name="field-{{%field.id}}-options-view_layout_width"
		       id="field-{{%field.id}}-options-view_layout_width-12"
		       autocomplete="off"
		       {{fieldOptions.view_layout_width-12}}checked{{/fieldOptions.view_layout_width-12}}
		value="12">
		<label for="field-{{%field.id}}-options-view_layout_width-12" class="btn btn-default">
			100%
		</label>

		<input type="radio"
		       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
		       data-name="view_layout_width"
		       name="field-{{%field.id}}-options-view_layout_width"
		       id="field-{{%field.id}}-options-view_layout_width-6"
		       autocomplete="off"
		       {{fieldOptions.view_layout_width-6}}checked{{/fieldOptions.view_layout_width-6}}
		value="6">
		<label for="field-{{%field.id}}-options-view_layout_width-6" class="btn btn-default">
			50%
		</label>

	</div><!--btn-group-->
</div><!--form-group -->
