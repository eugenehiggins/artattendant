<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_PostObject extends ACA_ACF_Filtering {

	public function get_filtering_data() {

		if ( $this->column->is_serialized() ) {
			$values = $this->get_meta_values_unserialized();
		} else {
			$values = $this->get_meta_values();
		}

		return array(
			'empty_option' => true,
			'options'      => acp_filtering()->helper()->get_post_titles( $values ),
		);
	}

}
