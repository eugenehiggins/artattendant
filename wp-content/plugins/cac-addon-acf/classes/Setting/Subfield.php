<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Setting_Subfield extends AC_Settings_Column {

	/**
	 * @var string
	 */
	private $sub_field;

	protected function define_options() {
		return array( 'sub_field' );
	}

	public function create_view() {
		$setting = $this->create_element( 'select' );

		$setting
			->set_no_result( sprintf( __( 'No %s subfields available.', 'codepress-admin-columns' ), 'ACF' ) )
			->set_attribute( 'data-refresh', 'column' )
			->set_options( $this->get_sub_fields() );

		$view = new AC_View( array(
			'label'   => __( 'Subfield', 'codepress-admin-columns' ),
			'tooltip' => __( 'Select a repeater sub field.', 'codepress-admin-columns' ),
			'setting' => $setting,
		) );

		return $view;
	}

	public function get_sub_fields() {
		$fields = array();

		if ( $sub_fields = $this->column->get_field()->get( 'sub_fields' ) ) {
			foreach ( $sub_fields as $sub_field ) {
				if ( 'repeater' == $sub_field['type'] ) {
					continue;
				}

				$fields[ $sub_field['key'] ] = $sub_field['label'];
			}
		}

		natcasesort( $fields );

		return $fields;
	}

	public function get_sub_field() {
		return $this->sub_field;
	}

	public function set_sub_field( $sub_field ) {
		$this->sub_field = $sub_field;

		return $this;
	}

	public function get_dependent_settings() {
		$acf_field = ACA_ACF_API::get_field( $this->sub_field );

		if ( ! $acf_field ) {
			return array();
		}

		return $this->column->get_field_by_type( $acf_field['type'] )->get_dependent_settings();
	}

}
