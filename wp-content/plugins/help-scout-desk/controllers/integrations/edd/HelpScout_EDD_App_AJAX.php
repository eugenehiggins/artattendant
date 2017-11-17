<?php

/**
 * This class takes care of AJAX requests from HelpScout
 *
 * A lot of the code below should be properly accredited to the edd-helpscout project.
 * @see https://github.com/dannyvankooten/edd-helpscout
 */
class HelpScout_EDD_App_AJAX extends HelpScout_EDD_App {

	/**
	 * Constructor
	 */
	public static function init() {
		// Double add_action because we want it to work when you're logged in too
		add_action( 'wp_ajax_nopriv_'.self::APP_AJAX_HANDLER, array( __CLASS__, 'ajax_action' ) );
		add_action( 'wp_ajax_'.self::APP_AJAX_HANDLER, array( __CLASS__, 'ajax_action' ) );
	}

	/**
	 * Handle AJAX actions
	 */
	public static function ajax_action() {

		// Use wp_verify_nonce and check for a 1 return, which means a nonce is valid for 12 hours instead of the default 24
		if ( ! current_user_can( 'manage_options' ) && ! wp_verify_nonce( $_REQUEST['nonce'], 'hs-edd-' . $_REQUEST[ self::APP_AJAX_HANDLER ] ) ) {
			wp_die( 'Do not test me!' );
		}

		switch ( $_REQUEST[ self::APP_AJAX_HANDLER ] ) {
			case 'deactivate':
				self::handle_deactivation_request();
				break;
			case 'purchase-receipt':
				self::handle_purchase_receipt_resend();
				break;
			default:
				break;
		}

		die('<script>window.close();</script>');
	}

	/**
	 * Deactivates a site
	 */
	private static function handle_deactivation_request() {
		$license_id = sanitize_text_field( $_REQUEST['license_id'] );
		$site_url = sanitize_text_field( $_REQUEST['site_url'] );
		edd_software_licensing()->delete_site( $license_id, $site_url );
	}

	/**
	 * Handle resending the purchase email.
	 */
	private static function handle_purchase_receipt_resend() {
		$purchase_id = (int) $_REQUEST['order'];

		edd_email_purchase_receipt( $purchase_id, false );

		// Grab all downloads of the purchase and update their file download limits, if needed
		// This allows admins to resend purchase receipts to grant additional file downloads
		$downloads = edd_get_payment_meta_downloads( $purchase_id );

		if ( is_array( $downloads ) ) {
			foreach ( $downloads as $download ) {
				$limit = edd_get_file_download_limit( $download['id'] );
				if ( ! empty( $limit ) ) {
					edd_set_file_download_limit_override( $download['id'], $purchase_id );
				}
			}
		}

	}
}
