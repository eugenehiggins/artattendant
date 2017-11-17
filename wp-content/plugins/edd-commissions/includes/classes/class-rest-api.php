<?php
/**
 * API Functions
 *
 * @package     EDD_Commissions
 * @subpackage  API
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// extends the default EDD REST API to provide an endpoint for commissions
class EDDC_REST_API {


	/**
	 * Get things started
	 *
	 * @access      public
	 * @return      void
	 */
	public function __construct() {
		add_filter( 'edd_api_valid_query_modes', array( $this, 'query_modes'  ) );
		add_filter( 'edd_api_output_data',       array( $this, 'user_commission_data' ), 10, 3 );
		add_filter( 'edd_api_output_data',       array( $this, 'store_commission_data' ), 10, 3 );
	}


	/**
	 * Add commissions to the available query modes
	 *
	 * @access      public
	 * @param       array $query_modes The current query modes
	 * @return      array The adjusted query modes
	 */
	public function query_modes( $query_modes ) {
		$query_modes[] = 'commissions';
		$query_modes[] = 'store-commissions';

		return $query_modes;
	}


	/**
	 * Fetch commission data
	 *
	 * @access      public
	 * @param       array $data
	 * @param       string $query_mode
	 * @param       object $api_object
	 * @return      array
	 */
	public function store_commission_data( $data, $query_mode, $api_object ) {
		if ( 'store-commissions' != $query_mode ) {
			return $data;
		}

		$user_id = $api_object->get_user();

		if ( ! user_can( $user_id, 'view_shop_reports' ) ) {
			return $data;
		}

		$data   = array( 'commissions' => array() );
		$paged  = $api_object->get_paged();
		$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'unpaid';

		$commission_args = array(
			'number' => $api_object->per_page(),
			'paged'  => $paged,
			'status' => $status,
		);

		$commissions = get_posts( $commission_args );

		// Cache the requested downloads for the following loop, to save requests.
		$requested_downloads = array();

		if ( $commissions ) {
			foreach ( $commissions as $commission ) {
				if ( empty( $requested_downloads[ $commission->download_id ] ) ) {
					$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
				}
				$download = $requested_downloads[ $commission->download_id ];

				$data['commissions'][] = array(
					'amount'   => edd_sanitize_amount( $commission->amount ),
					'rate'     => $commission->rate,
					'currency' => $commission->currency,
					'item'     => $download->get_name(),
					'status'   => $commission->status,
					'date'     => $commission->date_created,
					'renewal'  => $commission->get_meta( 'is_renewal' ) ? 1 : 0
				);
			}

			wp_reset_postdata();
		}

		$data['total_unpaid'] = eddc_get_unpaid_totals();

		return $data;
	}


	/**
	 * Fetch user commission data
	 *
	 * @access      public
	 * @param       array $data
	 * @param       string $query_mode
	 * @param       object $api_object
	 * @return      array
	 */
	public function user_commission_data( $data, $query_mode, $api_object ) {
		if ( 'commissions' != $query_mode ) {
			return $data;
		}

		$user_id = $api_object->get_user();

		$data['unpaid']  = array();
		$data['paid']    = array();
		$data['revoked'] = array();

		$unpaid = eddc_get_unpaid_commissions( array( 'user_id' => $user_id, 'number' => 30, 'paged' => $api_object->get_paged() ) );

		// Cache the requested downloads for the following loops, to save requests.
		$requested_downloads = array();

		if ( ! empty( $unpaid ) ) {
			foreach ( $unpaid as $commission ) {
				if ( empty( $requested_downloads[ $commission->download_id ] ) ) {
					$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
				}
				$download = $requested_downloads[ $commission->download_id ];

				$data['unpaid'][] = array(
					'amount'   => edd_sanitize_amount( $commission->amount ),
					'rate'     => $commission->rate,
					'currency' => $commission->currency,
					'item'     => $download->get_name(),
					'date'     => $commission->date_created,
					'renewal'  => $commission->get_meta( 'is_renewal' ) ? 1 : 0
				);
			}
		}

		$paid = eddc_get_paid_commissions( array( 'user_id' => $user_id, 'number' => 30, 'paged' => $api_object->get_paged() ) );

		if ( ! empty( $paid ) ) {
			foreach ( $paid as $commission ) {
				if ( empty( $requested_downloads[ $commission->download_id ] ) ) {
					$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
				}
				$download = $requested_downloads[ $commission->download_id ];

				$data['paid'][] = array(
					'amount'   => edd_sanitize_amount( $commission->amount ),
					'rate'     => $commission->rate,
					'currency' => $commission->currency,
					'item'     => $download->get_name(),
					'date'     => $commission->date_created,
					'renewal'  => $commission->get_meta( 'is_renewal' ) ? 1 : 0
				);
			}
		}

		$revoked = eddc_get_revoked_commissions( array( 'user_id' => $user_id, 'number' => 30, 'paged' => $api_object->get_paged() ) );

		if ( ! empty( $revoked ) ) {
			foreach ( $revoked as $commission ) {
				if ( empty( $requested_downloads[ $commission->download_id ] ) ) {
					$requested_downloads[ $commission->download_id ] = new EDD_Download( $commission->download_id );
				}
				$download = $requested_downloads[ $commission->download_id ];

				$data['revoked'][] = array(
					'amount'   => edd_sanitize_amount( $commission->amount ),
					'rate'     => $commission->rate,
					'currency' => $commission->currency,
					'item'     => $download->get_name(),
					'date'     => $commission->date_created,
					'renewal'  => $commission->get_meta( 'is_renewal' ) ? 1 : 0
				);
			}
		}

		$data['totals'] = array(
			'unpaid'  => eddc_get_unpaid_totals( $user_id ),
			'paid'    => eddc_get_paid_totals( $user_id ),
			'revoked' => eddc_get_revoked_totals( $user_id )
		);

		return $data;
	}
}
new EDDC_REST_API;
