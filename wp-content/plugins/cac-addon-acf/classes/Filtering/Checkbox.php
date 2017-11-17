<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Checkbox extends ACA_ACF_Filtering_Options {

	public function get_filtering_data() {
		$options = array();

		$choices = (array) $this->column->get_field()->get( 'choices' );

		foreach ( $this->get_meta_values_unserialized() as $value ) {
			if ( $choices && isset( $choices[ $value ] ) ) {
				$options[ $value ] = $choices[ $value ];
			}
		}

		return array(
			'empty_option' => true,
			'options'      => $options
		);
	}

}
