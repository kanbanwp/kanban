 <?php include Kanban_Template::find_template('inc/header') ?>

<div class="container">

<h1>
	<?php echo __('Login', Kanban::$instance->settings->file) ?>
</h1>



<?php if ( Kanban::$instance->flash->hasMessages() ) : ?>
			<div class="container" id="alerts-site-wide">
				<?php echo Kanban::$instance->flash->display(); ?>
			</div><!-- container -->
<?php endif // has messages ?>



<div class="jumbotron">
	<form action="" method="post">
<?php if ( !is_user_logged_in() ) : ?>
		<div class="form-group">
			<label for="email">
				<?php echo __('Email', Kanban::$instance->settings->file) ?>
			</label>
			<input type="email" name="email" id="email" class="form-control input-lg" placeholder="<?php echo __('email', Kanban::$instance->settings->file) ?>">
		</div><!-- form group -->
		<div class="form-group">
			<label for="password">
				<?php echo __('Password', Kanban::$instance->settings->file) ?>
			</label>
			<input type="password" name="password" id="password" class="form-control input-lg" placeholder="<?php echo __('password', Kanban::$instance->settings->file) ?>">
		</div><!-- form group -->
		<div>
			<button type="submit" class="btn btn-primary btn-lg">
				<?php echo __('Log in', Kanban::$instance->settings->file) ?>
			</button>
			<?php wp_nonce_field( 'login', Kanban_Utils::get_nonce() ); ?>
		</div>
<?php else : // is_user_logged_in ?>
		<p>
			<?php echo __('Whoops, looks like you haven\'t been granted access yet. Click below to request access.', Kanban::$instance->settings->file) ?>
			</p>
		<p class="text-center">
			<button type="submit" class="btn btn-primary btn-lg">
				<?php echo __('Request access', Kanban::$instance->settings->file) ?>
			</button>
			<?php wp_nonce_field( 'request_access', Kanban_Utils::get_nonce() ); ?>
		</p>
<?php endif // is_user_logged_in ?>
	</form>
</div><!-- jumbotron -->



<?php include Kanban_Template::find_template('inc/footer') ?>




