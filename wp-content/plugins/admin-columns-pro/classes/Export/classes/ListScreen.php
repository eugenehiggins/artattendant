<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for governing exporting for a list screen that is exportable. This class should be
 * extended, generally, per list screen. Furthermore, each instance of this class should be linked
 * to an Admin Columns list screen object
 *
 * @since 1.0
 */
abstract class ACP_Export_ListScreen {

	/**
	 * Admin Columns list screen object this object is attached to
	 *
	 * @since 1.0
	 * @var AC_ListScreen
	 */
	private $list_screen;

	/**
	 * Perform all required actions for when an AJAX export is requested. The parent class (this
	 * class) will perform the necessary validation, and the inheriting class should implement
	 * the actual functionality for setting up the items to be exported. The parent class's (this
	 * class) `export` method can then be used to actually export the items
	 *
	 * @since 1.0
	 */
	abstract protected function ajax_export();

	/**
	 * Constructor
	 *
	 * @since 1.0
	 *
	 * @param AC_ListScreen $list_screen Associated Admin Columns list screen object
	 */
	public function __construct( AC_ListScreen $list_screen ) {
		$this->list_screen = $list_screen;
	}

	/**
	 * Callback for when the list screen is loaded in Admin Columns, i.e., when it is active. Child
	 * classes should implement this method for any setup-related functionality
	 *
	 * @since 1.0
	 */
	public function attach() {
		$this->maybe_ajax_export();

		// Hooks
		add_action( 'admin_footer', array( $this, 'export_form' ) );
		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Check whether an AJAX export should be made, and validate the input data. Will call child's
	 * `ajax_export` method to do the actual exporting
	 *
	 * @since 1.0
	 */
	public function maybe_ajax_export() {
		// Check whether the user requested an export
		if ( 'acp_export_listscreen_export' !== filter_input( INPUT_POST, 'acp_export_action' ) ) {
			return;
		}

		// Validate nonce
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), 'acp_export_listscreen_export' ) ) {
			wp_send_json_error();
		}

		// Validate counter
		if ( $this->get_export_counter() === false ) {
			wp_send_json_error( __( 'Invalid value supplied for export counter.', 'codepress-admin-columns' ) );
		}

		// Validate hash
		if ( $this->get_export_hash() === false ) {
			wp_send_json_error( __( 'Invalid value supplied for export hash.', 'codepress-admin-columns' ) );
		}

		// Run the actual export
		$this->ajax_export();
	}

	/**
	 * Get the counter value passed for the AJAX export
	 *
	 * @since 1.0
	 *
	 * @return int Counter value, or false if there is no valid counter value
	 */
	protected function get_export_counter() {
		$counter = intval( filter_input( INPUT_POST, 'acp_export_counter', FILTER_SANITIZE_NUMBER_INT ) );

		return $counter >= 0 ? $counter : false;
	}

	/**
	 * Get the hash value passed for the AJAX export
	 *
	 * @since 1.0
	 *
	 * @return string|bool Hash value, or false if there is no valid hash value
	 */
	protected function get_export_hash() {
		$hash = filter_input( INPUT_POST, 'acp_export_hash', FILTER_DEFAULT );

		return $hash ? $hash : false;
	}

	/**
	 * Get the Admin Columns list screen object associated with this object
	 *
	 * @since 1.0
	 *
	 * @return AC_ListScreen Associated Admin Columns list screen object
	 */
	public function get_list_screen() {
		return $this->list_screen;
	}

	/**
	 * Retrieve the rows to export based on a set of item IDs. The rows contain the column data to
	 * export for each item
	 *
	 * @since 1.0
	 *
	 * @param array [int] $items IDs of the items to export
	 *
	 * @return array[mixed] Rows to export. One row is returned for each item ID
	 */
	public function get_rows( $ids ) {
		// Retrieve list screen columns
		$columns = $this->get_list_screen()->get_columns();

		// Construct CSV rows
		$rows = array();
		$headers = $this->get_headers( $columns );

		foreach ( $ids as $id ) {
			$row = array();

			foreach ( $columns as $column ) {
				$header = $column->get_name();

				if ( ! isset( $headers[ $header ] ) ) {
					continue;
				}

				$model = $column instanceof ACP_Export_Column
					? $column->export()
					: new ACP_Export_Model_RawValue( $column );

				$value = $model->get_value( $id );

				/**
				 * Filter the column value exported to CSV or another file format in the
				 * exportability add-on. This filter is applied to each value individually, i.e.,
				 * once for every column for every item in the list screen.
				 *
				 * @since 1.0
				 *
				 * @param string                $value                  Column value to export for item
				 * @param AC_Column             $column                 Column object to export for
				 * @param int                   $id                     Item ID to export for
				 * @param ACP_Export_ListScreen $exportable_list_screen Exportable list screen instance
				 */
				$value = apply_filters( 'ac/export/value', $value, $column, $id, $this );

				// Add column to row data
				$row[ $header ] = $value;
			}

			// Add current row to list of rows
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Retrieve the headers for the columns
	 *
	 * @param AC_Column[] $columns
	 *
	 * @since 1.0
	 * @return string[] Associative array of header labels for the columns.
	 */
	public function get_headers( array $columns ) {
		$headers = array();

		foreach ( $columns as $column ) {
			// Don't add columns that are not active
			if ( $column instanceof ACP_Export_Column && ! $column->export()->is_active() ) {
				continue;
			}

			$header = $column->get_name();
			$label = strip_tags( $column->get_setting( 'label' )->get_value() );

			if ( empty( $label ) ) {
				$label = $header;
			}

			$headers[ $header ] = $label;
		}

		return $headers;
	}

	/**
	 * Register and enqueue scripts when on a list screen page
	 *
	 * @since 1.0
	 */
	public function scripts() {
		global $wp_list_table;

		if ( ! $wp_list_table ) {
			return;
		}

		$url = ac_addon_export()->get_plugin_url();
		$version = ac_addon_export()->get_version();

		// Register and enqueue styles
		wp_enqueue_style( 'acp-export-listscreen', $url . 'assets/css/listscreen.css', array(), $version );

		// Register and enqueue scripts
		wp_enqueue_script( 'acp-export-listscreen', $url . 'assets/js/listscreen.js', array( 'jquery' ), $version );

		// Script localization
		wp_localize_script( 'acp-export-listscreen', 'ACP_Export', array(
			'total_num_items' => $wp_list_table->get_pagination_arg( 'total_items' ),
			'i18n'            => array(
				'Export'                                                                                                                           => __( 'Export', 'codepress-admin-columns' ),
				'Export to CSV'                                                                                                                    => __( 'Export to CSV', 'codepress-admin-columns' ),
				'Exporting current list of items.'                                                                                                 => __( 'Exporting current list of items.', 'codepress-admin-columns' ),
				'Processed {0} of {1} items ({2}%).'                                                                                               => __( 'Processed {0} of {1} items ({2}%).', 'codepress-admin-columns' ),
				'Export completed ({0} items). Your download will start automatically. If this does not happen, you can download the file again: ' => __( 'Export completed ({0} items). Your download will start automatically. If this does not happen, you can download the file again: ', 'codepress-admin-columns' ),
				'Download File'                                                                                                                    => __( 'Download File', 'codepress-admin-columns' ),
			),
		) );
	}

	/**
	 * Output the form that holds the export query arguments
	 *
	 * @since 1.0
	 */
	public function export_form() {
		?>
		<form action="" method="post" id="acp-export">
			<?php wp_nonce_field( 'acp_export_listscreen_export', '_wpnonce', false ); ?>
			<input type="submit" class="button button-secondary"/>
		</form>
		<?php
	}

	/**
	 * Export a list of items, given the item IDs, and sends the output as JSON to the requesting
	 * AJAX process
	 *
	 * @since 1.0
	 *
	 * @param array [int] $items Array of item IDs
	 */
	public function export( $ids ) {
		// Retrieve list screen items and columns
		$rows = $this->get_rows( $ids );

		if ( count( $rows ) > 0 ) {
			// Create CSV exporter
			$exporter = new ACP_Export_Exporter_CSV();
			$exporter->load_data( $rows );

			if ( $this->get_export_counter() === 0 ) {
				$exporter->load_column_labels( $this->get_headers( $this->get_list_screen()->get_columns() ) );
			}

			// Base of file name path
			$export_dir = ac_addon_export()->get_export_dir();
			$fname = md5( get_current_user_id() . $this->get_export_hash() ) . '.csv';
			$fpath = $export_dir['path'] . $fname;
			$fpath .= '-' . $this->get_export_counter() . '.csv';

			// Write CSV output to file
			$fh = fopen( $fpath, 'w' );
			$exporter->export( $fh, true );
			fclose( $fh );
		}

		$download_url = add_query_arg( array(
			'acp-export-download'        => $this->get_export_hash(),
			'acp-export-filename-prefix' => $this->get_list_screen()->get_label(),
		), admin_url( '/' ) );

		wp_send_json_success( array(
			'num_rows_processed' => count( $rows ),
			'download_url'       => $download_url,
		) );
	}

	/**
	 * Get the filtered number of items per iteration of the exporting algorithm
	 *
	 * @since 1.0
	 *
	 * @return int Number of items per export iteration
	 */
	protected function get_num_items_per_iteration() {
		/**
		 * Filters the number of items to export per iteration of the exporting mechanism. It
		 * controls the number of items per batch, i.e., the number of items to process at once:
		 * the final number of items in the export file does not depend on this parameter
		 *
		 * @since 1.0
		 *
		 * @param int                   $num_items              Number of items per export iteration
		 * @param ACP_Export_ListScreen $exportable_list_screen Exportable list screen instance
		 */
		return apply_filters( 'ac/export/exportable_list_screen/num_items_per_iteration', 250, $this );
	}

}
