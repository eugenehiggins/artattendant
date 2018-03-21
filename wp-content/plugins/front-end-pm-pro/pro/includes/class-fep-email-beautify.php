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
			add_action( 'fep_pro_plugin_update', array($this, 'schedule_event' ) );
			add_action( 'fep_action_after_admin_options_save', array($this, 'reschedule_event_on_save' ) );
			add_filter( 'fep_admin_settings_tabs', array($this, 'admin_settings_tabs' ) );
			add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );
			add_filter( 'fep_filter_before_email_send', array($this, 'filter_before_email_send' ), 10, 3 );
			add_action( 'fep_action_before_announcement_email_send', array($this, 'action_before_announcement_email_send' ), 10, 3 );
			add_action( 'fep_eb_ann_email_interval_event', array($this, 'ann_email_interval_event_callback' ) );
			
			add_filter('manage_fep_announcement_posts_columns', array($this, 'announcement_columns_head'));
			add_action('manage_fep_announcement_posts_custom_column', array($this, 'announcement_columns_content'), 10, 2);
    	}
	
	function email_beautify_activate(){
	
		if(  false !== fep_get_option( 'plugin_pro_version', false )  )
			return;
			
		$options = array();
	
		$options['eb_newmessage_subject'] = '{{site_title}} - New message';
		$options['eb_newmessage_content'] = '<p>Hi {{receiver}},<br />You have received a new message in {{site_title}}.<br />Subject: {{subject}}<br />Message: {{message}}<br />Message URL: <a href="{{message_url}}">{{message_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		$options['eb_reply_subject'] = '{{site_title}} - New reply';
		$options['eb_reply_content'] = '<p>Hi {{receiver}},<br />You have received a new reply of your message in {{site_title}}.<br />Subject: {{subject}}<br />Message: {{message}}<br />Message URL: <a href="{{message_url}}">{{message_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		$options['eb_announcement_subject'] = '{{site_title}} - Announcement';
		$options['eb_announcement_content'] = '<p>Hi {{receiver}},<br />A new announcement is published in {{site_title}}.<br />Title: {{subject}}<br />Announcement: {{message}}<br />Announcement URL: <a href="{{announcement_url}}">{{announcement_url}}</a><br /><a href="{{site_url}}">{{site_title}}</a></p>';
		$options['email_content_type'] = 'html';
			
		fep_update_option( $options );
		fep_eb_reschedule_event();
		
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
	
	function schedule_event( $prev_ver ){
		if ( version_compare( $prev_ver, '6.2', '<' ) && ! wp_next_scheduled ( 'fep_eb_ann_email_interval_event' ) ) {
			fep_eb_reschedule_event();
		}
	}

	function reschedule_event_on_save( $old_value ){
		if( !isset( $old_value['eb_announcement_interval'] ) )
		$old_value['eb_announcement_interval'] = 60;
		
		if( fep_get_option('eb_announcement_interval', 60 ) != $old_value['eb_announcement_interval'] ) {
			fep_eb_reschedule_event();
		}
	}
	
	function email_legends( $where = 'newmessage', $post = '', $value = 'description', $user_email = '' ){
		
		$autop = false;
		if( 'html' == fep_get_option( 'email_content_type', 'plain_text' ) && apply_filters( 'fep_email_wpautop', true ) ) {
			$autop = true;
		}
		$content = ! empty( $post->post_content ) ? $post->post_content : '';
		
		$legends = array(
			'subject' => array(
				'description' => __('Subject', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_title ) ? $post->post_title : ''
				),
			'message' => array(
				'description' => __('Full Message', 'front-end-pm'),
				'replace_with' => $autop ? wpautop( $content ) : $content
				),
			'message_url' => array(
				'description' => __('URL of message', 'front-end-pm'),
				'where' => array( 'newmessage', 'reply' ),
				'replace_with' => ! empty( $post->ID ) ? fep_query_url_raw( 'viewmessage', array( 'fep_id' => $post->ID ) ) : ''
				),
			'announcement_url' => array(
				'description' => __('URL of announcement', 'front-end-pm'),
				'where' => 'announcement',
				'replace_with' => ! empty( $post->ID ) ? fep_query_url_raw( 'viewannouncement', array( 'fep_id' => $post->ID ) ) : ''
				),
			'sender' => array(
				'description' => __('Sender Name', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_author ) ? fep_user_name( $post->post_author ) : ''
				),
			'receiver' => array(
				'description' => __('Receiver Name', 'front-end-pm'),
				'replace_with' => fep_user_name( fep_get_userdata( $user_email, 'ID', 'email' ) )
				),
			'site_title' => array(
				'description' => __('Website title', 'front-end-pm'),
				'replace_with' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
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
			$templates = array(
				'default'	=> __( 'Default', 'front-end-pm' ),
				);
			
			$fields['eb_newmessage_template'] =   array(
				'section'	=> 'eb_newmessage',
				'value' => fep_get_option('eb_newmessage_template', 'default'),
				'label' => __( 'New message email template', 'front-end-pm' ),
				'type'	=>	'select',
				//'description' => __( 'Admin alwayes have Wp Editor.', 'front-end-pm' ),
				'options'	=> apply_filters( 'fep_eb_templates', $templates, 'newmessage' ),
				);
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
			$fields['eb_newmessage_attachment'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'eb_newmessage',
				'value' => fep_get_option('eb_newmessage_attachment', 0 ),
				'label' => __( 'Send Attachments', 'front-end-pm' ),
				'cb_label' => __( 'Send attachments with new message email?', 'front-end-pm' )
				);
			$fields['eb_reply_template'] =   array(
				'section'	=> 'eb_reply',
				'value' => fep_get_option('eb_reply_template', 'default'),
				'label' => __( 'Reply message email template', 'front-end-pm' ),
				'type'	=>	'select',
				//'description' => __( 'Admin alwayes have Wp Editor.', 'front-end-pm' ),
				'options'	=> apply_filters( 'fep_eb_templates', $templates, 'reply' ),
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
			$fields['eb_reply_attachment'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'eb_reply',
				'value' => fep_get_option('eb_reply_attachment', 0 ),
				'label' => __( 'Send Attachments', 'front-end-pm' ),
				'cb_label' => __( 'Send attachments with reply message email?', 'front-end-pm' )
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
			$fields['eb_announcement_template'] =   array(
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_template', 'default'),
				'label' => __( 'Announcement email template', 'front-end-pm' ),
				'type'	=>	'select',
				//'description' => __( 'Admin alwayes have Wp Editor.', 'front-end-pm' ),
				'options'	=> apply_filters( 'fep_eb_templates', $templates, 'announcement' ),
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
			$fields['eb_announcement_attachment'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'eb_announcement',
				'value' => fep_get_option('eb_announcement_attachment', 0 ),
				'label' => __( 'Send Attachments', 'front-end-pm' ),
				'cb_label' => __( 'Send attachments with announcement email?', 'front-end-pm' )
				);
				
			unset($fields['ann_to']);
								
			return $fields;
			
		}

	function filter_before_email_send( $content, $post, $user_email ){
		
		$autop = false;
		$html = ( 'html' == fep_get_option( 'email_content_type', 'plain_text' ) ) ? true : false;
		
		if( $html && apply_filters( 'fep_email_wpautop', true ) ) {
			$autop = true;
		}
		
		if( 'fep_announcement' == $post->post_type ) {	
			$legends = $this->email_legends( 'announcement', $post, 'replace_with', $user_email );
			$subject = stripslashes( fep_get_option('eb_announcement_subject', '') );
			$message = stripslashes( fep_get_option('eb_announcement_content', '') );
			
			if( $autop ){
				$message = wpautop( $message );
			}
			$content['subject'] = str_replace( array_keys($legends), $legends, $subject );
			
			$template_slug = fep_get_option('eb_announcement_template', 'default');
			$template_name = "emails/{$template_slug}.php";
			
			if( $template_slug && has_filter( "fep_filter_announcement_email_template_{$template_slug}") ){
				$message = apply_filters( "fep_filter_announcement_email_template_{$template_slug}", $message, $post, $user_email );
				
			} elseif( $template_slug && $html && $template = fep_locate_template( $template_name ) ){
				ob_start();
				require( $template );
				$body = ob_get_clean();
				
				$message = str_replace( '{FEP-EMAIL-CONTENT}', $message, $body );
			}
			$content['message'] = str_replace( array_keys($legends), $legends, $message );
			
			if( fep_get_option('eb_announcement_attachment', 0 ) && $attachment_ids = fep_get_attachments( $post->ID, 'ids' ) ){
				foreach( $attachment_ids as $attachment_id ){
					if( $file = get_attached_file( $attachment_id ) ){
						$content['attachments'][] = $file;
					}
				}
			}
		} elseif( $post->post_parent ){
			$legends = $this->email_legends( 'reply', $post, 'replace_with', $user_email );
			$subject = stripslashes( fep_get_option('eb_reply_subject', '') );
			$message = stripslashes( fep_get_option('eb_reply_content', '') );
			
			if( $autop ){
				$message = wpautop( $message );
			}
			$content['subject'] = str_replace( array_keys($legends), $legends, $subject );
			
			$template_slug = fep_get_option('eb_reply_template', 'default');
			$template_name = "emails/{$template_slug}.php";
			
			if( $template_slug && has_filter( "fep_filter_reply_email_template_{$template_slug}") ){
				$message = apply_filters( "fep_filter_reply_email_template_{$template_slug}", $message, $post, $user_email );
				
			} elseif( $template_slug && $html && $template = fep_locate_template( $template_name ) ){
				ob_start();
				require( $template );
				$body = ob_get_clean();
				
				$message = str_replace( '{FEP-EMAIL-CONTENT}', $message, $body );
			}
			$content['message'] = str_replace( array_keys($legends), $legends, $message );
			
			if( fep_get_option('eb_reply_attachment', 0 ) && $attachment_ids = fep_get_attachments( $post->ID, 'ids' ) ){
				foreach( $attachment_ids as $attachment_id ){
					if( $file = get_attached_file( $attachment_id ) ){
						$content['attachments'][] = $file;
					}
				}
			}
			
		} else {
			$legends = $this->email_legends( 'newmessage', $post, 'replace_with', $user_email );
			
			$subject = stripslashes( fep_get_option('eb_newmessage_subject', '') );
			$message = stripslashes( fep_get_option('eb_newmessage_content', '') );
			
			if( $autop ){
				$message = wpautop( $message );
			}
			$content['subject'] = str_replace( array_keys($legends), $legends, $subject );
			
			$template_slug = fep_get_option('eb_newmessage_template', 'default');
			$template_name = "emails/{$template_slug}.php";
			
			if( $template_slug && has_filter( "fep_filter_newmessage_email_template_{$template_slug}") ){
				$message = apply_filters( "fep_filter_newmessage_email_template_{$template_slug}", $message, $post, $user_email );
				
			} elseif( $template_slug && $html && $template = fep_locate_template( $template_name ) ){
				ob_start();
				require( $template );
				$body = ob_get_clean();
				
				$message = str_replace( '{FEP-EMAIL-CONTENT}', $message, $body );
			}
			$content['message'] = str_replace( array_keys($legends), $legends, $message );
			
			if( fep_get_option('eb_newmessage_attachment', 0 ) && $attachment_ids = fep_get_attachments( $post->ID, 'ids' ) ){
				foreach( $attachment_ids as $attachment_id ){
					if( $file = get_attached_file( $attachment_id ) ){
						$content['attachments'][] = $file;
					}
				}
			}
		}

		return $content;
	}
	
	function action_before_announcement_email_send( $content, $post, $user_emails ){
		
		if( get_post_meta( $post->ID, '_fep_email_sent', true ) || empty( $user_emails ) )
			return false;
		
		$queue = get_option( 'fep_announcement_email_queue' );
		
		if( ! is_array( $queue ) ) {
			$queue = array();
		}
	
		$queue['id_'. $post->ID] = $user_emails;
		
		update_option( 'fep_announcement_email_queue', $queue, 'no' );
		update_post_meta( $post->ID, '_fep_email_sent', time() );
		update_post_meta( $post->ID, '_fep_announcement_total_users', count( $user_emails ) );
		
		add_filter( "fep_announcement_email_send_{$post->ID}", '__return_false' ); //this will prevent from email sending
		
		return true;
	}

	function ann_email_interval_event_callback(){
	
		$queue = get_option( 'fep_announcement_email_queue' );
		$per_interval = fep_get_option('eb_announcement_email_per_interval', 100 );
		$count = 0;
		
		if( ! $queue || ! is_array( $queue ) )
			return false;
		
		fep_add_email_filters( 'announcement' );
		if( 'html' == fep_get_option( 'email_content_type', 'plain_text' ) ) {
			$content_type = 'text/html';
		} else {
			$content_type = 'text/plain';
		}
		$empty_content = array(
			'subject' => '',
			'message' => '',
			'attachments' => array()
			);
		$headers = array();
		$headers['from'] = 'From: '.stripslashes( fep_get_option('from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ) ).' <'. fep_get_option('from_email', get_bloginfo('admin_email')) .'>';
		$headers['content_type'] = "Content-Type: $content_type";
			
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
			
			if( ! $post || 'fep_announcement' != $post->post_type || 'publish' != $post->post_status ) {
				unset( $queue[$k] );
				continue;
			}
			
			foreach( $v as $x => $y ) {
				if( absint($per_interval) <= $count )
					break 2;
				
				$content = $this->filter_before_email_send( $empty_content, $post, $y );
				$content['headers'] = $headers;
				
				$content = apply_filters( 'fep_filter_before_announcement_email_send', $content, $post, array( $y ) );
		
				if( empty( $content['subject'] ) || empty( $content['message'] ) || ! fep_get_user_option( 'allow_ann', 1, fep_get_userdata( $y, 'ID', 'email' ) ) ){
					unset( $queue[$k][$x] );
					continue;
				}
				
				
				if( wp_mail( $y, $content['subject'], $content['message'], $content['headers'], $content['attachments'] ) || apply_filters( 'fep_eb_announcement_email_return_check_bypass', false ) ) {
					unset( $queue[$k][$x] );
					$count++;
				}
				if( $count && ( $count % 10 == 0 ) ){
					//Save in every 10th emails for timeout issue
					update_option( 'fep_announcement_email_queue', $queue, 'no' );
				}
			}
		}
		
		fep_remove_email_filters( 'announcement' );
		
		update_option( 'fep_announcement_email_queue', $queue, 'no' );
			
	}
	
	function announcement_columns_head($defaults) {
		$defaults['email_sent'] = __('Email Sent', 'front-end-pm');
		return $defaults;
	}

	function announcement_columns_content($column_name, $post_ID) {
		
		if ($column_name == 'email_sent') {
		   $total = absint( get_post_meta( $post_ID, '_fep_announcement_total_users', true ) );
		
			if( !$total ){
				$roles = fep_get_participant_roles( $post_ID );
		
				if( $roles ) {
					$args = array( 
						'role__in' => $roles,
						'fields' => array( 'ID', 'user_email' ),
						'orderby' => 'ID' 
					);
					$usersarray = get_users( $args );
					$user_emails = array();
					foreach  ($usersarray as $user) {
						$notify = fep_get_user_option( 'allow_ann', 1, $user->ID);
						
						if ($notify == '1'){
							$user_emails[] = $user->user_email;
						}
					}
					$total = count( $user_emails );
					update_post_meta( $post_ID, '_fep_announcement_total_users', $total );
				}

			}
			
			$queue = get_option( 'fep_announcement_email_queue' );
			
			if( is_array( $queue ) && ! empty( $queue['id_'. $post_ID] ) ) {
				$email_sent = $total - count( $queue['id_'. $post_ID] );
			} else {
				$email_sent = $total;
			}
			
			if( $email_sent < 0 ) {
				$email_sent = $total;
			}
			
			echo "{$email_sent} / {$total}";
		}
		
	}
  } //END CLASS

add_action('init', array(Fep_Email_Beautify::init(), 'actions_filters'));

