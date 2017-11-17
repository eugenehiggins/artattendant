<?php
/**
 * Deprecated functions
 *
 * @package     EDD_Commissions
 * @copyright   Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy function that loaded EDD Commissions. Keeping it available for `function_exists` checks.
 * Will throw a deprecated notice if run, but will return the instance.
 *
 * @since 1.0
 * @deprecated 3.4
 */
function edd_commissions_load() {
	$backtrace = debug_backtrace();
	_edd_deprecated_function( 'edd_commissions_load', '3.4', 'edd_commissions', $backtrace );

	return edd_commissions();
}