<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Conversations extends HSD_Controller {
	const CONVERSATIONS_SHORTCODE = 'helpscout_desk';

	public static function init() {
		do_action( 'hsd_shortcode', self::CONVERSATIONS_SHORTCODE, array( __CLASS__, 'helpscout_desk' ) );
	}

	public static function helpscout_desk( $atts = array() ) {
		do_action( 'helpscout_desk' );
		if ( HelpScout_API::is_customer() ) {
			$mailbox_id = ( isset( $atts['mid'] ) ) ? $atts['mid'] : '' ;
			wp_enqueue_script( 'hsd' );
			wp_enqueue_style( 'hsd' );
			return '<div id="hsd_conversations_table" class="loading" data-mailbox-id='.$mailbox_id.'></div><!-- #hsd_conversations_table -->';
		} else {
			do_action( 'helpscout_desk_sc_table_not_customer' );
		}
	}

	/**
	 * Single conversation view
	 * @param  integer $post_id
	 * @return
	 */
	public static function single_conversation( $post_id = 0 ) {
		$mailbox_id = ( isset( $_REQUEST['mid'] ) && $_REQUEST['mid'] !== '' ) ? $_REQUEST['mid'] : 0 ;
		$refresh = ( isset( $_REQUEST['refresh_data'] ) ) ? $_REQUEST['refresh_data'] : 0 ;
		$refresh = apply_filters( 'refresh_hs_api_data', $refresh );
		$item = HelpScout_API::get_conversation( $_REQUEST['conversation_id'], $refresh, $mailbox_id );
		self::load_view( 'shortcodes/single_conversation', array(
				'item' => $item['item'],
				'threads' => $item['item']['threads'],
				'post_id' => $post_id,
		), true );
		exit();
	}

	/**
	 * Conversation table
	 * @param  integer $post_id
	 * @return
	 */
	public static function conversation_table( $post_id = 0 ) {
		$mailbox_id = ( isset( $_REQUEST['mid'] ) && $_REQUEST['mid'] !== '' ) ? $_REQUEST['mid'] : 0 ;
		$refresh = ( isset( $_REQUEST['refresh_data'] ) ) ? $_REQUEST['refresh_data'] : 0 ;
		$current_page = ( isset( $_REQUEST['current_page'] ) ) ? $_REQUEST['current_page'] : 0 ;
		$refresh = apply_filters( 'refresh_hs_api_data', $refresh );
		$conversations = HelpScout_API::get_conversations_by_user( 0, $refresh, $mailbox_id );
		self::load_view( 'shortcodes/conversation_table', array(
				'conversations' => $conversations,
				'post_id' => (int) $post_id,
				'current_page' => (int) $current_page,
		), true );
		exit();
	}
}
