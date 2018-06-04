<?php  if ( !isset($_COOKIE['kanban-v3-notice-hide']) ) : ?>

<div class="notice notice-success is-dismissible" id="kanban-v3-notice">
	<p style="font-size: 1.618em;"><b>Kanban for WordPress Version 3</b> is coming soon!</p>
	<p>The new version will arrive soon - faster, more customizable, and with lots of great new features.</p>
	<p style="font-size: 1.382em;">Join our mailing list for news, discounts, and to vote on the features that make it into version 3!</p>


	<form method="POST" action="https://kanbanforwordpress.activehosted.com/proc.php">
		<input type="hidden" name="u" value="1" />
		<input type="hidden" name="f" value="1" />
		<input type="hidden" name="s" />
		<input type="hidden" name="c" value="0" />
		<input type="hidden" name="m" value="0" />
		<input type="hidden" name="act" value="sub" />
		<input type="hidden" name="v" value="2" />
				<input type="text" name="email" placeholder="Your email address" class="input" required/>
			</label>
			<button class="button button-primary" type="submit">
				Sign up now!
			</button>
	</form>
</div>

<style>
	#kanban-v3-notice {
		border: 5px solid white;
		padding: 10px;

		-webkit-box-shadow: 0 0 10px 0px rgba(0,0,0,0.42);
		-moz-box-shadow: 0 0 10px 0px rgba(0,0,0,0.42);
		box-shadow: 0 0 10px 0px rgba(0,0,0,0.42);

		/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ffffff+0,90c745+100&0.61+0,0.38+100 */
		background: -moz-linear-gradient(-45deg, rgba(255,255,255,0.61) 0%, rgba(144,199,69,0.38) 100%); /* FF3.6-15 */
		background: -webkit-linear-gradient(-45deg, rgba(255,255,255,0.61) 0%,rgba(144,199,69,0.38) 100%); /* Chrome10-25,Safari5.1-6 */
		background: linear-gradient(135deg, rgba(255,255,255,0.61) 0%,rgba(144,199,69,0.38) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#9cffffff', endColorstr='#6190c745',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
	}

	#kanban-v3-notice p {
		margin: 0 0 5px;
	}

	#kanban-v3-notice input {
		font-size: 13px;
		line-height: 26px;
		height: 30px;
		margin: 0;
		padding: 0 10px 1px;
		border-width: 1px;
	}
</style>

<script>
	jQuery(function($) {
		$('#kanban-v3-notice').on(
			'click',
			'.notice-dismiss',
			function () {
				console.log('test');
				var d = new Date();
				d.setTime(d.getTime() + (30*24*60*60*1000)); // 30 days
				var expires = "expires="+ d.toUTCString();
				document.cookie = "kanban-v3-notice-hide=1;" + expires + ";path=/";
			}
		);
	});
</script>
<?php endif ?>

