<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Column_User extends ACA_ACF_Column {

	public function get_formatted_id( $id ) {
		return 'user_' . $id;
	}

	public function register_settings() {
		$this->register_settings_by_type( 'User' );
	}

}
