<?php
/**
 * Commissions Filters.
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Listen for calls to get_post_meta and see if we need to filter them.
 *
 * @since  3.4
 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  bool   $single      If the person wants the single value or an array of the value
 * @return mixed               The value to return
 */
function eddc_get_meta_backcompat( $value, $object_id, $meta_key, $single ) {
	global $wpdb;

	$meta_keys = apply_filters( 'eddc_post_meta_backwards_compat_keys', array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id', '_edd_all_access_info' ) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $value;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'eddc_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) );
	$commission      = new EDD_Commission( $object_id );

	if ( empty( $commission->id ) ) {
		// We didn't find a commission record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT commission_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $object_id" );
		if ( ! empty( $object_id ) ) {
			$commission = new EDD_Commission( $object_id );
		} else {
			return $value;
		}
	}

	switch( $meta_key ) {

		case '_edd_commission_info':

			$value = array(
				'user_id'  => $commission->user_id,
				'rate'     => $commission->rate,
				'amount'   => $commission->amount,
				'currency' => $commission->currency,
				'type'     => $commission->type,
			);

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_info postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_commission_status' :

			$value = $commission->status;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _commission_status postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;


		case '_edd_commission_payment_id':

			$value = $commission->payment_id;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_download_id':

			$value = $commission->download_id;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_edd_all_access_info':
			$commission = new EDD_Commission( $object_id );
			$value      = $commission->get_meta( '_edd_all_access_info' );
			break;

		default:
			// Developers can hook in here with add_action( 'eddc_get_post_meta_backwards_compat-meta_key... in order to
			// Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Commission::get_meta
			$value = apply_filters( 'eddc_get_post_meta_backwards_compat-' . $meta_key, $value, $object_id );
			break;
	}

	return array( $value );

}
add_filter( 'get_post_metadata', 'eddc_get_meta_backcompat', 99, 4 );

/**
 * Listen for calls to add_post_meta and see if we need to filter them.
 *
 * @since  3.4
 * @param mixed   $check       Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  mixed  $meta_value  The value get_post_meta would return if we don't filter.
 * @param  bool   $unique      Determines if the meta key should be unique or allow multiple entries for the meta_key
 * @return mixed               Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
 */
function eddc_add_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $unique ) {
	global $wpdb;

	$meta_keys = apply_filters( 'eddc_post_meta_backwards_compat_keys', array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id', '_edd_all_access_info' ) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $check;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'eddc_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) );
	$commission      = new EDD_Commission( $object_id );

	if ( empty( $commission->id ) ) {
		// We didn't find a commission record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT commission_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $object_id" );
		if ( ! empty( $object_id ) ) {
			$commission = new EDD_Commission( $object_id );
		} else {
			return $check;
		}
	}

	switch( $meta_key ) {

		case '_edd_commission_info':

			// Since the old commission data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Commission data is no longer stored in post meta. Please use the new custom database tables to insert a commission record.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;

		case '_commission_status' :

			// Status should only be able to be a single entry, and it's not meta, save it on the commission record;
			$commission->status = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _commission_status postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;


		case '_edd_commission_payment_id':

			$commission->payment_id = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;

		case '_download_id':

			$commission->download_id = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;

		case '_edd_all_access_info':
			$check = edd_commissions()->commission_meta_db->add_meta( $commission->id, $meta_key, $meta_value, $unique );
			break;

		default:
			// Developers can hook in here with add_action( 'eddc_add_post_meta_backwards_compat-meta_key... in order to
			// Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Commission::get_meta
			$check = apply_filters( 'eddc_add_post_meta_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $unique );
			break;
	}

	return $check;

}
add_filter( 'add_post_metadata', 'eddc_add_meta_backcompat', 99, 5 );

/**
 * Listen for calls to update_post_meta and see if we need to filter them.
 *
 * @since  3.4
 * @param mixed   $check       Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  mixed  $meta_value  The value get_post_meta would return if we don't filter.
 * @param  mixed  $prev_value  The previous value of the meta
 * @return mixed               Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
 */
function eddc_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
	global $wpdb;

	$meta_keys = apply_filters( 'eddc_post_meta_backwards_compat_keys', array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id', '_edd_all_access_info' ) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $check;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'eddc_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) );
	$commission      = new EDD_Commission( $object_id );

	if ( empty( $commission->id ) ) {
		// We didn't find a commission record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT commission_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $object_id" );
		if ( ! empty( $object_id ) ) {
			$commission = new EDD_Commission( $object_id );
		} else {
			return $check;
		}
	}

	switch( $meta_key ) {

		case '_edd_commission_info':

			if ( isset( $meta_value['user_id'] ) ) {
				$commission->user_id = $meta_value['user_id'];
			}

			if ( isset( $meta_value['rate'] ) ) {
				$commission->rate = $meta_value['rate'];
			}

			if ( isset( $meta_value['amount'] ) ) {
				$commission->amount = $meta_value['amount'];
			}

			if ( isset( $meta_value['currency'] ) ) {
				$commission->currency = $meta_value['currency'];
			}

			if ( isset( $meta_value['type'] ) ) {
				$commission->type = $meta_value['type'];
			}

			$commission->save();

			// Since the old commission data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Commission data is no longer stored in post meta. Please use the new custom database tables to insert a commission record.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;

		case '_commission_status' :

			// Status should only be able to be a single entry, and it's not meta, save it on the commission record;
			$commission->status = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _commission_status postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;


		case '_edd_commission_payment_id':

			$commission->payment_id = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;

		case '_download_id':

			$commission->download_id = $meta_value;
			$commission->save();

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = true;

			break;

		case '_edd_all_access_info':
			$check = edd_commissions()->commission_meta_db->update_meta( $commission->id, $meta_key, $meta_value, $prev_value );
			break;

		default:
			// Developers can hook in here with add_action( 'eddc_update_post_meta_backwards_compat-meta_key... in order to
			// Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Commission::get_meta
			$check = apply_filters( 'eddc_update_post_meta_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $prev_value );
			break;
	}

	return $check;

}
add_filter( 'update_post_metadata', 'eddc_update_meta_backcompat', 99, 5 );

/**
 * Listen for calls to update_post_meta and see if we need to filter them.
 *
 * @since  3.4
 * @param mixed   $check       Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  mixed  $meta_value  The value get_post_meta would return if we don't filter.
 * @param  mixed  $delete_all  Delete all records found with meta_key
 * @return mixed               Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
 */
function eddc_delete_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $delete_all ) {
	global $wpdb;

	$meta_keys = apply_filters( 'eddc_post_meta_backwards_compat_keys', array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id', '_edd_all_access_info' ) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $check;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = apply_filters( 'eddc_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) );
	$commission      = new EDD_Commission( $object_id );

	if ( empty( $commission->id ) ) {
		// We didn't find a commission record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT commission_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $object_id" );
		if ( ! empty( $object_id ) ) {
			$commission = new EDD_Commission( $object_id );
		} else {
			return $check;
		}
	}

	switch( $meta_key ) {

		case '_edd_commission_info':

			// Since the old commission data was simply stored in a single post meta entry, just don't let it be added.
			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'Commission data is no longer stored in post meta. Please use the new custom database tables to insert a commission record.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;

		case '_commission_status' :

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _commission_status postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to manage the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;


		case '_edd_commission_payment_id':

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to manage the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;

		case '_download_id':

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to manage the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			$check = false;

			break;

		case '_edd_all_access_info':
			$check = edd_commissions()->commission_meta_db->delete_meta( $commission->id, $meta_key, $meta_value, $delete_all );
			break;

		default:
			// Developers can hook in here with add_action( 'eddc_update_post_meta_backwards_compat-meta_key... in order to
			// Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Commission::get_meta
			$check = apply_filters( 'eddc_delete_post_meta_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $meta_value, $delete_all );
			break;
	}

	return $check;

}
add_filter( 'delete_post_metadata', 'eddc_delete_meta_backcompat', 99, 5 );
