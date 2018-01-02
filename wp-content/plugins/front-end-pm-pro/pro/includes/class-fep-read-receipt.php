<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Read_Receipt
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
			add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );
			
			if( fep_get_option('read_receipt', 1 ) ) {
				//add_action ('fep_display_after_parent_message', array($this, 'display_read_receipt'), 99 );
				//add_action ('fep_display_after_reply_message', array($this, 'display_read_receipt'), 99 );
				add_action ('fep_display_after_message', array($this, 'display_read_receipt'), 99 );
			}
			
    	}
	
	function settings_fields( $fields )
		{
			$fields['read_receipt'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'mr_multiple_recipients',
				'value' => fep_get_option('read_receipt', 1 ),
				'label' => __( 'Read Receipt', 'front-end-pm' ),
				'cb_label' => __( 'Show read receipt bottom of every message?', 'front-end-pm' )
				);
								
			return $fields;
			
		}
	
	function display_read_receipt(){
		
		$read_by = get_post_meta( get_the_ID(), '_fep_read_by', true );
		
		if( ! is_array( $read_by ) )
			return '';
		
		$receipt = array();
		  foreach( $read_by as $time => $user_id ) {
		  	if( get_the_author_meta('ID') == $user_id )
				continue;
			
			//date_i18 creates problem converting form gmt
			//$receipt[] = sprintf(__('Read by %s &#x40; %s', 'front-end-pm' ), fep_get_userdata( $user_id, 'display_name', 'id' ), date_i18n( get_option( 'date_format' ). ' '.get_option( 'time_format' ), $time, true ));
			
			$receipt[] = sprintf(__('Read by %s &#x40; %s', 'front-end-pm' ), fep_get_userdata( $user_id, 'display_name', 'id' ), get_date_from_gmt( date( 'Y-m-d H:i:s', $time ), get_option('date_format') . ' '. get_option('time_format') ));
		  }
		if( $receipt ) {
			echo '<hr />' . implode( '<br />', $receipt );
		}
		
	}
		
	

  } //END CLASS

add_action('init', array(Fep_Read_Receipt::init(), 'actions_filters'));

