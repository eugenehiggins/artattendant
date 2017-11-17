<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_User extends ACA_ACF_Filtering {

	public function get_filtering_data() {
		$options = array();

		if ( $this->column->is_serialized() ) {
			$values = $this->get_meta_values_unserialized();
		} else {
			$values = $this->get_meta_values();
		}

		foreach ( $values as $value ) {
			$options[ $value ] = $this->column->get_formatted_value( $value );
		}

		return array(
			'empty_option' => true,
			'options'      => $options,
		);
	}

}
