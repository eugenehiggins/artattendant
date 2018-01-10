 //HS.beacon.destroy();

  HS.beacon.config({
    color: '#d2232a',
    icon: 'question',
    topArticles: true,
    showName: true,
    showSubject: true,
   // topics: [
   //   { val: 'need-help', label: 'Need help with the product' },
   //   { val: 'bug', label: 'I think I found a bug'}
   // ],
    attachment: true,
    //instructions:'This is instructional text that goes above the form.'
  });

jQuery(function($) {



       $("#share").jsSocials({
            shares: ["email", "twitter", "facebook", "googleplus", "pinterest"],
            showLabel:false,
			showCount: false,
			shareIn: "popup",
        });



	jQuery(window).scroll(function() {
		if(jQuery(this).scrollTop() != 0) {
			jQuery('.scrollTop').fadeIn();
		} else {
			jQuery('.scrollTop').fadeOut();
		}
	});
	jQuery('.scrollTop a').click(function() {
		jQuery('body,html').animate({scrollTop:0},800);
	});

 $(".entry-content").fitVids();

//Switch url to app for iphones
 $(".social-links a").switcher();
//lazy load video
//$('iframe').sleepyHead();



/* affix the navbar after scroll below header */
$('.sidebar-nav').affix({
      offset: {
        top: $('.navbar.main').height()+70//-$('#nav').height()
      }
});



    $('.faq_question').click(function() {

        if ($(this).parent().is('.open')){
            $(this).closest('.faq').find('.faq_answer_container').animate({'height':'0'},500);
            $(this).closest('.faq').removeClass('open');

            }else{
                var newHeight =$(this).closest('.faq').find('.faq_answer').height() +'px';
                $(this).closest('.faq').find('.faq_answer_container').animate({'height':newHeight},500);
                $(this).closest('.faq').addClass('open');
            }

    });



$('.counter').counterUp({
    //delay: 10,
   // time: 1000
});



/*Scroll to top code*/


       $imgs = $("img.lazy");

    $imgs.show().lazyload({
        effect: "fadeIn",
        failure_limit: Math.max($imgs.length - 1, 0)
    });


   $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
/*

$('.refiner').click(function(){
    $('.the-filters').animate({height:'200px'}, 500);
    //this method increases the height to 72px
});
*/

   jQuery('.original').imagesLoaded( function(event) {
			//console.log(jQuery(this).parent());
			jQuery('#artwork').addClass('content--open');
			jQuery('.bottom-content').addClass('content--open');
		});

//Show hide content
jQuery(".refiner .refine-click").toggle(function(){
	jQuery('i', this).addClass('fa-rotate-270');
	//jQuery('i', this).removeClass("fa-angle-down").addClass("fa-angle-up");
  $('.the-filters').slideDown('normal').parent().addClass('open');

  // close artist list
    $('#artist-list').toggle();
}, function(){
	jQuery('i', this).removeClass('fa-rotate-270');
	//jQuery('i', this).removeClass("fa-angle-up").addClass("fa-angle-down");
   $('.the-filters').slideUp('normal').parent().removeClass('open');
});


//Show hide artists
jQuery(".refiner .artists-click").toggle(function(){
    $('#artist-list').slideDown('normal');

    //close refiner toggle
    $('i.fa-sliders').removeClass('fa-rotate-270');
    $('.the-filters').toggle();
}, function(){
	$('#artist-list').slideUp('normal');
});



 $( ".facetwp-facet-artist_list " ).on( "click", ".facetwp-radio", function() {
  	jQuery(".refiner .artists-click").click();
});





$('section h5').click(function(event) {
  event.preventDefault();
  $(this).addClass('active');
  $(this).siblings().removeClass('active');

  var ph = $(this).parent().height();
  var ch = $(this).next().height();

  if (ch > ph) {
    $(this).parent().css({
      'min-height': ch + 'px'
    });
  } else {
    $(this).parent().css({
      'height': 'auto'
    });
  }
});

function tabParentHeight() {
  var ph = $('section').height();
  var ch = $('section .tab-content').height();
  if (ch > ph) {
    $('section').css({
      'height': ch + 'px'
    });
  } else {
    $(this).parent().css({
      'height': 'auto'
    });
  }
}

$(window).resize(function() {
  tabParentHeight();
});

$(document).resize(function() {
  tabParentHeight();
});
tabParentHeight();



jQuery('.showhide').toggle(function(){
	request = jQuery(this).data('toshow');

   jQuery('#'+request).slideDown('normal').addClass('open');
}, function(){
    jQuery('#'+request).slideUp('normal').removeClass('open');
});



/*

	var $container = $('.masonry');
	// initialize Masonry after all images have loaded
	$container.imagesLoaded( function() {

		var columnCount = 3;
	    var gutter = 15;

	$('.loading').hide();

	  $container.find('.loading').hide();
	   $container.find('.item').fadeIn('slow');

		// initialize Masonry after all images have loaded
		$container.masonry({
			itemSelector: '.item',
			//gutter:15,
			//isFitWidth: true
		});

	});
*/
/*

	$(window).load(function() {
		$("html,body").trigger("scroll");
	});
	var $container = $('.masonry'),
		$imgs = $("img.lazy");
	$imgs.each(function(index) {
		imgRatio = $(this).attr('height') / $(this).attr('width');
		imgWidth = $(this).parent().width();
		$(this).width(imgWidth).height(parseInt(imgWidth * imgRatio));
		//$(this).parent().width(imgWidth).height(parseInt(imgWidth * imgRatio));
	});
	$(window).on('resize', function(){
		$imgs.each(function(index) {
			imgRatio = $(this).attr('height') / $(this).attr('width');
			imgWidth = $(this).parent().width();
			$(this).width(imgWidth).height(parseInt(imgWidth * imgRatio));
			//$(this).parent().width(imgWidth).height(parseInt(imgWidth * imgRatio));
		});
	});
	$imgs.lazyload({
		event: 'scroll',
		effect: 'fadeIn',
		threshold: 400,
		skip_invisible: false,
		failure_limit: Math.max($imgs.length - 1, 0),
		appear: function(e) {
			//  $container.isotope('layout');
			//$container.masonry('layout');
		}
	});
	//$container.find('.item').animate({opacity: 1,},1500 );
	// initialize Masonry after all images have loaded
		$container.masonry({
			itemSelector: '.artwork-item',
			//gutter:15,
			//isFitWidth: true
		});

	var timeout = setTimeout(function() {
		jQuery('.loading').fadeOut();
		$container.masonry('layout');
		$(window).trigger("scroll");
	}, 500);
*/



});//End on load
