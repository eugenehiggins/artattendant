
// Disabling autoDiscover, otherwise Dropzone will try to attach twice.
Dropzone.autoDiscover = false;

jQuery(function($) {

$("#media-uploader").sortable({
    items:'.dz-image-preview',
    cursor: 'move',
    opacity: 0.5,
    containment: "parent",
    distance: 20,
    tolerance: 'pointer',
    update: function(e, ui){

        var current_queue = [];
		$('#media-uploader .dz-complete').each( function(){
			//console.log($(this).data('file-id'));
			current_queue.push($(this).data('file-id'));
		});
		jQuery('#media-ids').val(current_queue);
    }
});



/*
If anyone else stumbles onto here and is wondering how to do this, here is how I did it.

Every time a file is added store the file data onto the element:

dropzone_ins.on('addedfile', function(file) {

});
Then before I called the processQueue() function I cleared the queued files and then looped through them then re-added them in the custom order:

var current_queue = [];
$('.sortable_img > li').each( function(){
    current_queue.push($(this).data('file'));
});

dropzone_ins.removeAllFiles();

for(i=0;i<current_queue.length;i++){
    dropzone_ins.addFile(current_queue[i]);
}

dropzone_ins.processQueue();
*/

    var sendDataObject = {
        'action': 'handle_dropped_media',
        //'do_action': 'uploadFile',
        'handle_dropped_media_nonce': dropParam.nonce
    };



var myDropzone = new Dropzone("#media-uploader", {
    url: dropParam.ajaxurl,
    params: sendDataObject,
    dictRemoveFile: '',
    dictCancelUpload: '',
    hiddenInputContainer: "#media-uploader .addfile",
    clickable : "#media-uploader .addfile",
    acceptedFiles: 'image/*',
    init: function() {
	    //console.log('test');
		//Allow adding image to edd uploads folder
	    $.post(fes_form.ajaxurl,{ action:'fes_turn_on_file_filter'}, function (res) { });

	        var self = this;
	         $.each(ar, function(index, val) {
		        // console.log(val);
			    var mockFile = { name: val.name, fullPath: val.url, thumb: val.thumb, attachment_id: val.attachment_id, size: val.size };
			    self.emit("addedfile",mockFile);
			     $(mockFile.previewElement).attr('data-file-id', val.attachment_id); // adds a custom id to the preview element.
			   // self.files.push(mockFile);
			   self.emit("thumbnail", mockFile, val.thumb); //use this to load a smaller image
			   self.emit("complete", mockFile);
/*
			    self.createThumbnailFromUrl(mockFile, val.url, function() {
						 self.emit("complete", mockFile);
				    });
*/

			  });
			  self._updateMaxFilesReachedClass();

			  $('.addfile').appendTo($('#media-uploader')).animate({ opacity: '1' });

    },

    uploadprogress: function (file, progress) {
	    file.previewElement.querySelector(".dz-upload").style.width = Math.abs(progress-100) + "%";
    },
/*    totaluploadprogress: function(progress) {
		  document.querySelector(".dz-upload").style.width = progress + "%";
		},
*/
    success: function (file, response) {
	    //console.log(response);
        file.previewElement.setAttribute("data-file-id", response.data.media_id);
        file.previewElement.classList.add("dz-success");
        file['attachment_id'] = response.data.media_id; // push the id for future reference
        var ids = jQuery('#media-ids').val();

		if(jQuery('#media-ids').val()==''){
			ids = response.data.media_id;
		}else{
			ids = ids + ',' + response.data.media_id;
		}


        jQuery('#media-ids').val(ids);
        this.emit("thumbnail", file, response.data.image_url);
        //console.log(ids);
        $('.addfile').appendTo($('#media-uploader')).animate({ opacity: '1' });
    },
/*
    error: function (file, response) {
        file.previewElement.classList.add("dz-error");
    },
*/
/*
    addedfile: function(file) {
	    $('.sortable_img li:last-of-type').data('file', file);
	    //file.previewElement = Dropzone.createElement(this.options.previewTemplate);
	    // Now attach this new element some where in your page
	  },
*/
//server handles rotate of uploaded image using exif data
//response from upload server contains image_url and status

    // update the following section is for removing image from library
    addRemoveLinks: true,
    removedfile: function(file) {
	    //console.log(file);
        var image_id = file.attachment_id;
        jQuery.ajax({
            url: dropParam.ajaxurl,
            type: 'POST',
            dataType: 'JSON',
            data: {
	            'action': 'handle_delete_media',
                'media_id' : image_id,
                'handle_delete_media_nonce': dropParam.nonce
            },
            success: function (response) {
	            //console.log(response.data.deleted_id);
	            var ids = jQuery('#media-ids').val();
	            var idsarray = ids.split(',');
				//remove deleted item form field val
	            idsarray = jQuery.grep(idsarray, function(value) {
				  return value != image_id;
				});

				jQuery('#media-ids').val(idsarray);
		     //console.log("Details saved successfully!!!");
		      },
		      error: function (xhr, ajaxOptions, thrownError) {

		      //  alert(xhr.status);
		       // alert(thrownError);
		      }
        });
        var _ref;
        return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
    }
});


myDropzone.on("addedfile", function (file) {
     // Build progress bar here
       $('.addfile').appendTo($('#media-uploader')).css('opacity', '0.0');
});

// Try to access the image's EXIF data to fix the orientation on the client.
/*
this.on('thumbnail', (file) => {
  // loadImage comes from blueimp-load-image
  window.loadImage.parseMetaData(file, (data) => {
    if (data.exif) {
      // In case the EXIF is readable, we display a canvas element
      // with the rotated image and hide the default thumbnail.
      window.loadImage(file, (img) => {
        filepicker.classList.remove('js-dropzone--show-thumbmail');
        document.querySelector('.dz-image').appendChild(img);
      }, {
        orientation: data.exif.get('Orientation'),
      });
    } else {
      // In case the EXIF data is unavailable, we just show the default thumbnail.
      filepicker.classList.add('js-dropzone--show-thumbmail');
    }
  });
}
*/


});//End on load



/*
// Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
var previewNode = document.querySelector("#template");
previewNode.id = "";
var previewTemplate = previewNode.parentNode.innerHTML;
previewNode.parentNode.removeChild(previewNode);

var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
  url: "/target-url", // Set the url
  thumbnailWidth: 80,
  thumbnailHeight: 80,
  parallelUploads: 20,
  previewTemplate: previewTemplate,
  autoQueue: false, // Make sure the files aren't queued until manually added
  previewsContainer: "#previews", // Define the container to display the previews
  clickable: ".anagram-uploader" // Define the element that should be used as click trigger to select files.
});

myDropzone.on("addedfile", function(file) {
  // Hookup the start button
  file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
});

// Update the total progress bar
myDropzone.on("totaluploadprogress", function(progress) {
  document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
});

myDropzone.on("sending", function(file) {
  // Show the total progress bar when upload starts
  document.querySelector("#total-progress").style.opacity = "1";
  // And disable the start button
  file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
});

// Hide the total progress bar when nothing's uploading anymore
myDropzone.on("queuecomplete", function(progress) {
  document.querySelector("#total-progress").style.opacity = "0";
});

// Setup the buttons for all transfers
// The "add files" button doesn't need to be setup because the config
// `clickable` has already been specified.
*/
/*
document.querySelector("#actions .start").onclick = function() {
  myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
};
document.querySelector("#actions .cancel").onclick = function() {
  myDropzone.removeAllFiles(true);
};
*/
