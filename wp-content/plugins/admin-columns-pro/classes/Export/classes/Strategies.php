<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all available ACP_Export_Strategy instances
 */
class ACP_Export_Strategies {

	/**
	 * Registered list screens supporting export functionality
	 *
	 * @since 1.0
	 * @var ACP_Export_Strategy[]
	 */
	protected static $strategies;

	/**
	 * @since 1.0
	 *
	 * @param ACP_Export_ListScreen $list_screen
	 */
	public static function register_strategy( ACP_Export_Strategy $strategy ) {
		self::$strategies[ $strategy->get_list_screen()->get_key() ] = $strategy;
	}

	/**
	 * @since 1.0
	 * @return ACP_Export_Strategy|null
	 */
	public static function get_strategy( $key ) {
		if ( isset( self::$strategies[ $key ] ) ) {
			return self::$strategies[ $key ];
		}

		return null;
	}

}
