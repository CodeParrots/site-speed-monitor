/**
 * Chart initialization.
 *
 * @author Code Parrots <support@codeparrots.com>
 * @since 1.0.0
 */
( function( $ ) {

	var $chart     = document.getElementById( 'speed-test-chart' ).getContext( '2d' ),
	    labels     = object_to_array( chartSettings.data.labels ),
	    firstView  = object_to_array( chartSettings.data.firstView ),
	    repeatView = object_to_array( chartSettings.data.repeatView );

	var charts = {

		init: function() {

			var toolTipData = {
				enabled: true,
				position: 'nearest',
				mode: 'index',
				intersect: false,
				callbacks: {
					title: function( tooltipItem, data ) {

						return chartSettings.options.toolTipTitle + ' - ' + tooltipItem[0].xLabel;

					},
					label: function( tooltipItem, data ) {

						return data.datasets[ tooltipItem.datasetIndex ].label + ': ' + tooltipItem.yLabel + ' ' + chartSettings.options.secondsLabel;

					},
				}
			};

			/**
			 * Display Diff - Custom Tooltips
			 */
			if ( chartSettings.chartOptions.displayDiff ) {

				toolTipData.custom = charts.customTooltip;

			}

			new Chart( $chart, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [
						{
							label: chartSettings.firstView.label,
							data: firstView,
							borderWidth: 1,
							fill: false,
							backgroundColor: chartSettings.firstView.color,
							borderColor: chartSettings.firstView.color,
							pointBackgroundColor: chartSettings.firstView.color,
							pointBorderColor: chartSettings.firstView.color,
							pointHoverBackgroundColor: chartSettings.firstView.color,
							pointHoverBorderColor: chartSettings.firstView.color
						},
						{
							label: chartSettings.repeatView.label,
							data: repeatView,
							borderWidth: 1,
							fill: false,
							backgroundColor: chartSettings.repeatView.color,
							borderColor: chartSettings.repeatView.color,
							pointBackgroundColor: chartSettings.repeatView.color,
							pointBorderColor: chartSettings.repeatView.color,
							pointHoverBackgroundColor: chartSettings.repeatView.color,
							pointHoverBorderColor: chartSettings.repeatView.color
						}
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					title: {
						display: true,
						text: chartSettings.options.chartTitle,
						fontSize: 16,
					},
					elements: {
						line: {
							tension: 0
						}
					},
					scales: {
						yAxes: [ {
							scaleLabel: {
								display: true,
								labelString: chartSettings.yAxisLabel
							}
						} ],
						xAxes: [ {
							scaleLabel: {
								display: true,
								labelString: chartSettings.xAxisLabel
							}
						} ]
					},
					tooltips: toolTipData,
				},
			} );

		},

		/**
		 * Custom tooltip template.
		 *
		 * @type {Object}
		 *
		 * @param {Objects} tooltip The tooltip object.
		 *
		 * @since 1.0.0
		 */
		customTooltip: function( tooltip ) {

			$( this._chart.canvas ).css( 'cursor', 'pointer' );

			$( '.chartjs-tooltip' ).css( {
				opacity: 0,
				height: 0,
				margin: 0,
			} );

			if ( ! tooltip || ! tooltip.opacity ) {

				return;

			}

			if ( tooltip.dataPoints.length > 0 ) {

				tooltip.dataPoints.forEach( function ( dataPoint ) {

					var displayColor = tooltip.labelColors[ dataPoint.datasetIndex ].backgroundColor,
					    $tooltip     = $( '#site-speed-monitor-diff-tooltip' ),
							$content     = chartSettings.data.toolTipFooters[ dataPoint.index ] ? chartSettings.data.toolTipFooters[ dataPoint.index ] : chartSettings.chartOptions.noDiffMessage;

					$tooltip.html( '<div class="interior">' + $content + '</div>' );

					$tooltip.css( {
						opacity: 1,
						height: 'auto',
						margin: '10px 0 0 0',
					} );

				} );

			}

		},

	};

	/**
	 * Chart options.
	 *
	 * @type {Object}
	 *
	 * @since 1.0.0
	 */
	var chartOptions = {

		/**
		 * Initialize the chart option tooltips.
		 *
		 * @since 1.0.0
		 */
		tooltips: function() {

			$( '.js-option-tooltip' ).each( function() {

				$( this ).tipso( {
					background:   'rgba( 51, 51, 51, 0.80 )',
					tooltipHover: true,
					size:         'medium',
					titleContent: $( this ).data( 'tooltip-title' ),
					content:      $( this ).data( 'tooltip' ),
				} );

			} );

		},

		/**
		 * Toggle a chart option.
		 *
		 * @param {Object} e The clicked element.
		 *
		 * @since 1.0.0
		 */
		toggle: function( e ) {

			e.preventDefault();

			var isChecked  = $( e.target ).attr( 'checked' ),
			    optionName = $( e.target ).attr( 'name' );

			chartOptions.updateOption( optionName, isChecked );

		},

		/**
		 * Update the chart option.
		 *
		 * @param {String}  optionName Chart option name to update.
		 * @param {Boolean} isChecked  Is the option checked.
		 *
		 * @since 1.0.0
		 *
		 * @return {Boolean}           True if the option was updated, else false.
		 */
		updateOption: function( optionName, isChecked ) {

			$.post( ajaxurl, {
				'action'   : 'update_chart_option',
				'option'   : optionName,
				'isChecked': ( 'checked' === isChecked ) ? 1 : 0,
			}, function( response ) {

				$( '.chart-options-notice' ).remove();

				if ( ! response.success ) {

					$( '.chart-options' ).prepend( '<div class="notice notice-error chart-options-notice"><p>' + chartSettings.chartOptions.error + '</p></div>' );

					return;

				}

				$( '.chart-options' ).find( 'input[type="checkbox"]' ).attr( 'disabled', 'disabled' );
				$( '.chart-options' ).prepend( '<div class="notice notice-success chart-options-notice"><p>' + chartSettings.chartOptions.preloader + ' ' + chartSettings.chartOptions.success + '</p></div>' );

				location.reload();

			} );

		}

	};

	/**
	 * Convert a javascript object into a usable array.
	 *
	 * @since 1.0.0
	 *
	 * @return {array} Javascript array.
	 */
	function object_to_array( object ) {

		var array = [];

		for ( var key in object ) {

			array.push( object[ key ] );

		}

		return array;

	}

	$( document ).ready( charts.init );
	$( document ).ready( chartOptions.tooltips );

	$( document ).on( 'change', '.js-chart-option', chartOptions.toggle );

	$( document ).on( 'click', '.row-actions .delete a', function( e ) {

		if ( ! confirm( chartSettings.testDelete.confirmation ) ) {

			e.preventDefault();

			return;

		}

	} );

} )( jQuery );
