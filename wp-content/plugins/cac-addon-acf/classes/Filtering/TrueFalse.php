<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_TrueFalse extends ACA_ACF_Filtering {

	public function get_filtering_vars( $vars ) {

		$value = $this->get_filter_value();

		if ( 1 == $value ) {
			$vars['meta_query'][] = array(
				'key'   => $this->column->get_meta_key(),
				'value' => $value,
			);
		}
		else {
			$vars['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => $this->column->get_meta_key(),
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => $this->column->get_meta_key(),
					'value' => $value,
				),
			);
		}

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();

		$values = $this->get_meta_values();
		$data['options'][0] = __( 'False', 'codepress-admin-columns' );
		foreach ( $values as $value ) {
			if ( 0 != $value ) {
				$data['options'][ $value ] = __( 'True', 'codepress-admin-columns' );
			}
		}

		return $data;
	}

}
