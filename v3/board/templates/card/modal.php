<script class="template" type="t/template" data-id="card-modal">

	<div class="modal-dialog modal-lg" id="card-modal" data-id="{{%card.id}}">
		<div class="modal-content" data-id="{{%card.id}}" id="card-modal-{{%card.id}}">

			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm">
						<?php echo sprintf(__( 'Card #%s', 'kanban'), '{{%card.id}}'); ?>
					</span>
				</div>
				<div id="modal-navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active">
							<a href="javascript:void(0);"
							   data-target="fields"
							   id="modal-tab-fields"
							   onclick="kanban.app.modal.tabChange(this);">
								<?php _e( 'Fields', 'kanban'); ?>
							</a></li>
						</li>
						{{isCommentRead}}
						<li>
							<a href="javascript:void(0);"
							   data-target="comments"
							   id="modal-tab-comments"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Comments', 'kanban'); ?></a>
						</li>
						{{/isCommentRead}}
						<li class="pull-right">
							<a href="javascript:void(0);"
						        onclick="kanban.cards[{{%card.id}}].modal.close(this);">
								<span class="visible-xs-inline-block"><?php _e( 'Close this window', 'kanban'); ?></span>
								<i class="ei ei-close hidden-xs"></i>
							</a>
						</li>
						{{isCardWrite}}
						<li class="pull-right">
							<a href="javascript:void(0);"
							   data-target="actions"
							   id="modal-tab-actions"
							   onclick="kanban.app.modal.tabChange(this);">
								<span class="visible-xs-inline-block"><?php _e( 'More actions', 'kanban'); ?></span>
								<i class="ei ei-cog hidden-xs"></i>
							</a>
						</li>
						{{/isCardWrite}}
					</ul>
				</div><!--/.nav-collapse -->
			</div><!--modal-header-->

			<div class="modal-body">

				<div class="tab-content">

					<div class="tab-pane active" id="modal-tab-pane-fields">
						<div class="row wrapper-form-group">
							{{=fields}}
						</div><!--row-->

						<hr>

						<div id="card-modal-lane-selector">
							<small>
								<?php _e('Move the card to another lane:', 'kanban') ?>
							</small><br>
							<div class="btn-group">
								{{=lanesSelector}}
							</div>
						</div>

					</div><!--tab-fields-->

					{{isCommentRead}}
					<div class="tab-pane" id="modal-tab-pane-comments">

						<div class="text-right">
							<div class="btn-group" data-toggle="buttons">
								<button type="button" class="btn btn-xs btn-default active"
							        onclick="kanban.cards[{{%card.id}}].modal.commentsFilter(this);"
							        data-filter="all">
									<?php _e('All', 'kanban') ?>
									<input type="radio" name="card-comments-filter" value="all" checked>
								</button>
								<button type="button" class="btn btn-xs btn-default"
								        onclick="kanban.cards[{{%card.id}}].modal.commentsFilter(this);"
								        data-filter="user">
									<?php _e('User', 'kanban') ?>
									<input type="radio" name="card-comments-filter" value="user">
								</button>
								<button type="button" class="btn btn-xs btn-default"
								        onclick="kanban.cards[{{%card.id}}].modal.commentsFilter(this);"
								        data-filter="system">
									<?php _e('system', 'kanban') ?>
									<input type="radio" name="card-comments-filter" value="system">
								</button>
							</div>
						</div>

						<div id="wrapper-card-modal-comments">

							<div id="card-modal-comments-list">

								<i class="ei ei-loading"></i>

							</div><!--card-modal-comments-list-->

							{{=commentForm}}
						</div><!--wrapper-card-modal-comments-->

					</div><!--tab-comments-->
					{{/isCommentRead}}

					<div  class="tab-pane" id="modal-tab-pane-actions">
						<p>
							<a href="javascript:void(0);" class="btn btn-default btn-sm">
								<?php _e( 'Move this task to another column or board.', 'kanban'); ?>
							</a>
						</p>
						<p>
							<a href="javascript:void(0);" class="btn btn-default btn-sm" onclick="kanban.cards[{{%card.id}}].copy(this)">
								<?php _e( 'Copy this task.', 'kanban'); ?>
							</a>
						</p>
						<p>
							<a href="javascript:void(0);" class="btn btn-warning btn-sm" onclick="kanban.cards[{{%card.id}}].delete(this)">
								<?php _e( 'Archive this task.', 'kanban'); ?>
								<i class="ei ei-loading show-on-loading" style="color: {{%lane.color}}"></i>
							</a>
						</p>
					</div><!--tab-pane-->
				</div><!--tab-panes-->
			</div><!--modal-body-->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>