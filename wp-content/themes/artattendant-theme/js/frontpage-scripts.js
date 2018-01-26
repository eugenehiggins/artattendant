jQuery(document).ready(function ($) {

    $boxes = $('.section3 .box');

    function slideOnHover(){
        $('.section3 .box').hover(function (e) {
                var $self = $(this);
                var $img = $(this).find('img');
                if (!$img.is(':animated')) {
                    $img.stop().hide("slide", {direction: "up"}, 200);
                }
                console.log("first",$self);
                // $boxes.each(function(index){
                //     if (!$self.is($(this))) {
                //         console.log("second", $(this).is($self) );
                //         $(this).find('img').stop().show("slide", {direction: "up"}, 700);
                //     }
                // })
            },
            function () {
                var $img = $(this).find('img');
                if (!$img.is(':animated')) {
                    $(this).find('img').stop().show("slide", {direction: "up"}, 200);
                }
            }
        )

    }

    if ($( window ).width() >= 1200) {
        //slideOnHover();
    }

    $( window ).resize(function (){
        if ($( window ).width() >= 1200) {
           // slideOnHover();
        }
    })



    $(window).scroll(function () {
        var windowH = $(window).height();
        // var lastPosition =;

        if ($(".institutions").length > 0) {
            var originalPosition = $(".institutions").offset();
            if (($(this).scrollTop() + windowH) > originalPosition.top) {
                // var travel = ($(this).scrollTop() + windowH) - originalPosition.top;
                console.log()
                $(".institutions").css({
                    position: 'relative',
                    left: 5
                }, 500, "linear", function () {
                    left: 0
                });
            }
        }
        $(".aos-animate").css({
            // 'transform': "translate(50px, 100px)"
        }, 500, "linear", function () {
            left: 0
        });
    })


    // var handlerIn = function(e){
    //     var target = e.target;
    //     var $img = $(target).find('img');
    //     $img.slideUp( "fast", function() {
    //         // Animation complete.
    //     });
    //     console.log($img);
    // }
    // var handlerOut = function(e){
    //     var target = e.target;
    //     var $img = $(target).find('img');
    //     $img.slideDown( "fast", function() {
    //         // Animation complete.
    //     });
    // }
    // $('div#artists.audience').hover(function(e){
    //     $(this).find('img').toggle('slide');
    // });
});