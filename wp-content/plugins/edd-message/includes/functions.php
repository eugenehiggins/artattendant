<?php
/**
 * Helper Functions
 *
 * @package     EDD\Message\Functions
 * @since       0.1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send a customer message
 *
 * @since  0.1.0
 *
 * @param  array $args The $_POST array being passed
 *
 * @return int         The Message ID that was saved, or 0 if nothing was saved
 */
function edd_message_send_message( $args ) {

	if ( empty( $args ) ) {
		return;
	}

	// The select field seems to only want to pass indexes so...
	$addresses = explode( ',', $args['edd-message-emails'] );
	$selected  = $args['edd-message-selected-emails'];
	// Let's get all the addresses and remove the ones that weren't selected
	foreach ( $addresses as $index => $address ) {
		if ( ! in_array( $index, $selected ) ) {
			unset( $addresses[ $index ] );
		}
	}
	$to         = $addresses;
	$message    = stripslashes( do_shortcode( $args['edd-message-message'] ) );
	$subject    = trim( sanitize_text_field( $args['edd-message-subject'] ) );
	$from_name  = trim( sanitize_text_field( $args['edd-message-from-name'] ) );
	$from_email = sanitize_email( $args['edd-message-from-email'] );
	$reply_to   = sanitize_email( $args['edd-message-reply'] );
	$cc         = trim( sanitize_text_field( $args['edd-message-cc'] ) );
	$bcc        = trim( sanitize_text_field( $args['edd-message-bcc'] ) );
	$customer_id = trim( sanitize_text_field( $args['edd-message-customer-id'] ) );

	if ( ! empty( $args['edd_download_files'][1]['file'] ) ) {
		$attachments = wp_list_pluck( $args['edd_download_files'], 'attachment_id' );
		$attachments = array_map( 'get_attached_file', $attachments );
	} else {
		$attachments = null;
	}

	$nonce = $args['add_customer_message_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-customer-message' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'edd-message' ) );
	}

	if ( empty( $message ) ) {
		edd_set_error( 'empty-customer-message', __( 'A message is required', 'edd-message' ) );
	}

	if ( edd_get_errors() ) {
		return;
	}

	// Send the message
	$email = new EDD_Emails();
	$email->send( $to, $subject, $message, $attachments );

	// Log the message in edd_logs
	$log_data = array(
		'log_type'     => 'email',
		'post_content' => $message,
		'post_title'   => $subject,
	);
	$log_meta = array(
		'to'          => $to,
		'customer_id' => $customer_id,
	);
	if ( ! empty( $from_email ) ) $log_meta['from'] = $from_name . ' <' . $from_email . '>';
	if ( ! empty( $reply_to ) ) $log_meta['reply_to'] = $reply_to;
	if ( ! empty( $cc ) ) $log_meta['cc'] = $cc;
	if ( ! empty( $bcc ) ) $log_meta['bcc'] = $bcc;
	if ( $attachments !== null ) $log_meta['attachments'] = $attachments;

	$log      = new EDD_Logging();
	$log->insert_log( $log_data, $log_meta );

	return false;

}

add_action( 'edd_add-customer-message', 'edd_message_send_message', 10, 1 );

/**
 * Define "From" name on email
 *
 * @param $name
 *
 * @return string|void
 */
function edd_message_from_name( $name ) {

	if ( empty( $_POST['edd-message-from-name'] ) ) {
		return $name;
	}

	$from = trim( sanitize_text_field( $_POST['edd-message-from-name'] ) );

	return $from;
}

add_filter( 'edd_email_from_name', 'edd_message_from_name' );

/**
 * Define "From" email address on message
 *
 * @param $email
 *
 * @return string|void
 */
function edd_message_from_email( $email ) {

	if ( empty( $_POST['edd-message-from-email'] ) || ! is_email( $_POST['edd-message-from-email'] ) ) {
		return $email;
	}

	$email = $_POST['edd-message-from-email'];

	return $email;
}

add_filter( 'edd_email_from_address', 'edd_message_from_email' );

/**
 * Set reply to header on email
 *
 * @param $headers
 *
 * @return string|void
 */
function edd_message_reply_to( $headers ) {

	if ( empty( $_POST['edd-message-reply'] ) || ! is_email( $_POST['edd-message-from-email'] ) ) {
		return $headers;
	}

	$reply_to = trim( sanitize_text_field( $_POST['edd-message-reply'] ) );
	$headers  = $headers . "Reply-To: $reply_to\r\n";

	return $headers;
}

add_filter( 'edd_email_headers', 'edd_message_reply_to' );

/**
 * Add CC to message
 *
 * @param $headers
 *
 * @return string|void
 */
function edd_message_cc( $headers ) {

	if ( empty( $_POST['edd-message-cc'] ) ) {
		return $headers;
	}

	$cc      = trim( sanitize_text_field( $_POST['edd-message-cc'] ) );
	$headers = $headers . "Cc: $cc\r\n";

	return $headers;
}

add_filter( 'edd_email_headers', 'edd_message_cc' );

/**
 * Add BCC to message
 *
 * @param $headers
 *
 * @return string|void
 */
function edd_message_bcc( $headers ) {

	if ( empty( $_POST['edd-message-bcc'] ) ) {
		return $headers;
	}

	$bcc     = trim( sanitize_text_field( $_POST['edd-message-bcc'] ) );
	$headers = $headers . "Bcc: $bcc\r\n";

	return $headers;
}

add_filter( 'edd_email_headers', 'edd_message_bcc' );