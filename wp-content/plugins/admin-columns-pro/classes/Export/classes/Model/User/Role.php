<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Role (default column) exportability model
 *
 * @since 4.1
 */
class ACP_Export_Model_User_Role extends ACP_Export_Model {

	public function get_value( $id ) {
		$user = get_userdata( $id );

		return implode( ', ', ac_helper()->user->translate_roles( $user->roles ) );
	}

}
