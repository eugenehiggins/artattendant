<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Last modified author column exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Post_LastModifiedAuthor extends ACP_Export_Model {

	public function get_value( $id ) {
		$raw_value = $this->get_column()->get_raw_value( $id );

		return $raw_value ? $this->get_column()->get_value( $id ) : '';
	}

}
