<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2
 *
 * @property ACP_Column_Post_Ancestors $column
 */
class ACP_Export_Model_Post_Ancestors extends ACP_Export_Model {

	public function __construct( ACP_Column_Post_Ancestors $column ) {
		parent::__construct( $column );
	}

	public function get_value( $id ) {
		$ancestors = $this->column->get_ancestor_ids( $id );
		$formatted_values = $this->column->get_formatted_value( new AC_Collection( $ancestors ) );

		return strip_tags( $formatted_values->implode( ', ' ) );
	}

}
