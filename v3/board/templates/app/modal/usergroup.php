<script class="template" type="t/template" data-id="app-modal-usergroup">

	<div class="panel panel-default" data-usergroup-id="{{%usergroup.id}}">
		<div class="panel-heading">

			<a class="h4 panel-title"
			   data-toggle="collapse"
			   data-parent="#app-modal-usergroups-accordion"
			   href="#app-modal-usergroup-{{%usergroup.id}}">
				{{usergroup.label}}{{=usergroup.label}}{{:usergroup.label}}<span
						class="text-muted"><?php _e( 'New user group', 'kanban'); ?></span>{{/usergroup.label}}
			</a>
		</div>
		<div id="app-modal-usergroup-{{%usergroup.id}}" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<div class="form-group form-group-title col col-sm-12">
						<label><?php _e( 'Title:', 'kanban'); ?></label>
						<input type="text"
						       class="form-control"
						       onfocus="kanban.app.modal.usergroupOnfocus(this);"
						       onblur="kanban.app.modal.usergroupOnblur(this);"
						       onkeyup="kanban.app.modal.usergroupOnkeyup(this, event);"
						       onkeydown="kanban.app.modal.usergroupOnkeydown(this, event);"
						       data-name="label"
						       autocomplete="off"
						       maxlength="24"
						       placeholder="<?php _e( 'User group title', 'kanban'); ?>"
						       value="{{%usergroup.label}}">
					</div><!--form-group-text -->

				</div>

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
							<div class="btn-group">
								<input type="radio"
								       onchange="kanban.app.modal.usergroupSave(this); {{_val.is_title}}kanban.app.modal.usergroupSectionToggle(this, 'show');{{/_val.is_title}}"
								       name="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}"
								       id="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}-0"
								       autocomplete="off"
								       {{!_val.is_checked}}checked{{/!_val.is_checked}}>
								<label for="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}-0"
								       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>
								<input type="radio"
								       onchange="kanban.app.modal.usergroupSave(this); {{_val.is_title}}kanban.app.modal.usergroupSectionToggle(this, 'hide');{{/_val.is_title}}"
								       data-name="capabilities"
								       name="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}"
								       id="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}-1"
								       autocomplete="off"
								       {{_val.is_checked}}checked{{/_val.is_checked}}
								value="{{=_key}}">
								<label for="app-modal-usergroup-{{%usergroup.id}}-cap-{{=_key}}-1"
								       class="btn"><?php _e( 'No', 'kanban'); ?></label>
							</div>
						</div>

					</div><!--form-group -->

					{{/@caps}}

				</div><!--wrapper-form-group -->

				<p class="text-center">
					<a data-toggle="collapse" href="#app-modal-usergroup-{{%usergroup.id}}-delete">
						<?php _e( 'More options', 'kanban'); ?>
						<i class="ei ei-arrow_carrot-down"></i>
					</a>
				</p>

				<div class="collapse" id="app-modal-usergroup-{{%usergroup.id}}-delete">
					<p>
						<button type="button" class="btn btn-danger"
						        onclick="kanban.app.modal.usergroupDelete(this);">
							Delete
						</button>
					</p>
				</div><!--collapse-->

			</div><!--panel-body-->
		</div><!--panel-collapse -->
	</div><!--panel-->

</script>