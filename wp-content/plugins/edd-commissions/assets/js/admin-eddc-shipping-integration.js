jQuery(document).ready(function($) {
	// Shipping Integration Script:

	// If both commissions and shipping are enabled, show Shipping Split options in the commissions metabox
	$.fn.edd_commissions_shipping_show_hide = function() {

		if( $( "#edd_commisions_enabled" ).is( ':checked' ) && $( "#edd_enable_shipping" ).is( ':checked' ) && $( "input[name='edd_commission_settings[type]']:checked" ).val() == 'percentage' ){
			$( "#edd_commissions_shipping_fee_split" ).show();
		}else{
			$( "#edd_commissions_shipping_fee_split" ).hide();
		}

		return this;
	}

	// If both commissions and shipping are enabled, show this option in the commissions metabox
	$( document ).on( 'click change', "#edd_commisions_enabled, #edd_enable_shipping, input[name='edd_commission_settings[type]']", function( event ){
		$( document ).edd_commissions_shipping_show_hide();
	});

	// Run it on page load to show the field appropriately
	$( document ).edd_commissions_shipping_show_hide();
});
