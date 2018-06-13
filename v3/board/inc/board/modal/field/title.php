<div class="form-group form-group-title col col-sm-12">
	<label><?php _e( 'Field title:', 'kanban'); ?></label>
	<input type="text"
	       class="form-control"
	       onfocus="kanban.fields[{{%field.id}}].titleOnfocus(this);"
	       onkeydown="kanban.fields[{{%field.id}}].titleOnkeydown(this, event);"
	       onblur="kanban.fields[{{%field.id}}].titleOnblur(this);"
	       autocomplete="off"
	       data-name="label"
	       maxlength="24"
	       placeholder="<?php _e( 'Field title', 'kanban'); ?>"
	       value="{{%field.label}}">
</div><!--form-group -->