<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Free_Setting_Field_Post extends ACA_ACF_Free_Setting_Field {

	public function get_grouped_field_options( ) {
		add_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		switch ( $this->column->get_post_type() ) {
			case 'post' :
				add_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );
				break;
			case 'page' :
				add_filter( 'acf/location/rule_match/page', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/page_parent', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/page_template', '__return_true', 16 );
				break;
		}

		add_filter( 'acf/location/rule_match/post', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/post_category', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/post_status', '__return_true', 16 );
		add_filter( 'acf/location/rule_match/taxonomy', '__return_true', 16 );

		$group_ids = apply_filters( 'acf/location/match_field_groups', array(), array( 'post_type' => $this->column->get_post_type() ) );

		remove_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );

		remove_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );

		remove_filter( 'acf/location/rule_match/page', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/page_parent', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/page_template', '__return_true', 16 );

		remove_filter( 'acf/location/rule_match/post', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/post_category', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/post_status', '__return_true', 16 );
		remove_filter( 'acf/location/rule_match/taxonomy', '__return_true', 16 );

		return $this->get_option_groups( $group_ids );
	}

}
