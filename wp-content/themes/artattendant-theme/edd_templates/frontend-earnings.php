<h1 class="fes-headers" id="fes-commissions-page-title"><?php _e( 'Purchase History', 'edd_fes' ); ?></h1>
<?php
echo do_shortcode('[purchase_history]');

?>

<?php
if ( EDD_FES()->integrations->is_commissions_active() ) { ?>
	<h1 class="fes-headers" id="fes-commissions-page-title"><?php _e( 'Commissions Overview', 'edd_fes' ); ?></h1>

	<div>Your commission rate is <?php echo eddc_get_recipient_rate( 0, get_current_user_id() ); ?>%.</div>
	<?php
	if ( eddc_user_has_commissions() ) {
		echo do_shortcode('[edd_commissions]');
	}
	else{

		echo __( 'You haven\'t made any sales yet!', 'edd_fes' );
	}
} else {
	echo 'Error 4908';
}