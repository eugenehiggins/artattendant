<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability class for posts list screen
 *
 * @since 1.0
 */
class ACP_Export_Strategy_Post extends ACP_Export_Strategy {

	/**
	 * @param AC_ListScreenPost $list_screen
	 */
	public function __construct( AC_ListScreenPost $list_screen ) {
		parent::__construct( $list_screen );
	}

	/**
	 * @since 1.0
	 * @see   ACP_Export_ExportableListScreen::ajax_export()
	 */
	protected function ajax_export() {
		add_action( 'pre_get_posts', array( $this, 'modify_posts_query' ), 1e6 );
		add_filter( 'the_posts', array( $this, 'catch_posts' ), 10, 2 );
	}

	/**
	 * Modify the main posts query to use the correct pagination arguments. This should be attached
	 * to the pre_get_posts hook when an AJAX request is sent
	 *
	 * @param WP_Query $query
	 *
	 * @since 1.0
	 * @see   action:pre_get_posts
	 */
	public function modify_posts_query( $query ) {
		if ( $query->is_main_query() ) {
			$per_page = $this->get_num_items_per_iteration();
			$query->set( 'nopaging', false );
			$query->set( 'offset', $this->get_export_counter() * $per_page );
			$query->set( 'posts_per_page', $per_page );
			$query->set( 'posts_per_archive_page', $per_page );
			$query->set( 'fields', 'all' );
		}
	}

	/**
	 * Run the actual export when the posts query is finalized. This should be attached to the
	 * the_posts filter when an AJAX request is run
	 *
	 * @param array    $posts
	 * @param WP_Query $query
	 *
	 * @since 1.0
	 * @see   action:the_posts
	 */
	public function catch_posts( $posts, $query ) {
		if ( $query->is_main_query() ) {
			$this->export( wp_list_pluck( $posts, 'ID' ) );
		}

		return $posts;
	}

}
