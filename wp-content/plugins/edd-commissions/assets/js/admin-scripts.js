jQuery(document).ready(function($) {

	$('#commission-payouts').submit(function() {
		if( confirm( eddc_vars.confirm_payout ) ) {
			return true;
		}
		return false;
	});

	if ($('.edd_datepicker').length > 0) {
		var dateFormat = 'mm/dd/yy';
		$('.edd_datepicker').datepicker({
			dateFormat: dateFormat
		});
	}

	if ($('.edd_commission_datepicker').length > 0) {
		var dateFormatMySQL = 'yy-mm-dd';
		$('.edd_commission_datepicker').datepicker({
			dateFormat: dateFormatMySQL
		});
	}

	$('.eddc-commissions-export-toggle').click( function() {
		$('.eddc-commissions-export-toggle').toggle();
		$('#eddc-export-commissions').toggle();
	});

	$('body').on('click', '.eddc-download-payout-file', function(e) {
		$(this).attr('disabled', 'disabled');
		$('#eddc-export-commissions').hide();
		$('#eddc-export-commissions-mark-as-paid').show();
		window.scrollTo(0, 0);
	});

	$("#edd_commisions_enabled").change( function() {
		var checked = $(this).is(':checked');
		var target  = $('.eddc_toggled_row');

		if ( checked ) {
			if ( $('body').hasClass('mobile')) {
				$('.edd_repeatable_row').find('.edd-select-chosen').css('width', '100%');
			} else {
				$('.edd_repeatable_row').find('.edd-select-chosen').css('width', '300px');
			}
			target.show();
		} else {
			target.hide();
		}
	});

	$("#edd_download_commissions").on( 'click', function() {
		if (! $('#edd_download_commissions').hasClass('closed')) {
			var checked = $('#edd_commisions_enabled').is(':checked');
			var target  = $('.eddc_toggled_row');

			if ( checked ) {
				if ( $('body').hasClass('mobile')) {
					$('.edd_repeatable_row').find('.edd-select-chosen').css('width', '100%');
				} else {
					$('.edd_repeatable_row').find('.edd-select-chosen').css('width', '300px');
				}
				target.show();
			} else {
				target.hide();
			}
		}
	});

	$('.eddc-add-commission input[name="type"]').on('change', function () {
		var value  = $(this).val();
		var target = $('#eddc-add-rate-row');

		if ( value === 'percentage' ) {
			target.find('input').prop('disabled', '');
		} else {
			target.find('input').prop('disabled','disabled');
		}
	});

	/**
	 * Commission Configuration Metabox
	 */
	var EDD_Commission_Configuration = {
		init : function() {
			this.add();
			this.remove();
		},
		clone_repeatable : function(row) {

			// Retrieve the highest current key
			var key = highest = 1;
			row.parent().find( 'tr.edd_repeatable_row' ).each(function() {
				var current = $(this).data( 'key' );
				if( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest += 1;

			clone = row.clone();

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			clone.removeClass( 'edd_add_blank' );

			clone.attr( 'data-key', key );
			clone.find( 'td input, td select, textarea' ).val( '' );
			clone.find( 'input, select, textarea' ).each(function() {
				var name = $( this ).attr( 'name' );
				var id   = $( this ).attr( 'id' );

				if( name ) {

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( key ) + ']');
					$( this ).attr( 'name', name );

				}

				$( this ).attr( 'data-key', key );

				if( typeof id != 'undefined' ) {

					id = id.replace( /(\d+)/, parseInt( key ) );
					$( this ).attr( 'id', id );

				}

			});

			clone.find( 'span.edd_price_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( 'span.edd_file_id' ).each(function() {
				$( this ).text( parseInt( key ) );
			});

			clone.find( '.edd_repeatable_default_input' ).each( function() {
				$( this ).val( parseInt( key ) ).removeAttr('checked');
			});

			clone.find( '.edd_repeatable_condition_field' ).each ( function() {
				$( this ).find( 'option:eq(0)' ).prop( 'selected', 'selected' );
			} )

			// Remove Chosen elements
			clone.find( '.search-choice' ).remove();
			clone.find( '.chosen-container' ).remove();

			return clone;
		},

		add : function() {
			$( document.body ).on( 'click', '.submit .edd_commission_rates_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( 'tr' ),
				clone = EDD_Commission_Configuration.clone_repeatable(row);

				clone.insertAfter( row ).find('input, textarea, select').filter(':visible').eq(0).focus();

				// Setup chosen fields again if they exist
				clone.find('.edd-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_single: edd_vars.one_option,
					placeholder_text_multiple: edd_vars.one_or_more_option,
				});
				clone.find( '.edd-select-chosen' ).css( 'width', '300px' );
				//clone.find( '.edd-select-chosen .chosen-search input' ).attr( 'placeholder', edd_vars.search_placeholder );
			});
		},

		remove : function() {
			$( document.body ).on( 'click', '.edd_commissions_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					repeatable = 'tr.edd_repeatable_commissions',
					focusElement,
					focusable,
					firstFocusable;

					// Set focus on next element if removing the first row. Otherwise set focus on previous element.
					if ( $(this).is( '.ui-sortable tr:first-child .edd_remove_repeatable:first-child' ) ) {
						focusElement  = row.next( 'tr' );
					} else {
						focusElement  = row.prev( 'tr' );
					}

					focusable  = focusElement.find( 'select, input, textarea, button' ).filter( ':visible' );
					firstFocusable = focusable.eq(0);

				if( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
					firstFocusable.focus();
				} else {
					alert( edd_vars.one_field_min );
				}

				/* re-index after deleting */
				$(repeatable).each( function( rowIndex ) {
					$(this).data( 'key', rowIndex );
					$(this).find( 'input, select' ).each(function() {
						var name = $( this ).attr( 'name' );
						if ( typeof name !== 'undefined' ) {
							name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
							$( this ).attr( 'name', name ).attr( 'id', name );
						}
					});
				});
			});
		},

	};

	EDD_Commission_Configuration.init();

	/**
	 * Add Commission Configuration
	 */
	var EDD_Add_Commission_Configuration = {
		init : function() {
			this.verify();
			this.type();
			this.status();
		},
		verify : function() {
			$('#add-item-info').submit(function(event) {
				$('div.alert').remove();
				var required_fields = $(this).find('input.required,select.required');
				var errors_detected = false;
				required_fields.each(function() {
					if (!$(this).val() || '' == $(this).val() || 0 == $(this).val()) {
						errors_detected = true;
					}
				});

				if ( errors_detected ) {
					var notice = '<div class="alert error"><p>' + eddc_vars.required_fields + '</p></div>';
					$('#edd-item-card-wrapper').before(notice);
					event.preventDefault();
				}
			});
		},
		type : function() {
			$('input[name="type"]').change(function() {
				var type = $(this).val();
				if ( 'percentage' === type ) {
					$('#eddc-add-rate-row').attr('disabled', '');
				} else {
					$('#eddc-add-rate-row').attr('disabled', 'disabled');
				}
			});
		},
		status : function() {
			$('select[name="status"]').change(function() {
				var status = $(this).val();
				var target = $('#date_paid');
				if ( 'paid' == status ) {
					target.removeAttr('disabled');
				} else {
					target.attr('disabled', 'disabled');
				}
			});
		}
	};
	EDD_Add_Commission_Configuration.init();

	$('#eddc-commission-delete-comfirm').change( function() {
		var submit_button = $('#eddc-delete-commission');

		if ( $(this).prop('checked') ) {
			submit_button.attr('disabled', false);
		} else {
			submit_button.attr('disabled', true);
		}
	});

	$('.eddc-edit-commission').on('click', function(e) {
		e.preventDefault();

		var link = $(this);

		if (link.text() === eddc_vars.action_edit) {
			link.text(eddc_vars.action_cancel);
		} else {
			$('#eddc_update_commission').fadeOut('fast', function () {
				$(this).css('display', 'none');
			});
			link.text(eddc_vars.action_edit);
		}

		$('#edit-item-info input.edd_commission_datepicker').toggle();
		$('#eddc_user_chosen').toggle();
		$('#eddc_download_chosen').toggle();
		$('.eddc-commission-rate').toggle();
		$('.eddc-commission-amount').toggle();
	});

	$('body').on( 'change', '.eddc-commission-card input', function(){
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	var EDD_Commission_Reports_Configuration = {
		init : function() {
			this.type();
		},
		type : function() {
			$('select[name="edd-export-class"]').change( function(e) {
				var value = $(this).val();
				if ( 'EDD_Batch_Commissions_Report_Export' == value) {
					$('#eddc_export_status').attr('disabled','disabled');
				} else {
					$('#eddc_export_status').removeAttr('disabled');
				}
			});
		},

	};

	EDD_Commission_Reports_Configuration.init();
});
