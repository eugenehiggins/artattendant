<?php
/*
Plugin Name: 		Admin Columns Pro - Advanced Custom Fields (ACF)
Version: 			2.1.1
Description: 		Supercharges Admin Columns Pro with very cool ACF columns.
Author: 			Admin Columns
Author URI: 		https://www.admincolumns.com
Plugin URI: 		https://www.admincolumns.com
Text Domain: 		codepress-admin-columns
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACA_ACF {

	CONST CLASS_PREFIX = 'ACA_ACF_';

	/**
	 * @var int Plugin version
	 */
	private $version;

	/**
	 * @var int ACF version
	 */
	private $acf_version;

	/**
	 * @var ACA_ACF
	 */
	private static $_instance = null;

	/**
	 * @since 2.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	private function __construct() {
		add_action( 'after_setup_theme', array( $this, 'init' ) );
	}

	/**
	 * @since 2.0
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		if ( $this->has_missing_dependencies() ) {
			return;
		}

		AC()->autoloader()->register_prefix( self::CLASS_PREFIX, $this->get_plugin_dir() . 'classes/' );

		add_action( 'ac/column_groups', array( $this, 'register_column_groups' ) );
		add_action( 'acp/column_types', array( $this, 'add_columns' ) );

		// Editing
		add_action( 'ac/table_scripts/editing', array( $this, 'table_scripts_editing' ) );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function register_column_groups( $groups ) {
		$groups->register_group( 'acf', __( 'Advanced Custom Fields' ), 11 );
	}

	/**
	 * @return bool True when there are missing dependencies
	 */
	private function has_missing_dependencies() {
		require_once $this->get_plugin_dir() . 'classes/Dependencies.php';

		$dependencies = new ACA_ACF_Dependencies( __FILE__ );

		$dependencies->is_acp_active( '4.1' );

		if ( ! $this->is_acf_active() ) {
			$dependencies->add_missing( $dependencies->get_search_link( 'Advanced Custom Fields', 'Advanced Custom Fields' ) );
		}

		return $dependencies->has_missing();
	}

	/**
	 * Main plugin directory
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * @since 2.0
	 */
	public function get_plugin_dir() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * @since 2.0
	 */
	public function get_plugin_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Set plugin version
	 */
	private function set_version() {
		$plugins = get_plugins();

		$this->version = $plugins[ $this->get_basename() ]['Version'];
	}

	/**
	 * @since 2.0
	 */
	public function get_version() {
		if ( null === $this->version ) {
			$this->set_version();
		}

		return $this->version;
	}

	/**
	 * Add custom columns
	 *
	 * @param AC_ListScreen $list_screen
	 *
	 * @since 1.0
	 */
	public function add_columns( $list_screen ) {

		switch ( true ) {

			case $list_screen instanceof AC_ListScreen_Post :
				$list_screen->register_column_type( new ACA_ACF_Column );

				break;
			case $list_screen instanceof AC_ListScreen_Media :
				$list_screen->register_column_type( new ACA_ACF_Column_Media );

				break;
			case $list_screen instanceof AC_ListScreen_User :
				$list_screen->register_column_type( new ACA_ACF_Column_User );

				break;
			case $list_screen instanceof AC_ListScreen_Comment :
				$list_screen->register_column_type( new ACA_ACF_Column_Comment );

				break;
			case $list_screen instanceof ACP_ListScreen_Taxonomy :
				$list_screen->register_column_type( new ACA_ACF_Column_Taxonomy );

				break;
		}
	}

	/**
	 * Whether ACF is active
	 *
	 * @since 1.1
	 *
	 * @return bool Returns true if ACF is active, false otherwise
	 */
	private function is_acf_active() {
		return class_exists( 'acf', false );
	}

	/**
	 * @since 2.0
	 *
	 * @return bool True when ACF PRO is installed
	 */
	public function is_acf_pro() {
		return 5 === $this->get_acf_major_version();
	}

	/**
	 * @since 2.0
	 *
	 * @return bool True when ACF Free is installed
	 */
	public function is_acf_free() {
		return 4 === $this->get_acf_major_version();
	}

	/**
	 * @return string
	 */
	public function get_class_prefix() {
		return self::CLASS_PREFIX;
	}

	/**
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function get_acf_major_version() {
		$acf_version = explode( '.', $this->get_acf_version() );

		if ( ! $acf_version || ! isset( $acf_version[0] ) ) {
			return false;
		}

		return absint( $acf_version[0] );
	}

	private function set_acf_version() {
		if ( $this->is_acf_active() ) {
			if ( function_exists( 'acf_get_setting' ) ) {

				// Pro
				$this->acf_version = acf_get_setting( 'version' );
			} else {

				// Free
				$this->acf_version = acf()->get_info( 'version' );
			}
		}
	}

	/**
	 * Get the version of the currently active ACF plugin
	 *
	 * @since 1.1
	 *
	 * @return string Currently active ACF plugin version
	 */
	private function get_acf_version() {
		if ( null === $this->acf_version ) {
			$this->set_acf_version();
		}

		return $this->acf_version;
	}

	/**
	 * @param string $field_hash ACF field hash
	 *
	 * @return array|false
	 */
	public function get_acf_field( $field_hash ) {
		if ( ! $field_hash ) {
			return false;
		}

		$field = false;

		if ( function_exists( 'acf_get_field' ) ) {

			// Pro
			$field = acf_get_field( $field_hash );
		} else if ( function_exists( 'get_field_object' ) ) {

			// Free
			$field = get_field_object( $field_hash );
		}

		return $field;
	}

	public function table_scripts_editing() {
		wp_enqueue_script( 'ac-acf-table', $this->get_plugin_url() . 'assets/js/table.js', array( 'jquery' ), $this->get_version() );
		wp_enqueue_style( 'ac-acf-table', $this->get_plugin_url() . 'assets/css/table.css' );
	}

}

function ac_addon_acf() {
	return ACA_ACF::instance();
}

ac_addon_acf();