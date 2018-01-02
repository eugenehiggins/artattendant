<?php
/*
Plugin Name: Front End PM PRO
Plugin URI: https://www.shamimsplugins.com/contact-us/
Description: Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system fromfront end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 6.2
Author: Shamim
Author URI: https://www.shamimsplugins.com/contact-us/
Text Domain: front-end-pm
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	
class Front_End_Pm_Pro {

	private static $instance;
	
	private function __construct() {
		if( class_exists( 'Front_End_Pm' ) ) {
			// Display notices to admins
			add_action( 'admin_notices', array( $this, 'notices' ) );
			return;
		}
		$this->constants();
		$this->includes();
		//$this->actions();
		//$this->filters();

	}
	
	public static function init()
        {
            if(!self::$instance instanceof self) {
                self::$instance = new self;
            }
            return self::$instance;
        }
	
	private function constants()
    	{
			global $wpdb;
			
			define('FEP_PLUGIN_VERSION', '6.2' );
			define('FEP_PLUGIN_FILE',  __FILE__ );
			define('FEP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define('FEP_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
			
			if ( !defined ('FEP_MESSAGES_TABLE' ) )
			define('FEP_MESSAGES_TABLE',$wpdb->prefix.'fep_messages');
			
			if ( !defined ('FEP_META_TABLE' ) )
			define('FEP_META_TABLE',$wpdb->prefix.'fep_meta');
    	}
	
	private function includes()
    	{
			require_once( FEP_PLUGIN_DIR. 'functions.php');

			if( file_exists( FEP_PLUGIN_DIR. 'pro/pro-features.php' ) ) {
				require_once( FEP_PLUGIN_DIR. 'pro/pro-features.php');
			}
    	}
	
	private function actions()
    	{

    	}

	
	public function notices() {

			echo '<div class="error"><p>'. __( 'Deactivate Front End PM to activate Front End PM PRO.', 'front-end-pm' ). '</p></div>';

	}
} //END Class

add_action( 'plugins_loaded', array( 'Front_End_Pm_Pro', 'init' ) );

register_activation_hook(__FILE__ , 'front_end_pm_pro_activate' );
register_deactivation_hook(__FILE__ , 'front_end_pm_pro_deactivate' );

function front_end_pm_pro_activate() {
	
	// fep_ann_email_interval in not present here
	//fep_eb_reschedule_event();
}

function front_end_pm_pro_deactivate(){
	wp_clear_scheduled_hook('fep_eb_ann_email_interval_event');
}

function fep_eb_reschedule_event() {
    if ( wp_next_scheduled ( 'fep_eb_ann_email_interval_event' ) ) {
		wp_clear_scheduled_hook('fep_eb_ann_email_interval_event');
    }
	wp_schedule_event(time(), 'fep_ann_email_interval', 'fep_eb_ann_email_interval_event');
}
	
