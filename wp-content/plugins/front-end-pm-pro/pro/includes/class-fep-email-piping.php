<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Email_Piping
  {
	private static $instance;
	
	public static function init()
        {
            if(!self::$instance instanceof self) {
                self::$instance = new self;
            }
            return self::$instance;
        }
	
    function actions_filters()
    	{
			add_filter( 'fep_admin_settings_tabs', array($this, 'admin_settings_tabs' ) );
			add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );
			
			if( fep_get_option('ep_enable', 0 ) ) {
				add_action( 'fep_action_after_add_email_filters', array($this, 'fep_action_after_add_email_filters' ) );
				add_action( 'fep_action_after_remove_email_filters', array($this, 'fep_action_after_remove_email_filters' ) );
				add_filter( 'fep_filter_before_email_send', array($this, 'filter_before_email_send' ), 99, 3 );
			}
			
    	}
	
	function admin_settings_tabs( $tabs ) {
				
		$tabs['email_piping'] =  array(
				'section_title'		=> __('Email Piping', 'front-end-pm'),
				'section_page'		=> 'fep_settings_emails',
				'priority'			=> 53,
				'tab_output'		=> false
				);
				
		return $tabs;
	}
	
	function settings_fields( $fields )
		{
			$fields['ep_enable'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'email_piping',
				'value' => fep_get_option('ep_enable', 0 ),
				'label' => __( 'Enable', 'front-end-pm' ),
				'cb_label' => __( 'Enable email piping?', 'front-end-pm' )
				);
			$fields['ep_email'] =   array(
				'type'	=>	'email',
				'section'	=> 'email_piping',
				'value' => fep_get_option('ep_email', get_bloginfo('admin_email') ),
				'description' => __( 'Use this email as email piping.', 'front-end-pm' ),
				'label' => __( 'Piping Email', 'front-end-pm' )
				);
			$fields['ep_clean_reply'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'email_piping',
				'value' => fep_get_option('ep_clean_reply', 1 ),
				'label' => __( 'Clean reply quote', 'front-end-pm' ),
				'cb_label' => __( 'Clean reply quote from email?', 'front-end-pm' )
				);
								
			return $fields;
			
		}
		
		function fep_action_after_add_email_filters( $for ){
			
			if( 'message' == $for ) {
				add_filter( 'wp_mail_from', array($this, 'email_filters' ), 10, 1 );
			}
		}
		
		function fep_action_after_remove_email_filters( $for ){
		
			if( 'message' == $for ) {
				remove_filter( 'wp_mail_from', array($this, 'email_filters' ), 10, 1 );
			}
		}
		
		function email_filters( $from_email ){
			
			$email = fep_get_option('ep_email', get_bloginfo('admin_email'));
	
			if( is_email( $email ) ) {
				return $email;
			}
			return $from_email;	
		}
		
		function filter_before_email_send( $message, $post, $to ){
			if( empty( $message['subject'] ) )
				return $message;
			
			$parent_id = fep_get_parent_id( $post->ID );
			
			$key = get_post_meta( $parent_id, '_message_key', true );
			
			if( ! $key ) {
				global $wpdb;
				do{
					$key = wp_generate_password( 12, false );
					$message_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_message_key' AND meta_value = '%s' LIMIT 1", $key ) );
					
				} while( $message_id );
				
				update_post_meta( $parent_id, '_message_key', $key );
			}
			
			if( is_multisite() ) {
				$key .= '-' . get_current_blog_id();
			}
			
			$message['subject'] = $message['subject'] . ' [MESSAGE KEY-' . $key . ']';
			
			return $message;
		}

  } //END CLASS

add_action('init', array(Fep_Email_Piping::init(), 'actions_filters'));

