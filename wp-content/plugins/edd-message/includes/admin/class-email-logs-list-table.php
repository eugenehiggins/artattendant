<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Message_Log_Table Class
 *
 * Renders the sales log list table
 *
 * @since 0.1
 */
class EDD_Message_Log_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @since 0.1
	 * @var int
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 0.1
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => edd_get_label_singular(),
			'plural'   => edd_get_label_plural(),
			'ajax'     => false,
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 0.1
	 *
	 * @param array $item Contains all the data of the log item
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$return   = '';

		switch ( $column_name ){

			case 'sent_by' :
				$user = get_userdata( $item['sent_by'] );
				$return = $user->display_name;
				break;

			case 'recipient' :
				$return = $item['recipient'];
				break;

			case 'subject' :
				$return = $item['subject'];
				break;

			default:
				$return = $item[ $column_name ];
				break;
		}

		return $return;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 0.1
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => __( 'Log ID', 'edd-message' ),
			'sent_by'    => __( 'Sent by', 'edd-message' ),
			'recipient'    => __( 'Recipient', 'edd-message' ),
			'subject'    => __( 'Subject', 'edd-message' ),
			'date'       => __( 'Date', 'edd-message' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 0.1
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since 0.1
	 * @return string|false string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since 0.1
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_log_views();
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since 0.1
	 * @global object $edd_logs EDD Logs Object
	 * @return array $logs_data Array of all the Log entries
	 */
	public function get_logs() {
		global $edd_logs;

		// Prevent the queries from getting cached. Without this there are occasional memory issues for some installs
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();

		$log_query = array(
			'log_type'    => 'email',
			'paged'       => $paged,
			'orderby'   => 'ID',
		);

		$logs = $edd_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$to = get_post_meta( $log->ID, '_edd_log_to', true );

					$logs_data[] = array(
						'ID'         => $log->ID,
						'sent_by'    => $log->post_author,
						'recipient'  => implode( '<br/>', $to ),
						'subject'  => $log->post_title,
						'date'       => get_post_field( 'post_date', $log->ID ),
					);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 0.1
	 * @global object $edd_logs EDD Logs Object
	 * @uses EDD_Message_Log_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses EDD_Message_Log_Table::get_pagenum()
	 * @uses EDD_Message_Log_Table::get_logs()
	 * @uses EDD_Message_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {
		global $edd_logs;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = $edd_logs->get_log_count( 0, 'email' );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}

	/**
	 * Since our "bulk actions" are navigational, we want them to always show, not just when there's items
	 *
	 * @access public
	 * @since 2.5
	 * @return bool
	 */
	public function has_items() {
		return true;
	}
}