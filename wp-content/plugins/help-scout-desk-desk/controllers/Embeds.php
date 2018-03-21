<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Embeds extends HSD_Controller {
	const THREAD_SHORTCODE = 'hsd_thread';

	public static function init() {
		do_action( 'hsd_shortcode', self::THREAD_SHORTCODE, array( __CLASS__, 'show_thread' ) );
	}

	/**
	 * Show a single conversation
	 * @param  array $atts
	 * @param  string $content used to show a message after a message is received.
	 * @return
	 */
	public static function show_thread( $atts, $content = '' ) {
		$mailbox_id = ( isset( $atts['mid'] ) ) ? $atts['mid'] : HSD_Settings::get_mailbox();
		$conversation_id = ( isset( $atts['id'] ) ) ? $atts['id'] : 0;

		if ( ! $conversation_id ) {
			return __( 'No conversation id was provided, e.g. [hsd_conversation id="781242"].', 'help-scout-desk' );
		}
		$refresh = ( isset( $_REQUEST['refresh_data'] ) ) ? $_REQUEST['refresh_data'] : 0;
		$refresh = apply_filters( 'refresh_hs_api_data', $refresh );
		$item = HelpScout_API::get_conversation( $conversation_id, $refresh, $mailbox_id );
		return self::load_view_to_string( 'shortcodes/single_conversation_embed', array(
				'item' => $item['item'],
				'threads' => $item['item']['threads'],
		), true );
	}
}
