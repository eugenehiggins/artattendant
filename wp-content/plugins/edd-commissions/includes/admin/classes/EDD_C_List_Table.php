<?php
/**
 * Commission list table
 *
 * @package     Easy Digital Downloads - Commissions
 * @subpackage  Classes/Discount
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Bootstrap WP_List_Table if necessary
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * The list table class
 *
 * @since       1.7
 */
class EDD_C_List_Table extends WP_List_Table {


	/**
	 * Number of results to show per page
	 *
	 * @since       1.7
	 * @var         int
	 */
	public $per_page = 10;


	/**
	 * Term counts
	 *
	 * @var null
	 */
	public $status_counts = null;


	/**
	 * Get things started
	 *
	 * @since       1.7
	 * @return      void
	 */
	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'commission',     //singular name of the listed records
			'plural'   => 'commissions',    //plural name of the listed records
			'ajax'     => false             //does this table support ajax?
		) );
	}


	/**
	 * Setup default column data
	 *
	 * @since       1.7
	 * @return      void
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'rate':
				$type = ! empty( $item[ 'type' ] ) ? $item[ 'type' ] : eddc_get_commission_type( $item['download'] );
				return eddc_format_rate( $item[ $column_name ], $type );
			case 'status':
				return $item[ $column_name ];
			case 'amount':
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			case 'date':
				return date_i18n( get_option( 'date_format' ), strtotime( $item['date'] ) );
			case 'download':
				$download = ! empty( $item['download'] ) ? $item['download'] : false;
				return $download ? '<a href="' . esc_url( add_query_arg( 'download', $download ) ) . '" title="' . __( 'View all commissions for this item', 'eddc' ) . '">' . get_the_title( $download ) . '</a>' . (!empty($item['variation']) ? ' - ' . $item['variation'] : '') : '';
			case 'payment':
				$payment = $item[ $column_name ];
				return $payment ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment ) ) . '" title="' . __( 'View payment details', 'eddc' ) . '">#' . $payment . '</a> - ' . edd_get_payment_status( get_post( $payment ), true  ) : '';
			case 'actions':
				echo '<a href="' . add_query_arg( array( 'view' => 'overview', 'commission' => $item['ID'] ) ) . '">' . __( 'View', 'eddc' ) . '</a>';
				break;
		}

		do_action( 'manage_edd_commissions_custom_column', $column_name, $item['ID'] );
	}


	/**
	 * Setup column titles
	 *
	 * @since       1.7
	 * @param       array $item The data for a given column
	 * @return      void
	 */
	function column_title( $item ) {
		//Build row actions
		$actions = array();
		$base    = admin_url( 'edit.php?post_type=download&page=edd-commissions' );

		if ( $item['status'] == 'revoked' ) {
			$actions['mark_as_accepted'] = sprintf( '<a href="%s&action=%s&commission=%s">' . __( 'Accept', 'eddc' ) . '</a>', $base, 'mark_as_accepted', $item['ID'] );
		} elseif ( $item['status'] == 'paid' ) {
			$actions['mark_as_unpaid'] = sprintf( '<a href="%s&action=%s&commission=%s">' . __( 'Mark as Unpaid', 'eddc' ) . '</a>', $base, 'mark_as_unpaid', $item['ID'] );
		} else {
			$actions['mark_as_paid'] = sprintf( '<a href="%s&action=%s&commission=%s">' . __( 'Mark as Paid', 'eddc' ) . '</a>', $base, 'mark_as_paid', $item['ID'] );
			$actions['mark_as_revoked'] = sprintf( '<a href="%s&action=%s&commission=%s">' . __( 'Revoke', 'eddc' ) . '</a>', $base, 'mark_as_revoked', $item['ID'] );
		}
		$actions['delete'] = sprintf( '<a href="%s&view=%s&commission=%s">' . __( 'Delete' ) . '</a>', $base, 'delete', $item['ID'] );

		$actions = apply_filters( 'edd_commission_row_actions', $actions, $item );

		$user = get_userdata( $item['user'] );

		if ( false !== $user ) {
			//Return the title contents
			return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
				/*$1%s*/ '<a href="' . esc_url( add_query_arg( 'user', $user->ID ) ) . '" title="' . __( 'View all commissions for this user', 'eddc' ) . '"">' . $user->display_name . '</a>',
				/*$2%s*/ $item['ID'],
				/*$3%s*/ $this->row_actions( $actions )
			);
		} else {
			return '<em>' . __( 'Invalid User', 'eddc' ) . '</em>';
		}
	}


	/**
	 * Output the checkbox for select all function
	 *
	 * @since       1.7
	 * @param       array $item The data for a given column
	 * @return      string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item['ID']
		);
	}


	/**
	 * Get available columns
	 *
	 * @since       1.7
	 * @return      array $columns The available columns
	 */
	function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'title'    => __( 'User', 'eddc' ),
			'download' => edd_get_label_singular(),
			'payment'  => __( 'Payment', 'eddc' ),
			'rate'     => __( 'Rate', 'eddc' ),
			'amount'   => __( 'Amount', 'eddc' ),
			'status'   => __( 'Status', 'eddc' ),
			'date'     => __( 'Date', 'eddc' ),
			'actions'  => __( 'Actions', 'eddc' )
		);

		$columns = apply_filters( 'manage_edd_commissions_columns', $columns );

		return $columns;
	}


	/**
	 * Get relevant table views
	 *
	 * @since       1.7
	 * @return      void
	 */
	function get_views() {
		$base        = admin_url( 'edit.php?post_type=download&page=edd-commissions' );
		$user_id     = $this->get_filtered_user();
		$download_id = $this->get_filtered_download();

		if ( ! empty( $user_id ) ) {
			$base = add_query_arg( array( 'user' => $user_id ), $base );
		}

		if ( ! empty( $download_id ) ) {
			$base = add_query_arg( array( 'download' => $download_id ), $base );
		}

		$current       = isset( $_GET['view'] ) ? $_GET['view'] : '';
		$status_counts = $this->get_commission_status_counts();

		$views = array(
			'all'       => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'view', $base ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'eddc' ), $status_counts['all'] ) . sprintf( _x( '(%d)', 'post count', 'eddc' ), $status_counts['all'] ),
			'unpaid'    => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'view', 'unpaid', $base ) ), $current === 'unpaid' ? ' class="current"' : '', __( 'Unpaid', 'eddc' ), $status_counts['unpaid'] ) . sprintf( _x( '(%d)', 'post count', 'eddc' ), $status_counts['unpaid'] ),
			'revoked'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'view', 'revoked', $base ) ), $current === 'revoked' ? ' class="current"' : '', __( 'Revoked', 'eddc' ), $status_counts['revoked'] ) . sprintf( _x( '(%d)', 'post count', 'eddc' ), $status_counts['revoked'] ),
			'paid'      => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'view', 'paid', $base ) ), $current === 'paid' ? ' class="current"' : '', __( 'Paid', 'eddc' ), $status_counts['paid'] ) . sprintf( _x( '(%d)', 'post count', 'eddc' ), $status_counts['paid'] ),
		);
		
		$views = apply_filters( 'manage_edd_commissions_views', $views, $base, $current, $status_counts );
		
		return $views;
	}


	/**
	 * Setup bulk actions
	 *
	 * @since       1.7
	 * @return      array $actions The registered bulk actions
	 */
	function get_bulk_actions() {
		$actions = array(
			'mark_as_paid'    => __( 'Mark as Paid', 'eddc' ),
			'mark_as_unpaid'  => __( 'Mark as Unpaid', 'eddc' ),
			'mark_as_revoked' => __( 'Mark as Revoked', 'eddc' ),
			'delete'          => __( 'Delete', 'eddc' )
		);

		$actions = apply_filters( 'manage_edd_commissions_bulk_actions', $actions );

		return $actions;
	}


	/**
	 * Retrieve the current page number
	 *
	 * @since       1.7
	 * @return      int
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Retrieves the user we are filtering commissions by, if any
	 *
	 * @access      private
	 * @since       1.7
	 * @return      integer Int if user ID, string if email or login
	 */
	function get_filtered_user() {
		$user_id = ! empty( $_GET['user'] ) ? sanitize_text_field( $_GET['user'] ) : 0;

		if ( ! is_numeric( $user_id ) ) {
			$user    = get_user_by( 'login', $_GET['user'] );
			$user_id = $user ? $user->data->ID : false;
		}

		return ! empty( $user_id ) ? absint( $user_id ) : false;
	}

	private function get_status() {
		return isset( $_GET['view'] ) ? strtolower( $_GET['view'] ) : '';
	}


	/**
	 * Retrieves the ID of the download we're filtering commissions by
	 *
	 * @access      private
	 * @since       1.7
	 * @return      int
	 */
	function get_filtered_download() {
		return ! empty( $_GET['download'] ) ? absint( $_GET['download'] ) : false;
	}


	/**
	 * Retrieves the ID of the download we're filtering commissions by
	 *
	 * @access      private
	 * @since       2.0
	 * @return      int
	 */
	function get_filtered_payment() {
		return ! empty( $_GET['payment'] ) ? absint( $_GET['payment'] ) : false;
	}


	/**
	 * Setup column titles
	 *
	 * @access      public
	 * @since       1.7
	 * @param       array $item The data for a given column
	 * @return      void
	 */
	function get_filtered_view() {
		return ! empty( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'all';
	}

	/**
	 * Process bulk actions
	 *
	 * @since       1.7
	 * @return      array
	 */
	function process_bulk_action() {
		$ids = isset( $_GET['commission'] ) ? $_GET['commission'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}
		
		$current_action = $this->current_action();

		foreach ( $ids as $id ) {
			
			do_action( 'edd_commissions_process_bulk_action_start', $id, $current_action );

			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				$commission = new EDD_Commission( $id );
				$commission->delete();
			}

			if ( 'mark_as_paid' === $this->current_action() ) {
				eddc_set_commission_status( $id, 'paid' );
			}

			if ( 'mark_as_unpaid' === $this->current_action() ) {
				eddc_set_commission_status( $id, 'unpaid' );
			}

			if ( 'mark_as_revoked' === $this->current_action() ) {
				eddc_set_commission_status( $id, 'revoked' );
			}

			if ( 'mark_as_accepted' === $this->current_action() ) {
				eddc_set_commission_status( $id, 'unpaid' );
			}

			do_action( 'edd_commissions_process_bulk_action_end', $id, $current_action );
			
		}
	}


	/**
	 * Gets commissions data
	 *
	 * @since       1.7
	 * @return      array
	 */
	function commissions_data() {
		$commissions_data = array();

		$paged      = $this->get_paged();
		$user       = $this->get_filtered_user();
		$status     = $this->get_status();
		$download   = $this->get_filtered_download();
		$payment_id = $this->get_filtered_payment();
		$offset     = ( $this->per_page * ( $paged - 1 ) );

		$commission_args = array(
			'status'  => $status,
			'number'  => $this->per_page,
			'offset'  => $offset,
			'orderby' => 'date_created',
			'order'   => 'DESC',
		);

		if ( ! empty( $user ) ) {
			$commission_args[ 'user_id' ] = $user;
		}

		if ( ! empty( $download ) ) {
			$commission_args[ 'download_id' ] = $download;
		}

		if ( ! empty( $payment_id ) ) {
			$commission_args[ 'payment_id' ] = $payment_id;
		}

		$commissions = edd_commissions()->commissions_db->get_commissions( $commission_args );

		if ( $commissions ) {
			foreach ( $commissions as $commission ) {

				$commissions_data[] = array(
					'ID'        => $commission->id,
					'title'     => $commission->description,
					'amount'    => $commission->amount,
					'rate'      => $commission->rate,
					'user'      => $commission->user_id,
					'download'  => $commission->download_id,
					'variation' => $commission->download_variation,
					'status'    => $commission->status,
					'payment'   => $commission->payment_id,
					'date'      => $commission->date_created,
					'type'      => $commission->type,
				);

			}
		}
		
		$commissions_data = apply_filters( 'edd_commissions_get_commission_data', $commissions_data );

		return $commissions_data;
	}


	/**
	 * Gets status counts
	 *
	 * @since       1.7
	 * @return      array
	 */
	function get_commission_status_counts() {
		if ( ! is_null( $this->status_counts ) ) {
			return $this->status_counts;
		}

		$user       = $this->get_filtered_user();
		$download   = $this->get_filtered_download();
		$payment_id = $this->get_filtered_payment();

		$base_args = array();
		if ( ! empty( $user ) ) {
			$base_args['user_id'] = $user;
		}

		if ( ! empty( $download ) ) {
			$base_args['download_id'] = $download;
		}

		if ( ! empty( $payment_id ) ) {
			$base_args['payment_id'] = $payment_id;
		}

		$paid    = edd_commissions()->commissions_db->count( array_merge( $base_args, array( 'status' => 'paid' ) ) );
		$unpaid  = edd_commissions()->commissions_db->count( array_merge( $base_args, array( 'status' => 'unpaid' ) ) );
		$revoked = edd_commissions()->commissions_db->count( array_merge( $base_args, array( 'status' => 'revoked' ) ) );

		$status_counts = array(
			'paid'    => $paid,
			'unpaid'  => $unpaid,
			'revoked' => $revoked,
		);

		$status_counts['all'] = $status_counts['paid'] + $status_counts['unpaid'] + $status_counts['revoked'];
		
		$status_counts = apply_filters( 'edd_commissions_get_status_counts', $status_counts, $base_args );

		$this->status_counts = $status_counts;

		return $this->status_counts;
	}


	/** ************************************************************************
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 * *************************************************************************/
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array(); // no hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$view          = $this->get_filtered_view();
		$status_counts = $this->get_commission_status_counts();

		$total_items = array_key_exists( $view, $status_counts ) ? $status_counts[ $view ] : $status_counts['all'];
		$this->items = $this->commissions_data();

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items/$this->per_page )   //WE have to calculate the total number of pages
		) );
	}
}
