<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_Image extends ACA_ACF_Filtering {

	public function get_filtering_data() {
		return array(
			'options' => acp_filtering()->helper()->get_post_titles( $this->get_meta_values() ),
		);
	}

}
