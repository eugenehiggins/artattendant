<?php

remove_filter( 'the_title','edd_microdata_title' );


add_action('fep_action_message_after_send', 'anagram_fep_save_message', 10, 3 );
function anagram_fep_save_message( $message_id, $message, $update ){

	if( ! empty($_REQUEST['artwork_id'] ) ) { //BACK END message_to return login of participants

		add_post_meta( $message_id, '_artwork_id', $_REQUEST['artwork_id'] );

		unset( $_REQUEST['artwork_id'] );
	}
		//$participants = get_post_meta( $postid, '_participants' );

}

function anagram_user_details($post_ID)  {
	$auth = get_post($post_ID); // gets author from post
	$authid = $auth->post_author; // gets author id for the post
	//$user_email = get_the_author_meta('user_email',$authid); // retrieve user email
	//$display_name = get_the_author_meta('display_name',$authid); // retrieve firstname
	return $authid;
}


//fep_success()->add( 'publish', __("custom note.", 'front-end-pm') );

//add_action( 'fep_form_field_output_', $field, $errors );
add_filter( 'fep_form_fields', 'anagram_cus_message' );
function anagram_cus_message( $fields )
{

	if(is_singular( 'download' )){

			$fields['artwork_id'] = array(
				'label'       => false,
				'type'        => 'hidden',
				//'required'    => true,
				'placeholder' => '',
				'priority'    => 0,
				'value'     => get_the_ID(),
				//'where'	=> array( 'newmessage', 'reply' )
			);
	}else{



	};

    return $fields;
}





/*

		$legends = array(
			'subject' => array(
				'description' => __('Subject', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_title ) ? $post->post_title : ''
				),
			'message' => array(
				'description' => __('Full Message', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_content ) ? $post->post_content : ''
				),
			'message_url' => array(
				'description' => __('URL of message', 'front-end-pm'),
				'where' => array( 'newmessage', 'reply' ),
				'replace_with' => ! empty( $post->ID ) ? fep_query_url( 'viewmessage', array( 'id' => $post->ID ) ) : ''
				),
			'announcement_url' => array(
				'description' => __('URL of announcement', 'front-end-pm'),
				'where' => 'announcement',
				'replace_with' => ! empty( $post->ID ) ? fep_query_url( 'viewannouncement', array( 'id' => $post->ID ) ) : ''
				),
			'sender' => array(
				'description' => __('Sender', 'front-end-pm'),
				'replace_with' => ! empty( $post->post_author ) ? fep_get_userdata( $post->post_author, 'display_name', 'id' ) : ''
				),
			'receiver' => array(
				'description' => __('Receiver', 'front-end-pm'),
				'replace_with' => fep_get_userdata( $user_email, 'display_name', 'email' )
				),
			'site_title' => array(
				'description' => __('Website title', 'front-end-pm'),
				'replace_with' => get_bloginfo('name')
				),
			'site_url' => array(
				'description' => __('Website URL', 'front-end-pm'),
				'replace_with' => get_bloginfo('url')
				),
			);
		$legends = apply_filters( 'fep_eb_email_legends', $legends, $post, $user_email );
*/


add_filter( 'fep_form_fields', 'fep_cus_fep_form_fields' );
function fep_cus_fep_form_fields( $fields )
{
    unset( $fields['message_content']['minlength'] );
    unset( $fields['message_title']['minlength'] );
    return $fields;
}


add_filter( 'fep_menu_buttons', 'fep_cus_fep_menu_buttons' );
function fep_cus_fep_menu_buttons( $menu )
{
    //unset( $menu['settings'] );
    unset( $menu['announcements'] );
    return $menu;
}