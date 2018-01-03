<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Column_Score extends AC_Column_Meta
	implements ACP_Export_Column, ACP_Column_SortingInterface {

	public function __construct() {
		$this->set_original( true );
		$this->set_group( 'yoast-seo' );
		$this->set_type( 'wpseo-score' );
	}

	// The display value is handled by the native column
	public function get_value( $id ) {
		return false;
	}

	public function register_settings() {
		$width = $this->get_setting( 'width' );

		$width->set_default( 63 );
		$width->set_default( 'px', 'width_unit' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta_key() {
		return '_yoast_wpseo_linkdex';
	}

	public function export() {
		return new ACP_Export_Model_StrippedRawValue( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function sorting() {
		$model = new ACP_Sorting_Model_Meta( $this );
		$model->set_data_type( 'numeric' );

		return $model;
	}

}
