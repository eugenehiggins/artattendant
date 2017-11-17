<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Tags extends HSD_Controller {
	const TAGS_OPTION = 'help_scout_tags_v3';
	const TAGS_CACHE = 'help_scout_tags_cache_v3';
	protected static $tags;

	public static function init() {
		self::$tags = get_option( self::TAGS_OPTION, array() );

		// Register Settings
		if ( is_admin() ) {
			self::register_settings();
		}

		// Enqueue
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_resources' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ), 20 );

		// front-end view
		add_action( 'hsd_form_message', array( __CLASS__, 'select_tags' ) );
		add_action( 'hsd_single_conversation_status', array( __CLASS__, 'converstation_tags' ) );

		add_filter( 'hsd_create_conversation_fields', array( __CLASS__, 'maybe_add_tags_to_conversation' ) );
	}

	/////////////////////////////
	// Process Form Submission //
	/////////////////////////////

	public static function maybe_add_tags_to_conversation( $conversation = array() ) {
		if ( ! isset( $_POST['hs_thread_type'] ) ) {
			return $conversation;
		}
		$selected_id = $_POST['hs_thread_type'];
		$hs_tags = wp_list_pluck( self::get_tags_from_help_scout(), 'tag', 'id' );
		if ( ! isset( $hs_tags[ $selected_id ] ) ) {
			return $conversation;
		}
		$conversation['tags'] = array( $hs_tags[ $selected_id ] );
		return $conversation;
	}


	///////////////
	// front end //
	///////////////

	public static function select_tags( $mailbox_id ) {
		if ( empty( self::$tags ) ) {
			return;
		}

		$conversation_view = ( isset( $_GET['conversation_id'] ) && '' !== $_GET['conversation_id'] );

		if ( $conversation_view ) {
			return;
		}

		$tags = self::get_tags_from_help_scout();
		foreach ( $tags as $key => $tag ) {
			if ( ! in_array( $tag->id, self::$tags )  ) {
				unset( $tags[ $key ] );
			}
		}
		print self::load_view_to_string( 'section/tag-selection', array(
				'tags' => $tags,
		), true );
	}

	public static function get_converstation_tags( $conversation = array() ) {
		if ( empty( self::$tags ) ) {
			return;
		}

		if ( ! isset( $conversation['tags'] ) ) {
			return;
		}

		if ( empty( $conversation['tags'] ) ) {
			return;
		}

		$converation_tags = array();
		$hs_tags = wp_list_pluck( self::get_tags_from_help_scout(), 'tag', 'id' );
		foreach ( self::$tags as $tag_id ) {
			// filter out acceptable tags
			if ( isset( $hs_tags[ $tag_id ] ) ) {
				$tag_name = $hs_tags[ $tag_id ];
				// filter out conversation tags
				if ( ( array_search( $tag_name, $conversation['tags'] ) ) !== false ) {
					$converation_tags[ $tag_id ] = $tag_name;
				}
			}
		}

		return $converation_tags;

	}

	public static function converstation_tags( $conversation = array() ) {
		$conversation_tags = self::get_converstation_tags( $conversation );

		print self::load_view_to_string( 'section/conversation-tags', array(
				'conversation' => $conversation,
				'converation_tags' => $conversation_tags,
		), true );
	}

	/////////////
	// Enqueue //
	/////////////

	public static function register_resources() {
		if ( HSD_FREE ) {
			return;
		}
		// Select2
		wp_register_style( 'select2_4.0_css', HSD_URL . '/resources/plugins/select2/css/select2.min.css', null, self::HSD_VERSION, false );
		wp_register_script( 'select2_4.0', HSD_URL . '/resources/plugins/select2/js/select2.min.js', array( 'jquery' ), self::HSD_VERSION, false );
	}

	public static function admin_enqueue() {
		// doc admin templates
		$screen = get_current_screen();
		if ( 'sprout-apps_page_sprout-apps/help_scout_desk' === $screen->id ) {
			wp_enqueue_script( 'select2_4.0' );
			wp_enqueue_style( 'select2_4.0_css' );
		}
	}


	//////////////
	// Settings //
	//////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {

		// Settings
		$settings = array(
			'hsd_tag_options' => array(
				'weight' => 20.1,
				'settings' => array(
					self::TAGS_OPTION => array(
						'label' => __( 'Tags' , 'help-scout-desk' ),
						'option' => array(
							'description' => __( 'Tags will be synced after checking this box and saving below.', 'help-scout-desk' ),
							'type' => 'bypass',
							'output' => self::show_tag_options(),
						),
						'sanitize_callback' => array( __CLASS__, 'maybe_refresh_tags_cache' ),
					),
				),
			),
		);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );
	}

	public static function show_tag_options() {

		$view = sprintf( '<select name="%s[]" multiple class="select2">', self::TAGS_OPTION );
		$tags = self::get_tags_from_help_scout();
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $key => $tag ) {
				$view .= sprintf( '<option value="%s" %s>%s</option>', $tag->id, selected( in_array( $tag->id, self::$tags ), true, false ),$tag->tag );
			}
		}
		$view .= '</select><script type="text/javascript">jQuery(".select2").select2();</script>';
		$view .= sprintf( '<p class="description">%s</p>', __( 'Select the tags your customers have available to choose from.', 'help-scout-desk' ) );

		$view .= sprintf( '<p><label><input type="checkbox" name="hsd_reset_tag_cache" value="1" id="hsd_reset_tag_cache" />%s</label></p>', __( 'Sync tags with the Help Scout', 'help-scout-desk' ) );
		return $view;
	}

	public static function maybe_refresh_tags_cache( $option = '' ) {
		if ( isset( $_POST['hsd_reset_tag_cache'] ) ) {
			self::get_tags_from_help_scout( true );
		}
		if ( '' == $option ) {
			return array();
		}
		return $option;
	}

	/////////
	// API //
	/////////

	public static function get_tags_from_help_scout( $flush = false ) {
		$tag_cache = get_option( self::TAGS_CACHE, false );
		if ( $tag_cache && ! $flush ) {
			return $tag_cache;
		}

		$tag_array = array();
		$tags = self::get_tags_from_help_scout_by_page( 1, $flush );

		if ( empty( $tags->items ) ) {
			update_option( self::TAGS_CACHE, $tag_array );
			return;
		}

		for ( $i = 1; $i <= $tags->pages; $i++ ) {
			if ( $i === (int) $tags->page ) {
				$tag_array = array_merge( $tags->items, $tag_array );
			} else {
				$paged_tags = self::get_tags_from_help_scout_by_page( $i, $flush );
				$tag_array = array_merge( $paged_tags->items, $tag_array );
			}
		}
		update_option( self::TAGS_CACHE, $tag_array );
		return $tag_array;
	}

	public static function get_tags_from_help_scout_by_page( $page_id = 1, $flush = false ) {
		$response = HelpScout_API::api_request( 'tags', '?page=' . $page_id, $flush );
		return json_decode( $response );
	}
}
