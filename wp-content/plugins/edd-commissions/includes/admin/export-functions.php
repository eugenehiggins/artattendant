<?php
/**
 * Export Functions
 *
 * Helper functions for the bulk export process
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
 * Loads the commissions report batch process if needed
 *
 * @since       3.3
 * @param       string $class The class being requested to run for the batch export
 * @return      void
 */
function eddc_include_commissions_report_batch_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Report_Export' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-batch-export-commissions-report.php';
	}
}

/**
 * Loads the commissions details report batch process if needed
 *
 * @since       3.4
 * @param       string $class The class being requested to run for the batch export for details
 * @return      void
 */
function eddc_include_commissions_report_details_batch_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Report_Details_Export' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-batch-export-commissions-report-details.php';
	}
}


/**
 * Loads the commissions payouts batch process if needed
 *
 * @since       2.4.2
 * @param       string $class The class being requested to run for the batch export
 * @return      void
 */
function eddc_include_payouts_batch_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Payout' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-batch-commissions-payout.php';
	}
}



/**
 * Loads the commissions mark paid batch process if needed
 *
 * @since       2.4.2
 * @param       string $class The class being requested to run for the batch export
 * @return      void
 */
function eddc_include_paid_batch_processor( $class ) {
	if ( 'EDD_Batch_Commissions_Mark_Paid' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-batch-commissions-mark-paid.php';
	}
}
