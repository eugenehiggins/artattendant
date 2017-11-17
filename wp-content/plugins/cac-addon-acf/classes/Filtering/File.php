<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_File extends ACA_ACF_Filtering {

	public function get_filtering_data() {
		$options = array();

		if ( $ids = $this->get_meta_values() ) {
			foreach ( $ids as $post_id ) {
				$options[ $post_id ] = basename( get_attached_file( $post_id ) );
			}
		}

		return array(
			'options' => $options,
		);
	}

}
