<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Media_Date extends ACP_Editing_Model_Post_Date {

	public function get_edit_value( $id ) {
		$post = get_post( $id );

		if ( ! $post ) {
			return null;
		}

		return $post->post_date;
	}

}
