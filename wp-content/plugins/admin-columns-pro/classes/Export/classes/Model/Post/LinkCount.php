<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows Internal / External links in post content
 *
 * @since 4.1
 */
class ACP_Export_Model_Post_LinkCount extends ACP_Export_Model {

	public function get_value( $id ) {
		$links = $this->get_column()->get_raw_value( $id );

		if ( ! $links ) {
			return false;
		}

		return sprintf( '%s / %s', count( $links[0] ), count( $links[1] ) );
	}

}
