<?php
/**
 * License handler for Front End PM
 *
 * This class should simplify the process of adding license information
 * to new Front End PM extensions.
 *
 * @version 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Fep_License_Handler' ) ) :

/**
 * Fep_License_Handler Class
 */
class Fep_License_Handler {
	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author = 'Shamim Hasan';
	private $api_url = 'https://www.shamimsplugins.com/';

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item, $_version, $_author = null, $_optname = null, $_api_url = null ) {

		$this->file           = $_file;
		$this->item_name  = $_item;
		$this->item_shortname = preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( fep_get_option( $this->item_shortname . '_license_key', '' ) );
		$this->author         = is_null( $_author ) ? $this->author : $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		// Setup hooks
		$this->includes();
		$this->hooks();

	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if( !class_exists( 'Fep_Plugin_Updater' ) ) {
			// load our custom updater
			include( 'Fep_Plugin_Updater.php' );
		}
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );
		
		// Register settings
		
		add_filter( 'fep_admin_settings_tabs', array($this, 'admin_settings_tabs' ) );
		add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );

		// Activate license key on settings save
		add_action( 'fep_action_before_admin_options_save', array( $this, 'activate_license' ), 10, 2 );

		// Deactivate license key
		add_action( 'fep_action_before_admin_options_save', array( $this, 'deactivate_license' ) );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {

		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author,
			'item_name'	=> $this->item_name
		);

		// Setup the updater
		$updater = new Fep_Plugin_Updater(
			$this->api_url,
			$this->file,
			$args
		);
	}


	/**
	 * Add license tab to settings
	 *
	 * @access  public
	 * @param array   $tabs
	 * @return  array
	 */
		function admin_settings_tabs( $tabs ) {
		
			$tabs['licenses'] =  array(
					'tab_title'			=> __('Licenses', 'front-end-pm'),
					'priority'			=> 35
					);
			return $tabs;
		}
		
	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $fields
	 * @return  array
	 */
		
		function settings_fields( $fields )
		{
			$fields[$this->item_shortname . '_license_key'] =   array(
				'section'	=> 'licenses',
				'value' => trim( fep_get_option( $this->item_shortname . '_license_key', '' ) ),
				'label' => sprintf(__( '%s License.', 'front-end-pm' ), $this->item_name ),
				'description' => $this->description()
				);
			return $fields;
		}
		
	/**
	 * Get license data
	 *
	 * @access  public
	 * @return  bool|object
	 */
		
		function get_licence_data(){
			if( empty( $this->license ) ) {
				return false;
			}
			$license_data = $this->get_transient( $this->item_shortname . '_license_data' );
			
			if( false === $license_data ) {

				// data to send in our API request
				$api_params = array(
					'edd_action'=> 'check_license',
					'license' 	=> $this->license,
					'item_name' => urlencode( $this->item_name ),
					'url'       => home_url()
				);
		
				// Call the API
				$response = wp_remote_post(
					$this->api_url,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params
					)
				);
		
				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					return false;
				}
		
				$license_data = wp_remote_retrieve_body( $response );
		
				$this->set_transient( $license_data );
			}
			
			$license_data = json_decode( $license_data );
			
			return $license_data;
		}
		
		function description(){
			$license_data = $this->get_licence_data();
			
			if( ! empty( $license_data ) && is_object( $license_data ) ) {
				// activate_license 'invalid' on anything other than valid, so if there was an error capture it
				if ( false === $license_data->success ) {
					if( empty( $license_data->error ) ){
						//return when check_license
						$messages = $this->switch_description( $license_data->license, $license_data );
					} else {
						//return when activate_license
						$messages = $this->switch_description( $license_data->error, $license_data );
					}
				} else {
					$messages = $this->switch_description( $license_data->license, $license_data );
				} //End success
			} else {
				$messages = '<div style="background-color:powderblue;"><p>'.sprintf(__( 'To receive updates, please enter your valid %s license key.', 'front-end-pm' ), $this->item_name ).'</p></div>';
			}
			if ( is_object( $license_data ) && 'valid' == $license_data->license ){
				$messages =  '<button type="submit" class="button-secondary" name="' . $this->item_shortname . '_license_deactivate" value="1">' . __( 'Deactivate License',  'front-end-pm' ) . '</button><br />' . $messages;
			}
			return $messages;
		}
		
		function switch_description( $switch, $license_data ){
			switch( $switch ){
				case 'expired' :
						$messages = '<div style="background-color:red;color:White"><p>'. sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'front-end-pm' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ),
							'https://www.shamimsplugins.com/checkout/?edd_license_key=' . $this->license .'&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						). '</p></div>';

						break;

					case 'revoked' :
					case 'disabled' :

						$messages = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'front-end-pm' ),
							'https://www.shamimsplugins.com/support/?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
						);

						break;

					case 'missing' :
						$messages = '<div style="background-color:Red;color:White"><p>'. sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'front-end-pm' ),
							'https://www.shamimsplugins.com/checkout/purchase-history/?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						). '</p></div>';

						break;

					case 'invalid' :
					case 'site_inactive' :
					case 'invalid_item_id' :
					case 'inactive' :
					case 'deactivated' :
						$messages = '<div style="background-color:red;color:White"><p>'. sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'front-end-pm' ),
							$this->item_name,
							'https://www.shamimsplugins.com/checkout/purchase-history/?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						). '</p></div>';

						break;
					
					case 'failed' :
						$messages = sprintf(
							__( 'Your %s deactivation failed. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'front-end-pm' ),
							$this->item_name,
							'https://www.shamimsplugins.com/checkout/purchase-history/?utm_campaign=admin&utm_source=licenses&utm_medium=deactivation_failed'
						);

						break;

					case 'item_name_mismatch' :
						$messages = sprintf( __( 'This appears to be an invalid license key for %s.', 'front-end-pm' ), $this->item_name );

						break;

					case 'no_activations_left':
						$messages = '<div style="background-color:red;color:White"><p>'. sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'front-end-pm' ), 'https://www.shamimsplugins.com/checkout/purchase-history/?utm_campaign=admin&utm_source=licenses&utm_medium=no_activations_left' ). '</p></div>';

						break;
						
				case 'valid' :
					default:
						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license_data->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license_data->expires ) {

							$messages = __( 'License key never expires.', 'front-end-pm' );

						} elseif( $expiration > $now && $expiration - $now < MONTH_IN_SECONDS ) {

							$messages = '<div style="background-color:OrangeRed;color:White"><p>'. sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'front-end-pm' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ),
								'https://www.shamimsplugins.com/checkout/?edd_license_key=' . $this->license .'&utm_campaign=admin&utm_source=licenses&utm_medium=expires_soon'
							). '</p></div>';

						} else {

							$messages = sprintf(
								__( 'Your license key expires on %s.', 'front-end-pm' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);

						}

						break;
			}
			return $messages;
		}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license( $settings, $tab ) {
		
		if( 'licenses' !== $tab ) {
			return;
		}
		
		if( ! empty( $_POST[$this->item_shortname . '_license_deactivate'] ) ){
			return;
		}
		
		if( empty( $settings[$this->item_shortname . '_license_key'] ) ){
			$this->delete_transient( $this->item_shortname . '_license_data' );
			return;
		}
		/*
		if( fep_get_option( $this->item_shortname . '_license_key', '' ) === $settings[$this->item_shortname . '_license_key'] ) {
			$prev_license_data = $this->get_licence_data();
			
			if( is_object( $prev_license_data ) && 'valid' === $prev_license_data->license ){
				return;
			}
		}
		*/

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $settings[$this->item_shortname . '_license_key'],
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		$license_data = wp_remote_retrieve_body( $response );

		$this->set_transient( $license_data );

	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license( $settings ) {

		if( empty( $_POST[$this->item_shortname . '_license_deactivate'] ) ){
			return;
		}

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $settings[$this->item_shortname . '_license_key'],
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return;
			}

			$license_data = wp_remote_retrieve_body( $response );

			$this->set_transient( $license_data );

	}


	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {
	
		if( ! current_user_can( 'update_plugins' ) )
			return;

		$license_data = $this->get_licence_data();
		//var_dump($license_data);

		if( ! is_object( $license_data ) || 'valid' !== $license_data->license ) {

			if( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {

				echo '<div class="error"><p>'. sprintf(
					__( 'You have invalid or expired license key for %1$s. Please go to <a href="%2$s">Licenses page</a> to correct this issue.', 'front-end-pm' ),
					$this->item_name,
					admin_url( 'edit.php?post_type=fep_message&page=fep_settings&tab=licenses' )
				). '</p></div>';

			}

		} elseif( 'lifetime' !== $license_data->expires ) {
			$now        = current_time( 'timestamp' );
			$expiration = strtotime( $license_data->expires, current_time( 'timestamp' ) );

			if( $expiration > $now && $expiration - $now < MONTH_IN_SECONDS ) {

				echo '<div class="error"><p>'. sprintf(
					__( 'Your license key expires soon for %1$s! Please <a href="%2$s" target="_blank">Renew your license key</a> by %3$s.', 'front-end-pm' ),
					$this->item_name,
					'https://www.shamimsplugins.com/checkout/?edd_license_key=' . $this->license .'&utm_campaign=admin&utm_source=licenses&utm_medium=expires_soon',
					date_i18n( get_option( 'date_format' ), $expiration )
				). '</p></div>';

			}
		}

	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		$license_data = $this->get_licence_data();

		if( ( ! is_object( $license_data ) || 'valid' !== $license_data->license ) ) {
		
			echo '&nbsp;<strong>'. sprintf(
					__( 'Enter <a href="%s">valid license key</a> for automatic updates.', 'front-end-pm' ),
					esc_url( admin_url( 'edit.php?post_type=fep_message&page=fep_settings&tab=licenses' ) )
				). '</a></strong>';
		}

	}
	
	public function get_transient( $cache_key = '' ) {

		if( empty( $cache_key ) ) {
			$cache_key = $this->item_shortname . '_license_data';
		}

		$cache = get_option( $cache_key );

		if( empty( $cache['timeout'] ) || current_time( 'timestamp' ) > $cache['timeout'] ) {
			return false; // Cache is expired
		}

		return isset( $cache['value'] ) ? $cache['value'] : false;

	}

	public function set_transient( $value, $cache_key = '', $expiry = WEEK_IN_SECONDS ) {

		if( empty( $cache_key ) ) {
			$cache_key = $this->item_shortname . '_license_data';
		}

		$data = array(
			'timeout' => current_time( 'timestamp' ) + (int) $expiry,
			'value'   => $value
		);

		update_option( $cache_key, $data, 'no' );

	}
	
	public function delete_transient( $cache_key = '' ) {

		if( empty( $cache_key ) ) {
			$cache_key = $this->item_shortname . '_license_data';
		}

		delete_option( $cache_key );

	}
}

endif; // end class_exists check
