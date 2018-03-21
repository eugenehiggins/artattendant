<?php
/**
 * Commissions Actions.
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Record Commissions
 *
 * @since       1.0
 * @param       int $payment_id The ID of a given payment
 * @param       string $new_status The new status of the payment object
 * @param       string $old_status The old status of the payment object
 * @return      void
 */
function eddc_record_commission( $payment_id, $new_status, $old_status ) {
	// Check if the payment was already set to complete
	if ( $old_status == 'publish' || $old_status == 'complete' ) {
		return; // Make sure that payments are only completed once
	}

	// Make sure the commission is only recorded when new status is complete
	$allowed_new_statuses = apply_filters( 'eddc_allowed_complete_statuses', array( 'publish', 'complete' ) );
	if ( ! in_array( $new_status, $allowed_new_statuses ) ) {
		return;
	}

	// If we were passed a numeric value as the payment id (which it should be)
	if ( ! is_object( $payment_id ) && is_numeric( $payment_id ) ) {
		$payment = new EDD_Payment( $payment_id );
	} else {
		// In case we happened to be passed an EDD_Payment object as the $payment_id, reset the $payment_id variable to be the int payment ID.
		$payment    = $payment_id;
		$payment_id = $payment->ID;
	}

	if ( $payment->gateway == 'manual_purchases' && ! isset( $_POST['commission'] ) ) {
		return; // do not record commission on manual payments unless specified
	}

	if ( $payment->completed_date ) {
		return;
	}

	$commissions_calculated = eddc_calculate_payment_commissions( $payment_id );

	// If there are no commission recipients set up, trigger an action and return.
	if ( empty( $commissions_calculated ) ) {
		do_action( 'eddc_no_commission_recipients', $payment_id );
		return;
	}

	$user_info = $payment->user_info;

	// loop through each calculated commission and award commissions
	foreach ( $commissions_calculated as $commission_calculated ) {

		// Bail if the commission amount is $0 and the zero-value setting is disabled
		if ( (float) $commission_calculated['commission_amount'] === (float) 0 && edd_get_option( 'edd_commissions_allow_zero_value', 'yes' ) == 'no' ) {
			continue;
		}

		$default_commission_calculated = array(
			'recipient'             => 0,
			'commission_amount'     => 0,
			'rate'                  => 0.00,
			'download_id'           => 0,
			'payment_id'            => 0,
			'currency'              => NULL,
			'has_variable_prices'   => NULL,
			'price_id'              => NULL,
			'variation'             => NULL,
			'cart_item'             => NULL,
			'cart_index'            => 0,
		);

		$commission_calculated = wp_parse_args(	$commission_calculated, $default_commission_calculated );

		$commission_calculated['download_id'] = absint( $commission_calculated['download_id'] );

		// set a flag so downloads with commissions awarded are easy to query
		/** TODO: We we won't need this with the new table, since we can query the unique download IDs with commission records */
		update_post_meta( $commission_calculated['download_id'], '_edd_has_commission', true );

		$commission = new EDD_Commission;
		$commission->status      = 'unpaid';
		$commission->cart_index  = $commission_calculated['cart_index'];
		$commission->user_id     = $commission_calculated['recipient'];
		$commission->rate        = $commission_calculated['rate'];
		$commission->amount      = $commission_calculated['commission_amount'];
		$commission->currency    = $commission_calculated['currency'];
		$commission->download_id = (int) $commission_calculated['download_id'];
		$commission->payment_id  = $payment_id;
		$commission->type        = eddc_get_commission_type( $commission_calculated['download_id'] );

		// If we are dealing with a variation, then save variation info
		if ( $commission_calculated['has_variable_prices'] && ! empty( $commission_calculated['variation'] ) ) {
			$commission->price_id = $commission_calculated['price_id'];
		}

		// If it's a renewal, save that detail
		if ( ! empty( $commission_calculated['cart_item']['item_number']['options']['is_renewal'] ) ) {
			$commission->is_renewal = true;
		}

		$commission->save();

		$args = array(
			'user_id'  => $commission->user_id,
			'rate'     => $commission->rate,
			'amount'   => $commission->amount,
			'currency' => $commission->currency,
			'type'     => $commission->type,
		);

		$commission_info = apply_filters( 'edd_commission_info', $args, $commission->ID, $commission->payment_ID, $commission->download_ID );
		$items_changed   = false;
		foreach ( $commission_info as $key => $value ) {
			if ( $value === $args[ $key ] ) {
				continue;
			}

			$commission->$key = $value;
			$items_changed    = true;
		}

		if ( $items_changed ) {
			$commission->save();
		}

		do_action( 'eddc_insert_commission', $commission_calculated['recipient'], $commission_calculated['commission_amount'], $commission_calculated['rate'], $commission_calculated['download_id'], $commission->ID, $payment_id );
	}
}
add_action( 'edd_update_payment_status', 'eddc_record_commission', 10, 3 );


/**
 * Generate a payout CSV file
 *
 * @param       array $data The data to pass to the query
 * @return      void
 */
function eddc_generate_payout_file( $data ) {
	if ( wp_verify_nonce( $data['eddc-payout-nonce'], 'eddc-payout-nonce' ) ) {

		$from = ! empty( $data['from'] ) ? sanitize_text_field( $data['from'] ) : date( 'm/d/Y', strtotime( '-1 month' ) );
		$to   = ! empty( $data['to'] )   ? sanitize_text_field( $data['to'] )   : date( 'm/d/Y' );

		$from = explode( '/', $from );
		$to   = explode( '/', $to );

		$args = array(
			'number'         => -1,
			'query_args'     => array(
				'date_query' => array(
					'after'       => array(
						'year'    => $from[2],
						'month'   => $from[0],
						'day'     => $from[1],
					),
					'before'      => array(
						'year'    => $to[2],
						'month'   => $to[0],
						'day'     => $to[1],
					),
					'inclusive' => true
				)
			)
		);

		$commissions = eddc_get_unpaid_commissions( $args );

		if ( $commissions ) {

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=edd-commission-payout-' . date( 'm-d-Y' ) . '.csv' );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			$payouts = array();

			foreach ( $commissions as $commission ) {

				$user          = get_userdata( $commission->user_id );
				$custom_paypal = get_user_meta( $commission->user_id, 'eddc_user_paypal', true );
				$email         = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

				if ( array_key_exists( $email, $payouts ) ) {
					$payouts[$email]['amount'] += $commission->amount;
				} else {
					$payouts[$email] = array(
						'amount'     => $commission->amount,
						'currency'   => $commission->currency
					);
				}

				eddc_set_commission_status( $commission->ID, 'paid' );

			}

			if ( $payouts ) {
				foreach ( $payouts as $key => $payout ) {

					echo $key . ",";
					echo edd_sanitize_amount( number_format( $payout['amount'], 2 ) ) . ",";
					echo $payout['currency'];

					echo "\r\n";

				}

			}

		} else {
			wp_die( __( 'No commissions to be paid', 'eddc' ), __( 'Error' ) );
		}
		die();
	}
}
add_action( 'edd_generate_payouts', 'eddc_generate_payout_file' );


/**
 * Generate an export file for a user
 *
 * @param       array $data Data to pass to the query
 * @return      void
 */
function eddc_generate_user_export_file( $data ) {
	$user_id = ! empty( $data['user_id'] ) ? intval( $data['user_id'] ) : get_current_user_id();

	if ( ( empty( $user_id ) || ! eddc_user_has_commissions( $user_id ) ) ) {
		return;
	}

	include_once EDDC_PLUGIN_DIR . 'includes/classes/class-commissions-export.php';
	$export = new EDD_Commissions_Export();
	$export->user_id = $user_id;
	$export->year    = $data['year'];
	$export->month   = $data['month'];
	$export->export();
}
add_action( 'edd_generate_commission_export', 'eddc_generate_user_export_file' );


/**
 * Store a payment note about this commission
 *
 * This makes it really easy to find commissions recorded for a specific payment.
 * Especially useful for when payments are refunded
 *
 * @since       2.0
 * @return      void
 */
function eddc_record_commission_note( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {
	$note = sprintf(
		__( 'Commission of %s recorded for %s &ndash; <a href="%s">View</a>', 'eddc' ),
		edd_currency_filter( edd_format_amount( $commission_amount ) ),
		get_userdata( $recipient )->display_name,
		admin_url( 'edit.php?post_type=download&page=edd-commissions&payment=' . $payment_id )
	);

	edd_insert_payment_note( $payment_id, $note );
}
add_action( 'eddc_insert_commission', 'eddc_record_commission_note', 10, 6 );


/**
 * Revoke an unpaid commission when the payment it's associated with is refunded.
 *
 * @since       3.3
 * @param       $payment EDD_Payment object.
 * @return      void
 */
function eddc_revoke_on_refund( $payment ) {
	$revoke_on_refund = edd_get_option( 'edd_commissions_revoke_on_refund', false );

	if ( false === $revoke_on_refund ) {
		return;
	}

	$commissions = eddc_get_commissions( array(
		'payment_id' => $payment->ID,
		'status'     => 'unpaid',
	) );

	if ( ! empty( $commissions ) ) {
		foreach ( $commissions as $commission ) {

			$commission->set_status( 'revoked' );

			$note  = sprintf(
				__( 'Commission revoked for %s due to refunded payment &ndash; <a href="%s">View</a>', 'eddc' ),
				get_userdata( $commission->user_id )->display_name,
				admin_url( 'edit.php?post_type=download&page=edd-commissions&payment=' . $payment->ID )
			);

			$payment->add_note( $note );
		}
	}
}
add_action( 'edd_post_refund_payment', 'eddc_revoke_on_refund', 10, 1 );
