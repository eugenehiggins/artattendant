<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Radio extends ACA_ACF_Filtering {

	public function get_filtering_data() {
		$options = array();

		$choices = (array) $this->column->get_field()->get( 'choices' );

		foreach ( $this->get_meta_values() as $value ) {
			if ( $choices && isset( $choices[ $value ] ) ) {
				$options[ $value ] = $choices[ $value ];
			}
		}

		return array(
			'empty_option' => true,
			'options'       => $options,
		);
	}

}
