#!/usr/bin/php -q
<?php
//  Use -q so that php doesn't print out the HTTP headers

error_reporting(0);
@ini_set('display_errors', 0);
ob_start();


//Change This if you have Multisite

$main_site_url = 'example.com'; //Main site without http(s)://

//------------------------------//


// Anything printed to STDOUT will be sent back to the sender as an error!
 //error_reporting(-1);
 //ini_set("display_errors", 1);

// Set a long timeout in case we're dealing with big files
set_time_limit(120);
ini_set('max_execution_time',120);


// Require the file with the FEP_Email_Parser class in it
require( dirname(__FILE__) .'/class-fep-email-parser.php');

$ep = new FEP_Email_Parser();

$message_key = $ep->message_key();
$b_id = $ep->blog_id();
$sender = $ep->sender_email();
$subject = $ep->subject();

if( ! $message_key || ! $sender || !$subject ){
	return;
}

if( $b_id && ! is_numeric( $b_id ) ){
	return;
}

if( $b_id ) {
	$_SERVER['HTTP_HOST'] = $main_site_url; //Main site without http(s)://
	$_SERVER['REQUEST_URI'] = '';
}

fep_load_wp_load();

if( is_multisite() && $b_id ) {
	$b_id = absint( $b_id );
	add_action( 'switch_blog', 'fep_switch_to_blog_cache_clear', 10, 2 );
	switch_to_blog( $b_id );
}

if( ! function_exists( 'fep_get_option' ) ) {
	return;
}

if( ! fep_get_option('ep_enable', 0 ) ) {
	return;
}

global $wpdb;

$message_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_message_key' AND meta_value = '%s' LIMIT 1", $message_key ) );

if( ! $message_id ){
	return;
}

$sender_id = fep_get_userdata( $sender, 'ID', 'email');

if( ! $sender_id ){
	return;
}

if( fep_is_user_blocked( fep_get_userdata( $sender_id, 'user_login', 'id') ) ){
	return;
}

$participants = get_post_meta( $message_id, '_participants' );

if( ! is_array( $participants ) || ! in_array( $sender_id, $participants ) )
	return;

if( fep_get_option('ep_clean_reply', 1 ) ) {
	$body = $ep->clean_body();
} else {
	$body = $ep->body();
}

$message = array(
	'fep_parent_id' => $message_id,
	'message_content' => $body,
	);
$override = array( 'post_author' => $sender_id );

$inserted_id = fep_send_message( $message, $override );

if( ! $inserted_id )
	return;

$attachments = $ep->attachments();

if( ! $attachments )
	return;
	
if ( ! fep_get_option('allow_attachment', 1) )
	return;

$fields = (int) fep_get_option('attachment_no', 4);

if( class_exists( 'Fep_Attachment' ) ){
	add_filter('upload_dir', array(Fep_Attachment::init(), 'upload_dir'));
}

$i = 0;
foreach( $attachments as $name => $contents ) {

	$mime = isset( $contents['mime'] ) ? $contents['mime'] : '';
	$content = isset( $contents['content'] ) ? $contents['content'] : '';
	
	if( !$mime || !in_array( $mime, get_allowed_mime_types() ) )
		continue;
		
	$att = wp_upload_bits( $name, null, $content);
	
	if( ! isset( $att['file'] ) || ! isset( $att['url'] ) || ! isset( $att['type'] ) )
		continue;
	
	$attachment = array(
		'guid'           => $att['url'], 
		'post_mime_type' => $att['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $att['url'] ) ),
		'post_content'   => '',
		'post_author'	=> $sender_id,
		'post_status'    => 'inherit'
	);
	
	// Insert the attachment.
	wp_insert_attachment( $attachment, $att['file'], $inserted_id );
		
	$i++;
	
	if( $i >= $fields )
		break;
}
if( class_exists( 'Fep_Attachment' ) ){
	remove_filter('upload_dir', array(Fep_Attachment::init(), 'upload_dir'));
}

function fep_load_wp_load() {
    $dir = dirname(dirname(__FILE__));
    do {
        if( file_exists( $dir . '/wp-load.php' ) ) {
            require( $dir . '/wp-load.php' );
			return;
        }
    } while( $dir = dirname( $dir ) );
}

function fep_switch_to_blog_cache_clear( $blog_id, $prev_blog_id = 0 ) {
    if ( $blog_id === $prev_blog_id )
        return;

    wp_cache_delete( 'notoptions', 'options' );
    wp_cache_delete( 'alloptions', 'options' );
}

//clean all levels of output buffering
while (ob_get_level()) {
	ob_end_clean();
}
