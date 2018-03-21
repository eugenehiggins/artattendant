<?php

/**
 * Help Scout API Controller
 *
 * The Email_Login class should work completely independent of any other class,
 * this way it could be unloaded.
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class Email_Login extends HSD_Controller {
	const SUBMISSION_SUCCESS_QV = 'email_sub_success';
	const SUBMISSION_ERROR_QV = 'email_submission_error';
	const EMAIL_SHORTCODE = 'hsd_email_capture';
	const TRANSIENT_KEY = 'hsd_email_trans_v1';
	const COOKIE = 'hsd_customer_info_v1';

	public static function init() {

		// Show the login
		add_filter( 'hsd_is_customer', array( __CLASS__, 'is_customer_check_cookie' ) );
		add_action( 'helpscout_desk_sc_form_not_customer', array( __CLASS__, 'email_submission_form' ) );

		// process form
		add_action( 'hsd_submission_form', array( __CLASS__, 'maybe_process_form' ) );

		// find email via cookie
		add_filter( 'hsd_api_find_email', array( __CLASS__, 'find_email_via_cookie' ), 10, 2 );
	}

	public static function is_customer_check_cookie( $bool = false ) {
		if ( ! $bool ) {
			if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
				$customer_info = self::get_stored_customer_email( $_COOKIE[ self::COOKIE ] );
				$bool = ( is_array( $customer_info ) || $customer_info != '' );
			}
		}
		return $bool;
	}

	public static function find_email_via_cookie( $user_email = '', $user_id = 0 ) {
		if ( ! $user_id && $user_email == '' ) {
			if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
				$user_email = self::get_stored_customer_email( $_COOKIE[ self::COOKIE ] );
			}
		}
		return $user_email;
	}

	public static function store_customer_email( $email = '' ) {
		if ( ! headers_sent() ) {
			$trans_key = self::TRANSIENT_KEY.microtime();
			set_transient( $trans_key, $email, 60 * 60 * 24 * 30 );
			setcookie( self::COOKIE, $trans_key, time() + ( 60 * 60 * 24 * 30 ), COOKIEPATH, COOKIE_DOMAIN );
		}
		do_action( 'hsd_maybe_store_customer_email' );
		return $email;
	}

	public static function get_stored_customer_email( $key ) {
		return get_transient( $key );
	}

	////////////
	// Views //
	////////////


	/**
	 * Show the reply/creation form
	 * @param  array $atts
	 * @param  string $content used to show a message after a message is received.
	 * @return
	 */
	public static function email_submission_form( $atts, $content = '' ) {
		// Don't show the form if not on the conversation view
		if ( isset( $_GET[ self::SUBMISSION_SUCCESS_QV ] ) && $_GET[ self::SUBMISSION_SUCCESS_QV ] ) {
			return self::load_view_to_string( 'shortcodes/success_message', array(
				'message' => $content,
			), true );
		}
		$error = false;
		if ( isset( $_GET[ self::SUBMISSION_ERROR_QV ] ) && $_GET[ self::SUBMISSION_ERROR_QV ] ) {
			$error = urldecode( $_GET[ self::SUBMISSION_ERROR_QV ] );
		}

		$mailbox_id = ( isset( $atts['mid'] ) ) ? $atts['mid'] : HSD_Settings::get_mailbox();

		// Print the form
		print self::load_view_to_string( 'shortcodes/email_form', array(
				'nonce' => wp_create_nonce( HSD_Controller::NONCE ),
				'mid' => $mailbox_id,
				'error' => $error,
		), true );
	}


	/**
	 * Maybe process the email submission
	 * @return
	 */
	public static function maybe_process_form() {
		// nonces are checked in HSD_Forms::maybe_process_form before hsd_submission_form

		do_action( 'hsd_process_email_form_submission' );
		// Process email request blocker
		if ( isset( $_REQUEST['email'] ) ) {
			// store the email if no customer ids are found.
			self::store_customer_email( $_REQUEST['email'] );
			wp_redirect( add_query_arg( 'email_info_success', 1, esc_url_raw( apply_filters( 'si_estimate_submitted_redirect_url', null ) ) ) );
			exit();
		}
	}
}
