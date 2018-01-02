<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field
	implements ACP_Column_FilteringInterface, ACP_Column_EditingInterface, ACP_Column_SortingInterface {

	/**
	 * @var ACA_ACF_Column
	 */
	protected $column;

	/**
	 * @param ACA_ACF_Column $column
	 */
	public function __construct( ACA_ACF_Column $column ) {
		$this->column = $column;

		// ACF multiple data is stored serialized
		$this->column->set_serialized( $this->get( 'multiple' ) );
	}

	// Display

	public function get_value( $id ) {
		return $this->column->get_formatted_value( $this->get_raw_value( $id ), $id );
	}

	public function get_raw_value( $id ) {
		return get_field( $this->column->get_meta_key(), $this->column->get_formatted_id( $id ), false );
	}

	public function get_separator() {
		return $this->column->get_separator();
	}

	// Pro

	public function filtering() {
		return new ACP_Filtering_Model_Disabled( $this->column );
	}

	public function editing() {
		return new ACP_Editing_Model_Disabled( $this->column );
	}

	public function sorting() {
		return new ACP_Sorting_Model_Disabled( $this->column );
	}

	public function export() {
		return new ACP_Export_Model_RawValue( $this->column );
	}

	// Settings

	/**
	 * @return AC_Settings_Column[]
	 */
	public function get_dependent_settings() {
		return array();
	}

	// Helpers

	/**
	 * Get ACF field property
	 *
	 * @param string $property
	 *
	 * @return string|array|false
	 */
	public function get( $property ) {
		return $this->column->get_acf_field_option( $property );
	}

	/**
	 * Get link to field's group settings
	 *
	 * @return false|string
	 */
	public function get_edit_link() {
		return get_edit_post_link( acf_get_field_group_id( $this->get( 'parent' ) ) );
	}

}
