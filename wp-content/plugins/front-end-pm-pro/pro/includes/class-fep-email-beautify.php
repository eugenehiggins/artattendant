<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Email_Beautify
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
			add_action( 'fep_pro_plugin_update', array($this, 'email_beautify_activate' ));
			add_filter( 'cron_schedules', array($this, 'cron_schedules' ));
			
			add_action( 'fep_action_before_admin_options_save', array($this, 'reschedule_event_on_save' ));
			add_filter( 'fep_admin_settings_tabs', array($this, 'admin_settings_tabs' ) );
			add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );
			add_filter( 'fep_filter_before_email_send', array($this, 'filter_before_email_send' ), 10, 3 );
			add_filter( 'fep_filter_before_announcement_email_send', array($this, 'filter_before_announcement_email_send' ), 10, 3 );
			add_action( 'fep_eb_ann_email_interval_event', array($this, 'ann_email_interval_event_callback' ) );
    	}
	
	function email_beautify_activate(){
	
		if( false !== fep_get_option( 'eb_newmessage_subject', false ) )
			return;
			
		$options = array();
	
		$options['eb_newmessage_subject'] = '{{site_title}} - New message';
		$options['eb_newmessage_content'] = '<p>Hi {{receiver}},<br />You have received a new message in {{site_title}}.<br />Subject: {{subject}}<br />Message: {{message}}<br />Message URL: <a href="{{message_url}}">{{message_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		$options['eb_reply_subject'] = '{{site_title}} - New reply';
		$options['eb_reply_content'] = '<p>Hi {{receiver}},<br />You have received a new reply of your message in {{site_title}}.<br />Subject: {{subject}}<br />Message: {{message}}<br />Message URL: <a href="{{message_url}}">{{message_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		$options['eb_announcement_subject'] = '{{site_title}} - Announcement';
		$options['eb_announcement_content'] = '<p>Hi {{receiver}},<br />A new announcement is published in {{site_title}}.<br />Title: {{subject}}<br />Announcement: {{message}}<br />Announcement URL: <a href="{{announcement_url}}">{{announcement_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		
		$admin_options = wp_parse_args( get_option('FEP_admin_options'), $options);
		$admin_options['email_content_type'] = 'html';
			
		update_option( 'FEP_admin_options', $admin_options );
		
	}
	
	function cron_schedules( $schedules ) {
		$interval = absint( fep_get_option( 'eb_announcement_interval', 60 ) );
			
		if( !$interval ) {
			$interval = 1;
		}
			
		$schedules['fep_ann_email_interval'] = array(
			'interval' => $interval * MINUTE_IN_SECONDS,
			'display' => __('Interval for sending announcement emails', 'front-end-pm')
		);
		return $schedules;
	}

	function reschedule_event_on_save( $settings ){
		if( fep_get_option('eb_announcement_interval', 60 ) != $settings['eb_announcement_interval'] ) {
			fep_eb_reschedule_event();
		}
	}
	
	function email_legends( $where = 'newmessage', $post = '', $value = 'description', $user_email = '' ){
		$legends = array(
			'subject' => array(
				'description' => __('Subject', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_title ) ? $post->post_title : ''
				),
			'message' => array(
				'description' => __('Full Message', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_content ) ? $post->post_content : ''
				),
			'message_url' => array(
				'description' => __('URL of message', 'front-end-pm'),
				'where' => array( 'newmessage', 'reply' ),
				'replace_with' => ! empty( $post->ID ) ? fep_query_url( 'viewmessage', array( 'id' => $post->ID ) ) : ''
				),
			'announcement_url' => array(
				'description' => __('URL of announcement', 'front-end-pm'),
				'where' => 'announcement',
				'replace_with' => ! empty( $post->ID ) ? fep_query_url( 'viewannouncement', array( 'id' => $post->ID ) ) : ''
				),
			'sender' => array(
				'description' => __('Sender', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_author ) ? fep_get_userdata( $post->post_author, 'display_name', 'id' ) : ''
				),
			'receiver' => array(
				'description' => __('Receiver', 'front-end-pm'),
				'replace_with' => fep_get_userdata( $user_email, 'display_name', 'email' )
				),
			'site_title' => array(
				'description' => __('Website title', 'front-end-pm'),
				'replace_with' => get_bloginfo('name')
				),
			'site_url' => array(
				'description' => __('Website URL', 'front-end-pm'),
				'replace_with' => get_bloginfo('url')
				),
			);
		$legends = apply_filters( 'fep_eb_email_legends', $legends, $post, $user_email );
		
		$ret = array();
		foreach( $legends as $k => $legend ) {
		
				if ( empty($legend['where']) )
					$legend['where'] = array( 'newmessage', 'reply', 'announcement' );
				
				if( is_array($legend['where'])){
					if ( ! in_array(  $where, $legend['where'] )){
						continue;
					}
				} else {
					if ( $where != $legend['where'] ){
						continue;
					}
				}
				if( 'description' == $value ) {
					$ret[$k] = '<code>{{' . $k . '}}</code> = ' . $legend['description'];
				} else {
					$ret['{{' . $k . '}}'] = $legend['replace_with'];
				}
		}
		return $ret;
	}
	
	function admin_settings_tabs( $tabs ) {
				
		$tabs['eb_newmessage'] =  array(
				'section_title'			=> __('New Message email', 'front-end-pm'),
				'section_page'		=> 'fep_settings_emails',
				'priority'			=> 55,
				'tab_output'		=> false
				);
		$tabs['eb_reply'] =  array(
				'section_title'			=> __('Reply Message email', 'front-end-pm'),
				'section_page'		=> 'fep_settings_emails',
				'priority'			=> 65,
				'tab_output'		=> false
				);
		$tabs['eb_announcement'] =  array(
				'section_title'			=> __('Announcement email', 'front-end-pm'),
				'section_page'		=> 'fep_settings_emails',
				'priority'			=> 75,
				'tab_output'		=> false
				);
				
		return $tabs;
	}
	
	function settings_fields( $fields )
		{
			$fields['eb_newmessage_subject'] =   array(
				'section'	=> 'eb_newmessage',
				'value' => fep_get_option('eb_newmessage_subject', ''),
				'label' => __( 'New message subject.', 'front-end-pm' )
				);
			$fields['eb_newmessage_content'] =   array(
				'type' => 'teeny',
				'section'	=> 'eb_newmessage',
				'value' => fep_get_option('eb_newmessage_content', ''),
				'description' => implode( '<br />', $this->email_legends() ),
				'label' => __( 'New message content.', 'front-end-pm' )
				);
			$fields['eb_reply_subject'] =   array(
				'section'	=> 'eb_reply',
				'value' => fep_get_option('eb_reply_subject', ''),
				'label' => __( 'Reply subject.', 'front-end-pm' )
				);
			$fields['eb_reply_content'] =   array(
				'type' => 'teeny',
				'section'	=> 'eb_reply',
				'value' => fep_get_option('eb_reply_content', ''),
				'description' => implode( '<br />', $this->email_legends( 'reply' ) ),
				'label' => __( 'Reply content.', 'front-end-pm' )
				);
			$fields['eb_announcement_interval'] =   array(
				'type' => 'number',
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_interval', 60 ),
				'label' => __( 'Sending Interval.', 'front-end-pm' ),
				'description' => __( 'Announcement sending Interval in minutes.', 'front-end-pm' )
				);
			$fields['eb_announcement_email_per_interval'] =   array(
				'type' => 'number',
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_email_per_interval', 100 ),
				'label' => __( 'Emails send per interval.', 'front-end-pm' ),
				'description' => __( 'Announcement emails send per interval.', 'front-end-pm' )
				);
			$fields['eb_announcement_subject'] =   array(
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_subject', ''),
				'label' => __( 'Announcement subject.', 'front-end-pm' )
				);
			$fields['eb_announcement_content'] =   array(
				'type' => 'teeny',
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_content', ''),
				'description' => implode( '<br />', $this->email_legends( 'announcement' ) ),
				'label' => __( 'Announcement content.', 'front-end-pm' )
				);
				
			unset($fields['ann_to']);
								
			return $fields;
			
		}

	function filter_before_email_send( $content, $post, $user_email ){
		
		if( 'fep_announcement' == $post->post_type ) {	
			$legends = $this->email_legends( 'announcement', $post, 'replace_with', $user_email );	
			$content['subject'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_announcement_subject', '') );
			$content['message'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_announcement_content', '') );
		} elseif( $post->post_parent ){
			$legends = $this->email_legends( 'reply', $post, 'replace_with', $user_email );
			$content['subject'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_reply_subject', '') );
			$content['message'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_reply_content', '') );
		} else {
			$legends = $this->email_legends( 'newmessage', $post, 'replace_with', $user_email );
			$content['subject'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_newmessage_subject', '') );
			$content['message'] = str_replace( array_keys($legends), $legends, fep_get_option('eb_newmessage_content', '') );
		}
		return $content;
	}
	
	function filter_before_announcement_email_send( $content, $post, $user_emails ){
		
		$queue = get_option( 'fep_announcement_email_queue' );
		
		if( ! is_array( $queue ) ) {
			$queue = array();
		}
	
		$queue['id_'. $post->ID] = $user_emails;
		
		update_option( 'fep_announcement_email_queue', $queue, 'no' );
		update_post_meta( $post->ID, '_fep_email_sent', time() );
		
		return array(); //this will prevent from email sending
	}

	function ann_email_interval_event_callback(){
	
		$queue = get_option( 'fep_announcement_email_queue' );
		$per_interval = fep_get_option('eb_announcement_email_per_interval', 100 );
		$count = 0;
		
		if( ! $queue || ! is_array( $queue ) )
			return false;
		
		fep_add_email_filters( 'announcement' );
			
		foreach( $queue as $k => $v ) {
			if( ! $v || ! is_array( $v ) ) {
				unset( $queue[$k] );
				continue;
			}
			$id = str_replace( 'id_', '', $k );
			
			if( ! $id || ! is_numeric( $id ) ) {
				unset( $queue[$k] );
				continue;
			}
			
			$post = get_post( $id );
			
			if( ! $post || 'fep_announcement' != $post->post_type) {
				unset( $queue[$k] );
				continue;
			}
			
			foreach( $v as $x => $y ) {
				if( absint($per_interval) <= $count )
					break 2;
				
				$content = $this->filter_before_email_send( array(), $post, $y );
				
				if( wp_mail( $y, $content['subject'], $content['message'] ) ) {
					unset( $queue[$k][$x] );
					$count++;
				}
			}
		}
		
		fep_remove_email_filters( 'announcement' );
		
		update_option( 'fep_announcement_email_queue', $queue, 'no' );
			
	}
  } //END CLASS

add_action('init', array(Fep_Email_Beautify::init(), 'actions_filters'));

