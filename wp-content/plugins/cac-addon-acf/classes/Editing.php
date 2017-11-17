<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
class ACA_ACF_Editing extends ACP_Editing_Model {

	public function __construct( ACA_ACF_Column $column ) {
		parent::__construct( $column );
	}

	public function get_view_settings() {
		$data = array(
			'type'         => 'text',
			'store_values' => true,
		);

		$field = $this->column->get_field();

		if ( $placeholder = $field->get( 'placeholder' ) ) {
			$data['placeholder'] = $placeholder;
		}
		if ( $min = $field->get( 'min' ) ) {
			$data['range_min'] = $min;
		}
		if ( $max = $field->get( 'max' ) ) {
			$data['range_max'] = $max;
		}
		if ( $step = $field->get( 'step' ) ) {
			$data['range_step'] = $step;
		}
		if ( $required = $field->get( 'required' ) ) {
			$data['required'] = $required;
		}
		if ( $maxlength = $field->get( 'maxlength' ) ) {
			$data['maxlength'] = $maxlength;
		}
		if ( 'uploadedTo' == $field->get( 'library' ) ) {
			$editable['attachment']['library']['uploaded_to_post'] = true;
		}
		if ( $field->get( 'multiple' ) ) {
			$data['multiple'] = true;
		}

		return $data;
	}

	public function save( $id, $value ) {
		return update_field( $this->column->get_field_hash(), $value, $this->column->get_formatted_id( $id ) );
	}

	public function get_edit_value( $id ) {
		$value = parent::get_edit_value( $id );

		// null will disable editing
		if ( null === $value ) {
			return false;
		}

		return $value;
	}

	/**
	 * @param array $ajax_query ACF ajax query [ 'results' => array() ]
	 *
	 * @return array
	 */
	protected function format_choices( $ajax_query ) {
		$options = array();

		if ( empty( $ajax_query['results'] ) ) {
			return array();
		}

		foreach ( $ajax_query['results'] as $choice ) {
			if ( ! isset( $choice['id'] ) ) {
				$options[ $choice['text'] ] = array(
					'label'   => $choice['text'],
					'options' => array(),
				);

				foreach ( $choice['children'] as $subchoice ) {
					$options[ $choice['text'] ]['options'][ $subchoice['id'] ] = htmlspecialchars_decode( $subchoice['text'] );
				}
			} else {
				$options[ $choice['id'] ] = htmlspecialchars_decode( $choice['text'] );
			}
		}

		return $options;
	}

}
