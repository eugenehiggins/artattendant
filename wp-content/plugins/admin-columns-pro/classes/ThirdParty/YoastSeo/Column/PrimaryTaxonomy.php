<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Column_PrimaryTaxonomy extends AC_Column_Meta
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Export_Column {

	public function __construct() {
		$this->set_label( __( 'Primary Taxonomy', 'codepress-admin-columns' ) );
		$this->set_group( 'yoast-seo' );
		$this->set_type( 'column-wpseo_column_taxonomy' );
	}

	public function get_value( $id ) {
		$raw_value = $this->get_raw_value( $id );
		if ( ! $raw_value ) {
			return $this->get_empty_char();
		}

		$term = array( get_term( $raw_value, $this->get_taxonomy() ) );
		$terms = ac_helper()->taxonomy->get_term_links( $term, $this->get_post_type() );

		if ( empty( $terms ) ) {
			return $this->get_empty_char();
		}

		return ac_helper()->string->enumeration_list( $terms, 'and' );
	}

	public function get_meta_key() {
		return '_yoast_wpseo_primary_' . $this->get_taxonomy();
	}

	protected function register_settings() {
		$this->add_setting( new ACP_ThirdParty_YoastSeo_Settings_PrimaryTaxonomy( $this ) );
	}

	public function editing() {
		return new ACP_ThirdParty_YoastSeo_Editing_PrimaryTaxonomy( $this );
	}

	public function filtering() {
		return new ACP_ThirdParty_YoastSeo_Filtering_PrimaryTaxonomy( $this );
	}

	public function export() {
		return new ACP_Export_Model_StrippedValue( $this );
	}

	/**
	 * @return string
	 */
	public function get_taxonomy() {
		$setting = $this->get_setting( 'primary_taxonomy' );

		if ( ! $setting instanceof ACP_ThirdParty_YoastSeo_Settings_PrimaryTaxonomy ) {
			return '';
		}

		return $setting->get_primary_taxonomy();
	}

}
