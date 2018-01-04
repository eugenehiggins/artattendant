<?php

/**
 * Admin Custom functions for Theme
 */


function anagramLoadFile($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


function anagram_debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}


add_filter( 'avatar_defaults', 'artattendantgravatar' );

function artattendantgravatar ($avatar_defaults) {
    $myavatar = get_bloginfo('template_directory') . '/img/profile-icon.png';
    $avatar_defaults[$myavatar] = "artAttendant Avatar";
    return $avatar_defaults;
}



add_filter('wp_title', 'filter_pagetitle');
function filter_pagetitle($title) {
    //check if its a blog post
    if (!is_singular('download'))
        return $title;

    //if you get here then its a blog post so change the title
    global $wp_query;
    if (isset($wp_query->post->post_title)){
         return $wp_query->post->post_title.' by '.get_custom_taxonomy('artist', ' ', 'name', $wp_query->post->ID ).' | ' .get_bloginfo( 'name' );
    }

    //if wordpress can't find the title return the default
    return $title;
}



function anagram_is_member() {
    global $current_user;
	$allowed = array('volunteer','member','administrator','editor','shop_manager','member-other');
		$user_roles = $current_user->roles;
	if( array_intersect ( $user_roles , $allowed  ) ){
	    return true;
    }else{
	    return false;
    }

}

/* Redirect to collection if logged in and on login page*/
function redirect_login_to_collections() {
			global $post;

			if ( (  ( $post->post_name=='login' || $post->post_name=='join' || is_front_page() ) && is_user_logged_in() ) ) {
					nocache_headers();
					wp_redirect(get_permalink(74));
					exit();
			}

}

function redirect_from_collections_to_login() {
			global $post;

			if ( !is_user_logged_in() ) {
					nocache_headers();
					wp_redirect(get_permalink(314));
					exit();
			}
}



function auth_redirect_to_login() {
			global $post;
			$user = wp_get_current_user();
			if ( !( $post->post_name=='login' || $post->post_name=='lost-password' || $post->post_name=='reset-password' ) && !current_user_can('edit_posts') ) {
					nocache_headers();
					wp_redirect(get_permalink(74));
					exit();
			}
}



add_filter( 'wp_nav_menu_items', 'your_custom_menu_item', 10, 2 );
function your_custom_menu_item ( $items, $args ) {

    if ( $args->theme_location == 'primary' && is_user_logged_in() ) {
	    $user = wp_get_current_user();
        $items .= '<li class="dropdown">
			          <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.anagramLoadFile(get_template_directory_uri()."/img/profile.svg").'</a>
						    <ul class="dropdown-menu" style="padding: 15px;min-width: 250px;">
							<li>
								<h5>
									Hi '.$user->first_name.'
									</h5>
									<ul class="list-unstyled">
										<li><a href="'.get_site_url().'/collection/">Dashboard</a></li>
										<li><a href="'.get_site_url().'/collection/?task=profile">Profile</a></li>
										<li><a href="'.get_site_url().'/collection/?task=logout">Logout</a></li>
									</ul>

							</li>
						</ul>
			        </li>';
    }else if( $args->theme_location == 'primary' ){
	    $items .= '<li><a href="'.get_site_url().'/login/">Login</a></li>';
     }/*
else if( $args->theme_location == 'primary' ){
	    $items .= '<li><a href="'.get_site_url().'/login/">Login</a></li>';
    }
*/
    return $items;
}



	class FacebookDebugger
	{
		/*
		 * https://developers.facebook.com/docs/opengraph/using-objects
		 *
		 * Updating Objects
		 *
		 * When an action is published, or a Like button pointing to the object clicked,
		 * Facebook will 'scrape' the HTML page of the object and read the meta tags.
		 * The object scrape also occurs when:
		 *
		 *      - Every 7 days after the first scrape
		 *
		 *      - The object URL is input in the Object Debugger
		 *           http://developers.facebook.com/tools/debug
		 *
		 *      - When an app triggers a scrape using an API endpoint
		 *           This Graph API endpoint is simply a call to:
		 *
		 *           POST /?id={object-instance-id or object-url}&scrape=true
		 */
		public function reload($url)
		{
			$graph = 'https://graph.facebook.com/';
			$post = 'id='.urlencode($url).'&scrape=true';
			return $this->send_post($graph, $post);
		}
		private function send_post($url, $post)
		{
			$r = curl_init();
			curl_setopt($r, CURLOPT_URL, $url);
			curl_setopt($r, CURLOPT_POST, 1);
			curl_setopt($r, CURLOPT_POSTFIELDS, $post);
			curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($r, CURLOPT_CONNECTTIMEOUT, 5);
			$data = curl_exec($r);
			curl_close($r);
			return $data;
		}
	}

add_action('transition_post_status', function ($new_status, $old_status, $post) {

   //if ( $old_status == 'publish' && $new_status == 'publish' ) {
if ( $new_status == 'publish' ) {
       //Do something when post is updated
       	$fb = new FacebookDebugger();
	   	$fb->reload(get_the_permalink($post->ID) );
   }

}, 10, 3 );










	/**
 * custom FES functions
 *
 */




function anagram_artwork_status( $artwork_id ) {

	if ( empty( $artwork_id ) )
		$artwork_id = get_the_ID();


	$status = get_post_meta( $artwork_id, 'public_status', true );

	if(!is_array($status)) return false;

	if( in_array('for loan', $status )  && in_array('for sale', $status )  ){
		$content  =  3;
	}else if(in_array('for sale', $status ) ){
		$content  =  1;
	}else if(in_array('for loan', $status ) ){
		$content  =  2;
	};

	return $content;

}

function anagram_output_artwork_status( $artwork_id ) {

	if ( empty( $artwork_id ) )
		$artwork_id = get_the_ID();

	$content = '';

	$status =anagram_artwork_status( $artwork_id );

	switch ($status) {
	    case 1:
	        $content .=  ' <i class="fa fa-shopping-cart fa-lg green" aria-hidden="true" title="For Sale"></i>';
	        break;
	    case 2:
	        $content .= ' <i class="fa fa-eye fa-lg" aria-hidden="true" title="For Loan"></i>';
	        break;
	    case 3:
	       $content .=  ' <i class="fa fa-shopping-cart fa-lg green" aria-hidden="true" title="For Sale"></i> <i class="fa fa-eye fa-lg" aria-hidden="true" title="For Loan"></i>';
	        break;
	}

	return $content;


}

function member_has_artworks( $user_id = false ) {

	if ( empty( $user_id ) )
		$user_id = get_current_user_id();

	$return = false;

	$args = array(
		'post_type' => 'download',
		'posts_per_page' => 1,
		'author' => $user_id,
		'fields' => 'ids',
		'post_status' => array( 'draft', 'pending', 'publish', 'private', 'archive'  )
	);

	$artworks = get_posts( $args );

	if ( $artworks ) {
		$return = true;
	}
	return $return;
}

function anagram_get_user_work_count($post_status){
		if ( empty( $user_id ) )
		$user_id = get_current_user_id();

  $args = array(
        'numberposts'   => -1,
        'post_type'     => 'download',
        'post_status'   => $post_status,//array( 'publish', 'private', 'draft', 'pending' ),
        'author'        => $user_id
    );
    $count_posts = count( get_posts( $args ) );
    return $count_posts;



}






function containsDecimal( $value ) {
    if ( strpos( $value, "." ) !== false ) {
        return true;
    }
    return false;
}
//This removeds the decimal - not needed??
function _toInt($str)
{
    return (int)preg_replace("/([^0-9\\.])/i", "", $str);
}

function count_total_cost( $symbol = '', $user_id = false, $status = array( 'draft', 'pending', 'publish', 'private' )) {
	global $current_user;
		if ( !$user_id ) {
			$user_id = $current_user->ID;
		};
		$artworks = get_posts( array(
				'nopaging' => true,
				'author' => $user_id,
				'post_type' => 'download',
				'post_status' => $status,
			) );
		if ( empty( $artworks ) ) {
			return '0';
		}
		$total = 0;
		foreach ( $artworks as $artwork ) {

			$cost = str_replace(',', '', str_replace('$', '', get_post_meta( $artwork->ID, 'cost', true ) ) );

/*
			if(containsDecimal( $cost )){
				$cost = number_format($cost, 2);
			}else{
				$cost = $cost;
			};
*/


			$total += $cost;
		}
	    return $symbol.formatMoney($total, true);

}
/*
$region = 'en_US';
$currency = 'USD';
$formatter = new NumberFormatter($region, NumberFormatter::CURRENCY);
echo $formatter->parseCurrency(12543.67, $currency);
*/

function count_total_edd_price( $symbol = '', $user_id = false, $status = array( 'draft', 'pending', 'publish', 'private' )) {
	global $current_user;
		if ( !$user_id ) {
			$user_id = $current_user->ID;
		};
		$artworks = get_posts( array(
				'nopaging' => true,
				'author' => $user_id,
				'post_type' => 'download',
				'post_status' => $status,
			) );
		if ( empty( $artworks ) ) {
			return '0';
		}
		$total = 0;
		foreach ( $artworks as $artwork ) {
					$cost = str_replace(',', '', str_replace('$', '', get_post_meta( $artwork->ID, 'edd_price', true ) ) );

/*
			if(containsDecimal( $cost )){
				$cost = number_format($cost, 2);
			}else{
				$cost = $cost;
			};
*/


			$total += $cost;
		}

	    return $symbol.formatMoney( $total, true);

}

function formatMoney($number, $fractional=false) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}



function anagram_get_sale_artwork_meta($artwork_id, $image = true ) {

	$artwork_details = '';
	if($image)$artwork_details .= get_the_post_thumbnail( $artwork_id, 'thumbnail', array( 'class' => 'alignleft' ) ).'<br/>';
	$artwork_details .= '<strong>'.get_custom_taxonomy('artist', ' ', 'name',$artwork_id ).' | <em>'.get_the_title($artwork_id).'</em></strong><br/>';
	$artwork_details .= get_custom_taxonomy('download_category', ' ', 'name', $artwork_id ).'<br/>';
	$artwork_details .= get_custom_taxonomy('download_tag', ' ', 'name',$artwork_id );

	if( get_post_meta( $artwork_id, 'date_created',true ) )$artwork_details .= ' | '.get_post_meta( $artwork_id, 'date_created',true );

	return $artwork_details;

}



function anagram_get_public_artwork_info($artwork_id, $private = false ) {

	$artwork_details = '';
	$artwork_details .= '<div><h4>'.get_custom_taxonomy('artist', ' ', 'name',$artwork_id ).' | <em>'.get_the_title($artwork_id).'</em></h4></div>';
	$artwork_details .= '<div class="details">';
	$artwork_details .= get_custom_taxonomy('download_category', ' ', 'name',$artwork_id ).'<br/>';
	$artwork_details .= get_custom_taxonomy('download_tag', ' ', 'name',$artwork_id );

	if( get_post_meta( $artwork_id, 'date_created',true ) )$artwork_details .= ' | '.get_post_meta( $artwork_id, 'date_created',true );

	if( get_post_field('post_content', $artwork_id) )$artwork_details .= '<div class="artwork-notes">'.get_post_field('post_content', $artwork_id).'</div>';
	//if( get_post_meta( $artwork_id, 'edd_price',true ) && (in_array('for sale', get_post_meta( $artwork_id, 'public_status', true ) ) && $private ) )$artwork_details .= ' | $'.formatMoney(get_post_meta( $artwork_id, 'edd_price',true ) );
	if( !$private )$artwork_details .= '<div id="extra-details" class="toshow extra-details">';
	$measurement = get_post_meta( $artwork_id, 'measurement',true) == 'Inches' ? 'in' : 'cm';
	$artwork_details .= 'Dimensions: ';
	if( get_post_meta( $artwork_id, 'artheight',true) ){ $artwork_details .=  get_post_meta( $artwork_id, 'artheight',true).$measurement; };
	if( get_post_meta( $artwork_id, 'artwidth',true ) ){ $artwork_details .= ' x '.get_post_meta( $artwork_id, 'artwidth',true).$measurement; };
	if( get_post_meta( $artwork_id, 'artdepth',true ) ){ $artwork_details .= '  x '.get_post_meta($artwork_id,'artdepth', true ).$measurement; };
	if( get_post_meta( $artwork_id, 'size_type',true ) )$artwork_details .= ' ('.get_post_meta( $artwork_id, 'size_type',true ).')';

	if( get_post_meta( $artwork_id, 'framed',true ) ){
		$artwork_details .= '<div>Framed: ';
		if( get_post_meta( $artwork_id, 'frame_height',true) ){ $artwork_details .=  get_post_meta( $artwork_id, 'frame_height',true).$measurement; };
		if( get_post_meta( $artwork_id, 'frame_width',true ) ){ $artwork_details .= ' x '.get_post_meta( $artwork_id, 'frame_width',true).$measurement; };
		if( get_post_meta( $artwork_id, 'frame_depth',true ) ){ $artwork_details .= '  x '.get_post_meta($artwork_id,'frame_depth', true ).$measurement; };
		$artwork_details .= '</div>';
	};

	if( get_post_meta( $artwork_id, 'signature_information',true ) )$artwork_details .= '<div>Signature: '.get_post_meta( $artwork_id, 'signature_information',true ).'</div>';
	if( get_post_meta( $artwork_id, 'inventory',true ) )$artwork_details .= '<div>Inventory #: '.get_post_meta( $artwork_id, 'inventory',true ).'</div>';
	if( get_post_meta( $artwork_id, 'edition_number',true ) )$artwork_details .= '<div>Edition #: '.get_post_meta( $artwork_id, 'edition_number',true ).'</div>';

	if( !$private )$artwork_details .= '</div>';//end extra details

	$artwork_details .= '</div>';
	return $artwork_details;
/*
Artist
Optional:
title, year
medium
dimensions
inventory number
retail/value
public description
*/

}


// Get Artwork info
function anagram_get_artwork_info_for_cardview($artwork_id) {

	$artwork_details = '';
	$artwork_details .= '<div><h4>'.get_custom_taxonomy('artist', ' ', 'name',$artwork_id ).' | <em>'.get_the_title($artwork_id).'</em></h4></div>';
	$artwork_details .= '<div class="details">';

	$artwork_details .= get_custom_taxonomy('download_category', ' ', 'name',$artwork_id ).'<br/>';
	if(get_custom_taxonomy('download_tag', ' ', 'name',$artwork_id ))$artwork_details .= get_custom_taxonomy('download_tag', ' ', 'name',$artwork_id ).' | ';

		$measurement = get_post_meta( $artwork_id, 'measurement',true) == 'Inches' ? 'in' : 'cm';
		$size= '';
		if( get_post_meta( $artwork_id, 'artheight',true) ){ $size .=  get_post_meta( $artwork_id, 'artheight',true).$measurement; };
		if( get_post_meta( $artwork_id, 'artwidth',true ) ){ $size .= ' x '.get_post_meta( $artwork_id, 'artwidth',true).$measurement; };
		if( get_post_meta( $artwork_id, 'artdepth',true ) ){ $size .= '  x '.get_post_meta($artwork_id,'artdepth', true ).$measurement; };
	$artwork_details .= $size;

	if( get_post_meta( $artwork_id, 'date_created',true ) )$artwork_details .= ' | '.get_post_meta( $artwork_id, 'date_created',true );

	if( get_post_meta( $artwork_id, '_edd_price',true ) )$artwork_details .= ' | $'.formatMoney(get_post_meta( $artwork_id, '_edd_price',true ) );

	$artwork_details .= '<div class="artwork-notes">'.get_post_field('post_content', $artwork_id).'</div>';

	$artwork_details .= '</div>';
	return $artwork_details;

}




function anagram_get_private_artwork_info($artwork_id ) {

	$artwork_details = '<table class="table">';

	$artwork_details .= '<tr  data-title="Artists" ><td class="field-label col-md-3 active">Artist</td><td>'.get_custom_taxonomy('artist', ' ', 'name',$artwork_id ).'</td></tr>';
	$artwork_details .= '<tr><td class="field-label col-md-3 active">Title</td><td>'.get_the_title($artwork_id).'</td></tr>';
	$artwork_details .= '<tr><td class="field-label col-md-3 active">Category</td><td>'.get_custom_taxonomy('download_category', ' ', 'name',$artwork_id ).'</td></tr>';
	$artwork_details .= '<tr><td class="field-label col-md-3 active">Medium/Material</td><td>'.get_custom_taxonomy('download_tag', ' ', 'name',$artwork_id ).'</td></tr>';

		$measurement = get_post_meta( $artwork_id, 'measurement',true) == 'Inches' ? 'in' : 'cm';
		$size= '';
		if( get_post_meta( $artwork_id, 'artheight',true) ){ $size .=  get_post_meta( $artwork_id, 'artheight',true).$measurement; };
		if( get_post_meta( $artwork_id, 'artwidth',true ) ){ $size .= ' x '.get_post_meta( $artwork_id, 'artwidth',true).$measurement; };
		if( get_post_meta( $artwork_id, 'artdepth',true ) ){ $size .= '  x '.get_post_meta($artwork_id,'artdepth', true ).$measurement; };
	$artwork_details .= '<tr><td class="field-label col-md-3 active">Dimensions</td><td>'.$size.'</td></tr>';

	if( get_post_meta( $artwork_id, 'size_type',true ) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Size Type</td><td>'.get_post_meta( $artwork_id, 'size_type',true ).'</td></tr>';

	if( get_post_meta( $artwork_id, 'date_created',true ) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Date Created</td><td>'.get_post_meta( $artwork_id, 'date_created',true ).'</td></tr>';

	if( get_post_meta( $artwork_id, 'framed',true ) ){
		$framed = '';
		if( get_post_meta( $artwork_id, 'frame_height',true) ){ $framed .=  get_post_meta( $artwork_id, 'frame_height',true).$measurement; };
		if( get_post_meta( $artwork_id, 'frame_width',true ) ){ $framed .= ' x '.get_post_meta( $artwork_id, 'frame_width',true).$measurement; };
		if( get_post_meta( $artwork_id, 'frame_depth',true ) ){ $framed .= '  x '.get_post_meta($artwork_id,'frame_depth', true ).$measurement; };
		$artwork_details .= '<tr><td class="field-label col-md-3 active">Framed</td><td>'.$framed.'</td></tr>';
	};

	if( get_post_meta( $artwork_id, 'signature_information',true ) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Signature</td><td>'.get_post_meta( $artwork_id, 'signature_information',true ).'</td></tr>';
	if( get_post_meta( $artwork_id, 'inventory',true ) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Inventory #</td><td> '.get_post_meta( $artwork_id, 'inventory',true ).'</td></tr>';
	if( get_post_meta( $artwork_id, 'edition_number',true ) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Edition #</td><td> '.get_post_meta( $artwork_id, 'edition_number',true ).'</td></tr>';


	if( get_post_field('post_content', $artwork_id) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Notes</td><td>'.get_post_field('post_content', $artwork_id).'</td></tr>';

	if( get_post_meta( $artwork_id, 'private_notes',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Notes</td><td>'.get_post_meta( $artwork_id, 'private_notes',true).'</td></tr>';
	if( get_post_meta( $artwork_id, 'location',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Location</td><td>'.get_post_meta( $artwork_id, 'location',true).'</td></tr>';
	if( get_post_meta( $artwork_id, 'purchased_from',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Purchased From</td><td>'.get_post_meta( $artwork_id, 'purchased_from',true).'</td></tr>';
	if( get_post_meta( $artwork_id, 'consigned_sold_to',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Consigned/Sold To</td><td>'.get_post_meta( $artwork_id, 'consigned_sold_to',true).'</td></tr>';
	if( get_post_meta( $artwork_id, 'cost',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Cost</td><td>$'.formatMoney(get_post_meta( $artwork_id, 'cost',true)).'</td></tr>';
	if( get_post_meta( $artwork_id, 'edd_price',true) )$artwork_details .= '<tr><td class="field-label col-md-3 active">Sales Price</td><td>$'.formatMoney(get_post_meta( $artwork_id, 'edd_price',true)).'</td></tr>';

	if( get_post_meta( $artwork_id, 'public_status', true ) && (in_array('for sale', get_post_meta( $artwork_id, 'public_status', true ) ) || in_array('for loan', get_post_meta( $artwork_id, 'public_status', true ) ) )  )$artwork_details .= '<tr><td class="field-label col-md-3 active">Status</td><td>'.implode('',get_post_meta( $artwork_id, 'public_status', true )).'</td></tr>';

	if( get_post_meta( $artwork_id, 'file_upload',true) ){
			$artwork_details .= '<tr><td class="field-label col-md-3 active">File Uploads</td><td>';
						$files = get_post_meta( $artwork_id, 'file_upload',true);
							foreach ( $files  as $attachment_id ) {
								$artwork_details .= wp_get_attachment_link( $attachment_id, 'thumbnail', false, true );
							}

			$artwork_details .=  '</td></tr>';
	};


	$artwork_details .= '</table>';
	return $artwork_details;



}





//add_filter('list_terms_exclusions', 'anagram_list_terms_exclusions', 10, 2);

function anagram_list_terms_exclusions( $exclusions ) {
    $currentScreen = get_current_screen();

    if( current_user_can( 'my_custom_capability_assigned_to_specific_users' )
            && !current_user_can( 'manage_options' ) // Show everything to Admin
            && is_object( $currentScreen )
            && $currentScreen->id == 'edit-location'
            && $currentScreen->taxonomy == 'location' ) {
        // Get term_id's array that you want to show as per your requirement
        $terms      = implode( ',', $term_id );
        $exclusions = ( empty( $exclusions ) ? '' : $exclusions ) . ' AND' . ' t.`term_id` IN (' . $terms . ')';
    }
    return $exclusions;
}




//http://codex.wordpress.org/Function_Reference/get_allowed_mime_types
function anagram_allowed_myme_types($mime_types){
    //Creating a new array will reset the allowed filetypes
    $mime_types = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        'mp3|m4a|m4b' => 'audio/mpeg',
        'zip' => 'application/zip',
		'gz|gzip' => 'application/x-gzip',
		'rar' => 'application/rar',
        //'bmp' => 'image/bmp',
        //'tif|tiff' => 'image/tiff'
    );
    return $mime_types;
}
//add_filter('upload_mimes', 'anagram_allowed_myme_types', 1, 1);

function anagram_remove_myme_types($mime_types){
    $mime_types['avi'] = 'video/avi'; //Adding avi extension
    unset($mime_types['pdf']); //Removing the pdf extension
    return $mime_types;
}
//add_filter('upload_mimes', 'anagram_remove_myme_types', 1, 1);


//add_action( 'admin_init', 'anagram_block_users_from_uploading_small_images' );

function anagram_block_users_from_uploading_small_images()
{
    //if( !current_user_can( 'administrator') )
        add_filter( 'wp_handle_upload_prefilter', 'anagram_block_small_images_upload' );
}



/**
 * Attach a class to linked to large iamge and adding rel attr
 * e.g. a img => a.img img
 */
function give_linked_images_class($html, $id, $caption, $title, $align, $url, $size, $alt = '' ){

  // check if there are already classes assigned to the anchor
  if ( preg_match('/<a.*?>/', $html) ) {
	  $image = wp_get_attachment_image_src( $id, 'large' );
	  $html = preg_replace('/<a(.*?)>/', '<a href=\'' . $image[0] . '\' rel="lightbox">', $html);
  }
  return $html;
}
//add_filter('image_send_to_editor','give_linked_images_class',10,8);


/**
 * anagram_block_small_images_upload function.
 *
 * @access public
 * @param mixed $file
 * @return void
 */
function anagram_block_small_images_upload( $file )
{
    // Mime type with dimensions, check to exit earlier
    $mimes = array( 'image/jpeg', 'image/png', 'image/gif' );

    if( !in_array( $file['type'], $mimes ) )
        return $file;

    $img = getimagesize( $file['tmp_name'] );
    $minimum = array( 'width' => 1200 );
    //$minimum = array( 'width' => 1200, 'height' => 480 );

    if ( $img[0] < $minimum['width'] )
        $file['error'] =
            'Image too small. Minimum width is '
            . $minimum['width']
            . 'px. Uploaded image width is '
            . $img[0] . 'px';

/*
    elseif ( $img[1] < $minimum['height'] )
        $file['error'] =
            'Image too small. Minimum height is '
            . $minimum['height']
            . 'px. Uploaded image height is '
            . $img[1] . 'px';
*/

    return $file;
}



/**
 * Adds active menu item to pages for child single post types
 */
add_filter('nav_menu_css_class', 'anagram_css_attributes_filter', 100, 2);
function anagram_css_attributes_filter($classes,$item) {

	//push page name to menu
	//array_push($classes,  'menu-item-'.sanitize_title($item->title) );

	$pt = get_post_type();

	//remove blog post from beging highlighted
	if( is_tax('folder') || $pt == 'collections' ||  $pt == 'attachment' ) {
		if (in_array('menu-item-works', $classes))
		{
			array_push($classes, 'current-menu-parent');
		}

		if ( has_term( array( 'places', 'projects' ), 'folder' ) && in_array('menu-item-works', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}
		if ( has_term( array( 'places', 'projects' ), 'folder' ) && in_array('menu-item-photography', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}
		if ( has_term( array( 'drawings' ), 'folder' ) && in_array('menu-item-drawings', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}


	} /*
elseif( is_tax('folder') || $pt == 'collections' ||  $pt == 'attachment' ) {
		if (in_array('menu-item-works', $classes))
		{
			array_push($classes, 'current-menu-parent');
		}

		if (in_array('menu-item-photos', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}
}
*/


	 elseif( $pt == 'post' ) {
		// $classes = array_filter($classes, "remove_parent_classes");
		// add the current page class to a specific menu item (replace ###).
		if (in_array('menu-item-about', $classes) )
		{
			array_push($classes, 'current-menu-parent');
		}
		if ( in_category( array( 433,434 ) ) && in_array('menu-item-news', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}
		if ( in_category( array( 437,438 ) ) && in_array('menu-item-recent', $classes) )
		{
			array_push($classes, 'current-menu-item current_page_item active');
		}


	}elseif( $pt == 'download' ) {
		// $classes = array_filter($classes, "remove_parent_classes");
		// add the current page class to a specific menu item (replace ###).
		if (in_array('menu-item-discover', $classes) )
		{
			array_push($classes, 'current-menu-parent active');
		}
	}



	return $classes;

}



//Sort post types
function custom_sort_pre_get_posts( $query ) {



    if( $query->is_main_query() && !is_admin() && is_post_type_archive( 'download' ) ) {
        $query->set( 'post_status', 'publish' );
         $query->set( 'posts_per_page', 60 );
        /*$query->set( 'meta_query', array(
            array(
                'key' => 'start_date',
                //'value' => date( "m-d-Y" ),
              //  'compare' => '<='//,
                'type' => 'NUMBER'
            )
        ) );*/
    }

   // is_tax('work_type')

}
add_filter('pre_get_posts' , 'custom_sort_pre_get_posts');


function add_query_vars($aVars) {

    $aVars[] = "task";    // represents the name of the product category as shown in the URL

    return $aVars;
}

// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');


function add_rewrite_rules($aRules) {
    $aNewRules = array(
    //'([^/]+)/?$' => 'index.php?pagename=$matches[1]&page=$matches[1]',
    'collection/([^/]+)/?$' => 'index.php?pagename=collection&task=$matches[1]',
    );
    $aRules = $aNewRules + $aRules;
    return $aRules;
}

// hook add_rewrite_rules function into rewrite_rules_array
add_filter('rewrite_rules_array', 'add_rewrite_rules');

