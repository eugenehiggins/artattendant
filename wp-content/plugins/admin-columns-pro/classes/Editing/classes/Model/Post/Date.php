<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Date extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		$post = get_post( $id );

		if ( ! $post ) {
			return null;
		}

		if ( in_array( $post->post_status, array( 'draft', 'inherit' ) ) ) {
			return null;
		}

		return $post->post_date;
	}

	public function get_view_settings() {
		return array(
			'type' => 'date_time',
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array(
			'post_date'     => $value,
			'post_date_gmt' => get_gmt_from_date( $value ),
		) );
	}

}
