jQuery(document).ready(function ($) {

    // AOS.init();

    $('.section3 .box').hover(function (e) {
            var $img = $(this).find('img');
            if(!$img.is(':animated')) {
                $img.stop().hide("slide", {direction: "up"}, 700);
            }
        },
        function () {
            var $img = $(this).find('img');
            if(!$img.is(':animated')) {
                $(this).find('img').stop().show("slide", {direction: "up"}, 700);
            }
        }
    )

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