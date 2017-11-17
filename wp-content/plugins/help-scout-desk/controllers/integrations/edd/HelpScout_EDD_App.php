<?php

/**
 * Help Scout EDD App Controller
 *
 * @package HelpScout_Desk
 * @subpackage HS API
 */
class HelpScout_EDD_App extends HSD_Controller {
	const APP_ACTION = 'helpscout_edd';
	const APP_AJAX_HANDLER = 'helpscout_edd_action';
	const SECRET_KEY = 'hs_secret_key';
	protected static $secret_key;
	private $input = false;


	public static function init() {
		self::$secret_key = get_option( self::SECRET_KEY, wp_generate_password( 40, false ) );
		// Register Settings
		self::register_settings();

		// EDD Payment history within HS sidebar - main APP
		add_action( 'wp_ajax_'.self::APP_ACTION, array( __CLASS__, 'help_scout_app' ) );
		add_action( 'wp_ajax_nopriv_'.self::APP_ACTION, array( __CLASS__, 'help_scout_app' ) );

		// AJAX callbacks from view
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			HelpScout_EDD_App_AJAX::init();
		}
	}

	/**
	 * Main view within HS sidebar
	 */
	public static function help_scout_app() {
		if ( ! isset( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) ) {
			return false;
		}

		// if this is a HelpScout Request, load the Endpoint class
		$edd_hsd = new HelpScout_EDD_App_Handler();
		$edd_hsd->process();
	}

	public static function get_secret_key() {
		return self::$secret_key;
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
			'hsd_edd_site_settings' => array(
				'title' => 'Help Scout EDD Setup',
				'weight' => 50,
				'callback' => array( __CLASS__, 'display_edd_general_section' ),
				'settings' => array(
					'app_url' => array(
						'label' => __( 'Callback Url', 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'When creating your Custom App select "Dynamic Content" as the content type. You will then be able to enter this callback url.', 'help-scout-desk' ),
							'type' => 'bypass',
							'output' => sprintf( '<code>%s</code>', add_query_arg( array( 'action' => self::APP_ACTION ), get_admin_url().'admin-ajax.php' ) ),
							),
						),
					self::SECRET_KEY => array(
						'label' => __( 'Secret Key', 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'When creating your Custom App select "Dynamic Content" as the content type. You will then be able to enter this secret key (that was randomly generated for your site).', 'help-scout-desk' ),
							'type' => 'text',
							'default' => self::$secret_key,
							),
						),
				),
			),
		);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );
	}

	//////////////////////
	// General Settings //
	//////////////////////

	public static function display_edd_general_section() {
		echo '<p>'._e( 'Help Scout Desk includes an Easy Digital Downloads custom app for Help Scout, just create a custom app in Help Scout with the integration information below. The sidebar block will be shown on the conversation view within Help Scout, showing customer purchase data and license information.', 'help-scout-desk' ).'</p>';
	}
}
