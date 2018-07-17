<script class="template" type="t/template" data-id="board-modal-field-time">

	<div class="panel panel-default" id="board-modal-field-{{%field.id}}" data-field-id="{{%field.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-field-handle">
			</a>

			<a class="h4 panel-title" data-toggle="collapse" data-parent="#board-modal-fields-accordion"
			   href="#board-modal-field-{{%field.id}}-settings">
				{{field.label}}{{=field.label}}{{:field.label}}<i><?php _e( 'Field name', 'kanban'); ?></i>{{/field.label}}
			</a>

			<small class="text-muted"><?php _e( 'Time', 'kanban'); ?></small>
		</div>
		<div id="board-modal-field-{{%field.id}}-settings" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/title.php' ?>

					<div class="form-group form-group-title col col-sm-12">
						<label><?php _e( 'Step:', 'kanban'); ?></label>
						<input type="number"
						       class="form-control"
						       step=".01"
						       min="0"
						       onfocus="kanban.fields[{{%field.id}}].stepOnfocus(this);"
						       onkeydown="kanban.fields[{{%field.id}}].stepOnkeydown(this, event);"
						       onblur="kanban.fields[{{%field.id}}].stepOnblur(this);"
						       autocomplete="off"
						       placeholder="<?php _e('Set step', 'kanban'); ?>"
						       value="{{%fieldOptions.step}}">
					</div><!--form-group -->

					<div class="form-group form-group-toggle col col-sm-12">
						<label><?php _e( 'Show estimate:', 'kanban'); ?></label>

						<div class="btn-group">
							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="show_estimate"
							       name="field-{{%field.id}}-show_estimate"
							       id="field-{{%field.id}}-show_estimate-false"
							       autocomplete="off"
							       {{!fieldOptions.show_estimate}}checked{{/!fieldOptions.show_estimate}}
							value="false">
							<label for="field-{{%field.id}}-show_estimate-false"
							       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

							<input type="radio"
							       onchange="kanban.fields[{{%field.id}}].optionOnChange(this);"
							       data-name="show_estimate"
							       name="field-{{%field.id}}-show_estimate"
							       id="field-{{%field.id}}-show_estimate-true"
							       autocomplete="off"
							       {{fieldOptions.show_estimate}}checked{{/fieldOptions.show_estimate}}
							value="true">
							<label for="field-{{%field.id}}-show_estimate-true"
							       class="btn"><?php _e( 'No', 'kanban'); ?></label>
						</div>
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