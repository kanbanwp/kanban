<script class="template" type="t/template" data-id="presets-modal">

	<div id="presets-modal" class="modal-dialog modal-lg">
		<div class="modal-content" data-label="<?php _e( 'Keyboard shortcuts', 'kanban' ); ?>">
			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm">
						<?php _e( 'Add preset', 'kanban'); ?>
						{{=add}}
					</span>
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
			</div><!--modal-header -->


			<div class="modal-body">

				<p class="h3">
					<?php _e( 'Add preset', 'kanban'); ?>
					{{=add}}
				</p>

				<div class="panel-group" id="presets-modal-accordion">
					<i class="ei ei-loading"></i>
				</div><!--accordion-->


			</div><!--modal-body-->

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>