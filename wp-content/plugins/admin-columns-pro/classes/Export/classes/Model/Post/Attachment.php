<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Export_Model_Post_Attachment extends ACP_Export_Model {

	public function get_value( $id ) {
		$urls = array();

		foreach ( $this->get_column()->get_raw_value( $id ) as $media_id ) {
			$urls[] = wp_get_attachment_url( $media_id );
		}

		return implode( ',', $urls );
	}

}
