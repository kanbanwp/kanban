<script class="template" type="t/template" data-id="field-thingslist">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-date col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="clearfix panel-group field-thingslist-accordion" id="field-thingslist-accordion-{{%card.id}}">
			{{=fieldvalue.selectedThings}}
		</div>

		<select class="field-thingslist-thing-find-control"
				onchange="kanban.fields[{{%field.id}}].onThingSelectChange(this);"
				autocomplete="off">{{=selectOptions}}</select>
	</div>

</script>