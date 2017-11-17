=== Menu Item Visibility Control ===
Contributors: shazdeh
Plugin Name: Menu Item Visibility Control
Tags: menu, nav-menu, navigation, navigation menu, conditional tags, context, filter
Requires at least: 3.1
Tested up to: 4.7.2
Stable tag: 0.3.5

Control individual menu items' visibility based on your desired condition.

== Description ==

Using this plugin you can use WordPress <a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a> to enable or disable menu items on the front-end. It works like 'Widget Logic' but for menu items.

= Usage =
You must insert conditional tags in the "Visibility" box in the menu item options form. You can use any PHP or WordPress functions to build crazy conditions and logics for menu items. For example, to hide the menu item on homepage you can set the visibility to:
<code>! is_home()</code>

To hide the menu item to logged in users: 
<code>! is_user_logged_in()</code>

To show the menu item only to users with "administrator" role:
<code>in_array('administrator', $GLOBALS['current_user']->roles)</code>


== Installation ==

1. Upload the `menu-item-visibility` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Locate the 'Menus' item on the 'Appearance' menu
4. While editing your menu item, you see another option: Visibility, input your logic and that's it.


== Screenshots ==
1. Visibility Control


== Frequently Asked Questions ==

= <a id="conflict"></a>I don't see the Nav Menu Roles options in the admin menu items?  =
Please see this page: https://wordpress.org/plugins/nav-menu-roles/faq/


== Changelog ==

= 0.3.5 =
* Possible fatal error prevention

= 0.3.4 =
* Fix compatibility with Menu Icons plugin

= 0.3.3 =
* Fix menu item edit screen styles

= 0.3.2 =
* Fix Customizer wiping out the Visibility value upon save

= 0.3.1 =
* Got rid of PHP notices in the admin area
* Updated Walker_Nav_Menu_Edit

= 0.3 =
* Gantry 4.0 compatibility
* implemented singleton pattern
* added the remove_visibility_meta function which cleans up the meta datas for deleted menu items

= 0.2.1 =
* Fixed a minor bug where unnecessary database rows in postmeta table would be created upon save
* fixed a bug concerning using quotes in conditions

= 0.2 =
* Compatibility with latest WordPress release
* Fixed a minor bug where conditions would also execute on the admin area