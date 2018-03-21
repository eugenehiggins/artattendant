<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Menu extends ACP_Editing_Model_Menu {

	/**
	 * @param int $id
	 *
	 * @return string|false
	 */
	protected function get_title( $id ) {
		return get_post_field( 'post_title', $id );
	}

}
