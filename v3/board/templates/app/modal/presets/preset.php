<script class="template" type="t/template" data-id="presets-modal-preset">

	<div class="panel panel-default">
		<div class="panel-heading">
			<span class="h3 panel-title"
			   data-toggle="collapse"
			   data-parent="#presets-modal-accordion"
			   href="#presets-modal-accordion-{{%preset.class}}">
				{{preset.icon}}
				<i class="{{%preset.icon}}"></i>
				{{/preset.icon}}
				&nbsp;
				{{=preset.label}}
			</span>
		</div>
		<div id="presets-modal-accordion-{{%preset.class}}" class="panel-collapse collapse">
			<div class="panel-body">
				<p>
					{{=preset.description}}
				</p>
				
				<dl>
				{{preset.lane_labels}}

					<dt>Lanes:</dt>
					<dd style="margin-bottom: .618em;">
						{{=preset.lane_labels}}
					</dd>
				{{/preset.lane_labels}}

				{{preset.field_labels}}
					<dt>Fields:</dt>
					<dd>
						{{=preset.field_labels}}
					</dd>
				{{/preset.field_labels}}
				</dl>

				<p>
					<button type="button"
					        class="btn btn-primary"
					        data-class="{{%preset.class}}"
					        data-add="{{%add}}"
					        onclick="kanban.app.presetAdd(this);">
						<?php _e('Add this preset') ?>
					</button>
				</p>
			</div><!--body-->
		</div><!--collapse-->
	</div><!--panel-->


</script>