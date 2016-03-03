<div class="modal fade" id="modal-empty-archive" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<p class="lead text-center" id="empty-archive-confirmation-label" data-label="<?php echo __('Delete all tasks in &quot;{0}&quot;?', 'kanban'); ?>">
				</p>
				<p class="text-center">
					<button type="button" class="btn btn-default" id="btn-empty-status-tasks-cancel" data-dismiss="modal">
						<?php echo __( 'Cancel', 'kanban' ); ?>
					</button>
					<button type="button" class="btn btn-primary" id="btn-empty-status-tasks" data-status-col-id="">
						<?php echo __( 'Archive', 'kanban' ); ?>
					</button>
				</p>
			</div>
		</div><!-- content -->
	</div><!-- dialog -->
</div><!-- modal -->
