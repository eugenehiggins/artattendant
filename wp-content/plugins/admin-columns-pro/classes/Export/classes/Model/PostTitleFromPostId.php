<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability model for outputting a post's title based on its ID
 *
 * @since 4.1
 */
class ACP_Export_Model_PostTitleFromPostId extends ACP_Export_Model {

	public function get_value( $id ) {
		return get_the_title( $this->get_column()->get_raw_value( $id ) );
	}

}
