<?php
/*
Plugin Name: Easy Digital Downloads - Commissions
Plugin URI: http://easydigitaldownloads.com/extension/commissions
Description: Record commissions automatically for users in your site when downloads are sold
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Version: 3.4.5
Text Domain: eddc
Domain Path: languages
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDDC' ) ) {

	/**
	 * Main EDDC class
	 *
	 * @since       3.4.0
	 */
	class EDDC {

		/**
		 * @var         EDDC $instance The one true EDDC
		 * @since       3.4.0
		 */
		private static $instance;


		/**
		 * @var         object $commissions_db The EDDC database object
		 * @since       3.4.0
		 */
		public $commissions_db;

		/**
		 * @var         object $commission_meta_db The EDDC_Meta database object
		 * @since       3.4.0
		 */
		public $commission_meta_db;


		private function __construct() {
			if ( ! class_exists( 'Easy_Digital_Downloads' ) ){
				return;
			}

			$this->setup_constants();
			$this->includes();
			$this->load_textdomain();
			$this->hooks();
			$this->commissions_db = new EDDC_DB();
			$this->commission_meta_db = new EDDC_Meta_DB();
		}
		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       3.4.0
		 * @return      object self::$instance The one true EDDC
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new EDDC();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       3.4.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_COMMISSIONS_VERSION', '3.4.5' );

			// Plugin folder url
			define( 'EDDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin folder path
			define( 'EDDC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin root file
			define( 'EDDC_PLUGIN_FILE', __FILE__ );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.1
		 * @return      void
		 */
		private function includes() {
			// Integration - Simple Shipping
			if ( class_exists( 'EDD_Simple_Shipping' ) ){
				require_once EDDC_PLUGIN_DIR . 'includes/integrations/simple-shipping.php';
			}

			// Integration - Paypal Adaptive
			if ( function_exists( 'epap_load_class' ) ) {
				require_once EDDC_PLUGIN_DIR . 'includes/integrations/paypal-adaptive-payments.php';
			}

			// Integration - Recurring Payments
			if ( class_exists( 'EDD_Recurring' ) ) {
				require_once EDDC_PLUGIN_DIR . 'includes/integrations/recurring-payments.php';
			}

			require_once EDDC_PLUGIN_DIR . 'includes/commission-actions.php';
			require_once EDDC_PLUGIN_DIR . 'includes/commission-functions.php';
			require_once EDDC_PLUGIN_DIR . 'includes/commission-filters.php';
			require_once EDDC_PLUGIN_DIR . 'includes/email-functions.php';
			require_once EDDC_PLUGIN_DIR . 'includes/post-type.php';
			require_once EDDC_PLUGIN_DIR . 'includes/scripts.php';
			require_once EDDC_PLUGIN_DIR . 'includes/short-codes.php';
			require_once EDDC_PLUGIN_DIR . 'includes/user-meta.php';

			require_once EDDC_PLUGIN_DIR . 'includes/classes/class-edd-commission.php';
			require_once EDDC_PLUGIN_DIR . 'includes/classes/class-edd-commission-db.php';
			require_once EDDC_PLUGIN_DIR . 'includes/classes/class-edd-commission-meta-db.php';
			require_once EDDC_PLUGIN_DIR . 'includes/classes/class-rest-api.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once EDDC_PLUGIN_DIR . '/includes/integrations/wp-cli.php';
			}

			if ( is_admin() ) {
				// Handle licensing
				if ( class_exists( 'EDD_License' ) ) {
					$eddc_license = new EDD_License( __FILE__, 'Commissions', EDD_COMMISSIONS_VERSION, 'Pippin Williamson' );
				}

				// These are no longer used... do they still need to be here?
				//require_once(EDDC_PLUGIN_DIR . 'includes/scheduled-payouts.php');
				//require_once(EDDC_PLUGIN_DIR . 'includes/masspay/class-paypal-masspay.php');

				require_once EDDC_PLUGIN_DIR . 'includes/admin/commissions.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/commissions-actions.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/commissions-filters.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/customers.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/export-actions.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/export-functions.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/metabox.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/misc-functions.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/reports.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/settings.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/widgets.php';

				require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/EDD_C_List_Table.php';
				require_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-admin-notices.php';

				require_once EDDC_PLUGIN_DIR . 'includes/admin/upgrades.php';
				require_once EDDC_PLUGIN_DIR . 'includes/deprecated-functions.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.1
		 * @return      void
		 */
		private function hooks() {
			add_action( 'fes_load_fields_require', array( $this, 'add_fes_functionality' ) );
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      public
		 * @since       3.4.0
		 * @return      void
		 */
		public function add_fes_functionality() {
			if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
				if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
					require_once( EDDC_PLUGIN_DIR . 'includes/integrations/fes-commissions-email-field.php' );

					add_filter(  'fes_load_fields_array', 'eddc_add_commissions_email', 10, 1 );

					function eddc_add_commissions_email( $fields ) {
						$fields['eddc_user_paypal'] = 'FES_Commissions_Email_Field';
						return $fields;
					}
				}
			}
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.1
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDDC_PLUGIN_DIR . '/languages/';
			$lang_dir = apply_filters( 'eddc_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'eddc' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'eddc', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-commissions/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-commissions/ folder
				load_textdomain( 'eddc', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/eddc/languages/ folder
				load_textdomain( 'eddc', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'eddc', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true EDDC
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDDC The one true EDDC
 */
function edd_commissions() {
	return EDDC::instance();
}
add_action( 'plugins_loaded', 'edd_commissions', 1 );

/**
 * Process install/upgrades
 *
 * @since       3.4.0
 * @return      void
 */
function edd_commissions_install() {

	global $wpdb;

	// We need the main Commissions plugin loaded to add the table.
	edd_commissions();

	if ( class_exists( 'EDDC_DB' ) ) {

		$db = new EDDC_DB;
		@$db->create_table();

		$meta_db = new EDDC_Meta_DB;
		@$meta_db->create_table();

		$version = get_option( 'eddc_version' );

		if ( empty( $version ) ) {
			if ( ! function_exists( 'edd_set_upgrade_complete' ) ) {
				require_once trailingslashit( EDD_PLUGIN_DIR ) . 'includes/admin/upgrades/upgrade-functions.php';
			}

			$results         = $wpdb->get_row( "SELECT count(ID) as has_commissions FROM $wpdb->posts WHERE post_type = 'edd_commission' LIMIT 0, 1" );
			$has_commissions = ! empty( $results->has_commissions ) ? true : false;

			if ( ! $has_commissions ) {
				edd_set_upgrade_complete( 'migrate_commissions' );
				edd_set_upgrade_complete( 'remove_legacy_commissions' );
				edd_set_upgrade_complete( 'commissions_342_rate_column' );
			}
		}

		update_option( 'eddc_version', EDD_COMMISSIONS_VERSION );
	}
}
register_activation_hook( __FILE__, 'edd_commissions_install' );