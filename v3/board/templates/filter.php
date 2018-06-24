<script class="template" type="t/template" data-id="filter-modal">

	<div class="modal-dialog modal-lg" id="filters-modal">
		<div class="modal-content" data-label="<?php _e( 'Filters', 'kanban' ); ?>">

			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm"><?php _e( 'Filters', 'kanban' ); ?></span>
				</div>
				<div id="modal-navbar" class="collapse navbar-collapse">

					<ul class="nav navbar-nav">
						<li class="pull-right">
							<a href="javascript:void(0);"
							   onclick="kanban.app.modal.close(this);">
								<span class="visible-xs-inline-block"><?php _e( 'Close this window', 'kanban'); ?></span>
								<i class="ei ei-close hidden-xs"></i>
							</a>
						</li>
					</ul>

				</div><!--/.nav-collapse -->
			</div>

			<div class="modal-body">
				<h3><?php _e( 'Filters', 'kanban' ); ?></h3>
				<div class="tab-content" >
					<div class="tab-pane active" id="modal-tab-pane-options">

						<div class="list-group">
						{{=fieldHtml}}
						</div>

						<p>
							<button type="button" class="btn btn-primary" onclick="kanban.app.applyFilters()">
								<?php _e('Apply filters', 'kanban') ?>
							</button>

							<button type="button" class="btn btn-empty" onclick="kanban.app.clearFilters()">
								<?php _e('Clear filters', 'kanban') ?>
							</button>
						</p>

					</div><!--tab-options-->
				</div><!--tab-panes-->
			</div><!--modal-body-->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>