<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_PostType extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		add_filter( 'comments_clauses', array( $this, 'filter_on_post_type' ) );

		return $vars;
	}

	public function filter_on_post_type( $comments_clauses ) {
		global $wpdb;

		$comments_clauses['join'] .= " JOIN wp_posts as pst ON {$wpdb->comments}.comment_post_ID = pst.ID";
		$comments_clauses['where'] .= $wpdb->prepare( " AND pst.post_type = %s", $this->get_filter_value() );

		return $comments_clauses;
	}

	public function get_filtering_data() {
		$options = array();
		$post_types = get_post_types( array(), 'object' );

		foreach ( $post_types as $key => $post_type ) {
			if ( ! post_type_supports( $key, 'comments' ) ) {
				continue;
			}

			$options[ $key ] = $post_type->labels->singular_name;
		}

		return array(
			'options' => $options,
		);
	}

}
