<?php
/**
 * PayPal Adaptive Payments integration
 *
 * This file holds all functions that take care of instant payouts using PayPal Adaptive Payments
 *
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Setup PayPal receivers when a purchase is made
 *
 * @since 2.7
 * @param $receivers string The default receivers and their percentages as defined in the Payment Gateway settings
 * @param $payment_id int The payment ID of the purchase
 * @return receivers $string The modified receivers string
 */
function eddc_paypal_adaptive_autopay( $receivers, $payment_id ) {
	if ( ! edd_get_option( 'edd_commissions_autopay_pa' ) ) {
		return $receivers;
	}

	$paypal_adaptive_receivers = array();

	$commissions_calculated = eddc_calculate_payment_commissions( $payment_id );

	$payment = new EDD_Payment( $payment_id );

	$epap_receivers = edd_get_option( 'epap_receivers' );
	$total_cost     = $payment->total;

	if ( ! is_array( $epap_receivers ) ) {
		$epap_receivers = explode( '|', $epap_receivers );
	}

	$store_email  = trim( $epap_receivers[0] );
	$store_amount = 0;

	$counter = 0;

	// Loop through each commission and add all commission amounts together if they are for the same recipient
	foreach ( $commissions_calculated as $commission_calculated ) {
		$default_commission_calculated = array(
			'recipient'           => 0,
			'commission_amount'   => 0,
			'rate'                => 0,
			'download_id'         => 0,
			'payment_id'          => 0,
			'currency'            => NULL,
			'has_variable_prices' => NULL,
			'price_id'            => NULL,
			'variation'           => NULL,
			'cart_item'           => NULL
		);

		$commission_calculated = wp_parse_args(	$commission_calculated, $default_commission_calculated );

		// Get the WordPress user attached to the recipient ID
		$wp_user         = get_user_by( 'id', $commission_calculated['recipient'] );
		$recipient_email = get_user_meta( $wp_user->ID, 'eddc_user_paypal', true );

		if ( empty( $recipient_email ) ) {
			$recipient_email = $wp_user->user_email;
		}

		// If this recipient is also the store, they are special. Store their amount so we can use it further down.
		if ( $recipient_email == $store_email ) {
			$store_amount = $store_amount + $commission_calculated['commission_amount'];
		}

		// If this recipient already has a commission listed from this payment,
		if ( array_key_exists( $recipient_email, $paypal_adaptive_receivers ) ) {
			// Add the amount for this commission to the previous commission total for this recipient
			$paypal_adaptive_receivers[$recipient_email] += $commission_calculated['commission_amount'];
		} else {
			$paypal_adaptive_receivers[$recipient_email] = $commission_calculated['commission_amount'];
		}
	}

	$total_amount_used = 0;

	// Now, lets figure out how much of the amount has been used by commissions and how much is left over
	foreach ( $paypal_adaptive_receivers as $recipient => $commission_amount ) {
		$total_amount_used = $total_amount_used + $commission_amount;
	}

	// If the amount leftover is greater than 0, add it to the store's amount
	if ( ( $total_cost - $total_amount_used ) > 0 ) {
		$store_amount = $store_amount + ( $total_cost - $total_amount_used );

		// Lets also move the store to the start of the array so they become the primary reciever
		$paypal_adaptive_receivers = array($store_email => $store_amount ) + $paypal_adaptive_receivers;
	} elseif ( 'chained' === edd_get_option( 'epap_payment_type', 'chained' ) && ( $total_cost - $total_amount_used ) == 0 ) {
		// If the amount leftover is exactly 0 and we are in chained mode, set the store's amount to be 0
		$store_amount = $store_amount + 0;

		// Lets also move the store to the start of the array so they become the primary reciever
		$paypal_adaptive_receivers = array( $store_email => $store_amount ) + $paypal_adaptive_receivers;
	}

	// Rebuild the final PayPal Adaptive receivers string
	foreach ( $paypal_adaptive_receivers as $recipient_email => $commission_amount ) {
		if ( $counter === 0) {
			$return = $recipient_email . "|" . ( $commission_amount == 0 ? $commission_amount : 100 / ( $total_cost / $commission_amount ) );
		} else {
			$return = $return . "\n" . $recipient_email . "|" . ( $commission_amount == 0 ? $commission_amount : 100 / ( $total_cost / $commission_amount ) );
		}

		$counter++;
	}

	return $return;
}
add_filter( 'epap_adaptive_receivers', 'eddc_paypal_adaptive_autopay', 8, 2 );


/**
 * Mark commissions as paid immediately since they are paid at the time of purchase
 *
 * @since       2.7
 * @return      void
 */
function eddc_override_commission_status( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {
	if ( ! edd_get_option( 'edd_commissions_autopay_pa' ) || 'paypal_adaptive_payments' != edd_get_payment_gateway( $payment_id ) ) {
		return;
	}

	eddc_set_commission_status( $commission_id, 'paid' );
}
add_action( 'eddc_insert_commission', 'eddc_override_commission_status', 8, 6 );
