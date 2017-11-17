<?php


/** hide CSS **/
add_filter( 'facetwp_load_css', '__return_false' );

/**
 * Custom artwork filters
 */

function my_facetwp_is_main_query( $is_main_query, $query ) {
    if ( isset( $query->query_vars['facetwp'] ) ) {
        $is_main_query = true;
    }
    return $is_main_query;
}
add_filter( 'facetwp_is_main_query', 'my_facetwp_is_main_query', 10, 2 );




add_filter( 'facetwp_index_row', function( $params, $class ) {

    if ( 'artwork_status' == $params['facet_name'] ) {
        $post_id = (int) $params['post_id'];
        $public_status = anagram_artwork_status( $post_id );
        if ( ! empty( $public_status ) && $public_status ===1) {
            $params['facet_value'] = 'sale';
            $params['facet_display_value'] = 'Sale';
            return $params;
        }else if ( ! empty( $public_status ) && $public_status ===2){
	        $params['facet_value'] = 'loan';
            $params['facet_display_value'] = 'For Loan';
            return $params;
        }else if ( ! empty( $public_status ) && $public_status ===3){
	        $params['facet_value'] = 'loan';
            $params['facet_display_value'] = 'For Loan';
            $params['facet_value'] = 'sale';
            $params['facet_display_value'] = 'Sale';
            return $params;
        }
        // If not on sale, skip the facet for this post
        return false;
    }
    return $params;

}, 10, 2 );




function fwp_slider_set_label() {

	if(!is_post_type_archive()) return;
?>
<script>
(function($) {
    $(function() {
        wp.hooks.addAction('facetwp/set_label/slider', function($this) {
            var facet_name = $this.attr('data-name');
            var min = FWP.settings[facet_name]['lower'];
            var max = FWP.settings[facet_name]['upper'];
            //var min_alpha = String.fromCharCode(65 + parseInt(min));
            //var max_alpha = String.fromCharCode(65 + parseInt(max));
            var label =  '<div class="pull-left">'+ min + '"</div><div class="pull-right">' + max + '"</div>';
            var label =  '';
            $this.find('.facetwp-slider-label').html(label);
        });

        wp.hooks.addFilter('facetwp/set_options/slider', function(slider_opts, params) {
	        //var facet_name = $this.attr('data-name');

/*
	        	FWP.settings[facet_name]['pips'] = {
					'mode'= 'positions',
					'values'= [0,25,50,75,100],
					'density'= 4
				};
*/
/*
	         pips => {
					'mode'=> 'positions',
					'values'=> [0,25,50,75,100],
					'density'=> 4
				};
*/
/*
                    var opts = {
            decimal_separator: FWP.settings[facet_name]['decimal_separator'],
            thousands_separator: FWP.settings[facet_name]['thousands_separator']
        };
*/

			slider_opts.pips ={
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 4,
					stepped: true
				};


		  return slider_opts;

	   });
    });
})(jQuery);
</script>
<?php
}
add_action( 'wp_head', 'fwp_slider_set_label', 999 );


function custom_slider_steps( $output, $params ) {
    $output['settings']['width']['range'] = array(
        'min' => array( 0.001 ),
        '25%' => array( 0.01, 0.01 ),
        '50%' => array( 1, 1 ),
        '75%' => array( 100, 100 ),
        'max' => array( 250000 )
    );
    $output['settings']['width']['pips'] = array(
        'mode' => 'range',
		//'values' =>  [ 20, 80],
		'density' =>  4
    );
    return $output;
}
//add_filter( 'facetwp_render_output', 'custom_slider_steps', 10, 2 );



/*

add_filter( 'facetwp_index_row', function( $params, $class ) {
    if ( 'status' == $params['facet_name'] ) {
        $values = (array) $params['facet_value']; // an array of post IDs (it's already unserialized)
        foreach ( $values as $val ) {
            $params['facet_value'] = $val;
            $params['facet_display_value'] = get_the_title( $val );
            $class->insert( $params ); // insert each value to the database
        }
        // skip the default indexing query
        return false;
    }
    return $params;
}, 10, 2 );
*/


/*
function anagram_remove_facetwp_css() {
    return false;
}
*/

function my_facetwp_result_count( $output, $params ) {
    $output = $params['lower'] . '-' . $params['upper'] . ' of ' . $params['total'] . ' artworks';
    return $output;
}

add_filter( 'facetwp_result_count', 'my_facetwp_result_count', 10, 2 );




/*
Infinate scrolling
https://gist.github.com/robneu/d917cd235a12822d4df9480f48446f9e
**/
function fwp_load_more() {
?>
<script>
(function($) {
    $(function() {
        if ('object' != typeof FWP) {
            return;
        }

        wp.hooks.addFilter('facetwp/template_html', function(resp, params) {
            if (FWP.is_load_more) {
                FWP.is_load_more = false;
                $('.facetwp-template').append(params.html);
                return true;
            }
            return resp;
        });
    });

    $(document).on('click', '.fwp-load-more', function() {
        $('.fwp-load-more').html('Loading...');
        FWP.is_load_more = true;
        FWP.paged = parseInt(FWP.settings.pager.page) + 1;
        FWP.soft_refresh = true;
        FWP.refresh();
    });

    $(document).on('facetwp-loaded', function() {
        if (FWP.settings.pager.page < FWP.settings.pager.total_pages) {
            if (! FWP.loaded && 1 > $('.fwp-load-more').length) {
                $('.facetwp-template').after('<button class="fwp-load-more">Load more</button>');
            }
            else {
                $('.fwp-load-more').html('Load more').show();
            }
        }
        else {
            $('.fwp-load-more').hide();
        }
    });

    $(document).on('facetwp-refresh', function() {
        if (! FWP.loaded) {
            FWP.paged = 1;
        }
    });
})(jQuery);
</script>
<?php
}
//add_action( 'wp_head', 'fwp_load_more', 99 );
//add_filter( 'facetwp_template_force_load', '__return_true' );