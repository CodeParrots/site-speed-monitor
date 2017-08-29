( function( $ ) {

	/**
	 * Repeating key/value Test Parmaeters settings fields.
	 * @since 1.0.0
	 * @type {Object}
	 */
	var settings = {

		addParameter: function( e ) {

			e.preventDefault();

			var $parentContainer = $( e.currentTarget ).closest( 'section.param' ),
			    $clone           = $parentContainer.clone();

			settings.cloneParameter( $clone );

			$parentContainer.find( '.add-parameter' ).remove();

		},

		cloneParameter: function( $element ) {

			var new_length = $( '.parameter-list > .param' ).length;

			$element.find( 'input[type="text"]' ).first().attr( 'name', 'site_speed_monitor_options[test_parameters][keys][' + new_length + ']' );
			$element.find( 'input[type="text"]' ).last().attr( 'name', 'site_speed_monitor_options[test_parameters][values][' + new_length + ']' );

			$element.appendTo( '.parameter-list' ).find( 'input[type="text"]' ).val( '' );

		},

		faqs: function( e ) {

			$( this ).toggleClass( 'open' );

			$( this ).next().slideToggle( 200 );

		},

		changeToggleLocationBrowsers: function( e ) {

			var newLocation    = $( this ).val(),
			    $browserSelect = $( 'select[name="site_speed_monitor_options[test_browser]"]' );

			console.log( newLocation );

			$browserSelect.find( '.browser-option' ).addClass( 'hidden' ).removeAttr( 'selected' );
			$browserSelect.find( '.' + newLocation + '-browser' ).removeClass( 'hidden' ).first().attr( 'selected', 'selected' );

		},

	};

	/**
	 * Clear the Site Speed Monitor logs.
	 * @since 1.0.0
	 * @type {Object}
	 */
	var logs = {

		remove_logs: function() {

			if ( ! confirm( speedCheckSettings.removeLogConfirmation ) ) {

				return;

			}

			$( '.clear-log-error' ).remove();

			$.post( ajaxurl, {
				'action': 'clear_site_speed_monitor_logs',
				'nonce' : $( this ).data( 'nonce' ),
			}, function( response ) {

				var $table = $( '.wp-list-table.speedchecklog' );

				if ( response.success ) {

					$table.find( '#the-list' ).html( '<tr class="no-items"><td class="colspanchange" colspan="2">' + speedCheckSettings.NoLogsMessage + '</td></tr>' );

					// Clear pagination containers
					$table.prev().html( '' );
					$table.next().html( '' );

					return;

				}

				// Prepend the error response.
				$table.prev().before( '<div class="site-speed-monitor-notice notice-error clear-log-error"><p>' + response.data.error + '</p></div>' );

			} );

		},

	};

	/**
	 * Documentation Sub Navigation functionality.
	 * @since 1.0.0
	 * @type {Object}
	 */
	var docSubNavigation = {

		/**
		 * Toggle the 'mouse' class on mouseenter.
		 * This allows keyboard navigation to still inherit accessibility markup.
		 */
		toggleClass: function() {

			$( this ).toggleClass( 'mouse' );

			$( this ).on( 'mouseleave', function() {

				$( this ).removeClass( 'mouse' );

			} );

		},

		/**
		 * Toggle the visible documentation container.
		 * @param {object} e Event.
		 * @since 1.0.0
		 */
		toggleDocs: function( e ) {

			e.preventDefault();

			var section = $( this ).data( 'section' );

			$( this ).blur();

			docSubNavigation.updateURL( section );
			docSubNavigation.toggleActive( $( this ).closest( 'li' ) );
			docSubNavigation.toggleVisible( section );

		},

		/**
		 * Update the URL with the proper query string.
		 * @since 1.0.0
		 */
		updateURL: function( section ) {

			var currURL = window.location.href,
			    found   = ( currURL.indexOf( '&section=' ) !== -1 ),
			    newURL  = found ? currURL.replace( /(&section=)[^\&]+/, '$1' + section ) : ( currURL + '&section=' + section );

			window.history.pushState( 'section', section, newURL );

		},

		/**
		 * Toggle the active link.
		 * @since 1.0.0
		 */
		toggleActive: function( li ) {

			$( '.documentation-submenu li.link' ).removeClass( 'active' );

			li.addClass( 'active' );

		},

		/**
		 * Toggle the active class on hover.
		 * @since 1.0.0
		 */
		toggleVisible: function( section ) {

			$( '.postbox' ).addClass( 'hidden' );

			$( '.postbox.' + section ).removeClass( 'hidden' );

		},

	};

	// Main nav.
	$( 'body' ).on( 'mouseenter', '.nav-tab', docSubNavigation.toggleClass );
	$( 'body' ).on( 'click', '.nav-tab', function() {

		$( this ).blur();

	} );

	// Documentation navigation.
	$( 'body' ).on( 'mouseenter', '.documentation-submenu a.section', docSubNavigation.toggleClass );
	$( 'body' ).on( 'click', '.documentation-submenu a.section', docSubNavigation.toggleDocs );

	// FAQ.
	$( 'dd' ).filter( ':nth-child(n+4)' ).addClass( 'hide' );
	$( 'dl' ).on( 'click', 'dt.question', settings.faqs );

	// Settings, additional API parameters.
	$( 'body' ).on( 'click', '.add-parameter', settings.addParameter );

	// Change the test location, display new test browsers.
	$( 'body' ).on( 'change', 'select[name="site_speed_monitor_options[test_location]"]', settings.changeToggleLocationBrowsers );

	// Settings, clear logs.
	$( 'body' ).on( 'click', '.clear-site-speed-monitor-log', logs.remove_logs );

	// Prevent developer mode link from being clicked.
	$( 'a.nav-tab.developer-mode' ).attr( 'onclick', 'return false;' );

}( jQuery ) );
