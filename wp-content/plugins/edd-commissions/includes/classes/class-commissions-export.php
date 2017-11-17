<?php
/**
 * Commissions Export Class
 *
 * This class handles exporting user's commissions
 *
 * @package     Easy Digital Downloads - Commissions
 * @subpackage  Export Class
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Bootstrap export class if necessary
if ( ! class_exists( 'EDD_Export' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';
}


/**
 * Exprt commissions class
 *
 * Handles exporting commissions
 *
 * @since       2.3
 */
class EDD_Commissions_Export extends EDD_Export {


	/**
	 * Our export type.
	 *
	 * @access      public
	 * @var         string
	 * @since       2.3
	 */
	public $export_type = 'commissions';


	/**
	 * User ID to export commissions for.
	 *
	 * @access      public
	 * @var         int
	 * @since       2.3
	 */
	public $user_id = 0;


	/**
	 * Export Year.
	 *
	 * @access      public
	 * @var         int
	 * @since       2.3
	 */
	public $year = 0;


	/**
	 * Export month.
	 *
	 * @access      public
	 * @var         int
	 * @since       2.3
	 */
	public $month = 0;


	/**
	 * Can we export?
	 *
	 * @access      public
	 * @since       2.3
	 * @return      bool Whether we can export or not
	 */
	public function can_export() {
		return (bool) apply_filters( 'edd_export_capability', current_user_can( 'read' ) );
	}


	/**
	 * Set the export headers
	 *
	 * @access      public
	 * @since       2.3
	 * @return      void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=edd-export-' . $this->export_type . '-' . $this->year . '-' . $this->month . '.csv' );
		header( "Expires: 0" );
	}


	/**
	 * Set the CSV columns
	 *
	 * @access      public
	 * @since       2.3
	 * @return      array
	 */
	public function csv_cols() {
		$cols = array(
			'download' => __( 'Product', 'eddc' ),
			'amount'   => __( 'Amount',  'eddc' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
			'date'     => __( 'Date',    'eddc' )
		);

		return $cols;
	}


	/**
	 * Get the data being exported
	 *
	 * @access      public
	 * @since       2.3
	 * @return      array
	 */
	public function get_data() {
		$data = array();

		$args = array(
			'year'     => ! empty( $this->year )  ? $this->year  : date( 'Y' ),
			'monthnum' => ! empty( $this->month ) ? $this->month : date( 'n' )
		);

		$commissions = eddc_get_paid_commissions( array( 'user_id' => $this->user_id, 'number' => -1, 'query_args' => $args ) );

		if ( $commissions ) {
			foreach ( $commissions as $commission ) {

				$data[]        = array(
					'download' => get_the_title( $commission->download_id ),
					'amount'   => $commission->amount,
					'date'     => $commission->date_created
				);
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
