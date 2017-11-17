<?php
/**
 * Commission Admin Notice Generation Class
 *
 * This class handles display of admin notices for EDD Commissions
 *
 * @package     EDDC
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDDC_Admin_Notices Class
 *
 * @since       3.3
 */
class EDDC_Admin_Notices {


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       3.3
	 * @return      void
	 */
	public function __construct() {
		$this->init();
	}


	/**
	 * Process hooks on init
	 *
	 * @access      public
	 * @since       3.3
	 * @return      void
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}


	/**
	 * Determine which, if any, messages to show
	 *
	 * @access      public
	 * @since       3.3
	 * @return      void
	 */
	public function notices() {
		if ( ! isset( $_GET['page'] ) || $_GET['page'] != 'edd-commissions' ) {
			return;
		}

		if ( empty( $_GET['edd-message'] ) ) {
			return;
		}

		$type    = 'updated';
		$message = '';

		switch ( strtolower( $_GET['edd-message'] ) ) {
			case 'add' :
				$message = __( 'Commission added successfully', 'eddc' );
				break;
			case 'delete' :
				$message = __( 'Commission deleted successfully', 'eddc' );
				break;
			case 'update' :
				$message = __( 'Commission updated successfully', 'eddc' );
				break;
			case 'mark_as_paid' :
				$message = __( 'Commission marked as paid', 'eddc' );
				break;
			case 'mark_as_unpaid' :
				$message = __( 'Commission marked as unpaid', 'eddc' );
				break;
			case 'mark_as_revoked' :
				$message = __( 'Commission marked as revoked', 'eddc' );
				break;
			case 'mark_as_accepted' :
				$message = __( 'Commission marked as accepted', 'eddc' );
				break;
		}

		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
		}
	}
}
$eddc_admin_notices = new EDDC_Admin_Notices;
