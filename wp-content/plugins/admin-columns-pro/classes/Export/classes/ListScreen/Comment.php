<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability class for comments list screen
 *
 * @since 1.0
 */
class ACP_Export_ListScreen_Comment extends ACP_Export_ListScreen {

	/**
	 * @param AC_ListScreen_Comment $list_screen
	 */
	public function __construct( AC_ListScreen_Comment $list_screen ) {
		parent::__construct( $list_screen );
	}

	/**
	 * @since 1.0
	 * @see   ACP_Export_ExportableListScreen::ajax_export()
	 */
	protected function ajax_export() {
		// Hooks
		add_action( 'parse_comment_query', array( $this, 'comments_query' ), PHP_INT_MAX - 100 );
	}

	/**
	 * Catch the comments query and run it with altered parameters for pagination. This should be
	 * attached to the parse_comment_query hook when an AJAX request is sent
	 *
	 * @since 1.0
	 * @see   action:pre_get_posts
	 */
	public function comments_query( $query ) {
		if ( ! $query->query_vars['count'] ) {
			$per_page = $this->get_num_items_per_iteration();

			$query->query_vars['offset'] = $this->get_export_counter() * $per_page;
			$query->query_vars['number'] = $per_page;
			$query->query_vars['fields'] = 'ids';

			remove_action( 'parse_comment_query', array( $this, __FUNCTION__ ), PHP_INT_MAX - 100 );

			$modified_query = new WP_Comment_Query( $query->query_vars );
			$comments = $modified_query->get_comments();
			$this->export( $comments );
		}
	}

}
