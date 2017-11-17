<?php
/*
Plugin Name: Front End PM PRO
Plugin URI: https://www.shamimsplugins.com/wordpress/contact-us/
Description: Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system fromfront end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 4.8
Author: Shamim
Author URI: https://www.shamimsplugins.com/wordpress/contact-us/
Text Domain: front-end-pm
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !defined ('FEP_PLUGIN_VERSION' ) )
define('FEP_PLUGIN_VERSION', '4.8' );
			
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
	global $wpdb;
		
	$roles = array_keys( get_editable_roles() );
	$id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[front-end-pm]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1");
	
	$options = array();
	
	$options['userrole_access'] = $roles;
	$options['userrole_new_message'] = $roles;
	$options['userrole_reply'] = $roles;
	$options['plugin_version'] = FEP_PLUGIN_VERSION;
	$options['page_id'] = $id;
	
	update_option( 'FEP_admin_options', wp_parse_args( get_option('FEP_admin_options'), $options) );
	
	fep_eb_reschedule_event();
	fep_add_caps_to_roles();
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

if ( !function_exists('fep_get_plugin_caps') ) :

function fep_get_plugin_caps( $edit_published = false, $for = 'both' ){
	$message_caps = array(
		'delete_published_fep_messages' => 1,
		'delete_private_fep_messages' => 1,
		'delete_others_fep_messages' => 1,
		'delete_fep_messages' => 1,
		'publish_fep_messages' => 1,
		'read_private_fep_messages' => 1,
		'edit_private_fep_messages' => 1,
		'edit_others_fep_messages' => 1,
		'edit_fep_messages' => 1,
		);
	
	$announcement_caps = array(
		'delete_published_fep_announcements' => 1,
		'delete_private_fep_announcements' => 1,
		'delete_others_fep_announcements' => 1,
		'delete_fep_announcements' => 1,
		'publish_fep_announcements' => 1,
		'read_private_fep_announcements' => 1,
		'edit_private_fep_announcements' => 1,
		'edit_others_fep_announcements' => 1,
		'edit_fep_announcements' => 1,
		'create_fep_announcements' => 1,
		);
	
	if( 'fep_message' == $for ) {
		$caps = $message_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
		}
	} elseif( 'fep_announcement' == $for ){
		$caps = $announcement_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_announcements'] = 1;
		}
	} else {
		$caps = array_merge( $message_caps, $announcement_caps );
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
			$caps['edit_published_fep_announcements'] = 1;
		}
	}
	return $caps;
}

endif;

if ( !function_exists('fep_add_caps_to_roles') ) :

function fep_add_caps_to_roles( $roles = array( 'administrator', 'editor' ) ) {

	if( ! is_array( $roles ) )
		$roles = array();
	
	$caps = fep_get_plugin_caps();
	
	foreach( $roles as $role ) {
		$role_obj = get_role( $role );
		if( !$role_obj )
			continue;
			
		foreach( $caps as $cap => $val ) {
			if( $val )
				$role_obj->add_cap( $cap );
		}
	}
}

endif;
	
