<?php
/**
 * Email functions
 *
 * @package     EDD_Commissions
 * @subpackage  Email
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Retrieve default email body
 *
 * @since       3.0
 * @return      string $body The default email
 */
function eddc_get_email_default_body() {
	$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
	$message   = __( 'Hello {name},', 'eddc' ) . "\n\n" . sprintf( __( 'You have made a new sale on %s!', 'eddc' ), stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) ) . "\n\n";
	$message  .= __( 'Item sold: ', 'eddc' ) . "{download}\n\n";
	$message  .= __( 'Amount: ', 'eddc' ) . "{amount}\n\n";
	$message  .= __( 'Commission Rate: ', 'eddc' ) . "{rate}\n\n";
	$message  .= __( 'Thank you', 'eddc' );

	return apply_filters( 'eddc_email_default_body', $message );
}


/**
 * Parse template tags for display
 *
 * @since       3.0
 * @return      string $tags The parsed template tags
 */
function eddc_display_email_template_tags() {
	$template_tags = eddc_get_email_template_tags();
	$tags = '';

	foreach ( $template_tags as $template_tag ) {
		$tags .= '{' . $template_tag['tag'] . '} - ' . $template_tag['description'] . '<br />';
	}

	return $tags;
}


/**
 * Retrieve email template tags
 *
 * @since       3.0
 * @return      array $tags The email template tags
 */
function eddc_get_email_template_tags() {
	$tags = array(
		array(
			'tag'         => 'download',
			'description' => sprintf( __( 'The name of the purchased %s', 'eddc' ), edd_get_label_singular() ),
		),
		array(
			'tag'         => 'amount',
			'description' => sprintf( __( 'The value of the purchased %s', 'eddc' ), edd_get_label_singular() ),
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the purchase', 'eddc' ),
		),
		array(
			'tag'         => 'rate',
			'description' => __( 'The commission rate of the user', 'eddc' ),
		),
		array(
			'tag'         => 'name',
			'description' => __( 'The first name of the user', 'eddc' ),
		),
		array(
			'tag'         => 'fullname',
			'description' => __( 'The full name of the user', 'eddc' ),
		),
		array(
			'tag'         => 'commission_id',
			'description' => __( 'The ID of the commission record', 'eddc' ),
		),
		array(
			'tag'         => 'item_price',
			'description' => __( 'The final price of the item sold', 'eddc' ),
		),
		array(
			'tag'         => 'item_tax',
			'description' => __( 'The amount of tax calculated for the item', 'eddc' ),
		),
	);

	return apply_filters( 'eddc_email_template_tags', $tags );
}


/**
 * Parse email template tags
 *
 * @since       3.0
 * @param       string $message The email body
 * @param       int $download_id The ID for a given download
 * @param       int $commission_id The ID of this commission
 * @param       int $commission_amount The amount of the commission
 * @param       int $rate The commission rate of the user
 * @return      string $message The email body
 */
function eddc_parse_template_tags( $message, $download_id, $commission_id, $commission_amount, $rate ) {
	$commission = new EDD_Commission( $commission_id );
	$download   = new EDD_Download( $commission->download_id );

	$payment    = false;
	if ( ! empty( $commission->payment_id ) ) {
		$payment = edd_get_payment( $commission->payment_id );
	}

	$item_purchased  = $download->get_name();
	if ( $download->has_variable_prices() ) {
		$prices = $download->get_prices();
		if ( isset( $prices[ $commission->price_id ] ) ) {
			$item_purchased .= ' - ' . $prices[ $commission->price_id ]['name'];
		}
	}
	$amount    = html_entity_decode( edd_currency_filter( edd_format_amount( $commission->amount ) ) );
	$date      = date_i18n( get_option( 'date_format' ), strtotime( $commission->date_created ) );
	$user      = get_userdata( $commission->user_id );

	if ( 'percentage' === $commission->type ) {
		$rate = $commission->rate . '%';
	} else {
		$rate = __( 'Flat rate', 'eddc' );
	}

	if ( ! empty( $user->first_name ) ) {
		$name = $user->first_name;

		if ( ! empty( $user->last_name ) ) {
			$fullname = $name . ' ' . $user->last_name;
		} else {
			$fullname = $name;
		}
	} else {
		$name = $user->display_name;
		$fullname = $name;
	}

	$item_price = '';
	$item_tax   = '';
	if ( false !== $payment ) {
		$cart_item = isset( $payment->cart_details[ $commission->cart_index ] ) ? $payment->cart_details[ $commission->cart_index ] : false;
		if ( $cart_item ) {
			$item_price = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['item_price'] ) ) );
			$item_tax   = html_entity_decode( edd_currency_filter( edd_format_amount( $cart_item['tax'] ) ) );
		}
	}

	$message = str_replace( '{download}', $item_purchased, $message );
	$message = str_replace( '{amount}', $amount, $message );
	$message = str_replace( '{date}', $date, $message );
	$message = str_replace( '{rate}', $rate, $message );
	$message = str_replace( '{name}', $name, $message );
	$message = str_replace( '{fullname}', $fullname, $message );
	$message = str_replace( '{commission_id}', $commission->id, $message );
	$message = str_replace( '{item_price}', $item_price, $message );
	$message = str_replace( '{item_tax}', $item_tax, $message );

	return $message;
}


/**
 * Email Sale Alert
 *
 * Email an alert about the sale to the user receiving a commission
 *
 * @since       1.1.0
 * @return      void
 */
function eddc_email_alert( $user_id, $commission_amount, $rate, $download_id, $commission_id ) {
	if ( edd_get_option( 'edd_commissions_disable_sale_alerts', false ) ) {
		return;
	}

	if ( get_user_meta( $user_id, 'eddc_disable_user_sale_alerts', true ) ) {
		return;
	}

	/* send an email alert of the sale */
	$user    = get_userdata( $user_id );
	$email   = $user->user_email; // set address here
	$subject = edd_get_option( 'edd_commissions_email_subject', __( 'New Sale!', 'eddc' ) );
	$message = edd_get_option( 'edd_commissions_email_message', eddc_get_email_default_body() );

	// Parse template tags
	$message = eddc_parse_template_tags( $message, $download_id, $commission_id, $commission_amount, $rate );
	$message = apply_filters( 'eddc_sale_alert_email', $message, $user_id, $commission_amount, $rate, $download_id, $commission_id );

	if ( class_exists( 'EDD_Emails' ) ) {
		EDD()->emails->__set( 'heading', $subject );
		EDD()->emails->send( $email, $subject, $message );
	} else {
		$from_name = apply_filters( 'eddc_email_from_name', $from_name, $user_id, $commission_amount, $rate, $download_id );

		$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
		$from_email = apply_filters( 'eddc_email_from_email', $from_email, $user_id, $commission_amount, $rate, $download_id );

		$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";

		wp_mail( $email, $subject, $message, $headers );
	}
}
add_action( 'eddc_insert_commission', 'eddc_email_alert', 10, 5 );
