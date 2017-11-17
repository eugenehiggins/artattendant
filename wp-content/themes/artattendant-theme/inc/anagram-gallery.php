<?php
/**
 * The class.
 */
class Anagram_Gallery_Setting {
	/**
	 * Stores the class instance.
	 *
	 * @var Custom_Gallery_Setting
	 */
	private static $instance = null;


	/**
	 * Returns the instance of this class.
	 *
	 * It's a singleton class.
	 *
	 * @return Custom_Gallery_Setting The instance
	 */
	public static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Initialises the plugin.
	 */
	public function init_gallery_code() {
		$this->init_hooks();
	}

	/**
	 * Initialises the WP actions.
	 *  - admin_print_scripts
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_media', array( $this, 'wp_enqueue_media' ) );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
	}


	/**
	 * Enqueues the script.
	 */
	public function wp_enqueue_media() {
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;

		wp_enqueue_script(
			'anagram-gallery-settings',
			get_stylesheet_directory_uri(). '/inc/anagram-gallery-setting.js',
			array( 'media-views' )
		);

	}

	/**
	 * Outputs the view template with the custom setting.
	 */
	function print_media_templates() {
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;

		?>
		<script type="text/html" id="tmpl-anagram-gallery-setting">
			<label class="setting">
				<span>Type</span>
				<select class="type" name="type" data-setting="type">
					<?php

					$types = apply_filters( 'gallery_type_choose', array(
						'default' => __( 'Default' ),
						'circle'    => __( 'Circles' ),
						'square'     => __( 'Square Tiles' ),
						'rectangular'     => __( 'Tiled Mosaic' ),
					) );

					foreach ( $types as $value => $name ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'default' ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>
					<?php } ?>
				</select>
			</label>
		</script>
		<?php
	}

}

// Put your hands up...
add_action( 'admin_init', array( Anagram_Gallery_Setting::get_instance(), 'init_gallery_code' ), 20 );



function anagram_gallery_default_type_set_link( $settings ) {
    $settings['galleryDefaults']['link'] = 'file';
    return $settings;
}
add_filter( 'media_view_settings', 'anagram_gallery_default_type_set_link');


add_shortcode( 'gallery', 'anagram_masonry_shortcode' );
function anagram_masonry_shortcode( $attr ) {

	global $post, $content_width;

	$show_controls = false;

	$gallery_count =0;

	$full_size = 'large';

	$thumbnail_width = 300;

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	$args = shortcode_atts(array(
		'id' 		=> intval($post->ID),
		'columns'    => 3,
		'itemclass'    => 'col-sm-4',
		'size'       => 'thumbnail',
		'order'      => 'DESC',
		'orderby'    => 'menu_order ID',
		'include'    => '',
		'exclude'    => '',
		'type'       => '',
	), $attr);

	$gallery_count += 1;
	$galley_id = intval($post->ID) . '_' . $gallery_count;


	$output_buffer='';

	    if ( !empty($args['include']) ) {

			//"ids" == "inc"

			$include = preg_replace( '/[^0-9,]+/', '', $args['include'] );
			$_attachments = get_posts( array('include' => $args['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $args['order'], 'orderby' => $args['orderby']) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}

		} elseif ( !empty($args['exclude']) ) {
			$exclude = preg_replace( '/[^0-9,]+/', '', $args['exclude'] );
			$attachments = get_children( array('post_parent' => $args['id'], 'exclude' => $args['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $args['order'], 'orderby' => $args['orderby']) );
		} else {

			$attachments = get_children( array('post_parent' => $args['id'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $args['order'], 'orderby' => $args['orderby']) );

		}


		$columns = intval($args['columns']);

        $itemwidth = $columns > 0 ? floor(100/$columns) : 100; //gets precentage

		$gutter = 15;
		$number = (((($content_width - (($columns*$gutter) - $gutter)) / $columns) / $content_width) * 100);
		//(((($container.width() - ((columnCount*gutter) - gutter)) / columnCount) / $container.width()) * 100)+'%';

        $imgwidth = intval(1140/$args['columns']);

		// Default to rectangular is tiled galleries are checked
		$imgclass = '';
		$imgheight = null;
		$crop = false;
		if ( 'circle' == $args['type'] ){
			$imgclass = 'img-circle';
			$imgheight = $imgwidth;
			$crop = true;
		}else if('square' == $args['type'] ){
			$imgheight = $imgwidth;
			$crop = true;

		}else{

		}


		/*Bootsrap columns*/
		//$bootcolumns = 12/$columns;
		//col-sm-'.$bootcolumns.'"

		$size_class = sanitize_html_class( $args['size'] );
		$output_buffer .=' <div style="clear:both"></div>

		<div id="container'.$galley_id.'" class="anagram_gallery masonry row type-' . $args['type']  . '  gallery-columns-'.$columns.' gallery-size-'.$size_class.'" itemscope itemtype="http://schema.org/ImageGallery" ><div class="loading"><i class="fa fa-cog fa-spin fa-3x fa-fw" ></i></div>';

		//$output_buffer .= $number .' -  32.2295805739514';


		if ( !empty($attachments) ) {
			$x=1;
			foreach ( $attachments as $aid => $attachment ) {

				$thumb = wp_get_attachment_image_src( $aid ,  $args['size'] );
/*
				$conwidth ='25%';
				if('rectangular' == $args['type'] && $x == 1)$conwidth = '60%';
				 style="width:'.$number .'%"
*/
				$thumb = anagram_resize_image(array('width' => $imgwidth, 'height' => $imgheight ,'url' => true,'image_id' => $aid, 'upscale'=>true ,'crop'=>$crop ));


				$full = wp_get_attachment_image_src( $aid , $full_size);

				$_post = get_post($aid);

				$image_title = esc_attr($_post->post_title);
				$image_alttext = get_post_meta($aid, '_wp_attachment_image_alt', true);
				$image_caption = $_post->post_excerpt;
				$image_description = $_post->post_content;

					//htmlspecialchars('<strong>'.$image_title .'</strong><br/> '.$image_caption)
				$output_buffer .='
				<div class="item '.$args['itemclass'].'">
					<a href="'. $full[0] .'" itemprop="contentUrl" rel="lightbox" title="'.htmlspecialchars('<strong>'.$image_title .'</strong>').'" data-size="'.$full[1].'x'.$full[2].'">
				        <img src="'. $thumb .'" class="'.$imgclass.'" itemprop="thumbnail" alt="'.$image_description.'" alt="'.$image_title.'" />
				    </a>
			    </div>
				';
			$x++;
			}
		}



		$output_buffer .="</div>

		<div style='clear:both'></div>";





		return $output_buffer;
}