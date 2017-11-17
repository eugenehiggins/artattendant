<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_Batch_FES_Recount_Vendor_Statistics extends EDD_Batch_Export {

	public $is_void = true;
	public $per_step  = 25;

	/**
	 * Retrieves data for a batch
	 *
	 * @access      public
	 * @since       2.5
	 * @return      int $percentage The calculated completion percentage
	 */
	public function get_data() {

		// Get first 25 vendors
		$vendor_db = new FES_DB_Vendors();
		$args = array(
			'number'  => $this->per_step,
			'offset'  => ( $this->step -1 ) * $this->per_step,
			'orderby' => 'id',
			'order'   => 'DESC'
		);

		$vendors = $vendor_db->get_vendors( $args );

		if ( $vendors ) {

			foreach ( $vendors as $vendor ) {
				// foreach get products
				$user_id = ! empty( $vendor->user_id ) ? intval( $vendor->user_id ) : 0;
				$products = EDD_FES()->vendors->get_all_products( $user_id );
				$product_count = 0;
				$sales_count   = 0;
				$sales_value   = 0;
				foreach ( $products as $product ) {
					$sales_count   = $sales_count + $product['sales'];
					$sales_value   = $sales_value + $product['earnings'];
					$product_count++;
				}

				$vendor_db->update( $vendor->id, array( 'sales_count' => $sales_count, 'sales_value' => $sales_value, 'product_count' => $product_count ) );
			}

			return true;
	
		} else {
	
			return false;
	
		}
	
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @access      public
	 * @since       2.5
	 * @return      int $percentage The calculated completion percentage
	 */
	public function get_percentage_complete() {
		$db_vendors = new FES_DB_Vendors();
		$total      = $db_vendors->count();
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
	 * @since       2.5
	 * @return      bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to recount statistics.', 'edd_fes' ), __( 'Error', 'edd_fes' ), array( 'response' => 403 ) );
		}
		$had_data = $this->get_data();
		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->done     = true;
			$this->message  = __( 'Statistics recounted.', 'edd_fes' );
			// This allows the page to redirect to help with the UI
			$this->message .= '<script>setTimeout(function(){ location.reload(); }, 2000);</script>';
			return false;
		}
	}
}