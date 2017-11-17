<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Column_Taxonomy extends ACA_ACF_Column {

	public function get_formatted_id( $id ) {
		return $this->get_taxonomy() . '_' . $id;
	}

	public function register_settings() {
		$this->register_settings_by_type( 'Taxonomy' );
	}

}
