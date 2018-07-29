<div class="wrap">
	<?php echo apply_filters( 'kanban_settings_h1_before', '' ); ?>

	<h1>
		<?php echo __( sprintf( '%s Settings', Kanban::get_instance()->settings->pretty_name ), 'kanban' ); ?>
		<a href="<?php echo Kanban_Template::get_uri() ?>"
		   class="page-title-action" target="_blank" id="btn-go-to-board"
		   onclick="window.open('<?php echo Kanban_Template::get_uri() ?>', 'kanbanboard'); return false;">
			<?php echo __( 'Go to your board', 'kanban' ); ?>
		</a>
	</h1>

	<?php include __DIR__ . '/notice-v3.php'?>

	<?php echo apply_filters( 'kanban_settings_h1_after', '' ); ?>



	<?php if ( empty($statuses) ) : ?>
		<div class="updated kanban-welcome-notice">
			<form action="" method="get">
			<p>
				<?php echo __( 'Customize your Kanban board below or get started by using a preset:', 'kanban' ) ?>
				<button type="button button-primary" class="button kanban-modal-show">
					<?php echo __( 'Choose a preset', 'kanban' ) ?>
				</button>
				<?php if ( isset($_GET['board_id']) ) : ?>
				<input type="hidden" name="board_id" value="<?php echo (int) sanitize_text_field($_GET[ 'board_id' ]) ?>">
			<?php endif // $_GET['board_id'] ?>
				<input type="hidden" name="page" value="kanban">
				<input type="hidden" name="kanban-modal" value="presets">
			</p>
			</form>
		</div>
	<?php endif // $statuses ?>



	<?php if ( isset( $_GET[ 'message' ] ) ) : ?>
		<div class="updated">
			<p><?php echo sanitize_text_field($_GET[ 'message' ]); ?></p>
		</div>
	<?php endif // message ?>


	<h2 class="nav-tab-wrapper">
		<a href="#tab-settings" id="tab-settings-tab"
		   class="nav-tab nav-tab-active"><?php echo __( 'General', 'kanban' ); ?></a>
		<a href="#tab-users" id="tab-users-tab" class="nav-tab"><?php echo __( 'Users', 'kanban' ); ?></a>
		<a href="#tab-statuses" id="tab-statuses-tab" class="nav-tab"><?php echo __( 'Statuses', 'kanban' ); ?></a>
		<a href="#tab-estimates" id="tab-estimates-tab" class="nav-tab"><?php echo __( 'Estimates', 'kanban' ); ?></a>
		<?php
		echo apply_filters( 'kanban_settings_tabs', '' );
		?>
		<a href="#tab-help" id="tab-help-tab" class="nav-tab"><?php echo __( 'Utilities', 'kanban' ); ?></a>
	</h2>


	<form action="" method="post">

		<div class="tab" id="tab-settings">

			<table class="form-table">
				<tbody>
				<tr>
					<th width="33%" scope="row">
						<label for="hour_interval">
							<?php echo __( 'Work hour interval', 'kanban' ); ?><br>
							<small><?php echo __( 'in hours', 'kanban' ); ?></small>
						</label>
					</th>
					<td>
						<input name="settings[hour_interval]" type="number" id="hour_increment" step="0.0001"
							   value="<?php echo isset( $settings[ 'hour_interval' ] ) ? $settings[ 'hour_interval' ] : 1 ?>"
							   class="regular-text">
						<p class="description">
							<?php echo __( 'Example: If you want to track work in 10 minute increments, enter ".1667" here.', 'kanban' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th width="33%" scope="row">
						<?php echo __( 'Hide time tracking', 'kanban' ); ?><br>
						<small>(<?php echo __( 'Estimates, hours', 'kanban' ); ?>)</small>
					</th>
					<td>

						<div class="switch-field">
							<input type="radio" id="hide_time_tracking_1" name="settings[hide_time_tracking]"
								   value="1" <?php echo (bool) $settings[ 'hide_time_tracking' ] ? 'checked' : ''; ?>>
							<label for="hide_time_tracking_1"><?php echo __( 'Yes', 'kanban' ); ?></label>
							<input type="radio" id="hide_time_tracking_0" name="settings[hide_time_tracking]"
								   value="0" <?php echo ! (bool) $settings[ 'hide_time_tracking' ] ? 'checked' : ''; ?>>
							<label for="hide_time_tracking_0"><?php echo __( 'No', 'kanban' ); ?></label>
						</div>

					</td>
				</tr>


				<tr>
					<th width="33%" scope="row">
						<?php echo __( 'Show task IDs', 'kanban' ); ?>
					</th>
					<td>

						<div class="switch-field">
							<input type="radio" id="show_task_ids_1" name="settings[show_task_ids]"
								   value="1" <?php echo (bool) $settings[ 'show_task_ids' ] ? 'checked' : ''; ?>>
							<label for="show_task_ids_1"><?php echo __( 'Yes', 'kanban' ); ?></label>
							<input type="radio" id="show_task_ids_0" name="settings[show_task_ids]"
								   value="0" <?php echo ! (bool) $settings[ 'show_task_ids' ] ? 'checked' : ''; ?>>
							<label for="show_task_ids_0"><?php echo __( 'No', 'kanban' ); ?></label>
						</div>

					</td>
				</tr>

				<tr>
					<th width="33%" scope="row">
						<?php echo __( 'Show all columns', 'kanban' ); ?><br>
						<small><?php echo __( '(Users can still choose their own preference)', 'kanban' ); ?></small>
					</th>
					<td>
						<div class="switch-field">
							<input type="radio" id="show_all_cols_1" name="settings[show_all_cols]"
								   value="1" <?php echo (bool) $settings[ 'show_all_cols' ] ? 'checked' : ''; ?>>
							<label for="show_all_cols_1"><?php echo __( 'Yes', 'kanban' ); ?></label>
							<input type="radio" id="show_all_cols_0" name="settings[show_all_cols]"
								   value="0" <?php echo ! (bool) $settings[ 'show_all_cols' ] ? 'checked' : ''; ?>>
							<label for="show_all_cols_0"><?php echo __( 'No', 'kanban' ); ?></label>
						</div>
					</td>
				</tr>

				<?php /*
					<tr>
						<th width="33%" scope="row">
							<?php echo __( 'Default the view to "compact"', 'kanban' ); ?>
						</th>
						<td>
							<div class="switch-field">
								<input type="radio" id="default_to_compact_view_1" name="settings[default_to_compact_view]" value="1" <?php echo (bool) $settings['default_to_compact_view'] ? 'checked' : ''; ?>>
								<label for="default_to_compact_view_1">Yes</label>
								<input type="radio" id="default_to_compact_view_0" name="settings[default_to_compact_view]" value="0" <?php echo ! (bool) $settings['default_to_compact_view'] ? 'checked' : ''; ?>>
								<label for="default_to_compact_view_0">No</label>
							</div>
						</td>
					</tr>
*/ ?>
				<?php /*
					<tr>
						<th width="33%" scope="row">
							<?php echo __( 'Hide progress bars', 'kanban' ); ?>
						</th>
						<td>
							<div class="switch-field">
								<input type="radio" id="hide_progress_bar_1" name="settings[hide_progress_bar]" value="1" <?php echo (bool) $settings['hide_progress_bar'] ? 'checked' : ''; ?>>
								<label for="hide_progress_bar_1">Yes</label>
								<input type="radio" id="hide_progress_bar_0" name="settings[hide_progress_bar]" value="0" <?php echo ! (bool) $settings['hide_progress_bar'] ? 'checked' : ''; ?>>
								<label for="hide_progress_bar_0">No</label>
							</div>
						</td>
					</tr>
*/ ?>
				<tr>
					<th width="33%" scope="row">
						<?php echo __( 'Use default login screen', 'kanban' ); ?>
					</th>
					<td>
						<div class="switch-field">
							<input type="radio" id="use_default_login_page_1" name="settings[use_default_login_page]"
								   value="1" <?php echo (bool) $settings[ 'use_default_login_page' ] ? 'checked' : ''; ?>>
							<label for="use_default_login_page_1"><?php echo __( 'Yes', 'kanban' ); ?></label>
							<input type="radio" id="use_default_login_page_0" name="settings[use_default_login_page]"
								   value="0" <?php echo ! (bool) $settings[ 'use_default_login_page' ] ? 'checked' : ''; ?>>
							<label for="use_default_login_page_0"><?php echo __( 'No', 'kanban' ); ?></label>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<?php submit_button(
				__( 'Save your Settings', 'kanban' ),
				'primary',
				'submit-settings'
			); ?>


			<a href="#settings-general-advanced"
			   class="slide-toggle"><?php echo __( 'Advanced settings', 'kanban' ); ?></a>

			<div id="settings-general-advanced" style="display: none;">
				<table class="form-table">
					<tbody>
					<tr>
						<th width="33%" scope="row">
							<label for="board_css">
								<?php echo __( 'Board CSS', 'kanban' ); ?><br>
								<small><?php echo __( 'Extra CSS that will applied to all boards', 'kanban' ); ?></small>
							</label>
						</th>
						<td>
							<textarea name="settings[board_css]" id="board_css" class="large-text"
									  rows="4"><?php echo isset( $settings[ 'board_css' ] ) ? stripslashes($settings[ 'board_css' ]) : '' ?></textarea>
						</td>
					</tr>

					<tr>
						<th width="33%" scope="row">
							<?php echo __( 'Disable sync notifications', 'kanban' ); ?>
						</th>
						<td>
							<div class="switch-field">
								<input type="radio" id="disable_sync_notifications_1" name="settings[disable_sync_notifications]"
									   value="1" <?php echo (bool) $settings[ 'disable_sync_notifications' ] ? 'checked' : ''; ?>>
								<label for="disable_sync_notifications_1"><?php echo __( 'Yes', 'kanban' ); ?></label>
								<input type="radio" id="disable_sync_notifications_0" name="settings[disable_sync_notifications]"
									   value="0" <?php echo ! (bool) $settings[ 'disable_sync_notifications' ] ? 'checked' : ''; ?>>
								<label for="disable_sync_notifications_0"><?php echo __( 'No', 'kanban' ); ?></label>
							</div>
						</td>
					</tr>

					<tr>
						<th width="33%" scope="row">
							<?php echo __( 'Sync check interval', 'kanban' ); ?>
						</th>
						<td>
							<input name="settings[updates_check_interval_sec]" type="number" step="1" id="updates_check_interval_sec"
							       value="<?php echo isset( $settings[ 'updates_check_interval_sec' ] ) ? $settings[ 'updates_check_interval_sec' ] : 1 ?>"
							       class="regular-text">
							<p class="description">
								<?php echo __( 'How many seconds between checks.', 'kanban' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th width="33%" scope="row">
							&nbsp;
						</th>
						<td>
							<a href="<?php echo admin_url('admin.php?page=kanban_v3') ?>" class="button">
								Learn more about version 3
							</a>
						</td>
					</tr>

					</tbody>
				</table>
			</div>


		</div><!-- tab-settings -->


		<div class="tab" id="tab-users" style="display: none;">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<div class="x-postbox">

								<table class="form-table">
									<tbody>
									<tr>
										<th width="33%" scope="row">
											<label for="allowed_users">
												<?php echo __( 'Allowed users', 'kanban' ); ?><br>
												<small>
													<?php echo __( '(Users who can make changes to the board)', 'kanban' ); ?>
												</small>
											</label>
										</th>
										<td id="td-allowed_users" class="check-list-wrapper">
											<label style="display: <?php echo count($all_users_arr) < 10 ? 'none' : 'block' ?>">
												<?php echo __('Filter:') ?>
												<input type="text" class="search" />
											</label>
											<ul class="list check-list" data-target="#allowed_users-values" data-name="settings[allowed_users][]">
												<?php foreach ( $all_users_arr as $user_id => $user_name ) : ?>
													<li>
													<label>
														<input type="checkbox"
															   value="<?php echo $user_id; ?>" class="check-list-input" <?php echo is_array( $settings[ 'allowed_users' ] ) ? in_array( $user_id, $settings[ 'allowed_users' ] ) ? 'checked' : '' : ''; ?> autocomplete="off">
														<span class="user_name"><?php echo $user_name; ?></span>
													</label>
													</li>
												<?php endforeach // $all_users_arr; ?>
											</ul>
											<ul class="list-pagination" style="display: <?php echo count($all_users_arr) < 21 ? 'none' : 'block' ?>"></ul>
											<div id="allowed_users-values">
												<?php foreach ( $all_users_arr as $user_id => $user_name ) :
													if ( is_array( $settings[ 'allowed_users' ] ) ) :
														if ( in_array( $user_id, $settings[ 'allowed_users' ] ) ) :
													?>
															<input type="hidden"
															       name="settings[allowed_users][]"
															       value="<?php echo $user_id; ?>" class="check-list-item">
												<?php endif; endif; endforeach // $all_users_arr; ?>
											</div>
										</td>
									</tr>

									<tr>
										<th width="33%" scope="row">
											<?php echo __( 'Assign new tasks to', 'kanban' ) ?>
										</th>
										<td>
											<div class="switch-field vertical radio" id="default_assigned_switch">
												<input type="checkbox" id="default_assigned_to_creator"
													   name="settings[default_assigned_to_creator]"
													   value="1" <?php echo isset( $settings[ 'default_assigned_to_creator' ] ) && (bool) $settings[ 'default_assigned_to_creator' ] ? 'checked' : '' ?>>
												<label for="default_assigned_to_creator">
													<?php echo __( 'The user who created it', 'kanban' ) ?>
												</label>

												<input type="checkbox" id="default_assigned_to_first"
													   name="settings[default_assigned_to_first]"
													   value="1" <?php echo isset( $settings[ 'default_assigned_to_first' ] ) && (bool) $settings[ 'default_assigned_to_first' ] ? 'checked' : '' ?>>
												<label for="default_assigned_to_first">
													<?php echo __( 'The first user to move it', 'kanban' ) ?>
												</label>
												<input type="checkbox"
													   id="default_assigned_to" <?php echo ! (bool) $settings[ 'default_assigned_to_creator' ] && ! (bool) $settings[ 'default_assigned_to_first' ] ? 'checked' : '' ?>>
												<label for="default_assigned_to">
													<?php echo __( 'A single user', 'kanban' ) ?>
												</label>
											</div>
										</td>
									</tr>

									<tr id="tr-default_assigned_to"
										style="<?php echo (bool) $settings[ 'default_assigned_to_creator' ] || (bool) $settings[ 'default_assigned_to_first' ] ? 'display: none;' : '' ?>">
										<th width="33%" scope="row">
											<label for="default_assigned_to_select">
												<?php echo __( 'Assign new tasks to', 'kanban' ); ?>
											</label>
										</th>
										<td>
											<select id="default_assigned_to_select" name="settings[default_assigned_to]"
													style="min-width: 10em;">
												<?php foreach ( $all_users_arr as $user_id => $user_name ) : ?>
													<option value="<?php echo $user_id; ?>" <?php echo isset( $settings[ 'default_assigned_to' ] ) ? $user_id == $settings[ 'default_assigned_to' ] ? 'selected' : '' : ''; ?>>
														<?php echo $user_name; ?>
													</option>
												<?php endforeach // $all_users_arr; ?>
												<option value="" <?php echo ! isset( $settings[ 'default_assigned_to' ] ) || empty( $settings[ 'default_assigned_to' ] ) ? 'selected' : ''; ?>>
													<?php echo __( 'No one', 'kanban' ); ?>
												</option>
											</select>
										</td>
									</tr>

									<?php echo apply_filters( 'kanban_settings_tab_users_content', '', $board ); ?>

									</tbody>
								</table>

								<?php submit_button(
									__( 'Save your Settings', 'kanban' ),
									'primary',
									'submit-users'
								); ?>

							</div><!-- postbox -->
						</div>
					</div>


					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<div class="postbox">

								<h2>
									<span>
									<?php echo __( 'Add a user', 'kanban' ); ?>
									</span>
								</h2>

								<div class="inside">
									<p>
										<i>
											<?php echo __( 'Note: user will immediately be added to the Kanban board.', 'kanban' ); ?>
										</i>
									</p>
									<?php echo Kanban_Template::render_template( 'admin/users-form-registration', array( 'board' => $board ) ); ?>
								</div>

							</div>
						</div>
					</div>

				</div>
				<br class="clear">
			</div>

		</div><!-- tab-users -->


		<div class="tab" id="tab-statuses" style="display: none;">

			<ol id="list-statuses" class="sortable">
				<?php foreach ( $statuses as $status_id => $status ) : ?>
					<?php echo Kanban_Template::render_template(
						'admin/t-status',
						array( 'status' => $status, 'settings' => $settings )
					); ?>
				<?php endforeach // statuses ?>
			</ol><!-- sortable -->
			<span style="float: right">
				<?php echo __( 'Auto-archive', 'kanban' ); ?>
				: <?php echo sprintf( __( 'Tasks will be automatically deleted after %s days', 'kanban' ), '<input type="number" name="settings[status_auto_archive_days]" min="1" max="120" step="1" value="' . ( isset( $settings[ 'status_auto_archive_days' ] ) ? $settings[ 'status_auto_archive_days' ] : 30 ) . '">' ); ?>
			</span>
			<p>
				<button type="button" class="button button-sortable-add" data-t="t-status">
					<?php echo __( 'Add another status', 'kanban' ); ?>
				</button>
			</p>

			<?php submit_button(
				__( 'Save your Settings', 'kanban' ),
				'primary',
				'submit-statuses'
			); ?>
		</div><!-- tab-statuses -->


		<div class="tab" id="tab-estimates" style="display: none;">

			<ol id="list-estimates" class="sortable">
				<?php foreach ( $estimates as $estimate_id => $estimate ) : ?>
					<?php echo Kanban_Template::render_template( 'admin/t-estimate', (array) $estimate ); ?>
				<?php endforeach // statuses ?>
			</ol><!-- sortable -->
			<p>
				<button type="button" class="button button-sortable-add" data-t="t-estimate">
					<?php echo __( 'Add another estimate', 'kanban' ); ?>
				</button>
			</p>


			<table class="form-table">
				<tbody>
				<tr>
					<th width="33%" scope="row">
						<label for="hour_interval">
							<?php echo __( 'Default estimate', 'kanban' ); ?>
						</label>
					</th>
					<td>
						<select name="settings[default_estimate]" style="min-width: 10em;">
							<?php foreach ( $estimates as $estimate_id => $estimate ) : ?>
								<option value="<?php echo $estimate->id; ?>" <?php echo isset( $settings[ 'default_estimate' ] ) ? $estimate->id == $settings[ 'default_estimate' ] ? 'selected' : '' : ''; ?>>
									<?php echo $estimate->title; ?>
								</option>
							<?php endforeach // $estimates; ?>
							<option value="" <?php echo ! isset( $settings[ 'default_estimate' ] ) || empty( $settings[ 'default_estimate' ] ) ? 'selected' : ''; ?>>
								<?php echo __( 'None', 'kanban' ); ?>
							</option>
						</select>
					</td>
				</tr>

				<?php echo apply_filters( 'kanban_settings_tab_estimates_content', '' ); ?>

				</tbody>
			</table>

			<?php submit_button(
				__( 'Save your Settings', 'kanban' ),
				'primary',
				'submit-estimates'
			); ?>
		</div><!-- tab-estimates -->


		<?php echo apply_filters( 'kanban_settings_tabs_content', '', $board ); ?>


		<div class="tab" id="tab-licenses" style="display: none;">
			<p>
				<?php echo __( 'Add your purchased add-on licenses below.', 'kanban' ); ?>
			</p>
			<table class="form-table">
				<tbody>
				<?php echo apply_filters( 'kanban_settings_licenses', '' ); ?>
				</tbody>
			</table>

			<?php submit_button(
				__( 'Save your Settings', 'kanban' ),
				'primary',
				'submit-licenses'
			); ?>
		</div><!-- tab-licenses -->


		<?php wp_nonce_field( 'kanban-options', Kanban_Utils::get_nonce() ); ?>


		<div class="tab" id="tab-help" style="display: none;">

			<h3><?php echo __( 'Import', 'kanban' ) ?></h3>

			<div id="kanban-import" style="display:none;">

				<div>
					<p style="font-weight: bold;">
						<?php echo __( '<b style="color: red;">Caution!</b> This will overwrite all data in the Kanban database tables!', 'kanban' ) ?>
					</p>

					<ol>
						<li>
							<?php echo __( 'Upload a Kanban data file (file type .kanbanwp) you exported from a Kanban install.', 'kanban' ) ?>
						</li>
						<li>
							<?php echo __( 'Before importing begins, your current data will be downloaded to your computer 
							(The file will also be saved to the "kanban-exports" folder in your "uploads" folder).
							You can use this file to restore your data at a later date.', 'kanban' ) ?>
						</li>
						<li>
							<?php echo __( 'Your current Kanban data will be deleted and replaced.
							Please do not stop the importer or your import may be interrupted and some data may not be imported.', 'kanban' ) ?>
						</li>
					</ol>

					<hr>

					<p>
						<label>
							<?php echo __( 'Select your Kanban data file (file type .kanbanwp):', 'kanban' ) ?><br>
							<input type="file" name="kanban_import">
						</label>
					</p>
					<p>
						<button type="submit" class="button">
							<?php echo __( 'Begin your import', 'kanban' ) ?>
						</button>
					</p>
				</div>
			</div>

			<script>
				function kanban_import () {
					tb_show("<?php echo __( 'Import Kanban data', 'kanban' ) ?><br>", "#TB_inline?width=600&height=400&inlineId=kanban-import", "");
					jQuery('#TB_ajaxContent').wrapInner('<form action="<?php echo add_query_arg(array(Kanban_Utils::get_nonce() => wp_create_nonce( 'import' ))) ?>" method="post" enctype="multipart/form-data"></form>');
				};
			</script>

			<a href="#" onclick="kanban_import(); return false;" class="button">
				<?php echo __( 'Import Kanban data', 'kanban' ) ?>
			</a>

			<h3><?php echo __( 'Export', 'kanban' ) ?></h3>

			<p>
				<?php echo __( 'A Kanban data file (file type .kanbanwp) will download to your computer. 
				The file will also be saved to the "kanban-exports" folder in your "uploads" folder.
				Uploaded files (images, documents, etc) are <b>not</b> copied. 
				Please copy them manually.', 'kanban' ) ?>
			</p>

			<p>
				<a href="<?php echo add_query_arg(array(Kanban_Utils::get_nonce() => wp_create_nonce( 'export' ))) ?>" class="button">
					<?php echo __( 'Export Kanban data', 'kanban' ) ?>
				</a>
			</p>

			<hr>

			<h3><?php echo __( 'Diagnostics', 'kanban' ) ?></h3>

			<p>
				<button type="button" class="button" id="button-load-diagnostic-info">
					<?php echo __( 'Send diagnostic info to support', 'kanban' ) ?>
				</button>
			</p>
			<p>
				<textarea readonly placeholder="<?php echo __( 'Please click the button above.', 'kanban' ) ?>"
						  class="large-text" id="kanban-diagnostic-info" rows="10"></textarea>
			</p>
		</div><!-- tab-help -->
	</form>


</div><!-- wrap -->


<script type="text/html" id="t-status">

	<?php echo Kanban_Template::render_template( 'admin/t-status' ); ?>

</script>

<script type="text/html" id="t-estimate">

	<?php echo Kanban_Template::render_template( 'admin/t-estimate' ); ?>

</script>
