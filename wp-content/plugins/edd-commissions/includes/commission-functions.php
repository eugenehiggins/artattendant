<?php
/**
 * Commissions Functions.
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Retrieves an instance of EDD_Commission for a specified ID.
 *
 * @since       2.7
 * @param       mixed int|EDD_Commission|WP_Post $commission Commission ID, EDD_Commission object or WP_Post object.
 * @return      mixed false|object EDD_Commission if a valid commission ID, false otherwise.
 */
function eddc_get_commission( $commission = null ) {
	if ( is_a( $commission, 'WP_Post' ) || is_a( $commission, 'EDD_Commission' ) ) {
		$commission_id = $commission->ID;
	} else {
		$commission_id = $commission;
	}

	if ( empty( $commission_id ) ) {
		return false;
	}

	$cache_key  = md5( 'eddc_commission' . $commission_id );
	$commission = wp_cache_get( $cache_key, 'commissions' );

	if ( false === $commission ) {
		$commission = new EDD_Commission( $commission_id );

		if ( empty( $commission->ID ) || ( (int) $commission->ID !== (int) $commission_id ) ) {
			return false;
		} else {
			wp_cache_set( $cache_key, $commission, 'commissions' );
		}
	}

	return $commission;
}


/**
 * Helper function used by anything needing to calculate commissions for a payment ID.
 *
 * @since       3.3
 * @param       integer $payment_id The ID of the Payment for which we need to calculate commissions.
 * @return      array of commissions that would need to be paid based on the payment id.
 */
function eddc_calculate_payment_commissions( $payment_id ) {
	// If we were passed a numeric value as the payment id (which it should be)
	if ( ! is_object( $payment_id ) && is_numeric( $payment_id ) ) {
		$payment = new EDD_Payment( $payment_id );
	} elseif( is_a( $payment_id, 'EDD_Payment' ) ) {
		$payment = $payment_id;
	} else {
		return false;
	}

	$commissions_calculated = array();

	$calc_base = edd_get_option( 'edd_commissions_calc_base', 'subtotal' );

	// loop through each purchased download and calculate commissions, if needed
	foreach ( $payment->cart_details as $cart_index => $cart_item ) {
		$download_id         = absint( $cart_item['id'] );
		$commissions_enabled = get_post_meta( $download_id, '_edd_commisions_enabled', true );
		$commission_settings = get_post_meta( $download_id, '_edd_commission_settings', true );

		if ( ! $commissions_enabled ) {
			continue;
		}

		if ( empty( $commission_settings ) ) {
			continue;
		}

		$should_record_commissions = apply_filters( 'eddc_should_record_download_commissions', true, $download_id, $payment_id );
		if ( false === $should_record_commissions ) {
			continue;
		}

		$recipients = eddc_get_recipients( $download_id );

		// Do not allow someone to purchase their own item and make a commission unless they are a shop accountant.
		$allow_self_commissions = apply_filters( 'eddc_should_allow_self_commissions', user_can( $payment->user_id, 'edit_shop_payments' ), $download_id, $payment_id );
		if ( false === $allow_self_commissions ) {

			$download = new EDD_Download( $download_id );

			foreach ( $recipients as $key => $user_id ) {
				if ( (int) $user_id !== (int) $payment->user_id ) {
					continue;
				}

				unset( $recipients[ $key ] );
				$payment->add_note( sprintf( __( 'Commission for %s skipped because %s made purchase and self commissions are disabled.', 'eddc' ), $download->get_name(), get_userdata( $user_id )->display_name ) );
			}

		}

		if ( empty( $recipients ) ) {
			continue;
		}

		switch ( $calc_base ) {
			case 'subtotal':
				$price = $cart_item['subtotal'];
				break;
			case 'total_pre_tax':
				$price = $cart_item['price'] - $cart_item['tax'];
				break;
			default:
				$price = $cart_item['price'];
				break;
		}

		if ( 'subtotal' != $calc_base && ! empty( $cart_item['fees'] ) ) {
			foreach ( $cart_item['fees'] as $fee ) {
				$fee_amt = (float) $fee['amount'];
				if ( $fee_amt > 0 ) {
					continue;
				}

				$price = $price + $fee_amt;
			}
		}

		// If we need to award a commission, and the price is greater than zero
		if ( ! floatval( $price ) > '0' && edd_get_option( 'edd_commissions_allow_zero_value', 'yes' ) == 'no' ) {
			continue;
		}

		$type = eddc_get_commission_type( $download_id );

		// but if we have price variations, then we need to get the name of the variation
		$has_variable_prices = edd_has_variable_prices( $download_id );

		if ( $has_variable_prices ) {
			$price_id  = edd_get_cart_item_price_id ( $cart_item );
			$variation = edd_get_price_option_name( $download_id, $price_id );
		}

		$recipient_counter = 0;

		// Calculate a commission for each user
		foreach ( $recipients as $recipient ) {
			// If the user did not enter anything for the recipient field, skip this commission
			if ( empty( $recipient ) ) {
				continue;
			}

			$rate = eddc_get_recipient_rate( $download_id, $recipient );

			$args = apply_filters( 'eddc_calc_commission_amount_args', array(
				'price'             => $price,
				'rate'              => $rate,
				'type'              => $type,
				'download_id'       => $download_id,
				'cart_item'         => $cart_item,
				'recipient'         => $recipient,
				'recipient_counter' => $recipient_counter,
				'payment_id'        => $payment->ID
			) );

			$commission_amount = eddc_calc_commission_amount( $args ); // calculate the commission amount to award

			$commissions_calculated[] = array(
				'recipient'           => $recipient,
				'commission_amount'   => $commission_amount,
				'rate'                => $rate,
				'download_id'         => $download_id,
				'payment_id'          => $payment->ID,
				'currency'            => $payment->currency,
				'has_variable_prices' => $has_variable_prices,
				'price_id'            => isset( $price_id ) ? $price_id : NULL,
				'variation'           => isset( $variation ) ? $variation : NULL,
				'cart_item'           => $cart_item,
				'cart_index'          => $cart_index,
			);

			$recipient_counter++;
		}
	}

	return apply_filters( 'eddc_commissions_calculated', $commissions_calculated, $payment );
}


/**
 * Retrieve the paid status of a commissions
 *
 * @since       2.8
 * @param       int $commission_id The post ID for this commission
 * @return      string
 */
function eddc_get_commission_status( $commission_id = 0 ) {
	$commission = new EDD_Commission( $commission_id );
	return apply_filters( 'eddc_get_commission_status', $commission->status, $commission_id );
}


/**
 * Sets the status for a commission record
 *
 * @since       2.8
 * @param       int $commission_id The ID for this commission
 * @param       string $new_status The new status for the commission
 * @return      void
 */
function eddc_set_commission_status( $commission_id = 0, $new_status = 'unpaid' ) {
	$commission = new EDD_Commission( $commission_id );
	$old_status = $commission->status;

	do_action( 'eddc_pre_set_commission_status', $commission_id, $new_status, $old_status );

	$commission->status = $new_status;
	if ( 'paid' === $new_status ) {
		$commission->date_paid = current_time( 'mysql' );
	}
	$commission->save();

	do_action( 'eddc_set_commission_status', $commission_id, $new_status, $old_status );
}


/**
 * Get if a commission was on a renewal
 *
 * @since       3.2
 * @param       integer $commission_id Commission ID
 * @return      bool If the commission was for a renewal or not
 */
function eddc_commission_is_renewal( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );
	$is_renewal = $commission->get_meta( 'is_renewal' );

	return $is_renewal;
}


/**
 * Get an array containing the user id's entered in the "Users" field in the Commissions metabox.
 *
 * @since       3.2.11
 * @param       int $download_id The id of the download for which we want the recipients.
 * @return      array An array containing the user ids of the recipients.
 */
function eddc_get_recipients( $download_id = 0 ) {
	$settings = get_post_meta( $download_id, '_edd_commission_settings', true );

	// If the information for commissions was not saved or this happens to be for a post with commissions currently disabled
	if ( !isset( $settings['user_id'] ) ){
		return array();
	}

	$recipients = array_map( 'intval', explode( ',', $settings['user_id'] ) );
	return (array) apply_filters( 'eddc_get_recipients', $recipients, $download_id );
}


/**
 * Check which position a recipient is in for a download's commission.
 *
 * @since       3.2.11
 * @param       int $user_id The user id of the commission recipient (aka vendor).
 * @param       int $download_id The download id being purchased
 * @return      int $position The array position that the recipient is in.
 */
function eddc_get_recipient_position( $recipient_id, $download_id ) {
	$recipients = eddc_get_recipients( $download_id );
	return array_search( $recipient_id, $recipients );
}


/**
 *
 * Retrieves the commission rate for a product and user
 *
 * If $download_id is empty, the default rate from the user account is retrieved.
 * If no default rate is set on the user account, the global default is used.
 *
 * This function requires very strict typecasting to ensure the proper rates are used at all times.
 *
 * 0 is a permitted rate so we cannot use empty(). We always use NULL to check for non-existent values.
 *
 * @param       $download_id INT The ID of the download product to retrieve the commission rate for
 * @param       $user_id INT The user ID to retrieve commission rate for
 * @return      $rate INT|FLOAT The commission rate
 */
function eddc_get_recipient_rate( $download_id = 0, $user_id = 0 ) {
	$rate = null;

	// Check for a rate specified on a specific product
	if ( ! empty( $download_id ) ) {
		$settings   = get_post_meta( $download_id, '_edd_commission_settings', true );
		$rates      = isset( $settings['amount'] ) ? array_map( 'trim', explode( ',', $settings['amount'] ) ) : array();
		$recipients = array_map( 'trim', explode( ',', $settings['user_id'] ) );
		$rate_key   = array_search( $user_id, $recipients );

		if ( isset( $rates[ $rate_key ] ) ) {
			$rate = $rates[ $rate_key ];
		}
	}

	// Check for a user specific global rate
	if ( ! empty( $user_id ) && ( null === $rate || '' === $rate ) ) {
		$rate = get_user_meta( $user_id, 'eddc_user_rate', true );

		if ( '' === $rate ) {
			$rate = null;
		}
	}

	// Check for an overall global rate
	if ( null === $rate && eddc_get_default_rate() ) {
		$rate = eddc_get_default_rate();
	}

	// Set rate to 0 if no rate was found
	if ( null === $rate || '' === $rate ) {
		$rate = 0;
	}

	return apply_filters( 'eddc_get_recipient_rate', (float) $rate, $download_id, $user_id );
}


/**
 * Retrieve the type of a commission for a download
 *
 * @param       int $download_id The download ID
 * @return      string The type of the commission
 */
function eddc_get_commission_type( $download_id = 0 ) {
	$settings = get_post_meta( $download_id, '_edd_commission_settings', true );
	$type     = isset( $settings['type'] ) ? $settings['type'] : 'percentage';
	return apply_filters( 'eddc_get_commission_type', $type, $download_id );
}


/**
 * Get a cart item ID
 */
function eddc_get_cart_item_id( $cart_details, $download_id ) {
	foreach( (array) $cart_details as $position => $item ) {
		if ( $item['id'] == $download_id ) {
			return $position;
		}
	}

	return null;
}


/**
 * Retrieve the Download IDs a user receives commissions for
 *
 * @since       2.1
 * @param       int $user_id The ID of the user to look up
 * @return      array The downloads associated with a given user
 */
function eddc_get_download_ids_of_user( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	global $wpdb;

	$downloads = $wpdb->get_results( "SELECT post_id, meta_value AS settings FROM $wpdb->postmeta WHERE meta_key='_edd_commission_settings' AND meta_value LIKE '%{$user_id}%';" );

	foreach ( $downloads as $key => $download ) {

		// Check if commissions are enabled
		$commissions_enabled = get_post_meta( $download->post_id, '_edd_commisions_enabled', true );

		$settings = maybe_unserialize( $download->settings );

		// If no user id exists here, something went wrong with the saving of this commission and the product needs to be re-saved.
		if ( ! isset( $settings['user_id'] ) ) {

			unset( $downloads[ $key ] );

		}elseif( empty( $commissions_enabled ) ) {

			// If commissions are not enabled for this product (they likely were on at one point but are now disabled)
			unset( $downloads[ $key ] );

		}else{

			$user_ids = explode( ',', $settings['user_id'] );

			if ( ! in_array( $user_id, $user_ids ) ) {
				unset( $downloads[ $key ] );
			}
		}
	}

	return wp_list_pluck( $downloads, 'post_id' );
}


/**
 * Retrieve the amount of a commission
 *
 * @param       array $args Arguments to pass to the query
 * @return      string The amount of the commission
 */
function eddc_calc_commission_amount( $args ) {
	$defaults = array(
		'type' => 'percentage'
	);

	$args = wp_parse_args( $args, $defaults );

	if ( 'flat' == $args['type'] ) {
		return $args['rate'];
	}

	if ( ! isset( $args['price'] ) || $args['price'] == false ) {
		$args['price'] = '0.00';
	}

	if ( $args['rate'] >= 1 ) {
		$amount = $args['price'] * ( $args['rate'] / 100 ); // rate format = 10 for 10%
	} else {
		$amount = $args['price'] * $args['rate']; // rate format set as 0.10 for 10%
	}

	return apply_filters( 'eddc_calc_commission_amount', $amount, $args );
}


/**
 * Check if a user has commissions
 *
 * @param       int $user_id The user to look up
 * @return      bool
 */
function eddc_user_has_commissions( $user_id = false ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$return = false;

	$args = array(
		'number' => 1,
		'user_id' => $user_id,
	);

	$commissions = edd_commissions()->commissions_db->get_commissions( $args );

	if ( ! empty( $commissions ) ) {
		$return = true;
	}

	return apply_filters( 'eddc_user_has_commissions', $return, $user_id );
}


/**
 * Retrieve an array of commissions
 *
 * @param       array $args Arguments to pass to the query
 * @return      array The array of commissions
 */
function eddc_get_commissions( $args = array() ) {
	$defaults = array(
		'user_id'    => false,
		'number'     => 30,
		'paged'      => 1,
		'query_args' => array(),
		'status'     => false,
		'payment_id' => false,
	);

	$args        = wp_parse_args( $args, $defaults );
	$commissions = edd_commissions()->commissions_db->get_commissions( $args );

	if ( $commissions ) {
		return $commissions;
	}

	return false; // no commissions
}


/**
 * Retrieve an array of unpaid commissions
 *
 * @param       array $args Arguments to pass to the query
 * @return      array The array of commissions
 */
function eddc_get_unpaid_commissions( $args = array() ) {
	$defaults = array(
		'user_id'    => false,
		'number'     => 30,
		'paged'      => 1,
		'query_args' => array(),
	);

	$args = wp_parse_args( $args, $defaults );
	$args['status'] = 'unpaid';

	$commissions = eddc_get_commissions( $args );

	if ( $commissions ) {
		return $commissions;
	}

	return false; // no commissions
}


/**
 * Retrieve an array of paid commissions
 *
 * @param       array $args Arguments to pass to the query
 * @return      array The array of commissions
 */
function eddc_get_paid_commissions( $args = array() ) {
	$defaults = array(
		'user_id'    => false,
		'number'     => 30,
		'paged'      => 1,
		'query_args' => array(),
	);

	$args = wp_parse_args( $args, $defaults );
	$args['status'] = 'paid';

	$commissions = eddc_get_commissions( $args );

	if ( $commissions ) {
		return $commissions;
	}

	return false; // no commissions
}


/**
 * Retrieve an array of revoked commissions
 *
 * @param       array $args Arguments to pass to the query
 * @return      array The array of commissions
 */
function eddc_get_revoked_commissions( $args = array() ) {

	$defaults = array(
		'user_id'    => false,
		'number'     => 30,
		'paged'      => 1,
		'query_args' => array(),
	);

	$args = wp_parse_args( $args, $defaults );
	$args['status'] = 'revoked';

	$commissions = eddc_get_commissions( $args );

	if ( $commissions ) {
		return $commissions;
	}

	return false; // no commissions

}


/**
 * Get a count of user commissions
 *
 * @param       int $user_id The ID of the user to look up
 * @param       string $status The status to look up
 * @return      int The number of commissions for the user
 */
function eddc_count_user_commissions( $user_id = false, $status = 'unpaid' ) {
	$args = array(
		'status'  => $status,
		'user_id' => ! empty( $user_id ) ? $user_id : false,
		'number'  => - 1,
	);

	$count = edd_commissions()->commissions_db->count( $args );
	if ( ! empty( $count ) ) {
		return $count;
	}

	return false; // no commissions
}

/**
 * Get the total unpaid commissions
 *
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of unpaid commissions
 */
function eddc_get_unpaid_totals( $user_id = 0 ) {
	$total = edd_commissions()->commissions_db->sum( 'amount', array( 'status' => 'unpaid', 'user_id' => $user_id, 'number' => -1 ) );

	return edd_sanitize_amount( $total );
}

/**
 * Get the total paid commissions
 *
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of paid commissions
 */
function eddc_get_paid_totals( $user_id = 0 ) {
	$total = edd_commissions()->commissions_db->sum( 'amount', array( 'status' => 'paid', 'user_id' => $user_id, 'number' => -1 ) );

	return edd_sanitize_amount( $total );
}


/**
 * Get the total revoked commissions
 *
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of revoked commissions
 */
function eddc_get_revoked_totals( $user_id = 0 ) {
	$total = edd_commissions()->commissions_db->sum( 'amount', array( 'status' => 'revoked', 'user_id' => $user_id, 'number' => -1 ) );

	return edd_sanitize_amount( $total );
}


/**
 * Get the total for a range of commissions
 *
 * @return      string The total of specified commissions
 */
function edd_get_commissions_by_date( $day = null, $month = null, $year = null, $hour = null, $user = 0  ) {
	$commission_args = array(
		'number' => -1,
		'year'   => $year,
		'month'  => $month,
		'status' => array( 'paid', 'unpaid' ),
	);

	if ( ! empty( $day ) ) {
		$commission_args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$commission_args['hour'] = $hour;
	}

	if ( ! empty( $user ) ) {
		$commission_args['user_id'] = absint( $user );
	}

	$commission_args = apply_filters( 'edd_get_commissions_by_date', $commission_args, $day, $month, $year, $user );

	$total = edd_commissions()->commissions_db->sum( 'amount', $commission_args );
	return edd_sanitize_amount( $total );
}


/**
 * Gets the default commission rate
 *
 * @since       2.1
 * @return      float
 */
function eddc_get_default_rate() {
	global $edd_options;

	$rate = isset( $edd_options['edd_commissions_default_rate'] ) ? $edd_options['edd_commissions_default_rate'] : false;

	return apply_filters( 'eddc_default_rate', $rate );
}

/**
 * This will take a rate and a commission type and format it correctly for output.
 * For example, if the rate is 5 and the commission type is "percentage", it will return "5%" as a string.
 * If the rate is 5 and the commission type is "flat", it will return "$5" as a string.
 *
 *
 * The status for commission records used to be stored in postmeta, now it's stored in a taxonomy
 *
 * @since       3.3.2
 * @param       int $unformatted_rate This is the number representing the rate.
 * @param       string $commission_type This is the type of commission.
 * @return      string $formatted_rate This is the rate formatted for output.
 */
function eddc_format_rate( $unformatted_rate, $commission_type ){

	// If the commission type is "percentage"
	if ( 'percentage' == $commission_type ) {

		// Format the rate to have the percentage sign after it.
		$formatted_rate = $unformatted_rate . '%';

	} else {

		// If the rate is anything else, format it as if it were a flat rate, or "dollar" amount. We add the currency symbol before it. For example, "$5".
		$formatted_rate = edd_currency_filter( edd_sanitize_amount( $unformatted_rate ) );

	}

	// Filter the formatted rate so it can be modified if needed
	return apply_filters( 'eddc_format_rate', $formatted_rate, $unformatted_rate, $commission_type );
}
