<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.1
 */
class ACP_Export_Model_Post_ChildPages extends ACP_Export_Model {

	public function get_value( $id ) {
		$titles = array();

		foreach ( $this->get_column()->get_raw_value( $id ) as $id ) {
			$titles[] = get_post_field( 'post_title', $id );
		}

		return implode( ',', $titles );
	}

}
