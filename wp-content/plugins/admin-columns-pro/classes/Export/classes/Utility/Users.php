<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility functions for users
 *
 * @since 1.0
 */
class ACP_Export_Utility_Users {

	/**
	 * Retrieve the encryption key belonging to a user. Creates a new key if no key exist yet for the user
	 *
	 * @since 1.0
	 *
	 * @param int $user_id Optional. ID of user to retrieve the key for
	 *
	 * @return string User's encryption key
	 */
	public static function get_user_encryption_key( $user_id = 0 ) {
		$use_user_id = $user_id ? $user_id : get_current_user_id();
		$encryption_key = get_user_meta( $use_user_id, '_acp_export_ek', true );

		if ( ! $encryption_key ) {
			$encryption_key = ACP_Export_Utility_Encryption::generate_key();
			update_user_meta( $use_user_id, '_acp_export_ek', $encryption_key );
		}

		return $encryption_key;
	}

}
