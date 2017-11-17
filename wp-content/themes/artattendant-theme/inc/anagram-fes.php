<?php


/*-----------------------------------------------------------------------------------*/
/* Enqueue Styles and Scripts
/*-----------------------------------------------------------------------------------*/

function anagram_uploader()  {

	wp_enqueue_style( 'anagram-bootstrap-table-css', get_template_directory_uri().'/js/bootstrap-table.min.css' );

	wp_enqueue_script('bootstrap-table', get_template_directory_uri()."/js/bootstrap-table.min.js");
	wp_enqueue_script('bootstrap-table-ext', get_template_directory_uri()."/js/bootstrap-table-ext.min.js", array(), filemtime( get_stylesheet_directory().'/js/bootstrap-table-ext.min.js') );

	wp_enqueue_script('select2', get_template_directory_uri()."/js/select2.min.js");
	wp_enqueue_style( 'select-css', get_template_directory_uri().'/js/select2.min.css' );



}
add_action( 'wp_enqueue_scripts', 'anagram_uploader' ); // Register this fxn and allow Wordpress to call it automatcally in the header



/* Removing private prefix from post titles */


function spi_remove_private_protected_from_titles( $format ) {
	return '"%s"';
}
add_filter( 'private_title_format',   'spi_remove_private_protected_from_titles' );






add_action( 'wp_enqueue_scripts', 'anagarm_artwork_table' );
function anagarm_artwork_table() {

if ( !fes_is_frontend() ) {
			return;
		}
		if ( is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) ) {




		    wp_enqueue_script( 'artwork_table_script', get_template_directory_uri() . '/js/artwork-table.js', array( 'jquery' ), filemtime( get_stylesheet_directory().'/js/artwork-table.js') , true );
		    wp_enqueue_script('artwork-editor', get_template_directory_uri()."/js/artwork-editor.js", array(), filemtime( get_stylesheet_directory().'/js/artwork-editor.js'), true );
		    $options = array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'user_id'  => get_current_user_id(),
				'measurement_default'  =>  get_user_meta( get_current_user_id(), 'measurement_default', true ) ,
				'nonce' => wp_create_nonce( 'artwork_ajax_updater-nonce' ),
				//'auto_save' => true,
			);

			wp_localize_script( 'artwork_table_script', 'artwork_ajax_vars', $options );
		}
}

add_action('wp_ajax_anagram_table_artwork', 'anagram_artwork_ajax_updater');
add_action('wp_ajax_nopriv_anagram_table_artwork', 'anagram_artwork_ajax_updater');

function anagram_artwork_ajax_updater() {
	if($_POST['type'] == 'amount'){

		update_post_meta($_POST['id'], $_POST['field'], $_POST['value']);
		wp_send_json_success(array(
			'costTotal' => count_total_cost(),
			'priceTotal' => count_total_edd_price(),
		));


	}else if($_POST['type'] == 'meta'){

		update_post_meta($_POST['id'], $_POST['field'], $_POST['value']);
		wp_send_json_success(true);


	}else if($_POST['type'] == 'status'){
	  $my_post = array(
	      'ID'           => $_POST['id'],
	      'post_status'   => $_POST['value'],
	      //'post_content' => 'This is the updated content.',
	  );

	// Update the post into the database
	  wp_update_post( $my_post );
	wp_send_json_success(true);

	}else if($_POST['type'] == 'title'){
		  $my_post = array(
		      'ID'           => $_POST['id'],
		      'post_title'   => $_POST['value'],
		      //'post_content' => 'This is the updated content.',
		  );

	// Update the post into the database
	wp_update_post( $my_post );
	wp_send_json_success(true);

	}else if($_POST['type'] == 'artist'){
	  	wp_set_post_terms( $_POST['id'], $_POST['value'], 'artist');
		wp_send_json_success(true);

	}else if($_POST['type'] == 'invonumber'){

		$allinvo = anagram_get_highest_invo_number(false,'all');
		if(in_array( $_POST['value'], $allinvo ) ){
			wp_send_json_error('Inventory number already used');
		}

	  	update_post_meta($_POST['id'], $_POST['field'], $_POST['value']);
		wp_send_json_success(true);
	};

	wp_send_json_error();
}

/*  Get location form current users locations */
add_action( 'wp_ajax_anagram_ajax_location_search','anagram_ajax_location_search' );
add_action( 'wp_ajax_nopriv_anagram_ajax_location_search', 'anagram_ajax_location_search' );
function anagram_ajax_location_search() {
	//global $wpdb;
/*
	if ( isset( $_GET['tax'] ) ) {
		$taxonomy = sanitize_key( $_GET['tax'] );
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax ){
			wp_die( 0 );
		}
	} else {
		wp_die( 0 );
	}
*/

	if ( !EDD_FES()->vendors->user_is_vendor( get_current_user_id() ) ){
		wp_die( 0 );
	}

	$s = stripslashes( $_GET['q'] );
	$comma = _x( ',', 'tag delimiter' );
	if ( ',' !== $comma ){
		$s = str_replace( $comma, ',', $s );
	}
	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}
	$s = trim( $s );
	if ( strlen( $s ) < 2 ){
		wp_die(); // require 2 chars for matching
	}
/*
	$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . $wpdb->esc_like( $s ) . '%' ) );
	echo join( $results, "\n" );
	wp_die();
*/



	if ( empty( $user_id ) )
		$user_id = get_current_user_id();



	$args = array(
		'post_type' => 'download',
		'posts_per_page' => -1,
		'author' => $user_id,
		'fields' =>'ids',
		'post_status' => array( 'draft', 'pending', 'publish', 'private' ),
		'meta_query' => array(
	        array(
	           'key' => 'location',
	           'value' =>  $s,
	           'compare' => 'LIKE'
	        )
	     )

	);

	$locations = array();
	$posts = get_posts( $args );
   foreach ( $posts as $post ) {
		$locations[] = get_post_meta( $post, 'location', true );
	}


	if ( empty($locations) ) {
		wp_die();
	}


	$results = array_unique($locations);


	echo implode( "\n", $results );
	wp_die();


}



add_action( 'wp_ajax_get_member_location','get_member_location' );
add_action( 'wp_ajax_nopriv_get_member_location', 'get_member_location' );

function get_member_location( $user_id = false ) {

	if ( empty( $user_id ) )
		$user_id = get_current_user_id();


	$args = array(
		'post_type' => 'download',
		'posts_per_page' => -1,
		'author' => $user_id,
		'fields' =>'ids',
		'post_status' => array( 'draft', 'pending', 'publish', 'private' ),
		'meta_query' => array(
	        array(
	           'key' => 'location',
	           'value' =>  $s,
	           'compare' => 'LIKE'
	        )
	     )

	);


	$locations = array();
	$posts = get_posts( $args );
	foreach ( $posts as $post ) {
		$location = get_post_meta( $post, 'location', true );
		 $locations[] = array(
            'id' => $location,
            'text' => $location
        );
	}



    // bail if we don't have any results
    if ( empty( $locations ) ) {
        wp_send_json_error();
    }

	$results = array_unique($locations);

		wp_send_json_success( $results );




}







 /* Class extender */
//$c = new Your_Tribe_Image_Widget();
class Your_Tribe_Image_Widget extends FES_Dashboard
{
/*
	function __construct() {

		remove_action( 'template_redirect', array( 'FES_Dashboard', 'check_access' ) );
	}
*/
    public function check_access() {
	    $current_user = wp_get_current_user();

		$db_user = new FES_DB_Vendors();

		if ( !$db_user->exists( 'email', $current_user->user_email ) ) {
			$db_user->add( array(
					'user_id'        => $current_user->ID,
					'email'          => $current_user->user_email,
					'username'       => $current_user->user_login,
					'name'           => $current_user->display_name,
					'product_count'  => 0,
					'status'         => 'approved',
				) );
		}
	 // are they not a vendor yet: show registration page
/*
					$base_url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
					$base_url = add_query_arg( 'user_id', $user_id, $base_url );
					$base_url = add_query_arg( 'view', 'application-w0w', $base_url );
					wp_redirect( $base_url );
					exit;
*/

     // Do something else useful
   }
}







function anagram_product_list_date($date){
	 $date = substr($date, 0, strpos($date, '<br />'));


	return $date;
}

add_filter( 'fes_product_list_date','anagram_product_list_date', 20, 1  );


function anagram_product_list_status( $status ) {
		//$status = '<span class="download-status ' . EDD_FES()->dashboard->product_list_generate_status( $product_id, true ) . '">' . EDD_FES()->dashboard->product_list_generate_status( $product_id, false ) . '</span>';

		return $status;
	}
//add_filter( 'fes_product_list_status', 'anagram_product_list_status', 20, 1 );


function add_form_intro($output){
    		$post_id = isset( $_REQUEST['post_id'] ) && absint( $_REQUEST['post_id'] )  ? absint( $_REQUEST['post_id'] ) : -2;
    	$output ='';
    	// Render the FES Form
		if ( $post_id && $post_id !== -2 ) {
			$output .= '<h2 class="fes-headers" id="fes-edit-product-form-title" >' . EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) . __( ' Details for ', 'edd_fes' ).get_the_title($post_id).'</h2>';
		} else {
			$output .= '<h2 class="fes-headers" id="fes-new-product-form-title" >Add to your collection</h2>';
    //$output .='<div class="form-intro">Please add a description of the work you’d like to add</div>';
	};

    return $output;
}
add_filter('fes_submission_form_header', 'add_form_intro', 1, 1);





function sumobi_edd_fes_dashboard_menu( $menu_items ) {
	// $menu_items['home']['name'] 			= '';
	 $menu_items['my_products']['name'] 	= 'My Images';
	 $menu_items['new_product']['name'] 	= 'Add New Image';
	// $menu_items['earnings']['name'] 		= '';
	// $menu_items['profile']['name'] 		= '';
	// $menu_items['logout']['name'] 		= '';
	// $menu_items['my_products']['name'] 	= '';
	return $menu_items;
}
//add_filter( 'edd_fes_vendor_dashboard_menu', 'sumobi_edd_fes_dashboard_menu' );



//apply_filters( 'fes_get_the_download_status', 'custom_vendor_dashboard_status' );

if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
	function custom_vendor_dashboard_status() {


		$status = 'test';

		return $status;
	}
}



if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
	function custom_vendor_dashboard_menu() {
		$menu_items = array();

		// Dashboard tab
		$menu_items['home'] = array(
			"icon" => "home",
			"task" => array( 'dashboard', '' ),
			"name" => __( 'Dashboard', 'edd_fes' ),
		);

		// "Products" tab
		$menu_items['my_products'] = array(
			"icon" => "list",
			"task" => array( 'products' ),
			"name" => EDD_FES()->vendors->get_product_constant_name( $plural = true, $uppercase = true ),
		);

		// Add "Product" tab
		if ( EDD_FES()->vendors->vendor_can_create_product() ) {
			$menu_items['new_product'] = array(
				"icon" => "pencil",
				"task" => array( 'new-product' ),
				"name" => __( 'Add', 'edd_fes' ) . ' ' . EDD_FES()->vendors->get_product_constant_name( $plural = false, $uppercase = true ),
			);
		}

		// Earnings tab
/*
		if ( EDD_FES()->integrations->is_commissions_active() ) {
			$menu_items['earnings'] = array(
				"icon" => "shopping-cart",
				"task" => array( 'earnings' ),
				"name" => __( 'Earnings', 'edd_fes' ),
			);
		}
*/

		// Orders tab
/*
		if ( EDD_FES()->vendors->vendor_can_view_orders() ){
			$menu_items['orders'] = array(
				"icon" => "gift",
				"task" => array( 'orders' ),
				"name" => __( 'Orders', 'edd_fes' ),
			);
		}
*/

		// Profile tab
		$menu_items['profile'] = array(
			"icon" => "user",
			"task" => array( 'profile' ),
			"name" => __( 'Profile', 'edd_fes' ),
		);

		// Logout tab
		$menu_items['logout'] = array(
			"icon" => "off",
			"task" => array( 'logout' ),
			"name" => __( 'Logout', 'edd_fes' ),
		);

		return $menu_items;
	}
}
//add_filter( 'fes_vendor_dashboard_menu', 'custom_vendor_dashboard_menu' );

add_filter( "fes_vendor_dashboard_menu","anagram_edit_vender_menu", 20, 1 );

	function anagram_edit_vender_menu($menu_items){
	$unread_count = fep_get_new_message_number();
	$mess_count ='';
	if($unread_count ) $mess_count = ' (<span class="fep-font-red">'.$unread_count.'</span>)';
		// Profile tab
		$menu_items['messages'] = array(
			"icon" => "chat",
			"task" => array( 'messages' ),
			"name" => __( 'Messages', 'edd_fes' ).$mess_count ,
		);

		// Profile tab
/*
		$menu_items['subscription'] = array(
			"icon" => "user",
			"task" => array( 'subscription' ),
			"name" => __( 'Subscription', 'edd_fes' ),
		);
*/
	if ( EDD_FES()->vendors->vendor_can_view_orders() ){
			$menu_items['orders'] = array(
				"icon" => "gift",
				"task" => array( 'orders' ),
				"name" => __( 'Transactions', 'edd_fes' ),
			);
		}

			//unset($menu_items['orders']);
			unset($menu_items['earnings']);
		// Logout tab
			unset($menu_items['logout']);

		return $menu_items;
	}


function anagram_custom_task_response( $custom, $task ) {
/*
	if ( $task == 'subscription' ) {
		$custom = 'subscription';
	}
*/
/*
	if ( $task == 'messages' ) {
		$custom = 'messages';
	}
*/
	if ( $task == 'preview' ) {
		$custom = 'preview';
	}
/*
	if ( $task == 'update' ) {
		$custom = 'update';
	}
*/
	return $custom;
}
add_filter( 'fes_signal_custom_task', 'anagram_custom_task_response', 10, 2 );




function anagram_custom_preview_view() {
	// custom content
	include( locate_template( 'content/artwork-preview.php', false, false ) );
	//register / subscription
}
add_action( 'fes_custom_task_preview','anagram_custom_preview_view' );


function anagram_custom_messages_view() {
	// custom content
	//include( locate_template( 'content/messages.php', false, false ) );
	//register / subscription
}
//add_action( 'fes_custom_task_messages','anagram_custom_messages_view' );



// vendor submission redirect
function sd_fes_vendor_submission_redirect( $output, $post_id, $form_id ) {
	$output['redirect_to'] = get_site_url().'/collection/?task=products';
	return $output;
}
//add_filter( 'fes_add_post_redirect', 'sd_fes_vendor_submission_redirect', 10, 3 );


// set avatar size
function anagram_fes_avatar_size( $avatar_size, $user_id, $attachment_id ) {
	$avatar_size = array( 200, 200 );
	return $avatar_size;
}
add_filter( 'fes_avatar_size', 'anagram_fes_avatar_size', 10, 3 );


add_filter('get_avatar','add_gravatar_class');
function add_gravatar_class($class) {
    $class = str_replace("class='avatar", "class='avatar img-round", $class);
    return $class;
}





/**
 * Create vendor when sign registration
 *
 */
function anagram_rcp_save_user_fields_on_register( $user_id ) {

	$vendor = EDD_FES()->vendors->make_user_vendor( $user_id );
	// set to approved
     $vendor  = new FES_Vendor( $user_id, true );
     $vendor->change_status( 'approved', false );

}
add_action( 'user_register', 'anagram_rcp_save_user_fields_on_register', 100, 1 );




	//Change register header
function anagram_form_legend( $title ) {

if (strpos($title, 'Registration') !== false) {
   return '<legend class="fes-form-legend" id="fes-registration-form-title">REGISTER NEW ACCOUNT</legend><p class="join-link">Already a member? <a href="'.get_the_permalink(314).'">Log in</a></p>';
}

	return $title;
}
add_filter('fes_form_legend', 'anagram_form_legend');


function anagram_edd_checkout_text( $translated, $context, $domain ) {



    if(  $translated == 'Save Draft'  && $domain == 'edd_fes'  ) {
		 $translated = __( '&#xf070;  Save as Private', 'edd_fes' );
	}

    if(  $translated == 'Success!'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'Welcome to artAttendant!' );
	}
	if(  $translated == 'Your Application has been Approved!'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'Welcome to the new artwork collection management system!' );
	}

	if(  $translated == 'Application Approved'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'Welcome to the artAttendant!' );
	}

	if(  $translated == 'New Vendor Application Approved'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'Someone Signed up on artAttendant' );
	}


/*
    if(  $original == '%s edited successfully!'  && $domain == 'edd_fes'  ) {
		 $translated = __( '%s updated and public! ', 'FES uppercase singular setting for download' , 'edd_fes' );
	}
*/
	// _x( 'New %s submitted successfully!', 'FES lowercase singular setting for download', 'edd_fes' )
	//_x( 'New %s shared public successfully!', 'FES lowercase singular setting for download', 'edd_fes' )


/*
    if(  $translated == 'Name of Store'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'Name of Collection', 'edd_fes' );
	}
    if(  $translated == 'What would you like your store to be called?'  && $domain == 'edd_fes'  ) {
		 $translated = __( 'What would you like your public collection to be called?', 'edd_fes' );
	}
*/

/*
   if( $translated == 'Checkout' && $domain == 'easy-digital-downloads' ) {
       //$translated = __( 'Continue', 'easy-digital-downloads' );
   }
   	if(  $translated == 'Added to cart'  && $domain == 'easy-digital-downloads'  ) {
		// $translated = __( 'Some new text here', 'easy-digital-downloads' );
	}

	 if(  $translated == 'Your cart is empty.'  && $domain == 'easy-digital-downloads'  ) {
		 //$translated = __( 'You have not added any classes for registration', 'edd' );
	}
    if( $translated == 'Download Name' && $domain == 'easy-digital-downloads' ) {
       $translated = __( 'Product Name', 'easy-digital-downloads' );
   }
      if( $translated == 'Files' && $domain == 'easy-digital-downloads' ) {
       $translated = __( 'Files', 'easy-digital-downloads' );

   }

   if( $translated == 'Free Download' && $domain == 'easy-digital-downloads' ) {
       $translated = __( 'Reserve Spot', 'easy-digital-downloads' );

   }

   if( $translated == 'With %s signup fee' && $domain == 'edd-recurring' ) {
       //$translated = __( 'With %s orientation fee', 'edd-recurring' );

   }


   if( $translated == 'Signup Fee' && $domain == 'edd-recurring' ) {
       //$translated = __( 'Orientation Fee', 'edd-recurring' );

   }

   if( $translated == 'Name your price' && $domain == 'edd_cp' ) {
       $translated = __( 'Custom Amount', 'edd_cp' );

   }

   if( $translated == 'Name your price' && $domain == 'edd_cp' ) {
       $translated = __( 'Custom Amount', 'edd_cp' );

   }

      if( $translated == 'Download Now' && $domain == 'edd-free-downloads' ) {
       $translated = __( 'Register Now', 'edd-free-downloads' );
   }


    if(  $translated == 'Easy Digital Downloads Sales Summary'  && $domain == 'easy-digital-downloads'  ) {
		 $translated = __( 'Sales Summary', 'easy-digital-downloads' );
	}


	if( $translated == 'Disable product when any item sells out' && $domain == 'edd-purchase-limit' ) {
       $translated = __( 'Disable product when any item sells out, if Checked ALL Purchase Limits must be the same.', 'edd-purchase-limit' );

   }
*/

   return $translated;
}
add_filter( 'gettext', 'anagram_edd_checkout_text', 10, 3 );


function anagram_fes_x_text( $translation, $text, $context, $domain ) {


    if(  $text == 'Submit' && $context == 'For the submission form'  && $domain == 'edd_fes'  ) {
		 $translation = _x( '&#xf06e;  Save as Public','For the submission form', 'edd_fes' );
	}

    if(  $text == 'Submit' && $context == 'For the login form'  && $domain == 'edd_fes'  ) {
		 $translation = _x( 'Login','For the login form', 'edd_fes' );
	}

	if(  $text == 'Submit' && $context == 'For the registration form'  && $domain == 'edd_fes'  ) {
		 $translation = _x( 'Join','For the registration form', 'edd_fes' );
	}


    if(  $text == 'Draft %s saved successfully!' && $context == 'FES lowercase singular setting for download'  && $domain == 'edd_fes'  ) {
		 $translation = _x( '%s saved to your private collection!','FES lowercase singular setting for download', 'edd_fes' );
	}
	if(  $text == 'New %s submitted successfully!' && $context == 'FES lowercase singular setting for download'  && $domain == 'edd_fes'  ) {
		 $translation = _x( 'New %s shared publicly!','FES lowercase singular setting for download', 'edd_fes' );
	}
/*
	if(  $text == '%s updated successfully!' && $context == 'FES uppercase singular setting for download'  && $domain == 'edd_fes'  ) {
		 $translation = _x( '%s shared publicly!','FES uppercase singular setting for download', 'edd_fes' );
	}
*/



/*
			if ( $this->name() == 'submission' ) {
				$label = _x( "Submit", "For the submission form",  "edd_fes" );
			} else if ( $this->name() == 'login' ) {
				$label = _x( "Submit", "For the login form",  "edd_fes" );
			} else if ( $this->name() == 'vendor-contact' ) {
				$label = _x( "Submit", "For the vendor contact form",  "edd_fes" );
			} else if ( $this->name() == 'profile' ) {
				$label = _x( "Submit", "For the profile form",  "edd_fes" );
			} else if ( $this->name() == 'registration' ) {
				$label = _x( "Submit", "For the registration form",  "edd_fes" );
			} else {
				$label = _x( "Submit", "For the ' . $this->name() . ' form",  "edd_fes" );
			}
*/


   return $translation;
}

add_filter( 'gettext_with_context', 'anagram_fes_x_text', 10, 4 );



/*
*
*   Message about sold status
*
*/

 add_action('artwork_sold_information', 'get_artwork_sold_information', 10, 3 );
// first param: Form Object
// second param: Save ID of post/user/custom
// third param: Field Object
function get_artwork_sold_information( $form, $save_id, $field ) {
	// Do whatever you want here
	$status = get_post_status( $save_id );

	//_edd_sold_to
	//_edd_download_sales // check if actually sold
	//
	//
	$sold_amount = get_post_meta($save_id, '_edd_download_sales', true);
		$sold_to = get_post_meta($save_id, '_edd_sold_to', true);

		$payment_id = get_post_meta($save_id, '_payment_id_record', true);
		//$sold_to = get_post_meta($save_id, '_edd_payment_user_id', true);
		$sold_date = get_post_meta($payment_id, '_edd_completed_date', true);

		$receipt_link = '/collection/?task=edit-order&order_id='.$payment_id;

		$user = get_user_by( 'ID', $sold_to );
		$userName = $user->display_name;

		if ( 'archive' == $status){
		echo '<div class="alert alert-danger">
		    <strong><i class="fa fa-eye fa-lg fa-fw"></i></strong> This artwork has been sold to '.$userName .' on '. $sold_date .' - Receipt: <a href="'.$receipt_link.'">link</a></strong>
		</div>';
		};




}


/*
*
*   Message about the atatus of the works
*
*/

 add_action('artwork_current_status', 'get_artwork_current_status', 10, 3 );
// first param: Form Object
// second param: Save ID of post/user/custom
// third param: Field Object
function get_artwork_current_status( $form, $save_id, $field ) {
	// Do whatever you want here
	$status = get_post_status( $save_id );


	//$user = wp_get_current_user();
	//if ( in_array( 'administrator', (array) $user->roles ) ) {
		$preview_link = ' <a href="https://artattendant.com/collection/?task=preview&post_id='.$save_id.'"   class="alert-link pull-right"><i class="fa fa-picture-o" aria-hidden="true"></i> view artwork</a>';
	 if ( 'private' == $status){
        echo '<div class="alert alert-warning">
		    <strong><i class="fa fa-eye-slash fa-lg fa-fw"></i></strong> This artwork is only viewable in your private collection. '.$preview_link.'
		</div>';
		}else if ( 'publish' == $status){
		echo '<div class="alert alert-info">
		    <strong><i class="fa fa-eye fa-lg fa-fw"></i></strong> This artwork is viewable to the public. '.$preview_link.'
		</div>';
		}else if ( 'archive' == $status){
		echo '<div class="alert alert-danger">
		    <strong><i class="fa fa-eye fa-lg fa-fw"></i></strong> This artwork has been sold. It is saved in your collection for your records</strong>
		</div>';
		};

	//}//end if user is


}



/**
 * Get array of inventory numbers
 *
 */
function anagram_get_highest_invo_number( $user_id = false , $all = false) {

	if ( empty( $user_id ) )
		$user_id = get_current_user_id();


	$args = array(
		'post_type' => 'download',
		'posts_per_page' => -1,
		'author' => $user_id,
		'fields' =>'ids',
		'post_status' => array( 'draft', 'pending', 'publish', 'private', 'archive' ),
	);

	$invos = array();
	$posts = get_posts( $args );
	foreach ( $posts as $post ) {
		$invo = get_post_meta( $post, 'inventory', true );
		if($invo && is_numeric ( $invo ) ){
			$invos[] =  $invo;
		}
	}

	if( $all )return $invos;

	return max($invos)+1;; //$highest_invo;

}

/**
 * Set User first inventory number
 *
 */
function anagram_set_invo_user_on_profile( $posted, $user_id ) {

	if(!empty(get_user_meta( $user_id, 'user_inventory_number', true )))return;

	update_user_meta( $user_id, 'user_inventory_number', 100 );

}
add_action( 'fes_save_profile_form_values_after_save', 'anagram_set_invo_user_on_profile', 100, 2 );

/**
 * Custom Inventory increaser
 *
 */
function anagram_increase_inventory_count( $obj, $user_id, $save_id ) {

	if ( ! EDD()->session->get( 'fes_is_new' ) && get_user_meta( get_current_user_id(), 'auto_inventory', true ) ) {
		return;
	}
	$count = (int) get_user_meta( $user_id, 'user_inventory_number', true );
	$count++;
	update_user_meta( $user_id, 'user_inventory_number', $count );
}
add_action( 'fes_save_submission_form_values_after_save', 'anagram_increase_inventory_count', 10, 3 );

/**
 * Custom Inventory populating
 *
 */
function anagram_add_invetory_field($form, $save_id, $field) {

	$disabled = '';
	$inventory = get_post_meta( $save_id, 'inventory', true );
	$auto   = get_user_meta( get_current_user_id(), 'auto_inventory', true );
	$auto_number  = get_user_meta( get_current_user_id(), 'user_inventory_number', true );
	if(!$inventory && $auto ) {
		//Do not allow the same number, if there is the same number get highest and increse by 1
		$allinvo = anagram_get_highest_invo_number(false,'all');
		if(in_array( $auto_number, $allinvo ) ){
			$auto_number = max($allinvo)+1;
		}
		$inventory = $auto_number;
		$disabled = 'disabled';
	};

	//mapi_var_dump($inventory);
	?>
		<fieldset class="fes-el text inventory">
			<div class="fes-label">
				<label for="fes-inventory">Inventory ID</label>
				<?php if($auto=='Yes'){ ?> <span class="fes-help">The inventory number will auto increment, you can turn this off on you <a href="<?php echo get_site_url(); ?>/collection/?task=profile">profile</a>.</span><?php }; ?>
			</div>
			<div class="fes-fields">
			   <input class="textfield" id="inventory" type="text" data-required="" data-type="text" name="inventory" <?php echo $disabled; ?> placeholder="" value="<?php echo $inventory; ?>" size="">
			</div>
		</fieldset>
	<?php
}
add_action( 'anagram_custom_invetory_field', 'anagram_add_invetory_field', 10, 3 );


/**
 * Custom Inventory starter profile
 *
 */
function anagram_user_start_number_field($form, $save_id, $field) {

	$disabled = '';
	$auto_number  = get_user_meta( get_current_user_id(), 'user_inventory_number', true );
	if( $auto_number ) {
		//Do not allow the same number, if there is the same number get highest and increse by 1
		$allinvo = anagram_get_highest_invo_number(false,'all');
		if(in_array( $auto_number, $allinvo ) ){
			$auto_number = max($allinvo)+1;
		}
		$inventory = $auto_number;
		$disabled = 'disabled';
	}else{
		$inventory = 100;
	};

	//mapi_var_dump($inventory);
	?>
	<fieldset class="fes-el text user_inventory_number start-number">
		<div class="fes-label">
			<label for="fes-user_inventory_number"><?php if( $auto_number && $auto_number!== 100 ) { ?>Your next Inventory number<?php }else{ ?>Set Inventory Start Number<?php };  ?></label>
				<?php if( $auto_number && $auto_number!== 100 ) { ?><span class="fes-help">You already set your starting number, contact support if you want to reset it.</span> <?php };  ?>
					</div>
				<div class="fes-fields">
		   <input class="textfield" id="user_inventory_number" type="number" data-required="" data-type="text" name="user_inventory_number" <?php echo $disabled; ?> placeholder="" value="<?php echo $inventory; ?>" size=""></div>
		</fieldset>
	<?php
}
add_action( 'anagram_user_start_number', 'anagram_user_start_number_field', 10, 3 );


/**
 * User vendor's display name for store name if unedited
 */
function sd_fes_store_name_username() {
	if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
		$the_vendor        = wp_get_current_user();
		$vendor_name       = get_user_meta( $the_vendor->ID, 'display_name', true );
		$vendor_store_name = get_user_meta( $the_vendor->ID, 'name_of_store', true );

		if ( ! $vendor_store_name ) {
			update_user_meta( $the_vendor->ID, 'name_of_store', $vendor_name );
		}
	}
}
add_action( 'init', 'sd_fes_store_name_username' );



	/**
 * Create vendor when sign registration
 *
 */
function anagram_save_user_fields_on_register( $user_id, $values ) {

	if(!empty(get_user_meta( $user_id, 'user_inventory_number', true )))return;

		update_user_meta( $user_id, 'user_inventory_number', 100 );
		update_user_meta( $user_id, 'auto_inventory', 1 );


}
add_action( 'fes_registration_form_frontend_vendor', 'anagram_save_user_fields_on_register', 100, 2 );



/**
 * Email connect for registers
 *
 */

function anagram_admin_email_rcp($values){

	$values = 'connect@artattendant.com';

	return $values;
}
add_filter('fes_registration_form_frontend_vendor_to_admin', 'anagram_admin_email_rcp', 100, 1);




/**
 * Email connect for registers
 *
 */

function anagram_add_email_signup($admin_emails){ ?>

	<p>
	<label>
		<input type="checkbox" name="mc4wp-subscribe" value="1" />
		Subscribe to our newsletter.	</label>
</p>
	<?php
}
//add_filter('fes_registration_form_frontend_vendor_to_admin', 'anagram_add_email_signup', 20, 1);
