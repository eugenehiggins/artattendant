<?php
/**
Plugin Name: artAttendant API
 */
add_action( 'plugins_loaded', function() {
	spl_autoload_register( function ( $class ) {
		$prefix = 'artattendant\\artattendant_api\\';
		$base_dir = dirname( __FILE__ ) . '/src/' ;
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0) {

			return;
		}
		$relative_class = substr($class, $len);
		$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

		if ( file_exists( $file )) {
			require $file;
		}
	});


	$api_namespace = 'artattendant_api';
	$version = 'v2';
	new \artattendant\artattendant_api\boot( $api_namespace, $version );


});


