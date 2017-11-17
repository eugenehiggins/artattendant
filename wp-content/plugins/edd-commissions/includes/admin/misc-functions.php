<?php
/**
 * Misc Functions
 *
 * Helper functions
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register a filter against the user search when commissions is active
 *
 * @since       3.2
 * @return      void
 */
function eddc_register_filter_found_users() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_filter( 'edd_ajax_found_users', 'eddc_filter_found_users', 10, 2 );
	}
}
add_action( 'admin_init', 'eddc_register_filter_found_users' );


/**
 * Filter the users found by the ajax search to include PayPal email
 *
 * @since       3.2
 * @param       array $users The users found by the default search
 * @param       string $search_query The query searched for
 * @return      array The array of found users
 */
function eddc_filter_found_users( $users, $search_query ) {
	$exclude = array();

	if ( ! empty( $users ) ) {
		foreach ( $users as $user ) {
			$exclude[] = $user->ID;
		}
	}

	$get_users_args = array(
		'number'     => 9999,
		'exclude'    => $exclude,
		'meta_query' => array(
			array(
				'key'     => 'eddc_user_paypal',
				'value'   => $search_query,
				'compare' => 'LIKE',
			),
		),
	);

	$found_users = get_users( $get_users_args );
	if ( ! empty( $found_users ) ) {
		$users = array_merge( $users, $found_users );
	}

	return $users;
}
