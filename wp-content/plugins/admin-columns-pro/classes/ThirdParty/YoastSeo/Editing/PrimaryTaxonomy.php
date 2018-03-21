<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_ThirdParty_YoastSeo_Column_PrimaryTaxonomy $column
 */
class ACP_ThirdParty_YoastSeo_Editing_PrimaryTaxonomy extends ACP_Editing_Model_Meta {

	/**
	 * @param int $id
	 *
	 * @return array|false
	 */
	public function get_edit_value( $id ) {
		$term = $this->column->get_raw_value( $id );

		if ( ! $term ) {
			$terms = wp_get_post_terms( $id, $this->column->get_taxonomy() );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return null;
			}

			return false;
		}

		$term = get_term( $term, $this->column->get_taxonomy() );

		return array(
			$term->term_id => $term->name,
		);
	}

	public function get_view_settings() {
		return array(
			'type'          => 'select2_dropdown',
			'multiple'      => false,
			'ajax_populate' => true,
		);
	}

	public function get_ajax_options( $request ) {
		if ( $request['paged'] > 1 ) {
			return array();
		}

		$terms = wp_get_post_terms( $request['object_id'], $this->column->get_taxonomy() );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		$options = array();

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

}
