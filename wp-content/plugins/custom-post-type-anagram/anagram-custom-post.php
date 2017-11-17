<?php
/*
Plugin Name: custom post types - Anagram
Plugin URI: http://anagr.am
Description: Functions, custom post types and other for Anagram
Author: Anagram
Version: 1.0
Author URI: http:/anagr.am
License: GPL2
*/




/**
 * Register the ustom post types.
 */






/*********************************
  Quick & Easy Custom Post Types
*********************************/

$cpts = array(
	//Portfolios
	'var' => array( // unique value
		'post_type' => 'artwork', // post type name
		'supports' => array('title','editor','thumbnail'), // supports 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes', 'post-formats' (optional, default: title, editor)
		'singular' => 'Artwork', // post type singular label
		'plural' => 'Artworks', // post type plural label
		'item_singular' => '', // post type singular item label (optional, if none is supplied, uses singular)
		'item_plural' => '', // post type plural item label (optional, if none is supplied, uses plural)
		'archive' => true, // Does it have archive?
		'hierarchical' => false, // post or page
		'position' => '', // post type position in backend menu (optional, default: 20)
		'show_in_menu' => '',//show in admin menu
		'menu_icon'=> 'dashicons-portfolio',
		'taxonomies' => array(),
		'slug' => 'artworks', // post type slug
		'hide_search' =>'',
		'rewrite' => ''
	),
);



$ctaxs = array(
	'tax' => array(
		'taxonomy' => 'art_category', // taxonomy name
		'singular' => 'Category', // taxonomy singular label
		'plural' => 'Categories', // taxonomy plural label
		'hierarchical' => true, // taxonomy hierarchical
		'slug' => 'art-category', // taxonomy slug
		'pts' => array('artwork') // post types that are supported by the taxonomy
	),
	'med' => array(
		'taxonomy' => 'medium', // taxonomy name
		'singular' => 'Medium', // taxonomy singular label
		'plural' => 'Mediums', // taxonomy plural label
		'hierarchical' => true, // taxonomy hierarchical
		'slug' => 'medium', // taxonomy slug
		'pts' => array('artwork') // post types that are supported by the taxonomy
	),
);

/*
			Missing from loop??
			'public' => true,
    		'publicly_queryable' => true,
			'sort' => true,
			'args' => array( 'orderby' => 'term_order' ),
*/



/******************
POST TYPES
******************/
if(is_array($cpts)) {
add_action('init', 'wpreso_custom_posttypes');
}
function wpreso_custom_posttypes() {
	global $cpts;
	foreach($cpts as $cpt){
		$item_singular_label = ((isset($cpt['item_singular']) && !empty($cpt['item_singular']))? $cpt['item_singular'] : $cpt['singular'] );
		$item_plural_label = ((isset($cpt['item_plural']) && !empty($cpt['item_plural']))? $cpt['item_plural'] : $cpt['plural'] );
		$labels = array(
			'name' => __($cpt['plural'], 'wpreso'),
			'singular_name' => __($cpt['singular'], 'wpreso'),
			'add_new' => __('Add New', 'wpreso'),
			'add_new_item' => __('Add New '.$item_singular_label, 'wpreso'),
			'edit_item' => __('Edit '.$item_singular_label, 'wpreso'),
			'new_item' => __('New '.$item_singular_label, 'wpreso'),
			'view_item' => __('View '.$item_singular_label, 'wpreso'),
			'search_items' => __('Search '.$item_plural_label, 'wpreso'),
			'not_found' =>  __('No '.$item_plural_label.' Found', 'wpreso'),
			'not_found_in_trash' => __('No '.$item_plural_label.' found in Trash', 'wpreso'),
			'parent_item_colon' => ''
		);
		$supports = ((isset($cpt['supports']) && !empty($cpt['supports']) && is_array($cpt['supports']))? $cpt['supports'] : array( 'title', 'editor') );
		$taxonomies = ((isset($cpt['taxonomies']) && !empty($cpt['taxonomies']) && is_array($cpt['taxonomies']))? $cpt['taxonomies'] : array() );
		$menu_position = ((isset($cpt['position']) && !empty($cpt['position']))? $cpt['position'] :  40);
		$show_menu = ((isset($cpt['show_in_menu']) && !empty($cpt['show_in_menu']))? $cpt['show_in_menu'] :  true);
		$archive = ((isset($cpt['archive']) && !empty($cpt['archive']))? $cpt['archive'] :  false);
		$hidesearch = ((isset($cpt['hide_search']) && !empty($cpt['hide_search']))? $cpt['hide_search'] :  false);
		$hierarchical= ((isset($cpt['hierarchical']) && !empty($cpt['hierarchical']))? $cpt['hierarchical'] :  false);
		$menu_icon = ((isset($cpt['menu_icon']) && !empty($cpt['menu_icon']))? $cpt['menu_icon'] :  null);
		$rewrite = ((isset($cpt['rewrite']) && !empty($cpt['rewrite']))? $cpt['rewrite'] :  array('slug' => $cpt['slug']));
		$args = array(
			'label' => __($cpt['singular'], 'wpreso'),
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'map_meta_cap' => true,
			'rewrite' => $rewrite,
			'capability_type' => 'post',
			'has_archive' => $archive,
			'hierarchical' => $hierarchical,
			'menu_position' => $menu_position,
			'show_in_menu' => $show_menu,
			'menu_icon'=> $menu_icon,
			'show_in_nav_menus'   => true,
			'supports' => $supports,
			'taxonomies' => $taxonomies,
			'exclude_from_search' => $hidesearch
		);
		register_post_type($cpt['post_type'],$args);

	}
	add_filter('post_updated_messages', 'wpreso_posttype_updated_messages');


}


function wpreso_posttype_updated_messages( $messages ) {
	global $cpts, $post;
	foreach($cpts as $cpt){
		$messages[$cpt['post_type']] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __($cpt['singular'].' Updated. <a href="%s">View '.$cpt['singular'].'</a>', 'wpreso'), esc_url( get_permalink($post->ID) ) ),
			2 => __('Custom Field Updated.', 'wpreso'),
			3 => __('Custom Field Deleted.', 'wpreso'),
			4 => __($cpt['singular'].' Updated.', 'wpreso'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __($cpt['singular'].' restored to revision from %s', 'wpreso'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __($cpt['singular'].' Published. <a href="%s">View '.$cpt['singular'].'</a>', 'wpreso'), esc_url( get_permalink($post->ID) ) ),
			7 => __($cpt['singular'].' Saved.', 'wpreso'),
			8 => sprintf( __($cpt['singular'].' Submitted. <a target="_blank" href="%s">Preview '.$cpt['singular'].'</a>', 'wpreso'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
			9 => sprintf( __($cpt['singular'].' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview '.$cpt['singular'].'</a>', 'wpreso'),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'd/m-Y @ H:i', 'wpreso'), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
			10 => sprintf( __($cpt['singular'].' Draft Updated. <a target="_blank" href="%s">Preview '.$cpt['singular'].'</a>', 'wpreso'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
		);
		return $messages;
	}
}

/******************
TAXONOMIES
******************/
if(is_array($ctaxs)){
add_action('init', 'wpreso_custom_taxonomies');
}
function wpreso_custom_taxonomies(){
	global $ctaxs;
	foreach($ctaxs as $ct){
		// Add new taxonomy, make it hierarchical (like categories)
		$taxlabels = array(
			'name' => __( $ct['plural'], 'wpreso' ),
			'singular_name' => __( $ct['singular'], 'wpreso'),
			'search_items' =>  __( 'Search '.$ct['plural'], 'wpreso'),
			'all_items' => __( 'All '.$ct['plural'], 'wpreso'),
			'parent_item' => __( 'Parent '.$ct['singular'], 'wpreso'),
			'parent_item_colon' => __( 'Parent '.$ct['singular'].':', 'wpreso'),
			'edit_item' => __( 'Edit '.$ct['singular'], 'wpreso'),
			'update_item' => __( 'Update '.$ct['singular'], 'wpreso'),
			'add_new_item' => __( 'Add New '.$ct['singular'], 'wpreso'),
			'new_item_name' => __( 'New '.$ct['singular'].' name', 'wpreso'),
			//'menu_name' => __( $ct['singular'], 'wpreso')
		);

		register_taxonomy($ct['taxonomy'],$ct['pts'], array(
			'hierarchical' => $ct['hierarchical'],
			'labels' => $taxlabels,
			'show_ui' => true,
			'query_var' => true,
			//'show_admin_column' => true,
			'rewrite' => array( 'slug' => $ct['slug'] ),
		));
	}




}