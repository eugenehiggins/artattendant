jQuery(document).ready(function($) {
	// Recurring Integration Script:

	// If both commissions and shipping are enabled, show Recurring options in the commissions metabox
	$.fn.edd_commissions_recurring_show_hide = function() {

		if( $( "#edd_commisions_enabled" ).is( ':checked' ) ){
			$( "#edd_commissions_recurring" ).show();
		}else{
			$( "#edd_commissions_recurring" ).hide();
		}

		return this;
	}

	// If both commissions and recurring are enabled, show this option in the commissions metabox
	$( document ).on( 'click change', "#edd_commisions_enabled", function( event ){
		$( document ).edd_commissions_recurring_show_hide();
	});

	// Run it on page load to show the field appropriately
	$( document ).edd_commissions_recurring_show_hide();
});
