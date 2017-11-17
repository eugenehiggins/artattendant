<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Relationship extends ACA_ACF_Filtering_PostObject {

	public function get_filtering_data() {
		return array(
			'empty_option' => true,
			'options'      => $this->get_meta_values_unserialized(),
		);
	}

}
