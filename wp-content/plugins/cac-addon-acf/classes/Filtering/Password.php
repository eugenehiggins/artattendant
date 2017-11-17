<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Password extends ACA_ACF_Filtering {

	public function get_filtering_data() {
		return array(
			'empty_option' => true,
			'options'      => array(),
		);
	}

}
