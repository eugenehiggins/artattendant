<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function fep_plugin_activate(){

	//Deprecated in 4.4
	//Move inside Front_End_Pm class
	
	}

function fep_get_option( $option, $default = '', $section = 'FEP_admin_options' ) {
	
    $options = get_option( $section );
	
	$is_default = false;

    if ( isset( $options[$option] ) ) {
        $value = $options[$option];
    } else {
		$value = $default;
		$is_default = true;
	}

    return apply_filters('fep_get_option', $value, $option, $default, $is_default );
}

function fep_get_user_option( $option, $default = '', $userid = '', $section = 'FEP_user_options' ) {
			
    $options = get_user_option( $section, $userid ); //if $userid = '' current user option will be return

    $is_default = false;

    if ( isset( $options[$option] ) ) {
        $value = $options[$option];
    } else {
		$value = $default;
		$is_default = true;
	}

    return apply_filters('fep_get_user_option', $value, $option, $default, $userid, $is_default );
}

if ( !function_exists('fep_get_plugin_caps') ) :

function fep_get_plugin_caps( $edit_published = false, $for = 'both' ){
	$message_caps = array(
		'delete_published_fep_messages' => 1,
		'delete_private_fep_messages' => 1,
		'delete_others_fep_messages' => 1,
		'delete_fep_messages' => 1,
		'publish_fep_messages' => 1,
		'read_private_fep_messages' => 1,
		'edit_private_fep_messages' => 1,
		'edit_others_fep_messages' => 1,
		'edit_fep_messages' => 1,
		);
	
	$announcement_caps = array(
		'delete_published_fep_announcements' => 1,
		'delete_private_fep_announcements' => 1,
		'delete_others_fep_announcements' => 1,
		'delete_fep_announcements' => 1,
		'publish_fep_announcements' => 1,
		'read_private_fep_announcements' => 1,
		'edit_private_fep_announcements' => 1,
		'edit_others_fep_announcements' => 1,
		'edit_fep_announcements' => 1,
		'create_fep_announcements' => 1,
		);
	
	if( 'fep_message' == $for ) {
		$caps = $message_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
		}
	} elseif( 'fep_announcement' == $for ){
		$caps = $announcement_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_announcements'] = 1;
		}
	} else {
		$caps = array_merge( $message_caps, $announcement_caps );
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
			$caps['edit_published_fep_announcements'] = 1;
		}
	}
	return $caps;
}

endif;

if ( !function_exists('fep_add_caps_to_roles') ) :

function fep_add_caps_to_roles( $roles = array( 'administrator', 'editor' ) ) {

	if( ! is_array( $roles ) )
		$roles = array();
	
	$caps = fep_get_plugin_caps();
	
	foreach( $roles as $role ) {
		$role_obj = get_role( $role );
		if( !$role_obj )
			continue;
			
		foreach( $caps as $cap => $val ) {
			if( $val )
				$role_obj->add_cap( $cap );
		}
	}
}

endif;

if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) return;

add_action('after_setup_theme', 'fep_include_require_files');

function fep_include_require_files() {

	$fep_files = array(
			'announcement' 	=> FEP_PLUGIN_DIR. 'includes/class-fep-announcement.php',
			'attachment' 	=> FEP_PLUGIN_DIR. 'includes/class-fep-attachment.php',
			'cpt' 			=> FEP_PLUGIN_DIR. 'includes/class-fep-cpt.php',
			'directory' 	=> FEP_PLUGIN_DIR. 'includes/class-fep-directory.php',
			'email' 		=> FEP_PLUGIN_DIR. 'includes/class-fep-emails.php',
			'form' 			=> FEP_PLUGIN_DIR. 'includes/class-fep-form.php',
			'menu' 			=> FEP_PLUGIN_DIR. 'includes/class-fep-menu.php',
			'message' 		=> FEP_PLUGIN_DIR. 'includes/class-fep-message.php',
			'main' 			=> FEP_PLUGIN_DIR. 'includes/fep-class.php',
			'widgets' 		=> FEP_PLUGIN_DIR. 'includes/fep-widgets.php'
			);
	
	if( is_admin() ) {
		$fep_files['settings'] 	= FEP_PLUGIN_DIR. 'admin/class-fep-admin-settings.php';
		$fep_files['update'] 	= FEP_PLUGIN_DIR. 'admin/class-fep-update.php';
		$fep_files['pro-info'] 	= FEP_PLUGIN_DIR. 'admin/class-fep-pro-info.php';
	}			
					
	$fep_files = apply_filters('fep_include_files', $fep_files );
	
	foreach ( $fep_files as $fep_file ) {
			require_once( $fep_file );
		}
}

function fep_plugin_update(){
	
	$prev_ver = fep_get_option( 'plugin_version', '4.1' );
	
	if( version_compare( $prev_ver, FEP_PLUGIN_VERSION, '!=' ) ) {
		
		do_action( 'fep_plugin_update', $prev_ver );
		
		update_option( 'FEP_admin_options', wp_parse_args( array( 'plugin_version' => FEP_PLUGIN_VERSION ), get_option('FEP_admin_options') ) );
	}

}
add_action( 'admin_init', 'fep_plugin_update' );

function fep_plugin_update_from_first( $prev_ver ){
	
	if( is_admin() && '4.1' == $prev_ver ) { //any previous version of 4.1 also return 4.1
		fep_plugin_activate();
	}

}
add_action( 'fep_plugin_update', 'fep_plugin_update_from_first' );

add_action('after_setup_theme', 'fep_translation');

function fep_translation()
	{
	//SETUP TEXT DOMAIN FOR TRANSLATIONS
	load_plugin_textdomain('front-end-pm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

add_action('wp_enqueue_scripts', 'fep_enqueue_scripts');
	
function fep_enqueue_scripts()
    {
	
	wp_register_style( 'fep-common-style', FEP_PLUGIN_URL . 'assets/css/common-style.css' );
	wp_register_style( 'fep-style', FEP_PLUGIN_URL . 'assets/css/style.css' );

	if( fep_page_id() ) {
		if( is_page( fep_page_id() ) ) {
			wp_enqueue_style( 'fep-style' );
		}
	} else {
		wp_enqueue_style( 'fep-style' );
	}
	wp_enqueue_style( 'fep-common-style' );
	$custom_css = trim( stripslashes(fep_get_option('custom_css') ) );
	if( $custom_css ) {
		wp_add_inline_style( 'fep-common-style', $custom_css );
	}
	
	wp_register_script( 'fep-script', FEP_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), '3.1', true );
	wp_localize_script( 'fep-script', 'fep_script', 
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce('fep-autosuggestion')
			) 
		);
		
	wp_register_script( 'fep-notification-script', FEP_PLUGIN_URL . 'assets/js/notification.js', array( 'jquery' ), '3.1', true );
	wp_localize_script( 'fep-notification-script', 'fep_notification_script', 
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce('fep-notification'),
				'interval' => apply_filters( 'fep_filter_ajax_notification_interval', 60000 )
			) 
		);
	
	
	wp_register_script( 'fep-replies-show-hide', FEP_PLUGIN_URL . 'assets/js/replies-show-hide.js', array( 'jquery' ), '3.1', true );
	
	wp_register_script( 'fep-attachment-script', FEP_PLUGIN_URL . 'assets/js/attachment.js', array( 'jquery' ), '3.1', true );
	wp_localize_script( 'fep-attachment-script', 'fep_attachment_script', 
			array( 
				'remove' => esc_js(__('Remove', 'front-end-pm')),
				'maximum' => esc_js( number_format_i18n( fep_get_option('attachment_no', 4) ) ),
				'max_text' => esc_js(__('Maximum file allowed', 'front-end-pm'))
				
			) 
		);
    }

function fep_page_id() {
		
     return apply_filters( 'fep_page_id_filter', fep_get_option('page_id', 0 ) );
}

function fep_action_url( $action = '', $arg = array() ) {
	  
	  return fep_query_url( $action, $arg );
}

function fep_query_url( $action, $arg = array() ) {
      
	$args = array( 'fepaction' => $action );
	$args = array_merge( $args, $arg );
	
	if ( fep_page_id() ) {
		$url = esc_url( add_query_arg( $args, get_permalink( fep_page_id() ) ) );
	} else {
		$url = esc_url( add_query_arg( $args ) );
	}
	
	return apply_filters( 'fep_query_url_filter', $url, $args );
}

if ( !function_exists('fep_create_nonce') ) :
 /**
 * Creates a token usable in a form
 * return nonce with time
 * @return string
 */
	function fep_create_nonce($action = -1) {
   	 $time = time();
    	$nonce = wp_create_nonce($time.$action);
    return $nonce . '-' . $time;
	}	

endif;

if ( !function_exists('fep_verify_nonce') ) :
 /**
 * Check if a token is valid. Mark it as used
 * @param string $_nonce The token
 * @return bool
 */
	function fep_verify_nonce( $_nonce, $action = -1) {

    //Extract timestamp and nonce part of $_nonce
    $parts = explode( '-', $_nonce );
	
	// bad formatted onetime-nonce
	if ( empty( $parts[0] ) || empty( $parts[1] ) )
		return false;
		
    $nonce = $parts[0]; // Original nonce generated by WordPress.
    $generated = $parts[1]; //Time when generated

    $expire = (int) $generated + HOUR_IN_SECONDS; //We want these nonces to have a short lifespan
    $time = time();

    //Verify the nonce part and check that it has not expired
    if( ! wp_verify_nonce( $nonce, $generated.$action ) || $time > $expire )
        return false;

    //Get used nonces
    $used_nonces = get_option('_fep_used_nonces');
	
	if(! is_array( $used_nonces ) )
		$used_nonces = array();

    //Nonce already used.
    if( isset( $used_nonces[$nonce] ) )
        return false;

    foreach ($used_nonces as $nonces => $timestamp){
        if( $timestamp < $time ){
        //This nonce has expired, so we don't need to keep it any longer
        unset( $used_nonces[$nonces] );
		}
    }

    //Add nonce to used nonces
    $used_nonces[$nonce] = $expire;
    update_option( '_fep_used_nonces',$used_nonces );
	return true;
}
endif;

function fep_error($wp_error){
	if(!is_wp_error($wp_error)){
		return '';
	}
	if(count($wp_error->get_error_messages())==0){
		return '';
	}
	$errors = $wp_error->get_error_messages();
	if (is_admin())
	$html = '<div id="message" class="error">';
	else
	$html = '<div class="fep-wp-error">';
	foreach($errors as $error){
		$html .= '<strong>' . __('Error', 'front-end-pm') . ': </strong>'.esc_html($error).'<br />';
	}
	$html .= '</div>';
	return $html;
}

function fep_get_new_message_number()
    {

      return fep_get_user_message_count( 'unread' );
    }

add_shortcode( 'fep_new_message_count_shortcode', 'fep_get_new_message_button' );
	
function fep_get_new_message_button(){
	if (fep_get_new_message_number()){
	  	$newmgs = " (<span class='fep-font-red'>";
		$newmgs .= fep_get_new_message_number();
		$newmgs .='</span>)';
		} else {
		$newmgs = '';
	}
		
	return $newmgs;
}

function fep_get_new_announcement_number()
    {

      return fep_get_user_announcement_count( 'unread' );
    }
	
function fep_get_new_announcement_button(){
	if (fep_get_new_announcement_number()){
	  	$newmgs = " (<span class='fep-font-red'>";
		$newmgs .= fep_get_new_announcement_number();
		$newmgs .='</span>)';
		} else {
		$newmgs = '';
	}
		
	return $newmgs;
}

function fep_is_user_blocked( $login = '' ){
	global $user_login;
	if ( !$login && $user_login )
	$login = $user_login;
	
	if ($login){
		$wpusers = explode(',', fep_get_option('have_permission') );
		
		$wpusers = array_map( 'trim', $wpusers );

		if( in_array( $login, $wpusers) )
		return true;
	} //User not logged in
	return false;
}

function fep_is_user_whitelisted( $login = '' ){
	global $user_login;
	if ( !$login && $user_login )
	$login = $user_login;
	
	if ($login){
	$wpusers = explode(',', fep_get_option('whitelist_username') );
	
	$wpusers = array_map( 'trim', $wpusers );

		if(in_array( $login, $wpusers))
		return true;
	} //User not logged in
	return false;
}

function fep_get_userdata($data, $need = 'ID', $type = 'slug' )
		{
		if (!$data)
			return '';
		
		$type = strtolower($type);
		
		if( 'user_nicename' == $type )
			$type = 'slug';
			
		if ( !in_array($type, array ('id', 'slug', 'email', 'login' )))
			return '';
		
		$user = get_user_by( $type , $data);
		
		if ( $user && in_array($need, array('ID', 'user_login', 'display_name', 'user_email', 'user_nicename', 'user_registered' )))
			return $user->$need;
		else
			return '';
	}

function fep_get_user_message_count( $value = 'all', $force = false, $user_id = false )
{
	return Fep_Message::init()->user_message_count( $value, $force, $user_id );
}

function fep_get_user_announcement_count( $value = 'all', $force = false, $user_id = false )
{
	return Fep_Announcement::init()->get_user_announcement_count( $value, $force, $user_id );
}

function fep_get_message( $id )
{
	$post = get_post( $id );
	
	if( $post && in_array( get_post_type( $post ), array( 'fep_message', 'fep_announcement') ) ){
		return $post;
	} else {
		return null;
	}
	
}

function fep_get_replies( $id )
{
	$args = array(
		'post_type' => 'fep_message',
		'post_status' => 'publish',
		'post_parent' => $id,
		'posts_per_page' => -1,
		'order'=> 'ASC'
	 );
	 
	 $args = apply_filters( 'fep_filter_get_replies', $args );
	 
	return new WP_Query( $args );
}

function fep_get_attachments( $post_id = 0 ) {

	if( ! $post_id ) {
		$post_id = get_the_ID();
	}
	
	if( ! $post_id ) {
		return array();
	}
    $args =  array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_status'    => array( 'publish', 'inherit' ),
        'post_parent'    => $post_id
    );
	
	$args = apply_filters( 'fep_filter_get_attachments', $args );
	
	return get_posts( $args );
}

function fep_get_message_with_replies( $parent_id )
{
	
	$args = array(
		'post_type' => 'fep_message',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'order'=> 'ASC'
	 );
	 
	 if( 'threaded' == fep_get_option('message_view','threaded') ) {
		$args['post_parent'] = $parent_id;
		$args['fep_include_parent'] = true;
	} else {
		$args['post__in'] = array( $parent_id );
	}
	
	$args = apply_filters( 'fep_filter_get_message_with_replies', $args );
	 
	return new WP_Query( $args );
}

add_filter( 'posts_where' , 'fep_posts_where', 10, 2 );

function fep_posts_where( $where, $q ) {

	global $wpdb;
	
	if ( true === $q->get( 'fep_include_parent' ) && $q->get( 'post_parent' ) ){
        $where .= $wpdb->prepare( " OR ( $wpdb->posts.ID = %d AND $wpdb->posts.post_status = %s )", $q->get( 'post_parent' ), $q->get( 'post_status' ) );
	}
	
	return $where;
}

function fep_get_parent_id( $id ) {

	if( ! $id )
		return 0;
	
	$parent = wp_get_post_parent_id( $id );
	
	// climb up the hierarchy until we reach parent = '0'
    while ( $parent != '0'){
        $id = $parent;

        $parent = wp_get_post_parent_id( $id );
    }
	
	return $id;

}

add_filter( 'the_time', 'fep_format_date', 10, 2  ) ;

function fep_format_date( $date, $d )
    {
		global $post;
		
		if( is_admin() || ! in_array( get_post_type(), apply_filters( 'fep_post_types_for_time', array( 'fep_message', 'fep_announcement' ) ) ) )
			return $date;
			
		
		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$h_time = __( 'Unpublished', 'front-end-pm' );
		} else {
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post, false );
			
			if ( ( abs( $t_diff = time() - $time ) ) < DAY_IN_SECONDS ) {
				$h_time = sprintf( __( '%s ago', 'front-end-pm' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'F j, Y g:i a', 'front-end-pm' ), $m_time );
			}
		}

	  
	  return apply_filters( 'fep_formate_date', $h_time, $date, $d );
    }
	
	function fep_output_filter($string, $title = false)
    {
		$string = stripslashes($string);
		
	  if ($title) {
	  $html = apply_filters('fep_filter_display_title', $string);
	  } else {
	  $html = apply_filters('fep_filter_display_message', $string);
	  }
	  
      return $html;
    }

function fep_sort_by_priority( $a, $b ) {
	    if ( ! isset( $a['priority'] ) || ! isset( $b['priority'] ) || $a['priority'] === $b['priority'] ) {
	        return 0;
	    }
	    return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}

	
function fep_pagination( $total = null, $per_page = null, $list_class = 'fep-pagination' ) {

	$filter = ! empty( $_GET['fep-filter'] ) ? $_GET['fep-filter'] : 'total';
	
 	if( null === $total ) {
		$total = fep_get_user_message_count($filter);
	}
	if( null === $per_page ) {
		$per_page = fep_get_option('messages_page',15);
	}
		
    $last       = ceil( absint($total) / absint($per_page) );
	
	if( $last <= 1 )
		return;
		
	//$numPgs = $total_message / fep_get_option('messages_page',50);
	$page 		=  ( ! empty( $_GET['feppage'] )) ? absint($_GET['feppage']) : 1;
	$links      = ( isset( $_GET['links'] ) ) ? absint($_GET['links']) : 2;
 
    $start      = ( ( $page - $links ) > 0 ) ? $page - $links : 1;
    $end        = ( ( $page + $links ) < $last ) ? $page + $links : $last;
 
    $html       = '<div class="fep-align-centre"><ul class="' . $list_class . '">';
 
    $class      = ( $page == 1 ) ? "disabled" : "";
    $html       .= '<li class="' . $class . '"><a href="' . esc_url( add_query_arg( 'feppage', ( $page - 1 ) ) ) . '">&laquo;</a></li>';
 
    if ( $start > 1 ) {
        $html   .= '<li><a href="' . esc_url( add_query_arg( 'feppage',  1 ) ) . '">' . number_format_i18n( 1 ) . '</a></li>';
        $html   .= '<li class="disabled"><span>...</span></li>';
    }
 
    for ( $i = $start ; $i <= $end; $i++ ) {
        $class  = ( $page == $i ) ? "active" : "";
        $html   .= '<li class="' . $class . '"><a href="' . esc_url( add_query_arg( 'feppage', $i ) ) . '">' . number_format_i18n( $i ) . '</a></li>';
    }
 
    if ( $end < $last ) {
        $html   .= '<li class="disabled"><span>...</span></li>';
        $html   .= '<li><a href="' . esc_url( add_query_arg( 'feppage', $last ) ) . '">' . number_format_i18n( $last ) . '</a></li>';
    }
 
    $class      = ( $page == $last ) ? "disabled" : "";
    $html       .= '<li class="' . $class . '"><a href="' . esc_url( add_query_arg( 'feppage', ( $page + 1 ) ) ) . '">&raquo;</a></li>';
 
    $html       .= '</ul></div>';
 
    return $html;
}

function fep_is_user_admin(){
	
	$admin_cap = apply_filters( 'fep_admin_cap', 'manage_options' );
	
	return current_user_can( $admin_cap );
}

function fep_current_user_can( $cap, $id = false ) {
	$can = false;
	
	if( ! is_user_logged_in() || fep_is_user_blocked() ) {
		return apply_filters( 'fep_current_user_can', $can, $cap, $id );
	}
	
	switch( $cap ) {
		case 'access_message':
			if( fep_is_user_whitelisted() || array_intersect( fep_get_option('userrole_access', array() ), wp_get_current_user()->roles )){
				$can = true;
			}
		break;
		case 'send_new_message' :
			if( fep_is_user_whitelisted() || array_intersect( fep_get_option('userrole_new_message', array() ), wp_get_current_user()->roles )){
				$can = true;
			}
		break;
		case 'send_reply' :
			if( ! $id || ! in_array( get_current_user_id(), get_post_meta( $id, '_participants' ) ) || get_post_status ( $id ) != 'publish' ) {
			
			} elseif( fep_is_user_whitelisted() || array_intersect( fep_get_option('userrole_reply', array() ), wp_get_current_user()->roles )){
				$can = true;
			}
		break;
		case 'view_message' :
			if( $id && ( ( in_array( get_current_user_id(), get_post_meta( $id, '_participants' ) ) && get_post_status ( $id ) == 'publish' ) || fep_is_user_admin() )) {
				$can = true;
			}
		break;
		case 'delete_message' : //only for himself
			if( $id && in_array( get_current_user_id(), get_post_meta( $id, '_participants' ) ) && get_post_status ( $id ) == 'publish' ) {
				$can = true;
			}
		break;
		case 'access_directory' :
			if( fep_is_user_admin() || ! fep_get_option('hide_directory', 0 ) ) {
				$can = true;
			}
		break;
		case 'view_announcement' :
			if( $id && ( ( array_intersect( get_post_meta( $id, '_participant_roles' ), wp_get_current_user()->roles ) && get_post_status ( $id ) == 'publish') || fep_is_user_admin() || get_post_field( 'post_author', $id ) == get_current_user_id() ) ) {
				$can = true;
			}
		break;
		default :	
			$can = apply_filters( 'fep_current_user_can_' . $cap, $can, $cap, $id );
		break;
	}
	return apply_filters( 'fep_current_user_can', $can, $cap, $id );
}

function fep_is_read( $parent = false, $post_id = false, $user_id = false )
{
	if( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if( ! $post_id ) {
		$post_id = get_the_ID();
	}
	if( !$post_id || !$user_id ) {
		return false;
	}
	if( $parent ) {
		return get_post_meta( $post_id, '_fep_parent_read_by_'. $user_id, true );
	}
	$read_by = get_post_meta( $post_id, '_fep_read_by', true );

	
	if( is_array( $read_by ) && in_array( $user_id, $read_by ) ) {
		return true;
	}

	return false;
}

function fep_make_read( $parent = false, $post_id = false, $user_id = false )
{
	if( ! $post_id ) {
		$post_id = get_the_ID();
	}
	if( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if( !$post_id || !$user_id ) {
		return false;
	}
	if( $parent ) {
		$return = add_post_meta( $post_id, '_fep_parent_read_by_'. $user_id, time(), true );
		if( $return ) {
		 delete_user_meta( $user_id, '_fep_user_message_count' );
		 	return true;
		} else {
			return false;
		}
	} 
	$read_by = get_post_meta( $post_id, '_fep_read_by', true );
	
	if( ! is_array( $read_by ) ) {
		$read_by = array();
	}
	if( in_array( $user_id, $read_by ) ) {
		return false;
	}
	$read_by[time()] = $user_id;
	
	return update_post_meta( $post_id, '_fep_read_by', $read_by );
	
}

function fep_get_the_excerpt($count = 100, $excerpt = false ){
  if( false === $excerpt )
  $excerpt = get_the_excerpt();
  $excerpt = strip_shortcodes($excerpt);
  $excerpt = wp_strip_all_tags($excerpt);
  $excerpt = substr($excerpt, 0, $count);
  $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
  $excerpt = $excerpt.' ...';
  
  return apply_filters( 'fep_get_the_excerpt', $excerpt, $count);
}

function fep_get_current_user_max_message_number()
{
	$roles = wp_get_current_user()->roles;
	
	$count_array = array();
	
	if( $roles && is_array($roles) ) {
		foreach( $roles as $role ) {
			$count = fep_get_option("message_box_{$role}", 50);
			if( ! $count ) {
				return 0;
			}
			$count_array[] = $count;
		}
	}
	if( $count_array ) {
		return max($count_array);
	} else {
		return 0; //FIX ME. 0 = unlimited !!!!
	}
}

function fep_wp_mail_from( $from_email ) {
	
	$email = fep_get_option('from_email', get_bloginfo('admin_email'));
	
	if( is_email( $email ) ) {
		return $email;
	}
	return $from_email;	
	
}

function fep_wp_mail_from_name( $from_name ) {
	
	$name = fep_get_option('from_name', get_bloginfo('name'));
	
	if( $name ) {
		return $name;
	}
	return $from_name;	
	
}

function fep_wp_mail_content_type( $content_type ) {
	
	$type = fep_get_option( 'email_content_type', 'plain_text' );
	
	if( 'html' == $type ) {
		return 'text/html';
	} elseif( 'plain_text' == $type ) {
		return 'text/plain';
	}
	return $content_type;	
	
}

function fep_add_email_filters( $for = 'message' ){
	
	add_filter( 'wp_mail_from', 'fep_wp_mail_from', 10, 1 );
	add_filter( 'wp_mail_from_name', 'fep_wp_mail_from_name', 10, 1 );
	add_filter( 'wp_mail_content_type', 'fep_wp_mail_content_type', 10, 1 );
	
	do_action( 'fep_action_after_add_email_filters', $for );
}

function fep_remove_email_filters( $for = 'message' ){
	
	remove_filter( 'wp_mail_from', 'fep_wp_mail_from', 10, 1 );
	remove_filter( 'wp_mail_from_name', 'fep_wp_mail_from_name', 10, 1 );
	remove_filter( 'wp_mail_content_type', 'fep_wp_mail_content_type', 10, 1 );
	
	do_action( 'fep_action_after_remove_email_filters', $for );
}

function fep_send_message( $message = null, $override = array() )
{
	if( null === $message ) {
		$message = $_POST;
	}
	
	if( ! empty($message['fep_parent_id'] ) ) {
		$message['post_status'] = fep_get_option('reply_post_status','publish');
		$message['message_title'] = __('RE:', 'front-end-pm'). ' ' . get_the_title( $message['fep_parent_id'] );
		$message['message_to_id'] = get_post_meta( $message['fep_parent_id'], '_participants' );
		$message['post_parent'] = absint( $message['fep_parent_id'] );
	} else {
		$message['post_status'] = fep_get_option('parent_post_status','publish');
		$message['post_parent'] = 0;
	}
	
	$message = apply_filters('fep_filter_message_before_send', $message );
	
	if( empty($message['message_title']) || empty($message['message_content']) ) {
		return false;
	}
	// Create post array
	$post = array(
	  	'post_title'    => $message['message_title'],
	  	'post_content'  => $message['message_content'],
	  	'post_status'   => $message['post_status'],
	  	'post_parent'   => $message['post_parent'],
	  	'post_type'   	=> 'fep_message'
	);
	
	if( $override && is_array( $override ) ) {
		$post = wp_parse_args( $override, $post );
	}
	 
	$post = apply_filters('fep_filter_message_after_override', $post );
	
	// Insert the message into the database
	$message_id = wp_insert_post( $post );
	
	if( ! $message_id || is_wp_error( $message_id ) ) {
		return false;
	}
	$inserted_message = get_post( $message_id );
	
	if( ! empty($message['message_to_id'] ) ) { //FRONT END message_to return id of participants
		if( is_array( $message['message_to_id'] ) ) {
			foreach( $message['message_to_id'] as $participant ) {
				add_post_meta( $message_id, '_participants', $participant );
			}
		} else {
			add_post_meta( $message_id, '_participants', $message['message_to_id'] );
		}
	}
	add_post_meta( $message_id, '_participants', $inserted_message->post_author );
	
	if( $inserted_message->post_parent ) {
		
		$participants = get_post_meta( $inserted_message->post_parent, '_participants' );
	
		if( $participants && is_array( $participants ) )
		{
			foreach( $participants as $participant ) 
			{
				delete_post_meta( $inserted_message->post_parent, '_fep_parent_read_by_'. $participant );
				delete_user_meta( $participant, '_fep_user_message_count' );
			}
		}
		fep_make_read( true, $inserted_message->post_parent, $inserted_message->post_author );
		
	} else {
		$participants = get_post_meta( $message_id, '_participants' );
	
		if( $participants && is_array( $participants ) )
		{
			foreach( $participants as $participant ) 
			{
				delete_user_meta( $participant, '_fep_user_message_count' );
			}
		}
	}
	
	fep_make_read( true, $message_id, $inserted_message->post_author );
	
	 do_action('fep_action_message_after_send', $message_id, $message, $inserted_message );
	
	return $message_id;
}


function fep_backticker_encode($text) {
	$text = $text[1];
    $text = str_replace('&amp;lt;', '&lt;', $text);
    $text = str_replace('&amp;gt;', '&gt;', $text);
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = preg_replace("|\n+|", "\n", $text);
	$text = nl2br($text);
    $text = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $text);
	$text = preg_replace("/^ /", '&nbsp;', $text);
    $text = preg_replace("/(?<=&nbsp;| |\n) /", '&nbsp;', $text);
    
    return "<code>$text</code>";
}

function fep_backticker_display_code($text) {
    //$text = preg_replace_callback("|`(.*?)`|", "fep_backticker_encode", $text);
	$text = preg_replace_callback('!`(?:\r\n|\n|\r|)(.*?)(?:\r\n|\n|\r|)`!ims', "fep_backticker_encode", $text);
    $text = str_replace('<code></code>', '`', $text);
    return $text;
}

function fep_backticker_code_input_filter( $message ) {

	$message['message_content'] = fep_backticker_display_code($message['message_content']);
	
	return $message;
	}
add_filter( 'fep_filter_message_before_send', 'fep_backticker_code_input_filter', 5);

function fep_autosuggestion_ajax() {
global $user_ID;

if(fep_get_option('hide_autosuggest') == '1' && !fep_is_user_admin() )
die();

if ( check_ajax_referer( 'fep-autosuggestion', 'token', false )) {

$searchq = $_POST['searchBy'];


$args = array(
					'search' => "*{$searchq}*",
					'search_columns' => array( 'display_name' ),
					'exclude' => array( $user_ID ),
					'number' => 5,
					'orderby' => 'display_name',
					'order' => 'ASC',
					'fields' => array( 'display_name', 'user_nicename' )
		);
	
	$args = apply_filters ('fep_autosuggestion_arguments', $args );
	
	// The Query
	$user_query = new WP_User_Query( $args );
	
if(strlen($searchq)>0)
{
	echo "<ul>";
	if (! empty( $user_query->results ))
	{
		foreach($user_query->results as $user)
		{
				
				?>
				<li><a href="#" onClick="fep_fill_autosuggestion('<?php echo $user->user_nicename; ?>','<?php echo $user->display_name; ?>');return false;"><?php echo $user->display_name; ?></a></li>
				<?php
			
		}
	}
	else
		echo "<li>".__("No matches found", 'front-end-pm')."</li>";
	echo "</ul>";
}
}
die();
}

add_action('wp_ajax_fep_autosuggestion_ajax','fep_autosuggestion_ajax');	

function fep_footer_credit()
    {
	$style = '';
	if ( fep_get_option('hide_branding',0) == 1 ) {
		$style = " style='display: none'";
	}
	echo "<div{$style}><a href='https://www.shamimsplugins.com/wordpress/products/front-end-pm/' target='_blank'>Front End PM</a></div>";
    }	

add_action('fep_footer_note', 'fep_footer_credit' );

function fep_notification() 
		{
			if ( ! fep_current_user_can( 'access_message' ) )
				return;
			if ( fep_get_option('hide_notification',0) == 1 )
				return;
			
			$unread_count = fep_get_new_message_number();
			$sm = sprintf(_n('%s unread message', '%s unread messages', $unread_count, 'front-end-pm'), number_format_i18n($unread_count) );

				$show = '';
				
				$unread_ann_count = fep_get_user_announcement_count( 'unread' );
				$sa = sprintf(_n('%s unread announcement', '%s unread announcements', $unread_ann_count, 'front-end-pm'), number_format_i18n($unread_ann_count) );
	
			if ( $unread_count || $unread_ann_count ) {
				$show = __("You have", 'front-end-pm');
	
			if ( $unread_count )
				$show .= "<a href='".fep_query_url('messagebox')."'> $sm</a>";
	
			if ( $unread_count && $unread_ann_count )
				$show .= ' ' .__('and', 'front-end-pm');
	
			if ( $unread_ann_count )
				$show .= "<a href='".fep_query_url('announcements')."'> $sa</a>";
				
				}
				return apply_filters('fep_header_notification', $show);
		}
			

function fep_notification_div() {
	if ( ! fep_current_user_can( 'access_message' ) )
				return;
	if ( fep_get_option('hide_notification',0) == 1 )
				return;
				
	wp_enqueue_script( 'fep-notification-script' );
	$notification = fep_notification();
	if ( $notification )
	echo "<div id='fep-notification-bar'>$notification</div>";
	else
	echo "<div id='fep-notification-bar' style='display: none'></div>";
	}

add_action('wp_head', 'fep_notification_div', 99 );

function fep_notification_ajax() {

	if ( check_ajax_referer( 'fep-notification', 'token', false )) {
	
		$notification = fep_notification();
		if ( $notification )
		echo $notification;
	}
	wp_die();
	}

add_action('wp_ajax_fep_notification_ajax','fep_notification_ajax');
add_action('wp_ajax_nopriv_fep_notification_ajax','fep_notification_ajax');

function fep_auth_redirect(){
	if( !fep_page_id() || ! is_page( fep_page_id() ) ) {
		return;
	}
	
	do_action( 'fep_template_redirect' );
	
	if( apply_filters( 'fep_using_auth_redirect', false ) ) {
		auth_redirect();
	}
}
add_action('template_redirect','fep_auth_redirect', 99 );

add_filter( 'auth_redirect_scheme', 'fep_auth_redirect_scheme' );
function fep_auth_redirect_scheme( $scheme ){

	if( is_admin() || ! fep_page_id() || ! is_page( fep_page_id() ) ) {
		return $scheme;
	}
	
    return 'logged_in';
}

add_filter( 'map_meta_cap', 'fep_map_meta_cap', 10, 4 );

function fep_map_meta_cap( $caps, $cap, $user_id, $args ) {

	$our_caps = array( 'read_fep_message', 'edit_fep_message', 'delete_fep_message', 'read_fep_announcement', 'edit_fep_announcement', 'delete_fep_announcement' );
	
	/* If editing, deleting, or reading a message or announcement, get the post and post type object. */
	if ( in_array( $cap, $our_caps ) ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		/* Set an empty array for the caps. */
		$caps = array();
	} else {
		return $caps;
	}

	/* If editing a message or announcement, assign the required capability. */
	if ( 'edit_fep_message' == $cap || 'edit_fep_announcement' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	/* If deleting a message or announcement, assign the required capability. */
	elseif ( 'delete_fep_message' == $cap || 'delete_fep_announcement' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	/* If reading a private message or announcement, assign the required capability. */
	elseif ( 'read_fep_message' == $cap || 'read_fep_announcement' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}

	/* Return the capabilities required by the user. */
	return $caps;
}

function fep_array_trim( $array )
{
		
	if (!is_array( $array ))
       return trim( $array );
 
    return array_map('fep_array_trim',  $array );
}

function fep_is_pro(){
	return file_exists( FEP_PLUGIN_DIR. 'pro/pro-features.php' );
}

function fep_errors(){
    static $errors; // Will hold global variable safely
    return isset($errors) ? $errors : ($errors = new WP_Error());
}

function fep_success(){
    static $success; // Will hold global variable safely
    return isset($success) ? $success : ($success = new WP_Error());
}

function fep_info_output(){
    
	/* 
	// If conditions are met and errors exist:
    if(!fep_info()->get_error_codes()) return;
	
	$success = array();
	$info = array();
	$errors = array();
	
	// Loop error codes and display errors
    foreach( fep_info()->get_error_codes() as $code ){
	
        $data = fep_info()->get_error_data($code);
        // Display stuff here
		if( 'success' == $data ) {
			$success[] = fep_info()->get_error_message($code);
		} elseif( 'info' == $data ){
			$info[] = fep_info()->get_error_message($code);
		} else {
			$errors[] = fep_info()->get_error_message($code);
		}
    }
	*/
	
	$html = '';
	
	if( fep_success()->get_error_messages() ) {
		$html .= '<div class="fep-success">';
		foreach( fep_success()->get_error_messages() as $s){
			$html .= esc_html($s).'<br />';
		}
		$html .= '</div>';
	}

	if( fep_errors()->get_error_messages() ) {
		$html .= '<div class="fep-wp-error">';
		foreach( fep_errors()->get_error_messages() as $e){
			$html .= '<strong>' . __('Error', 'front-end-pm') . ': </strong>'.esc_html($e).'<br />';
		}
		$html .= '</div>';
	}
	
	return $html;
	
}

function fep_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) {
		$template_path = 'front-end-pm/';
	}
	// Set default plugin templates path.
	if ( ! $default_path ) {
		$default_path = FEP_PLUGIN_DIR . 'templates/'; // Path to the template folder
	}
	// Search template file in theme folder.
	$template = locate_template( $template_path . $template_name );
	
	// Get plugins template file.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}
	
	return apply_filters( 'fep_locate_template', $template, $template_name, $template_path, $default_path );
}

