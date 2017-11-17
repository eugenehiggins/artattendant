/*
 * LightCheckout  v1.1
 *
 * Copyright 2015, Claudio Sperti, http://www.lightcheckout.com
 *
 */
/* !function(a){var b,c,d={},e={inputWrapper:"div",autocomplete:null,address:".address",city:".city",province:".province",zip:".zip",shortProvince:!0,noNumberError:"Please, insert also street number.",language:"en",setField:function(a,b){a.val(b)}},f=!0,g={types:["address"]};a.fn.loadedGp=function(){f&&(f=!1,d.form.length>0&&d.form.trigger("initGp"))},a.fn.lightCheckout=function(f){if(0===this.length)return this;if(this.length>1)return console.error("lightCheckout: you have to initialize 1 form at time."),this;d.form=a(this);var h=function(){d.settings=a.extend({},e,f);return d.settings.inputs={address:d.form.find(d.settings.address),city:d.form.find(d.settings.city),province:d.form.find(d.settings.province),zip:d.form.find(d.settings.zip)},1!==d.settings.inputs.address.length||1!==d.settings.inputs.city.length||1!==d.settings.inputs.province.length||1!==d.settings.inputs.zip.length?void console.error("lightCheckout: form input missed."):(d.settings.wrappers={address:d.settings.inputs.address.closest(d.settings.inputWrapper),city:d.settings.inputs.city.closest(d.settings.inputWrapper),province:d.settings.inputs.province.closest(d.settings.inputWrapper),zip:d.settings.inputs.zip.closest(d.settings.inputWrapper)},"function"!=typeof a.getScript?void console.error("lightCheckout: getScript function missed."):(null===d.settings.autocomplete?j():i(),k(),void("undefined"==typeof google||"undefined"==typeof google.maps||"undefined"==typeof google.maps.places?(a.getScript("https://maps.googleapis.com/maps/api/js?libraries=places&language="+d.settings.language+"&callback=$.fn.loadedGp"),d.form.one("initGp",m)):m())))},i=function(){d.clonedInput=d.form.find(d.settings.autocompleteInput),c=d.clonedInput.find("input").attr("id")},j=function(){d.clonedInput=d.settings.wrappers.address.clone().addClass("lcWrapper"),b=d.settings.inputs.address.attr("id"),d.clonedInput.find("input").attr("id",b+"_lcInput").attr("autocomplete","off").removeAttr("required").removeAttr("name"),d.settings.wrappers.address.before(d.clonedInput.attr("id",b+"_lcWrapper")),newInptId=b+"_lcInput"},k=function(){d.settings.wrappers.address.addClass("lc-input lc-hide"),d.settings.wrappers.city.addClass("lc-input lc-hide"),d.settings.wrappers.zip.addClass("lc-input lc-hide"),d.settings.wrappers.province.addClass("lc-input lc-hide")},l=function(){d.settings.wrappers.address.removeClass("lc-hide"),d.settings.wrappers.city.removeClass("lc-hide"),d.settings.wrappers.zip.removeClass("lc-hide"),d.settings.wrappers.province.removeClass("lc-hide"),d.clonedInput.addClass("lc-hide")},m=function(){if("undefined"!=typeof newInptId&&"undefined"!=typeof g){var a=document.getElementById(newInptId);autocomplete=new google.maps.places.Autocomplete(a,g),google.maps.event.addListener(autocomplete,"place_changed",n)}},n=function(){var b=autocomplete.getPlace(),c="",e="",f="",g="",h="",i="",j="";if("undefined"!=typeof b&&"undefined"!=typeof b.address_components){for(var k=0;k<b.address_components.length;k++){var m=b.address_components[k],n=m.types[0];"street_number"==n&&(c=m.long_name),"route"==n&&(e=m.long_name),"locality"==n&&(f=m.long_name),"administrative_area_level_3"==n&&(f=m.long_name),"administrative_area_level_2"==n&&(h=m),"postal_code"==n&&(g=m.long_name),"administrative_area_level_1"==n&&(i=m),"country"==n&&(j=m.short_name)}h="US"==j?i:h,h=d.settings.shortProvince?h.short_name:h.long_name,""===c&&(d.settings.inputs.address.after('<span class="error noNumError">'+d.settings.noNumberError+"</span>"),d.settings.inputs.address.one("focus",function(){a(this).siblings(".noNumError").remove()})),d.settings.setField(d.settings.inputs.address,e+" "+c),d.settings.setField(d.settings.inputs.city,f),d.settings.setField(d.settings.inputs.zip,g),d.settings.setField(d.settings.inputs.province,h),l()}};h()}}(jQuery); */



/**
 * jQuery Geocoding and Places Autocomplete Plugin - V 1.6.5
 *
 * @author Martin Kleppe <kleppe@ubilabs.net>, 2014
 * @author Ubilabs http://ubilabs.net, 2014
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 */// # $.geocomplete()
// ## jQuery Geocoding and Places Autocomplete Plugin
//
// * https://github.com/ubilabs/geocomplete/
// * by Martin Kleppe <kleppe@ubilabs.net>
(function(e,t,n,r){function u(t,n){this.options=e.extend(!0,{},i,n),this.input=t,this.$input=e(t),this._defaults=i,this._name="geocomplete",this.init()}var i={bounds:!0,country:null,map:!1,details:!1,detailsAttribute:"name",detailsScope:null,autoselect:!0,location:!1,mapOptions:{zoom:14,scrollwheel:!1,mapTypeId:"roadmap"},markerOptions:{draggable:!1},maxZoom:16,types:["geocode"],blur:!1,geocodeAfterResult:!1,restoreValueAfterBlur:!1},s="street_address route intersection political country administrative_area_level_1 administrative_area_level_2 administrative_area_level_3 colloquial_area locality sublocality neighborhood premise subpremise postal_code natural_feature airport park point_of_interest post_box street_number floor room lat lng viewport location formatted_address location_type bounds".split(" "),o="id place_id url website vicinity reference name rating international_phone_number icon formatted_phone_number".split(" ");e.extend(u.prototype,{init:function(){this.initMap(),this.initMarker(),this.initGeocoder(),this.initDetails(),this.initLocation()},initMap:function(){if(!this.options.map)return;if(typeof this.options.map.setCenter=="function"){this.map=this.options.map;return}this.map=new google.maps.Map(e(this.options.map)[0],this.options.mapOptions),google.maps.event.addListener(this.map,"click",e.proxy(this.mapClicked,this)),google.maps.event.addListener(this.map,"dragend",e.proxy(this.mapDragged,this)),google.maps.event.addListener(this.map,"idle",e.proxy(this.mapIdle,this)),google.maps.event.addListener(this.map,"zoom_changed",e.proxy(this.mapZoomed,this))},initMarker:function(){if(!this.map)return;var t=e.extend(this.options.markerOptions,{map:this.map});if(t.disabled)return;this.marker=new google.maps.Marker(t),google.maps.event.addListener(this.marker,"dragend",e.proxy(this.markerDragged,this))},initGeocoder:function(){var t=!1,n={types:this.options.types,bounds:this.options.bounds===!0?null:this.options.bounds,componentRestrictions:this.options.componentRestrictions};this.options.country&&(n.componentRestrictions={country:this.options.country}),this.autocomplete=new google.maps.places.Autocomplete(this.input,n),this.geocoder=new google.maps.Geocoder,this.map&&this.options.bounds===!0&&this.autocomplete.bindTo("bounds",this.map),google.maps.event.addListener(this.autocomplete,"place_changed",e.proxy(this.placeChanged,this)),this.$input.on("keypress."+this._name,function(e){if(e.keyCode===13)return!1}),this.options.geocodeAfterResult===!0&&this.$input.bind("keypress."+this._name,e.proxy(function(){event.keyCode!=9&&this.selected===!0&&(this.selected=!1)},this)),this.$input.bind("geocode."+this._name,e.proxy(function(){this.find()},this)),this.$input.bind("geocode:result."+this._name,e.proxy(function(){this.lastInputVal=this.$input.val()},this)),this.options.blur===!0&&this.$input.on("blur."+this._name,e.proxy(function(){if(this.options.geocodeAfterResult===!0&&this.selected===!0)return;this.options.restoreValueAfterBlur===!0&&this.selected===!0?setTimeout(e.proxy(this.restoreLastValue,this),0):this.find()},this))},initDetails:function(){function i(e){r[e]=t.find("["+n+"="+e+"]")}if(!this.options.details)return;if(this.options.detailsScope)var t=e(this.input).parents(this.options.detailsScope).find(this.options.details);else var t=e(this.options.details);var n=this.options.detailsAttribute,r={};e.each(s,function(e,t){i(t),i(t+"_short")}),e.each(o,function(e,t){i(t)}),this.$details=t,this.details=r},initLocation:function(){var e=this.options.location,t;if(!e)return;if(typeof e=="string"){this.find(e);return}e instanceof Array&&(t=new google.maps.LatLng(e[0],e[1])),e instanceof google.maps.LatLng&&(t=e),t&&(this.map&&this.map.setCenter(t),this.marker&&this.marker.setPosition(t))},destroy:function(){this.map&&(google.maps.event.clearInstanceListeners(this.map),google.maps.event.clearInstanceListeners(this.marker)),this.autocomplete.unbindAll(),google.maps.event.clearInstanceListeners(this.autocomplete),google.maps.event.clearInstanceListeners(this.input),this.$input.removeData(),this.$input.off(this._name),this.$input.unbind("."+this._name)},find:function(e){this.geocode({address:e||this.$input.val()})},geocode:function(t){if(!t.address)return;this.options.bounds&&!t.bounds&&(this.options.bounds===!0?t.bounds=this.map&&this.map.getBounds():t.bounds=this.options.bounds),this.options.country&&(t.region=this.options.country),this.geocoder.geocode(t,e.proxy(this.handleGeocode,this))},selectFirstResult:function(){var t="";e(".pac-item-selected")[0]&&(t="-selected");var n=e(".pac-container:last .pac-item"+t+":first span:nth-child(2)").text(),r=e(".pac-container:last .pac-item"+t+":first span:nth-child(3)").text(),i=n;return r&&(i+=" - "+r),this.$input.val(i),i},restoreLastValue:function(){this.lastInputVal&&this.$input.val(this.lastInputVal)},handleGeocode:function(e,t){if(t===google.maps.GeocoderStatus.OK){var n=e[0];this.$input.val(n.formatted_address),this.update(n),e.length>1&&this.trigger("geocode:multiple",e)}else this.trigger("geocode:error",t)},trigger:function(e,t){this.$input.trigger(e,[t])},center:function(e){e.viewport?(this.map.fitBounds(e.viewport),this.map.getZoom()>this.options.maxZoom&&this.map.setZoom(this.options.maxZoom)):(this.map.setZoom(this.options.maxZoom),this.map.setCenter(e.location)),this.marker&&(this.marker.setPosition(e.location),this.marker.setAnimation(this.options.markerOptions.animation))},update:function(e){this.map&&this.center(e.geometry),this.$details&&this.fillDetails(e),this.trigger("geocode:result",e)},fillDetails:function(t){var n={},r=t.geometry,i=r.viewport,s=r.bounds;e.each(t.address_components,function(t,r){var i=r.types[0];e.each(r.types,function(e,t){n[t]=r.long_name,n[t+"_short"]=r.short_name})}),e.each(o,function(e,r){n[r]=t[r]}),e.extend(n,{formatted_address:t.formatted_address,location_type:r.location_type||"PLACES",viewport:i,bounds:s,location:r.location,lat:r.location.lat(),lng:r.location.lng()}),e.each(this.details,e.proxy(function(e,t){var r=n[e];this.setDetail(t,r)},this)),this.data=n},setDetail:function(e,t){t===r?t="":typeof t.toUrlValue=="function"&&(t=t.toUrlValue()),e.is(":input")?e.val(t):e.text(t)},markerDragged:function(e){this.trigger("geocode:dragged",e.latLng)},mapClicked:function(e){this.trigger("geocode:click",e.latLng)},mapDragged:function(e){this.trigger("geocode:mapdragged",this.map.getCenter())},mapIdle:function(e){this.trigger("geocode:idle",this.map.getCenter())},mapZoomed:function(e){this.trigger("geocode:zoom",this.map.getZoom())},resetMarker:function(){this.marker.setPosition(this.data.location),this.setDetail(this.details.lat,this.data.location.lat()),this.setDetail(this.details.lng,this.data.location.lng())},placeChanged:function(){var e=this.autocomplete.getPlace();this.selected=!0;if(!e.geometry){if(this.options.autoselect){var t=this.selectFirstResult();this.find(t)}}else this.update(e)}}),e.fn.geocomplete=function(t){var n="plugin_geocomplete";if(typeof t=="string"){var r=e(this).data(n)||e(this).geocomplete().data(n),i=r[t];return typeof i=="function"?(i.apply(r,Array.prototype.slice.call(arguments,1)),e(this)):(arguments.length==2&&(i=arguments[1]),i)}return this.each(function(){var r=e.data(this,n);r||(r=new u(this,t),e.data(this,n,r))})}})(jQuery,window,document);



/*
var autocomplete = new google.maps.places.Autocomplete(jQuery("#geocomplete")[0], {});

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var place = autocomplete.getPlace();
                console.log(place.address_components);
            });
*/


    jQuery(document).bind('gform_post_render', function(){

	    jQuery('.ginput_container_address').before('<div id="geocomplete_holder" class="form-group"><input id="geocomplete" tabindex="1013" class="form-control" type="text" placeholder="Populate fields from your street address or enter them below" value="" /><span id="geo_error" class="help-block"></span></div>');
	    //jQuery('.ginput_container_address').hide();
        jQuery("#geocomplete").geocomplete({
          //map: ".map_canvas",
          //details: ".ginput_container_address",
          //detailsAttribute: "data-geo",
         // types: ["geocode", "establishment"],
        }).bind("geocode:result", function(event, result){

	        jQuery('#geocomplete_holder').removeClass('has-error');
			jQuery('#geo_error').text('');

	              // Create a simplified version of the address components.
		      jQuery.each(result.address_components, function(index, object){
		        var name = object.types[0];

		        jQuery.each(object.types, function(index, type){
			        if( type == "street_number" ) sNumber = object.long_name;
					if( type == "route" ) address = object.long_name;
					if( type == "locality" ) city = object.long_name;
					if( type == "administrative_area_level_3" ) county = object.long_name;
					if( type == "administrative_area_level_1" ) province = object.short_name;
					if( type == "postal_code" ) zip = object.long_name;
					if( type == "postal_code_suffix" ) postal_code_suffix = object.short_name;
					if( type == "country" ) country = object.long_name;
		          //console.log(object.long_name);
		          //data[name + "_short"] = object.short_name;
		        });
		      });


			  if( typeof sNumber !== 'undefined' ){
	         	 jQuery('.street-address').val(sNumber+' '+address);
				 jQuery('.city').val(city);
				 jQuery('.state-province').val(province);
				 jQuery('.country').val(country).change();
				 jQuery('.zip-postal-code').val(zip);
				 jQuery('#geocomplete_holder').hide();
				 jQuery('.ginput_container_address').slideDown();
			}else{
				jQuery('#geocomplete_holder').addClass('has-error').find('input').focus();
				jQuery('#geo_error').text('Please, add a street number.');

			};

            //console.log(result);
            //result.address_components[0]
          })
          .bind("geocode:error", function(event, status){
           console.log("ERROR: " + status);
           //jQuery('.ginput_container_address').slideDown();
          })
          .bind("geocode:multiple", function(event, results){
           console.log("Multiple: " + results.length + " results found");
           //jQuery('.ginput_container_address').slideDown();
          });
/*
        jQuery("#find").click(function(){
          jQuery("#geocomplete").trigger("geocode");
        });
*/
    });



/*
    jQuery(document).bind('gform_post_render', function(){



				// create the options object

				var options = {
				   inputWrapper : 'span',
				   //autocomplete: null,
				   address: '.street-address',
				   city: '.city',
				   province: '.state-province',
				   zip: '.zip-postal-code',
				  // shortProvince: true,
				   //noNumberError: 'Please, insert also street number.',
				   //language: 'en',

				   //setField: function ($input, value) {
				   //   $input.val(value);
				   //}

				};

				// initialize lightCheckout on your form.
				//container class .ginput_container_address

				//jQuery('#gform_11').lightCheckout(options);


    });
*/