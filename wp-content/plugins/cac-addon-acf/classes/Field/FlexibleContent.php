<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_FlexibleContent extends ACA_ACF_Field {

	public function get_value( $id ) {
		$raw_value = $this->get_raw_value( $id );

		if ( ! $raw_value ) {
			return false;
		}

		$field = $this->column->get_acf_field();

		if ( empty( $field['layouts'] ) ) {
			return false;
		}

		$labels = array();
		foreach ( $field['layouts'] as $layout ) {
			$labels[ $layout['name'] ] = $layout['label'];
		}

		$layouts = array();
		foreach ( $raw_value as $values ) {
			$layouts[ $values['acf_fc_layout'] ] = array(
				'count' => empty( $layouts[ $values['acf_fc_layout'] ] ) ? 1 : ++$layouts[ $values['acf_fc_layout'] ]['count'],
				'label' => $labels[ $values['acf_fc_layout'] ],
			);
		}

		$output = array();
		foreach ( $layouts as $layout ) {
			$label = $layout['label'];

			if ( $layout['count'] > 1 ) {
				$label .= '<span class="cpac-rounded">' . $layout['count'] . '</span>';
			}

			$output[] = $label;
		}

		return implode( '<br/>', $output );
	}

	public function export() {
		return new ACP_Export_Model_Disabled( $this->column );
	}

}
