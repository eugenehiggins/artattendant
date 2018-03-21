<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ACA_ACF_API
 *
 * Interface to the ACF API that works across the free and pro version
 */
class ACA_ACF_API {

	/**
	 * @var string
	 */
	protected static $version;

	/**
	 * @return string
	 */
	protected static function get_version() {
		if ( null === self::$version ) {
			self::set_version();
		}

		return self::$version;
	}

	protected static function set_version() {
		self::$version = function_exists( 'acf_get_setting' )
			? acf_get_setting( 'version' ) // Pro
			: acf()->get_info( 'version' ); // Free
	}

	/**
	 * @return bool
	 */
	public static function is_free() {
		return -1 === version_compare( self::get_version(), 5 );
	}

	/**
	 * @param string $field_hash
	 *
	 * @return mixed
	 */
	public static function get_field( $field_hash ) {
		if ( ! $field_hash ) {
			return false;
		}

		if ( self::is_free() ) {
			return get_field_object( $field_hash );
		}

		return acf_get_field( $field_hash );
	}

}
