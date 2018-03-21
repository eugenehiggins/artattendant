<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Delegated extends ACP_Filtering_Model {

	/**
	 * @var string Dropdown HTML attribute id
	 */
	private $dropdown_attr_id;

	/**
	 * @param AC_Column $column
	 * @param string    $dropdown_attr_id
	 */
	public function __construct( AC_Column $column, $dropdown_attr_id = null ) {
		parent::__construct( $column );

		$this->dropdown_attr_id = $dropdown_attr_id;
	}

	public function get_filtering_vars( $vars ) {
		return $vars;
	}

	public function get_filtering_data() {
		return array();
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings_Delegated( $this->column ) );
	}

	public function get_dropdown_attr_id() {
		return $this->dropdown_attr_id;
	}

	/**
	 * @deprecated 4.2.3
	 *
	 * @param string $dropdown_attr_id
	 */
	public function set_dropdown_attr_id( $dropdown_attr_id ) {
		_deprecated_function( __METHOD__, '4.2.3' );

		$this->dropdown_attr_id = $dropdown_attr_id;
	}

}
