<div class="wrap">

	<?php $h2 ='Contact us'; include Kanban::instance()->settings()->path . 'admin/templates/header.php'; ?>




	<form action="" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="from">From</label>
					</th>
					<td>
						<input name="from" type="text" id="from" value="<?php echo get_option('admin_email'); ?>"  class="large-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="from">How can we help?</label>
					</th>
					<td>
						<label class="radio">
							<input type="radio" name="request" value="Technical support request" checked>
							<span><?php esc_attr_e( 'Request technical support', 'kanban' ); ?></span>
						</label>
						<label class="radio">
							<input type="radio" name="request" value="Bug report">
							<span><?php esc_attr_e( 'Report a bug', 'kanban' ); ?></span>
						</label>
						<label class="radio">
							<input type="radio" name="request" value="Suggestion">
							<span><?php esc_attr_e( 'Make a suggestion', 'kanban' ); ?></span>
						</label>
						<label class="radio">
							<input type="radio" name="request" value="Contact request">
							<span><?php esc_attr_e( 'Something else', 'kanban' ); ?></span>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="message">Message</label>
					</th>
					<td>
						<textarea name="message" id="message" cols="80" rows="10" class="large-text" placeholder="<?php _e('Add your comments here...', 'kanban') ?>"></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label>&nbsp;</label>
					</th>
					<td>
						<input class="button-primary" type="submit" value="<?php esc_attr_e( 'Email away!' ); ?>" />
						<?php wp_nonce_field( 'kanban-admin-comment'); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>


<style>
	label.radio {
		display: block;
		padding: 10px 0;
	}


</style>

</div><!-- wrap -->
