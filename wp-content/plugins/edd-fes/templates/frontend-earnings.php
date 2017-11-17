<?php
if ( EDD_FES()->integrations->is_commissions_active() ) { ?>
	<?php
	echo do_shortcode( '[edd_commissions_overview]' );
	if ( eddc_user_has_commissions() ) {
		echo do_shortcode( '[edd_commissions]' );
	} else {
		echo __( 'You haven\'t made any sales yet!', 'edd_fes' );
	}
} else {
	echo 'Error 4908';
}
