<script class="template" type="t/template" data-id="card">

	<div class="card panel panel-default {{%showCardIdClass}}" data-id="{{%card.id}}" id="card-{{%card.id}}">

		<div class="panel-body highlight">

		<div class="row wrapper-form-group">
			{{=fields}}
		</div><!--row-->
		<div class="card-menu">
			{{isCardWrite}}
			<button class="btn btn-sm btn-fade btn-empty card-move-handle">
				<i class="ei ei-menu" style="color: {{%lane.color}}"></i>
			</button>
			{{/isCardWrite}}

			<a href="javascript:void(0);" class="btn btn-sm btn-fade btn-empty card-edit"
			        type="button"
			        onclick="kanban.cards[{{%card.id}}].modal.show(this);"
			        data-title="<?php _e( 'Edit card', 'kanban'); ?> {{%card.id}}">
				<i class="ei ei-pencil" style="color: {{%lane.color}}"></i>

				<span class="btn-group-vertical card-edit-menu">
					<button type="button" class="btn btn-default btn-sm" onclick="event.stopPropagation(); kanban.cards[{{%card.id}}].copy(this); return false;">
						<?php _e( 'Copy', 'kanban'); ?>
					</button>
<?php /*
					<button type="button" class="btn btn-default btn-sm" onclick="event.stopPropagation(); kanban.cards[{{%card.id}}].move(this); return false;">
						<?php _e( 'Move', 'kanban'); ?>
					</button>
 */ ?>
					<button class="btn btn-default btn-sm" onclick="event.stopPropagation(); kanban.cards[{{%card.id}}].delete(this);">
						<?php _e( 'Archive', 'kanban'); ?>
						<i class="ei ei-loading show-on-loading" style="color: {{%lane.color}}"></i>
					</button>
				</span>

			</a>

			<button class="btn btn-sm btn-fade btn-empty card-comments"
			        type="button"
			        onclick="kanban.cards[{{%card.id}}].modal.show(this, 'comments');"
			        title="Comments">
				<i class="ei ei ei-comment_alt" style="color: {{%lane.color}}"></i>

				<span class="card-comments-count" style="display: {{commentsCount}}inline-block{{:commentsCount}}none{{/commentsCount}};">
					{{=commentsCount}}
				</span>

			</button>

		</div>

		{{fields_hidden}}
		<button class="btn btn-xs btn-block btn-empty card-expand"
		        onclick="kanban.cards[{{%card.id}}].toggleHiddenFields('card-{{%card.id}}-fields-hidden');"
		        href="#form-groups-{{%card.id}}">
			<i class="ei ei-arrow_carrot-down" style="color: {{%lane.color}}"></i>
		</button>

		<div class="row wrapper-form-group wrapper-form-group-hidden collapse" id="card-{{%card.id}}-fields-hidden">
			{{=fields_hidden}}
		</div>
		{{/fields_hidden}}

		</div>
	</div><!--card-->


</script>