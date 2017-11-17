jQuery(function($) {





$('.framed-holder').hide();

/*
$(".checkbox").change(function() {
    if(this.checked) {
        //Do stuff
    }
});
*/

/*

$("form :input").each(function(index, elem) {
    var eId = $(elem).attr("id");
    var label = null;
    if (eId && (label = $(elem).parents("form").find("label[for="+eId+"]")).length == 1) {
        $(elem).attr("placeholder", $(label).html());
        $(label).remove();
    }
 });
*/

			// Repeatable file inputs
/*
			$('.fes-fields').on('click', 'a.insert-file-row', function (e) {
				e.preventDefault();
				var clickedID = $(this).attr('id');
				var max = $('#fes-upload-max-files-'+clickedID ).val();
				var optionContainer = $('.fes-variations-list-'+clickedID);
				var option = optionContainer.find('.fes-single-variation:last');
				var newOption = option.clone();
				delete newOption[1];
				newOption.length = 1;
				var count = optionContainer.find('.fes-single-variation').length;

				// too many files
				if ( count + 1 > max && max != 0 ){
					return alert(fes_form.too_many_files_pt_1 + max + fes_form.too_many_files_pt_2);
				}

				newOption.find('input, select, textarea').val('');
				newOption.find('input, select, textarea').each(function () {
					var name = $(this).attr('name');
					name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');
					$(this)
						.attr('name', name)
						.attr('id', name);

					newOption.insertBefore("#"+clickedID);
				});
				return false;
			});
*/

		$('.fes-fields').on('click', 'a.upload_file_button',  function (e) {
				e.preventDefault();
				var option = $(this).parents('.fes-single-variation');
				var optionContainer = $(this).parents('[class^=fes-variations-list-]');
				var count = optionContainer.find('.fes-single-variation').length;
				var file = option.find('input').val();


					option.find('.upload_file_button').hide();
					option.find('input').hide();
					option.find('.fes-url-row').prepend('<a href="'+file+'"><i class="fa fa-file" aria-hidden="true"></i> Download File</a>');

					return false;

			});
			// Repeatable file inputs
			$('.fes-fields').on('click', 'a.insert-file-row', function (e) {
				e.preventDefault();
				var optionContainer = $('.fes-variations-list-file_upload');

				var option = optionContainer.find('.fes-single-variation:last');
					option.find('input, select, textarea').show();
					option.find('.upload_file_button').show();
					option.find('.fes-url-row a').remove();
					option.find('input, select, textarea').val('');

				return false;
			});

			$('.fes-fields').on('click', 'a.edd-fes-delete', function (e) {
				e.preventDefault();
				var option = $(this).parents('.fes-single-variation');
				var optionContainer = $(this).parents('[class^=fes-variations-list-]');
				var count = optionContainer.find('.fes-single-variation').length;

				if (count == 1) {
					option.find('input, select, textarea').val('');
					//Anagram /  Geet - show field if files removed
					option.find('input, select, textarea').show();
					option.find('.upload_file_button').show();
					option.find('.fes-url-row a').remove();

					return false;
				} else {
					//option.remove();
					return false;
				}
			});




 $('input[name=framed]').on('click',function () {
        if ($(this).is(':checked')) {
           $('.framed-holder').show();
        } else {
           $('.framed-holder').hide();
        }
    });

//$(document).ready(function(){

       if ($('input:radio[name=framed]').is(':checked')) {//console.log('Framed');
        $('.start-number').show();
    }
//});


 $('input[name=auto_inventory]').on('click',function () {
        if ($(this).is(':checked')) {
           $('.start-number').show();
        } else {
           $('.start-number').hide();
        }
    });


//$(document).ready(function(){

   if ($('input[name=auto_inventory]').is(':checked')) {//console.log('Framed');
        $('.start-number').show();
    }
//});

//set measurement as default
 var $radios = $('input:radio[name=measurement]');
    if($radios.is(':checked') === false && artwork_ajax_vars.measurement_default) {
        $radios.filter('[value='+artwork_ajax_vars.measurement_default+']').prop('checked', true);
    }


$('.public_status input').on('change', function () {
	 $('input.edd-submit').removeClass('btn-success btn-warning').addClass('btn-primary');

/*
	     if ($('.public_status input').is(':checked')) {//console.log('1');
       // $two[0].disabled = true;
      // $('.fes-submission-form-div .fes-submit.col-md-5').show();
      //  $('.fes-el.draft-holder').hide();

    }
else

*/if (this.value == 'for sale' ){//console.log('Framed');
        //$('.fes-submission-form-div .fes-submit.col-md-4').show();
         //$('.fes-el.draft-holder').hide();
		 $('input.edd-submit').removeClass('btn-primary btn-warning').addClass('btn-success');
    }else if(this.value == 'for loan' ){
	    $('input.edd-submit').removeClass('btn-primary btn-success').addClass('btn-warning');
    }
    //||this.value == 'for loan'||this.value == 'for view'

   // $('.fes-submit.col-md-5').hide();
   //  $('.fes-el.draft-holder').show();
/*
     if(this.checked) {
        $('input[type=submit]').show();
    }
*/
//console.log(this.value );
/*
if ($('.public_status input').is(':checked')) {//console.log('1');
       // $two[0].disabled = true;
      // $('.fes-submission-form-div .fes-submit.col-md-5').show();
      //  $('.fes-el.draft-holder').hide();

    }
*/


});

/*
function showhideHield(){

}
*/



		function template(data, container) {
		  return 'hank';
		}

		$('.fes-fields #download_category').select2({
		    theme: "bootstrap",
		     multiple: false,
		/*
		    id: '-1', // the value of the option
		    placeholder: 'Search an medium',
		*/
		    //allowClear: true,
		/*
		      templateResult: function (data) {
			      if(data.element) console.log(data.element.className);
			       //debugger;
				    if (data.element && data.element.className === 'level-0') { // adjust for custom placeholder values
				      return 'Custom styled placeholder text';
				    }

				    return data.text;
				  }
		*/
		});

		/*
		$('#artist').select2({
		    theme: "bootstrap",
		     multiple: false,
		});
		*/


		 var dt = $('#download_tag').outerWidth()-2;

		    $(".ac_results").width(dt);

		    $(window).resize(function() {
		        var dt = $('#download_tag').outerWidth()-2;
		        $(".ac_results").width(dt);
		    });


		 var cw = $('#location').outerWidth()-2;
		    $(".ac_results").width(cw);
		    	// console.log(cw);

		    $(window).resize(function() {

		        var cw = $('#location').outerWidth()-2;
		        $(".ac_results").width(cw);
		    });



	jQuery('#location').suggest( artwork_ajax_vars.ajaxurl + '?action=anagram_ajax_location_search', { delay: 100, minchars: 2, multiple: false, multipleSep: ', ' } );





		//console.log(fes_form);

		/*
		$("#download_category").select2({
		    tags: false,
		    multiple: false,
		    //tokenSeparators: [',', ' '],
		    minimumInputLength: 2,
		   // minimumResultsForSearch: 1,
		    ajax: {
		        url: fes_form.ajaxurl + '?action=fes_ajax_taxonomy_search&tax=download_category',
		        dataType: "text",
		        type: "POST",
		        data: function (params) {

		            var queryParameters = {
		                term: params.term
		            }
		            return queryParameters;
		        },
		        processResults: function (data) {
		            return {
		                results: $.map(data, function (item) {
		                    return {
		                        text: item.tag_value,
		                        id: item.tag_id
		                    }
		                })
		            };
		        }
		    }
		});
		*/




		/*
			jQuery(function(){
				jQuery('#download_category').suggest( fes_form.ajaxurl + '?action=fes_ajax_taxonomy_search&tax=download_category', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
			});
		*/
/*
var json = [{"ime":"BioPlex TM"},{"ime":"Aegis sym agrilla"},{"ime":"Aegis sym irriga"},{"ime":"Aegis sym microgranulo"},{"ime":"Aegis sym pastiglia"},{"ime":"Agroblen 15816+3MgO"},{"ime":"Agroblen 18816+3MgO"},{"ime":"Agrobor 15 HU"},{"ime":"Agrocal (Ca + Mg)"},{"ime":"Agrocal (Ca)"},{"ime":"Agrogold"},{"ime":"Agroleaf Power 12525+ME"},{"ime":"Agroleaf Power 151031+ME"},{"ime":"Agroleaf Power 202020+ME"},{"ime":"Agroleaf Power 311111+ME"},{"ime":"Agroleaf Power Ca"},{"ime":"Agrolution 14714+14 CaO+ME"},{"ime":"Agrovapno dolomitno"},{"ime":"Agrovit HSF"},{"ime":"Agrovit P"},{"ime":"Agrozin 32 T"},{"ime":"Albatros Hydro"},{"ime":"Albatros Sprint"},{"ime":"Albatros Standard"},{"ime":"Albatros Universal"},{"ime":"Algaren"},{"ime":"AlgoVital ? Plus"},{"ime":"Amalgerol PREMIUM"},{"ime":"Amcolon \/ Novalon"},{"ime":"Amcopaste"},{"ime":"Aminosprint N8"},{"ime":"AminoVital"},{"ime":"Ammonium nitrate 33.5%"},{"ime":"Ammonium nitrate with calcium sulfate"},{"ime":"Ammonium sulfate"}];

$("#location").select2({
    width: '300px',
    ajax: {
        type: 'post',
       // url: '/echo/json/',
        dataType: 'json',
        data: function () {
            return {
                json: JSON.stringify(json),
                delay: 0.3
            };
        },
        processResults: function (data) {
            return {
                results: $.map(data, function(obj) {
                    return { id: obj.ime, text: obj.ime };
                })
            };
        }
    }
});
*/

/*
jQuery.getJSON('https://artattendant.com/wp-json/artattendant_api/v2/products/?user=1&per_page=300').done(
    function( data ) {

        data = $.map(data, function(item) {
            return { id: item.location, text: item.location };
        });

         $("#location").select2({
	       // tags: true,
            placeholder: '',
            allowClear: false,
            minimumInputLength: 0,
            multiple: false,
            data: data,
             createTag: function (params) {
	             console.log(params);
			    var term = $.trim(params.term);

			    if (term === '') {
			      return null;
			    }

			    return {
			      id: term,
			      text: term,
			      newTag: true // add additional parameters
			    }
			  }
            createTag: function (data) {
			    return {
			      id: data.term,
			      text: data.term,
			      newOption: true
			    }
			  }
        }).on("blur", function(e) {
	        console.log(e);
		    var isNew = $(this).find('[data-select2-tag="true"]');
		    if(isNew.length){
		        isNew.replaceWith('<option selected value="'+isNew.val()+'">'+isNew.val()+'</option>');
		        $('#console').append('<code>New tag: {"' + isNew.val() + '":"' + isNew.val() + '"}</code><br>');
		    }
		});
    }
);
*/






/*
     $.getJSON('https://artattendant.com/wp-json/artattendant_api/v2/products/?user=1&per_page=300', function(data) {

        var output = "";

        $.each(data, function(key, val) {
            output += '<option value="' + val.location + '" data-foo="">' + val.location + '</option>';
        });
        $("#location").html(output);

    });
*/


/*
$("#location").select2({
    minimumInputLength: 2,
    ajax: {
        url: 'https://artattendant.com/wp-json/artattendant_api/v2/products/?user=1&per_page=300',
	    dataType: 'json',
	    delay: 250,
	    data: function (params) {
	      return {
	        q: params.term, // search term
	        page: params.page
	      };
	    },
	    processResults: function (data, params) {
	      // parse the results into the format expected by Select2
	      // since we are using custom formatting functions we do not need to
	      // alter the remote JSON data, except to indicate that infinite
	      // scrolling can be used
	      params.page = params.page || 1;

	      return {
	        results: data.items,
	        pagination: {
	          more: (params.page * 30) < data.total_count
	        }
	      };
	    },
        cache: true
    }
});
*/


// /wp-json/artattendant_api/v2/products/?user=<?php echo get_current_user_id() ?>&per_page=300
/*
		$('#artheight').selectize({
		    valueField: 'url',
		    labelField: 'name',
		    searchField: 'name',
		    create: false,
		    render: {
		        option: function(item, escape) {
		            return '<div>' +
		                '<span class="title">' +
		                    '<span class="name"><i class="icon ' + (item.fork ? 'fork' : 'source') + '"></i>' + escape(item.name) + '</span>' +
		                    '<span class="by">' + escape(item.username) + '</span>' +
		                '</span>' +
		                '<span class="description">' + escape(item.description) + '</span>' +
		                '<ul class="meta">' +
		                    (item.language ? '<li class="language">' + escape(item.language) + '</li>' : '') +
		                    '<li class="watchers"><span>' + escape(item.watchers) + '</span> watchers</li>' +
		                    '<li class="forks"><span>' + escape(item.forks) + '</span> forks</li>' +
		                '</ul>' +
		            '</div>';
		        }
		    },
		    score: function(search) {
		        var score = this.getScoreFunction(search);
		        return function(item) {
		            return score(item) * (1 + Math.min(item.watchers / 100, 1));
		        };
		    },
		    load: function(query, callback) {
		        if (!query.length) return callback();
		        $.ajax({
		            url: 'https://api.artsy.net/api/search?client_id=85fefdb850a145f620cc&client_secret=b724e0fb1227a5e09b0c6b06821a82b0&q=' + encodeURIComponent(query),
		            type: 'GET',
		            error: function() {
		                callback();
		            },
		            success: function(res) {
		                callback(res.repositories.slice(0, 10));
		            }
		        });
		    }
		});
*/




  // Get the form fields and hidden div
  var checkbox = $(".framed .fes-checkbox-checklist input");
  var hidden = $(".framed-holder");
 // var populate = $("#populate");

  // Hide the fields.
  // Use JS to do this in case the user doesn't have JS
  // enabled.
  hidden.hide();

  // Setup an event listener for when the state of the
  // checkbox changes.
  checkbox.change(function() {
    // Check to see if the checkbox is checked.
    // If it is, show the fields and populate the input.
    // If not, hide the fields.
    if (checkbox.is(':checked')) {
      // Show the hidden fields.
      hidden.show();
      // Populate the input.
     // populate.val("Dude, this input got populated!");
    } else {
      // Make sure that the hidden fields are indeed
      // hidden.
      hidden.hide();

      // You may also want to clear the value of the
      // hidden fields here. Just in case somebody
      // shows the fields, enters data to them and then
      // unticks the checkbox.
      //
      // This would do the job:
      //
      // $("#hidden_field").val("");
    }
  });



/*
var $one = $('#one'),
    $two = $('#two');
*/


/*
var fValidate = $.parselyConditions({
        formname: 'fes-submission-form',
        validationfields: [{
            fid: 'dimensions_type',
            ftype: 'radio',
            fvalue: 'Framed',
            faffected: 'framed_width',
            //fhide: 'fes-fields'
        }],
    });

$('.fes-ajax-form fes-submission-form').parsley();
*/
 /* $('.fes-ajax-form fes-submission-form').parsley().on('field:validated', function() {
    var ok = $('.parsley-error').length === 0;
    $('.bs-callout-info').toggleClass('hidden', !ok);
    $('.bs-callout-warning').toggleClass('hidden', ok);
  })
/*
  .on('form:submit', function() {
    return false; // Don't submit form for this demo
  });
*/

/*


		var testimonial_ok=false;
		//Inputs that determine what fields to show
		var rating = $('#live_form input:radio[name=rating]');
		var testimonial=$('#live_form input:radio[name=testimonial]');

		//Wrappers for all fields
		var bad = $('#live_form textarea[name="feedback_bad"]').parent();
		var ok = $('#live_form textarea[name="feedback_ok"]').parent();
		var great = $('#live_form textarea[name="feedback_great"]').parent();
		var testimonial_parent = $('#live_form #div_testimonial');
		var thanks_anyway  = $('#live_form #thanks_anyway');
		var all=bad.add(ok).add(great).add(testimonial_parent).add(thanks_anyway);

		rating.change(function(){
			var value=this.value;
			all.addClass('hidden'); //hide everything and reveal as needed

			if (value == 'Bad' || value == 'Fair'){
				bad.removeClass('hidden');
			}
			else if (value == 'Good' || value == 'Very Good'){
				ok.removeClass('hidden');
			}
			else if (value == 'Excellent'){
				testimonial_parent.removeClass('hidden');
				if (testimonial_ok == 'yes'){great.removeClass('hidden');}
				else if (testimonial_ok == 'no'){thanks_anyway.removeClass('hidden');}
			}
		});


		testimonial.change(function(){
			all.addClass('hidden');
			testimonial_parent.removeClass('hidden');

			testimonial_ok=this.value;

			if (testimonial_ok == 'yes'){
				great.removeClass('hidden');
			}
			else{
				thanks_anyway.removeClass('hidden');
			}

		});
*/

});//End on load