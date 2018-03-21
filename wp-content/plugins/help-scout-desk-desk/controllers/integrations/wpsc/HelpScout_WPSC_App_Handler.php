<?php

/**
 * HelpScout WP eCommerce (WPSC) integration.
 *
 */
class HelpScout_WPSC_App_Handler extends HelpScout_WPSC_App {

	/**
	 * @var array|mixed
	 */
	private $data;

	/**
	 * @var array
	 */
	private $customer_emails = array();

	/**
	 * @var array
	 */
	private $customer_orders = array();

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Process the request
	 *  - Read input
	 *  - Validate signature
	 *  - Find purchase data
	 *  - Generate response
	 *
	 * @link http://developer.helpscout.net/custom-apps/style-guide/ HelpScout Custom Apps Style Guide
	 */
	public function process() {

		// get request data
		$this->data = $this->parse_data();

		// validate request
		if ( ! $this->is_signature_valid() ) {
			$this->respond( 'Invalid signature' );
		}

		// get customer email(s)
		$this->customer_emails = $this->get_customer_emails();

		// get customer order(s)
		$this->customer_orders = $this->query_customer_orders();

		// build the final response HTML for HelpScout
		$html = $this->build_response_html();

		// respond with the built HTML string
		$this->respond( $html );
	}

	/**
	 * @return array|mixed
	 */
	private function parse_data() {

		$data_string = file_get_contents( 'php://input' );
		$data        = json_decode( $data_string, true );

		return $data;
	}

	/**
	 * Validate the request
	 *
	 * - Validates the payload
	 * - Validates the request signature
	 *
	 * @return bool
	 */
	private function is_signature_valid() {

		// we need at least this
		if ( ! isset( $this->data['customer']['email'] ) && ! isset( $this->data['customer']['emails'] ) ) {
			return false;
		}

		if ( ! isset( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) ) {
			return false;
		}
		// check request signature
		$expected_signature = base64_encode( hash_hmac( 'sha1', json_encode( $this->data ), self::$secret_key, true ) );

		return $expected_signature === $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'];
	}

	/**
	 * Get an array of emails belonging to the customer
	 *
	 * @return array
	 */
	private function get_customer_emails() {

		$customer_data = $this->data['customer'];
		$emails        = array();

		if ( isset( $customer_data['emails'] ) && is_array( $customer_data['emails'] ) && count( $customer_data['emails'] ) > 1 ) {
			$emails = array_values( $customer_data['emails'] );
		} elseif ( isset( $customer_data['email'] ) ) {
			$emails = array( $customer_data['email'] );
		}

		$emails = apply_filters( 'helpscout_wpsc_customer_emails', $emails, $this->data );

		if ( count( $emails ) === 0 ) {
			$this->respond( __( 'No customer email given.', 'help-scout-desk' ) );
		}

		return $emails;
	}

	/**
	 * Query all orders belonging to the customer (by email)
	 *
	 * @return array
	 */
	private function query_customer_orders() {

		// allows you to perform your own search for customer orders, based on given data.
		$orders = apply_filters( 'helpscout_wpsc_customer_orders', array(), $this->customer_emails, $this->data );

		if ( ! empty( $orders ) ) {
			return $orders;
		}

		global $wpdb;

		$emails = rtrim( implode( "','", $this->customer_emails ), ",'" );

		$billing_email_form_id = WPSC_Checkout_Form::get()->get_field_id_by_unique_name( 'billingemail' );

		// query by email(s)
		$sql = 'SELECT p.id, p.processed AS status, p.date, p.totalprice, s1.value AS email FROM ' . WPSC_TABLE_PURCHASE_LOGS . ' AS p
                LEFT OUTER JOIN ' . WPSC_TABLE_SUBMITTED_FORM_DATA . " AS s1 ON s1.log_id = p.id AND s1.form_id = $billing_email_form_id
				WHERE 1 = 1 ";

		if ( count( $this->customer_emails ) > 1 ) {
			$in_clause = rtrim( str_repeat( "'%s', ", count( $this->customer_emails ) ), ', ' );
			$sql .= "AND s1.value IN( $in_clause ) ";
		} else {
			$sql .= "AND s1.value = '%s' ";
		}

		$sql .= 'GROUP BY p.ID  ORDER BY p.ID DESC';

		$query   = $wpdb->prepare( $sql, $this->customer_emails );
		$results = $wpdb->get_results( $query );

		if ( is_array( $results ) ) {
			return $results;
		}

		return array();
	}

	/**
	 * Process the request
	 *  - Find purchase data
	 *  - Generate response
	 *
	 * @link http://developer.helpscout.net/custom-apps/style-guide/ HelpScout Custom Apps Style Guide
	 * @return string
	 */
	private function build_response_html() {

		if ( count( $this->customer_orders ) === 0 ) {
			return apply_filters( 'hsd_wpsc_app_no_purchases_html', __( 'No purchase data found', 'help-scout-desk' ), $this );
		}

		$orders = array();

		foreach ( $this->customer_orders as $payment ) {

			$order              = array();
			$order['id']        = $payment->id;
			$order['status']    = $payment->status;
			$order['date']      = date_i18n( 'jS M Y', $payment->date );
			$order['link']      = '<a target="_blank" href="' . esc_url( admin_url( 'index.php?page=wpsc-purchase-logs&c=item_details&id=' . $payment->id ) ) . '">#' . $payment->id . '</a>';
			$order['amount']    = $payment->totalprice;
			$order['downloads'] = array();

			$orders[] = $order;
		}

		// build HTML output
		$html = apply_filters( 'hsd_wpsc_app_early_html', '', $this, $orders );

		foreach ( $orders as $order ) {

			$class = '';

			// open completed purchases by default
			$order_completed = WPSC_Purchase_Log::is_order_status_completed( $order['status'] );

			if ( $order_completed ) {
				$class = ' open';
			}

			$html .= '<div class="toggleGroup' . $class . '">';
			$html .= '<strong><i class="icon-cart"></i> ' . $order['link'] . '</strong> <a class="toggleBtn"><i class="icon-arrow"></i></a>';

			// show status if order wasn't completed. otherwise, show resend receipt icon.
			if ( ! $order_completed ) {
				$html .= '<span style="color:orange;font-weight:bold;">' . wpsc_find_purchlog_status_name( $order['status'] ) . '</span>';
			} else {
				$html .= '<span style="color:red;font-weight:bold;">' . wpsc_find_purchlog_status_name( $order['status'] ) . '</span>';

				// add icon to resend purchase receipt
				$args        = array(
					'action'    => self::APP_AJAX_HANDLER,
					'nonce'     => wp_create_nonce( 'hs-wpsc-purchase-receipt' ),
					self::APP_AJAX_HANDLER => 'purchase-receipt',
					'order'     => $order['id'],
				);

				$resend_link = '<a style="float:right;margin-top:2pxt" href="' . add_query_arg( $args, admin_url( 'admin-ajax.php' ) ) . '" target="_blank"><i title="' . __( 'Resend Purchase Receipt', 'wp-e-commerce' ) . '" class="icon-email"></i></a>';
				$html .= $resend_link;
			}

			$html .= '<div class="toggle indent">';
			$html .= '<p><span class="muted">' . $order['date'] . '</span><br/>';
			$html .= wpsc_currency_display( $order['amount'], array( 'display_as_html' => false ) ) . '</p>';

			$html .= '</div></div>';
			$html .= '<div class="divider"></div>';
		}

		return $html;
	}


	/**
	 * Set JSON headers, return the given response string
	 *
	 * @param string $html
	 */
	private function respond( $html ) {
		$response = array( 'html' => $html );

		// clear output, some plugins might have thrown errors by now.
		if ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		die();
	}
}
