<?php

function anagram_registration_register_button() {
	if( is_user_logged_in() ) {
			return 'Upgrade';
		}else{
			return 'Join';
		};
}
add_filter( 'rcp_registration_register_button',   'anagram_registration_register_button' );



function anagram_admin_email_rcp($admin_emails){

	$admin_emails[] = 'connect@artattendant.com';

	return $admin_emails;
}
add_filter('rcp_admin_notice_emails', 'anagram_admin_email_rcp', 20, 1);



/**
 * Set User first inventory number
 *
 */
function anagram_set_invo_user_on_register( $posted, $user_id ) {

	if(!empty(get_user_meta( $user_id, 'user_inventory_number', true )))return;

		update_user_meta( $user_id, 'user_inventory_number', 100 );
		update_user_meta( $user_id, 'auto_inventory', 'Yes' );

}
add_action( 'rcp_form_processing', 'anagram_set_invo_user_on_register', 100, 2 );


/**
 * Create vendor when sign registration
 *
 */
function pw_rcp_save_user_fields_on_register( $posted, $user_id ) {

	$vendor = EDD_FES()->vendors->make_user_vendor( $user_id );
	// set to approved
     $vendor  = new FES_Vendor( $user_id, true );
     $vendor->change_status( 'approved', false );

}
add_action( 'rcp_form_processing', 'pw_rcp_save_user_fields_on_register', 100, 2 );

/**
 * Change commision depending on subscription role
 *
 */

function anagram_custom_commission_rcp_rate($rate){

	if( rcp_is_active() ) :
	$sub_id = rcp_get_subscription_id();

		switch ($sub_id)
		{
		    case 1: $rate = 90;
		    break;
		    case 2: $rate = 92;
		    break;
/*
		    case 3: $rate = 85;
		    break;
		    case 4: $rate = 90;
		    break;
*/
		}

	endif;

	return $rate;
}
add_filter('eddc_default_rate', 'anagram_custom_commission_rcp_rate', 20, 1);

