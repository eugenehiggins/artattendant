<?php
/*
Plugin Name: EDD FES - User Location Field
Plugin URI: http://anagr.am/location-field
Description: Adds user location field
Version: 2.2.1
Author: Geet Jacobs
Author URI:  http://anagr.am
Contributors: Jeradin
Text Domain: edd-fes-location
Domain Path: languages
*/

class EDD_location_field {

	private static $instance;

	/**
	 * Flag for whether Frontend Submissions is enabled
	 *
	 * @since 2.0
	 *
	 * @access protected
	 */
	protected $is_fes = false;

	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new EDD_location_field();

		return self::$instance;
	}

	/**
	 * Class constructor.  Includes constants, includes and init method.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		define( 'EDD_location_field_VERSION', '2.2.1' );

		$this->init();

	}


	/**
	 * Run action and filter hooks.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return void
	 */
	protected function init() {

		if( ! class_exists( 'Easy_Digital_Downloads' ) )
			return; // EDD not present

		global $edd_options;

		// Check for dependent plugins
		$this->plugins_check();

		// internationalization
		add_action( 'init', array( $this, 'textdomain' ) );



		if( $this->is_fes ) {

			/**
			 * Frontend Submissions actions
			 */

				add_action( 'fes_load_fields_require',  array( $this, 'edd_fes_location_field' ) );



				//add_action( 'admin_post_handle_dropped_media',  array( $this,'handle_dropped_media') );

				// if you want to allow your visitors of your website to upload files, be cautious.
				//add_action( 'admin_post_nopriv_handle_dropped_media',  array( $this,'handle_dropped_media') );

				//add_action( 'admin_post_handle_delete_media',  array( $this,'handle_delete_media') );

				// if you want to allow your visitors of your website to upload files, be cautious.
				//add_action( 'admin_post_nopriv_handle_delete_media',  array( $this,'handle_delete_media') );

				//add_action( 'admin_post_handle_delete_media',  array( $this,'handle_delete_media') );


				//add_action( 'wp_enqueue_scripts', array( $this, 'edd_fes_location_field_scripts') ); // Register this fxn and allow Wordpress to call it automatcally in the header


		}

	}


	/**
	 * Load plugin text domain
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public static function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'edd_artwork_location_lang_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'location-field', false, $lang_dir );

	}

	/**
	 * Determine if dependent plugins are loaded and set flags appropriately
	 *
	 * @since 2.0
	 *
	 * @access private
	 * @return void
	 */
	public function plugins_check() {

		if( class_exists( 'EDD_Front_End_Submissions' ) ) {
			$this->is_fes = true;
		}

	}


	function edd_fes_location_field(){
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			require_once dirname( __FILE__ ) . '/location-field.php';
			add_filter(  'fes_load_fields_array', 'edd_fes_location_field_add_field', 10, 1 );
			function edd_fes_location_field_add_field( $fields ){
				$fields['edd_artwork_location'] = 'EDD_fes_location_field';
				return $fields;
			}
		}
	}

		function edd_fes_location_field_scripts()  {


			//wp_enqueue_style( 'anagram-dropzonecss', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.css' );
/*

			wp_enqueue_script('dropzonejs',  plugins_url('scripts/dropzone.js', __FILE__) );
			wp_enqueue_script('my-dropzonejs', plugins_url('scripts/mydrop.js', __FILE__),array('jquery','dropzonejs'));

			$drop_param = array(
			  'upload_image'=>admin_url( 'admin-post.php?action=handle_dropped_media' ),
			  'delete_image'=>admin_url( 'admin-post.php?action=handle_delete_media' ),
			);
			wp_localize_script('my-dropzonejs','dropParam', $drop_param);
*/



		}


	function handle_dropped_media() {
	    status_header(200);

	    $upload_dir = wp_upload_dir();
	    $upload_path = $upload_dir['path'] . DIRECTORY_SEPARATOR;
	    $num_files = count($_FILES['file']['tmp_name']);

	    $newupload = 0;

	    if ( !empty($_FILES) ) {
	        $files = $_FILES;
	        foreach($files as $file) {
	            $newfile = array (
	                    'name' => $file['name'],
	                    'type' => $file['type'],
	                    'tmp_name' => $file['tmp_name'],
	                    'error' => $file['error'],
	                    'size' => $file['size']
	            );

	            $_FILES = array('upload'=>$newfile);
	            foreach($_FILES as $file => $array) {
	                $newupload = media_handle_upload( $file, 0 );
	            }
	        }
	    }

	    echo $newupload;
	    die();
	}



	function handle_delete_media(){

	    if( isset($_REQUEST['media_id']) ){
	        $post_id = absint( $_REQUEST['media_id'] );

	        $status = wp_delete_attachment($post_id, true);

	        if( $status )
	            echo json_encode(array('status' => 'OK', 'deleted_id' => $post_id ));
	        else
	            echo json_encode(array('status' => 'FAILED'));
	    }

	    die();
	}




}


/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function edd_artwork_location_load() {
	$edd_location_field = new EDD_location_field();
}
add_action( 'plugins_loaded', 'edd_artwork_location_load', 0 );
