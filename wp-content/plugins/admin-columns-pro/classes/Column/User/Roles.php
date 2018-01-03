<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_User_Roles extends AC_Column_Meta
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function __construct() {
		$this->set_type( 'column-roles' );
		$this->set_label( __( 'Roles', 'codepress-admin-columns' ) );
	}

	public function get_meta_key() {
		global $wpdb;

		return $wpdb->get_blog_prefix() . 'capabilities'; // WPMU compatible
	}

	// Display

	public function get_value( $user_id ) {
		$user = new WP_User( $user_id );

		$roles = array();
		foreach ( ac_helper()->user->translate_roles( $user->roles ) as $role => $label ) {
			$roles[] = ac_helper()->html->tooltip( $label, $role );
		}

		if ( empty( $roles ) ) {
			return $this->get_empty_char();
		}

		return implode( $this->get_separator(), $roles );
	}

	public function editing() {
		return new ACP_Editing_Model_User_Role( $this );
	}

	public function sorting() {
		return new ACP_Sorting_Model_User_Roles( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_Role( $this );
	}

	public function export() {
		return new ACP_Export_Model_User_Role( $this );
	}

}
