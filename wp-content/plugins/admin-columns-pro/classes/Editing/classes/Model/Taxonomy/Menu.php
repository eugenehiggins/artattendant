<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Taxonomy_Menu extends ACP_Editing_Model_Menu {

	/**
	 * @param int $id
	 *
	 * @return string|false
	 */
	protected function get_title( $id ) {
		$term = get_term_by( 'id', $id, $this->column->get_taxonomy() );

		return $term->name;
	}

}
