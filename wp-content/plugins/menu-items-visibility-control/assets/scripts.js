jQuery(function($){

	// for new menu items, attempt to add the Visibility field
	$( '#update-nav-menu' ).on( 'click', '.item-edit', function(){
		var parent = $( this ).closest( '.menu-item' );
		if ( parent.find( '.field-visibility' ).length ) {
			return;
		}

		var id = parent.find( 'input.menu-item-data-db-id' ).val();

		var template = wp.template( 'menu-items-visivility-control' );
		parent.find( 'p.field-description' ).after( template( {
			'id' : id,
			'value' : ''
		} ) );
	});

	// add fields for previously added menu items 
	$( '#menu-to-edit > li' ).each( function(){
		var $this = $( this ),
			id = $this.find( 'input.menu-item-data-db-id' ).val();

		var value = '';
		if ( typeof MIVC.values[ id ] !== 'undefined' ) {
			value = MIVC.values[ id ];
		}

		var template = wp.template( 'menu-items-visivility-control' );
		$this.find( 'p.field-description' ).after( template( {
			'id' : id,
			'value' : value
		} ) );
	} );

});