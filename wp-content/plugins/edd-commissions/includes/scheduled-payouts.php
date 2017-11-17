<?php
/**
 * Scheduled commission payouts
 *
 * This file holds all functions that take care of paying out commissions automatically on a schedule
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
 * Add / remove cron schedules when settings are saved
 *
 * @since       2.7
 * @return      $input array The settings being saved
 */
function eddc_check_schedule( $input ) {
	global $edd_options;

	$old_interval = wp_get_schedule( 'eddc_schedule_mass_payments' );
	$new_interval = $input['edd_commissions_autopay_schedule'];
	$instapay     = $edd_options['edd_commissions_autopay_pa'];

	/**
	 * 1. The user actually changed the schedule
	 * 2. Instant Pay is turned off
	 * 3. Manual was not selected
	 */
	if ( ( $old_interval != $new_interval ) && ! $instapay && $new_interval != 'manual' ) {
		eddc_remove_cron_schedule( $options );
		eddc_schedule_cron( $new_interval );
	}

	if ( $new_interval == 'manual' || $instapay ) {
		eddc_remove_cron_schedule( $options );
	}

	return $input;
}
add_filter( 'edd_settings_extensions_sanitize', 'eddc_check_schedule' );


/**
 * Pay commissions
 *
 * @since       2.7
 * @return      $input array The settings being saved
 */
function eddc_commissions_pay_now() {
	$mass_pay = new EDDC_Mass_Pay;
	$mass_pay = $mass_pay->do_payments();
}


/**
 * Remove cron schedule
 *
 * @since       2.7
 * @return      void
 */
function eddc_remove_cron_schedule() {
	$timestamp = wp_next_scheduled( 'eddc_schedule_mass_payments' );

	return wp_unschedule_event( $timestamp, 'eddc_schedule_mass_payments' );
}


/**
 * Add cron schedule
 *
 * @since       2.7
 * @param       $interval The interval to schedule
 * @return      void
 */
function eddc_schedule_cron( $interval ) {
	// Scheduled event
	add_action('eddc_schedule_mass_payments', 'eddc_commissions_pay_now');

	// Schedule the event
	if ( ! wp_next_scheduled( 'eddc_schedule_mass_payments' ) ) {
		wp_schedule_event( time(), $interval, 'eddc_schedule_mass_payments' );

		return true;
	}

	return false;
}


/**
 * Setup custom cron schedule
 *
 * @since       2.7
 * @return      void
 */
function eddc_commissions_custom_cron_intervals( $schedules ) {
	$schedules['biweekly'] = array(
		'interval' => 1209600,
		'display' => __('Once every two weeks','eddc')
	);

	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __('Once a month','eddc')
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'eddc_commissions_custom_cron_intervals' );
