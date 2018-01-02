<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility functions for encryption
 *
 * @since 1.0
 */
class ACP_Export_Utility_Encryption {

	/**
	 * Generate a random encryption key
	 *
	 * @since 1.0
	 *
	 * @return string Generated encryption key
	 */
	public static function generate_key() {
		return md5( microtime( true ) . wp_rand() );
	}

}
