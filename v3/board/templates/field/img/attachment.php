<script class="template" type="t/template" data-id="field-img-attachment">

	<div class="item {{is_active}}active{{/is_active}}">
		<img class="attachment" src="{{%attachment.href}}"
		     data-href="{{%attachment.href}}"
		     alt="{{%attachment.name}}">

		<button type="button" class="btn btn-xs btn-default btn-fade btn-delete"
		        onclick="kanban.fields[{{%field.id}}].deleteAttachment(this);">
			<?php _e('Delete', 'kanban') ?>
		</button>

	</div>


</script>