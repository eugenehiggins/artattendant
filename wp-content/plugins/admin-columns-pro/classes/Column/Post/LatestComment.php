<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2
 */
class ACP_Column_Post_LatestComment extends AC_Column
	implements ACP_Export_Column, ACP_Column_SortingInterface {

	public function __construct() {
		$this->set_type( 'column-latest_comment' );
		$this->set_label( __( 'Latest Comment', 'codepress-admin-columns' ) );
	}

	public function is_valid() {
		return post_type_supports( $this->get_post_type(), 'comments' );
	}

	public function get_raw_value( $post_id ) {
		$comments = get_comments( array(
			'number'  => 1,
			'fields'  => 'ids',
			'post_id' => $post_id,
		) );

		if ( empty( $comments ) ) {
			return false;
		}

		return $comments[0];
	}

	public function register_settings() {
		$this->add_setting( new AC_Settings_Column_Comment( $this ) );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this );
	}

}
