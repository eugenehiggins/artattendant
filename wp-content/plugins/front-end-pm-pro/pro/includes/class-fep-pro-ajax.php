<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Pro_Ajax
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
			add_action('wp_ajax_fep_mr_ajax', array($this, 'mr_ajax_callback' ) );
    	}

	function mr_ajax_callback() {
		global $user_ID;
		
		if ( check_ajax_referer( 'fep-mr-script', 'token', false )) {
		
		$searchq = $_POST['q'];
		$already = empty( $_POST['x'] ) ? array() : explode( ',', $_POST['x']);
		$already[] = $user_ID;
		
		
		$args = array(
			'search' => "*{$searchq}*",
			'search_columns' => array( 'display_name' ),
			'exclude' => $already,
			'number' => 10,
			'orderby' => 'display_name',
			'order' => 'ASC',
			'fields' => array( 'ID', 'display_name' )
		);
			
		$args = apply_filters ('fep_autosuggestion_arguments', $args );
		
		// The Query
		$user_query = new WP_User_Query( $args );
		
		$ret = array();
			
		if(strlen($searchq)>0)
		{
			if (! empty( $user_query->results ))
			{
				foreach($user_query->results as $user)
				{
					$ret[] = array(
							'id' => $user->ID,
							'name' => $user->display_name
						);
				}
			}
		}
		
		wp_send_json($ret);
		}
		die();
	}
  } //END CLASS

add_action('init', array(Fep_Pro_Ajax::init(), 'actions_filters'));

