<?php
/*
Plugin Name: Easy Digital Downloads - Simple Shipping
Plugin URI: http://easydigitaldownloads.com/extension/simple-shipping
Description: Provides the ability to charge simple shipping fees for physical products in EDD
Version: 2.2.1
Author: Pippin Williamson
Author URI:  http://pippinsplugins.com
Contributors: mordauk
Text Domain: edd-simple-shipping
Domain Path: languages
*/

class EDD_Simple_Shipping {

	private static $instance;

	/**
	 * Flag for domestic / international shipping
	 *
	 * @since 1.0
	 *
	 * @access protected
	 */
	protected $is_domestic = true;

	/**
	 * Flag for whether Frontend Submissions is enabled
	 *
	 * @since 2.0
	 *
	 * @access protected
	 */
	protected $is_fes = false;

	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new EDD_Simple_Shipping();

		return self::$instance;
	}

	/**
	 * Class constructor.  Includes constants, includes and init method.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		define( 'EDD_SIMPLE_SHIPPING_VERSION', '2.2.1' );

		$this->init();

	}


	/**
	 * Run action and filter hooks.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return void
	 */
	protected function init() {

		if( ! class_exists( 'Easy_Digital_Downloads' ) )
			return; // EDD not present

		global $edd_options;

		// Check for dependent plugins
		$this->plugins_check();

		// internationalization
		add_action( 'init', array( $this, 'textdomain' ) );

		// register our license key settings
		add_filter( 'edd_settings_general', array( $this, 'settings' ), 1 );

		// Add the meta box fields to Download Configuration
		add_action( 'edd_meta_box_fields', array( $this, 'metabox' ), 10 );

		// Add our variable pricing shipping header
		add_action( 'edd_download_price_table_head', array( $this, 'price_header' ), 700 );

		// Add our variable pricing shipping options
		add_action( 'edd_download_price_table_row', array( $this, 'price_row' ), 700, 3 );

		// Add our meta fields to the EDD save routine
		add_filter( 'edd_metabox_fields_save', array( $this, 'meta_fields_save' ) );

		// Save shipping details on edit
		add_action( 'edd_updated_edited_purchase', array( $this, 'save_payment' ) );

		// Check shipping rate when billing/shipping fields are changed
		add_action( 'wp_ajax_edd_get_shipping_rate', array( $this, 'ajax_shipping_rate' ) );
		add_action( 'wp_ajax_nopriv_edd_get_shipping_rate', array( $this, 'ajax_shipping_rate' ) );

		// Apply shipping costs to the checkout
		add_action( 'init', array( $this, 'apply_shipping_fees' ) );

		// Display the shipping address fields
		add_action( 'edd_purchase_form_after_cc_form', array( $this, 'address_fields' ), 999 );

		// Check for errors on checkout submission
		add_action( 'edd_checkout_error_checks', array( $this, 'error_checks' ), 10, 2 );

		// Store shipping info
		add_filter( 'edd_purchase_data_before_gateway', array( $this, 'set_shipping_info' ), 10, 2 );

		// Send shipping info to PayPal
		add_filter( 'edd_paypal_redirect_args', array( $this, 'send_shipping_to_paypal' ), 10, 2 );

		// Display the user's shipping info in the View Details popup
		add_action( 'edd_view_order_details_billing_after', array( $this, 'show_shipping_details' ), 10 );

		// Set payment as not shipped
		add_action( 'edd_insert_payment', array( $this, 'set_as_not_shipped' ), 10, 2 );

		// Add the shipped status column
		add_filter( 'edd_payments_table_columns', array( $this, 'add_shipped_column' ) );

		// Make our Shipped? column sortable
		add_filter( 'edd_payments_table_sortable_columns', array( $this, 'add_sortable_column' ) );

		// Sort the payment history by orders that have been shipped or not
		add_filter( 'edd_get_payments_args', array( $this, 'sort_payments' ) );

		// Display our Shipped? column value
		add_filter( 'edd_payments_table_column', array( $this, 'display_shipped_column_value' ), 10, 3 );

		// Add our Shipped? checkbox to the edit payment screen
		add_action( 'edd_edit_payment_bottom', array( $this, 'edit_payment_option' ) );

		// Modify the admin sales notice
		add_filter( 'edd_sale_notification', array( $this, 'admin_sales_notice' ), 10, 3 );

		// Add a new box to the export screen
		add_action( 'edd_reports_tab_export_content_bottom', array( $this, 'show_export_options' ) );

		add_action( 'edd_unshipped_orders_export', array( $this, 'do_export' ) );

		add_filter( 'edd_payments_table_bulk_actions', array( $this, 'register_bulk_action' ) );
		add_action( 'edd_payments_table_do_bulk_action', array( $this, 'process_bulk_actions' ), 10, 2 );

		if( $this->is_fes ) {

			/**
			 * Frontend Submissions actions
			 */

			add_action( 'fes-order-table-column-title', array( $this, 'shipped_column_header' ), 10 );
			add_action( 'fes-order-table-column-value', array( $this, 'shipped_column_value' ), 10 );
			add_action( 'edd_payment_receipt_after', array( $this, 'payment_receipt_after' ), 10, 2 );
			add_action( 'edd_toggle_shipped_status', array( $this, 'frontend_toggle_shipped_status' ) );

			if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
				add_action( 'fes_load_fields_require',  array( $this, 'edd_fes_simple_shipping' ) );
			} else {
				add_action( 'fes_custom_post_button', array( $this, 'edd_fes_simple_shipping_field_button' ) );
				add_action( 'fes_admin_field_edd_simple_shipping', array( $this, 'edd_fes_simple_shipping_admin_field' ), 10, 3 );
				add_filter( 'fes_formbuilder_custom_field', array( $this, 'edd_fes_simple_shipping_formbuilder_is_custom_field' ), 10, 2 );
				add_action( 'fes_submit_submission_form_bottom', array( $this, 'edd_fes_simple_shipping_save_custom_fields' ) );
				add_action( 'fes_render_field_edd_simple_shipping', array( $this, 'edd_fes_simple_shipping_field' ), 10, 3 );
			}

		}

		// auto updater
		if( is_admin() ) {

			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Simple Shipping', EDD_SIMPLE_SHIPPING_VERSION, 'Pippin Williamson', 'edd_simple_shipping_license_key' );
			}
		}
	}


	/**
	 * Load plugin text domain
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public static function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'edd_simple_shipping_lang_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'edd-simple-shipping', false, $lang_dir );

	}

	/**
	 * Determine if dependent plugins are loaded and set flags appropriately
	 *
	 * @since 2.0
	 *
	 * @access private
	 * @return void
	 */
	public function plugins_check() {

		if( class_exists( 'EDD_Front_End_Submissions' ) ) {
			$this->is_fes = true;
		}

	}


	/**
	 * Render the extra meta box fields
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function metabox( $post_id = 0 ) {

		global $edd_options;

		$enabled       = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$display       = $enabled ? '' : 'style="display:none;"';
		$domestic      = get_post_meta( $post_id, '_edd_shipping_domestic', true );
		$international = get_post_meta( $post_id, '_edd_shipping_international', true );
		?>
		<div id="edd_simple_shipping">
			<script type="text/javascript">jQuery(document).ready(function($) {$('#edd_enable_shipping').on('click',function() {$('#edd_simple_shipping_fields,.edd_prices_shipping').toggle();});});</script>
			<p><strong><?php _e( 'Shipping Options', 'edd-simple-shipping' ); ?></strong></p>
			<p>
				<label for="edd_enable_shipping">
					<input type="checkbox" name="_edd_enable_shipping" id="edd_enable_shipping" value="1"<?php checked( 1, $enabled ); ?>/>
					<?php printf( __( 'Enable shipping for this %s', 'edd-simple-shipping' ), edd_get_label_singular() ); ?>
				</label>
			</p>
			<div id="edd_simple_shipping_fields" <?php echo $display; ?>>
				<table>
					<tr>
						<td>
							<label for="edd_shipping_domestic"><?php _e( 'Domestic Rate:', 'edd-simple-shipping' ); ?>&nbsp;</label>
						</td>
						<td>
							<?php if( ! isset( $edd_options['currency_position'] ) || $edd_options['currency_position'] == 'before' ) : ?>
								<span><?php echo edd_currency_filter( '' ); ?></span><input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="_edd_shipping_domestic"/>
							<?php else : ?>
								<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="_edd_shipping_domestic"/><?php echo edd_currency_filter( '' ); ?>
							<?php endif; ?>
						</td>
						<td>
							<label for="edd_shipping_international"><?php _e( 'International Rate:', 'edd-simple-shipping' ); ?>&nbsp;</label>
						</td>
						<td>
							<?php if( ! isset( $edd_options['currency_position'] ) || $edd_options['currency_position'] == 'before' ) : ?>
								<span><?php echo edd_currency_filter( '' ); ?></span><input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_international" name="_edd_shipping_international"/>
							<?php else : ?>
								<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_international" name="_edd_shipping_international"/><?php echo edd_currency_filter( '' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}


	/**
	 *Add the table header cell for price shipping
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	function price_header( $post_id = 0 ) {
		$enabled       = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$display       = $enabled ? '' : 'style="display:none;"';
		?>
		<th class="edd_prices_shipping"<?php echo $display; ?>><?php _e( 'Shipping', 'edd-simple-shipping' ); ?></th>
	<?php
	}


	/**
	 *Add the table cell for price shipping
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	function price_row( $post_id = 0, $price_key = 0, $args = array() ) {
		$enabled       = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$display       = $enabled ? '' : 'style="display:none;"';
		$prices        = edd_get_variable_prices( $post_id );
		$shipping      = isset( $prices[ $price_key ]['shipping'] );
		?>
		<td class="edd_prices_shipping"<?php echo $display; ?>>
			<label for="edd_variable_prices[<?php echo $price_key; ?>][shipping]">
				<input type="checkbox" value="1"<?php checked( true, $shipping ); ?> id="edd_variable_prices[<?php echo $price_key; ?>][shipping]" name="edd_variable_prices[<?php echo $price_key; ?>][shipping]" style="float:left;width:auto;margin:2px 5px 0 0;"/>
				<span><?php _e( 'Check to enable shipping costs for this price.', 'edd-simple-shipping' ); ?></span>
			</label>
		</td>
	<?php
	}


	/**
	 * Save our extra meta box fields
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	public function meta_fields_save( $fields ) {

		// Tell EDD to save our extra meta fields
		$fields[] = '_edd_enable_shipping';
		$fields[] = '_edd_shipping_domestic';
		$fields[] = '_edd_shipping_international';
		return $fields;

	}

	/**
	 * Save the shipping details on payment edit
	 *
	 * @since 1.5
	 *
	 * @access private
	 * @return void
	 */
	public function save_payment( $payment_id = 0 ) {

		$address = isset( $_POST['edd-payment-shipping-address'] ) ? $_POST['edd-payment-shipping-address'] : false;
		if( ! $address )
			return;

		$meta      = edd_get_payment_meta( $payment_id );
		$user_info = maybe_unserialize( $meta['user_info'] );

		$user_info['shipping_info'] = $address[0];

		$meta['user_info'] = $user_info;
		update_post_meta( $payment_id, '_edd_payment_meta', $meta );

		if( isset( $_POST['edd-payment-shipped'] ) ) {
			update_post_meta( $payment_id, '_edd_payment_shipping_status', '2' );
		} elseif( get_post_meta( $payment_id, '_edd_payment_shipping_status', true ) ) {
			update_post_meta( $payment_id, '_edd_payment_shipping_status', '1' );
		}
	}

	/**
	 * Determine if a product has snipping enabled
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function item_has_shipping( $item_id = 0, $price_id = 0 ) {
		$enabled          = get_post_meta( $item_id, '_edd_enable_shipping', true );
		$variable_pricing = edd_has_variable_prices( $item_id );

		if( $variable_pricing && ! $this->price_has_shipping( $item_id, $price_id ) )
			$enabled = false;

		return (bool) apply_filters( 'edd_simple_shipping_item_has_shipping', $enabled, $item_id );
	}


	/**
	 * Determine if a price option has snipping enabled
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function price_has_shipping( $item_id = 0, $price_id = 0 ) {
		$prices = edd_get_variable_prices( $item_id );
		$ret    = isset( $prices[ $price_id ]['shipping'] );
		return (bool) apply_filters( 'edd_simple_shipping_price_hasa_shipping', $ret, $item_id, $price_id );
	}


	/**
	 * Determine if shipping costs need to be calculated for the cart
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function cart_needs_shipping() {
		$cart_contents = edd_get_cart_contents();
		$ret = false;
		if( is_array( $cart_contents ) ) {
			foreach( $cart_contents as $item ) {
				$price_id = isset( $item['options']['price_id'] ) ? (int) $item['options']['price_id'] : null;
				if( $this->item_has_shipping( $item['id'], $price_id ) ) {
					$ret = true;
					break;
				}
			}
		}
		return (bool) apply_filters( 'edd_simple_shipping_cart_needs_shipping', $ret );
	}


	/**
	 * Get the base country (where the store is located)
	 *
	 * This is used for determining if customer should be charged domestic or international shipping
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_base_region( $download_id = 0 ) {

		global $edd_options;

		if( ! empty( $download_id ) ) {

			$author  = get_post_field( 'post_author', $download_id );
			$country = get_user_meta( $author, 'vendor_country', true );
			if( $country ) {
				$countries   = edd_get_country_list();
				$code        = array_search( $country, $countries );
				if( false !== $code ) {
					$base_region = $code;
				}
			}

		}

		$base_region = isset( $base_region ) ? $base_region : edd_get_option( 'edd_simple_shipping_base_country', 'US' );

		return $base_region;

	}


	/**
	 * Update the shipping costs via ajax
	 *
	 * This fires when the customer changes the country they are shipping to
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function ajax_shipping_rate() {

		// Calculate new shipping
		$shipping = $this->apply_shipping_fees();

		ob_start();
		edd_checkout_cart();
		$cart = ob_get_clean();

		$response = array(
			'html'  => $cart,
			'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
		);

		echo json_encode( $response );

		die();
	}


	/**
	 * Apply the shipping fees to the cart
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function apply_shipping_fees() {

		$this->remove_shipping_fees();

		if( ! $this->cart_needs_shipping() ) {
			return;
		}

		$cart_contents = edd_get_cart_contents();

		if( ! is_array( $cart_contents ) ) {
			return;
		}

		$amount = 0.00;

		foreach( $cart_contents as $key => $item ) {

			$price_id = isset( $item['options']['price_id'] ) ? (int) $item['options']['price_id'] : null;

			if( ! $this->item_has_shipping( $item['id'], $price_id ) ) {

				continue;

			}

			if( is_user_logged_in() && empty( $_POST['country'] ) ) {

				$address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
				if( isset( $address['country'] ) && $address['country'] != $this->get_base_region( $item['id'] ) ) {
					$this->is_domestic = false;
				} else {
					$this->is_domestic = true;
				}

			} else {

				$country = ! empty( $_POST['country'] ) ? $_POST['country'] : $this->get_base_region();

				if( $country != $this->get_base_region( $item['id'] ) ) {
					$this->is_domestic = false;
				} else {
					$this->is_domestic = true;
				}
			}

			if( $this->is_domestic ) {

				$amount = (float) get_post_meta( $item['id'], '_edd_shipping_domestic', true );

			} else {

				$amount = (float) get_post_meta( $item['id'], '_edd_shipping_international', true );

			}

			if( $amount > 0 ) {

				EDD()->fees->add_fee( array(
					'amount'      => $amount,
					'label'       => sprintf( __( '%s Shipping', 'edd-simple-shipping' ), get_the_title( $item['id'] ) ),
					'id'          => 'simple_shipping_' . $key,
					'download_id' => $item['id'],
					'price_id'	  => isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null
				) );

			}

		}

	}

	/**
	 * Removes all shipping fees from the cart
	 *
	 * @since 2.1
	 *
	 * @access public
	 * @return void
	 */
	public function remove_shipping_fees() {

		$fees = EDD()->fees->get_fees( 'fee' );
		if( empty( $fees ) ) {
			return;
		}

		foreach( $fees as $key => $fee ) {

			if( false === strpos( $key, 'simple_shipping' ) ) {
				continue;
			}

			unset( $fees[ $key ] );

		}

		EDD()->session->set( 'edd_cart_fees', $fees );

	}


	/**
	 * Determine if the shipping fields should be displayed
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function needs_shipping_fields() {
		return $this->cart_needs_shipping();

	}


	/**
	 * Determine if the current payment method has billing fields
	 *
	 * If no billing fields are present, the shipping fields are always displayed
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function has_billing_fields() {

		$did_action = did_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		if( ! $did_action && edd_use_taxes() )
			$did_action = did_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_tax_fields' );

		// Have to assume all gateways are using the default CC fields (they should be)
		return ( $did_action || isset( $_POST['card_address'] ) );

	}


	/**
	 * Shipping info fields
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function address_fields() {

		if( ! $this->needs_shipping_fields() )
			return;

		$display = $this->has_billing_fields() ? ' style="display:none;"' : '';

		ob_start();
		?>
		<script type="text/javascript">var edd_global_vars; jQuery(document).ready(function($) {
				$('body').on('change', 'select[name=shipping_country],select[name=billing_country]',function() {

					var billing = true;

					if( $('select[name=billing_country]').length && ! $('#edd_simple_shipping_show').is(':checked') ) {
						var val = $('select[name=billing_country]').val();
					} else {
						var val = $('select[name=shipping_country]').val();
						billing = false;
					}

					if( billing && edd_global_vars.taxes_enabled == '1' )
						return; // EDD core will recalculate on billing address change if taxes are enabled

					if( val == 'US' ) {
						$('#shipping_state_other').hide();$('#shipping_state_us').show();$('#shipping_state_ca').hide();
					} else if(  val =='CA') {
						$('#shipping_state_other').hide();$('#shipping_state_us').hide();$('#shipping_state_ca').show();
					} else {
						$('#shipping_state_other').show();$('#shipping_state_us').hide();$('#shipping_state_ca').hide();
					}

					var postData = {
						action: 'edd_get_shipping_rate',
						country:  val
					};

					$.ajax({
						type: "POST",
						data: postData,
						dataType: "json",
						url: edd_global_vars.ajaxurl,
						success: function (response) {
							$('#edd_checkout_cart').replaceWith(response.html);
							$('.edd_cart_amount').each(function() {
								$(this).text(response.total);
							});
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
				});

				$('body').on('edd_taxes_recalculated', function( event, data ) {

					if( $('#edd_simple_shipping_show').is(':checked') )
						return;

					var postData = {
						action: 'edd_get_shipping_rate',
						country: data.postdata.billing_country,
						state: data.postdata.state
					};
					$.ajax({
						type: "POST",
						data: postData,
						dataType: "json",
						url: edd_global_vars.ajaxurl,
						success: function (response) {
							if( response ) {

								$('#edd_checkout_cart').replaceWith(response.html);
								$('.edd_cart_amount').each(function() {
									$(this).text(response.total);
								});

							} else {
								if ( window.console && window.console.log ) {
									console.log( response );
								}
							}
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				});

				$('select#edd-gateway, input.edd-gateway').change( function (e) {
					var postData = {
						action: 'edd_get_shipping_rate',
						country: 'US' // default
					};
					$.ajax({
						type: "POST",
						data: postData,
						dataType: "json",
						url: edd_global_vars.ajaxurl,
						success: function (response) {
							$('#edd_checkout_cart').replaceWith(response.html);
							$('.edd_cart_amount').each(function() {
								$(this).text(response.total);
							});
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
				});
				$('#edd_simple_shipping_show').change(function() {
					$('#edd_simple_shipping_fields_wrap').toggle();
				});
			});</script>

		<div id="edd_simple_shipping">
			<?php if( $this->has_billing_fields() ) : ?>
				<fieldset id="edd_simple_shipping_diff_address">
					<label for="edd_simple_shipping_show">
						<input type="checkbox" id="edd_simple_shipping_show" name="edd_use_different_shipping" value="1"/>
						<?php _e( 'Ship to Different Address?', 'edd-simple-shipping' ); ?>
					</label>
				</fieldset>
			<?php endif; ?>
			<div id="edd_simple_shipping_fields_wrap"<?php echo $display; ?>>
				<fieldset id="edd_simple_shipping_fields">
					<?php do_action( 'edd_shipping_address_top' ); ?>
					<legend><?php _e( 'Shipping Details', 'edd-simple-shipping' ); ?></legend>
					<p id="edd-shipping-address-wrap">
						<label class="edd-label"><?php _e( 'Shipping Address', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The address to ship your purchase to.', 'edd-simple-shipping' ); ?></span>
						<input type="text" name="shipping_address" class="shipping-address edd-input" placeholder="<?php _e( 'Address line 1', 'edd-simple-shipping' ); ?>"/>
					</p>
					<p id="edd-shipping-address-2-wrap">
						<label class="edd-label"><?php _e( 'Shipping Address Line 2', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your shipping address.', 'edd-simple-shipping' ); ?></span>
						<input type="text" name="shipping_address_2" class="shipping-address-2 edd-input" placeholder="<?php _e( 'Address line 2', 'edd-simple-shipping' ); ?>"/>
					</p>
					<p id="edd-shipping-city-wrap">
						<label class="edd-label"><?php _e( 'Shipping City', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The city for your shipping address.', 'edd-simple-shipping' ); ?></span>
						<input type="text" name="shipping_city" class="shipping-city edd-input" placeholder="<?php _e( 'City', 'edd-simple-shipping' ); ?>"/>
					</p>
					<p id="edd-shipping-country-wrap">
						<label class="edd-label"><?php _e( 'Shipping Country', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The country for your shipping address.', 'edd-simple-shipping' ); ?></span>
						<select name="shipping_country" class="shipping-country edd-select">
							<?php
							$countries = edd_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . $country_code . '">' . $country . '</option>';
							}
							?>
						</select>
					</p>
					<p id="edd-shipping-state-wrap">
						<label class="edd-label"><?php _e( 'Shipping State / Province', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The state / province for your shipping address.', 'edd-simple-shipping' ); ?></span>
						<input type="text" size="6" name="shipping_state_other" id="shipping_state_other" class="shipping-state edd-input" placeholder="<?php _e( 'State / Province', 'edd-simple-shipping' ); ?>" style="display:none;"/>
						<select name="shipping_state_us" id="shipping_state_us" class="shipping-state edd-select">
							<?php
							$states = edd_get_states_list();
							foreach( $states as $state_code => $state ) {
								echo '<option value="' . $state_code . '">' . $state . '</option>';
							}
							?>
						</select>
						<select name="shipping_state_ca" id="shipping_state_ca" class="shipping-state edd-select" style="display: none;">
							<?php
							$provinces = edd_get_provinces_list();
							foreach( $provinces as $province_code => $province ) {
								echo '<option value="' . $province_code . '">' . $province . '</option>';
							}
							?>
						</select>
					</p>
					<p id="edd-shipping-zip-wrap">
						<label class="edd-label"><?php _e( 'Shipping Zip / Postal Code', 'edd-simple-shipping' ); ?></label>
						<span class="edd-description"><?php _e( 'The zip / postal code for your shipping address.', 'edd-simple-shipping' ); ?></span>
						<input type="text" size="4" name="shipping_zip" class="shipping-zip edd-input" placeholder="<?php _e( 'Zip / Postal code', 'edd-simple-shipping' ); ?>"/>
					</p>
					<?php do_action( 'edd_shipping_address_bottom' ); ?>
				</fieldset>
			</div>
		</div>
		<?php 	echo ob_get_clean();
	}


	/**
	 * Perform error checks during checkout
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function error_checks( $valid_data, $post_data ) {

		// Only perform error checks if we have a product that needs shipping
		if( ! $this->cart_needs_shipping() ) {
			return;
		}

		// Check to see if shipping is different than billing
		if( isset( $post_data['edd_use_different_shipping'] ) || ! $this->has_billing_fields() ) {

			// Shipping address is different

			if( empty( $post_data['shipping_address'] ) ) {
				edd_set_error( 'missing_address', __( 'Please enter a shipping address', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['shipping_city'] ) ) {
				edd_set_error( 'missing_city', __( 'Please enter a city for shipping', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['shipping_zip'] ) ) {
				edd_set_error( 'missing_zip', __( 'Please enter a zip/postal code for shipping', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['shipping_country'] ) ) {
				edd_set_error( 'missing_country', __( 'Please select your country', 'edd-simple-shipping' ) );
			}

			if( 'US' == $post_data['shipping_country'] ) {

				if( empty( $post_data['shipping_state_us'] ) ) {
					edd_set_error( 'missing_state', __( 'Please select your state', 'edd-simple-shipping' ) );
				}

			} elseif( 'CA' == $post_data['shipping_country'] ) {

				if( empty( $post_data['shipping_state_ca'] ) ) {
					edd_set_error( 'missing_province', __( 'Please select your province', 'edd-simple-shipping' ) );
				}

			}

		} else {

			// Shipping address is the same as billing
			if( empty( $post_data['card_address'] ) ) {
				edd_set_error( 'missing_address', __( 'Please enter a shipping address', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['card_city'] ) ) {
				edd_set_error( 'missing_city', __( 'Please enter a city for shipping', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['card_zip'] ) ) {
				edd_set_error( 'missing_zip', __( 'Please enter a zip/postal code for shipping', 'edd-simple-shipping' ) );
			}

			if( 'US' == $post_data['billing_country'] ) {

				if( empty( $post_data['card_state'] ) ) {
					edd_set_error( 'missing_state', __( 'Please select your state', 'edd-simple-shipping' ) );
				}

			} elseif( 'CA' == $post_data['billing_country'] ) {

				if( empty( $post_data['card_state'] ) ) {
					edd_set_error( 'missing_province', __( 'Please select your province', 'edd-simple-shipping' ) );
				}

			}

		}

	}


	/**
	 * Attach our shipping info to the payment gateway daya
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function set_shipping_info( $purchase_data, $valid_data ) {

		if( ! $this->cart_needs_shipping() ) {
			return $purchase_data;
		}

		$shipping_info = array();

		// Check to see if shipping is different than billing
		if( isset( $_POST['edd_use_different_shipping'] ) || ! $this->has_billing_fields() ) {

			$shipping_info['address']  = sanitize_text_field( $_POST['shipping_address'] );
			$shipping_info['address2'] = sanitize_text_field( $_POST['shipping_address_2'] );
			$shipping_info['city']     = sanitize_text_field( $_POST['shipping_city'] );
			$shipping_info['zip']      = sanitize_text_field( $_POST['shipping_zip'] );
			$shipping_info['country']  = sanitize_text_field( $_POST['shipping_country'] );

			// Shipping address is different
			switch ( $_POST['shipping_country'] ) :
				case 'US' :
					$shipping_info['state'] = isset( $_POST['shipping_state_us'] )	 ? sanitize_text_field( $_POST['shipping_state_us'] ) 	 : '';
					break;
				case 'CA' :
					$shipping_info['state'] = isset( $_POST['shipping_state_ca'] )	 ? sanitize_text_field( $_POST['shipping_state_ca'] ) 	 : '';
					break;
				default :
					$shipping_info['state'] = isset( $_POST['shipping_state_other'] ) ? sanitize_text_field( $_POST['shipping_state_other'] ) : '';
					break;
			endswitch;

		} else {

			$shipping_info['address']  = sanitize_text_field( $_POST['card_address'] );
			$shipping_info['address2'] = sanitize_text_field( $_POST['card_address_2'] );
			$shipping_info['city']     = sanitize_text_field( $_POST['card_city'] );
			$shipping_info['zip']      = sanitize_text_field( $_POST['card_zip'] );
			$shipping_info['state']    = sanitize_text_field( $_POST['card_state'] );
			$shipping_info['country']  = sanitize_text_field( $_POST['billing_country'] );

		}

		$purchase_data['user_info']['shipping_info'] = $shipping_info;

		return $purchase_data;

	}


	/**
	 * Sets up the shipping details for PayPal
	 *
	 * This makes it possible to use the Print Shipping Label feature in PayPal
	 *
	 * @since 1.1
	 *
	 * @access public
	 * @return array
	 */
	public function send_shipping_to_paypal( $paypal_args = array(), $purchase_data = array() ) {

		if( ! $this->cart_needs_shipping() ) {
			return $paypal_args;
		}

		$shipping_info = $purchase_data['user_info']['shipping_info'];

		$paypal_args['no_shipping'] = '0';
		$paypal_args['address1']    = $shipping_info['address'];
		$paypal_args['address2']    = $shipping_info['address2'];
		$paypal_args['city']        = $shipping_info['city'];
		$paypal_args['state']       = $shipping_info['country'] == 'US' ? $shipping_info['state'] : null;
		$paypal_args['country']     = $shipping_info['country'];
		$paypal_args['zip']         = $shipping_info['zip'];


		return $paypal_args;

	}


	/**
	 * Set a purchase as not shipped
	 *
	 * This is so that we can grab all purchases in need of being shipped
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_as_not_shipped( $payment_id = 0, $payment_data = array() ) {

		$shipping_info = ! empty( $payment_data['user_info']['shipping_info'] ) ? $payment_data['user_info']['shipping_info'] : false;

		if( ! $shipping_info ) {
			return;
		}

		// Indicate that this purchase needs shipped
		update_post_meta( $payment_id, '_edd_payment_shipping_status', '1' );

	}


	/**
	 * Display shipping details in the View Details popup
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function show_shipping_details( $payment_id = 0 ) {

		if( empty( $payment_id ) ) {
			$payment_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		}

		$user_info     = edd_get_payment_meta_user_info( $payment_id );

		$address = ! empty( $user_info['shipping_info'] ) ? $user_info['shipping_info'] : false;

		if( ! $address )
			return;

		$status  = get_post_meta( $payment_id, '_edd_payment_shipping_status', true );

		$shipped = $status == '2' ? true : false;
		?>
		<div id="edd-shipping-details" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Shipping Address', 'edd' ); ?></span>
			</h3>
			<div class="inside edd-clearfix">

				<div id="edd-order-shipping-address">

					<div class="order-data-address">
						<div class="data column-container">
							<div class="column">
								<p>
									<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][address]" value="<?php esc_attr_e( $address['address'] ); ?>" class="medium-text" />
								</p>
								<p>
									<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][address2]" value="<?php esc_attr_e( $address['address2'] ); ?>" class="medium-text" />
								</p>

							</div>
							<div class="column">
								<p>
									<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][city]" value="<?php esc_attr_e( $address['city'] ); ?>" class="medium-text"/>

								</p>
								<p>
									<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][zip]" value="<?php esc_attr_e( $address['zip'] ); ?>" class="medium-text"/>

								</p>
							</div>
							<div class="column">
								<p id="edd-order-address-country-wrap">
									<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'edd' ); ?></strong><br/>
									<?php
									echo EDD()->html->select( array(
										'options'          => edd_get_country_list(),
										'name'             => 'edd-payment-shipping-address[0][country]',
										'selected'         => $address['country'],
										'show_option_all'  => false,
										'show_option_none' => false
									) );
									?>
								</p>
								<p id="edd-order-address-state-wrap">
									<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'edd' ); ?></strong><br/>
									<?php
									$states = edd_get_shop_states( $address['country'] );
									if( ! empty( $states ) ) {
										echo EDD()->html->select( array(
											'options'          => $states,
											'name'             => 'edd-payment-shipping-address[0][state]',
											'selected'         => $address['state'],
											'show_option_all'  => false,
											'show_option_none' => false
										) );
									} else { ?>
										<input type="text" name="edd-payment-shipping-address[0][state]" value="<?php esc_attr_e( $address['state'] ); ?>" class="medium-text"/>
									<?php
									} ?>
								</p>
							</div>
						</div>
						<label for="edd-payment-shipped">
							<input type="checkbox" id="edd-payment-shipped" name="edd-payment-shipped" value="1"<?php checked( $shipped, true ); ?>/>
							<?php _e( 'Check if this purchase has been shipped.', 'edd-simple-shipping' ); ?>
						</label>
					</div>
				</div><!-- /#edd-order-address -->

				<?php do_action( 'edd_payment_shipping_details', $payment_id ); ?>

			</div><!-- /.inside -->
		</div><!-- /#edd-shipping-details -->
	<?php
	}


	/**
	 * Add a shipped status column to Payment History
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function add_shipped_column( $columns ) {
		// Force the Shipped column to be placed just before Status
		unset( $columns['status'] );
		$columns['shipped'] = __( 'Shipped?', 'edd-simple-shipping' );
		$columns['status']  = __( 'Status', 'edd-simple-shipping' );
		return $columns;
	}


	/**
	 * Make the Shipped? column sortable
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function add_sortable_column( $columns ) {
		$columns['shipped'] = array( 'shipped', false );
		return $columns;
	}


	/**
	 * Sort payment history by shipped status
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function sort_payments( $args ) {

		if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'shipped' ) {

			$args['orderby'] = 'meta_value';
			$args['meta_key'] = '_edd_payment_shipping_status';

		}

		return $args;

	}


	/**
	 * Display the shipped status
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return string
	 */
	public function display_shipped_column_value( $value = '', $payment_id = 0, $column_name = '' ) {

		if( $column_name == 'shipped' ) {
			$shipping_status = get_post_meta( $payment_id, '_edd_payment_shipping_status', true );
			if( $shipping_status == '1' ) {
				$value = __( 'No', 'edd-simple-shipping' );
			} elseif( $shipping_status == '2' ) {
				$value = __( 'Yes', 'edd-simple-shipping' );
			} else {
				$value = __( 'N/A', 'edd-simple-shipping' );
			}
		}
		return $value;
	}


	/**
	 * Add the shipping info to the admin sales notice
	 *
	 * @access      public
	 * @since       1.1
	 * @return      string
	 */
	public function admin_sales_notice( $email = '', $payment_id = 0, $payment_data = array() ) {

		$shipped = get_post_meta( $payment_id, '_edd_payment_shipping_status', true );

		// Only modify the email if shipping info needs to be added
		if( '1' == $shipped ) {

			$user_info     = maybe_unserialize( $payment_data['user_info'] );
			$shipping_info = $user_info['shipping_info'];

			$email .= "<p><strong>" . __( 'Shipping Details:', 'edd-simple-shipping' ) . "</strong></p>";
			$email .= __( 'Address:', 'edd-simple-shipping' ) . " " . $shipping_info['address'] . "<br/>";
			$email .= __( 'Address Line 2:', 'edd-simple-shipping' ) . " " . $shipping_info['address2'] . "<br/>";
			$email .= __( 'City:', 'edd-simple-shipping' ) . " " . $shipping_info['city'] . "<br/>";
			$email .= __( 'Zip/Postal Code:', 'edd-simple-shipping' ) . " " . $shipping_info['zip'] . "<br/>";
			$email .= __( 'Country:', 'edd-simple-shipping' ) . " " . $shipping_info['country'] . "<br/>";
			$email .= __( 'State:', 'edd-simple-shipping' ) . " " . $shipping_info['state'] . "<br/>";

		}

		return $email;

	}


	/**
	 * Add the export unshipped orders box to the export screen
	 *
	 * @access      public
	 * @since       1.2
	 * @return      void
	 */
	public function show_export_options() {
		?>
		<div class="postbox">
			<h3><span><?php _e( 'Export Unshipped Orders to CSV', 'edd-simple-shipping' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Download a CSV of all unshipped orders.', 'edd-simple-shipping' ); ?></p>
				<p><a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'unshipped_orders_export' ) ), 'edd_export_unshipped_orders' ); ?>"><?php _e( 'Generate CSV', 'edd-simple-shipping' ) ; ?></a></p>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	<?php
	}


	/**
	 * Trigger the CSV export
	 *
	 * @access      public
	 * @since       1.2
	 * @return      void
	 */
	public function do_export() {
		require_once dirname( __FILE__ ) . '/class-shipping-export.php';

		$export = new EDD_Simple_Shipping_Export();

		$export->export();
	}

	/**
	 * Add our extension settings
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function settings( $settings ) {
		$license_settings = array(
			array(
				'id' => 'edd_simple_shipping_license_header',
				'name' => '<strong>' . __( 'Simple Shipping', 'edd-simple-shipping' ) . '</strong>',
				'desc' => '',
				'type' => 'header',
				'size' => 'regular'
			),
			array(
				'id' => 'edd_simple_shipping_base_country',
				'name' => __( 'Base Region', 'edd-simple-shipping'),
				'desc' => __( 'Choose the country your store is based in', 'edd-simple-shipping'),
				'type'  => 'select',
				'options' => edd_get_country_list()
			)
		);

		return array_merge( $settings, $license_settings );
	}

	/**
	 * Register the bulk action for marking payments as Shipped
	 *
	 * @since 1.5
	 *
	 * @access public
	 * @return array
	 */
	public function register_bulk_action( $actions ) {
		$actions['set-as-shipped'] = __( 'Set as Shipped', 'edd-simple-shipping' );
		return $actions;
	}

	/**
	 * Mark payments as shipped in bulk
	 *
	 * @since 1.5
	 *
	 * @access public
	 * @return array
	 */
	public function process_bulk_actions( $id, $action ) {
		if ( 'set-as-shipped' === $action ) {
			update_post_meta( $id, '_edd_payment_shipping_status', '2' );
		}
	}

	/**
	 * Add the shipped status column header
	 *
	 * @since 2.0
	 *
	 * @param object $order
	 * @return void
	 */
	public function shipped_column_header( $order ) {
		echo '<th>' . __( 'Shipped', 'edd-simple-shipping' ) . '</th>';
	}

	/**
	 * Add the shipped status column header
	 *
	 * @since 2.0
	 *
	 * @param object $order
	 * @return void
	 */
	public function shipped_column_value( $order ) {

		$shipping_status = get_post_meta( $order->ID, '_edd_payment_shipping_status', true );
		if( $shipping_status == '1' ) {
			$value = __( 'No', 'edd-simple-shipping' );
		} elseif( $shipping_status == '2' ) {
			$value = __( 'Yes', 'edd-simple-shipping' );
		} else {
			$value = __( 'N/A', 'edd-simple-shipping' );
		}

		$shipped = get_post_meta( $order->ID, '_edd_payment_shipping_status', true );
		if( $shipped == '2' ) {
			$new_status = '1';
		} else {
			$new_status = '2';
		}

		$toggle_url = esc_url( add_query_arg( array(
			'edd_action' => 'toggle_shipped_status',
			'order_id'   => $order->ID,
			'new_status' => $new_status
		) ) );

		$toggle_text = $shipped == '2' ? __( 'Mark as not shipped', 'edd-simple-shipping' ) : __( 'Mark as shipped', 'edd-simple-shipping' );

		echo '<td>' . esc_html( $value );
		if( $shipped ) {
			echo '<span class="edd-simple-shipping-sep">&nbsp;&ndash;&nbsp;</span><a href="' . $toggle_url . '" class="edd-simple-shipping-toggle-status">' . $toggle_text . '</a>';
		}
		echo '</td>';
	}

	/**
	 * Add the shipping address to the end of the payment receipt.
	 *
	 * @since 2.0
	 *
	 * @param object $payment
	 * @param array $edd_receipt_args
	 * @return void
	 */
	public function payment_receipt_after( $payment, $edd_receipt_args ) {

		$user_info = edd_get_payment_meta_user_info( $payment->ID );
		$address   = ! empty( $user_info[ 'shipping_info' ] ) ? $user_info[ 'shipping_info' ] : false;

		if ( ! $address ) {
			return;
		}

		$shipped = get_post_meta( $payment->ID, '_edd_payment_shipping_status', true );
		if( $shipped == '2' ) {
			$new_status = '1';
		} else {
			$new_status = '2';
		}

		$toggle_url = esc_url( add_query_arg( array(
			'edd_action' => 'toggle_shipped_status',
			'order_id'   => $payment->ID,
			'new_status' => $new_status
		) ) );

		$toggle_text = $shipped == '2' ? __( 'Mark as not shipped', 'edd-simple-shipping' ) : __( 'Mark as shipped', 'edd-simple-shipping' );

		echo '<tr>';
		echo '<td><strong>' . __( 'Shipping Address', 'edd-simple-shipping' ) . '</strong></td>';
		echo '<td>' . self::format_address( $user_info, $address ) . '<td>';
		echo '</tr>';

		if( current_user_can( 'edit_shop_payments' ) || ( function_exists( 'EDD_FES' ) && EDD_FES()->vendors->vendor_is_vendor() ) ) {

			echo '<tr>';
			echo '<td colspan="2">';
			echo '<a href="' . $toggle_url . '" class="edd-simple-shipping-toggle-status">' . $toggle_text . '</a>';
			echo '</td>';
			echo '</tr>';

		}
	}

	/**
	 * Format an address based on name and address information.
	 *
	 * For translators, a sample default address:
	 *
	 * (1) First (2) Last
	 * (3) Street Address 1
	 * (4) Street Address 2
	 * (5) City, (6) State (7) ZIP
	 * (8) Country
	 *
	 * @since 2.0
	 *
	 * @param array $user_info
	 * @param array $address
	 * @return string $address
	 */
	public static function format_address( $user_info, $address ) {

		$address = apply_filters( 'edd_shipping_address_format', sprintf(
			__( '<div><strong>%1$s %2$s</strong></div><div>%3$s</div><div>%4$s</div>%5$s, %6$s %7$s</div><div>%8$s</div>', 'edd-simple-shipping' ),
			$user_info[ 'first_name' ],
			$user_info[ 'last_name' ],
			$address[ 'address' ],
			$address[ 'address2' ],
			$address[ 'city' ],
			$address[ 'state' ],
			$address[ 'zip' ],
			$address[ 'country' ]
		), $address, $user_info );

		return $address;
	}

	/**
	 * Mark a payment as shipped.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function frontend_toggle_shipped_status() {

		$payment_id = absint( $_GET[ 'order_id' ] );
		$status     = ! empty( $_GET['new_status'] ) ? absint( $_GET['new_status'] ) : '1';
		$key        = edd_get_payment_key( $payment_id );

		if( function_exists( 'EDD_FES' ) ) {
			if ( ! EDD_FES()->vendors->vendor_can_view_receipt( false, $key ) ) {
				wp_safe_redirect( wp_get_referer() ); exit;
			}
		}

		update_post_meta( $payment_id, '_edd_payment_shipping_status', $status );

		wp_safe_redirect( wp_get_referer() );

		exit();
	}

	function edd_fes_simple_shipping(){
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			require_once dirname( __FILE__ ) . '/shipping-field.php';
			add_filter(  'fes_load_fields_array', 'edd_fes_simple_shipping_add_field', 10, 1 );
			function edd_fes_simple_shipping_add_field( $fields ){
				$fields['edd_simple_shipping'] = 'FES_Simple_Shipping_Field';
				return $fields;
			}
		}
	}


	/**
	 * Register a custom FES submission form button
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function edd_fes_simple_shipping_field_button( $title ) {
		if ( version_compare( fes_plugin_version, '2.2', '>=' ) ) {
			echo  '<button class="fes-button button" data-name="edd_simple_shipping" data-type="action" title="' . esc_attr( $title ) . '">'. __( 'Shipping', 'edd-simple-shipping' ) . '</button>';
		}
	}

	/**
	 * Setup the custom FES form field
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function edd_fes_simple_shipping_admin_field( $field_id, $label = "", $values = array() ) {
		if( ! isset( $values['label'] ) ) {
			$values['label'] = __( 'Shipping', 'edd-simple-shipping' );
		}

		$values['no_css']  = true;
		$values['is_meta'] = true;
		$values['name']    = 'edd_simple_shipping';
		?>
		<li class="edd_simple_shipping">
			<?php FES_Formbuilder_Templates::legend( $values['label'] ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][input_type]", 'edd_simple_shipping' ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][template]", 'edd_simple_shipping' ); ?>
			<div class="fes-form-holder">
				<?php FES_Formbuilder_Templates::common( $field_id, 'edd_simple_shipping', false, $values, false, '' ); ?>
			</div> <!-- .fes-form-holder -->
		</li>
	<?php
	}

	/**
	 * Indicate that this is a custom field
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	function edd_fes_simple_shipping_formbuilder_is_custom_field( $bool, $template_field ) {
		if ( $bool ) {
			return $bool;
		} else if ( isset( $template_field['template'] ) && $template_field['template'] == 'edd_simple_shipping' ) {
			return true;
		} else {
			return $bool;
		}
	}

	/**
	 * save the input values when the submission form is submitted
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function edd_fes_simple_shipping_save_custom_fields( $post_id ) {
		if ( isset( $_POST ['edd_simple_shipping'] ) && isset( $_POST ['edd_simple_shipping']['enabled'] ) ) {
			$domestic      = ! empty( $_POST ['edd_simple_shipping']['domestic'] ) ? edd_sanitize_amount( $_POST ['edd_simple_shipping']['domestic'] ) : 0;
			$international = ! empty( $_POST ['edd_simple_shipping']['international'] ) ? edd_sanitize_amount( $_POST ['edd_simple_shipping']['international'] ) : 0;
			update_post_meta( $post_id, '_edd_enable_shipping', '1' );
			update_post_meta( $post_id, '_edd_shipping_domestic', $domestic );
			update_post_meta( $post_id, '_edd_shipping_international', $international );

			$prices = edd_get_variable_prices( $post_id );
			if( ! empty( $prices ) ) {
				foreach( $prices as $price_id => $price ) {
					$prices[ $price_id ]['shipping'] = '1';
				}
				update_post_meta( $post_id, 'edd_variable_prices', $prices );
			}
		} else {
			delete_post_meta( $post_id, '_edd_enable_shipping' );
		}
	}

	/**
	 * Render our shipping fields in the submission form
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function edd_fes_simple_shipping_field( $attr, $post_id, $type ) {

		$required = '';
		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			$required = apply_filters( 'fes_required_class', ' edd-required-indicator', $attr );
		}

		$enabled       = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$domestic      = get_post_meta( $post_id, '_edd_shipping_domestic', true );
		$international = get_post_meta( $post_id, '_edd_shipping_international', true );

		?>
		<style>
			div.fes-form fieldset .fes-fields.edd_simple_shipping label { width: 100%; display:block; }
			div.fes-form fieldset .fes-fields.edd_simple_shipping .edd-fes-shipping-fields label { width: 45%; display:inline-block; }
			div.fes-form fieldset .fes-fields .edd-shipping-field { width: 45%; display:inline-block; }
		</style>
		<div class="fes-fields <?php echo sanitize_key( $attr['name']); ?>">
			<label for="edd_simple_shipping[enabled]">
				<input type="checkbox" name="edd_simple_shipping[enabled]" id="edd_simple_shipping[enabled]" value="1"<?php checked( '1', $enabled ); ?>/>
				<?php _e( 'Enable Shipping', 'edd-simple-shipping' ); ?>
			</label>
			<div class="edd-fes-shipping-fields">
				<label for="edd_simple_shipping[domestic]"><?php _e( 'Domestic', 'edd-simple-shipping' ); ?></label>
				<label for="edd_simple_shipping[international]"><?php _e( 'International', 'edd-simple-shipping' ); ?></label>
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[domestic]" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text" name="<?php echo esc_attr( $attr['name'] ); ?>[domestic]" placeholder="<?php echo __( 'Enter the domestic shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $domestic ) ?>" size="10" />
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[international]" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text" name="<?php echo esc_attr( $attr['name'] ); ?>[international]" placeholder="<?php echo __( 'Enter the international shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $international ) ?>" size="10" />
			</div>
		</div> <!-- .fes-fields -->
	<?php
	}


}


/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function edd_simple_shipping_load() {
	$edd_simple_shipping = new EDD_Simple_Shipping();
}
add_action( 'plugins_loaded', 'edd_simple_shipping_load', 0 );
