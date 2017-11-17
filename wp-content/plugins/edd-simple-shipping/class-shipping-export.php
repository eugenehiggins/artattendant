<?php
/**
 * Shipping Export Class
 *
 * This class handles exporting orders that need shipped
 *
 * @package     Easy Digital Downloads - Simple Shipping
 * @subpackage  Export Class
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Simple_Shipping_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters / actions
	 *
	 * @access      public
	 * @var         string
	 * @since       1.2
	 */
	public $export_type = 'unshipped_orders';

	/**
	 * Set the CSV columns
	 *
	 * @access      public
	 * @since       1.2
	 * @return      array
	 */
	public function csv_cols() {
		$cols = array(
			'id'         => __( 'Order ID',   'edd-simple-shipping' ),
			'date'       => __( 'Date', 'edd' ),
			'first_name' => __( 'First Name', 'edd-simple-shipping' ),
			'last_name'  => __( 'Last Name', 'edd-simple-shipping' ),
			'email'      => __( 'Email', 'edd-simple-shipping' ),
			'address'    => __( 'Address', 'edd-simple-shipping' ),
			'address2'   => __( 'Address Line 2', 'edd-simple-shipping' ),
			'city'       => __( 'City', 'edd-simple-shipping' ),
			'state'      => __( 'State / Province', 'edd-simple-shipping' ),
			'zip'        => __( 'Zip / Postal Code', 'edd-simple-shipping' ),
			'country'    => __( 'Country', 'edd-simple-shipping' ),
			'amount'     => __( 'Amount', 'edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
			'tax'        => __( 'Tax', 'edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
			'gateway'    => __( 'Payment Method', 'edd' ),
			'key'        => __( 'Purchase Key', 'edd' ),
			'products'   => __( 'Products', 'edd' ),
			'status'     => __( 'Payment Status', 'edd' )
		);
		return $cols;
	}

	/**
	 * Get the data being exported
	 *
	 * @access      public
	 * @since       1.2
	 * @return      array
	 */
	public function get_data() {
		global $edd_logs;

		$data = array();

		$args = array(
			'nopaging'  => true,
			'post_type' => 'edd_payment',
			'meta_key'  => '_edd_payment_shipping_status',
			'meta_value'=> '1',
			'fields'    => 'ids'
		);

		$payments = get_posts( $args );

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$user_info = edd_get_payment_meta_user_info( $payment );
				$downloads = edd_get_payment_meta_cart_details( $payment );
				$products  = '';

				if ( $downloads ) {
					foreach ( $downloads as $key => $download ) {

						// Display the Downoad Name
						$products .= get_the_title( $download['id'] );

						if ( $key != ( count( $downloads ) -1 ) ) {
							$products .= ' / ';
						}
					}
				}
				$data[]    = array(
					'id'         => $payment,
					'date'       => get_post_field( 'post_date', $payment ),
					'first_name' => $user_info['first_name'],
					'last_name'  => $user_info['last_name'],
					'email'      => $user_info['email'],
					'address'    => $user_info['shipping_info']['address'],
					'address2'   => ! empty( $user_info['shipping_info']['address2'] ) ? $user_info['shipping_info']['address2'] : '',
					'city'       => $user_info['shipping_info']['city'],
					'state'      => $user_info['shipping_info']['state'],
					'zip'        => $user_info['shipping_info']['zip'],
					'country'    => $user_info['shipping_info']['country'],
					'amount'     => edd_get_payment_amount( $payment ),
					'tax'        => edd_get_payment_tax( $payment ),
					'gateway'    => edd_get_payment_gateway( $payment ),
					'key'        => edd_get_payment_key( $payment ),
					'products'   => $products,
					'status'     => get_post_field( 'post_status', $payment )
				);
			}
		}

		$data = apply_filters( 'edd_export_get_data', $data );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}