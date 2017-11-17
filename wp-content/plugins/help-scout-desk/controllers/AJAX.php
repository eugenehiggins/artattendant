<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_AJAX extends HSD_Controller {

	public static function init() {
		add_filter( 'hsd_scripts_localization', array( __CLASS__, 'add_conversation_id' ) );

		// AJAX
		add_action( 'wp_ajax_hsd_shortcodes', array( __CLASS__, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_nopriv_hsd_shortcodes', array( __CLASS__, 'handle_ajax_request' ) );
	}

	/**
	 * Handle all HSD ajax requests
	 * @return
	 */
	public static function handle_ajax_request() {
		$nonce = $_REQUEST['shortcodes_nonce'];
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::ajax_fail( 'Not going to fall for it!' );}

		if ( ! isset( $_REQUEST['type'] ) ) {
			self::ajax_fail( 'Forget something?' );
		}
		define( 'DONOTCACHEPAGE', true );
		$type = $_REQUEST['type'];
		if ( method_exists( 'HSD_Conversations', $type ) ) { // TODO register callbacks
			call_user_func( array( 'HSD_Conversations', $type ), $_REQUEST['post_id'] );
		}
	}

	/**
	 * Add the conversation id to the js object
	 * @param array $hsd_js_object
	 */
	public static function add_conversation_id( $hsd_js_object ) {
		if ( isset( $_GET['conversation_id'] ) ) {
			$hsd_js_object += array(
				'conversation_id' => $_GET['conversation_id'],
			);
		}
		return $hsd_js_object;
	}
}
