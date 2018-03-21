<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Column_Post extends ACA_ACF_Column {

	public function register_settings() {
		$this->register_settings_by_type( 'Post' );
	}

	public function get_formatted_id( $id ) {
		return $id;
	}

}
