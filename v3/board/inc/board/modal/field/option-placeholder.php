<div class="form-group form-group-text col col-sm-12">
	<label><?php _e( 'Placeholder:', 'kanban'); ?></label>
	<input type="text"
	       class="form-control"
	       onfocus="kanban.fields[{{%field.id}}].placeholderOnfocus(this);"
	       onkeydown="kanban.fields[{{%field.id}}].placeholderOnkeydown(this, event);"
	       onblur="kanban.fields[{{%field.id}}].placeholderOnblur(this);"
	       data-name="placeholder"
	       maxlength="24"
	       autocomplete="off"
	       placeholder="<?php _e( 'Field placeholder', 'kanban'); ?>"
	       value="{{%fieldOptions.placeholder}}">
</div><!--form-group -->