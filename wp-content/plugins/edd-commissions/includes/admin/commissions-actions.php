<?php
/**
 * Commissions Actions
 *
 * @package 	EDD_Commissions
 * @subpackage 	Admin
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Commissions link
 *
 * @since       1.0
 * @global      object $eddc_commissions_page Reference for the commissions edit page
 * @return      void
 */
function eddc_add_commissions_link() {
	global $eddc_commissions_page;

	$eddc_commissions_page = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Commissions', 'eddc' ), __( 'Commissions', 'eddc' ), 'edit_shop_payments', 'edd-commissions', 'eddc_commissions_page' );
}
add_action( 'admin_menu', 'eddc_add_commissions_link', 10 );


/**
 * Add a Commission
 *
 * @since       2.9
 * @return      void
 */
function eddc_add_manual_commission() {
	if ( ! isset( $_POST['eddc_add_commission_nonce'] ) || ! wp_verify_nonce( $_POST['eddc_add_commission_nonce'], 'eddc_add_commission' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		wp_die( __( 'You do not have permission to record commissions', 'eddc' ) );
	}

	$user_info   = get_userdata( $_POST['user_id'] );
	$download_id = sanitize_text_field( $_POST['download_id'] );
	$price_id    = false;

	// Since it supports variable prices, we need to detect variable pricing.
	if ( strpos( $download_id, '_' ) ) {
		$price_parts = explode( '_', $download_id );
		$download_id = absint( $price_parts[0] );
		$price_id    = absint( $price_parts[1] );
	}

	$payment_id  = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;
	$type         = sanitize_text_field( $_POST['type'] );
	$amount       = edd_sanitize_amount( $_POST['amount'] );
	$rate         = ! empty( $_POST['rate'] ) ? sanitize_text_field( $_POST['rate'] ) : $amount;
	$status       = ! empty( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'unpaid';
	$date_created = ! empty( $_POST['date_created'] ) ? $_POST['date_created'] . ' 00:00:00' : null;
	$date_paid    = ! empty( $_POST['date_paid'] ) ? $_POST['date_paid'] . ' 00:00:00' : null;

	$statuses    = array( 'unpaid', 'paid', 'revoked' );
	if ( ! in_array( $status, $statuses ) ) {
		$status = 'unpaid';
	}

	$types = array( 'percentage', 'flat' );
	if ( ! in_array( $type, $types ) ) {
		wp_die( __( 'Invalid commission type.', 'eddc' ) );
	}

	// set a flag so downloads with commissions awarded are easy to query
	update_post_meta( $download_id, '_edd_has_commission', true );

	$commission = new EDD_Commission;
	$commission->user_id      = absint( $_POST['user_id'] );
	$commission->rate         = $rate;
	$commission->amount       = $amount;
	$commission->currency     = edd_get_option( 'currency', 'USD' );
	$commission->download_id  = $download_id;
	$commission->payment_id   = $payment_id;
	$commission->type         = $type;
	$commission->status       = $status;
	$commission->date_created = $date_created;
	$commission->date_paid    = $date_paid;

	// If we are dealing with a variation, then save variation info
	if ( false !== $price_id ) {
		$commission->download_variation = $price_id;
	}

	$commission->save();

	$args = array(
		'user_id'  => $commission->user_id,
		'rate'     => $commission->rate,
		'amount'   => $commission->amount,
		'currency' => $commission->currency,
		'type'     => $commission->type,
	);

	$commission_info = apply_filters( 'edd_commission_info', $args, $commission->ID, $commission->payment_id, $commission->download_id );
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

	do_action( 'eddc_insert_commission', $commission->user_id, $commission->amount, $commission->rate, $commission->download_id, $commission->ID, $payment_id );

	wp_redirect( add_query_arg( array( 'view' => false, 'edd-message' => 'add' ) ) );
	exit;
}
add_action( 'admin_init', 'eddc_add_manual_commission' );


/**
 * Process commission actions for single view
 *
 * @since       3.3
 * @return      void
 */
function eddc_process_commission_update() {
	if ( empty( $_GET['commission'] ) || empty( $_GET['action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( isset( $_GET['_wpnonce'] ) && ! wp_verify_nonce( $_GET['_wpnonce'], 'eddc_commission_nonce' ) ) {
		return;
	}

	$action = sanitize_text_field( $_GET['action'] );
	$id     = absint( $_GET['commission'] );

	switch ( $action ) {
		case 'mark_as_paid':
			eddc_set_commission_status( $id, 'paid' );
			break;
		case 'mark_as_unpaid':
			eddc_set_commission_status( $id, 'unpaid' );
			break;
		case 'mark_as_revoked':
			eddc_set_commission_status( $id, 'revoked' );
			break;
		case 'mark_as_accepted':
			eddc_set_commission_status( $id, 'unpaid' );
			break;
	}

	do_action( 'eddc_process_commission_update', $action, $id );

	wp_redirect( add_query_arg( array( 'action' => false, '_wpnonce' => false, 'edd-message' => $action ) ) );
	exit;
}
add_action( 'admin_init', 'eddc_process_commission_update', 1 );


/**
 * Update commission data
 *
 * @since       3.3
 * @return      void
 */
function eddc_update_commission() {
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( ! isset( $_POST['eddc_user'] ) && ! isset( $_POST['eddc_download'] ) && ! isset( $_POST['eddc_rate'] ) && ! isset( $_POST['eddc_amount'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['eddc_update_commission_nonce'], 'eddc_update_commission' ) ) {
		wp_die( __( 'Nonce verification failed', 'eddc' ), __( 'Error', 'eddc' ), array( 'response' => 403 ) );
	}

	$commission_id   = (int) $_POST['commission_id'];
	$commission      = new EDD_Commission( $commission_id );

	$rate = str_replace( '%', '', $_POST['eddc_rate'] );
	if ( $rate < 1 ) {
		$rate = $rate * 100;
	}

	$amount = str_replace( '%', '', $_POST['eddc_amount'] );

	if ( ! empty( $_POST['date_created'] ) ) {
		$commission->date_created = date( 'Y-m-d H-i-s', strtotime( sanitize_text_field( $_POST['date_created'] ) ) );
	}

	if ( ! empty( $_POST['date_paid'] ) ) {
		$commission->date_paid = date( 'Y-m-d H-i-s', strtotime( sanitize_text_field( $_POST['date_paid'] ) ) );
	}

	$commission->rate        = (float) $rate;
	$commission->amount      = (float) $amount;
	$commission->user_id     = (int) $_POST['eddc_user'];
	$commission->download_id = absint( $_POST['eddc_download'] );
	$commission->save();

	wp_redirect( add_query_arg( array( 'edd-message' => 'update' ) ) );
	exit;
}
add_action( 'admin_init', 'eddc_update_commission', 1 );


/**
 * Delete a commission
 *
 * @since       3.3
 * @return      void
 */
function eddc_delete_commission( $args ) {
	$commission_id = absint( $_POST['commission_id'] );

	// First verify the nonce.
	$nonce = $args['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'delete-commission' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'eddc' ) );
	}

	// Check to see if they connfirmed they want to delete.
	$confirm = ! empty( $args['eddc-commission-delete-comfirm'] ) ? true : false;
	if ( ! $confirm ) {
		edd_set_error( 'commission-delete-no-confirm', __( 'Please confirm you want to delete this commission', 'eddc' ) );
	}

	if ( edd_get_errors() ) {
		wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission_id ) );
		exit;
	}

	// Once nonce and verification have been passed, look up the data.
	$commission    = new EDD_Commission( $commission_id );
	if ( ! current_user_can( 'edit_shop_payments', $commission->payment_id ) ) {
		wp_die( __( 'You do not have permission to edit this commission', 'eddc' ), __( 'Error', 'eddc' ), array( 'response' => 403 ) );
	}

	$commission->delete();

	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-commissions&edd-message=delete' ) );
	exit;
}
add_action( 'edd_delete_commission', 'eddc_delete_commission' );
