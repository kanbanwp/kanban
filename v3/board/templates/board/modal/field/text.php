<script class="template" type="t/template" data-id="board-modal-field-text">

	<div class="panel panel-default" id="board-modal-field-{{%field.id}}" data-field-id="{{%field.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-field-handle">
			</a>

			<a class="h4 panel-title" data-toggle="collapse" data-parent="#board-modal-fields-accordion"
			   href="#board-modal-field-{{%field.id}}-options">
				{{field.label}}{{=field.label}}{{:field.label}}<i><?php _e( 'Field name', 'kanban'); ?></i>{{/field.label}}
			</a>

			<small class="text-muted"><?php _e( 'Text', 'kanban'); ?></small>
		</div>
		<div id="board-modal-field-{{%field.id}}-options" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/title.php' ?>

					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/option-placeholder.php' ?>

					<div class="form-group form-group-toggle col col-sm-12">
						<label><?php _e( 'Allow file attachments:', 'kanban'); ?></label>

						<div class="btn-group">
							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="allow_files"
							       name="field-{{%field.id}}-allow_files"
							       id="field-{{%field.id}}-allow_files-false"
							       autocomplete="off"
							       {{!fieldOptions.allow_files}}checked{{/!fieldOptions.allow_files}}
							value="false">
							<label for="field-{{%field.id}}-allow_files-false"
							       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="allow_files"
							       name="field-{{%field.id}}-allow_files"
							       id="field-{{%field.id}}-allow_files-true"
							       autocomplete="off"
							       {{fieldOptions.allow_files}}checked{{/fieldOptions.allow_files}}
							value="true">
							<label for="field-{{%field.id}}-allow_files-true"
							       class="btn"><?php _e( 'No', 'kanban'); ?></label>
						</div>
					</div><!--form-group -->
					
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/option-hidden.php' ?>

				</div><!--wrapper-form-group-->

				<?php include KANBAN_APP_DIR . '/inc/board/modal/field/more-options.php' ?>

				<div class="collapse" id="board-modal-field-{{%field.id}}-actions">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/option-delete.php' ?>
				</div><!--collapse-->

			</div><!--panel-body-->
		</div><!--panel-collapse-->
	</div><!--panel-->

</script>