<script class="template" type="t/template" data-id="field-img">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-img col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="carousel slide field-{{%card.id}}-{{%field.id}}-carousel" data-interval="false">

			<div class="carousel-inner {{isCardWrite}}dropzone{{/isCardWrite}}">
				{{attachmentsHtml}}
				{{=attachmentsHtml}}
				{{:attachmentsHtml}}
				{{isCardWrite}}
				<div class="item active">
					<?php _e('Drop images here', 'kanban') ?>
				</div>
				{{/isCardWrite}}
				{{/attachmentsHtml}}
			</div>

			<a class="left carousel-control" href=".field-{{%card.id}}-{{%field.id}}-carousel" data-slide="prev">
			</a>
			<a class="right carousel-control" href=".field-{{%card.id}}-{{%field.id}}-carousel" data-slide="next">
			</a>
		</div>




	</div>

</script>