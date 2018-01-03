<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Export_Addon extends AC_Addon {

	/**
	 * @var self
	 */
	protected static $instance;

	protected function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Export_', $this->get_plugin_dir() . 'classes/' );

		// Register list screens
		add_action( 'ac/registered_list_screen', array( $this, 'register_default_list_screens' ) );

		// Initialize classes
		new ACP_Export_Admin();
		new ACP_Export_TableScreen();
		new ACP_Export_TableScreenOptions();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_file() {
		return __FILE__;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return ACP()->get_version();
	}

	/**
	 * Get the path and URL to the directory used for uploading
	 *
	 * @since 1.0
	 *
	 * @return array Two-dimensional associative array with keys "path" and "url", containing the
	 *   full path and the full URL to the export files directory, respectively
	 */
	public function get_export_dir() {
		// Base directory for uploads
		$upload_dir = wp_upload_dir();

		// Paths for exported files
		$suffix = 'admin-columns/export/';
		$export_path = trailingslashit( $upload_dir['basedir'] ) . $suffix;
		$export_url = trailingslashit( $upload_dir['baseurl'] ) . $suffix;
		$export_path_exists = true;

		// Maybe create export directory
		if ( ! is_dir( $export_path ) ) {
			$export_path_exists = wp_mkdir_p( $export_path );
		}

		return array(
			'path'  => $export_path,
			'url'   => $export_url,
			'error' => $export_path_exists ? '' : __( 'Creation of Admin Columns export directory failed. Please make sure that your uploads folder is writable.', 'codepress-admin-columns' ),
		);
	}

	/**
	 * Callback for when a list screen is registered in Admin Columns. In case the list screen
	 * supports exporting, an "ExportableListScreen" will be instantiated, and the list screen
	 * object will be attached to it
	 *
	 * @param AC_ListScreen $list_screen
	 *
	 * @see   filter:ac/registered_list_screen
	 * @since 1.0
	 */
	public function register_default_list_screens( AC_ListScreen $list_screen ) {
		switch ( true ) {
			case $list_screen instanceof AC_ListScreenPost :
				ACP_Export_ListScreens::register_list_screen( new ACP_Export_ListScreen_Post( $list_screen ) );

				break;
			case $list_screen instanceof AC_ListScreen_User :
				ACP_Export_ListScreens::register_list_screen( new ACP_Export_ListScreen_User( $list_screen ) );

				break;
			case $list_screen instanceof AC_ListScreen_Comment :
				ACP_Export_ListScreens::register_list_screen( new ACP_Export_ListScreen_Comment( $list_screen ) );

				break;
			case $list_screen instanceof ACP_ListScreen_Taxonomy :
				ACP_Export_ListScreens::register_list_screen( new ACP_Export_ListScreen_Taxonomy( $list_screen ) );

				break;
		}
	}

}

/**
 * @return ACP_Export_Addon
 */
function ac_addon_export() {
	return ACP_Export_Addon::instance();
}
