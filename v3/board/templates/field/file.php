<script class="template" type="t/template" data-id="field-file">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-file col
col-sm-{{field.options.view_layout_width}}{{%field.options.view_layout_width}}{{:field.options.view_layout_width}}12{{/field.options.view_layout_width}}"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="list-group dropzone">
			{{attachmentsHtml}}
			{{=attachmentsHtml}}
			{{:attachmentsHtml}}
			<div class="list-group-item text-muted attachment-placeholder">
				<?php _e('Drop files here', 'kanban') ?>
			</div>
			{{/attachmentsHtml}}
		</div>
	</div>

</script>