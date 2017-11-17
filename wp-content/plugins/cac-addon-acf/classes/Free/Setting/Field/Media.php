<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Free_Setting_Field_Media extends ACA_ACF_Free_Setting_Field {

	public function get_grouped_field_options() {

		add_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		add_filter( 'acf/location/rule_match/post_type', array( $this, 'is_location_attachment' ), 16, 2 );
		add_filter( 'acf/location/rule_match/ef_media', '__return_true', 16 );

		$group_ids = apply_filters( 'acf/location/match_field_groups', array(), array() );

		remove_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		remove_filter( 'acf/location/rule_match/post_type', array( $this, 'is_location_attachment' ), 16 );
		remove_filter( 'acf/location/rule_match/ef_media', '__return_true', 16 );

		return $this->get_option_groups( $group_ids );
	}

	public function is_location_attachment( $match, $rule ) {
		return isset( $rule['value'] ) && 'attachment' === $rule['value'];
	}

}
