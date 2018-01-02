<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ACP_Export_ListScreens
 *
 * Contains all available ACP_Export_ListScreen instances
 */
class ACP_Export_ListScreens {

	/**
	 * Registered list screens supporting export functionality
	 *
	 * @since 1.0
	 * @var ACP_Export_ListScreen[]
	 */
	protected static $list_screens;

	/**
	 * @since 1.0
	 *
	 * @param ACP_Export_ListScreen $list_screen
	 */
	public static function register_list_screen( ACP_Export_ListScreen $list_screen ) {
		self::$list_screens[ $list_screen->get_list_screen()->get_key() ] = $list_screen;
	}

	/**
	 * @since 1.0
	 * @return ACP_Export_ListScreen|null
	 */
	public static function get_list_screen( $key ) {
		if ( isset( self::$list_screens[ $key ] ) ) {
			return self::$list_screens[ $key ];
		}

		return null;
	}

}
