<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACP_ThirdParty_bbPress_Addon {

	public function __construct() {

		// Columns
		add_action( 'ac/column_types', array( $this, 'set_columns' ) );
		add_action( 'ac/column_groups', array( $this, 'register_column_group' ) );

		// Listscreen
		add_action( 'ac/list_screen_groups', array( $this, 'register_list_screen_group' ) );
		add_action( 'ac/list_screens', array( $this, 'register_list_screens' ), 11 );

		// Editing
		add_filter( 'ac/editing/role_group', array( $this, 'editing_role_group' ), 10, 2 );

	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function set_columns( $list_screen ) {
		$list_screen->register_column_types_from_dir( plugin_dir_path( __FILE__ ) . 'Column', ACP()->get_prefix() );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function register_column_group( $groups ) {
		$groups->register_group( 'bbpress', __( 'bbPress' ), 25 );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function register_list_screen_group( $groups ) {
		$groups->register_group( 'bbpress', __( 'bbPress' ), 8 );
	}

	/**
	 * @since 4.0
	 * @param AC_ListScreen $list_screen
	 */
	public function register_list_screens() {
		foreach ( $this->get_post_types() as $post_type ) {

			$list_screen = new ACP_ListScreen_Post( $post_type );
			$list_screen->set_group( 'bbpress' );

			AC()->register_list_screen( $list_screen );
		}
	}

	/**
	 * @return bool
	 */
	private function is_active() {
		return class_exists( 'bbPress' );
	}

	/**
	 * @return string[]
	 */
	private function get_post_types() {
		if ( ! $this->is_active() ) {
			return array();
		}

		return array( 'forum', 'topic', 'reply' );
	}

	/**
	 * @param array $roles
	 *
	 * @return string
	 */
	public function editing_role_group( $group, $role ) {
		if ( substr( $role, 0, 4 ) === "bbp_" ) {
			$group = __( 'bbPress', 'codepress-admin-columns' );
		}

		return $group;
	}

}
