<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Fep_Pro_Features {

	private static $instance;
	
	private function __construct() {

		$this->constants();
		$this->includes();
		$this->actions();
		$this->filters();

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
    	}
	
	private function includes()
    	{
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-pro-to.php' );
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-email-beautify.php' );
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-email-piping.php' );
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-read-receipt.php' );
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-role-to-role-block.php' );
			require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-group-message.php' );
			
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				require( FEP_PLUGIN_DIR . 'pro/includes/class-fep-pro-ajax.php' );
			}
    	}
	
	private function actions()
    	{
			add_action( 'init', array($this, 'pro_updater' ) );
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts' ) );
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts' ) );
			
			add_action( 'admin_init', array($this, 'update' ) );
			add_action( 'fep_pro_plugin_update', array($this, 'pro_update' ) );
    	}
		
    function filters()
    	{
    	}
	
	function pro_updater() {
	
		if( !is_admin() ) {
			return;
		}
		if( !class_exists( 'Fep_License_Handler' ) ) {
			// load our custom update handler
			include( 'updater/class-fep-license-handler.php' );
		}
	
		// setup our custom update handler
		$updater = new Fep_License_Handler( FEP_PLUGIN_FILE, 'Front End PM PRO', FEP_PLUGIN_VERSION );
	
	}
	
	function enqueue_scripts()
    {
		//Next version tokeninput will be removed from here. It is already included into free version
		//Please use wp_enqueue_script( 'fep-tokeninput-script'); and wp_enqueue_style( 'fep-tokeninput-style'); respectively
		wp_register_script( 'fep-mr-script', plugins_url( '/assets/js/jquery.tokeninput.js', __FILE__ ), array( 'jquery' ), '1.1', true );
	
		if( isset( $_GET['fepaction'] ) && 'newmessage' == $_GET['fepaction'] ) {
			wp_register_style( 'fep-mr-style', plugins_url( '/assets/css/token-input-facebook.css', __FILE__ ) );
		}
    }
	
	function admin_enqueue_scripts()
    {
		wp_register_script( 'fep-oa-script', plugins_url( '/assets/js/oa-script.js', __FILE__ ), array( 'jquery' ), '5.2', true );
		
		wp_register_style( 'fep-tokeninput-style', FEP_PLUGIN_URL . 'assets/css/token-input-facebook.css' );
		wp_register_script( 'fep-tokeninput-script', FEP_PLUGIN_URL . 'assets/js/jquery.tokeninput.js', array( 'jquery' ), '6.1', true );
    }
	
	function update(){
	
		$prev_ver = fep_get_option( 'plugin_pro_version', '4.4' );
		
		if( version_compare( $prev_ver, FEP_PLUGIN_VERSION, '<' ) ) {
			
			do_action( 'fep_pro_plugin_update', $prev_ver );
			
			fep_update_option( 'plugin_pro_version', FEP_PLUGIN_VERSION );
		}
	
	}
	
	function pro_update( $prev_ver ){
	
		if( version_compare( $prev_ver, '7.2', '<' ) ){
			delete_option('api_calls');
		}
	}
	
} //End Class

add_action('plugins_loaded', array( 'Fep_Pro_Features', 'init' ), 20 );

