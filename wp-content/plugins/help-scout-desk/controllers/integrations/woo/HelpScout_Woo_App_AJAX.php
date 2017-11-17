<?php

/**
 * This class takes care of AJAX requests from HelpScout
 *
 * A lot of the code below should be properly accredited to the woo-helpscout project.
 * @see https://github.com/dannyvankooten/woo-helpscout
 */
class HelpScout_Woo_App_AJAX extends HelpScout_Woo_App {

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
		if ( ! current_user_can( 'manage_options' ) && ! wp_verify_nonce( $_REQUEST['nonce'], 'hs-woo-' . $_REQUEST[ self::APP_AJAX_HANDLER ] ) ) {
			wp_die( 'Do not test me!' );
		}

		switch ( $_REQUEST[ self::APP_AJAX_HANDLER ] ) {
			case 'purchase-receipt':
				self::handle_purchase_receipt_resend();
				break;
			default:
				break;
		}

		die('<script>window.close();</script>');
	}

	/**
	 * Handle resending the purchase email.
	 */
	private static function handle_purchase_receipt_resend() {
		$purchase_id = (int) $_REQUEST['order'];

	}
}
