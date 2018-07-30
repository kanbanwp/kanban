<div class="wrap">

	<?php $h2 = 'Kanban Boards for WordPress'; include Kanban::instance()->settings()->path . 'admin/templates/header.php'; ?>

	<p style="text-align: center;">
		<a href="<?php print add_query_arg(array('page'=> 'kanban', 'kanban_nonce'=>wp_create_nonce('kanban3_to_2')), admin_url('admin.php'));?>"
		   class="button">
			Switch back version 2
		</a>
	</p>


	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">


				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h3>
							<?php _e( 'Getting started', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php _e(
									'Welcome to Kanban for WordPress—the fact that you\'re here means you\'ve already taken a big step towards a more productive you! The easiest way to learn what\'s going on is usually to jump straight in, so hit the Open Kanban board button above to dive into your board and get going. Happy organizing!',
									'kanban'
								); ?></p>

							<hr>
						</div>

						<h3>
							<?php _e( 'Additional help', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php _e(
									'If there\'s any functionality you can\'t work out, our <a href="https://kanbanwp.com/documentation/v3" target="_blank">Kanban User Guides</a> should be your first port of call. They offer great step-by-step instructions to walk you through pretty much anything you can do with a Kanban board. If you have a question about the plugin itself, or you need some help that the guides don\'t cover, just get in touch using the Contact Us link in the sidebar to the left.',
									'kanban'
								); ?></p>

							<hr>
						</div>

						<h3>
							<?php _e( 'Advanced tips and tricks', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php _e(
									'Only for the power users! Check out the <a href="https://kanbanwp.com/documentation/v3/advanced-tips-and-tricks/" target="_blank">Advanced</a> section of our documentation.',
									'kanban'
								); ?></p>

							<p><?php echo sprintf(__(
									'Or have you got a clever idea? If you\'re trying to do something fancy with Kanban, have thought of some cool additional features you\'d like to see, or have a more advanced technical query, we want to hear about it! Use the <a href="%s">Contact Us</a> link in the sidebar to the left and we\'ll get back to you soon.',
									'kanban'
								),
								admin_url('/admin.php?page=kanban_contact')
									); ?></p>

							<hr>
						</div>

						<h3>
							<?php _e( 'What\'s with the bird?', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php _e(
									'It\'s a kanbird, of course! They\'re sociable little critters who you\'ll see around the place occasionally. The support staff love them, so play nicely, or you may not get a reply when you ask for help!',
									'kanban'
								); ?></p>

							<p style="margin-bottom: -18px; text-align: right;">
								<img src="<?php echo Kanban::instance()->settings()->uri ?>/img/bird 2-125.png">
							</p>
							<hr>
						</div>

						<h3>
							<?php _e( 'Kanban Pro', 'kanban' ); ?>
						</h3>

						<div class="inside">
							<p><?php _e(
									'Take your productivity to the next level! <b>Kanban Pro</b> brings extra power, flexibility and more options to Kanban.',
									'kanban'
								); ?></p>

							<p><?php _e(
									'Designed for businesses and the power user, Pro gives you total control of your workflow. With it, you can:',
									'kanban'
								); ?></p>

							<ol>
								<li>
									<?php _e(
											'Build your team with advanced user group management and seamless onboarding.',
											'kanban'
										); ?>
								</li>
								<li>
									<?php _e(
										'Supercharge your communication, productivity, and morale with enhanced communication and notification features.',
										'kanban'
									); ?>
								</li>

								<li>
									<?php _e(
										'Get greater insight into the performance of your team and its individuals with powerful tracking and analysis.',
										'kanban'
									); ?>
								</li>

							</ol>

							<p><?php _e(
									'<b>Kanban Pro</b> lets you get more done, more quickly, all in one place.',
									'kanban'
								); ?></p>

							<p><?php echo sprintf(__(
									'<a href="%s">Learn more about Pro</a>',
									'kanban'
								), admin_url('/admin.php?page=kanban_pro')); ?></p>
						</div>

					</div>
				</div>

			</div>

			<aside id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h3>
							<?php _e( 'Stay up to date', 'kanban' ); ?>
						</h3>

						<div class="inside">

							<?php _e(
								'We don\'t rest on our laurels, whatever they are… We\'re always looking for ways to improve Kanban and the experience for our users. If you\'d like us to let you know what\'s new, what features will be coming in the future, and how you can better make use of your board, just enter your email below to join our mailing list.',
								'kanban'
							); ?>

							<form method="POST" action="https://kanbanforwordpress.activehosted.com/proc.php" id="_form_14_" class="_form _form_14 _inline-form  _dark" novalidate>
								<input type="hidden" name="u" value="14" />
								<input type="hidden" name="f" value="14" />
								<input type="hidden" name="s" />
								<input type="hidden" name="c" value="0" />
								<input type="hidden" name="m" value="0" />
								<input type="hidden" name="act" value="sub" />
								<input type="hidden" name="v" value="2" />
								<p>
									<label class="_form-label">
										Your email *
										<br>
										<input type="text" name="email" class="input" placeholder="Type your email" required/>
									</label>
								</p>
								<p>
									<button id="_form_14_submit" class="button button-primary" type="submit">
										Sign me up!
									</button>
								</p>
							</form>

						</div>

					</div>

				</div>

			</aside>

		</div>

	</div>
</div>
