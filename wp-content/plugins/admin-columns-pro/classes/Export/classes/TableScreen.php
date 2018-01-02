<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all functionality for table screens, i.e., admin screens that have an items table (also
 * called "list table" on them). It activates whenever an Admin Columns list screen class is loaded
 *
 * @since 1.0
 */
class ACP_Export_TableScreen {

	/**
	 * Attached exportable list screen object, i.e., the exportable list screen (if any exist) for
	 * the current list screen
	 *
	 * @since 1.0
	 * @var ACP_Export_ListScreen
	 */
	private $exportable_list_screen;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'ac/table/list_screen', array( $this, 'load_list_screen' ) );
	}

	/**
	 * Load a list screen and potentially attach the proper exporting information to it
	 *
	 * @since 1.0
	 *
	 * @param AC_ListScreen $list_screen List screen for current table screen
	 */
	public function load_list_screen( $list_screen ) {
		// Fetch exportable list screen object
		$exportable_list_screen = ACP_Export_ListScreens::get_list_screen( $list_screen->get_key() );

		if ( ! $exportable_list_screen ) {
			return;
		}

		// Store reference to exportable list screen object
		$this->exportable_list_screen = $exportable_list_screen;

		// Attach exportable list screen
		$this->exportable_list_screen->attach();

		/**
		 * Fires when an exportable list screen is loaded for an active Admin Columns list screen
		 *
		 * @since 1.0
		 *
		 * @param AC_ListScreen                   $list_screen            Admin Columns list screen instance
		 * @param ACP_Export_ExportableListScreen $exportable_list_screen Exportable list screen
		 *                                                                class instance
		 * @param ACP_Export_TableScreen          $table_screen           Table screen class instance
		 */
		do_action( 'ac/export/table_screen/loaded_list_screen', $list_screen, $this->exportable_list_screen, $this );
	}

}
