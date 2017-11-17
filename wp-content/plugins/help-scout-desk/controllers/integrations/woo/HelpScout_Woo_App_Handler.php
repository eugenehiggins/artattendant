<?php

/**
 * HelpScout Woo integration.
 *
 */
class HelpScout_Woo_App_Handler extends HelpScout_Woo_App {

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
	public function __construct() {
		// explicitly called
		// $this->process();
	}

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
			//exit;
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
		$data = json_decode( $data_string, true );

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
		if ( $expected_signature === $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Get an array of emails belonging to the customer
	 *
	 * @return array
	 */
	private function get_customer_emails() {
		$customer_data = $this->data['customer'];
		$emails = array();

		if ( isset( $customer_data['emails'] ) && is_array( $customer_data['emails'] ) && count( $customer_data['emails'] ) > 1 ) {
			$emails = array_values( $customer_data['emails'] );
		} elseif ( isset( $customer_data['email'] ) ) {
			$emails = array( $customer_data['email'] );
		}

		$emails = apply_filters( 'helpscout_woo_customer_emails', $emails, $this->data );

		if ( count( $emails ) === 0 ) {
			$this->respond( 'No customer email given.' );
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
		$orders = apply_filters( 'helpscout_woo_customer_orders', array(), $this->customer_emails, $this->data );
		if ( ! empty( $orders ) ) {
			return $orders;
		}

		$customer_orders = array();

		foreach ( $this->customer_emails as $customer_email ) {

			// Collect for guest checkout
			$orders = get_posts( array(
				'numberposts' => -1,
				'meta_key'    => '_billing_email',
				'meta_value'  => $customer_email,
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
			) );
			$customer_orders = array_merge( $customer_orders, $orders );

			if ( empty( $orders ) ) {
				$user = get_user_by( 'email', $customer_email );
				if ( ! is_a( $user, 'WP_User' ) ) {
					continue;
				}
				$orders = get_posts( array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $user->ID,
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
				) );
				$customer_orders = array_merge( $customer_orders, $orders );
			}
		}

		return array_unique( $customer_orders , SORT_REGULAR );
	}

	/**
	 * Process the request
	 *  - Find purchase data
	 *  - Generate response
	 *
	 * @TODO: Refactor out loop to find additional order data.
	 *
	 * @link http://developer.helpscout.net/custom-apps/style-guide/ HelpScout Custom Apps Style Guide
	 * @return string
	 */
	private function build_response_html() {

		if ( count( $this->customer_orders ) === 0 ) {
			return apply_filters( 'hsd_woo_app_no_purchases_html', 'No purchase data found', $this );
		}

		$html = '';

		foreach ( $this->customer_orders as $post ) {

			$order = wc_get_order( $post->ID );
			$order_data  = get_post_meta( $post->ID );

			$id             = $post->ID;
			$status         = wc_get_order_status_name( $order->get_status() );
			$date           = date_i18n( 'Y-m-d', strtotime( $order->order_date ) );
			$edit_url		= apply_filters( 'hsd_woo_build_response_html_edit_url', sprintf( '%spost.php?post=%s&action=edit', get_admin_url(), $id ) );
			$link           = sprintf( '<a target="_blank" href="%s">#%s</a>', $edit_url, $post->ID );
			$amount         = $order->get_formatted_order_total();

			$payment_gateway = wc_get_payment_gateway_by_order( $order );
			$gateway_name             = false !== $payment_gateway ? ( ! empty( $payment_gateway->method_title ) ? $payment_gateway->method_title : $payment_gateway->get_title() ) : __( 'Payment Gateway', 'woocommerce' );
			$order_method = $gateway_name;

			$order_item_html      = array();
			$items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
			if ( is_array( $items ) && count( $items ) > 0 ) {

				foreach ( $items as $item_id => $item ) {

					$_product  = $order->get_product_from_item( $item );
					$item_meta = $order->get_item_meta( $item_id );

					if ( ! $item_id || empty( $item_id ) ) {
						continue;
					}

					$item_details = '<div style="background: #fefefe;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;margin-bottom: 1em;padding: .5em .7em;">';

						// generate item string
						$item_details .= '<strong>' . esc_html( $item['name'] ) . '</strong><br />';
						$item_details .= wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_order_currency() ) );

					$item_details .= '</div>';
					$order_item_html[] = $item_details;
				}
			}

			$open_class = '';
			// open completed purchases by default
			if ( $order->get_status() === 'completed' ) {
				$open_class = ' open';
			}

			$html .= '<div class="toggleGroup' . $open_class . '">';
			$html .= '<strong><i class="icon-cart"></i> ' . $link . '</strong> <a class="toggleBtn"><i class="icon-arrow"></i></a>';

			if ( $order->get_status() !== 'completed' ) {
				$html .= '<span style="color:orange;font-weight:bold;">' . $status . '</span>';
			}

			$html .= '<div class="toggle indent">';
			$html .= '<p><span class="muted">' . $date . '</span><br/>';
			$html .= trim( $amount ) . ( ( isset( $order_method ) && '' !== $order_method ) ?  ' - ' . $order_method : '' ) . '</p>';

			if ( ! empty( $order_item_html ) && count( $order_item_html ) > 0 ) {
				// buid list of items with license keys
				$html .= '<ul class="unstyled">';
				foreach ( $order_item_html as $item ) {
					$html .= '<li>' . $item . '</li>';
				}
				$html .= '</ul>';
			}
			$html .= '</div></div>';
			$html .= '<div class="divider"></div>';
		}

		return apply_filters( 'hsd_woo_build_response_html', $html, $this );
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
