<?php
	/*-----------------------------------------------------------------------------------*/
	/* This file will be referenced every time a template/page loads on your Wordpress site
	/* This is the place to define custom fxns and specialty code
	/*-----------------------------------------------------------------------------------*/

// Define the version so we can easily replace it throughout the theme
define( 'anagramtheme_version', 1.0 );




if ( ! function_exists( 'anagramtheme_setup' ) ) :
/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function anagramtheme_setup() {
    global $cap, $content_width;


		if ( ! isset( $content_width ) ) {
			$content_width = 906;
		}


	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );


       add_editor_style('/css/editor-styles.css');

    if ( function_exists( 'add_theme_support' ) ) {

		/**
		 * Add default posts and comments RSS feed links to head
		*/
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Enable support for Post Thumbnails on posts and pages
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		*/
		add_theme_support( 'post-thumbnails' );

			//set_post_thumbnail_size( 250, 160, true );
			//add_image_size( 'tiny', 75, '', false );
			//add_image_size( 'block-image', 300, 170, true );

		add_filter( 'image_size_names_choose', 'anagram_custom_sizes' );

			function anagram_custom_sizes( $sizes ) {
			    return array_merge( $sizes, array(
			        'post-thumbnail' => __('Post Thumbnail'),
			    ) );
			}

		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
    }


	/**
	 * This theme uses wp_nav_menu() in one location.
	*/
    register_nav_menus( array(
        'primary'  => __( 'Header Menu', 'anagramtheme' ),
        'user_nav'  => __( 'User Menu', 'anagramtheme' ),
    ) );

}
endif; // anagramtheme_setup
add_action( 'after_setup_theme', 'anagramtheme_setup' );


/*Custom Tiny MCS buttons*/
add_filter( 'mce_buttons', 'anagram_mce_buttons' );
function anagram_mce_buttons( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}
function anagram_tinymce_buttons($buttons)
 {
	//Remove the format dropdown select and text color selector
	$remove = array('formatselect');

	return array_diff($buttons,$remove);
 }
add_filter('mce_buttons_2','anagram_tinymce_buttons');

add_filter( 'tiny_mce_before_init', 'anagram_mce_before_init' );
function anagram_mce_before_init( $settings ) {
	//$settings['theme_advanced_blockformats'] = 'p,a,div,span,h1,h2,h3,h4,h5,h6,tr,';
	$settings['theme_advanced_disable'] = 'formatselect';

    $style_formats = array(
    	array(
    		'title' => 'Button',
    		'selector' => 'a',
    		'classes' => 'btn btn-default'
    	),
    	array(
    		'title' => 'Main Header',
    		'block' => 'h2'
    	),
    	array(
    		'title' => 'Sub Header',
    		'block' => 'h3'
    	),
    	array(
    		'title' => 'Small Header',
    		'block' => 'h4'
    	),
    	array(
    		'title' => 'Blue Header',
    		'block' => 'h3',
    		'classes' => 'blue'
    	),
    	array(
    		'title' => 'Black Bold',
    		'inline' => 'span',
    		'classes' => 'black-bold'
    	)/*,
        array(
        	'title' => 'Bold Red Text',
        	'inline' => 'span',
        	'styles' => array(
        		'color' => '#f00',
        		'fontWeight' => 'bold'
        	)
        )*/
    );

    $settings['style_formats'] = json_encode( $style_formats );

    return $settings;

}






/**
 * Register widgetized area and update sidebar with default widgets
 */
function anagramtheme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'anagramtheme' ),
		'id'            => 'sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'anagramtheme_widgets_init' );

/*-----------------------------------------------------------------------------------*/
/* Enqueue Styles and Scripts
/*-----------------------------------------------------------------------------------*/

function anagramtheme_scripts()  {

	// load bootstrap
	//wp_enqueue_style( 'anagramtheme-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css' );

	// get the theme directory style.css and link to it in the header
	wp_enqueue_style( 'anagramtheme-style', get_stylesheet_directory_uri().'/style.css', array(), filemtime( get_stylesheet_directory().'/style.css') );

	//Load google fonts
	$anagram_google_fonts = array(
	    'family' => 'Strait|Roboto:400,300,300italic,400italic,100'
	);
   // wp_enqueue_style('anagramtheme-google-fonts', add_query_arg( $anagram_google_fonts, "https://fonts.googleapis.com/css" ), array(), null );
    wp_enqueue_style( 'anagramtheme2-google-fonts', 'https://fonts.googleapis.com/css?family=Muli:300,400,600,700|Palanquin:400,700', false );

	// load awesome font
	  wp_enqueue_script('anagram-fontawesome', 'https://use.fontawesome.com/601d722179.js' );

    // load bootstrap js
    wp_enqueue_script('anagramtheme-bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js', array('jquery') );


			wp_enqueue_script( 'jquery-masonry' );
			wp_enqueue_script( 'imagesloaded' );

			wp_enqueue_script( 'jquery-ui-sortable' );


	wp_enqueue_script('anagramfilters-plugins', (get_template_directory_uri()."/js/filter-custom.js"),array('jquery'),filemtime( get_stylesheet_directory().'/js/filter-custom.js'),true);

	// load plugins
	wp_enqueue_script('anagramtheme-plugins', (get_template_directory_uri()."/js/theme-plugins.js"),array('jquery'),filemtime( get_stylesheet_directory().'/js/theme-plugins.js'),true);

	// add theme scripts
	wp_enqueue_script('anagramtheme-scripts', (get_template_directory_uri()."/js/theme-scripts.js"),array('jquery'),filemtime( get_stylesheet_directory().'/js/theme-scripts.js'),true);

}
add_action( 'wp_enqueue_scripts', 'anagramtheme_scripts' ); // Register this fxn and allow Wordpress to call it automatcally in the header



function my_custom_js() {
    echo '<script type="text/javascript">
window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=t.forceSSL||"https:"===document.location.protocol,a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=(r?"https:":"http:")+"//cdn.heapanalytics.com/js/heap-"+e+".js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n);for(var o=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","removeEventProperty","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=o(p[c])};
      heap.load("3004042690");
</script>';
}

// Add hook for front-end <head></head>
add_action('wp_head', 'my_custom_js');

function frontpage_js() {
    wp_enqueue_script('frontpage-scripts', (get_template_directory_uri()."/js/frontpage-scripts.js"),array('jquery'),filemtime( get_stylesheet_directory().'/js/frontpage-scripts.js'),true);
    wp_enqueue_style( 'aos-css', (get_template_directory_uri()."/node_modules/aos/dist/aos.css"), false );

    wp_enqueue_script('aos-script', (get_template_directory_uri()."/node_modules/aos/dist/aos.js"),array(),2.2,true);
}
add_action( 'wp_enqueue_scripts' , 'frontpage_js');


/**
 * Custom Theme functions
 */

	include_once('inc/anagram-customs.php');



	include_once('inc/anagram-filters.php');


	include_once('inc/edd-custom.php');

		include_once('inc/anagram-fes.php');


	include_once('inc/custom-messages.php');

/**
 * Load custom WordPress nav walker.
 */

include_once('inc/bootstrap-wp-navwalker.php');


/**
 * Load custom Gallery
 */
include_once('inc/anagram-gallery.php');

include_once('inc/php_gravity_forms_bootstrap.php');


/*Load default settings on theme activation*/
add_action('after_switch_theme', 'anagram_defaults_settings');

function anagram_defaults_settings() {
    global $wpdb, $wp_rewrite, $table_prefix;

    /* * ANAGRAM ADDITIONS * Customize Some Options */

    //Disable Avatar Call
	update_option( 'show_avatars', 0 );
	update_option( 'avatar_default', 'blank' );

	//Dsiable pings
	update_option( 'default_ping_status', 'closed' );

	//Disable User registration
	update_option( 'users_can_register', 0 );

	//Set default lang
	update_option( 'WPLANG', '' );


	//Add purchase keys to default plugins
	update_option( 'cpupdate_cac-pro', '99e74138-8a53-4c16-abe0-87cbd181f597' );
	update_option( 'acf_pro_license', 'YToyOntzOjM6ImtleSI7czo3MjoiYjNKa1pYSmZhV1E5TXpJNU56QjhkSGx3WlQxa1pYWmxiRzl3WlhKOFpHRjBaVDB5TURFMExUQTNMVEEzSURFMk9qQTBPalU1IjtzOjM6InVybCI7czozMjoiaHR0cDovL2Rldi4xOTIuMTY4LjIyMi41MC54aXAuaW8iO30=' );
	update_option( 'rg_gforms_key', '38fd93100c2666e5ecd12efa6ced7e8d' );
	update_option( 'rg_gforms_enable_html5', '1' );

    // Set Timezone
    $timezone = "America/Denver";
    update_option( 'timezone_string', $timezone );

	//site slogan
	update_option( 'blogdescription', 'Custom site built by ANAGR.AM' );

    // Start of the Week
    // 0 is Sunday, 1 is Monday and so on
    update_option( 'start_of_week', 0 );

    // Disable Smilies
    update_option( 'use_smilies', 0 );

	//Set default Default file insert to none instead of attachment page
	update_option( 'image_default_link_type', 'none' );

    // Don't Organize Uploads by Date
    update_option('uploads_use_yearmonth_folders',1);


    update_option( 'thumbnail_size_w', 125 );
	update_option( 'thumbnail_size_h', 125 );
	update_option( 'thumbnail_crop', '1' );

	update_option( 'medium_size_w', 450 );
	update_option( 'medium_size_h', 9999 );

	update_option( 'large_size_w', 1140 );
	update_option( 'large_size_h', 9999 );

    // Update Permalinks
    update_option( 'selection','custom' );
    update_option( 'permalink_structure','/%postname%/' );
    $wp_rewrite->init();
    $wp_rewrite->flush_rules();

}


