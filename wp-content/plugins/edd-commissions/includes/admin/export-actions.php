<?php
/**
 * Export Actions
 *
 * These are actions related to exporting data from EDD Commissions.
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register the commissions report batch exporter
 *
 * @since       3.3
 * @return      void
 */
function eddc_register_commissions_report_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_commissions_report_batch_processor', 11, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_commissions_report_batch_export', 11 );

/**
 * Register the commissions report details batch exporter
 *
 * @since       3.4
 * @return      void
 */
function eddc_register_commissions_report_details_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_commissions_report_details_batch_processor', 11, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_commissions_report_details_batch_export', 11 );

/**
 * Register the payouts batch exporter
 *
 * @since       2.4.2
 * @return      void
 */
function eddc_register_payouts_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_payouts_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_payouts_batch_export', 10 );


/**
 * Register the mark paid batch exporter
 *
 * @since       2.4.2
 * @return      void
 */
function eddc_register_mark_paid_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_paid_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_mark_paid_batch_export', 10 );
