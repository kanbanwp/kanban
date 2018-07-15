<script class="template" type="t/template" data-id="footer">

<div class="container-fluid">
	<div class="navbar-header">

		<button type="button" class="btn btn-default btn-toggle-lane visible-xs-inline"
		        data-direction="left"
		        onclick="kanban.app.toggleLane(this);">
			<span class="sr-only"><?php _e( 'Toggle lanes', 'kanban'); ?></span>
			<i class="ei ei-arrow_carrot-left ei-2x"></i>
		</button>

		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#footer-nav">
			<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
			<i class="ei ei-menu ei-2x"></i>
		</button>

		<button type="button" class="btn btn-default btn-toggle-lane visible-xs-inline"
		        data-direction="right"
		        onclick="kanban.app.toggleLane(this);">
			<span class="sr-only"><?php _e( 'Toggle lanes', 'kanban'); ?></span>
			<i class="ei ei-arrow_carrot-right ei-2x"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse" id="footer-nav">
		<form class="navbar-form navbar-left">
			<div class="form-group">
				<input type="search"
				       class="form-control" placeholder="<?php _e( 'Search', 'kanban'); ?>"
				       onkeyup="kanban.app.searchCurrentBoard(this)">
			</div>
		</form>

		<ul class="nav navbar-nav">

			<li>
				<a href="javascript:void(0);"
				   onclick="kanban.app.filterModalToggle(); return false;"
				   class="btn btn-fade btn-empty">
					<span class="visible-xs-inline-block"><?php _e( 'Filter', 'kanban'); ?></span>
					<i class="ei ei-adjust-vert ei-2x hidden-xs"></i>
				</a>
			</li>

			<li class="dropup" id="footer-menu">
				{{=footerMenuHtml}}
				<a href="javascript:void(0);" class="btn btn-empty btn-fade" data-toggle="dropdown">
					<span class="visible-xs-inline-block"><?php _e( 'Options', 'kanban'); ?></span>
					<i class="ei ei-cog ei-2x hidden-xs"></i>
				</a>
			</li>

		</ul>
	</div><!-- /.navbar-collapse -->
</div><!-- /.container-fluid -->

<i class="ei ei-loading ei-2x" id="app-ajax-loading" style="display: none;"></i>

</script>