<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles general functionality for admin screens
 *
 * @since 1.0
 */
class ACP_Export_Admin {

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_download_export' ) );
	}

	/**
	 * Check whether a request was sent for downloading an export file, and if so, offer it for
	 * download (if the user has permission to download it)
	 *
	 * @since 1.0
	 */
	public function maybe_download_export() {
		$export_download = filter_input( INPUT_GET, 'acp-export-download' );

		if ( ! $export_download ) {
			return;
		}

		$export_dir = ac_addon_export()->get_export_dir();

		// Base directory for the export file
		$fname = md5( get_current_user_id() . $export_download ) . '.csv';

		// Final file CSV contents
		$content = '';

		// Counter for merging the decrypted value of multiple files
		$counter = 0;

		while ( true ) {
			// Construct full file path
			$fpath = $export_dir['path'] . $fname;
			$fpath .= '-' . $counter . '.csv';

			// Check whether the file to load exists
			if ( ! file_exists( $fpath ) ) {
				if ( $counter === 0 ) {
					wp_die( __( 'The requested file does not exist.', 'codepress-admin-columns' ) );
				}

				break;
			}

			// Get contents of file
			$file_content = file_get_contents( $fpath );

			// Decrypt the file contents
			try {
				$cryptor = new ACP_Export_Cryptor();
				$key = ACP_Export_Utility_Users::get_user_encryption_key();
				$result = $cryptor->decrypt( $file_content, $key );
				$content .= $result;
			} catch ( Exception $e ) {
				wp_die( __( 'The requested file could not be processed.', 'codepress-admin-columns' ) );
			}

			$counter++;
		}

		$file = new ACP_Export_DownloadableFile();
		$file->load_content_string( $content );

		$prefix = filter_input( INPUT_GET, 'acp-export-filename-prefix', FILTER_SANITIZE_STRING );

		if ( $prefix ) {
			$prefix .= '-';
		}

		$file->export( $prefix . 'export-' . date( 'Y-m-d' ) . '.csv' );
	}

}
