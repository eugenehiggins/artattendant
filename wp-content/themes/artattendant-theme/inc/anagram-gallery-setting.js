/**
 * Custom Gallery Setting
 */
( function( $ ) {
	var media = wp.media;

	// Wrap the render() function to append controls
	media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
		render: function() {
			media.view.Settings.prototype.render.apply( this, arguments );

			// Append the custom template
			this.$el.append( media.template( 'anagram-gallery-setting' ) );

			// Save the setting
			media.gallery.defaults.type = 'default';
			this.update.apply( this, ['type'] );

						// Hide the Columns setting for all types except Default
/*
			this.$el.find( 'select[name=type]' ).on( 'change', function () {
				var columnSetting = $el.find( 'select[name=columns]' ).closest( 'label.setting' );

				if ( 'default' == $( this ).val() )
					columnSetting.show();
				else
					columnSetting.hide();
			} ).change();
*/

			return this;
		}
	} );
} )( jQuery );