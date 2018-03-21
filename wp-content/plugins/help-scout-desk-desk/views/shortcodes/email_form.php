<div class="row">
	<div class="col-sm-offset-3 col-md-6">
		<?php if ( $error ) : ?>
			<div class="alert alert-danger" role="alert"><?php echo esc_attr( $error, 'help-scout-desk' ) ?></div>
		<?php endif ?>

		<form id="hsd_email_form" action="" method="post" accept-charset="utf-8" class="form" role="form">
			<div class="form-group">
				<label for="email"><?php esc_html_e( 'Your e-mail', 'help-scout-desk' ) ?></label>
				<input type="email" class="form-control" id="hsd_email" name="email" placeholder="<?php esc_attr_e( 'you@gmail.com', 'help-scout-desk' ) ?>" required="required">
			</div>
			<?php do_action( 'hsd_email_form_fields' ) ?>
			<input type="hidden" name="mid" value="<?php echo esc_attr( $mid ) ?>">
			<input type="hidden" name="hsd_nonce" value="<?php echo esc_attr( $nonce ) ?>">
			<button type="submit" class="button"><?php esc_html_e( 'Submit', 'help-scout-desk' ) ?></button>
		</form>
	</div><!-- .col-sm-offset-3 col-md-6 -->	
</div><!-- .row -->
