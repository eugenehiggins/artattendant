<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Customers extends HSD_Controller {
	const SYNC_STATUS = 'hsd_sync_in_progress_v10';

	public static function init() {

		// Register Settings
		self::register_settings();

		add_action( 'wp_ajax_hsd_sync_users', array( __CLASS__, 'maybe_init_sync' ) );

		add_action( 'profile_update', array( __CLASS__, 'sync_user_record' ) );
		add_action( 'user_register', array( __CLASS__, 'sync_user_record' ) );
	}

	////////////////////
	// Help Scout API //
	////////////////////

	/**
	 * If the user already has a help scout record that update, otherwise create a new one.
	 * @param  array  $data array
	 * @return bool
	 */
	public static function maybe_update_contact_at_hs( $data = array() ) {
		if ( ! isset( $data['emails'] ) || empty( $data['emails'] ) ) {
			return false;
		}

		$user_id = $data['WPID'];
		unset( $data['WPID'] );

		$customer_ids = array_filter( HelpScout_API::find_customer_ids( $user_id ) );

		if ( empty( $customer_ids ) ) {
			$customer_ids = self::create_contact( $user_id, $data );
			return $customer_ids;
		}
		// Update all records
		foreach ( $customer_ids as $customer_id ) {
			 // TODO, emails and websites aren't updated because it requires an id for each value
			unset( $data['emails'] );
			unset( $data['websites'] );
			$response = HelpScout_API::api_post( 'customers/' . $customer_id, wp_json_encode( $data ), 'PUT' );
		}
		return $customer_ids;
	}

	/**
	 * http://developer.helpscout.net/help-desk-api/customers/create/
	 * @param  array  $data array
	 * @return array
	 */
	public static function create_contact( $user_id, $customer_object = array() ) {
		$response = HelpScout_API::api_post( 'customers', wp_json_encode( $customer_object ) );
		$customer = json_decode( $response );
		// Error response
		if ( isset( $customer->code ) ) {
			$error = $customer;
			if ( 409 === (int) $error->code ) {
				if ( isset( $error->validationErrors[0]->value ) ) {
					$email = $error->validationErrors[0]->value;
					$customer_ids = HelpScout_API::find_customer_ids_by_email( $email );
				}
			}
		} else {
			$customer_ids = array( $customer->item->id );
		}
		HelpScout_API::set_user_customer_ids( $user_id, $customer_ids );
		return $customer_ids;
	}


	//////////////
	// Settings //
	//////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {

		// Settings
		$settings = array(
			'hsd_customer_sync_options' => array(
				'weight' => 20.2,
				'settings' => array(
					self::SYNC_STATUS => array(
						'label' => __( 'User Sync' , 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'All WordPress users will be sent to Help Scout. This should only be done once, since all new registrations are automatically added to Help Scout.' ),
							'type' => 'bypass',
							'output' => self::show_sync_options(),
						),
						'sanitize_callback' => array( __CLASS__, 'maybe_refresh_tags_cache' ),
					),
				),
			),
		);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );
	}

	public static function show_sync_options() {
		$button = sprintf( '<button id="hsd_sync_customers" class="button">%s</button>', __( 'Sync Users', 'help-scout-desk' ) );
		ob_start();
		?>
		<?php echo $button; ?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery("#hsd_sync_customers").on('click', function(event) {
				event.stopPropagation();
				event.preventDefault();
				var $button = jQuery( this );
				
				$button.attr( 'disabled', 'disabled' );
				$button.after('<span class="spinner si_inline_spinner" style="visibility:visible;display:inline-block;"></span>');

				if( confirm( '<?php _e( 'Are you sure? This process could take a while so you may want to go get some coffee.', 'help-scout-desk' ) ?>' ) ) {
					start_sync_via_ajax( 0 );
				}

				function start_sync_via_ajax ( $in_progress ) {
					var $progressBar = jQuery('#clients_sync_progress'),
						$progressInfo = jQuery('#clients_sync_information');
					jQuery.post( ajaxurl, { action: 'hsd_sync_users', in_progress: $in_progress },
						function( response ) {
							console.log(response);
							if ( ! response.success ) {
								$progressInfo.text( response.data );
								$progressBar.css( { width: '100%' } );
								$progressBar.attr( 'aria-valuenow', '100' );
								$progressBar.removeClass( 'active progress-bar-striped' ).addClass( 'progress-bar-error' );
								return;
							};
							// update the informational rows
							$progressBar.css( { width: response.data.progress+'%' } );
							$progressBar.attr( 'aria-valuenow', response.data.progress );
							if ( response.data.message ) {
								$progressInfo.text( response.data.message );
							};

							// continue to loop
							if ( response.data.progress < 100 ) {
								start_sync_via_ajax( response.data.user_sync_count );
							};

							// finish
							if ( response.data.progress >= 100 ) {
								jQuery('.si_inline_spinner').remove();
								$progressBar.removeClass( 'active progress-bar-striped' ).addClass( 'progress-bar-success' );
							};

						}
					);
				}
			});
			//]]>
		</script>

		<div id="hsd_user_sync">
			<p id="clients_sync_information"></p>
			<div class="progress">
				<div id="clients_sync_progress" class="progress-bar progress-bar-striped active" style="overflow:hidden;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">&nbsp;</div>
			</div>
		</div>
		<?php
		$option = ob_get_clean();
		return $option;
	}

	//////////////////
	// AJAX Syncing //
	//////////////////

	/**
	 * Check to see if it's time to start the import process.
	 * @return
	 */
	public static function maybe_init_sync() {
		$count_users = count_users( 'memory' );
		$user_total = $count_users['total_users'];

		// No progress, huh?
		if ( ! isset( $_POST['in_progress'] ) ) {
			wp_send_json_error( __( 'No progress!', 'help-scout-desk' ) );
		}

		$in_progress = $_POST['in_progress'];

		// Just starting out
		if ( ! $in_progress ) {
			wp_send_json_success( array(
				'message' => sprintf( __( 'Starting to sync %s users...', 'help-scout-desk' ), $user_total ),
				'progress' => 5,
				'user_sync_count' => 1,
			) );
			return; // not necessary but whatevs
		}

		$new_progress = self::sync_users();

		// complete
		if ( $new_progress === -1 ) {
			wp_send_json_success( array(
				'message' => sprintf( __( 'It happened, %s users are now in sync at Help Scout!', 'help-scout-desk' ), $user_total ),
				'progress' => 100,
				'user_sync_count' => 1,
			) );
			return; // not necessary but whatevs
		}

		// to be continued...
		wp_send_json_success( array(
			'message' => sprintf( __( 'Some magic in the background has completed %s of the %s users...', 'help-scout-desk' ), $new_progress, $user_total ),
			'progress' => ( ( (int) $new_progress / $user_total ) * 100 ) - 1, // don't want to return 100%
			'user_sync_count' => $new_progress,
		) );
		return; // not necessary but whatevs

	}

	public static function sync_users( $offset = 0 ) {
		if ( ! $offset ) {
			$offset = get_option( self::SYNC_STATUS, 0 );
		}

		$user_query = new WP_User_Query( array( 'number' => 20, 'offset' => $offset ) );
		$users = $user_query->get_results();

		if ( empty( $users ) ) {
			return -1;
		}

		foreach ( $users as $user ) {
			self::sync_user_record( $user );
			// update sync status
			$offset++;
			update_option( self::SYNC_STATUS, $offset );
		}

		return $offset;
	}

	public static function sync_user_record( $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			$user = get_user_by( 'id', absint( $user ) );
		}
		if ( ! is_a( $user, 'WP_User' ) ) {
			return false;
		}
		$customer_object = self::map_wpuser_to_customer( $user );
		self::maybe_update_contact_at_hs( $customer_object );
	}

	public static function map_wpuser_to_customer( WP_User $user ) {
		$user_data = get_userdata( $user->ID );
		$customer_object = array(
				'WPID' => $user->ID,
				'firstName' => ( isset( $user_data->user_firstname ) ) ? $user_data->first_name : '',
				'lastName' => ( isset( $user_data->user_lastname ) ) ? $user_data->last_name : '',
				'background' => ( isset( $user_data->description ) ) ? $user_data->description : '',
				'emails' => ( isset( $user->data->user_email ) && '' != $user->data->user_email ) ? array( array( 'value' => $user->data->user_email, 'location' => 'unknown' ) ) : array(),
				'websites' => ( isset( $user->data->user_url ) && '' != $user->data->user_url ) ? array( array( 'value' => $user->data->user_url ) ) : array(),
			);
		return apply_filters( 'map_wpuser_to_customer', $customer_object, $user );
	}
}
