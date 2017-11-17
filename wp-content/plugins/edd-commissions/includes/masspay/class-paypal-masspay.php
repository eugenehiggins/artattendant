<?php
/**
 * Mass payment class
 *
 * This class handles paying out commissions via the PayPal Mass Pay API
 *
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

class EDDC_Mass_Pay {

	private static $plugin_dir;

	public function do_payments() {
		self::$plugin_dir = trailingslashit( dirname( __FILE__ ) );
		$vendors          = $this->get_users();

		return $this->pay_vendors( $vendors );
	}

	private function include_paypal_sdk() {
		$path = self::$plugin_dir . 'api/lib';

		require_once $path . 'services/PayPalAPIInterfaceService/PayPalAPIInterfaceServiceService.php';
		require_once $path . 'PPLoggingManager.php';
	}


	private function get_users() {

		$commissions = eddc_get_unpaid_commissions( array(
			'number' => -1
		) );

		if ( empty( $commissions ) )
			return false;

		$due_amounts = array();
		foreach ( $commissions as $commission ) {

			$commission_meta = get_post_meta( $commission->ID, '_edd_commission_info', true );
			$user_id         = $commission_meta['user_id'];
			$user            = get_userdata( $user_id );
			$custom_paypal   = get_user_meta( $user_id, 'eddc_user_paypal', true );
			$email           = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

			$due_amounts[ $user_id ][] = $commission_meta['amount'];

			eddc_set_commission_status( $commission->ID, 'paid' );
		}

		foreach ( $due_amounts as $vendor_id => $totals_due ) {
			$due_amounts[ $vendor_id ] = array_sum( $totals_due );
		}

		foreach ( $due_amounts as $vendor_id => $total_due ) {
			$commission_due = $total_due;
			$custom_paypal  = get_user_meta( $vendor_id, 'eddc_user_paypal', true );
			$user           = get_userdata( $vendor_id );
			$paypal_email   = is_email( $custom_paypal ) ? $custom_paypal : $user->user_email;

			// Skip vendors that haven't filled a paypal address
			// Or that don't have an outstanding balance
			if ( empty( $paypal_email ) || empty( $commission_due ) )
				continue;

			// Who knows if it exists more than once. Let's not take a risk
			// Therefore, we add the total due to perhaps a previously existing one
			$vendors                 = array();
			$vendors[ $paypal_email ] = array(
				'user_id'   => $user_id,
				'total_due' => ! empty( $vendors[ $paypal_email ][ $vendor_id ][ 'total_due' ] ) ? $vendors[ $paypal_email ][ $vendor_id ][ 'total_due' ] + $commission_due : $commission_due
			);
		}

		return $vendors;
	}

	private function pay_vendors( $vendors ) {

		if ( empty( $vendors ) ) {

			$return = array(
				'status' => 'error',
				'msg' => __( 'No vendors found to pay. Maybe they haven\'t set a PayPal address?', 'eddc' )
			);
			$this->mail_results( $return );

			return $return;
		}

		$this->include_paypal_sdk();

		$logger                      = new PPLoggingManager( 'MassPay' );
		$massPayRequest              = new MassPayRequestType();
		$massPayRequest->MassPayItem = array();

		$total_pay = 0;
		foreach ( $vendors as $user_paypal => $user ) {

			// Don't attempt to process payments for users that owe the admin money
			if ( $user['total_due'] <= 0 )
				continue;

			$total_pay += $user['total_due'];
			$masspayItem                   = new MassPayRequestItemType();
			$masspayItem->Amount           = new BasicAmountType( edd_get_currency(), $user['total_due'] );
			$masspayItem->ReceiverEmail    = $user_paypal;
			$massPayRequest->MassPayItem[] = $masspayItem;

		}

		$massPayReq                 = new MassPayReq();
		$massPayReq->MassPayRequest = $massPayRequest;

		$paypalService = new PayPalAPIInterfaceServiceService();

		// Wrap API method calls on the service object with a try catch
		try {

			$massPayResponse = $paypalService->MassPay( $massPayReq );

		}
		catch ( Exception $ex ) {

			$return = array(
				'status' => 'error',
				'msg'    => sprintf( __( 'Error: %s', 'eddc' ), $ex->getMessage() ),
				'total'  => $total_pay
			);

			return $return;

		}

		$return = array();

		if ( isset( $massPayResponse ) ) {

			if ( $massPayResponse->Ack === 'Success' ) {
				if ( $this->purge_user_meta( $vendors ) ) {
					$return = array(
						'status' => 'updated',
						'msg'    => __( 'All due commission has been paid for.', 'eddc' ),
						'total'  => $total_pay
					);
				} else {
					$return = array(
						'status' => 'error',
						'msg'    => __( 'All due commission has been paid for, but I could not clear it from their profiles due to an internal error. Commission will still be listed as due. Please manually mark the commission as paid from the Commissions page.', 'eddc' ),
						'total'  => $total_pay
					);
				}
			} else {
				$return = array(
					'status' => 'error',
					'msg'    => sprintf( '%s. %s (%s): %s.', $massPayResponse->Ack, $massPayResponse->Errors->ShortMessage, $massPayResponse->Errors->ErrorCode, $massPayResponse->Errors->LongMessage ),
					'total'  => $total_pay
				);
			}

		}

		$this->mail_results( $return );

		return $return;
	}

	private function mail_results( $result ) {
		global $edd_options;

		/* Send an email notification to the admin */
		$admin_email = edd_get_admin_notice_emails();

		$admin_message = edd_get_email_body_header();
		$admin_message .= __( 'Hello! A payment was just triggered to mass pay all vendors their due commission.', 'eddc' ) . PHP_EOL . PHP_EOL;
		$admin_message .= sprintf( __( 'Payment status: %s.', 'eddc' ), $result['status'] ) . PHP_EOL;
		$admin_message .= sprintf( __( 'Payment message: %s.', 'eddc' ), $result['msg'] ) . PHP_EOL;

		if ( !empty( $result['total'] ) ) {
			$admin_message .= sprintf( __( 'Payment total: %s.', 'eddc' ), $result['total'] );
		}
		$admin_message .= edd_get_email_body_footer();

		$admin_subject = __( 'EDD Commissions: Mass payments for vendors update', 'eddc' );
		$admin_subject = apply_filters( 'eddc_admin_commissions_payout_notification_subject', $admin_subject, $result );

		$from_name  = isset( $edd_options['from_name'] ) ? $edd_options[ 'from_name' ] : get_bloginfo( 'name' );
		$from_email = isset( $edd_options['from_email'] ) ? $edd_options[ 'from_email' ] : get_option( 'admin_email' );

		$admin_headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$admin_headers .= "Reply-To: " . $from_email . "\r\n";
		$admin_headers .= "MIME-Version: 1.0\r\n";
		$admin_headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$admin_headers .= apply_filters( 'eddc_admin_commissions_payout_notification_headers', $admin_headers, $result );

		$sent = wp_mail( $admin_email, $admin_subject, $admin_message, $admin_headers );
		return $sent;
	}
}
