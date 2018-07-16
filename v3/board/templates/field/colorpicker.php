<script class="template" type="t/template" data-id="field-colorpicker">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-colorpicker col
	col-sm-{{fieldOptions.view_layout_width}}{{%fieldOptions.view_layout_width}}{{:fieldOptions.view_layout_width}}12{{/fieldOptions.view_layout_width}}"
	     id="field-{{%card.id}}-{{%field.id}}"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="dropdown">
			<button type="button" class="btn btn-empty btn-color"
			        {{isCardWrite}}
			        data-toggle="dropdown"
			        onclick="kanban.fields[{{%field.id}}].onClick(this);"
			        {{/isCardWrite}}
			        style="background:
			        {{fieldvalue.content}}
			            {{%fieldvalue.content}}
		            {{:fieldvalue.content}}
		                {{fieldOptions.default_content}}
		                    {{%fieldOptions.default_content}}
	                    {{/fieldOptions.default_content}}
                    {{/fieldvalue.content}};">
			</button>
			{{isCardWrite}}
			<div class="dropdown-menu">
			</div>
			{{/isCardWrite}}
		</div><!--dropdown-->
	</div>

	{{fieldOptions.view_card_corner}}
	<div class="field-colorpicker-corner"
	     style="border-left-color: {{fieldvalue.content}}{{%fieldvalue.content}}{{/fieldvalue.content}};"></div>
	{{/fieldOptions.view_card_corner}}


</script>