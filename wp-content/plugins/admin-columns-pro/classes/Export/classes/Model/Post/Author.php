<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_Author extends ACP_Export_Model {

	public function get_value( $id ) {
		$user = get_userdata( get_post_field( 'post_author', $id ) );

		return isset( $user->display_name ) ? $user->display_name : '';
	}

}
