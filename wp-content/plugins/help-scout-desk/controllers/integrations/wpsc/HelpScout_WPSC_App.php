<?php

/**
 * Help Scout WP eCommerce (WPSC) App Controller
 *
 * @package HelpScout_Desk
 * @subpackage HS API
 */
class HelpScout_WPSC_App extends HSD_Controller {

	const APP_ACTION       = 'helpscout_wpsc';
	const APP_AJAX_HANDLER = 'helpscout_wpsc_action';
	const SECRET_KEY       = 'hs_secret_key';
	private $input         = false;
	protected static $secret_key;

	public static function init() {

		self::$secret_key = get_option( self::SECRET_KEY, wp_generate_password( 40, false ) );

		// Register Settings
		self::register_settings();

		// WPSC Payment history within HS sidebar - main APP
		add_action( 'wp_ajax_' . self::APP_ACTION       , array( __CLASS__, 'help_scout_app' ) );
		add_action( 'wp_ajax_nopriv_' . self::APP_ACTION, array( __CLASS__, 'help_scout_app' ) );

		// AJAX callbacks from view
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			HelpScout_WPSC_App_AJAX::init();
		}
	}

	/**
	 * Main view within HS sidebar
	 */
	public static function help_scout_app() {

		// if this is a HelpScout Request, load the Endpoint class
		if ( ! isset( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) ) {
			return false;
		}

		$wpec_hsd = new HelpScout_WPSC_App_Handler();
		$wpec_hsd->process();
	}

	public static function get_secret_key() {
		return self::$secret_key;
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {

		// Settings
		$settings = array(
			'hsd_wpsc_site_settings' => array(
				'title'    => 'Help Scout WP eCommerce Setup',
				'weight'   => 50,
				'callback' => array( __CLASS__, 'display_wpsc_general_section' ),
				'settings' => array(
					'app_url' => array(
						'label'  => __( 'Callback Url', 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'When creating your Custom App select "Dynamic Content" as the content type. You will then be able to enter this callback url.', 'help-scout-desk' ),
							'type'        => 'bypass',
							'output'      => sprintf( '<code>%s</code>', add_query_arg( array( 'action' => self::APP_ACTION ), get_admin_url().'admin-ajax.php' ) ),
							),
						),
					self::SECRET_KEY => array(
						'label'  => __( 'Secret Key', 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'When creating your Custom App, select "Dynamic Content" as the content type. You will then be able to enter this secret key (that was randomly generated for your site).', 'help-scout-desk' ),
							'type'        => 'text',
							'default'     => self::$secret_key,
							),
						),
				),
			),
		);

		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );

	}

	public static function display_wpsc_general_section() {
		echo '<p>' . _e( 'Help Scout Desk includes an WP eCommerce custom app for Help Scout, just create a custom app in Help Scout with the integration information below. The sidebar block will be shown on the conversation view within Help Scout, showing customer purchase data.', 'help-scout-desk' ) . '</p>';
	}
}
