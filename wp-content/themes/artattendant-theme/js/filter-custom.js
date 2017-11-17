jQuery(function($) {


			var $container = jQuery('#container'),
				masonryOn = false;




 	jQuery(document).on('click', '.facetwp-selections li .facetwp-selection-value', function() {
		numItems = $('.facetwp-selections .facetwp-selection-value').length;
		if(numItems==1){
			$(this).parent().fadeOut();
		}else{
			$(this).fadeOut();
		};


		//console.log(numItems);
		jQuery(document).trigger('facetwp-refresh');

	});

	 jQuery(document).on('facetwp-refresh', function() {
	 // Make checkbox options semi-transparent
        FWP.loading_handler = function(params) {
            params.element.find('.facetwp-checkbox').css('opacity', 0.5);

        }
		//console.log('refresh');
		if(masonryOn) $container.masonry('destroy');


		jQuery('.artwork-item').animate({ opacity: 0 }, 0);
		//jQuery('.item').hide();
		jQuery('.loading').show();

		//$container.masonry('layout');

        //$('.facetwp-template').prepend('<div class="loading">Loading</div>');
    });


    $(document).on('facetwp-loaded', function() {
        // Scroll to the top of the page after the page is refreshed
	 		//$container.masonry('destroy');
			$container.imagesLoaded( function() {
					jQuery('.search-bar').animate({ opacity: 1 }, 600);
					jQuery('.facetwp-template').animate({ opacity: 1 }, 600);
					//jQuery('html, body').animate({ scrollTop: 0 }, 500);
				    jQuery('.artwork-item').animate({ opacity: 1 }, 600);

				    jQuery('.loading').hide();

					$container.masonry({
						itemSelector: '.artwork-item',
						gutter:0,
						//isAnimated: true,
						//isFitWidth: true
					});
					masonryOn = true;
					//$container.masonry('layout');
					 //console.log('image loaded');

			});
					// $container.masonry('layout');
					//console.log('load');

     });

 	jQuery(document).on('click', '.autocomplete-suggestion', function() {

		//console.log('yes');
		jQuery(document).trigger('facetwp-refresh');

	});


        // Click on a selection item
/*
        jQuery('.facetwp-search').on({
		    keyup: function(event) {
			    console.log('bob');
			    var searchterm = $(event.target).val().trim();
			     if ( searchterm.length >= 3 ){
			     	console.log(searchterm);
			     	}
			     },
		    blur:  function(event) {

			     },
		    focus: function(event) {

			     }
		});
*/




/*
var timer = null; //timer varible is to turn off search spiner after typing has stopped

jQuery(document).on("keyup blur", ".facetwp-search", function(e)
    {
			    var searchterm = jQuery(e.target).val().trim();
			      if (13 == e.which) {
			           // FWP.autoload();
			        }else{
				       if ( searchterm.length >= 3 ){
					        clearTimeout(timer);
	                        timer = setTimeout(function(){
	                            //console.log(searchterm);
	                            //FWP.soft_refresh = true;
						 		FWP.autoload();
	                        }, 400);

				     	}

			        }




    });
*/


/*
	'keyup  #search': _.throttle(function (event) {
        jQuery(document).on('click', '.facetwp-selections li', function() {
	        var searchterm = $(event.target).val().trim();



            var $this = $(this);
            var facet_name = $this.attr('data-facet');
            var facet_value = $this.attr('data-value');
            var facet_type = $('.facetwp-facet-' + facet_name).attr('data-type');

            // Load the DOM values
            FWP.parse_facets();

            // Update the "FWP.facets" object
            if ('string' == typeof FWP.facets[facet_name]) {
                FWP.facets[facet_name] = '';
            }
            else if ('date_range' == facet_type) {
                FWP.facets[facet_name] = [];
            }
            else {
                var array = FWP.facets[facet_name];
                var index = array.indexOf(facet_value);
                if (-1 < index) {
                    array.splice(index, 1);
                    FWP.facets[facet_name] = array;
                }
                else {
                    FWP.facets[facet_name] = [];
                }
            }

            // Update the URL hash
            FWP.set_hash();

            // Run the AJAX request
            FWP.fetch_data();
        });
*/


/*
  $(function() {
    wp.hooks.addFilter('facetwp/selections/slider', function(label, params) {
      var facet_name = params.el.attr('data-name');
      if ('width' == facet_name) {
        label = 'Number of students: ' + label;
      }
      return label;
    }, 12);
  });
*/


});//End on load





	/* globals FWP */
/**
 * JavaScript for FacetWP Infinite Scroll
 */
/*
(function( $ ) {
    'use-strict';

    var throttleTimer = null;
    var throttleDelay = 100;

    function ScrollHandler() {
        clearTimeout( throttleTimer );
        throttleTimer = setTimeout(function() {
            if ( $( window ).scrollTop() !== $( document ).height() - $( window ).height() ) {
                return;
            }

            if ( FWP.settings.pager.page < FWP.settings.pager.total_pages ) {
                FWP.paged = parseInt( FWP.settings.pager.page ) + 1;
                FWP.is_load_more = true;
                FWP.soft_refresh = false;
                FWP.refresh();
            }
        }, throttleDelay );
    }

    wp.hooks.addFilter( 'facetwp/template_html', function( resp, params ) {
        if ( FWP.is_load_more ) {
            FWP.is_load_more = false;
            $( '.facetwp-template' ).append( params.html );
            return true;
        }

        return resp;
    });

    $( document ).on( 'facetwp-loaded', function() {
        if ( ! FWP.loaded ) {
            $( window ).off( 'scroll', ScrollHandler ).on( 'scroll', ScrollHandler );
        }
    });
})( jQuery );
*/