<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Export_Model_Media_Title extends ACP_Export_Model {

	public function get_value( $id ) {
		return wp_get_attachment_url( $id );
	}

}
