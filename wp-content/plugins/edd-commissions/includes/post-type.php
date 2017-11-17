<?php
/**
 * Post Type Functions
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Setup Commission Post Type
 *
 * Registers the Commissions CPT.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function eddc_setup_post_type() {
	/* commission post type */
	$commission_labels = array(
		'name'               => _x('Commissions', 'post type general name', 'eddc'),
		'singular_name'      => _x('Commission', 'post type singular name', 'eddc'),
		'add_new'            => __('Add New', 'eddc'),
		'add_new_item'       => __('Add New Commission', 'eddc'),
		'edit_item'          => __('Edit Commission', 'eddc'),
		'new_item'           => __('New Commission', 'eddc'),
		'all_items'          => __('All Commissions', 'eddc'),
		'view_item'          => __('View Commission', 'eddc'),
		'search_items'       => __('Search Commissions', 'eddc'),
		'not_found'          => __('No Commissions found', 'eddc'),
		'not_found_in_trash' => __('No Commissions found in Trash', 'eddc'),
		'parent_item_colon'  => '',
		'menu_name'          => __('Commissions', 'eddc')
	);

	$commission_args = array(
		'labels'             => apply_filters('edd_commission_labels', $commission_labels),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => false,
		'show_in_menu'       => false,
		'show_in_nav_menu'   => false,
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'page',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title' )
	);

	register_post_type('edd_commission', $commission_args);
}
add_action( 'init', 'eddc_setup_post_type' );


/**
 * Registers the custom taxonomy for tracking commission statuses
 *
 * @since       2.8
 * @return      void
*/
function eddc_setup_taxonomy() {
	register_taxonomy( 'edd_commission_status', 'edd_commission', array( 'public' => false ) );
}
add_action( 'init', 'eddc_setup_taxonomy', 0 );
