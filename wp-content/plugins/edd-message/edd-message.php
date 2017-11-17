<?php
/**
 * Plugin Name:     Easy Digital Downloads - Message
 * Plugin URI:      https://realbigplugins.com
 * Description:     EDD Message adds powerful email messaging capabilities through which store owners can correspond with customers and vendors.
 * Version:         1.0
 * Author:          Kyle Maurer
 * Author URI:      http://realbigplugins.com
 * Text Domain:     edd-message
 *
 * @package         EDD\EddMessage
 * @author          brashrebel
 * @copyright       Copyright (c) Real Big Plugins
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Message' ) ) {

    /**
     * Main EDD_Message class
     *
     * @since       0.1.0
     */
    class EDD_Message {

        /**
         * @var         EDD_Message $instance The one true EDD_Message
         * @since       0.1.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       0.1.0
         * @return      object self::$instance The one true EDD_Message
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Message();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       0.1.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_MESSAGE_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_MESSAGE_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_MESSAGE_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       0.1.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_MESSAGE_DIR . 'includes/scripts.php';
            require_once EDD_MESSAGE_DIR . 'includes/functions.php';
            require_once EDD_MESSAGE_DIR . 'includes/logging.php';
	        require_once EDD_MESSAGE_DIR . 'includes/admin/customer.php';
	        if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
		        require_once EDD_MESSAGE_DIR . 'includes/fes/frontend.php';
		        require_once EDD_MESSAGE_DIR . 'includes/fes/backend.php';
	        }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       0.1.0
         * @return      void
         */
        private function hooks() {
            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'EDD Message', EDD_MESSAGE_VER, 'Kyle Maurer' );
            }
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       0.1.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_MESSAGE_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_message_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-message' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-message', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-message/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-message/ folder
                load_textdomain( 'edd-message', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-message/languages/ folder
                load_textdomain( 'edd-message', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-message', false, $lang_dir );
            }
        }
    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Message
 * instance to functions everywhere
 *
 * @since       0.1.0
 * @return      \EDD_Message The one true EDD_Message
 */
function edd_message_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return edd_message::instance();
    }
}
add_action( 'plugins_loaded', 'edd_message_load' );
