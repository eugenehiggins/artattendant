<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Number extends ACA_ACF_Filtering {

	public function get_data_type() {
		return 'numeric';
	}

	public function is_ranged() {
		return true;
	}

}
