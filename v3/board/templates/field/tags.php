<script class="template" type="t/template" data-id="field-tags">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-tags col
col-sm-{{field.options.view_layout_width}}{{%field.options.view_layout_width}}{{:field.options.view_layout_width}}12{{/field.options.view_layout_width}}"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<input class="field-tags-form-control" type="text" value="{{%fieldvalue.content}}"></input>
	</div>

</script>