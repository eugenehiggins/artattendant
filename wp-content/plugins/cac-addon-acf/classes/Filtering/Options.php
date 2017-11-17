<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Options extends ACA_ACF_Filtering {

	public function get_filtering_data() {

		if ( $this->column->is_serialized() ) {
			$values = $this->get_meta_values_unserialized();
		} else {
			$values = $this->get_meta_values();
		}

		$options = array();

		$choices = (array) $this->column->get_field()->get( 'choices' );

		foreach ( $values as $value ) {
			if ( $choices && isset( $choices[ $value ] ) ) {
				$options[ $value ] = $choices[ $value ];
			}
		}

		return array(
			'empty_option' => true,
			'options'      => $options,
		);
	}

}
