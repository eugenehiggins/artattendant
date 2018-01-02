<?php
/*
Plugin Name: FacetWP - Select2
Plugin URI: https://facetwp.com/
Description: Adds the Select2 facet type
Version: 1.3.3
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub Plugin URI: https://github.com/FacetWP/facetwp-select2

Copyright 2016 FacetWP, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) exit;


class FWP_Select2
{
    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        add_filter( 'facetwp_facet_types', array( $this, 'register_facet_type' ) );
    }


    function wp_enqueue_scripts() {

        if ( is_admin() || ! apply_filters( 'facetwp_select2_load_assets', true ) ) {
            return;
        }

        $plugins_url = plugins_url( 'facetwp-select2' );

        // ACF5 ships with an older version
        wp_deregister_script( 'select2' );
        wp_deregister_style( 'select2' );

        // Register
        wp_register_script( 'select2', $plugins_url . '/select2/select2.min.js', array( 'jquery' ), '4.0.3' );
        wp_register_style( 'select2', $plugins_url . '/select2/select2.min.css', array(), '4.2.7' );

        // Enqueue
        wp_enqueue_script( 'select2' );
        wp_enqueue_style( 'select2' );
    }


    function register_facet_type( $facet_types ) {
        include( dirname( __FILE__ ) . '/select2.php' );
        $facet_types['select2'] = new FacetWP_Facet_Select2();
        return $facet_types;
    }
}

new FWP_Select2();
