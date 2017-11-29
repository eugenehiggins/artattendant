jQuery(document).ready(function($){



    var handlerIn = function(e){
        var target = e.target;
        var $img = $(target).find('img');
        $img.slideUp( "fast", function() {
            // Animation complete.
        });
        console.log($img);
    }
    var handlerOut = function(e){
        var target = e.target;
        var $img = $(target).find('img');
        $img.slideDown( "fast", function() {
            // Animation complete.
        });
    }
    $('div#artists.audience').hover(function(e){
        $(this).find('img').toggle('slide');
    });
});