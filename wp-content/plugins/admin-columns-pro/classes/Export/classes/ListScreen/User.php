<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exportability class for users list screen
 *
 * @since 1.0
 */
class ACP_Export_ListScreen_User extends ACP_Export_ListScreen {

	/**
	 * @param AC_ListScreen_User $list_screen
	 */
	public function __construct( AC_ListScreen_User $list_screen ) {
		parent::__construct( $list_screen );
	}

	/**
	 * @since 1.0
	 * @see   ACP_Export_ExportableListScreen::ajax_export()
	 */
	protected function ajax_export() {
		// Hooks
		add_filter( 'users_list_table_query_args', array( $this, 'catch_users_query' ), PHP_INT_MAX - 100 );
	}

	/**
	 * Modify the users query to use the correct pagination arguments, and epxort the resulting
	 * items. This should be attached to the users_list_table_query_args hook when an AJAX request
	 * is sent
	 *
	 * @since 1.0
	 * @see   filter:users_list_table_query_args
	 */
	public function catch_users_query( $args ) {
		$per_page = $this->get_num_items_per_iteration();

		$args['offset'] = $this->get_export_counter() * $per_page;
		$args['number'] = $per_page;
		$args['fields'] = 'ids';

		// Construct users query
		$query = new WP_User_Query( $args );

		// Export
		$this->export( $query->get_results() );
	}

}
