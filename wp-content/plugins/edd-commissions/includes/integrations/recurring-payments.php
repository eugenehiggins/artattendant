<?php
/**
 * Recurring Payments integration
 *
 * This file holds all functions make commissions work with the Recurring Payments extension
 *
 * @copyright   Copyright (c) 2016, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add new site-wide settings under "Downloads" > "Extensions" > "Commissions" for determining if recurring commissions should be enabled.
 *
 * @since    3.3
 * @param    array $commission_settings The array of settings for the Commissions settings page.
 * @return   array $commission_settings The array of settings for the Commissions settings page.
 */
function eddc_settings_add_recurring_options( $commission_settings ){
	$commission_settings[] = array(
		'id'   => 'edd_commissions_recurring_renewals',
		'name' => __( 'Recurring Payments Commissions', 'eddc' ),
		'desc' => sprintf( __('If checked and <a href="%s">Recurring Payments</a> is installed, EDD will automatically record commissions when subscription renewals are recorded.', 'eddc'), 'https://easydigitaldownloads.com/downloads/recurring-payments/' ),
		'type' => 'checkbox',
	);

	return $commission_settings;
}
add_filter( 'eddc_settings', 'eddc_settings_add_recurring_options', 10, 1 );


/**
 * When Recurring Payments records a subscription renewal, run the record commission function.
 *
 * @since       3.3
 * @param       $payment_id
 * @param       $parent_payment_id
 * @param       $payment_total
 * @param       $transaction_id
 * @return      void
 */
function eddc_record_subscription_commissions( $payment, $subscription ) {
	eddc_record_commission( $payment->ID, 'edd_subscription', 'pending' );
}
add_action( 'edd_recurring_add_subscription_payment', 'eddc_record_subscription_commissions', 10, 2 );


/**
 * Add 'edd_subscription' to the list of payment statuses that are allowed to record commissions.
 *
 * @since       3.3
 * @param       $statuses
 * @return      array
 */
function eddc_add_recurring_payment_status( $statuses ) {
	$allow_recurring_commissions = edd_get_option( 'edd_commissions_recurring_renewals', false );

	if ( $allow_recurring_commissions ) {
		$statuses = array_merge( array( 'edd_subscription' ), $statuses );
	}

	return $statuses;
}
add_filter( 'eddc_allowed_complete_statuses', 'eddc_add_recurring_payment_status', 10, 1 );


/**
 * Check if a download has recurring commissions disabled.
 *
 * @since       3.3
 * @param       $download_id
 * @return      bool
 */
function eddc_download_has_recurring_commissions( $download_id = 0 ) {
	$meta  = get_post_meta( $download_id, '_edd_commission_settings', true );
	$allow = isset( $meta['disable_recurring'] ) ? false : true;

	return $allow;
}


/**
 * Show the checkbox to disable any recurring commissions on a a download.
 *
 * @since       3.3
 * @return      void
 */
function eddc_metabox_disable_recurring_checkbox() {
	global $post;

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'eddc-recurring-admin-scripts', EDDC_PLUGIN_URL . 'assets/js/admin-eddc-recurring-integration' . $suffix . '.js', array( 'jquery' ), EDD_COMMISSIONS_VERSION, true );
	wp_enqueue_script( 'eddc-recurring-admin-scripts' );

	$has_recurring_commissions     = eddc_download_has_recurring_commissions( $post->ID );
	$recurring_commissions_enabled = edd_get_option( 'edd_commissions_recurring_renewals', false );

	if ( false === $recurring_commissions_enabled ) {
		return;
	}

	echo '<tr style="display:none;" class="eddc_commission_row" id="edd_commissions_recurring">';
		echo '<td class="edd_field_type_text">';
			echo '<label for="edd_commission_settings[type]"><strong>' . __( 'Recurring:', 'eddc' ) . '</strong></label>';
			echo '<p>';
				echo '<input type="checkbox" ' . checked( false, $has_recurring_commissions, false ) . ' name="edd_commission_settings[disable_recurring]" id="edd_commission_amount" value="1"/>&nbsp;';
				echo __( 'Disable Recurring Commissions.', 'eddc' );
			echo '</p>';
		echo '<td>';
	echo '</tr>';
}
add_action( 'eddc_metabox_options_table_after', 'eddc_metabox_disable_recurring_checkbox' );


/**
 * Allow the record commissions process to avoid downloads that have recurring commissions disabled.
 *
 * @since       3.3
 * @param       $record_commissions
 * @param       $download_id
 * @param       $payment_id
 * @return      bool
 */
function eddc_recurring_record_download_commissions( $record_commissions, $download_id, $payment_id ) {
	$payment = new EDD_Payment( $payment_id );

	$has_recurring_commissions = eddc_download_has_recurring_commissions( $download_id );

	if ( 'edd_subscription' === $payment->status && ! $has_recurring_commissions ) {
		$record_commissions = false;
	}

	return $record_commissions;
}
add_filter( 'eddc_should_record_download_commissions', 'eddc_recurring_record_download_commissions', 10, 3 );
