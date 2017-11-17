<?php
/**
 * Simple Shipping integration
 *
 * This file holds all functions make commissions work with the Simple Shipping extension
 *
 * @copyright   Copyright (c) 2016, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add new site-wide settings under "Downloads" > "Extensions" > "Commissions" for determining how shipping is split for commissions by default.
 *
 * @since       3.3
 * @param       array $commission_settings The array of settings for the Commissions settings page.
 * @return      array $commission_settings The array of settings for the Commissions settings page.
 */
function eddc_settings_add_shipping_options( $commission_settings ){
	$commission_settings[] = array(
		'id'      => 'edd_commissions_shipping',
		'name'    => __( 'Shipping Fees', 'eddc' ),
		'desc'    => __( 'How should shipping fees be split-up for commission recievers?', 'eddc' ) . '<br />' . __( 'To learn more about how this works, see', 'eddc' ) . ' <a href="http://docs.easydigitaldownloads.com/article/1448-integration-guide-commissions-and-simple-shipping" target="_blank">' . __( 'this doc.', 'eddc' ) . '</a>',
		'type'    => 'select',
		//Shipping fee values are named strangely because originally they didn't make sense and are kept this way for backwards compatibility.
		//For the actual descriptions, see the array values, not the keys:
		'options' => array(
			'ignored'          => __( 'Split Shipping according to commission rates in product.', 'eddc' ), //during calculation this becomes 'split_shipping'
			'include_shipping' => __( 'Pay Shipping to 1st reciever in product.', 'eddc' ),//during calculation this becomes 'pay_to_first_user'
			'exclude_shipping' => __( 'Shipping fee paid to store.', 'eddc' ),//during calculation this becomes 'pay_to_store'
		),
		'tooltip_title' => __( 'When is this option used?', 'edd_sl' ),
		'tooltip_desc'  => __( 'Ship-able Products with commissions enabled will use this site-wide setting by default. It can be over-ridden by the Shipping setting in the Commissions metabox for each product.', 'eddc' )
	);

	return $commission_settings;
}
add_filter( 'eddc_settings', 'eddc_settings_add_shipping_options' );


/**
 * Output a new option in commissions for Simple Shipping which allows the site owner to choose how shipping fees are split in Commissions.
 *
 * @since       3.3
 * @return      void
 */
function eddc_metabox_add_shipping_options(){
	global $post;

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'eddc-shipping-admin-scripts', EDDC_PLUGIN_URL . 'assets/js/admin-eddc-shipping-integration' . $suffix . '.js', array( 'jquery' ), EDD_COMMISSIONS_VERSION, true );
	wp_enqueue_script( 'eddc-shipping-admin-scripts' );

	$enabled      = get_post_meta( $post->ID, '_edd_commisions_enabled', true ) ? true : false;
	$meta         = get_post_meta( $post->ID, '_edd_commission_settings', true );
	$shipping_fee = isset( $meta['shipping_fee'] ) ? $meta['shipping_fee'] : 'site_default';

	echo '<tr style="display:none;" class="eddc_commission_row" id="edd_commissions_shipping_fee_split">';
		echo '<td class="edd_field_type_text">';
			echo '<label for="edd_commission_settings[type]"><strong>' . __( 'Shipping:', 'eddc' ) . '</strong></label>';
			echo '<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>' . __( 'Shipping', 'eddc' ) . '</strong>: ' . __( 'How should the shipping fees be split?', 'eddc' ) . '"></span><br/>';
			echo '<p>';
				echo '<select name="edd_commission_settings[shipping_fee]" id="edd_commission_shipping_fee">';
				  echo '<option value="site_default" ' . selected( $shipping_fee, 'site_default', false ) . '>' . __( 'Use site-wide default.', 'eddc' ) . '</option>';
				  echo '<option value="split_shipping" ' . selected( $shipping_fee, 'split_shipping', false ) . '>' . __( 'Split based on rate.', 'eddc' ) . '</option>';
				  echo '<option value="pay_to_first_user" ' . selected( $shipping_fee, 'pay_to_first_user', false ) . '>' . __( 'Pay to 1st user.', 'eddc' ) . '</option>';
				  echo '<option value="pay_to_store" ' . selected( $shipping_fee, 'pay_to_store', false ) . '>' . __( 'Pay to store.', 'eddc' ) . '</option>';
				echo '</select>';
			echo '</p>';
			echo '<p>';
			echo __( ' To learn more about how this works, see', 'eddc' ) . ' <a href="http://docs.easydigitaldownloads.com/article/1448-integration-guide-commissions-and-simple-shipping" target="_blank">' . __( 'this doc.', 'eddc' ) . '</a>';
			echo '</p>';
		echo '<td>';
	echo '</tr>';
}
add_action( 'eddc_metabox_options_table_after', 'eddc_metabox_add_shipping_options' );


/**
 * Make the commission calulation include shipping fees from Simple Shipping (if the site owner chose that).
 *
 * @since       3.3
 * @param       int $commission_amount The amount already calculated for the commission
 * @param       array $args The args passed to the eddc_calc_commission_amount function
 * @return      int $amount The commission amount including shipping fee calculations
 */
function eddc_include_shipping_calc_in_commission( $commission_amount, $args ){
	$defaults = array(
		'price'             => NULL,
		'rate'              => NULL,
		'type'              => 'percentage',
		'download_id'       => 0,
		'cart_item'         => NULL,
		'recipient'         => NULL,
		'recipient_counter' => 0,
		'payment_id'        => NULL
	);

	$args = wp_parse_args( $args, $defaults );

	$commission_settings = get_post_meta( $args['download_id'], '_edd_commission_settings', true );
	$shipping            = edd_get_option( 'edd_commissions_shipping', 'split_shipping' );

	//Reset shipping fee value because originally they didn't make sense and are kept that way for backwards compatibility only.
	if ( $shipping == 'ignored' ){
		$shipping = 'split_shipping';
	} elseif( $shipping == 'include_shipping' ){
		$shipping = 'pay_to_first_user';
	} elseif( $shipping == 'exclude_shipping' ){
		$shipping = 'pay_to_store';
	}

	// Check if a special shipping setting has been applied to this product in particular and over-ride the site-default if so.
	if ( isset( $commission_settings['shipping_fee'] ) && $commission_settings['shipping_fee'] !== 'site_default' ){
		$shipping = $commission_settings['shipping_fee'];
	}

	// If there are fees
	if ( ! empty( $args['cart_item']['fees'] ) ) {
		//Loop through each fee
		foreach ( $args['cart_item']['fees'] as $fee_id => $fee ) {
			if ( 'split_shipping' == $shipping ){
				$commission_amount += $fee['amount'] * ( $args['rate'] / 100 );
			} elseif( 'pay_to_first_user' == $shipping ) {
				if ( eddc_get_recipient_position( $args['recipient'], $args['download_id'] ) == 0 ){
					$commission_amount += $fee['amount'];
				}
			}
		}
	}

	return $commission_amount;
}
add_filter( 'eddc_calc_commission_amount', 'eddc_include_shipping_calc_in_commission', 10, 2 );
