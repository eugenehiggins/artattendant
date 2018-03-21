<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ListScreen_MSSite extends AC_ListScreenWP
	implements ACP_Editing_ListScreen {

	public function __construct() {
		$this->set_label( __( 'Network Sites' ) );
		$this->set_singular_label( __( 'Network Site' ) );
		$this->set_key( 'wp-ms_sites' );
		$this->set_screen_id( 'sites-network' );
		$this->set_screen_base( 'sites-network' );
		$this->set_meta_type( 'site' );
		$this->set_group( 'network' );
		$this->set_network_only( true );
	}

	/**
	 * @since 4.0
	 * @return WP_Site Site object
	 */
	protected function get_object( $site_id ) {
		return get_site( $site_id );
	}

	/**
	 * @return WP_MS_Sites_List_Table
	 */
	public function get_list_table() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-ms-sites-list-table.php' );

		return new WP_MS_Sites_List_Table( array( 'screen' => $this->get_screen_id() ) );
	}

	public function set_manage_value_callback() {
		add_action( "manage_sites_custom_column", array( $this, 'manage_value' ), 100, 2 );
	}

	/**
	 * @return string
	 */
	protected function get_admin_url() {
		return network_admin_url( 'sites.php' );
	}

	/**
	 * @since 2.4.7
	 */
	public function manage_value( $column_name, $blog_id ) {
		echo $this->get_display_value_by_column_name( $column_name, $blog_id, null );
	}

	public function get_single_row( $site_id ) {
		return false;
	}

	/**
	 * Register custom columns
	 */
	protected function register_column_types() {
		$this->register_column_type( new ACP_Column_Actions() );

		$this->register_column_types_from_dir( ACP()->get_plugin_dir() . 'classes/Column/NetworkSite', ACP()->get_prefix() );
	}

	public function editing( $model ) {
		return new ACP_Editing_Strategy_Site( $model );
	}

}
