<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display name (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_User_Name extends ACP_Export_Model {

	public function get_value( $id ) {
		$user = get_userdata( $id );

		return "{$user->first_name} {$user->last_name}";
	}

}
