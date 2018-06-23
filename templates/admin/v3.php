

<div class="wrap">
	<p>
	<a href="<?php echo Kanban_Template::get_uri() ?>" class="page-title-action" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo Kanban_Template::get_uri() ?>', 'kanbanboard'); return false;">
		<?php echo __( 'Go to your board', 'kanban' ); ?>
	</a>
	</p>

	<h1>
		<?php echo __( 'Introducing the Version 3 beta', 'kanban' ); ?>
	</h1>

	<hr>

	<h3>
		<b>Version 3 of Kanban for WordPress is all about customization.</b>
	</h3>

	<p>
		When I first started Kanban for WordPress, I built it for project management.
		That's what I needed it for.
		But I quickly learned that kanban, as a methodology, solves so many more problems than that.
	</p>

	<p>
		So for two years, we've collected feedback from you and other Kanban users.
		We've learned how you're using Kanban for sales tracking, leaderboards, real estate, and so much more.
		What we learned is that Kanban for WordPress needs to stay simple, but be able to grow with you, solving the more granular needs as your business grows.
	</p>

	<p>
		A year later, we now present the beta release of Kanban for WordPress.
		We're calling it version 3 because it's a complete reimagining of the app I started 3+ years ago.
	</p>

	<hr>

	<h3>
		What to expect
	</h3>

	<p>
		What you hold in your hands is a work in progress.
		We wanted to get it in your hands as soon as possible to start getting feedback and bug reports.
		It's still a little rough around the edges.
		You may encounter issues, bugs, or things that don't make sense.
	</p>

	<p>
		<b> Your version 2 data is safe</b>
	</p>

	<p>
		For now, this version 3 beta will not touch your data.
		It's meant to be a sandbox.
		You can play with it, and then switch back to version 2 when you need to get back to work.
	</p>

	<hr>

	<h3>
		What we ask of you
	</h3>

	<p>
		We're looking to you to tell us what goes wrong.
		We can only test so much in-house.
		If you encounter any bugs or problems, <i>please</i> use the "contact us" link in the Kanban menu in the WordPress admin.
		Shoot us a note with as much detail as you can, and we'll fix it as soon as possible.
	</p>


	<p>
		<a href="<?php print wp_nonce_url(admin_url('options.php?page=kanban'), 'kanban2_to_3');?>" class="button">
			Switch to version 3
			(Extremely experimental)
		</a>
	</p>


</div><!-- wrap -->
