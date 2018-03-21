#!/usr/bin/php -q
<?php
//  Use -q so that php doesn't print out the HTTP headers

ob_start();

error_reporting(0);
@ini_set( 'display_errors', 0);
@ini_set( 'max_execution_time', 300 );
@ini_set( 'memory_limit', '256M' );


if ( ! ini_get( 'safe_mode' ) ){
	// Set a long timeout in case we're dealing with big files
	@set_time_limit( 300 );
}

//Change This if you have Multisite

$main_site_url = ''; //Main site without http(s)://

//------------------------------//

// Anything printed to STDOUT will be sent back to the sender as an error!
 //error_reporting(-1);
 //ini_set("display_errors", 1);

if( $main_site_url ) {
	$_SERVER['HTTP_HOST'] = $main_site_url; //Main site without http(s)://
	//$_SERVER['REQUEST_URI'] = '';
}

empty( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] = 'localhost';
empty( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] = '/';
empty( $_SERVER['DOCUMENT_ROOT'] ) && $_SERVER['DOCUMENT_ROOT'] = __DIR__;
empty( $_SERVER['SCRIPT_NAME'] ) && $_SERVER['SCRIPT_NAME'] = '';
empty( $_SERVER['PHP_SELF'] ) && $_SERVER['PHP_SELF'] = '';

fep_load_wp_load();

if( defined( 'FEP_PLUGIN_DIR' ) && file_exists( FEP_PLUGIN_DIR . 'pro/fep-email-parser/class-fep-email-pipe.php' ) ){
	require_once( FEP_PLUGIN_DIR . 'pro/fep-email-parser/class-fep-email-pipe.php' );
	new FEP_Email_Pipe;
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

//clean all levels of output buffering
while (ob_get_level()) {
	ob_end_clean();
}
