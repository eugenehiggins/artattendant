<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Filtering_PrimaryTaxonomy extends ACP_Filtering_Model_Meta {

	/**
	 * @return array
	 */
	public function get_filtering_data() {
		$options = array();

		foreach ( $this->get_meta_values() as $term_id ) {
			$term = get_term_by( 'id', $term_id, $this->column->get_taxonomy() );

			if ( ! $term ) {
				continue;
			}

			$options[ $term_id ] = $term->name;
		}

		return array(
			'empty_option' => true,
			'options'      => $options,
		);
	}

}
