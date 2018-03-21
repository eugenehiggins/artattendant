<?php
/*
Plugin Name:    Menu Items Visibility Control
Description:    Control the display logic of individual menu items.
Author:         Hassan Derakhshandeh
Version:        0.3.7
Text Domain:    menu-items-visibility-control
Domain Path:    /languages
*/

class Menu_Items_Visibility_Control {

	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'i18n' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'nav_menu_script' ) );
			add_action( 'wp_update_nav_menu_item', array( $this, 'update_option' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'remove_visibility_meta' ), 1, 3 );
		} else {
			add_filter( 'wp_get_nav_menu_items', array( $this, 'visibility_check' ), 10, 3 );
		}
	}

	function i18n() {
		load_plugin_textdomain( 'menu-items-visibility-control', false, '/languages' );
	}

	/**
	 * Scripts for admin Menus manager
	 *
	 * @since 0.3.6
	 */
	function nav_menu_script() {
		global $menu_items, $wpdb;

		$screen = get_current_screen();
		if( 'nav-menus' != $screen->base )
			return;

		$this->template_edit();
		wp_enqueue_script( 'menu-items-visibility-control', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/scripts.js', array( 'jquery', 'underscore', 'wp-util' ), '0.3.7', true );
		$values = array();
		if ( ! empty( $menu_items ) ) {
			$menu_items_ids = join( ',', wp_list_pluck( $menu_items, 'ID' ) );
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_menu_item_visibility' AND post_id IN({$menu_items_ids})", OBJECT );
			if ( ! empty( $results ) ) {
				$values = array_combine( wp_list_pluck( $results, 'post_id' ), wp_list_pluck( $results, 'meta_value' ) );
			}
		}
		wp_localize_script( 'menu-items-visibility-control', 'MIVC', array(
			'values' => $values,
		) );

		/* ensure this does not run again */
		remove_action( 'admin_enqueue_scripts', array( $this, 'nav_menu_script' ) );
	}

	/**
	 * Template for the Visibility field
	 *
	 * @since 0.3.6
	 */
	function template_edit() {
		?>
		<script type="text/html" id="tmpl-menu-items-visivility-control">
			<p class="field-visibility description description-wide">
				<label for="edit-menu-item-visibility-{{data.id}}">
					<?php _e( 'Visibility', 'menu-items-visibility-control' ) ?>:
					<input type="text" class="widefat code" id="edit-menu-item-visibility-{{data.id}}" name="menu-item-visibility[{{data.id}}]" value="{{data.value}}" />
				</label>
			</p>
		</script>
		<?php
	}

	function update_option( $menu_id, $menu_item_db_id, $args ) {
		if ( isset( $_POST['menu-item-visibility'][$menu_item_db_id] ) ) {
			$meta_value = get_post_meta( $menu_item_db_id, '_menu_item_visibility', true );
			$new_meta_value = stripcslashes( $_POST['menu-item-visibility'][$menu_item_db_id] );

			if ( '' == $new_meta_value ) {
				delete_post_meta( $menu_item_db_id, '_menu_item_visibility', $meta_value );
			} elseif ( $meta_value !== $new_meta_value ) {
				update_post_meta( $menu_item_db_id, '_menu_item_visibility', $new_meta_value );
			}
		}
	}

	/**
	 * Checks the menu items for their visibility options and
	 * removes menu items that are not visible.
	 *
	 * @return array
	 * @since 0.1
	 */
	function visibility_check( $items, $menu, $args ) {
		$hidden_items = array();
		foreach ( $items as $key => $item ) {
			$item_parent = get_post_meta( $item->ID, '_menu_item_menu_item_parent', true );
			if ( $logic = get_post_meta( $item->ID, '_menu_item_visibility', true ) ) {
				eval( '$visible = ' . $logic . ';' );
			} else {
				$visible = true;
			}
			if ( ! $visible
				|| isset( $hidden_items[$item_parent] ) // also hide the children of invisible items
			) {
				unset( $items[$key] );
				$hidden_items[$item->ID] = '1';
			}
		}

		return $items;
	}

	/**
	 * Remove the _menu_item_visibility meta when the menu item is removed
	 *
	 * @since 0.2.2
	 */
	function remove_visibility_meta( $post_id ) {
		if( is_nav_menu_item( $post_id ) ) {
			delete_post_meta( $post_id, '_menu_item_visibility' );
		}
	}
}
Menu_Items_Visibility_Control::get_instance();