<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2
 */
class ACP_Column_Comment_PostType extends AC_Column
	implements ACP_Column_FilteringInterface {

	public function __construct() {
		$this->set_type( 'column-post_type' );
		$this->set_label( __( 'Post Type', 'codepress-admin-columns' ) );
	}

	public function get_value( $id ) {
		$post_type = $this->get_raw_value( $id );
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object ) {
			return false;
		}

		return $post_type_object->labels->singular_name;
	}

	public function get_raw_value( $id ) {
		$comment = get_comment( $id );

		return get_post_type( $comment->comment_post_ID );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_PostType( $this );
	}

}
