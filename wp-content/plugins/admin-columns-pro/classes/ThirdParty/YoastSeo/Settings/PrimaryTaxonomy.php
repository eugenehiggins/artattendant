<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Settings_PrimaryTaxonomy extends AC_Settings_Column {

	/**
	 * @var string
	 */
	private $primary_taxonomy;

	protected function define_options() {
		return array( 'primary_taxonomy' );
	}

	public function create_view() {
		$setting = $this->create_element( 'select' )
		                ->set_attribute( 'data-label', 'update' )
		                ->set_options( $this->get_taxonies() );

		$view = new AC_View( array(
			'label'   => __( 'Taxonomy' ),
			'setting' => $setting,
		) );

		return $view;
	}

	/**
	 * @return array
	 */
	private function get_taxonies() {
		$taxonomies = get_object_taxonomies( $this->column->get_post_type(), 'objects' );
		$options = array();

		foreach ( $taxonomies as $taxonomy => $tax_object ) {
			if ( ! $tax_object->hierarchical ) {
				continue;
			}

			$options[ $taxonomy ] = $tax_object->label;
		}

		return $options;
	}

	/**
	 * @return string
	 */
	public function get_primary_taxonomy() {
		return $this->primary_taxonomy;
	}

	/**
	 * @param string $primary_taxonomy
	 */
	public function set_primary_taxonomy( $primary_taxonomy ) {
		$this->primary_taxonomy = $primary_taxonomy;

		return true;
	}

}
