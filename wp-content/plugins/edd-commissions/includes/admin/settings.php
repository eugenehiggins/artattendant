<?php
/**
 * Extension settings
 *
 * @package     EDDC
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the subsection for EDD Settings
 *
 * @since       3.2.5
 * @param       array $sections The sections
 * @return      array Sections with commissions added
 */
function eddc_settings_section_extensions( $sections ) {
	$sections['commissions'] = __( 'Commissions', 'eddc' );
	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'eddc_settings_section_extensions' );


/**
 * Registers the new Commissions options in Extensions
 *
 * @since       1.2.1
 * @param       $settings array the existing plugin settings
 * @return      array The new EDD settings array with commissions added
 */
function eddc_settings_extensions( $settings ) {
	$calc_options = array(
		'subtotal'      => __( 'Item Subtotal (default)', 'eddc' ),
		'total'         => __( 'Item Total with Taxes', 'eddc' ),
		'total_pre_tax' => __( 'Item Total without Taxes', 'eddc' ),
	);

	$commission_settings = array(
		array(
			'id'      => 'eddc_header',
			'name'    => '<strong>' . __( 'Commissions Settings', 'eddc' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_commissions_default_rate',
			'name'    => __( 'Default rate', 'eddc' ),
			'desc'    => __( 'Enter the default rate recipients should receive. This can be overwritten on a per-product basis. 10 = 10%', 'eddc' ),
			'type'    => 'text',
			'size'    => 'small',
		),
		array(
			'id'      => 'edd_commissions_calc_base',
			'name'    => __( 'Calculation Base', 'eddc' ),
			'desc'    => __( 'Should commissions be calculated from the item subtotal (before taxes and discounts) or from the item total (after taxes and discounts)?', 'eddc' ),
			'type'    => 'select',
			'options' => $calc_options,
		),
		array(
			'id'      => 'edd_commissions_allow_zero_value',
			'name'    => sprintf( __( 'Allow %s commissions', 'eddc' ), edd_currency_filter( edd_format_amount( 0.00 ) ) ),
			'desc'    => __( 'This option determines whether or not zero-value commissions are recorded.', 'eddc' ),
			'type'    => 'radio',
			'std'     => 'yes',
			'options' => array(
				'yes' => __( 'Yes, record zero value commissions', 'eddc' ),
				'no'  => __( 'No, do not record zero value commissions', 'eddc' ),
			),
			'tooltip_title' => __( 'Allow zero value commissions', 'eddc' ),
			'tooltip_desc'  => sprintf( __( 'By default, EDD records commissions even if the value of the commission is %s. While this may be useful for tracking purposes in some situations, some users may find it confusing. If you prefer not to see $0.00 commissions, disable them here.', 'eddc' ), edd_currency_filter( edd_format_amount( 0.00 ) ) ),
		),
		array(
			'id'      => 'edd_commissions_autopay_pa',
			'name'    => __('Instant Pay Commmissions', 'eddc'),
			'desc'    => sprintf( __('If checked and <a href="%s">PayPal Adaptive Payments</a> gateway is installed, EDD will automatically pay commissions at the time of purchase', 'eddc'), 'https://easydigitaldownloads.com/downloads/paypal-adaptive-payments/' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_commissions_revoke_on_refund',
			'name'    => __('Revoke on Refund', 'eddc'),
			'desc'    => __('If checked EDD will automatically revoke any <em>unpaid</em> commissions when a payment is refunded.', 'eddc'),
			'type'    => 'checkbox',
		),
	);

	$commission_settings = apply_filters( 'eddc_settings', $commission_settings );

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$commission_settings = array( 'commissions' => $commission_settings );
	}

	return array_merge( $settings, $commission_settings );
}
add_filter( 'edd_settings_extensions', 'eddc_settings_extensions' );


/**
 * Add the Commissions Notifications emails subsection to the settings
 *
 * @since       3.2.12
 * @param       array $sections Sections for the emails settings tab
 * @return      array
 */
function eddc_settings_section_emails( $sections ) {
	$sections['commissions'] = __( 'Commission Notifications', 'eddc' );
	return $sections;
}
add_filter( 'edd_settings_sections_emails', 'eddc_settings_section_emails' );


/**
 * Registers the new Commissions options in Emails
 *
 * @since       3.0
 * @param       $settings array the existing plugin settings
 * @return      array
*/
function eddc_settings_emails( $settings ) {
	$commission_settings = array(
		array(
			'id'    => 'eddc_header',
			'name'  => '<strong>' . __( 'Commission Notifications', 'eddc' ) . '</strong>',
			'desc'  => '',
			'type'  => 'header',
			'size'  => 'regular'
		),
		array(
			'id'    => 'edd_commissions_disable_sale_alerts',
			'name'  => __( 'Disable New Sale Alerts', 'eddc' ),
			'desc'  => __( 'Check this box to disable the New Sale notification emails sent to commission recipients.', 'eddc' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_commissions_email_subject',
			'name'  => __( 'Email Subject', 'eddc' ),
			'desc'  => __( 'Enter the subject for commission emails.', 'eddc' ),
			'type'  => 'text',
			'size'  => 'regular',
			'std'   => __( 'New Sale!', 'eddc' )
		),
		array(
			'id'    => 'edd_commissions_email_message',
			'name'  => __( 'Email Body', 'eddc' ),
			'desc'  => __( 'Enter the content for commission emails. HTML is accepted. Available template tags:', 'eddc' ) . '<br />' . eddc_display_email_template_tags(),
			'type'  => 'rich_editor',
			'std'   => eddc_get_email_default_body()
		)
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$commission_settings = array( 'commissions' => $commission_settings );
	}

	return array_merge( $settings, $commission_settings );

}
add_filter( 'edd_settings_emails', 'eddc_settings_emails' );
