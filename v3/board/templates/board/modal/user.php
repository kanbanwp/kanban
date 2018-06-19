<script class="template" type="t/template" data-id="board-modal-user">

	<div class="panel panel-default" data-user-id="{{%user.id}}">
		<div class="panel-heading">

			<a class="h4 panel-title"
			   data-toggle="collapse"
			   data-parent="#board-modal-users-accordion"
			   href="#board-modal-user-{{%user.id}}">
				{{=user.display_name_long}}
			</a>
		</div>
		<div id="board-modal-user-{{%user.id}}" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">

				<div class="wrapper-form-group wrapper-form-group-toggles row">

					{{@caps}}

					<div class="form-group form-group-toggle {{=_val.classes}} {{_val.is_title}}form-group-title{{/_val.is_title}} col col-sm-12"
					     style="{{_val.is_hidden}}display: none;{{/_val.is_hidden}}"
					     data-cap="{{=_key}}">
						<div class="pull-left">
							<h5>{{=_val.label}}</h5>
							{{_val.description}}
							<small>{{=_val.description}}</small>
							{{/_val.description}}
						</div>

						<div class="pull-right">
							{{_val.is_readonly}}<i class="ei ei-lock"></i>{{/_val.is_readonly}}
							<div class="btn-group {{_val.is_readonly}}disabled{{/_val.is_readonly}}">
								<input type="radio"
								       onchange="kanban.boards[{{%board.id}}].modal.userSave(this); {{_val.is_title}}kanban.boards[{{%board.id}}].modal.userSectionToggle(this, 'show');{{/_val.is_title}}"
								       name="app-modal-user-{{%user.id}}-cap-{{=_key}}"
								       id="app-modal-user-{{%user.id}}-cap-{{=_key}}-0"
								       autocomplete="off"
								       {{_val.is_readonly}}disabled{{/_val.is_readonly}}
								       {{!_val.is_checked}}checked{{/!_val.is_checked}}>
								<label for="app-modal-user-{{%user.id}}-cap-{{=_key}}-0"
								       style="{{user.is_admin}}display: none{{/user.is_admin}}"
								       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>
								<input type="radio"
								       onchange="kanban.boards[{{%board.id}}].modal.userSave(this); {{_val.is_title}}kanban.boards[{{%board.id}}].modal.userSectionToggle(this, 'hide');{{/_val.is_title}}"
								       data-name="capabilities"
								       name="app-modal-user-{{%user.id}}-cap-{{=_key}}"
								       id="app-modal-user-{{%user.id}}-cap-{{=_key}}-1"
								       autocomplete="off"
								       {{_val.is_readonly}}disabled{{/_val.is_readonly}}
								       {{_val.is_checked}}checked{{/_val.is_checked}}
								value="{{=_key}}">
								<label for="app-modal-user-{{%user.id}}-cap-{{=_key}}-1"
								       class="btn"><?php _e( 'No', 'kanban'); ?></label>
							</div>
						</div>

					</div><!--form-group -->

					{{/@caps}}

				</div><!--wrapper-form-group -->

				{{allowDelete}}
				<p class="text-center">
					<a data-toggle="collapse" class="btn btn-default btn-block" href="#board-modal-user-{{%user.id}}-delete">
						<?php _e( 'More options', 'kanban'); ?>
						<i class="ei ei-arrow_carrot-down"></i>
					</a>
				</p>

				<div class="collapse" id="board-modal-user-{{%user.id}}-delete">
					<p>
						<button type="button" class="btn btn-danger" data-user-id="{{%user.id}}"
						        onclick="kanban.boards[{{%board.id}}].modal.userDelete(this);">
							<?php _e( 'Delete', 'kanban'); ?>
						</button>
					</p>
				</div><!--collapse-->
				{{/allowDelete}}

			</div><!--panel-body-->
		</div><!--panel-collapse -->
	</div><!--panel-->

</script>