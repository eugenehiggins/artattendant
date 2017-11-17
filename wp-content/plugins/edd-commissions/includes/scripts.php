<?php
/**
 * Scripts
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue admin scripts
 *
 * @since       3.3
 * @return      void
 */
function eddc_admin_scripts() {
	$screen = get_current_screen();

	if ( ! is_object( $screen ) ) {
		return;
	}

	$allowed_screens = array(
		'download_page_edd-commissions',
		'download',
		'download_page_edd-reports',
	);

	$allowed_screens = apply_filters( 'eddc-admin-script-screens', $allowed_screens );

	if ( ! in_array( $screen->id, $allowed_screens ) ) {
		return;
	}

	$css_dir = EDD_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_script( 'eddc-admin-scripts', EDDC_PLUGIN_URL . 'assets/js/admin-scripts' . $suffix . '.js', array( 'jquery' ), EDD_COMMISSIONS_VERSION );
	wp_enqueue_script( 'eddc-admin-scripts' );
	wp_localize_script( 'eddc-admin-scripts', 'eddc_vars', array(
		'action_edit'     => __( 'Edit Commission', 'eddc' ),
		'action_cancel'   => __( 'Cancel Edit', 'eddc' ),
		'confirm_payout'  => __( 'Generating a payout file will mark all unpaid commissions as paid. Do you want to continue?', 'eddc' ),
		'required_fields' => __( 'Please fill out all required fields.', 'eddc' ),
	));

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_enqueue_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'eddc-admin-styles', EDDC_PLUGIN_URL . 'assets/css/admin-styles' . $suffix . '.css', EDD_COMMISSIONS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'eddc_admin_scripts' );
