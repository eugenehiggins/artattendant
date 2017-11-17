<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Commissions Meta DB Class
 *
 * @since  3.4
 */

class EDDC_Meta_DB extends EDD_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_commissionmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function get_columns() {
		return array(
			'meta_id'       => '%d',
			'commission_id' => '%d',
			'meta_key'      => '%s',
			'meta_value'    => '%s',
		);
	}

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function register_table() {
		global $wpdb;
		$wpdb->commissionmeta = $this->table_name;
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function get_column_defaults() {
		return array(
			'commission_id' => 0,
			'meta_key'      => '',
			'meta_value'    => 0,
		);
	}
	/**
	 * Retrieve commission meta field for a commission.
	 *
	 * For internal use only. Use EDD_Commission->get_meta() for public usage.
	 *
	 * @param   int    $commission_id   Commission ID.
	 * @param   string $meta_key        The meta key to retrieve.
	 * @param   bool   $single          Whether to return a single value.
	 * @return  mixed                   Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access  private
	 * @since   3.4
	 */
	public function get_meta( $commission_id = 0, $meta_key = '', $single = false ) {
		$commission_id = $this->sanitize_commission_id( $commission_id );
		if ( false === $commission_id ) {
			return false;
		}

		return get_metadata( 'commission', $commission_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a commission record.
	 *
	 * For internal use only. Use EDD_Commission->add_meta() for public usage.
	 *
	 * @param   int    $commission_id Commission ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  private
	 * @since   3.4
	 */
	public function add_meta( $commission_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$commission_id = $this->sanitize_commission_id( $commission_id );
		if ( false === $commission_id ) {
			return false;
		}

		if ( $unique ) {
			$commission = new EDD_Commission( $commission_id );
			$existing_meta = $commission->get_meta( $meta_key, true );
			if ( ! empty( $existing_meta ) ) {
				return false;
			}
		}

		return add_metadata( 'commission', $commission_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update commission meta field based on Commission ID.
	 *
	 * For internal use only. Use EDD_Commission->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Commission ID.
	 *
	 * If the meta field for the commission does not exist, it will be added.
	 *
	 * @param   int    $commission_id Commission ID.
	 * @param   string $meta_key      Metadata key.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   mixed  $prev_value    Optional. Previous value to check before removing.
	 * @return  bool                  False on failure, true if success.
	 *
	 * @access  private
	 * @since   3.4
	 */
	public function update_meta( $commission_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$commission_id = $this->sanitize_commission_id( $commission_id );
		if ( false === $commission_id ) {
			return false;
		}

		return update_metadata( 'commission', $commission_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a commission record.
	 *
	 * For internal use only. Use EDD_Commission->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param   int    $commission_id Commission ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Optional. Metadata value.
	 * @param   bool   $delete_all    If all items should be deleted or just the first
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  private
	 * @since   3.4
	 */
	public function delete_meta( $commission_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {
		return delete_metadata( 'commission', $commission_id, $meta_key, $meta_value, $delete_all );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			commission_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY commission_id (commission_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Given a commission ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since  3.4
	 * @param  int|stirng $commission_id A passed customer ID.
	 * @return int|bool                  The normalized customer ID or false if it's found to not be valid.
	 */
	private function sanitize_commission_id( $commission_id ) {
		if ( ! is_numeric( $commission_id ) ) {
			return false;
		}

		$commission_id = (int) $commission_id;

		// We were given a non positive number
		if ( absint( $commission_id ) !== $commission_id ) {
			return false;
		}

		if ( empty( $commission_id ) ) {
			return false;
		}

		return absint( $commission_id );

	}

}
