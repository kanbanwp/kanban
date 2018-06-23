<script class="template" type="t/template" data-id="board-modal-field-colorpicker">

	<div class="panel panel-default" id="board-modal-field-{{%field.id}}" data-field-id="{{%field.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-field-handle">
			</a>

			<a class="h4 panel-title" data-toggle="collapse" data-parent="#board-modal-fields-accordion"
			   href="#board-modal-field-{{%field.id}}-options">
				{{field.label}}{{=field.label}}{{:field.label}}<i><?php _e( 'Field name', 'kanban'); ?></i>{{/field.label}}
			</a>

			<small class="text-muted"><?php _e( 'Color picker', 'kanban'); ?></small>
		</div>
		<div id="board-modal-field-{{%field.id}}-options" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<?php include KANBAN_APP_DIR . '/inc/board/modal/field/title.php' ?>

					<div class="form-group form-group-colorpicker col col-sm-12">
						<label><?php _e( 'Default Color:', 'kanban'); ?></label>

						<div class="dropdown">
							<div class="dropdown">
								<button type="button"
								        class="btn btn-empty btn-color"
								        style="background: {{%fieldOptions.default_content}}"
								        onclick="kanban.fields[{{%field.id}}].optionColorOnclick(this);"
								        data-toggle="dropdown">
								</button>

								<div class="dropdown-menu">
								</div><!--dropdown-menu-->
							</div><!--dropdown-->

						</div><!--dropdown-->
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