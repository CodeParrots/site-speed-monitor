( function( $ ) {

	var confirmation = {

		show: function( e ) {

			e.preventDefault();

			var btnText    = $( this ).text(),
			    popupClass = $( '#clear-test-log' ).attr( 'class' ).replace( 'cp-confirmation-hidden', '' );

			tb_show( null, '#TB_inline?width=400&height=150&inlineId=clear-test-log' );

			$( '#TB_window' )
			 .css( { 'height' : 'auto', 'width' : 'auto', 'margin-left' : '-15%', 'top' : '35%' })
			 .removeClass( 'thickbox-loading' )
			 .addClass( popupClass + ' cp-confirmation cp-animated cp-fadeInDown' )
			 .find( '.actions .button-primary' )
			 .text( btnText );

		},

		hide: function( e ) {

			e.preventDefault();

			$( '#TB_window' ).addClass( 'cp-fadeOutUp' );

			self.parent.tb_remove();

		}

	};

	var site_details = {

		show: function( e ) {

			e.preventDefault();

			tb_show( null, '#TB_inline?width=400&height=150&inlineId=test-site-details' );

			$( '#TB_window' )
			 .css( { 'margin-left': '-30%', 'margin-top':'0', 'max-height':'450px', 'top':'15%' } )
			 .addClass( 'cp-confirmation cp-animated cp-fadeInDown' );

			// Ajax request
			var data = {
				'action' : 'get_test_site_details',
				'test'   : $( this ).data( 'test-id' )
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( ajaxurl, data, function( response ) {

				/**
				 * On success
				 */
				$( '#TB_window' )
				 .css( { 'width': '70%', 'max-height' : '600px' } )
				 .removeClass( 'thickbox-loading' )
				 .find( '#TB_ajaxContent' )
				 .css( { 'height': 'auto', 'width': 'auto' } )
				 .html( response );

			} );

		},

	};

	if ( $( 'div.grade' ).length ) {

		$( 'div.grade' ).each( function() {

			var test_id = $( this ).closest( 'tr' ).find( '.test-id' ).val();

			$( this ).tipso( {
				background:     'rgba( 51, 51, 51, 0.80 )',
				width:          350,
				maxWidth:       '50%',
				useTitle:       true,
				tooltipHover:   true,
				ajaxContentUrl: site_speed_monitor.ajaxurl + '?action=get_test_scores&test=' + test_id,
			} );

		} );

	}

	$( 'body' ).on( 'click', '.clear-test-log', confirmation.show );
	$( 'body' ).on( 'click', '.close-modal', confirmation.hide );

	$( 'body' ).on( 'click', '.test-site-details', site_details.show );

}( jQuery ) );
