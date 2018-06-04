<script class="template" type="t/template" data-id="field">

	<div class="field field-{{%field.id}} form-group col col-sm-12 field-{{%card.id}}-{{%field.id}}" data-id="{{%field.id}}" data-card-id="{{%card.id}}" data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}
		<span>{{=fieldvalue.content}}</span>
	</div>

</script>