<div class="wrap">

	<?php $h2 ='Kanban Pro'; include Kanban::instance()->settings()->path . 'admin/templates/header.php'; ?>


	<?php
	if ( ! is_network_admin() && Kanban::instance()->settings()->is_network && $status != 'valid' ) : ?>
	<div class="kanban-notice">
		<?php _e('Kanban is network activated.', 'kanban'); ?>
		<?php _e('Contact your network administrator to activate your Pro license.', 'kanban'); ?>
	</div>
	<?php endif ?>



	<h2>Take your productivity to the next level!</h2>

	<p><b>KanbanPro</b> brings extra power, flexibility and more options to Kanban.</p>

	<p>
		Designed for businesses and the power user, you will have total control of your workflow.
		Enhance your team’s communication, productivity, and morale with boosted communication and notification
		features.
		Get greater insight into the performance of your team and its individuals with powerful tracking and analysis.
		Get more done, more quickly, all in one place.
	</p>

	<p>
		<a href="https://KanbanWP.com/addon/kanban-pro/" target="_blank" class="button button-primary">
			<?php echo __( 'Learn more about Kanban Pro', 'kanban' ) ?>
		</a>
	</p>
</div><!-- wrap -->


