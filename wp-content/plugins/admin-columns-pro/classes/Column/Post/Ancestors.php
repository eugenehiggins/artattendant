<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2
 */
class ACP_Column_Post_Ancestors extends AC_Column
	implements ACP_Export_Column, ACP_Column_FilteringInterface {

	public function __construct() {
		$this->set_type( 'column-ancestors' );
		$this->set_label( __( 'Ancestors', 'codepress-admin-columns' ) );
	}

	/**
	 * @return string
	 */
	public function get_separator() {
		return '<span class="dashicons dashicons-arrow-right-alt2"></span>';
	}

	public function get_value( $id ) {
		$ancestors = $this->get_ancestor_ids( $id );

		if ( ! $ancestors ) {
			return $this->get_empty_char();
		}

		/**
		 * @var AC_Collection $formatted_values
		 */
		$formatted_values = $this->get_formatted_value( new AC_Collection( $ancestors ) );

		return $formatted_values->implode( $this->get_separator() );
	}

	/**
	 * @param int $id
	 *
	 * @return array|false
	 */
	public function get_ancestor_ids( $id ) {
		$ancestors = $this->get_raw_value( $id );

		if ( empty( $ancestors ) ) {
			return false;
		}

		return array_reverse( $ancestors );
	}

	public function get_raw_value( $id ) {
		$post = get_post( $id );

		return $post->ancestors;
	}

	public function register_settings() {
		$this->add_setting( new AC_Settings_Column_Post( $this ) );
	}

	public function is_valid() {
		return is_post_type_hierarchical( $this->get_post_type() );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Ancestors( $this );
	}

	public function export() {
		return new ACP_Export_Model_Post_Ancestors( $this );
	}

}
