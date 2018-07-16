<script class="template" type="t/template" data-id="board-modal-field-users">

	<div class="panel panel-default" id="board-modal-field-{{%field.id}}" data-field-id="{{%field.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-field-handle">
			</a>

			<a class="h4 panel-title" data-toggle="collapse" data-parent="#board-modal-fields-accordion"
			   href="#board-modal-field-{{%field.id}}-options">
				{{field.label}}{{=field.label}}{{:field.label}}<i><?php _e( 'Field name', 'kanban'); ?></i>{{/field.label}}
			</a>

			<small class="text-muted"><?php _e( 'Users', 'kanban'); ?></small>
		</div>
		<div id="board-modal-field-{{%field.id}}-options" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/title.php' ?>

					<div class="form-group form-group-toggle col col-sm-12">
						<label><?php _e( 'Allow selecting multiple users:', 'kanban'); ?></label>

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

					<div class="form-group form-group-radio col col-sm-12">
						<label><?php _e( 'Selectable users:', 'kanban'); ?></label><br>

						<div class="btn-group-vertical">
							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="available_users"
							       name="field-{{%field.id}}-available_users"
							       id="field-{{%field.id}}-available_users-wp"
							       autocomplete="off"
							       {{fieldOptions.available_users-wp}}checked{{/fieldOptions.available_users-wp}}
							value="wp">
							<label for="field-{{%field.id}}-available_users-wp" class="btn btn-default">
								<?php _e( 'All WordPress users', 'kanban'); ?>
							</label>

							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="available_users"
							       name="field-{{%field.id}}-available_users"
							       id="field-{{%field.id}}-available_users-board"
							       autocomplete="off"
							       {{fieldOptions.available_users-board}}checked{{/fieldOptions.available_users-board}}
							value="board">
							<label for="field-{{%field.id}}-available_users-board"
							       class="btn btn-default">
								<?php _e( 'Board users', 'kanban'); ?>
							</label>
						</div><!--btn-group-->
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