<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Export_Model_Post_Roles extends ACP_Export_Model {

	public function get_value( $id ) {
		return implode( ',', ac_helper()->user->get_role_names( $this->get_column()->get_raw_value( $id ) ) );
	}

}
