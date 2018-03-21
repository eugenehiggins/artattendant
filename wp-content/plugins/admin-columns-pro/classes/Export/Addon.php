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

		new ACP_Export_Admin();
		new ACP_Export_TableScreenOptions();

		add_action( 'ac/table/list_screen', array( $this, 'load_list_screen' ) );
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
	 * Load a list screen and potentially attach the proper exporting information to it
	 *
	 * @since 1.0
	 *
	 * @param AC_ListScreen $list_screen List screen for current table screen
	 */
	public function load_list_screen( $list_screen ) {
		if ( $list_screen instanceof ACP_Export_ListScreen ) {
			$list_screen->export()->attach();
		}
	}

}

/**
 * @return ACP_Export_Addon
 */
function ac_addon_export() {
	return ACP_Export_Addon::instance();
}
