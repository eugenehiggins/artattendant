<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy (default column) exportability model
 * @property AC_Column_Taxonomy $column
 * @since 4.1
 */
class ACP_Export_Model_Post_Taxonomy extends ACP_Export_Model {

	public function __construct( $column ) {
		parent::__construct( $column );
	}

	public function get_value( $id ) {
		$terms = wp_get_post_terms( $id, $this->column->get_taxonomy(), array( 'fields' => 'names' ) );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}

		return implode( ', ', $terms );
	}

}
