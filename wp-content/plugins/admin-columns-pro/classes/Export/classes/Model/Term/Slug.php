<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Name (default column) exportability model
 *
 * @since NEWVERSION
 */
class ACP_Export_Model_Term_Slug extends ACP_Export_Model {

	public function get_value( $id ) {
		$term = get_term( $id );

		return apply_filters( 'editable_slug', $term->slug, $term );
	}

}
