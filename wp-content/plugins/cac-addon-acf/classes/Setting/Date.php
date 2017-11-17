<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Setting_Date extends AC_Settings_Column_Date
	implements AC_Settings_FormatValueInterface {

	public function __construct( ACA_ACF_Column $column ) {
		parent::__construct( $column );

		$this->set_default( 'acf' );
	}

	protected function get_acf_date_format() {
		return $this->column->get_field()->get( 'display_format' );
	}

	protected function get_date_options() {
		$label = __( 'ACF Date Format', 'codepress-admin-columns' );

		$options = array(
			'acf' => $this->get_html_label( array(
				'label'       => $label,
				'date_format' => $this->get_acf_date_format(),
				'description' => sprintf( __( "%s uses the %s from it's field settings.", 'codepress-admin-columns' ), $label, '"' . __( 'Display Format', 'codepress-admin-columns' ) . '"' ),
			) ),
		);

		return ac_helper()->array->insert( parent::get_date_options(), $options, 'diff' );
	}

	public function format( $value, $original_value ) {
		if ( ! $value ) {
			return false;
		}

		if ( 'acf' === $this->get_date_format() ) {
			return date_i18n( $this->get_acf_date_format(), strtotime( $value ) );
		}

		return parent::format( $value, $original_value );
	}

}
