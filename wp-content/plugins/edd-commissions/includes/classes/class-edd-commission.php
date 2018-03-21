<?php
/**
 * Commission Object
 *
 * @package     Easy Digital Downloads - Commissions
 * @subpackage  Classes/Commission
 * @copyright   Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Commission Class
 *
 * @since 3.3
 */
class EDD_Commission {


	/**
	 * Commission ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $id = 0;


	/**
	 * User ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $user_id = 0;


	/**
	 * Description (same as post_title).
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $description = null;

	/**
	 * The Download Variation name
	 *
	 * @since 3.3
	 * @access protected
	 * @var string
	 */
	protected $download_variation = null;


	/**
	 * Commission Rate.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         mixed float|int
	 */
	protected $rate = 0.00;


	/**
	 * Commission Type.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $type = null;


	/**
	 * Commission Amount.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         mixed float|int
	 */
	protected $amount = 0.00;


	/**
	 * Currency.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $currency = null;


	/**
	 * Download ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $download_id = 0;


	/**
	 * Payment ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $payment_id = 0;


	/**
	 * Status.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $status = null;

	/**
	 * The position the item commissioned was in the cart
	 *
	 * @since   3.4
	 * @access  protected
	 * @var     string
	 *
	 */
	protected $cart_index = 0;

	/**
	 * Is Renewal?
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         bool
	 */
	protected $is_renewal = false;

	/**
	 * Download variation (if any).
	 *
	 * @since       3.4
	 * @access      protected
	 * @var         string
	 */
	protected $price_id = null;

	/**
	 * The date the commission was recorded
	 *
	 * @since   3.4
	 * @access  protected
	 * @var     string
	 *
	 */
	protected $date_created = null;

	/**
	 * The date the commission was paid
	 *
	 * @since   3.4
	 * @access  protected
	 * @var     string
	 *
	 */
	protected $date_paid = null;


	/**
	 * Array of items that have changed since the last save() was run.
	 * This is for internal use, to allow fewer update calls to be run.
	 *
	 * @since       3.3
	 * @access      private
	 * @var         array
	 */
	private $pending;

	/**
	 * Constructor.
	 *
	 * @since       3.3
	 * @access      protected
	 * @param       int $id Commission ID.
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$id         = absint( $id );
		$commission = edd_commissions()->commissions_db->get_by( 'id', $id );

		if ( ! empty( $commission ) ) {
			$this->setup_commission( $commission );
		}
	}


	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       mixed $key
	 * @return      mixed
	 */
	public function __get( $key ) {
		$key = $this->sanitize_key( $key );

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};
		} else {
			return new WP_Error( 'edd-commissions-invalid-property', sprintf( __( 'Can\'t get property %s', 'eddc' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since       3.3
	 * @access      public
	 * @see         set()
	 * @param       string $key Property name.
	 * @param       mixed $value Property value.
	 *
	 * @return mixed
	 */
	public function __set( $key, $value ) {
		$key = $this->sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		$this->pending[ $key ] = $value;

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}
	}


	/**
	 * Magic __isset method to allow empty checks on protected elements
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $key The attribute to get
	 * @return      boolean If the item is set or not
	 */
	public function __isset( $key ) {
		$key = $this->sanitize_key( $key );

		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}


	/**
	 * Converts the instance of the EDD_Discount object into an array for special cases.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      array EDD_Discount object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}


	/**
	 * Setup object vars with commission WP_Post object.
	 *
	 * @since       3.3
	 * @access      private
	 * @param       object $commission WP_Post instance of the commission.
	 * @return      bool Object var initialisation successful or not.
	 */
	private function setup_commission( $commission = null ) {
		$this->pending = array();

		if ( empty( $commission ) ) {
			return false;
		}

		/**
		 * Fires before the instance of the EDD_Commission object is set up.
		 *
		 * @since 3.3
		 *
		 * @param object EDD_Commission      EDD_Commission instance of the commission object.
		 * @param object WP_Post $commission WP_Post instance of the commission object.
		 */
		do_action( 'eddc_pre_setup_commission', $this, $commission );

		/**
		 * Setup all object variables
		 */
		$this->rate        = (float) $commission->rate;
		$this->type        = $commission->type;
		$this->amount      = (float) $commission->amount;
		$this->currency    = $commission->currency;
		$this->download_id = absint( $commission->download_id );
		$this->payment_id  = absint( $commission->payment_id );
		$this->user_id     = (int) $commission->user_id;
		$this->status      = $commission->status;
		$this->price_id    = absint( $commission->price_id );
		$this->is_renewal  = (bool) $this->setup_is_renewal();
		$this->download_variation = $this->setup_download_variation();
		$this->description = $this->setup_description();
		$this->cart_index  = absint( $commission->cart_index );

		/**
		 * Setup discount object vars with WP_Post vars
		 */
		foreach ( get_object_vars( $commission ) as $key => $value ) {
			$this->{$key} = $value;
		}

		/**
		 * Fires after the instance of the EDD_Commission object is set up. Allows extensions to add items to this object via hook.
		 *
		 * @since       3.3
		 * @param       object EDD_Commission EDD_Commission instance of the commission object.
		 * @param       object WP_Post $commission WP_Post instance of the commission object.
		 */
		do_action( 'eddc_after_setup_commission', $this, $commission );

		return true;
	}

	/**
	 * Setup Functions
	 *
	 * Setup the property that determines whether the commission is a renewal or not.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      bool Is renewal?
	 */
	private function setup_is_renewal() {
		$is_renewal = $this->get_meta( 'is_renewal', true );
		return (bool) $is_renewal;
	}

	private function setup_download_variation() {
		$variation = '';
		$download  = new EDD_Download( $this->download_id );
		if ( $download->has_variable_prices() ) {
			$variation = edd_get_price_option_name( $this->download_id, $this->price_id, $this->payment_id );
		}

		return $variation;
	}

	private function setup_description() {
		$payment     = new EDD_Payment( $this->payment_id );
		$download    = new EDD_Download( $this->download_id );
		$description = $payment->email . ' - ' . $download->get_name();

		return $description;
	}

	/**
	 * Helper method to retrieve meta data associated with the commission.
	 *
	 * @since       3.3
	 * @since       3.4 - Updated to look at custom table columns before going to meta table.
	 * @access      public
	 * @param       string $key Meta key.
	 * @param       bool $single Return single item or array.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true ) {

		// Since 3.4, we've moved some of the meta into columns of the custom table, identify those and return.
		$core_columns = edd_commissions()->commissions_db->get_column_labels();
		if ( in_array( $key, $core_columns ) ) {
			return $this->$key;
		}

		if ( 'description' === $key ) {
			return $this->description;
		}

		if ( '_' !== mb_substr( $key, 0, 1, 'utf-8') ) {
			$key = '_edd_commission_' . $key;
		}

		$meta = edd_commissions()->commission_meta_db->get_meta( $this->id, $key, $single );

		// Run a wildcard filter for any meta we're getting
		$meta = apply_filters( 'eddc_commission_' . $key, $meta, $this->id );

		return $meta;

	}

	/**
	 * Retrieve the paid status of a commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      string Status.
	 */
	public function get_status() {
		/**
		 * Allow the paid status of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $status Paid status of a commission.
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_get_commission_status', $this->status, $this->id );
	}

	/**
	 * Returns the commission rate type, accounting for a time for when we did not store the commission type on the record
	 *
	 * @since 3.4
	 * @return string
	 */
	public function get_type() {
		if ( empty( $this->type ) ) {
			$this->type = eddc_get_commission_type( $this->download_id );
		}

		return $this->type;
	}


	/**
	 * Update the status of a commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $new_status New status.
	 * @return      void
	 */
	public function set_status( $new_status = 'unpaid' ) {
		do_action( 'eddc_pre_set_commission_status', $this->id, $new_status, $this->status );

		// Only run the update_status method if someone isn't using the save methods
		if ( empty( $this->pending['status'] ) ) {
			$this->update_status( $new_status );
		}

		do_action( 'eddc_set_commission_status', $this->id, $new_status, $this->status );
	}


	/**
	 * Retrieve whether or not this commission is a renewal.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool Is renewal?
	 */
	public function get_is_renewal() {
		/**
		 * Allow the renewal flag of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $is_renewal Is the commission a renewal?
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_commission_is_renewal', $this->is_renewal, $this->ID );
	}


	/**
	 * Retrieve the description (post_title) for the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      string Commission description.
	 */
	public function get_description() {
		/**
		 * Allow the description of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $description Commission description.
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_commission_description', $this->description, $this->id );
	}

	/**
	 * When the post_date is requested, return the 'date_crated' property as that's the replacement in 3.4
	 *
	 * @since 3.4
	 * @deprected     The 'post_date' is deprecated since 3.4, when we moved to custom tables and post_date was no longer relevant.
	 * @return string
	 */
	private function get_post_date() {
		$backtrace = debug_backtrace();
		_edd_deprected_argument( 'post_date', 'EDD_Commission::$post_date', 3.4, 'date_created', $backtrace );
		return $this->date_created;
	}

	/**
	 * Check if a commission exists.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool Commission exists.
	 */
	public function exists() {
		if ( $this->id > 0 ) {
			return true;
		}

		return false;
	}


	/**
	 * Create a new commission. If the commission already exists in the database, update it.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new commission.
	 */
	private function add() {

		/**
		 * Allow the arguments passed to `wp_insert_post` to be filtered.
		 *
		 * @since       3.3
		 * @param       array $legacy_args {
		 *     @type string $post_title    Post title.
		 *     @type string $post_status   Post status.
		 *     @type string $post_type     Post type
		 *     @type string $post_date     Post date.
		 *     @type string $post_date_gmt Post date in the GMT timezone.
		 * }
		 */
		$legacy_args = apply_filters( 'eddc_insert_commission_args', array(
			'post_title'    => $this->description,
			'post_status'   => 'publish',
			'post_type'     => 'edd_commission',
			'post_date'     => ! empty( $this->date ) ? $this->date : null,
			'post_date_gmt' => ! empty( $this->date ) ? get_gmt_from_date( $this->date ) : null
		) );

		/**
		 * Allow the commission information to be filtered.
		 *
		 * @since       3.3
		 * @param       array $args {
		 *     Filterable metadata.
		 *
		 *     @type int             $user_id  User ID.
		 *     @type mixed int|float $rate     Commission rate.
		 *     @type mixed int|float $amount   Commission amount.
		 *     @type string          $currency Currency (e.g. USD).
		 * }
		 * @param       int $ID Commission ID.
		 * @param       int $payment_ID Payment ID linked to the commission.
		 * @param       int $download_ID Download ID linked to the commission.
		 */
		$payment         = new EDD_Payment( $this->payment_id );
		$commission_info = apply_filters( 'edd_commission_info', array(
			'user_id'      => (int) $this->user_id,
			'amount'       => (float) $this->amount,
			'status'       => ! empty( $this->status ) ? $this->status : 'unpaid',
			'download_id'  => absint( $this->download_id ),
			'payment_id'   => absint( $this->payment_id ),
			'cart_index'   => absint( $this->cart_index ),
			'price_id'     => absint( $this->price_id ),
			'date_created' => ! empty( $legacy_args['post_date'] ) ? $legacy_args['post_date'] : $payment->date,
			'date_paid'    => ! empty( $this->date_paid ) ? $this->date_paid : '',
			'rate'         => (float) $this->rate,
			'type'         => ! empty( $this->type ) ? $this->type : eddc_get_commission_type( $this->download_id ),
			'currency'     => $this->currency,
		), $this->id, $this->payment_id, $this->download_id );

		if ( empty( $commission_info['date_created'] ) ) {
			$commission_info['date_created'] = current_time( 'mysql' );
		}

		if ( empty( $commission_info['date_paid'] ) && 'paid' === $commission_info['status'] ) {
			$commission_info['date_paid'] = current_time( 'mysql' );
		}

		$commission_id = edd_commissions()->commissions_db->insert( $commission_info, 'commission' );

		if ( ! empty( $commission_id ) ) {
			$this->id  = $commission_id;
		}

		return $this->id;

	}


	/**
	 * Once object variables has been set, an update is needed to persist them to the database.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool True if the save was successful, false if it failed or wasn't needed.
	 */
	public function save() {
		$saved = false;

		if ( empty( $this->id ) ) {
			$commission_id = $this->add();

			if ( false === $commission_id ) {
				$saved = false;
			} else {
				$this->id = $commission_id;
			}
		}

		/**
		 * Save all the object variables that have been updated to the database.
		 */

		$base_columns = edd_commissions()->commissions_db->get_column_labels();
		$base_values  = array();

		if ( ! empty( $this->pending ) ) {

			foreach ( $this->pending as $key => $value ) {

				// If the property being updated is a core column, collect those in order to make one update call.
				if ( in_array( $key, $base_columns ) ) {

					$base_values[ $key ] = $value;
					do_action( 'eddc_pre_set_commission_' . $key, $this->id, $value, $this->$key );

				} else {
					$this->update_meta( $key, $value );
				}

			}

			// If there were updates to core columns, update those in a single update statement
			if ( ! empty( $base_values ) ) {

				$updated = edd_commissions()->commissions_db->update( $this->id, $base_values );

				if ( $updated ) {
					$updated_columns = array_keys( $base_values );
					foreach ( $updated_columns as $column ) {

						// Run an action for each of the updated columns.
						do_action( 'eddc_set_commission_' . $column, $this->id, $this->pending[ $column ], $this->$column );

					}
				}

			}

			$saved = true;
		}

		if ( true == $saved ) {
			$this->setup_commission( edd_commissions()->commissions_db->get_by( 'id', $this->id ) );

			/**
			 * Fires after each meta update allowing developers to hook their own items saved in $pending.
			 *
			 * @since       3.3
			 * @param       object EDD_Commission Instance of EDD_Commission object.
			 * @param       string $key Meta key.
			 */
			do_action( 'eddc_commission_save', $this->id, $this );

			$this->pending = array();
		}

		/**
		 * Update the commission in the object cache.
		 */
		$cache_key = md5( 'eddc_commission' . $this->id );
		wp_cache_set( $cache_key, $this, 'commissions' );

		return $saved;
	}

	/**
	 * Delete this commission record
	 *
	 * @since 3.4
	 * @return bool
	 */
	public function delete() {
		$deleted = edd_commissions()->commissions_db->delete( $this->id );

		if ( $deleted ) {
			$cache_key = md5( 'eddc_commission' . $this->id );
			wp_cache_delete( $cache_key );
		}


		return $deleted;
	}


	/**
	 * Helper method to update meta data associated with the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $key Meta key.
	 * @param       string $value Meta value.
	 * @param       string $prev_value Previous meta value.
	 * @return      int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key = '', $value = '', $prev_value = '' ) {

		if ( empty( $key ) || '' == $key ) {
			return false;
		}

		$key   = sanitize_key( $key );
		$value = apply_filters( 'eddc_update_commission_meta_' . $key, $value, $this->id );

		// Backwards compatibility check in case someone tries to update one of the items in the old commission info meta key.
		$commission_columns = edd_commissions()->commissions_db->get_column_labels();
		if ( in_array( $key, $commission_columns ) ) {
			switch( $key ) {

				case 'rate':
				case 'amount':
					$value = (float) $value;
					break;

				case 'user_id':
					$value = absint( $value );
					break;

				default:
					$value = sanitize_text_field( $value );
					break;

			}

			return edd_commissions()->commissions_db->update( array( $key => $value ) );
		}

		if ( '_' !== mb_substr( $key, 0, 1, 'utf-8') ) {
			$key = '_edd_commission_' . $key;
		}

		$updated = edd_commissions()->commission_meta_db->update_meta( $this->id, $key, $value, $prev_value );

		if ( true == $updated ) {
			/**
			 * Update the commission in the object cache.
			 */
			$cache_key = md5( 'eddc_commission' . $this->id );
			wp_cache_set( $cache_key, $this, 'commissions' );
		}

		return $updated;
	}


	/**
	 * Update the status of the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $new_status New status
	 * @return      bool
	 */
	public function update_status( $new_status = '' ) {
		if ( empty( $new_status ) ) {
			return false;
		}

		$this->pending['status'] = $new_status;
		$this->save();

		if ( $this->status !== $new_status ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize the key so it's lower-case
	 *
	 * @since 3.4
	 * @param $key
	 *
	 * @return string
	 */
	private function sanitize_key( $key ) {
		return strtolower( $key );
	}

}
