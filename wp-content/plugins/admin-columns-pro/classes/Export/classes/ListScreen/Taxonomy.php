<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability class for terms list screen
 *
 * @since 1.0
 */
class ACP_Export_ListScreen_Taxonomy extends ACP_Export_ListScreen {

	/**
	 * @param ACP_ListScreen_Taxonomy $list_screen
	 */
	public function __construct( ACP_ListScreen_Taxonomy $list_screen ) {
		parent::__construct( $list_screen );
	}

	/**
	 * @since 1.0
	 * @see   ACP_Export_ExportableListScreen::ajax_export()
	 */
	protected function ajax_export() {
		// Hooks
		add_action( 'parse_term_query', array( $this, 'terms_query' ), PHP_INT_MAX - 100 );
	}

	/**
	 * Catch the terms query and run it with altered parameters for pagination. This should be
	 * attached to the parse_term_query hook when an AJAX request is sent
	 *
	 * @since 1.0
	 * @see   action:parse_term_query
	 */
	public function terms_query( $query ) {
		if ( $query->query_vars['fields'] === 'count' ) {
			$per_page = $this->get_num_items_per_iteration();

			$query->query_vars['offset'] = $this->get_export_counter() * $per_page;
			$query->query_vars['number'] = $per_page;
			$query->query_vars['fields'] = 'ids';

			remove_action( 'parse_term_query', array( $this, __FUNCTION__ ), PHP_INT_MAX - 100 );

			$modified_query = new WP_Term_Query( $query->query_vars );
			$terms = $modified_query->get_terms();
			$this->export( $terms );
		}
	}

}
