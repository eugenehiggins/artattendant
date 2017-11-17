jQuery(document).ready( function($) {
    iosif_open_pointer(0);
    function iosif_open_pointer(i) {
        pointer = iosifPointer.pointers[i];
        options = $.extend( pointer.options, {
            close: function() {
                $.post( ajaxurl, {
                    pointer: pointer.pointer_id,
                    action: 'dismiss-wp-pointer'
                });
            }
        });
 
        $(pointer.target).pointer( options ).pointer('open');
    }
});