<?php
/**
 * FES Vendors
 *
 * This file contains vendor helper functions,
 * not to be confused with the FES Vendor objects
 * created in class-vendor.php.
 *
 * @package FES
 * @subpackage Vendors
 * @since 2.3.0
 *
 * @todo Simply and deprecate some of these functions that are
 *       no longer used.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Vendors class.
 *
 * Contains vendor helper functions.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Vendors {

	/**
	 * FES Vendors construct.
	 *
	 * Sets up all of the action and filters needed
	 * for the functions in this class.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ), 1000 );
		add_filter( 'show_admin_bar' , array( $this, 'hide_admin_bar' ), 99999 );
		add_filter( 'edd_user_can_view_receipt', array( $this, 'vendor_can_view_receipt' ), 10, 2 );
		add_action( 'edd_complete_purchase',  array( $this, 'increment_vendor_earnings' ), 10, 1 );
		add_action( 'edd_payment_delete',  array( $this, 'decrement_vendor_earnings' ), 10, 1 );
		add_action( 'post_updated', array( $this, 'change_author_check' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'post_status_transition' ), 10, 3 );
		add_filter( 'map_meta_cap',array( $this, 'meta_caps' ), 10, 4 );
	}

	/**
	 * Change author check.
	 *
	 * When the author of a download is changed, this function
	 * removes the sales count/earnings from the old vendor and
	 * adds it to the new author if applicable, as well as
	 * increments/decrements the vendor's product count.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int     $post_id     The post ID of the download.
	 * @param WP_Post $post_after  The post object of the download before the change.
	 * @param WP_Post $post_before The post object of the download after the change.
	 * @return void
	 */
	public function change_author_check( $post_id, $post_after, $post_before ) {
		if ( $post_after->post_type === 'download' && $post_after->post_status === 'publish' && $post_before->post_status === 'publish' ) {
			if ( $post_after->post_author !== $post_before->post_author ) {
				$download     = new EDD_Download( $post_id );
				$sales_value  = $download->earnings;
				$sales_count  = $download->sales;
				if ( EDD_FES()->vendors->user_is_vendor( $post_before->post_author ) ) {
					$vendor = new FES_Vendor( $post_before->post_author, true );
					// decrement sales
					$vendor->decrease_value( $sales_value );
					// decrement earnings
					$vendor->decrease_sales_count( $sales_count );
					// decrement products
					$vendor->decrease_product_count( 1 );
				}

				if ( EDD_FES()->vendors->user_is_vendor( $post_after->post_author ) ) {
					$vendor = new FES_Vendor( $post_after->post_author, true );
					// increment sales
					$vendor->increase_value( $sales_value );
					// increment earnings
					$vendor->increase_sales_count( $sales_count );
					// increment products
					$vendor->increase_product_count( 1 );
				}
			}
		}
	}

	/**
	 * Change status check.
	 *
	 * When the status of a download is changed, this function
	 * removes/adds the sales count/earnings from download to the
	 * author if applicable, as well as increments/decrements
	 * the vendor's product count.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string  $new_status The new post status.
	 * @param string  $old_status The old post status.
	 * @param WP_Post $post       The post object of the download.
	 * @return void
	 */
	public function post_status_transition( $new_status, $old_status, $post ) {

		// Not an object if its not a draft yet. So prior to autosave this might throw warnings
		// We can prevent this by returning till it's been autosaved. This is when it becomes an obj.
		if ( ! is_object( $post ) ) {
			return;
		}

		if ( 'download' !== $post->post_type ) {
			return;
		}

		$download     = new EDD_Download( $post->ID );
		$sales_value  = $download->earnings;
		$sales_count  = $download->sales;

		if ( EDD_FES()->vendors->user_is_vendor( $post->post_author ) ) {

			if ( 'publish' !== $new_status && 'publish' === $old_status ) {
				$vendor = new FES_Vendor( $post->post_author, true );
				// decrement sales
				$vendor->decrease_value( $sales_value );
				// decrement earnings
				$vendor->decrease_sales_count( $sales_count );
				// decrement products
				$vendor->decrease_product_count( 1 );
				return;
			}

			if ( 'publish' === $new_status && 'publish' !== $old_status ) {
				$vendor = new FES_Vendor( $post->post_author, true );
				// increment sales
				$vendor->increase_value( $sales_value );
				// increment earnings
				$vendor->increase_sales_count( $sales_count );
				// increment products
				$vendor->increase_product_count( 1 );
				return;
			}
		}
	}

	/**
	 * Increment Vendor Earnings.
	 *
	 * When an order is set to complete, this
	 * function is called which increments the vendor's
	 * sales and earnings counts.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int $payment_id The payment id.
	 * @return void
	 */
	public function increment_vendor_earnings( $payment_id ) {

		$cart_details = edd_get_payment_meta_cart_details( $payment_id );

		if ( is_array( $cart_details ) ) {
			foreach ( $cart_details as $cart_index => $download ) {
				// Increase the earnings for this download ID
				$post = get_post( $download['id'] );
				$author = $post->post_author;
				if ( EDD_FES()->vendors->user_is_vendor( $author ) ) {
					$vendor = new FES_Vendor( $author, true );
					$vendor->increase_value( $download['price'] * $download['quantity'] );
					$vendor->increase_sales_count( $download['quantity'] );
				}
			}
		}
	}

	/**
	 * Increment Vendor Earnings.
	 *
	 * When an order is set to refunded/revoked, this
	 * function is called which decrements the vendor's
	 * sales and earnings counts.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int $payment_id The payment id.
	 * @return void
	 */
	public function decrement_vendor_earnings( $payment_id ) {

		$amount      = edd_get_payment_amount( $payment_id );
		$post        = get_post( $payment_id );
		$status      = edd_get_payment_status( $post->ID );
		$customer_id = edd_get_payment_customer_id( $payment_id );
		if ( $status == 'revoked' || $status == 'publish' ) {
			$cart_details   = edd_get_payment_meta_cart_details( $payment_id );
			foreach ( $cart_details as $cart_index => $download ) {
				// Increase the earnings for this download ID
				$post = get_post( $download['id'] );
				$author = $post->post_author;
				if ( EDD_FES()->vendors->user_is_vendor( $author ) ) {
					$vendor = new FES_Vendor( $author, true );
					$vendor->decrease_value( $download['price'] * $download['quantity'] );
					$vendor->decrease_sales_count( $download['quantity'] );
				}
			}
		}
	}

	/**
	 * Hide Admin Bar.
	 *
	 * If the hide admin bar setting is on in FES,
	 * this function hides the admin bar on the
	 * frontend for non-admins.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param bool $bool Whether to hide the admin bar.
	 * @return bool Whether to hide the admin bar.
	 */
	public function hide_admin_bar( $bool ) {

		if ( ! fes_is_frontend() ) {
			return $bool;
		}

		// This setting is reversed. ! removed means it is removed. Stupid.
		if ( ! EDD_FES()->helper->get_option( 'fes-remove-admin-bar', false ) ) {
			if ( EDD_FES()->vendors->user_is_status( 'approved' ) && ! (bool) EDD_FES()->vendors->user_is_admin() ) {
				$bool = false;
			}
		}

		if ( $bool && ( EDD_FES()->vendors->user_is_status( 'pending' ) || EDD_FES()->vendors->user_is_status( 'suspended' ) ) && ! (bool) EDD_FES()->vendors->user_is_admin() ) {
			// Never show admin bar to pending or suspended vendors
			$bool = false;
		}

		return $bool;
	}

	/**
	 * Vendor Store URL for Dashboard.
	 *
	 * This function is used on the vendor dashboard to
	 * show the vendor store url.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param WP_User $user The vendor's WP_User object.
	 * @return string The URL for the dashboard.
	 */
	public function get_vendor_store_url_dashboard( $user = false ) {
		if ( is_numeric( $user ) ) {
			$user = new WP_User( $user );
		}
		if ( ! $user || ! is_object( $user ) ) {
			$user = new WP_User( get_current_user_id() );
		}
		$vendor_url = EDD_FES()->vendors->get_vendor_store_url( $user );
		return sprintf( __( ' Your store url is: %s', 'edd_fes' ), '<a href="' . esc_url( $vendor_url ) . '">' . $vendor_url . '</a>' );
	}

	/**
	 * Vendor Store URL.
	 *
	 * This function is used to get the vendor store url.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param WP_User $user The vendor's WP_User object.
	 * @return string The URL.
	 */
	public function get_vendor_store_url( $user = false ) {

		if ( ! is_object( $user ) ) {
			if ( $user === false ) {
				$user = get_current_user_id();
			}
			$user = new WP_User( $user );
		}

		if ( ! $user || ! is_object( $user ) ) {
			$user = new WP_User( get_current_user_id() );
		}

		$name       = get_userdata( $user->ID );
		$nicename   = apply_filters( 'fes_user_nicename_to_lower' , strtolower( $name->user_nicename ), $user );
		$vendor_url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-page', '' ) );
		$permalink  = apply_filters( 'fes_adjust_vendor_url', untrailingslashit( 'vendor/' ) );
		$vendor_url = str_replace( 'fes-vendor/', $permalink, $vendor_url );
		$vendor_url = str_replace( 'vendor/', $permalink, $vendor_url );

		if ( get_option( 'permalink_structure' ) ) {
			$vendor_url = trailingslashit( $vendor_url ) . $nicename;
		} else {
			$vendor_url = add_query_arg( 'vendor', $nicename, $vendor_url );
		}

		return $vendor_url;
	}

	/**
	 * Prevent Admin Access.
	 *
	 * If the FES setting is on for denying backend access,
	 * this function prevents non-admins from being able to access
	 * the wp-admin area, and redirects them to the vendor
	 * dashboard.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function prevent_admin_access() {

		$allow = apply_filters( 'fes_prevent_admin_access', true );

		if ( ! $allow ) {
			return;
		}

		if (
			// Look for the presence of /wp-admin/ in the url
			stripos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) !== false &&
			// Allow calls to async-upload.php
			stripos( $_SERVER['REQUEST_URI'], 'async-upload.php' ) == false &&
			// Allow calls to media-upload.php
			stripos( $_SERVER['REQUEST_URI'], 'media-upload.php' ) == false &&
			// Allow calls to admin-ajax.php
			stripos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) == false ) {
			if ( EDD_FES()->vendors->user_is_vendor() && ! EDD_FES()->vendors->user_is_admin() ) {
				wp_redirect( get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) ) );
				exit();
			}
		}
	}

	/**
	 * Vendor exists.
	 *
	 * Attempts to see if a vendor exists by vendor id,
	 * user id, username, or email.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int|string $user       The id, username or email.
	 * @param bool       $by_user_id If user is an id, is it a user id.
	 * @return bool Whether or not the vendor exists.
	 */
	public function vendor_exists( $user = false, $by_user_id = true ) {

		if ( $user === -2 ) {
			$user = get_current_user_id();
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} elseif ( is_email( $user ) ) {
				$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		$output = true;
		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			$output = false;
		}

		return apply_filters( 'fes_vendors_vendor_exists', $output, $vendor, $user, $by_user_id );
	}

	/**
	 * Retrieve vendors from the database
	 *
	 * Get vendors by user id, vendor id, email, status,
	 * date range, or username.
	 *
	 * @access  public
	 * @since   2.4.7
	 *
	 * @param  array $args Arguments for search.
	 * @return array Vendors found.
	 */
	public function get_vendors( $args ) {
		$defaults = array(
			'status' => 'approved',
			'number' => 10,
			'offset' => 0,
		);
		$args = wp_parse_args( $args, $defaults );
		$vendor_db = new FES_DB_Vendors();
		return $vendor_db->get_vendors( $args );
	}

	/**
	 * User is vendor.
	 *
	 * Attempts to see if a vendor exists by
	 * user id, username, or email.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int|string $user The id, username or email.
	 * @return bool Whether or not the user is a vendor.
	 */
	public function user_is_vendor( $user = -2 ) {

		if ( $user === -2 ) {
			$user = get_current_user_id();
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = 'user_id';
		} elseif ( is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			$output = false;
			if ( $vendor->status === 'approved' ) {
				$output = true;
			}
			return $output;
		}
	}

	// warning: to use this function as named, the second param must be false ( backwards compat reasons for this )
	/**
	 * Vendor is vendor.
	 *
	 * Attempts to see if a vendor exists by vendor id,
	 * user id, username, or email.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  Can these params be flipped somehow?
	 *
	 * @param int|string $user       The id, username or email.
	 * @param bool       $by_user_id If user is an id, is it a user id.
	 * @return bool Whether or not the vendor is a vendor.
	 */
	public function vendor_is_vendor( $user = -2, $by_user_id = true ) {

		if ( $user === -2 ) {
			if ( $by_user_id ) {
				$user = get_current_user_id();
			} else {
				$user = EDD_FES()->vendors->vendor_from_user( get_current_user_id() );
			}
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} elseif ( is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			$output = false;
			if ( $vendor->status === 'approved' ) {
				$output = true;
			}
			return $output;
		}
	}

	/**
	 * User From Vendor.
	 *
	 * Based on the username, vendor id, or
	 * vendor email, attempts to get the user_id
	 * of a vendor.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int|string $user The id, username or email.
	 * @return int User id of vendor.
	 */
	public function user_from_vendor( $user = false ) {

		if ( $user === -2 ) {
			$user = get_current_user_id();
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = 'id';
		} elseif ( is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			return $vendor->user_id;
		}

	}

	/**
	 * Vendor from User.
	 *
	 * Based on the username, user id, or
	 * user email, attempts to get the vendor id
	 * of a vendor.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int|string $user The id, username or email.
	 * @return int User id of vendor.
	 */
	public function vendor_from_user( $user = false ) {

		if ( $user === -2 ) {
			$user = EDD_FES()->vendors->vendor_from_user( get_current_user_id() );
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = 'user_id';
		} elseif ( is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			return $vendor->id;
		}
	}

	/**
	 * User is Status.
	 *
	 * Based on the username, user id, or
	 * user email, attempts to see if the user
	 * is a vendor, and if so, that they are
	 * of the status passed in.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string     $status Status to compare against.
	 * @param int|string $user   The id, username or email.
	 * @return bool Whether user is a vendor of specific status.
	 */
	public function user_is_status( $status = '', $user = -2 ) {

		if ( $user === -2 ) {
			$user = get_current_user_id();
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = 'user_id';
		} elseif ( is_string( $user ) && is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			$output = false;
			if ( $vendor->status === $status ) {
				$output = true;
			}
			return $output;
		}
	}

	/**
	 * Vendor is Status.
	 *
	 * Based on the username, vendor id, or
	 * vendor email, attempts to see if the vendor
	 * is a vendor, and if so, that they are
	 * of the status passed in.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string     $status Status to compare against.
	 * @param int|string $user   The id, username or email.
	 * @return bool Whether vendor is a vendor of specific status.
	 */
	public function vendor_is_status( $status = '', $user = -2 ) {

		if ( $user === -2 ) {
			$user = EDD_FES()->vendors->vendor_from_user( get_current_user_id() );
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		if ( is_numeric( $user ) ) {
			$field = 'id';
		} elseif ( is_string( $user ) && is_email( $user ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$db     = new FES_DB_Vendors;
		$vendor = $db->get_vendor_by( $field, $user );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			$output = false;
			if ( $vendor->status === $status ) {
				$output = true;
			}
			return $output;
		}
	}

	/**
	 * User is Admin.
	 *
	 * Based on the username, user id, or
	 * user email, attempts to see if the user
	 * is a an admin.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int|string $vendor The id, username or email.
	 * @return bool Whether user is an admin.
	 */
	public function user_is_admin( $vendor = -2 ) {

		if ( $vendor === -2 ) {
			$vendor = get_current_user_id();
		}

		if ( $vendor == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		$user = false;

		if ( is_numeric( $vendor ) ) {
			$user = new WP_User( $vendor );
		} elseif ( is_string( $vendor ) && is_email( $vendor ) ) {
			$user = get_user_by( 'email', $vendor );
		} else {
			$user = get_user_by( 'login', $vendor );
		}

		if ( ! $user || ! is_object( $user ) ) {
			return false; // user doesn't exist
		}

		$bool = false;

		/**
		 * FES Skip is Admin
		 *
		 * This allows devs to take what would normally
		 * be a vendor and say they aren't a vendor.
		 *
		 * @since 2.2.0
		 * @param bool    $bool Whether user is admin.
		 * @param WP_User $user User object of current user.
		 */
		$bool = apply_filters( 'fes_skip_is_admin', $bool, $user );

		// Note to developers: I passed in the entire user object above.
		// So expect either an object (logged in user) or false (not logged in user).
		if ( $bool ) {
			return false;
		}

		// Authentication Attempt #1: okay let's try caps
		$vendor_caps = array( 'fes_is_admin', 'manage_options', 'manage_shop_settings', 'shop_worker', 'shop_accountant', 'shop_manager' );
		/**
		 * FES Is Admin Capabilities
		 *
		 * Determines which WordPress capabilities should
		 * dictate if the user is an admin or not.
		 *
		 * @since 2.4.0
		 * @param array   $vendor_caps Array of admin capabilities.
		 */
		$vendor_caps = apply_filters( 'fes_is_admin_capabilities' , $vendor_caps );
		if ( is_array( $vendor_caps ) && ! empty( $vendor_caps ) ) {
			foreach ( $vendor_caps as $vcap ) {
				if ( user_can( $user->ID, $vcap ) ) {
					return true;
				}
			}
		}

		// Authentication Attempt #2:  maybe a developer has a reason for wanting to hook a user in?
		$bool = false;

		/**
		 * FES Is Admin Check Override
		 *
		 * Let's a developer say a non-admin is an admin.
		 *
		 * @since 2.2.0
		 * @param bool    $bool Whether user is admin.
		 * @param WP_User $user User object of current user.
		 */
		$bool = apply_filters( 'fes_is_admin_check_override', $bool, $user );

		// Note to developers: I passed in the entire user object above.
		// So expect either an object (logged in user) or false (not logged in user).
		if ( $bool ) {
			return true;
		}

		// end of the line
		return false;
	}

	/**
	 * Vendor is Admin.
	 *
	 * Gets the user object from a vendor id, email,
	 * or username, and then checks to see if the vendor
	 * is an admin user.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @todo  The === -2 part could be optimized.
	 *
	 * @uses FES_Vendor::user_from_vendor() Get user from vendor.
	 * @uses FES_Vendor::user_is_admin() See if user is admin.
	 *
	 * @param int|string $vendor The id, username or email.
	 * @return bool Whether user is an admin.
	 */
	public function vendor_is_admin( $user = -2 ) {

		if ( $user === -2 ) {
			$user = EDD_FES()->vendors->vendor_from_user( get_current_user_id() );
		}

		if ( $user == 0 ) {
			// This is a logged out user, since get_current_user_id returns 0 for non logged in
			// since we can't do anything with them, lets get them out of here. They aren't vendors.
			return false;
		}

		$user = EDD_FES()->vendors->user_from_vendor( $user );
		return EDD_FES()->vendors->user_is_admin( $user );
	}

	/**
	 * Get User ID.
	 *
	 * Gets the user id from email or username.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string $email_or_username The username or email.
	 * @return int The user id.
	 */
	public function get_user_id( $email_or_username = false ) {

		if ( $email_or_username === false ) {
			return false;
		}

		if ( is_string( $email_or_username ) && is_email( $email_or_username ) ) {
			$user = get_user_by( 'email', $email_or_username );
			if ( ! $user || ! is_object( $user ) ) {
				return false;
			} else {
				return $user->ID;
			}
		} else {
			$user = get_user_by( 'login', $email_or_username );
			if ( ! $user || ! is_object( $user ) ) {
				return false;
			} else {
				return $user->ID;
			}
		}
	}

	/**
	 * Get Vendor ID.
	 *
	 * Gets the vendor id from email or username.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string $email_or_username The username or email.
	 * @return int The vendor id.
	 */
	public function get_vendor_id( $email_or_username = false ) {

		if ( $email_or_username === false ) {
			return false;
		}

		$user_id = false;

		if ( is_string( $email_or_username ) && is_email( $email_or_username ) ) {
			$user = get_user_by( 'email', $email_or_username );
			if ( ! $user || ! is_object( $user ) ) {
				return false;
			} else {
				$user_id = $user->ID;
			}
		} else {
			$user = get_user_by( 'login', $email_or_username );
			if ( ! $user || ! is_object( $user ) ) {
				return false;
			} else {
				$user_id = $user->ID;
			}
		}

		return EDD_FES()->vendors->vendor_from_user( $user_id );
	}

	/**
	 * Vendor Can View Receipt.
	 *
	 * Determines if a vendor can view the receipt.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param bool $user_can_view  By default can this user
	 *                         see the receipt.
	 * @param int  $edd_receipt_id The payment id.
	 * @return bool Whether the vendor can view the receipt.
	 */
	public function vendor_can_view_receipt( $user_can_view, $edd_receipt_id ) {
		if ( ! $edd_receipt_id ) {
			return false;
		}

		$payment_id = ! empty( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : false;

		if ( $payment_id ) {

			$cart = edd_get_payment_meta_cart_details( $payment_id );

			foreach ( $cart as $item ) {
				$item = get_post( $item['id'] );

				if ( $item->post_author == get_current_user_id() ) {
					$user_can_view = true;

					break;
				}
			}
		}

		return $user_can_view;
	}

	/**
	 * Vendor Can View Receipt Item.
	 *
	 * Determines if a vendor can view the receipt item.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param bool $user_can_view By default can this user
	 *                         see the receipt.
	 * @param int  $item          The item id in question.
	 * @return bool Whether the vendor can view the receipt.
	 */
	public function vendor_can_view_receipt_item( $user_can_view, $item ) {

		if ( is_user_logged_in() && ! EDD_FES()->vendors->user_is_admin() && EDD_FES()->vendors->user_is_vendor( get_current_user_id() ) ) {

			$user_can_view = false;

			$download = get_post( $item['id'] );
			if ( (int) $download->post_author == (int) get_current_user_id() ) {
				$user_can_view = true;
			}
		}

		return $user_can_view;
	}

	/**
	 * Vendor is Author.
	 *
	 * Is the user the author of the download.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int $post_id The id of the download.
	 * @param int $user_id The id of the user.
	 * @return bool Whether user is the author of the download.
	 */
	public function vendor_is_author( $post_id = false, $user_id = false ) {

		$ret = false;

		if ( ! empty( $post_id ) && ! empty( $user_id ) ) {

			$download = get_post( $post_id );
			if ( (int) $download->post_author === (int) $user_id ) {
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Vendor Can Create Product.
	 *
	 * Determines if user is a vendor, and if so
	 * based on the FES settings, can they create a new
	 * product.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $user_id The current user id.
	 * @return bool Whether the vendor can create a product.
	 */
	public function vendor_can_create_product( $user_id = -2 ) {

		$ret = false;

		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}

		if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-create-products', false ) ) {

			if ( EDD_FES()->vendors->user_is_vendor( $user_id ) ) {
				$ret = true;
			}
		}

		return $ret;

	}

	/**
	 * Vendor Can Edit Product.
	 *
	 * Determines if user is a vendor, and if so
	 * based on the FES settings, can they edit a
	 * product.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $post_id The download id.
	 * @return bool Whether the vendor can edit a product.
	 */
	public function vendor_can_edit_product( $post_id ) {

		$ret = false;

		if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-edit-products', false ) ) {

			$user_id = get_current_user_id();
			$post = get_post( $post_id, ARRAY_A );

			if ( EDD_FES()->vendors->user_is_vendor( $user_id ) && ( EDD_FES()->vendors->user_is_admin( $user_id ) || $post['post_author'] == $user_id ) ) {
				$ret = true;
			}
		}

		return $ret;

	}

	/**
	 * Vendor Can Delete Product.
	 *
	 * Determines if user is a vendor, and if so
	 * based on the FES settings, can they delete a
	 * product.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $post_id The download id.
	 * @return bool Whether the vendor can delete a product.
	 */
	public function vendor_can_delete_product( $post_id ) {

		$ret = false;

		if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-delete-products', false ) ) {

			$user_id = get_current_user_id();
			$post    = get_post( $post_id, ARRAY_A );

			if ( EDD_FES()->vendors->user_is_vendor( $user_id ) && ( EDD_FES()->vendors->user_is_admin( $user_id ) || $post['post_author'] == $user_id ) ) {
				$ret = true;
			}
		}

		return $ret;

	}

	/**
	 * Vendor Can View Order.
	 *
	 * Determines if user is a vendor, and if so
	 * based on the FES settings, can they view a
	 * order.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $post_id The order id.
	 * @return bool Whether the vendor can see the order.
	 */
	public function vendor_can_view_order( $post_id ) {

		$ret = false;

		if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-view-orders', false ) ) {

			$user_id = get_current_user_id();

			if ( EDD_FES()->vendors->user_is_vendor( $user_id ) || EDD_FES()->vendors->user_is_admin( $user_id ) ) {
				$ret = edd_FES()->vendors->vendor_can_view_receipt( false, $post_id );
			}
		}

		return $ret;

	}

	/**
	 * Vendor Can View Orders.
	 *
	 * Determines if user is a vendor, and if so
	 * based on the FES settings, can they view a
	 * orders in general (not a specific order).
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return bool Whether the vendor can see orders.
	 */
	public function vendor_can_view_orders() {

		$ret = false;

		if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-view-orders', false ) ) {

			$user_id = get_current_user_id();

			if ( EDD_FES()->vendors->user_is_vendor( $user_id ) || EDD_FES()->vendors->user_is_admin( $user_id ) ) {
				$ret = true;
			}
		}

		return $ret;

	}

	/**
	 * Get Pending Products.
	 *
	 * Return an array of all pending products of
	 * a particular vendor.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $user_id The user id.
	 * @return array The pending products.
	 */
	public function get_pending_products( $user_id = false ) {
		global $wpdb, $current_user;

		if ( ! $user_id ) {
			$user_id = $user_id;
		}

		$vendor_products = get_posts( array(
			'nopaging' => true,
			'author' => $user_id,
			'orderby' => 'title',
			'post_type' => 'download',
			'post_status' => 'pending',
			'order' => 'ASC',
		) );

		if ( empty( $vendor_products ) ) {
			return false;
		}

		foreach ( $vendor_products as $product ) {
			$data[] = array(
				'ID'       => $product->ID,
				'date'     => $product->post_date,
				'title'    => $product->post_title,
				'url'      => esc_url( get_permalink( $product->ID ) ),
				'sales'    => edd_get_download_sales_stats( $product->ID ),
				'earnings' => edd_get_download_earnings_stats( $product->ID ),
			);
		}
		/**
		 * Get Pending Products
		 *
		 * Allows for additional data to be returned when
		 * requesting the pending products of a vendor.
		 *
		 * @since 2.2.0
		 *
		 * @param array   $data    The pending products array.
		 * @param int     $user_id The user id.
		 */
		return apply_filters( 'fes_get_pending_products', $data, $user_id );
	}

	/**
	 * Get Published Products.
	 *
	 * Return an array of all published products of
	 * a particular vendor.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int $user_id The user id.
	 * @return array The published products.
	 */
	public function get_published_products( $user_id = false ) {
		global $wpdb, $current_user;
		if ( ! $user_id ) {
			$user_id = $current_user->ID;
		}

		$vendor_products = get_posts( array(
			'nopaging' => true,
			'author' => $user_id,
			'orderby' => 'title',
			'post_type' => 'download',
			'post_status' => array('publish', 'draft', 'private', 'archive'),  //anagram geet show more status 'publish',
			'order' => 'ASC',
		) );

		if ( empty( $vendor_products ) ) {
			return false;
		}

		foreach ( $vendor_products as $product ) {
			$data[] = array(
				'ID'       => $product->ID,
				'date'     => $product->post_date,
				'title'    => $product->post_title,
				'url'      => esc_url( get_permalink( $product->ID ) ),
				'sales'    => edd_get_download_sales_stats( $product->ID ),
				'earnings' => edd_get_download_earnings_stats( $product->ID ),
			);
		}

		/**
		 * Get Published Products
		 *
		 * Allows for additional data to be returned when
		 * requesting the published products of a vendor.
		 *
		 * @since 2.2.0
		 *
		 * @param array   $data    The published products array.
		 * @param int     $user_id The user id.
		 */
		return apply_filters( 'fes_get_published_products', $data, $user_id );
	}

	/**
	 * Retrieve an array of all products of a particular vendor.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int   $user_id The user id.
	 * @param array $status  Array of statuses to get downloads of.
	 * @return array|bool Products or false if vendor has no products.
	 */
	public function get_all_products( $user_id = false, $status = array( 'draft', 'pending', 'publish', 'trash', 'future', 'private' ) ) {
		global $wpdb, $current_user;

		if ( ! $user_id ) {
			$user_id = $current_user->ID;
		}

		$vendor_products = get_posts( array(
			'nopaging'    => true,
			'author'      => $user_id,
			'orderby'     => 'title',
			'post_type'   => 'download',
			'post_status' => $status,
			'order'       => 'ASC',
		) );

		if ( empty( $vendor_products ) ) {
			return false;
		}

		foreach ( $vendor_products as $product ) {
			$data[] = array(
				'ID'       => $product->ID,
				'title'    => $product->post_title,
				'status'   => $product->post_status,
				'url'      => esc_url( admin_url( 'post.php?post=' . $product->ID . '&action=edit' ) ),
				'sales'    => edd_get_download_sales_stats( $product->ID ),
				'earnings' => edd_get_download_earnings_stats( $product->ID ),
			);
		}

		$data = $this->array_msort( $data, array( 'status' => SORT_ASC, 'title' => SORT_ASC, 'sales' => SORT_DESC, 'ID' => SORT_ASC, 'url' => SORT_ASC ) );

		/**
		 * Get All Products
		 *
		 * Allows for additional data to be returned when
		 * requesting the products of a vendor.
		 *
		 * @since 2.2.0
		 *
		 * @param array   $data    The products array.
		 * @param int     $user_id The user id.
		 */
		return apply_filters( 'fes_get_all_products', $data, $user_id );
	}

	/**
	 * Array Multiple Sort.
	 *
	 * Allows for a cross-PHP version
	 * multiple array sorting function.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @todo  Move to helper functions.
	 *
	 * @param array $array The array to sort.
	 * @param array $cols  The order of columns to sort.
	 * @return array The sorted array.
	 */
	public function array_msort( $array, $cols ) {
		$colarr = array();

		foreach ( $cols as $col => $order ) {
			$colarr[ $col ] = array();
			foreach ( $array as $k => $row ) {
				$colarr[ $col ][ '_' . $k ] = strtolower( $row[ $col ] );
			}
		}

		$params = array();

		foreach ( $cols as $col => $order ) {
			$params[] =& $colarr[ $col ];
			$cols[ $col ] = (array) $cols[ $col ];
			foreach ( $cols[ $col ] as $k => $ordval ) {
				$params[] =& $cols[ $col ][ $k ];
			}
		}

		call_user_func_array( 'array_multisort', $params );
		$ret = array();
		$keys = array();
		$first = true;

		foreach ( $colarr as $col => $arr ) {
			foreach ( $arr as $k => $v ) {
				if ( $first ) {
					$keys[ $k ] = substr( $k, 1 );
				}
				$k = $keys[ $k ];
				if ( ! isset( $ret[ $k ] ) ) {
					$ret[ $k ] = $array[ $k ];
				}
				$ret[ $k ][ $col ] = $array[ $k ][ $col ];
			}
			$first = false;
		}
		return $ret;
	}

	/**
	 * Get Products.
	 *
	 * Return an array of all products of
	 * a particular vendor with specific post
	 * status(es).
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int   $user_id     The user id.
	 * @param array $post_status The post status to get products of.
	 * @return array The products.
	 */
	public function get_products( $user_id, $post_status = false ) {
		global $wpdb;


		$products = array();

		$paged = 1;
		if ( ! empty( $_REQUEST['paged'] ) ) {
			$paged = absint( $_REQUEST['paged'] );
		} else if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		}

		$allowed_statuses = array( 'publish', 'draft', 'pending', 'future' );

		if ( empty( $post_status ) || ! in_array( $post_status, $allowed_statuses ) ) {
			$post_status = array( 'publish', 'draft', 'pending', 'future' );
		}

		$args = array(
			'author'         => $user_id,
			'post_type'      => 'download',
			'post_status'    => $post_status,
			'posts_per_page' => 10,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'paged'          => $paged,
		);

		/**
		 * Get Products Arguments
		 *
		 * Allows for additional filtering of the get
		 * product arguments.
		 *
		 * @since 2.2.0
		 *
		 * @param array   $args The arguments array.
		 */
		$args = apply_filters( 'fes_get_products_args', $args );

		$products = get_posts( $args );

		/**
		 * Get Products
		 *
		 * Allows for additional data to be returned when
		 * requesting the products of a vendor.
		 *
		 * @since 2.2.0
		 *
		 * @param array   $products The products array.
		 */
		return apply_filters( 'fes_get_products_data', $products );
	}

	/**
	 * Get Products Count.
	 *
	 * Return the count of all products of
	 * a particular vendor with specific post
	 * status(es).
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int   $user_id     The user id.
	 * @param array $post_status The post status to get products of.
	 * @return int The number of matching products.
	 */
	public function get_all_products_count( $user_id = false, $status = array( 'draft', 'pending', 'publish', 'trash', 'future', 'private' ) ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$args = array(
			'author'         => $user_id,
			'post_type'      => 'download',
			'post_status'    => $status,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$products = new WP_Query( $args );
		return $products->found_posts;
	}

	/**
	 * Get All Orders.
	 *
	 * Return all of the orders of
	 * a particular vendor with specific order
	 * status(es).
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int   $user_id The user id.
	 * @param array $args  Args to allow overriding of. Currently only number.
	 * @return array The array of matching orders.
	 */
	public function get_all_orders( $user_id = 0, $args = array() ) {

		$published_products = EDD_FES()->vendors->get_published_products( $user_id );

		if ( ! $published_products ) {
			return array();
		}

		$published_products = wp_list_pluck( $published_products, 'ID' );

		$number = ! empty( $args ['number'] ) ? $args ['number'] : 10;
		$args   = array(
			'download' => $published_products,
			'output'   => 'edd_payment',
			'mode'     => 'all',
			'number'   => $number,
			'orderby'  => 'post_date',
			'order'    => 'DESC',
			'paged'    => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
		);

		$payments = edd_get_payments( $args );

		if ( ! $payments ) {
			return array();
		}

		// nothing fancy with this for now
		return $payments;
	}

	/**
	 * Get All Orders Count.
	 *
	 * Return the number of orders of
	 * a particular vendor with specific order
	 * status(es).
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param int   $user_id The user id.
	 * @param array $status  The order status to get orders of.
	 * @return int The number of matching orders.
	 */
	public function get_all_orders_count( $user_id, $args = array() ) {
		$args['number'] = -1;
		return count( $this->get_all_orders( $user_id, $args ) );
	}

	/**
	 * Can See Login.
	 *
	 * Whether or not a user can see the login screen.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @deprecated 2.4.0
	 * @see is_user_logged_in()
	 *
	 * @return bool Whether a user can see the login form.
	 */
	public function can_see_login() {
		return ! is_user_logged_in();
	}

	/**
	 * Can See Registration.
	 *
	 * Whether or not a user can see the registration form.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @todo Put this in the registration form logic.
	 *
	 * @return bool Whether a user can see the registration form.
	 */
	public function can_see_registration() {
		if ( ! EDD_FES()->vendors->user_is_status( 'pending' ) && ! EDD_FES()->vendors->user_is_vendor() && ( EDD_FES()->helper->get_option( 'fes-allow-registrations', false ) || EDD_FES()->helper->get_option( 'fes-allow-applications', false ) ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Can Get Form Value.
	 *
	 * Unused function which has never been used.
	 * Do not use. It will be removed in 2.4.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @deprecated 2.4.0.
	 *
	 * @param int  $id       Unused.
	 * @param bool $is_admin Unused.
	 * @param int  $user_id  Unused.
	 * @param bool $public   Unused.
	 * @return bool Always returns true.
	 */
	public function can_get_form_values( $id = -2, $is_admin = -2, $user_id = -2, $public = -2 ) {
		return true;
	}

	/**
	 * Get Combo Form Count.
	 *
	 * Returns 1 if the user can see only
	 * the login OR registration form, 2 if
	 * they can see both, and 0 otherwise.
	 * Used for making the table HTML for the
	 * combo form.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @todo Move to combo form.
	 *
	 * @return int Number of the 2 forms a user can see.
	 */
	public function combo_form_count() {
		if ( EDD_FES()->vendors->can_see_registration() && EDD_FES()->vendors->can_see_login() ) {
			return 2;
		} elseif ( EDD_FES()->vendors->can_see_registration() || EDD_FES()->vendors->can_see_login() ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Get Avatar.
	 *
	 * The FES helper function to get_avatar.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @param string     $hook_name   Name for filters.
	 * @param string|int $id_or_email User id or email.
	 * @param int        $size        Size of avatar to return (max is 512), with default of 96.
	 * @param string     $default     Url for an image, defaults to the "Mystery Man".
	 * @param string     $alt         Alternate text for the avatar.
	 * @return int Number of the 2 forms a user can see.
	 */
	public function get_avatar( $hook_name = '', $id_or_email = 0, $size = 96, $default = '', $alt = false ) {

		if ( $id_or_email === 0 ) {
			$id_or_email = get_current_user_id();
		}

		/**
		 * Get Avatar ID or Email
		 *
		 * Allows for the filtering of id or email to use
		 * to get the avatar.
		 *
		 * @since 2.2.0
		 *
		 * @param string|int $id_or_email User id or email.
		 */
		$id_or_email = apply_filters( 'fes_get_avatar_id_or_email_' . $hook_name, $id_or_email );

		/**
		 * Get Avatar Size
		 *
		 * Allows for the filtering of size of the avatar
		 * to retrieve.
		 *
		 * @since 2.2.0
		 *
		 * @param int     $size Size of avatar to return (max is 512), with default of 96.
		 */
		$size = apply_filters( 'fes_get_avatar_size_' . $hook_name, $size );

		/**
		 * Get Avatar Default
		 *
		 * Allows for the filtering of default avatar
		 * image.
		 *
		 * @since 2.2.0
		 *
		 * @param string  $default Url for an image, defaults to the "Mystery Man".
		 */
		$default  = apply_filters( 'fes_get_avatar_id_or_email_' . $hook_name, $default );

		/**
		 * Get Avatar Alternate Text
		 *
		 * Allows for the filtering of alternate
		 * avatar text.
		 *
		 * @since 2.2.0
		 *
		 * @param string  $alt Alternate text for the avatar.
		 */
		$alt = apply_filters( 'fes_get_avatar_alt_' . $hook_name, $alt );

		$avatar = get_avatar( $id_or_email, $size, $default, $alt );

		/**
		 * Get Avatar (General)
		 *
		 * Allows for the filtering of the avatar
		 * retrieved.
		 *
		 * @since 2.2.0
		 *
		 * @param string  $avatar The avatar url retrieved.
		 */
		$avatar = apply_filters( 'fes_get_avatar', $avatar );

		/**
		 * Get Avatar (Specific)
		 *
		 * Allows for the filtering of the avatar
		 * retrieved.
		 *
		 * @since 2.2.0
		 *
		 * @param string  $avatar      The avatar url retrieved.
		 * @param string|int $id_or_email User id or email.
		 * @param int     $size        Size of avatar to return (max is 512), with default of 96.
		 * @param string  $default     Url for an image, defaults to the "Mystery Man".
		 * @param string  $alt         Alternate text for the avatar.
		 */
		return apply_filters( 'fes_get_avatar_' . $hook_name, $avatar, $id_or_email, $size, $default, $alt );
	}

	/**
	 * Can Save Formbuilder.
	 *
	 * Can the user edit/save the formbuilder.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @todo Move to formbuilder class.
	 *
	 * @param int $post_id Form being edited.
	 * @return bool Can user save the formbuilder.
	 */
	public function can_save_formbuilder( $post_id = -2 ) {
		if ( $post_id === -2 ) {
			$post_id = get_the_ID();
		}

		// Is the user allowed to see the formbuilder?
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Make User Vendor.
	 *
	 * Turns a user into a vendor if the user
	 * isn't already in the vendor table.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int $user_id User to make into vendor.
	 * @return void
	 */
	public function make_user_vendor( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$db_user = new FES_DB_Vendors();

		if ( $db_user->exists( 'id', $user_id ) ) {
			return;
		}

		// create user
		$user = new WP_User( $user_id );
		// note: Insert as pending then set to approved right underneath.
		$db_user->add( array(
				'user_id'        => $user->ID,
				'email'          => $user->user_email,
				'username'       => $user->user_login,
				'name'           => $user->display_name,
				'product_count'  => 0,
				'sales_count'    => 0,
				'sales_value'    => 0.00,
				'status'         => 'pending',
				'notes'          => '',
				'date_created'   => date( 'Y-m-d H:i:s' ),
		) );

		// set to approved
		$vendor  = new FES_Vendor( $user->ID, true );
		$vendor->change_status( 'approved', false );
	}

	/**
	 * Map FES Capabilities.
	 *
	 * Using meta caps, we're creating virtual capabilities that are replacements
	 * for the frontend_vendor, suspended_vendor, and pending_vendor roles.
	 *
	 * @access public
	 * @since 4.7.0
	 *
	 * @param array  $caps Array of capabilities the user has.
	 * @param string $cap The current cap being filtered.
	 * @param int    $user_id User to check permissions for.
	 * @param array  $args Extra parameters. Unused.
	 * @return array Array of caps needed to have this meta cap. If returned array is empty, user has the capability.
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! defined( 'fes_plugin_file' ) || is_plugin_inactive( plugin_basename( fes_plugin_file ) ) ) {
			return $caps;
		}

		switch ( $cap ) {
			case 'frontend_vendor' :
				if ( EDD_FES()->vendors->user_is_status( 'approved', $user_id ) ) {
					$caps = array();
				}
				break;
			case 'pending_vendor' :

				if ( EDD_FES()->vendors->user_is_status( 'pending', $user_id ) ) {
					$caps = array();
				}
				break;

			case 'suspended_vendor' :

				if ( EDD_FES()->vendors->user_is_status( 'suspended', $user_id ) ) {
					$caps = array();
				}
				break;

			case 'fes_is_admin':
				$vendor_caps = array( 'manage_options', 'manage_shop_settings', 'shop_manager' );
				foreach ( $vendor_caps as $vcap ) {
					if ( user_can( $user_id, $vcap ) ) {
						$caps = array();
						break;
					}
				}
				break;

			case 'read':
			case 'edit_posts':
			case 'upload_files':
			case 'edit_product':
			case 'read_product':
			case 'delete_product':
			case 'edit_products':
			case 'read_private_products':
			case 'edit_private_products':
			case 'manage_product_terms':
			case 'edit_product_terms':
			case 'assign_product_terms':
			case 'upload_files':
			case 'edit_posts':
				if ( EDD_FES()->vendors->user_is_status( 'approved', $user_id ) ) {
					$caps = array();
				}
				break;
		}// End switch().
		return $caps;
	}

	/**
	 * FES Deprecated Functions:
	 * All functions below this point deprecated in 2.3
	 * and slated for removal in 2.4
	 */

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public static function is_pending( $user_id = -2 ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->is_vendor', '2.3', 'EDD_FES()->vendors->user_is_status' );
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		return EDD_FES()->vendors->user_is_status( 'pending', $user_id );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public static function is_frontend( $user_id = -2 ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->is_vendor', '2.3', 'EDD_FES()->vendors->user_is_status' );
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		return EDD_FES()->vendors->user_is_status( 'approved', $user_id );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public static function is_suspended( $user_id = -2 ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->is_vendor', '2.3', 'EDD_FES()->vendors->user_is_status' );
		if ( $user_id == -2 ) {
			$user_id = get_current_user_id();
		}
		return EDD_FES()->vendors->user_is_status( 'suspended', $user_id );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public static function is_vendor( $user_id ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->is_vendor', '2.3', 'EDD_FES()->vendors->user_is_vendor' );
		$bool = user_can( 'frontend_vendor', $user_id ) ? true : false;
		return apply_filters( 'fes_is_vendor', $bool, $user_id );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public static function is_admin( $user_id ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->is_vendor', '2.3', 'EDD_FES()->vendors->user_is_admin' );
		$bool  = user_can( 'fes_is_admin', $user_id ) ? true : false;
		return apply_filters( 'fes_is_admin', $bool, $user_id );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function get_product_constant_name( $plural = false, $uppercase = true ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->get_product_constant_name()', '2.3', 'EDD_FES()->helper->get_product_constant_name()' );
		return EDD_FES()->helper->get_product_constant_name( $plural, $uppercase );
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function get_vendor_constant_name( $plural = false, $uppercase = true ) {
		_fes_deprecated_function( 'EDD_FES()->vendors->get_vendor_constant_name()', '2.3', 'EDD_FES()->helper->get_vendor_constant_name()' );
		return EDD_FES()->helper->get_vendor_constant_name( $plural, $uppercase );
	}
}
