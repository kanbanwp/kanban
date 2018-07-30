
<link rel='stylesheet' href='<?php echo Kanban::get_instance()->settings->uri ?>v3/admin/css/admin.css' type='text/css' media='all' />

<div class="wrap">


	<header id="kanban-header">

		<img src="<?php echo Kanban::get_instance()->settings->uri ?>/v3/img/kanbanwp-sq-black.svg" id="kanban-logo">

		<h2>Introducing the Version 3 beta</h2>

	</header>

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

	<hr>

	<p style="font-size: 1.382em;  text-align: center;">
		Ready to give it a try?
		<b style="background: yellow;">
			Backup your site
		</b>
		and then...
	</p>

	<p style="margin-top: 90px; text-align: center;">
		<a href="<?php print add_query_arg(array('page'=> 'kanban', Kanban_Utils::get_nonce()=>wp_create_nonce('kanban2_to_3')), admin_url('admin.php'));?>"
		   style="position: relative"
		   class="button button-primary">
			Switch to version 3
			(Extremely experimental)
			<img src="<?php echo Kanban::get_instance()->settings->uri ?>/v3/img/bird 2-125.png" style="left: 20px; position: absolute; top: -77px;">
		</a>

	</p>


</div><!-- wrap -->
