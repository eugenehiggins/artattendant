<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Export_Model_CustomField_Image extends ACP_Export_Model {

	public function get_value( $id ) {
		$urls = array();

		foreach ( (array) $this->get_column()->get_raw_value( $id ) as $url ) {
			if ( is_numeric( $url ) ) {
				$url = wp_get_attachment_url( $url );
			}

			$urls[] = strip_tags( $url );
		}

		return implode( ',', $urls );
	}

}
