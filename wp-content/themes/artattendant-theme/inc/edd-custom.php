<?php


define('EDD_SLUG', 'discover');




add_action( 'init', 'create_artist_tax', 0 );

function create_artist_tax() {
	register_taxonomy(
		'artist',
		'download',
		array(
			'label' => __( 'Artist' ),
			'rewrite' => array( 'slug' => 'artist' ),
			'hierarchical' => false,
		)
	);
}




//custom status Inactive
/*
function jc_custom_post_status(){
     register_post_status( 'inactive', array(
          'label'                     => _x( 'Inactive', 'download' ),
          'public'                    => false,
          'show_in_admin_all_list'    => true,
          'show_in_admin_status_list' => true,
          'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>' )
     ) );
}
add_action( 'init', 'jc_custom_post_status' );

add_action('admin_footer-post.php', 'jc_append_post_status_list');
function jc_append_post_status_list(){
     global $post;
     $complete = '';
     $label = '';
     if(in_array($post->post_type, array('download')) == true){
          if($post->post_status == 'inactive'){
               $complete = ' selected=\"selected\"';
               $label = '<span id=\"post-status-display\"> Inactive</span>';
          }
          echo '
          <script>
          jQuery(document).ready(function($){
               $("select#post_status").append("<option value=\"inactive\" '.$complete.'>Inactive</option>");
               $(".misc-pub-section label").append("'.$label.'");
          });
          </script>
          ';
     }
}
function jc_display_archive_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != 'inactive'){
          if($post->post_status == 'inactive'){
               return array('Inactive');
          }
     }
    return $states;
}
add_filter( 'display_post_states', 'jc_display_archive_state' );
*/



function jc_append_post_status_bulk_edit() {

	echo '<script>
	jQuery(document).ready(function($){
		$(".inline-edit-status select ").append("<option value=\"inactive\">Inactive</option>");
	});
	</script>';

}
add_action( 'admin_footer-edit.php', 'jc_append_post_status_bulk_edit' );



/** Use this for rates depending on artwork count */
function anagram_custom_commission_rate($rate){

	$rate = 90;
	if( anagram_get_user_work_count(array('publish',  'private', 'archive' )) > 250 ) :
		   $rate = 92;

	endif;

	return $rate;

}
add_filter('eddc_default_rate', 'anagram_custom_commission_rate', 20, 1);



/** Check if Easy Digital Downloads and Frontend Submissions is active */
if ( class_exists('Easy_Digital_Downloads') && class_exists('EDD_Front_End_Submissions') ) {
	/**
	 * Allow FES upload directory override
	 *
	 * @since 1.0.0
	 */
	add_filter( 'override_default_fes_dir',  '__return_true' );
	/**
	 * Add user nicename directory to all uploads
	 *
	 * @since 1.0.0
	 */
	function fes_set_custom_upload_dir( $upload ) {
		$user          = wp_get_current_user();
		$user_nicename = $user->user_nicename;

		// Override the year / month being based on the post publication date, if year/month organization is enabled
		if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
			// Generate the yearly and monthly dirs
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$folderDate = "/$y/$m";
		}


		$upload['subdir'] = '/edd/'.strtolower($user_nicename).$folderDate;
		$upload['path']   = $upload['basedir'] . $upload['subdir'];
		$upload['url']	  = $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}
}
//
//*change upload directory for everytihng here!
function change_uploads_dir() {
	global $pagenow;

	// Add any conditional logic on when to change directory

	add_filter( 'upload_dir', 'fes_set_custom_upload_dir' );
}
add_action( 'admin_init', 'edd_change_downloads_upload_dir', 999 );




function anagram_get_order_title( $orderID ) {
    $cart      = edd_get_payment_meta_cart_details( $orderID, true );
     if ( $cart ) :
		foreach ( $cart as $key => $item ) :

		endforeach;


		return  esc_html( $cart[0]['name'] );
    endif;
    return false;
}


function anagram_get_seller_name( $orderID, $email = false ) {
	$customer      = get_post_meta( $orderID, '_edd_payment_customer_id', true );
	$user_info = get_userdata($customer);

		$customer_name = $user_info->first_name . ' ' . $user_info->last_name;
		if($email)$customer_name .= ' - '.$user_info->user_email;

		return $customer_name;
}




/**
 * EDD functions
 *
 * Add merge tag for email
 */

add_filter( 'edd_email_tags','anagram_edd_email_tags' , 10, 1 );
	function anagram_edd_email_tags( $email_tags ) {

		$email_tags[] = array(
			'tag'         => 'seller',
			'description' => __( 'The seller\'s Name', 'easy-digital-downloads' ),
			'function'    => 'anagram_get_seller_name'
		);


		return $email_tags;
	}


/**
 * EDD functions
 *
 * @access public
 * @param mixed $imgargs
 * @return void
 */

	add_filter( 'edd_email_receipt_download_title','anagram_email_receipt' , 10, 3 );

		/**
		 * Modify email template to remove dash if the item is a service
		 *
		 * @since 1.0
		*/
		function anagram_email_receipt( $title, $item, $price_id, $payment_id ) {
				$title = get_the_title( $item['id'] );


				if(anagram_get_sale_artwork_meta($item['id']) ) {
					return anagram_get_sale_artwork_meta($item['id']);
				}


			return $title;
		}

		add_filter( 'edd_receipt_no_files_found_text',  'anagram_download_text', 10, 1 );

		function anagram_download_text( $item_id ) {

			return '';
		}




/* On purchase, transfer artworks*/
function pw_edd_on_complete_purchase( $payment_id ) {

	// Basic payment meta
	$payment_meta = edd_get_payment_meta( $payment_id );

	// Cart details
	$cart = edd_get_payment_meta_cart_details( $payment_id );

	// Duplicate the artowkr and put in the buyers collection
	anagram_duplicate_post_as_private($cart[0]['id'], $payment_id);





}
add_action( 'edd_complete_purchase', 'pw_edd_on_complete_purchase' );






/*
 * Function creates post duplicate as a draft and redirects then to the edit post screen
 */
function anagram_duplicate_post_as_private($artworkID, $payment_id){
	global $wpdb;
	if (empty($artworkID)  ) {
		wp_die('No post to duplicate has been supplied!');
	}

	/*
	 * Nonce verification
	 */
/*
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
		return;
*/

	/*
	 * get the original post id
	 */
	$post_id = $artworkID; //(isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );

	$seller_id = $post->post_author;

	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {

		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'private',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);

		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );


		$salesPrice = get_post_meta( $post_id, 'edd_price', true );
		//add the oringal artwork ID
		update_post_meta( $new_post_id, '_original_artwork', $post_id  );
		update_post_meta( $new_post_id, 'cost', $salesPrice  );
		update_post_meta( $new_post_id, 'edd_price', $salesPrice  );
		
		update_post_meta( $new_post_id, 'inventory', anagram_get_highest_invo_number()  );//update this to auto invo number
		update_post_meta( $new_post_id, '_payment_id_record', $payment_id  );



		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$excludedMeta = array('_wp_old_slug','private_notes','cost','inventory', '_original_artwork', 'consigned_inactive_to','purchased_from','file_upload','public_status','location','_edd_download_earnings','_edd_download_sales');
				if( in_array($meta_key, $excludedMeta) ) continue;



				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}




				//Duplicate media and update efield of new works

				$old_artwork  = get_post_meta( $post_id, 'edd_image_uploader' , true);


		        $attachments = get_posts( array(
		            'post_type' => 'attachment',
		            'post_status' => 'inherit',
					'post_mime_type' => 'image',
		            'posts_per_page' => -1,
		             'post__in' => $old_artwork,
		        ) );


                if ( $attachments ) {

					$new_imageArray= [];

                    foreach ( $attachments as $attachment ) {

				        // required libraries for media_handle_sideload
						require_once(ABSPATH . 'wp-admin/includes/file.php');
						require_once(ABSPATH . 'wp-admin/includes/media.php');
						require_once(ABSPATH . 'wp-admin/includes/image.php');


						// get thumbnail ID
						$old_thumbnail_id = get_post_thumbnail_id( $post_id );


						$url = wp_get_attachment_url($attachment->ID);



						// Let's copy the actual file
						$tmp = download_url( $url );
						if( is_wp_error( $tmp ) ) {
							@unlink($tmp);
							continue;
						}

						$desc = wp_slash($attachment->post_content);

						$file_array = array();
						$file_array['name'] = basename($url);
						$file_array['tmp_name'] = $tmp;


						// "Upload" to the media collection
						$new_attachment_id = media_handle_sideload( $file_array, $new_post_id, $desc );

						if ( is_wp_error($new_attachment_id) ) {
							@unlink($file_array['tmp_name']);
							continue;
						}

						$cloned_attachment = array(
								'ID'           => $new_attachment_id,
								'post_title'   => $attachment->post_title,
								'post_exceprt' => $attachment->post_title,
								'post_author'  => $new_post_author
						);
						wp_update_post( wp_slash($cloned_attachment) );

						$alt_title = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
						if($alt_title) update_post_meta($new_attachment_id, '_wp_attachment_image_alt', wp_slash($alt_title));


						$new_imageArray[] =$new_attachment_id;
						// if we have cloned the post thumbnail, set the copy as the thumbnail for the new post
						if( $old_thumbnail_id == $attachment->ID){

								set_post_thumbnail($new_post_id, $new_attachment_id);
						}



                    }

                    update_post_meta( $new_post_id, 'edd_image_uploader', $new_imageArray );

                }



		/*
		* Now to deal with the sold piece
		*
		*/

		  $my_post = array(
		      'ID'           => $post_id,
		      'post_status'    => 'archive',
		  );


		/*
		 * update the post by wp_update_post() function
		 */
		$post_id = wp_update_post( $my_post, true );

		if (is_wp_error($post_id)) {
			$errors = $post_id->get_error_messages();
			foreach ($errors as $error) {
				echo $error;
			}
		}

		update_post_meta( $post_id, 'public_status', '' );
		update_post_meta( $post_id, '_edd_sold_to', $new_post_author );
		update_post_meta( $post_id, '_cloned_artwork', $new_post_id );
		update_post_meta( $post_id, '_payment_id_record', $payment_id  );

		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		//wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		//exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}



/* End purchase edd data*/




	//Change name
function pw_edd_product_labels( $labels ) {
	$labels = array(
	   'singular' => __('Artwork', 'your-domain'),
	   'plural' => __('Artworks', 'your-domain')
	);
	return $labels;
}
add_filter('edd_default_downloads_name', 'pw_edd_product_labels');


