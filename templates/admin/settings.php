<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo __(sprintf('%s Settings', Kanban::get_instance()->settings->pretty_name), Kanban::get_text_domain()) ?>
		<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ) ?>" class="page-title-action" target="_blank">
			<?php echo __('Go to your board', Kanban::get_text_domain()) ?>
		</a>

	</h1>

<?php if ( isset($_GET['message']) ) : ?>
	<div class="updated">
		<p><?php echo $_GET['message'] ?></p>
	</div>
<?php endif // message ?>

	<h2 class="nav-tab-wrapper">
		<a href="#tab-settings" class="nav-tab nav-tab-active"><?php echo __('Settings', Kanban::get_text_domain() ) ?></a>
		<a href="#tab-users" class="nav-tab"><?php echo __('Users', Kanban::get_text_domain() ) ?></a>
		<a href="#tab-statuses" class="nav-tab"><?php echo __('Statuses', Kanban::get_text_domain() ) ?></a>
		<a href="#tab-estimates" class="nav-tab"><?php echo __('Estimates', Kanban::get_text_domain() ) ?></a>
	</h2>



	<form action="" method="post">

		<div class="tab" id="tab-settings">

			<table class="form-table">
				<tbody>
					<tr>
						<th width="33%" scope="row">
							<label for="hour_interval">
								<?php echo __('Work hour interval', Kanban::get_text_domain() ) ?><br>
								<small><?php echo __('in hours', Kanban::get_text_domain() ) ?></small>
							</label>
						</th>
						<td>
							<input name="settings[hour_interval]" type="text" id="hour_increment" value="<?php echo isset($settings['hour_interval']) ? $settings['hour_interval'] : 1 ?>" class="regular-text">
							<p class="description">
								<?php echo __('Example: If you want to track work in 10 minute increments, enter ".1667" here.', Kanban::get_text_domain() ) ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(
				__('Save your Settings', Kanban::get_text_domain()),
					'primary',
					'submit'
			) ?>
		</div><!-- tab-settings -->



		<div class="tab" id="tab-users" style="display: none;">

			<table class="form-table">
				<tbody>
					<tr>
						<th width="33%" scope="row">
							<label for="hour_interval">
								<?php echo __('Allowed users', Kanban::get_text_domain() ) ?>
							</label>
						</th>
						<td>
							<fieldset>
<?php foreach ($all_users_arr as $user_id => $user_name) : ?>
								<label>
									<input name="settings[allowed_users][]" type="checkbox" value="<?php echo $user_id ?>" <?php echo isset($settings['allowed_users']) ? in_array($user_id, $settings['allowed_users']) ? 'checked' : '' : '' ?>>
									<?php echo $user_name ?>
								</label><br>
<?php endforeach // $all_users_arr ?>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(
				__('Save your Settings', Kanban::get_text_domain()),
					'primary',
					'submit'
			) ?>
		</div><!-- tab-users -->



		<div class="tab" id="tab-statuses" style="display: none;">

			<ol id="list-statuses" class="sortable">
<?php foreach ($statuses as $status_id => $status) : ?>
				<?php echo Kanban_Template::render_template('admin/t-status', (array) $status) ?>
<?php endforeach // statuses ?>
			</ol><!-- sortable -->
			<p>
				<button type="button" class="button" id="add-status">
					<?php echo __('Add another status', Kanban::get_text_domain()) ?>
				</button>
			</p>

			<?php submit_button(
				__('Save your Settings', Kanban::get_text_domain()),
					'primary',
					'submit'
			) ?>
		</div><!-- tab-statuses -->



		<div class="tab" id="tab-estimates" style="display: none;">

			<ol id="list-estimates" class="sortable">
<?php foreach ($estimates as $estimate_id => $estimate) : ?>
				<?php echo Kanban_Template::render_template('admin/t-estimate', (array) $estimate) ?>
<?php endforeach // statuses ?>
			</ol><!-- sortable -->
			<p>
				<button type="button" class="button" id="add-estimate">
					<?php echo __('Add another estimate', Kanban::get_text_domain()) ?>
				</button>
			</p>

			<?php submit_button(
				__('Save your Settings', Kanban::get_text_domain()),
					'primary',
					'submit'
			) ?>
		</div><!-- tab-estimates -->



		<?php wp_nonce_field(
				sprintf(
					'%s-%s',
					Kanban::$instance->settings->basename,
					Kanban_Option::table_name()
				),
				Kanban_Utils::get_nonce()
			); ?>

	</form>



</div><!-- wrap -->



<script type="text/html" id="t-status">

<?php include sprintf('%s/t-status.php', __DIR__) ?>

</script>

<script type="text/html" id="t-estimate">

<?php include sprintf('%s/t-estimate.php', __DIR__) ?>

</script>

