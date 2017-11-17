<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Setting_Field_User extends ACA_ACF_Setting_Field {

	public function get_grouped_field_options() {

		add_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		add_filter( 'acf/location/rule_match/user_form', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/user_role', '__return_true', 16 );

		$groups = acf_get_field_groups( array( 'ac_dummy' => true ) ); // We need to pass an argument, otherwise the filters won't work

		// Remove all location filters for the next storage_model
		remove_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		remove_filter( 'acf/location/rule_match/user_form', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/user_role', '__return_true', 16 );

		return $this->get_option_groups( $groups );
	}

}
