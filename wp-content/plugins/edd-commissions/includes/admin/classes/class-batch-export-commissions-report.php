<?php
/**
 * Batch Commissions Report Export Class.
 *
 * This class handles commissions report export.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Commissions_Report_Export Class
 *
 * @since 3.3
 */
class EDD_Batch_Commissions_Report_Export extends EDD_Batch_Export {


	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since       3.3
	 * @access      public
	 * @var         string
	 */
	public $export_type = 'commissions_report';


	/**
	 * Set the export headers.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_commissions_report_export_filename', 'edd-export-' . $this->export_type . '-' . date( 'm' ) . '-' . date( 'Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}


	/**
	 * Output the CSV columns.
	 *
	 * We make use of this function to set up the header of the earnings report.
	 *
	 * @access      public
	 * @since       3.3
	 * @return      string $cols The generated CSV header row
	 */
	public function print_csv_cols() {
		$cols = array(
			__( 'Monthly Commissions Activity', 'eddc' ),
			__( 'Unpaid', 'eddc' ),
			__( 'Paid', 'eddc' ),
			__( 'Revoked', 'eddc' ),
			__( 'Gross Total', 'eddc' ),
			__( 'Net Total', 'eddc' )
		);

		$col_data = '';

		for ( $i = 0; $i < count( $cols ); $i++ ) {
			$col_data .= $cols[ $i ];

			// We don't need an extra space after the first column
			if ( $i == 0 ) {
				$col_data .= ',';
				continue;
			}

			if ( $i == ( count( $cols ) - 1 ) ) {
				$col_data .= "\r\n";
			} elseif( $i == ( count( $cols ) - 2 ) ) {
				$col_data .= ",";
			} else {
				$col_data .= ",,";
			}
		}

		// Subtract 3 for `Gross Total`, `Net Total` and `Monthly Commissions Activity` column
		$statuses = count( $cols ) - 3;

		$col_data .= ',';
		for ( $i = 0; $i < $statuses; $i++ ) {
			$col_data .= __( 'Count', 'eddc' ) . ',' . __( 'Gross Amount', 'eddc' );

			if ( $i == ( $statuses - 1 ) ) {
				$col_data .= "\r\n";
			} else {
				$col_data .= ",";
			}
		}

		$this->stash_step_data( $col_data );

		return $col_data;
	}


	/**
	 * Print the CSV rows for the current step.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      mixed false|string $row_data The data for a given row
	 */
	public function print_csv_rows() {
		$row_data = '';

		$data = $this->get_data();

		if ( $data ) {
			$start_date = date( 'Y-m-d', strtotime( $this->start ) );

			if ( $this->count() == 0 ) {
				$end_date = date( 'Y-m-d', strtotime( $this->end ) );
			} else {
				$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
			}

			if ( $this->step == 1 ) {
				$row_data .= date( 'Y-m', strtotime( $start_date ) ) . ',';
			} elseif ( $this->step > 1 ) {
				$start_date = date( 'Y-m-d', strtotime( 'first day of +' . ( $this->step - 1 ) . ' month', strtotime( $start_date ) ) );

				if ( date( 'Y-m', strtotime( $start_date ) ) == date( 'Y-m', strtotime( $this->end ) ) ) {
					$end_date = date( 'Y-m-d', strtotime( $this->end ) );
					$row_data .= date( 'Y-m', strtotime( $end_date ) ) . ',';
				} else {
					$row_data .= date( 'Y-m', strtotime( $start_date ) ) . ',';
				}
			}

			$unpaid_total  = isset( $data['unpaid']['amount']  ) ? $data['unpaid']['amount'] : 0;
			$paid_total    = isset( $data['paid']['amount'] ) ? $data['paid']['amount'] : 0;
			$revoked_total = isset( $data['revoked']['amount'] ) ? $data['revoked']['amount'] : 0;

			$row_data .= isset( $data['unpaid']['count'] ) ? $data['unpaid']['count'] . ',' : 0 . ',';
			$row_data .= '"' . edd_format_amount( $unpaid_total ) . '"' . ',';

			$row_data .= isset( $data['paid']['count'] ) ? $data['paid']['count'] . ',' : 0 . ',';
			$row_data .= '"' . edd_format_amount( $paid_total ) . '"' . ',';

			$row_data .= isset( $data['revoked']['count'] ) ? $data['revoked']['count'] . ',' : 0 . ',';
			$row_data .= '"' . edd_format_amount( $revoked_total ) . '"' . ',';

			$row_data .= '"' . edd_format_amount( $paid_total + $unpaid_total + $revoked_total ) . '",';

			$row_data .= '"' . edd_format_amount( $paid_total + $unpaid_total ) . '"';

			$row_data .= "\r\n";

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}


	/**
	 * Get the Export Data.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      mixed false|array $data The data for the CSV file
	 */
	public function get_data() {
		$data = array();

		$start_date     = date( 'Y-m-d', strtotime( $this->start ) );
		$maybe_end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );

		if ( $this->count() == 0 ) {
			$end_date = date( 'Y-m-d', strtotime( $this->end ) );
		} else {
			$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
		}

		if ( $this->step > 1 ) {
			$start_date = date( 'Y-m-d', strtotime( 'first day of +' . ( $this->step - 1 ) . ' month', strtotime( $start_date ) ) );

			if ( date( 'Y-m', strtotime( $start_date ) ) == date( 'Y-m', strtotime( $this->end ) ) ) {
				$end_date = date( 'Y-m-d', strtotime( $this->end ) );
			} else {
				$end_date = date( 'Y-m-d', strtotime( 'first day of +1 month', strtotime( $start_date ) ) );
			}
		}

		if ( strtotime( $start_date ) > strtotime( $this->end ) ) {
			return false;
		}

		$args = array(
			'number'     => -1,
			'query_args' => array(
				'date_query' => array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				)
			)
		);

		$unpaid        = eddc_get_unpaid_commissions( $args );
		$unpaid_total  = edd_commissions()->commissions_db->sum( 'amount', array_merge( $args, array( 'status' => 'unpaid' ) ) );
		$paid          = eddc_get_paid_commissions( $args );
		$paid_total    = edd_commissions()->commissions_db->sum( 'amount', array_merge( $args, array( 'status' => 'paid' ) ) );
		$revoked       = eddc_get_revoked_commissions( $args );
		$revoked_total = edd_commissions()->commissions_db->sum( 'amount', array_merge( $args, array( 'status' => 'revoked' ) ) );

		$data = array(
			'unpaid' => array(
				'count'  => ! empty( $unpaid ) ? count( $unpaid ) : 0,
				'amount' => edd_sanitize_amount( $unpaid_total )
			),
			'paid' => array(
				'count'  => ! empty( $paid ) ? count( $paid ) : 0,
				'amount' => edd_sanitize_amount( $paid_total )
			),
			'revoked' => array(
				'count'  => ! empty( $revoked ) ? count( $revoked ) : 0,
				'amount' => edd_sanitize_amount( $revoked_total )
			)
		);

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}


	/**
	 * Count the number of months we are dealing with.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int The calculated number of months for this CSV
	 */
	private function count() {
		return abs( ( date( 'Y', strtotime( $this->end ) ) - date( 'Y', strtotime( $this->start ) ) ) * 12 + ( date( 'm', strtotime( $this->end ) ) - date( 'm', strtotime( $this->start ) ) ) );
	}


	/**
	 * Return the calculated completion percentage
	 *
	 * @since       3.3
	 * @access      public
	 * @return      int $percentage Percentage of batch processing complete
	 */
	public function get_percentage_complete() {
		$percentage = 100;

		$total = $this->count();

		if ( $total > 0 ) {
			$percentage = ( $this->step / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}


	/**
	 * Set the properties specific to the commissions report.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       array $request The Form Data passed into the batch processing
	 * @return      void
	 */
	public function set_properties( $request ) {
		$this->start = ( isset( $request['start_month'] ) && isset( $request['start_year'] ) ) ? sanitize_text_field( $request['start_year'] ) . '-' . sanitize_text_field( $request['start_month'] ) . '-1' : '';
		$this->end   = ( isset( $request['end_month'] ) && isset( $request['end_year'] ) ) ? sanitize_text_field( $request['end_year'] ) . '-' . sanitize_text_field( $request['end_month'] ) . '-' . cal_days_in_month( CAL_GREGORIAN, sanitize_text_field( $request['end_month'] ), sanitize_text_field( $request['end_year'] ) ) : '';
		$this->status = ( isset( $request['status'] ) ) && 'all' !== $request['status'] ? sanitize_text_field( $request['status'] ) : 'all';
	}
}
