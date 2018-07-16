<script class="template" type="t/template" data-id="card-modal-comment">

	<div class="comment comment-{{%comment.id}} comment-{{%comment.comment_type}} clearfix {{isEditing}}is-editing{{/isEditing}}"
	     {{isForm}}id="card-modal-comment-form"{{/isForm}}
	     data-id="{{%comment.id}}"
	     data-card-id="{{%cardId}}">

	{{author.initials}}
		<span class="pull-left wrapper-avatar" data-initials="{{=author.initials}}">
			<img src="{{=author.avatar}}">
		</span>
	{{:author.initials}}
		<span class="pull-left">
			<i class="ei ei-cog ei-2x"></i>
		</span>
	{{/author.initials}}

		<div class="card-modal-comment-content wrapper-form-group {{isForm}}dropzone{{:isForm}}{{isEditing}}dropzone{{/isEditing}}{{/isForm}}">
			<div class="field form-group form-group-text"
			     data-card-id="{{%cardId}}">
				<b>
				{{author.display_name}}
					{{=author.display_name}}
				{{:author.display_name}}
					<?php _e( 'System', 'kanban'); ?>
				{{/author.display_name}}
				</b>

				{{comment}}
				<small class="text-muted timeago"
				       data-datetime="{{=comment.created_dt_gmt}}">
					{{=comment.created_dt}}
				</small>
				{{/comment}}

				<div class="wrapper-contenteditable">
					<div class="contenteditable-prevent-click">&#8203;</div>
					<div class="form-control card-modal-comment-input {{isSystem}}is-system{{/isSystem}}"
					     data-card-id="{{%cardId}}"
					     {{isEditing}}contenteditable="true"{{/isEditing}}
					     data-onpaste="kanban.cards[{{%cardId}}].modal.commentOnPaste(this, event);"
					     data-placeholder="<?php _e( 'Add a comment', 'kanban'); ?>">{{=comment.content}}</div>
					<div class="contenteditable-prevent-click">&#8203;</div>
				</div>

			</div><!--field-->


			{{isSystem}}
			{{:isSystem}}
				<div class="field form-group form-group-button">
					{{isEditing}}
					{{comment}}
					<button type="button"
					        class="btn btn-primary"
					        data-id="{{%comment.id}}"
					        onclick="kanban.comments[{{%comment.id}}].update(this);">
						<span class="hide-on-loading">
							<?php _e( 'Update your comment', 'kanban'); ?>
						</span>
						<i class="ei ei-loading show-on-loading"></i>
					</button>
					<button type="button"
					        class="btn btn-link"
					        data-id="{{%comment.id}}"
					        onclick="kanban.comments[{{%comment.id}}].rerenderNotEditable(this);">
						<span class="hide-on-loading btn btn-link btn-sm">
							<?php _e( 'Cancel', 'kanban'); ?>
						</span>
						<i class="ei ei-loading show-on-loading"></i>
					</button>
					{{:comment}}
					<button type="button"
					        class="btn btn-primary btn-block"
					        data-card-id="{{%cardId}}"
					        onclick="kanban.cards[{{%cardId}}].modal.commentAdd(this);">
						<span class="hide-on-loading">
							<?php _e( 'Add your comment', 'kanban'); ?>
						</span>
						<i class="ei ei-loading show-on-loading"></i>
					</button>
					{{/comment}}
					{{/isEditing}}



				</div><!--field-->



				{{isEditing}}
				{{:isEditing}}
				{{isAuthor}}
				<div class="card-modal-comment-actions">
					<a href="javascript:void(0);"
					   class="btn btn-default btn-xs"
					   onclick="kanban.comments[{{%comment.id}}].rerenderEditable(this)"
					><?php _e( 'Edit', 'kanban'); ?></a>

					<a href="javascript:void(0);"
					   class="btn btn-default btn-xs"
					   onclick="kanban.comments[{{%comment.id}}].delete(this)"
					><?php _e( 'Delete', 'kanban'); ?></a>
				</div>
				{{/isAuthor}}
				{{/isEditing}}


				{{comment.modified_dt}}
				<p class="card-modal-comment-edited">
					<small class="text-muted">
						<?php _e( 'Edited', 'kanban'); ?>
						<span class="timeago"
						      data-datetime="{{=comment.modified_dt_gmt}}">
							{{=comment.modified_dt}}
						</span>
					</small>
				</p>
				{{/comment.modified_dt}}

			{{/isSystem}}

		</div>
	</div><!--col-->


</script>