"use strict";jQuery(document).ready(function($){$("#acp_export_show_export_button").on("click",function(){if($(this).is(":checked")){$("body").removeClass("ac-hide-export-button")}else{$("body").addClass("ac-hide-export-button")}$.post(ajaxurl,{action:"acp_export_show_export_button",value:$(this).is(":checked"),layout:AC.layout,list_screen:AC.list_screen,_ajax_nonce:AC.ajax_nonce})})});
