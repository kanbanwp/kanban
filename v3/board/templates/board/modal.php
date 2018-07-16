<script class="template" type="t/template" data-id="board-modal">

	<div class="modal-dialog modal-lg" id="board-modal" data-id="{{%board.id}}">
		<div class="modal-content" data-id="{{%board.id}}" data-label="<?php echo sprintf(
				__( 'Editing %s', 'kanban' ),
				'{{board.label}}{{%board.label}}{{:board.label}}' . __('New Board', 'kanban') . '{{/board.label}} (#{{%board.id}})'


		); ?>">

			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm">{{%board.label}}</span>
				</div>
				<div id="modal-navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						{{isBoardAdmin}}
						<li class="active">
							<a href="javascript:void(0);"
							   data-target="options"
							   id="modal-tab-options"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Options', 'kanban'); ?></a></li>
						</li>
						<li>
							<a href="javascript:void(0);"
							   data-target="lanes"
							   id="modal-tab-lanes"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Lanes', 'kanban'); ?></a>
						</li>
						<li>
							<a href="javascript:void(0);"
							   data-target="fields"
							   id="modal-tab-fields"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Fields', 'kanban'); ?></a>
						</li>
						{{/isBoardAdmin}}
						<li class="{{!isBoardAdmin}}active{{/!isBoardAdmin}}">
							<a href="javascript:void(0);"
							   data-target="users"
							   id="modal-tab-users"
							   onclick="kanban.app.modal.tabChange(this);"><?php _e( 'Users', 'kanban'); ?></a>
						</li>
						<li class="pull-right">
							<a href="javascript:void(0);"
							   onclick="kanban.app.modal.close(this);">
								<span class="visible-xs-inline-block"><?php _e( 'Close this window', 'kanban'); ?></span>
								<i class="ei ei-close hidden-xs"></i>
							</a>
						</li>
						{{isBoardAdmin}}
						<li class="pull-right">
							<a href="javascript:void(0);"
							   data-target="actions"
							   id="modal-tab-actions"
							   onclick="kanban.app.modal.tabChange(this);">
								<span class="visible-xs-inline-block"><?php _e( 'More actions', 'kanban'); ?></span>
								<i class="ei ei-cog hidden-xs"></i>
							</a>
						</li>
						{{/isBoardAdmin}}
					</ul>
				</div><!--/.nav-collapse -->
			</div>

			<div class="modal-body">

					<div class="tab-content" >
						{{isBoardAdmin}}
						<div class="tab-pane active" id="modal-tab-pane-options">

							<div class="row wrapper-form-group">
								<div class="form-group form-group-title col col-sm-12">
									<label><?php _e( 'Board title:', 'kanban'); ?></label>
									<input type="text"
									       class="form-control"
									       {{!isBoardAdmin}}readonly{{/!isBoardAdmin}}
									placeholder="<?php _e( 'Board title', 'kanban'); ?>"
									onfocus="kanban.boards[{{%board.id}}].modal.titleOnfocus(this);"
									onkeydown="kanban.boards[{{%board.id}}].modal.titleOnkeydown(this, event);"
									onblur="kanban.boards[{{%board.id}}].modal.titleOnblur(this);"
									value="{{%board.label}}">

								</div>


								<div class="form-group form-group-toggle col col-sm-12">
									<label><?php _e( 'Make board public:', 'kanban'); ?></label><br>

									<div class="btn-group">
										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="is_public"
										       name="board-{{%board.id}}-is_public"
										       id="board-{{%board.id}}-is_public-false"
										       autocomplete="off"
										       {{!optionsBoard.is_public}}checked{{/!optionsBoard.is_public}}
										value="false">
										<label for="board-{{%board.id}}-is_public-false"
										       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="is_public"
										       name="board-{{%board.id}}-is_public"
										       id="board-{{%board.id}}-is_public-true"
										       autocomplete="off"
										       {{optionsBoard.is_public}}checked{{/optionsBoard.is_public}}
										value="true">
										<label for="board-{{%board.id}}-is_public-true"
										       class="btn"><?php _e( 'No', 'kanban'); ?></label>
									</div>
								</div><!--form-group -->

								<div class="form-group form-group-toggle col col-sm-12">
									<label><?php _e( 'Show task Ids:', 'kanban'); ?></label><br>

									<div class="btn-group">
										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionUserOnChange(this);"
										       data-name="show_task_id"
										       name="board-{{%board.id}}-show_task_id"
										       id="board-{{%board.id}}-show_task_id-false"
										       autocomplete="off"
										       {{!optionsUser.show_task_id}}checked{{/!optionsUser.show_task_id}}
										value="false">
										<label for="board-{{%board.id}}-show_task_id-false"
										       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionUserOnChange(this);"
										       data-name="show_task_id"
										       name="board-{{%board.id}}-show_task_id"
										       id="board-{{%board.id}}-show_task_id-true"
										       autocomplete="off"
										       {{optionsUser.show_task_id}}checked{{/optionsUser.show_task_id}}
										value="true">
										<label for="board-{{%board.id}}-show_task_id-true"
										       class="btn"><?php _e( 'No', 'kanban'); ?></label>
									</div>
								</div><!--form-group -->

								<div class="form-group form-group-radio col col-sm-12">
									<label><?php _e( '@mentions should include:', 'kanban'); ?></label><br>

									<div class="btn-group-vertical">
										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="users_list_mention"
										       name="board-{{%board.id}}-options-users_list_mention"
										       id="board-{{%board.id}}-options-users_list_mention-wp"
										       autocomplete="off"
										       {{optionsBoard.users_list_mention-wp}}checked{{/optionsBoard.users_list_mention-wp}}
										value="wp">
										<label for="board-{{%board.id}}-options-users_list_mention-wp" class="btn btn-default">
											<?php _e( 'All WordPress users', 'kanban'); ?>
										</label>

										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="users_list_mention"
										       name="board-{{%board.id}}-options-users_list_mention"
										       id="board-{{%board.id}}-options-users_list_mention-board"
										       autocomplete="off"
										       {{optionsBoard.users_list_mention-board}}checked{{/optionsBoard.users_list_mention-board}}
										value="board">
										<label for="board-{{%board.id}}-options-users_list_mention-board"
										       class="btn btn-default">
											<?php _e( 'Board users', 'kanban'); ?>
										</label>
									</div><!--btn-group-->
								</div><!--form-group -->

								<div class="form-group form-group-toggle col col-sm-12">
									<label><?php _e( 'Only the user who created a card can delete it:', 'kanban'); ?></label><br>

									<div class="btn-group">
										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="card_creator_delete_card"
										       name="board-{{%board.id}}-card_creator_delete_card"
										       id="board-{{%board.id}}-card_creator_delete_card-false"
										       autocomplete="off"
										       {{!optionsBoard.card_creator_delete_card}}checked{{/!optionsBoard.card_creator_delete_card}}
										value="false">
										<label for="board-{{%board.id}}-card_creator_delete_card-false"
										       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="card_creator_delete_card"
										       name="board-{{%board.id}}-card_creator_delete_card"
										       id="board-{{%board.id}}-card_creator_delete_card-true"
										       autocomplete="off"
										       {{optionsBoard.card_creator_delete_card}}checked{{/optionsBoard.card_creator_delete_card}}
										value="true">
										<label for="board-{{%board.id}}-card_creator_delete_card-true"
										       class="btn"><?php _e( 'No', 'kanban'); ?></label>
									</div>
								</div><!--form-group -->

								<div class="form-group form-group-toggle col col-sm-12">
									<label><?php _e( 'Only the user who created a card can move it:', 'kanban'); ?></label><br>

									<div class="btn-group">
										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="card_creator_move_card"
										       name="board-{{%board.id}}-card_creator_move_card"
										       id="board-{{%board.id}}-card_creator_move_card-false"
										       autocomplete="off"
										       {{!optionsBoard.card_creator_move_card}}checked{{/!optionsBoard.card_creator_move_card}}
										value="false">
										<label for="board-{{%board.id}}-card_creator_move_card-false"
										       class="btn"><?php _e( 'Yes', 'kanban'); ?></label>

										<input type="radio"
										       onchange="kanban.boards[{{%board.id}}].modal.optionOnChange(this);"
										       data-name="card_creator_move_card"
										       name="board-{{%board.id}}-card_creator_move_card"
										       id="board-{{%board.id}}-card_creator_move_card-true"
										       autocomplete="off"
										       {{optionsBoard.card_creator_move_card}}checked{{/optionsBoard.card_creator_move_card}}
										value="true">
										<label for="board-{{%board.id}}-card_creator_move_card-true"
										       class="btn"><?php _e( 'No', 'kanban'); ?></label>
									</div>
								</div><!--form-group -->

							</div><!--wrapper-form-group-->

						</div><!--tab-options-->
						{{/isBoardAdmin}}


						{{isBoardAdmin}}
						<div class="tab-pane" id="modal-tab-pane-lanes">

							<div class="panel-group" id="board-modal-lanes-accordion">
								{{=lanesHtml}}
							</div>

							<p>
								<button type="button" class="btn btn-default btn-sm"
								        onclick="kanban.boards[{{%board.id}}].modal.laneAdd(this);">
									<?php _e( 'Add a lane', 'kanban'); ?>
									<i class="ei ei-loading show-on-loading"></i>
								</button>
								{{lanesHtml}}
								{{:lanesHtml}}
								<span class="hide-on-loading">
								<?php _e('Or', 'kanban') ?>
								<button type="button"
								        onclick="kanban.app.presetsToggleModal(this);"
								        data-add="lanes and fields"
								        class="btn btn-sm btn-primary">
									<?php _e('Choose from a preset', 'kanban') ?>
								</button>
								</span>
								{{/lanesHtml}}
							</p>

						</div><!--tab-lanes-->
						{{/isBoardAdmin}}

						{{isBoardAdmin}}
						<div class="tab-pane " id="modal-tab-pane-fields">

							<div class="panel-group loading" id="board-modal-fields-accordion">
								{{=fieldsHtml}}
							</div>

							<div class="dropdown" style="display: inline-block">
								<button class="btn btn-default btn-sm dropdown-toggle"
								        data-toggle="dropdown">
									<?php _e( 'Add a field', 'kanban'); ?>
									<span class="caret"></span>
									<i class="ei ei-loading show-on-loading"></i>
								</button>
								<ul class="dropdown-menu">
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="title"><?php _e( 'Title', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="text"><?php _e( 'Text', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="file"><?php _e( 'File', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="img"><?php _e( 'Image', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="date"><?php _e( 'Date', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="users"><?php _e( 'Users', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="tags"><?php _e( 'Tags', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="time"><?php _e( 'Time tracking', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="colorpicker"><?php _e( 'Color picker', 'kanban'); ?></a></li>
									<li><a href="javascript:void(0);" onclick="kanban.boards[{{%board.id}}].modal.fieldAdd(this);"
									       data-field-type="todo"><?php _e( 'To-do list', 'kanban'); ?></a></li>
								</ul>
							</div>
							{{fieldsHtml}}
							{{:fieldsHtml}}
							<span class="show-on-loading">
							<?php _e('Or', 'kanban') ?>
							<button type="button"
							        onclick="kanban.app.presetsToggleModal(this);"
							        data-add="fields"
							        class="btn btn-sm btn-primary">
								<?php _e('Choose from a preset', 'kanban') ?>
							</button>
							</span>
							{{/fieldsHtml}}


						</div><!--tab-fields-->
						{{/isBoardAdmin}}


						<div  class="tab-pane {{!isBoardAdmin}}active{{/!isBoardAdmin}} loading" id="modal-tab-pane-users">

							<div class="clearfix panel-group hide-on-loading" id="board-modal-users-accordion">
								{{=usersHtml}}
							</div>

							<select id="board-modal-user-find-control"
							        class="hide-on-loading"							        
							        autocomplete="off">
							</select>

							<i class="ei ei-loading show-on-loading"></i>

						</div><!--tab-pane-->

						<div  class="tab-pane" id="modal-tab-pane-actions">
							<p>
								<a href="javascript:void(0);" class="btn btn-default btn-sm">
									<?php _e( 'Copy this board.', 'kanban'); ?>
								</a>
							</p>
							<p>
								<a href="javascript:void(0);"
								   onclick="kanban.boards[{{%board.id}}].modal.boardDelete(this);"
								   class="btn btn-warning btn-sm">
									<?php _e( 'Archive this board.', 'kanban'); ?>
								</a>
							</p>
						</div><!--tab-pane-->
					</div><!--tab-panes-->
				</div><!--modal-body-->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>