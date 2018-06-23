<script class="template" type="t/template" data-id="field-file-attachment">

	<div class="list-group-item">

		<input type="button" class="attachment attachment-file" value="{{%attachment.name}}" data-href="{{%attachment.href}}">

		<button type="button" class="btn btn-xs btn-link btn-fade btn-delete"
		onclick="kanban.fields[{{%field.id}}].deleteAttachment(this);">
			<?php _e('Delete', 'kanban') ?>
		</button>
	</div>
</script>