<script class="template" type="t/template" data-id="app-modal">

	<div id="app-modal" class="modal-dialog modal-lg">
		<div class="modal-content" data-label="<?php _e( 'App settings', 'kanban' ); ?>">
			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm"><?php _e( 'App settings', 'kanban'); ?></span>
				</div>
				<div id="modal-navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active">
							<a href="javascript:void(0);"
							   data-target="options"
							   id="modal-tab-options"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Options', 'kanban'); ?></a></li>
						</li>
						{{currentUserIsAdmin}}
						<li>
							<a href="javascript:void(0);"
							   data-target="users"
							   id="modal-tab-users"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Users', 'kanban'); ?></a>
						</li>
						{{/currentUserIsAdmin}}
						<li class="pull-right">
							<a href="javascript:void(0);"
							   onclick="kanban.app.modal.close(this);">
								<span class="visible-xs-inline-block"><?php _e( 'Close this window', 'kanban'); ?></span>
								<i class="ei ei-close hidden-xs"></i>
							</a>
						</li>
						<?php /*
						<li class="pull-right">
							<a href="javascript:void(0);"
							   data-target="actions"
							   id="modal-tab-actions"
							   onclick="kanban.app.modal.tabChange(this);">
								<span class="visible-xs-inline-block">More actions</span>
								<i class="ei ei-cog hidden-xs"></i>
							</a>
						</li>
 */ ?>
					</ul>
				</div><!--/.nav-collapse -->
			</div><!--modal-header -->


			<div class="modal-body">

					<div class="tab-content">
						<div  class="tab-pane active" id="modal-tab-pane-options">

							<div class="panel-group" id="app-modal-options-accordion">
								<div class="panel panel-default">
									<div class="panel-heading">
										<a class="h4 panel-title"
										   data-toggle="collapse"
										   data-parent="#app-modal-options-accordion"
										   href="#app-modal-options-options">
											<?php _e('Options', 'kanban') ?>
											</a>
									</div>
									<div id="app-modal-options-options" class="panel-collapse collapse in">
										<div class="panel-body">

											<div class="wrapper-form-group row">

												<div class="form-group form-group-toggle col col-sm-12">
													<label><?php _e( 'Check for live updates:', 'kanban'); ?></label><br>

													<div class="btn-group">
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="do_live_updates_check"
														       name="app-do_live_updates_check"
														       id="app-do_live_updates_check-false"
														       autocomplete="off"
														       {{!optionsUser.do_live_updates_check}}checked{{/!optionsUser.do_live_updates_check}}
														value="false">
														<label for="app-do_live_updates_check-false" class="btn"><?php _e( 'Yes', 'kanban'); ?></label>
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="do_live_updates_check"
														       name="app-do_live_updates_check"
														       id="app-do_live_updates_check-true"
														       autocomplete="off"
														       {{optionsUser.do_live_updates_check}}checked{{/optionsUser.do_live_updates_check}}
														value="true">
														<label for="app-do_live_updates_check-true" class="btn"><?php _e( 'No', 'kanban'); ?></label>
													</div>

												</div><!--form-group -->

												<div class="form-group form-group-text col col-sm-12">
													<label><?php _e( 'Live update check time:', 'kanban'); ?></label>

														<input type="number"
														       class="form-control"
														       step="1"
														       min="1"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="live_updates_check_interval"
														       name="app-live_updates_check_interval"
														       id="app-live_updates_check_interval"
														       autocomplete="off"
														value="{{optionsUser.live_updates_check_interval}}{{%optionsUser.live_updates_check_interval}}{{:optionsUser.live_updates_check_interval}}3{{/optionsUser.live_updates_check_interval}}">

												</div><!--form-group -->

												<div class="form-group form-group-radio col col-sm-12">
													<label><?php _e( 'First day of the week:', 'kanban'); ?></label><br>

													<div class="btn-group-vertical">
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="first_day_of_week"
														       name="app-first_day_of_week"
														       id="first_day_of_week-sunday"
														       autocomplete="off"
														       {{optionsUser.first_day_of_week-sunday}}checked{{/optionsUser.first_day_of_week-sunday}}
														value="sunday">
														<label for="first_day_of_week-sunday" class="btn btn-default">
															<?php _e( 'Sunday', 'kanban'); ?>
														</label>

														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="first_day_of_week"
														       name="app-first_day_of_week"
														       id="first_day_of_week-monday"
														       autocomplete="off"
														       {{optionsUser.first_day_of_week-monday}}checked{{/optionsUser.first_day_of_week-monday}}
														value="monday">
														<label for="first_day_of_week-monday" class="btn btn-default">
															<?php _e( 'Monday', 'kanban'); ?>
														</label>

													</div><!--btn-group-->
												</div><!--form-group -->

												<div class="form-group form-group-radio col col-sm-12">
													<label><?php _e( 'Date format:', 'kanban'); ?></label><br>

													<div class="btn-group-vertical">
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="date_view_format"
														       name="field-{{%field.id}}-options-date-format"
														       id="field-{{%field.id}}-options-date-format-mm_dd_yyyy"
														       autocomplete="off"
														       {{optionsUser.date_view_format-mm_dd_yyyy}}checked{{/optionsUser.date_view_format-mm_dd_yyyy}}
														value="mm/dd/yyyy">
														<label for="field-{{%field.id}}-options-date-format-mm_dd_yyyy" class="btn btn-default">
															<?php echo date('m\/d\/Y') ?>
															<?php _e( '(mm/dd/yyyy)', 'kanban'); ?>
														</label>

														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="date_view_format"
														       name="field-{{%field.id}}-options-date-format"
														       id="field-{{%field.id}}-options-date-format-dd_mm_yyyy"
														       autocomplete="off"
														       {{optionsUser.date_view_format-dd_mm_yyyy}}checked{{/optionsUser.date_view_format-dd_mm_yyyy}}
														value="dd/mm/yyyy">
														<label for="field-{{%field.id}}-options-date-format-dd_mm_yyyy" class="btn btn-default">
															<?php echo date('d\/m\/Y') ?>
															<?php _e( '(dd/mm/yyyy)', 'kanban'); ?>
														</label>

														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="date_view_format"
														       name="field-{{%field.id}}-options-date-format"
														       id="field-{{%field.id}}-options-date-format-yyyy_mm_dd"
														       autocomplete="off"
														       {{optionsUser.date_view_format-yyyy_mm_dd}}checked{{/optionsUser.date_view_format-yyyy_mm_dd}}
														value="yyyy-mm-dd">
														<label for="field-{{%field.id}}-options-date-format-yyyy_mm_dd" class="btn btn-default">
															<?php echo date('Y-m-d') ?>
															<?php _e( '(yyyy-mm-dd)', 'kanban'); ?>
														</label>

														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="date_view_format"
														       name="field-{{%field.id}}-options-date-format"
														       id="field-{{%field.id}}-options-date-format-Mdyyyy"
														       autocomplete="off"
														       {{optionsUser.date_view_format-M_d_yyyy}}checked{{/optionsUser.date_view_format-M_d_yyyy}}
														value="M d, yyyy">
														<label for="field-{{%field.id}}-options-date-format-Mdyyyy" class="btn btn-default">
															<?php echo date('M j, Y') ?>
															<?php _e( '(M d, yyyy)', 'kanban'); ?>
														</label>
													</div><!--btn-group-->
												</div><!--form-group -->

												{{currentUserIsAdmin}}
												<div class="form-group form-group-toggle col col-sm-12">
													<label><?php _e('Use Kanban for WordPress for the whole site:', 'kanban'); ?></label><br>

													<div class="btn-group">
														<input type="radio"
														       onchange="kanban.app.modal.optionOnChange(this);"
														       data-name="site_takeover"
														       name="app-site_takeover"
														       id="app-site_takeover-false"
														       autocomplete="off"
														       {{!optionsApp.site_takeover}}checked{{/!optionsApp.site_takeover}}
														value="false">
														<label for="app-site_takeover-false" class="btn"><?php _e( 'Yes', 'kanban'); ?></label>
														<input type="radio"
														       onchange="kanban.app.modal.optionOnChange(this);"
														       data-name="site_takeover"
														       name="app-site_takeover"
														       id="app-site_takeover-true"
														       autocomplete="off"
														       {{optionsApp.site_takeover}}checked{{/optionsApp.site_takeover}}
														value="true">
														<label for="app-site_takeover-true" class="btn"><?php _e( 'No', 'kanban'); ?></label>
													</div>

												</div><!--form-group -->
												{{/currentUserIsAdmin}}
											</div><!--row-->
										</div><!--body-->
									</div><!--collapse-->
								</div><!--options panel-->

								<div class="panel panel-default">
									<div class="panel-heading">
										<a class="h4 panel-title"
										   data-toggle="collapse"
										   data-parent="#app-modal-options-accordion"
										   href="#app-modal-options-notification">
											<?php _e('Notifications', 'kanban') ?>
										</a>
									</div>
									<div id="app-modal-options-notification" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="wrapper-form-group row">

												{{currentUserIsAdmin}}
												<div class="form-group form-group-text col col-sm-12">
													<label><?php _e( 'From name:', 'kanban'); ?></label>
													<input type="text"
													       class="form-control"
													       onfocus="kanban.app.modal.optionOnfocus(this);"
													       onkeydown="kanban.app.modal.optionOnkeydown(this, event);"
													       onblur="kanban.app.modal.optionOnblur(this);"
													       data-name="notification_from_name"
													       maxlength="124"
													       autocomplete="off"
													       placeholder="<?php _e( 'From name', 'kanban'); ?>"
													       value="{{%optionsApp.notification_from_name}}">
												</div><!--form-group -->
												{{/currentUserIsAdmin}}

												{{currentUserIsAdmin}}
												<div class="form-group form-group-text col col-sm-12">
													<label><?php _e( 'From email:', 'kanban'); ?></label>
													<input type="text"
													       class="form-control"
													       onfocus="kanban.app.modal.optionOnfocus(this);"
													       onkeydown="kanban.app.modal.optionOnkeydown(this, event);"
													       onblur="kanban.app.modal.optionOnblur(this);"
													       data-name="notification_from_email"
													       maxlength="124"
													       autocomplete="off"
													       placeholder="<?php _e( 'From email', 'kanban'); ?>"
													       value="{{%optionsApp.notification_from_email}}">
												</div><!--form-group -->
												{{/currentUserIsAdmin}}

												<div class="form-group form-group-toggle col col-sm-12">
													<label><?php _e('Send notification emails:', 'kanban'); ?></label><br>

													<div class="btn-group">
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="do_notifications"
														       name="app-do_notifications"
														       id="app-do_notifications-false"
														       autocomplete="off"
														       {{!optionsUser.do_notifications}}checked{{/!optionsUser.do_notifications}}
														value="false">
														<label for="app-do_notifications-false" class="btn"><?php _e( 'Yes', 'kanban'); ?></label>
														<input type="radio"
														       onchange="kanban.app.modal.optionUserOnChange(this);"
														       data-name="do_notifications"
														       name="app-do_notifications"
														       id="app-do_notifications-true"
														       autocomplete="off"
														       {{optionsUser.do_notifications}}checked{{/optionsUser.do_notifications}}
														value="true">
														<label for="app-do_notifications-true" class="btn"><?php _e( 'No', 'kanban'); ?></label>
													</div>

												</div><!--form-group -->
											</div><!--wrapper-form-group-->
										</div><!--body-->
									</div><!--collapse-->
								</div><!--panel-->
								<?php /*
								<div class="panel panel-default">
									<div class="panel-heading">
										<a class="h4 panel-title"
										   data-toggle="collapse"
										   data-parent="#app-modal-options-accordion"
										   href="#app-modal-options-colors">
											<?php _e('Colors', 'kanban') ?>
										</a>
									</div>
									<div id="app-modal-options-colors" class="panel-collapse collapse">
										<div class="panel-body">
											colors
										</div><!--body-->
									</div><!--collapse-->
								</div><!--colors panel-->
 */ ?>
							</div>

						</div><!--tab-pane-->

						{{currentUserIsAdmin}}
						<div  class="tab-pane" id="modal-tab-pane-users">
							<?php /*
							<div class="text-right">
								Select:
								<button type="button" class="btn btn-xs btn-link" onclick="kanban.app.modal.userSelectAll()">
									All
								</button>
								<button type="button" class="btn btn-xs btn-link" onclick="kanban.app.modal.userSelectNone()">
									None
								</button>
							</div>
 */ ?>
							<div class="clearfix panel-group" id="app-modal-users-accordion">
								{{=usersHtml}}
							</div>

							<select id="app-modal-user-find-control"							        
							        autocomplete="off"></select>

						</div><!--tab-pane-->
						{{/currentUserIsAdmin}}

						<div  class="tab-pane" id="modal-tab-pane-usergroups">
							<div class="panel-group" id="app-modal-usergroups-accordion">
								{{=usergroupsHtml}}
							</div>

							<p>
								<button type="button" class="btn btn-default btn-sm"
								        onclick="kanban.app.modal.usergroupAdd(this);">
									<?php _e( 'Add a user group', 'kanban'); ?>
								</button>
							</p>

						</div><!--tab-pane-->

						<div  class="tab-pane" id="modal-tab-pane-actions">
							<div class="wrapper-form-group row">


							</div>

						</div><!--tab-pane-->
					</div><!--tab-panes-->
				</div><!--modal-body-->

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>