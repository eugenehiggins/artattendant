<?php
/*
Plugin Name: EDD FES - Image Uploader Field
Plugin URI: http://anagr.am/image-uploader
Description: Adds an image upload field to FES
Version: 2.2.1
Author: Geet Jacobs
Author URI:  http://anagr.am
Contributors: Jeradin
Text Domain: edd-image-uploader
Domain Path: languages
*/

class EDD_image_uploader {

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
			self::$instance = new EDD_image_uploader();

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

		define( 'EDD_image_uploader_VERSION', '2.2.1' );

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
		//add_action( 'init', array( $this, 'textdomain' ) );



		if( $this->is_fes ) {

			/**
			 * Frontend Submissions actions
			 */

				add_action( 'fes_load_fields_require',  array( $this, 'edd_fes_image_uploader' ) );


				add_action( 'wp_ajax_handle_dropped_media',  array( $this,'upload_dropped_media') );
				// if you want to allow your visitors of your website to upload files, be cautious.
				add_action( 'wp_ajax_nopriv_handle_dropped_media',  array( $this,'upload_dropped_media') );


				add_action( 'wp_ajax_handle_delete_media',  array( $this,'delete_media') );
				// if you want to allow your visitors of your website to upload files, be cautious.
				add_action( 'wp_ajax_nopriv_handle_delete_media',  array( $this,'delete_media') );


				add_action( 'wp_enqueue_scripts', array( $this, 'edd_fes_image_uploader_scripts') ); // Register this fxn and allow Wordpress to call it automatcally in the header


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
		$lang_dir = apply_filters( 'edd_image_uploader_lang_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'edd-image-uploader', false, $lang_dir );

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


	function edd_fes_image_uploader(){
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			require_once dirname( __FILE__ ) . '/image-upload-field.php';
			add_filter(  'fes_load_fields_array', 'edd_fes_image_uploader_add_field', 10, 1 );
			function edd_fes_image_uploader_add_field( $fields ){
				$fields['edd_image_uploader'] = 'FES_image_uploader_Field';
				return $fields;
			}
		}
	}

		function edd_fes_image_uploader_scripts()  {

			//mapi_var_dump($form_id);

			$task       = ! empty( $_GET['task'] ) ? $_GET['task'] : '';

			//wp_enqueue_style( 'anagram-dropzonecss', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.css' );
			//wp_enqueue_script('load-imgjs',  plugins_url('scripts/load-img.all-min.js', __FILE__) );
			if ( is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) && ( $task == 'new-product' || $task == 'edit-product' ) ) {

					wp_enqueue_script('dropzonejs',  plugins_url('scripts/dropzone.js', __FILE__) );
					wp_enqueue_script('my-dropzonejs', plugins_url('scripts/mydrop.js', __FILE__),array('jquery','dropzonejs'),'4.6', true);

					$drop_param = array(
					  'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					  'nonce' => wp_create_nonce( 'handle_dropped_media-nonce' ),
					  //'upload_image'=>admin_url( 'admin-post.php?action=handle_dropped_media' ),
					  //'delete_image'=>admin_url( 'admin-post.php?action=handle_delete_media' ),
					);
					wp_localize_script('my-dropzonejs','dropParam', $drop_param);

			}

		}



	function upload_dropped_media() {
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

	           // $newupload_id = $this->upload_user_file( $newfile  );

	            $_FILES = array('upload'=>$newfile);
	            foreach($_FILES as $file => $array) {


	                $newupload_id = media_handle_upload( $file, 0 );
	            }
	        }
	    }

		if($newupload_id){
				    //echo $newupload_id;
		    wp_send_json_success(array(
				'media_id' => $newupload_id,
				'image_url' => wp_get_attachment_thumb_url( $newupload_id ),
			));
		  //  die();

		}else{
			 wp_send_json_error(array(
				'message' => $newupload_id
			));

		}

	}



	function delete_media(){

	    if( isset($_POST['media_id']) ){
	        $post_id = absint( $_POST['media_id'] );

	        $status = wp_delete_attachment($post_id, true);

	        if( $status )
	         wp_send_json_success(array(
				'status' => 'OK',
				'deleted_id' => $post_id,
			));
	           // echo json_encode(array('status' => 'OK', 'deleted_id' => $post_id ));
	        else
	         wp_send_json_error(array(
				'status' => 'FAILED'
			));
	            //echo json_encode(array('status' => 'FAILED'));
	    }

	    die();
	}




}

/*
		function upload_user_file( $file = array() ) {

			require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		      $file_return = wp_handle_upload( $file, array('test_form' => false ) );

		      if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		          return false;
		      } else {

		          $filename = $file_return['file'];

		          $attachment = array(
		              'post_mime_type' => $file_return['type'],
		              'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
		              'post_content' => '',
		              'post_status' => 'inherit',
		              'guid' => $file_return['url']
		          );

		          $attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );

		          require_once(ABSPATH . 'wp-admin/includes/image.php');
		          $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		          wp_update_attachment_metadata( $attachment_id, $attachment_data );

		          if( 0 < intval( $attachment_id ) ) {
		          	return $attachment_id;
		          }
		      }

		      return false;
		}
*/




/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function edd_image_uploader_load() {
	$edd_image_uploader = new EDD_image_uploader();
}
add_action( 'plugins_loaded', 'edd_image_uploader_load', 0 );
