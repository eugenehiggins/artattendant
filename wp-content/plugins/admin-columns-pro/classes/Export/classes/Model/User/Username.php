<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Username (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_User_Username extends ACP_Export_Model {

	public function get_value( $id ) {
		$user = get_userdata( $id );

		return isset( $user->user_login ) ? $user->user_login : '';
	}

}
