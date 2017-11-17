<?php
/**
 * Scripts
 *
 * @package     EDD\Message\Scripts
 * @since       0.1.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load admin scripts
 *
 * @since       0.1.0
 * @global      array $edd_settings_page The slug for the EDD settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function edd_message_admin_scripts( $hook ) {

    if( $hook == 'download_page_edd-customers' || $hook == 'edd-fes_page_fes-vendors' ) {
        wp_enqueue_style( 'edd_message_admin_css', EDD_MESSAGE_URL . '/assets/css/admin.css' );
	    wp_enqueue_script( 'edd-admin-scripts' );
	    wp_enqueue_script( 'postbox' );
    }
}
add_action( 'admin_enqueue_scripts', 'edd_message_admin_scripts', 200 );

/**
 * Load frontend scripts
 *
 * @since       0.1.0
 * @return      void
 */
function edd_message_scripts() {

	$task = ( isset( $_GET['task'] ) ) ? $_GET['task'] : null;

	if ( $task == 'message' ) {
	    wp_enqueue_style( 'edd_message_css', EDD_MESSAGE_URL . 'assets/css/styles.css' );
		$fes = new FES_Setup();
		$fes->enqueue_scripts( true );
	}
}
add_action( 'wp_enqueue_scripts', 'edd_message_scripts' );

function edd_message_print_footer_scripts() {
	echo '<script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow); });</script>';
}
add_action( 'admin_footer-download_page_edd-customers','edd_message_print_footer_scripts' );
add_action( 'admin_footer-edd-fes_page_fes-vendors','edd_message_print_footer_scripts' );