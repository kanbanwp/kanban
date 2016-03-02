<?php include Kanban_Template::find_template( 'inc/header' ); ?>



<div class="row" style="margin-top: 60px;">



	<form action="" method="post" class="col-sm-4 col-sm-offset-4">
		<img src="<?php echo Kanban::get_instance()->settings->uri ?>/img/kanbanwp-logo.png" class="img-responsive" style="margin: 0 auto 30px;">



		<?php Kanban_Flash::flash(); ?>



		<div class="panel panel-default">
			<div class="panel-heading">
<?php if ( ! is_user_logged_in() ) : ?>
				<h2><?php echo __( 'Please Login', Kanban::$instance->settings->file ); ?></h2>
				<div class="form-group">
					<label for="email" class="sr-only">
						<?php echo __( 'Email or username', 'kanban' ); ?>
					</label>
					<input type="text" name="email" id="email" class="form-control input-lg" placeholder="<?php echo __('Email or username', 'kanban'); ?>" required autofocus>
				</div><!-- form group -->
				<div class="form-group">
					<label for="password" class="sr-only">
						<?php echo __( 'Password', 'kanban' ); ?>
					</label>
					<input type="password" name="password" id="password" class="form-control input-lg" placeholder="<?php echo __('Password', 'kanban'); ?>" required>
				</div><!-- form group -->
				<div>
					<button type="submit" class="btn btn-lg btn-primary btn-block">
						<?php echo __( 'Log in', 'kanban' ); ?>
					</button>
					<?php wp_nonce_field( 'login', Kanban_Utils::get_nonce() ); ?>
				</div>
<?php else : // is_user_logged_in ?>
				<p>
					<?php echo __( 'Whoops, looks like you haven\'t been granted access yet. Click below to request access.', 'kanban' ); ?>
				</p>
				<p class="text-center">
					<button type="submit" class="btn btn-primary btn-lg">
						<?php echo __( 'Request access', 'kanban' ); ?>
					</button>
					<?php wp_nonce_field( 'request_access', Kanban_Utils::get_nonce() ); ?>
				</p>
<?php endif // is_user_logged_in ?>
			</div><!-- panel-body -->
		</div><!-- panel -->
	</form>



</div><!-- row -->



<?php include Kanban_Template::find_template( 'inc/footer' )
