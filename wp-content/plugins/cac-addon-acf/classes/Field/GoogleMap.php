<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Field_GoogleMap extends ACA_ACF_Field {

	public function get_value( $id ) {
		$value = parent::get_value( $id );

		$map_data = array();
		if ( ! empty( $value['address'] ) ) {
			$map_data[] = $value['address'];
		}
		if ( ! empty( $value['lat'] ) ) {
			$map_data[] = $value['lat'];
		}
		if ( ! empty( $value['lng'] ) ) {
			$map_data[] = $value['lng'];
		}

		return implode( "<br/>\n", $map_data );
	}

}
