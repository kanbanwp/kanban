<div class="modal fade" id="modal-projects">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="close" data-dismiss="modal">&times;</button>

				<p class="h3">
					<?php echo __( 'Projects', 'kanban' ); ?>
				</p>

				<p id="modal-projects-sort-filter">
					<input type="text" class="form-control input-sm pull-right" id="modal-projects-filter" placeholder="filter">
					<span class="btn btn-default btn-sm" id="modal-projects-sort" data-reverse="false">
						<?php echo __( 'Sort', 'kanban' ); ?>
						<span class="glyphicon glyphicon-arrow-up" data-class="glyphicon glyphicon-arrow-down" style="display: none;"></span>
					</span>
				</p>

				<div class="panel-group" id="accordion-projects">
				</div><!-- panel-group -->

				<div class="row">
					<p class="col-sm-6 col-sm-offset-3">
						<label for="modal-project-new-input" class="sr-only">
							<?php echo __( 'Add a project', 'kanban' ); ?>:
						</label>
						<span class="input-group">
							<input type="text" class="form-control input-sm" id="modal-project-new-input" placeholder="<?php echo __( 'Add a project', 'kanban' ); ?>" autocomplete="off">
							<span class="input-group-btn">
								<button class="btn btn-default btn-sm" type="button" id="modal-project-new-btn">
									<?php echo __( 'Add a project', 'kanban' ); ?>
								</button>
							</span>
						</span>
					</p>
				</div>
			</div><!-- body -->
		</div><!-- content -->
	</div><!-- dialog -->
</div><!-- modal -->
