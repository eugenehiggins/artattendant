<?php
/*
Plugin Name: 		Admin Columns Pro - Advanced Custom Fields (ACF)
Version: 			2.2.1
Description: 		Supercharges Admin Columns Pro with very cool ACF columns.
Author: 			Admin Columns
Author URI: 		https://www.admincolumns.com
Plugin URI: 		https://www.admincolumns.com
Text Domain: 		codepress-admin-columns
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_admin() ) {
	return;
}

require_once 'classes/Dependencies.php';

function aca_acf_loader() {
	$dependencies = new ACA_ACF_Dependencies( plugin_basename( __FILE__ ) );

	if ( ! class_exists( 'acf', false ) ) {
		$dependencies->add_missing( __( 'Advanced Custom Fields' ), $dependencies->get_search_url( 'Advanced Custom Fields' ) );

		return;
	}

	if ( $dependencies->is_missing_acp( '4.2' ) ) {
		return;
	}

	AC()->autoloader()->register_prefix( 'ACA_ACF_', plugin_dir_path( __FILE__ ) . 'classes/' );

	ac_addon_acf()->register();
}

add_action( 'after_setup_theme', 'aca_acf_loader' );

function ac_addon_acf() {
	return new ACA_ACF_Plugin( __FILE__ );
}