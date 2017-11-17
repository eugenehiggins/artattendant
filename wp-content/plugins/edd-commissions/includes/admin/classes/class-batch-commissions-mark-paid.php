<?php
/**
 * Batch Commissions Mark as Paid Class
 *
 * This class handles payment export in batches
 *
 * @package     EDDC
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2.8
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Batch_Commissions_Mark_Paid Class
 *
 * @since       3.2.8
 */
class EDD_Batch_Commissions_Mark_Paid extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var         string
	 * @since       3.2.8
	 */
	public $export_type = 'commissions_paid';
	public $is_void     = true;
	public $per_step    = 25;

	/**
	 * Get the Export Data
	 *
	 * @access      public
	 * @since       3.2.8
	 * @global      object $wpdb Used to query the database using the WordPress Database API
	 * @return      bool
	 */
	public function get_data() {
		$items = get_option( '_eddc_ids_to_pay', array() );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );

		if ( $step_items ) {

			foreach ( $step_items as $item ) {
				eddc_set_commission_status( $item, 'paid' );
			}

			return true;

		}

		return false;
	}


	/**
	 * Return the calculated completion percentage
	 *
	 * @access      public
	 * @since       3.2.8
	 * @return      int $percentage The calculated completion percentage
	 */
	public function get_percentage_complete() {
		$ids_to_pay = get_option( '_eddc_ids_to_pay', array() );
		$total      = count( $ids_to_pay );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}


	/**
	 * Process a step
	 *
	 * @access      public
	 * @since       3.2.8
	 * @return      bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			delete_option( '_eddc_ids_to_pay' );
			$this->done     = true;
			$this->message  = __( 'Commissions marked as paid.', 'eddc' );
			// This allows the page to redirect to help with the UI
			$this->message .= '<script>setTimeout(function(){ location.reload(); }, 2000);</script>';
			return false;
		}
	}
}
