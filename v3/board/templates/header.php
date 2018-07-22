<script class="template" type="t/template" data-id="header">

		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed"
			        data-toggle="collapse"
			        data-target="#header-navbar">
				<span id="header-board-toggle-label"></span>
				<i class="ei ei-arrow_carrot-down"></i>
			</button>
		</div>
		<nav id="header-navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav" id="header-board-tabs">
				{{=boardTabsHtml}}
				{{boardTabsDropdownHtml}}
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<?php _e('More boards', 'kanban') ?>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						{{=boardTabsDropdownHtml}}
					</ul>
				</li>
				{{/boardTabsDropdownHtml}}
			</ul>
			{{isAddBoard}}
			<ul class="nav navbar-nav">
				<li class="">
					<a href="javascript:void(0);"class="" id="header-board-tabs-add-new" onclick="kanban.app.addBoard(this); return false;">
						<span class="visible-xs-inline"><?php _e( 'Add a board', 'kanban'); ?></span>
						<span class=" ei ei-plus hiden-sm hidden-xs"></span>
					</a>
				</li>
			</ul>
			{{/isAddBoard}}

			<ul class="nav navbar-nav navbar-right" id="header-user-nav">
				<li class="dropdown">
					{{isUserLoggedIn}}
						<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
							{{=currentUser.display_name_short}}
							<span class="caret"></span>
						</a>
					{{:isUserLoggedIn}}
						<li><a href="<?php echo wp_login_url( Kanban_Router::instance()->get_page_uri('board') ) ?>"><?php _e( 'Log in', 'kanban'); ?></a></li>
					{{/isUserLoggedIn}}
					<ul class="dropdown-menu">
<?php /*
						<li><a href="<?php echo Kanban_Router::instance()->get_page_uri('calendar') ?>">
								<?php _e( 'Calendar', 'kanban'); ?>
							</a></li>
 */ ?>
						{{isUserLoggedIn}}
							<li><a href="<?php echo admin_url() ?>" target="_blank"><?php _e( 'WordPress admin', 'kanban'); ?></a></li>
							<li><a href="<?php echo wp_logout_url() ?>"><?php _e( 'Log out', 'kanban'); ?></a></li>
						{{/isUserLoggedIn}}
					</ul>
				</li>
			</ul>
		</nav><!--/.nav-collapse -->


</script>