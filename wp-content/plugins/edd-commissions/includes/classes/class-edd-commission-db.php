<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Commissions DB Class
 *
 * @since  3.4
 */

class EDDC_DB extends EDD_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_commissions';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'user_id'      => '%d',
			'amount'       => '%f',
			'status'       => '%s',
			'download_id'  => '%d',
			'payment_id'   => '%d',
			'cart_index'   => '%d',
			'price_id'     => '%d',
			'date_created' => '%s',
			'date_paid'    => '%s',
			'type'         => '%s',
			'rate'         => '%f',
			'currency'     => '%s',
		);
	}

	/**
	 * Returns the column labels only.
	 *
	 * @since 3.4
	 * @return array
	 */
	public function get_column_labels() {
		return array_keys( $this->get_columns() );
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function get_column_defaults() {
		return array(
			'user_id'      => 0,
			'amount'       => '',
			'status'       => 'unpaid',
			'download_id'  => 0,
			'payment_id'   => '',
			'cart_index'   => 0,
			'price_id'     => 1,
			'date_created' => date( 'Y-m-d H:i:s' ),
			'date_paid'    => '',
			'type'         => '',
			'rate'         => 0.00,
			'currency'     => edd_get_currency(),
		);
	}

	/**
	 * Retrieve all commissions for a customer
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function get_commissions( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'search'  => '',
			'user_id' => 0,
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		// Account for 'paged' in legacy $args
		if ( isset( $args['paged'] ) && $args['paged'] > 1 ) {
			$number         = isset( $args['number'] ) ? $args['number'] : $defaults['number'];
			$args['offset'] = ( ( $args['paged'] - 1 ) * $number );
			unset( $args['paged'] );
		}

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = $this->parse_where( $args );

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if( 'amount' == $args['orderby'] ) {
			$args['orderby'] = 'amount+0';
		}

		$cache_key = md5( 'edd_commissions_' . serialize( $args ) );

		//$commissions = wp_cache_get( $cache_key, 'commissions' );
		$commissions = false;

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if( $commissions === false ) {
			$commissions = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ), 0 );

			if( ! empty( $commissions ) ) {

				foreach( $commissions as $key => $commission ) {
					$commissions[ $key ] = new EDD_Commission( $commission );
				}

				wp_cache_set( $cache_key, $commissions, 'commissions', 3600 );

			}

		}

		return $commissions;
	}

	/**
	 * Count the total number of commissions in the database
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function count( $args = array() ) {
		global $wpdb;

		$where     = $this->parse_where( $args );
		$cache_key = md5( 'edd_commissions_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'commissions' );

		if( $count === false ) {

			$sql   = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};";
			$count = $wpdb->get_var( $sql );

			wp_cache_set( $cache_key, $count, 'commissions', 3600 );

		}

		return absint( $count );

	}

	public function sum( $column = '', $args ) {

		global $wpdb;

		$summable_columns = apply_filters( 'edd_commissions_summable_columns', array( 'amount' ) );
		if ( ! in_array( $column, $summable_columns ) ) {
			return 0;
		}

		$where     = $this->parse_where( $args );
		$cache_key = md5( 'edd_commissions_sum_' . $column . serialize( $args ) );

		$sum = wp_cache_get( $cache_key, 'commissions' );
		if( $sum === false ) {

			$sql   = "SELECT SUM({$column }) FROM " . $this->table_name . "{$where};";
			$sum = $wpdb->get_var( $sql );

			wp_cache_set( $cache_key, $sum, 'commissions', 3600 );

		}

		return (float) $sum;

	}

	private function parse_where( $args ) {
		$where = '';

		// Specific users
		if( ! empty( $args['user_id'] ) ) {

			if( is_array( $args['user_id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['user_id'] ) );
			} else {
				$ids = intval( $args['user_id'] );
			}

			$where .= " AND `user_id` IN( {$ids} ) ";

		}

		if ( ! empty( $args['payment_id'] ) ) {

			if( is_array( $args['payment_id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['payment_id'] ) );
			} else {
				$ids = intval( $args['payment_id'] );
			}

			$where .= " AND `payment_id` IN( {$ids} ) ";

		}


		// Specific Downloads
		if ( ! empty( $args['download_id'] ) ) {
			if ( is_array( $args['download_id'] ) ) {
				$download_ids = implode( "','", array_map( 'absint', $args['download_id'] ) );
			} else {
				$download_ids = absint( $args['download_id'] );
			}

			$where .= " AND `download_id` IN( '{$download_ids}' ) ";
		}

		// Specific statuses
		if( ! empty( $args['status'] ) ) {

			if( is_array( $args['status'] ) ) {
				$statuses = implode( "','", array_map( 'sanitize_text_field', $args['status'] ) );
			} else {
				$statuses = sanitize_text_field( $args['status'] );
			}

			$where .= " AND `status` IN( '{$statuses}' ) ";

		}

		// Created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					$where .= " AND `date_created` >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		// Specific paid date or in a paid date range
		if( ! empty( $args['date_paid'] ) ) {

			if( is_array( $args['date_paid'] ) ) {

				if( ! empty( $args['date_paid']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date_paid']['start'] ) );

					$where .= " AND `date_paid` >= '{$start}'";

				}

				if( ! empty( $args['date_paid']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date_paid']['end'] ) );

					$where .= " AND `date_paid` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date_paid'] ) );
				$month = date( 'm', strtotime( $args['date_paid'] ) );
				$day   = date( 'd', strtotime( $args['date_paid'] ) );

				$where .= " AND $year = YEAR ( date_paid ) AND $month = MONTH ( date_paid ) AND $day = DAY ( expiration )";
			}

		}

		if ( ! empty( $args['query_args']['date_query'] ) ) {
			$after  = $args['query_args']['date_query']['after'];
			$after_date  = false;
			if ( ! empty( $after ) ) {
				if ( is_array( $after ) ) {
					$after_date = $after['year'] . '-' . $after['month'] . '-' . $after['day'] . ' 00:00:00';
				} else {
					$after_date = $after;
				}
			}

			$before = $args['query_args']['date_query']['before'];
			$before_date = false;
			if ( ! empty( $before ) ) {
				if ( is_array( $before ) ) {
					$before_date = $before['year'] . '-' . $before['month'] . '-' . $before['day'] . ' 23:59:59';
				} else {
					$before_date = $before;
				}
			}

			if ( ! empty( $after_date ) && ! empty( $before_date ) ) {
				$where .= " AND date_created BETWEEN CAST('$after_date' AS DATETIME) AND CAST('$before_date' AS DATETIME)";
			} else {
				if ( ! empty( $after_date ) ) {
					$where .= " AND date_created >= CAST('$after_date' AS DATETIME)";
				}

				if ( ! empty( $before_date ) ) {
					$where .= " AND date_created <= CAST('$before_date' AS DATETIME)";
				}
			}
		}



		if ( ! empty( $args['hour'] ) ) {
			$where .= " AND {$args['hour']} = HOUR( date_created )";
		}

		if ( ! empty( $args['day'] ) ) {
			$where .= " AND {$args['day']} = DAY( date_created )";
		}

		if ( ! empty( $args['month'] ) ) {
			$where .= " AND {$args['month']} = MONTH( date_created )";
		}

		if ( ! empty( $args['year'] ) ) {
			$where .= " AND {$args['year']} = YEAR( date_created )";
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   3.4
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		amount mediumtext NOT NULL,
		status varchar(20) NOT NULL,
		download_id bigint(20) NOT NULL,
		payment_id varchar(60) NOT NULL,
		cart_index bigint(20) NOT NULL,
		price_id bigint(20) NOT NULL,
		date_created datetime NOT NULL,
		date_paid datetime NOT NULL,
		type varchar(20) NOT NULL,
		rate DOUBLE NOT NULL,
		currency varchar(10) NOT NULL,
		PRIMARY KEY  (id),
		KEY download_id (download_id),
		KEY payment_id (payment_id),
		KEY user_id (user_id),
		KEY payment_id_and_cart_index ( payment_id, cart_index),
		KEY download_id_and_price_id ( download_id, price_id ),
		KEY user_id_download_id ( user_id, download_id )
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		if ( $this->table_exists( $this->table_name ) ) {
			update_option( $this->table_name . '_db_version', $this->version );
		}
	}

}
