<script class="template" type="t/template" data-id="board-modal-field-tags">

	<div class="panel panel-default" id="board-modal-field-{{%field.id}}" data-field-id="{{%field.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-field-handle">
			</a>

			<a class="h4 panel-title" data-toggle="collapse" data-parent="#board-modal-fields-accordion"
			   href="#board-modal-field-{{%field.id}}-options">
				{{field.label}}{{=field.label}}{{:field.label}}<i><?php _e( 'Field name', 'kanban'); ?></i>{{/field.label}}
			</a>

			<small class="text-muted"><?php _e( 'Tags', 'kanban'); ?></small>
		</div>
		<div id="board-modal-field-{{%field.id}}-options" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/title.php' ?>

					<div class="form-group form-group-toggle col col-sm-12">
						<label><?php _e( 'Allow selecting multiple tags:', 'kanban'); ?></label>

						<div class="btn-group">
							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="select_multiple"
							       name="field-{{%field.id}}-select_multiple"
							       id="field-{{%field.id}}-select_multiple-false"
							       autocomplete="off"
							       {{!fieldOptions.select_multiple}}checked{{/!fieldOptions.select_multiple}}
							value="false">
							<label for="field-{{%field.id}}-select_multiple-false"
							       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="select_multiple"
							       name="field-{{%field.id}}-select_multiple"
							       id="field-{{%field.id}}-select_multiple-true"
							       autocomplete="off"
							       {{fieldOptions.select_multiple}}checked{{/fieldOptions.select_multiple}}
							value="true">
							<label for="field-{{%field.id}}-select_multiple-true"
							       class="btn"><?php _e( 'No', 'kanban'); ?></label>
						</div>
					</div><!--form-group -->

					<div class="form-group form-group-toggle col col-sm-12">
						<label><?php _e( 'Allow adding new tags from field:', 'kanban'); ?></label>

						<div class="btn-group">
							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="add_new_on_field"
							       name="field-{{%field.id}}-add_new_on_field"
							       id="field-{{%field.id}}-add_new_on_field-false"
							       autocomplete="off"
							       {{!fieldOptions.add_new_on_field}}checked{{/!fieldOptions.add_new_on_field}}
							value="false">
							<label for="field-{{%field.id}}-add_new_on_field-false"
							       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="add_new_on_field"
							       name="field-{{%field.id}}-add_new_on_field"
							       id="field-{{%field.id}}-add_new_on_field-true"
							       autocomplete="off"
							       {{fieldOptions.add_new_on_field}}checked{{/fieldOptions.add_new_on_field}}
							value="true">
							<label for="field-{{%field.id}}-add_new_on_field-true"
							       class="btn"><?php _e( 'No', 'kanban'); ?></label>
						</div>
					</div><!--form-group -->

					<div class="form-group col col-sm-12">	
						<div class="board-modal-field-tags-list">
							{{=tagsHtml}}
						</div>
					</div><!--form-group -->

					<div class="form-group col col-sm-12">
						<button type="button" class="btn btn-sm btn-primary" onclick="kanban.fields[{{%field.id}}].optionsAddTag(this);">
							<?php _e( 'Add a tag', 'kanban'); ?>
						</button>

					</div><!--form-group -->

					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/option-layout-col.php' ?>

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