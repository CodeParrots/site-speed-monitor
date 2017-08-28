<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Tools {

	use Helpers;

	private $sections;

	private $chart_data;

	private $chart_options;

	public function __construct() {

		add_action( 'admin_menu', [ $this, 'tools_page' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );

		if ( empty( Helpers::option( 'api_key', '' ) ) ) {

			return;

		}

		/**
		 * Filter the tool sections.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		$this->sections = (array) apply_filters( 'site_speed_monitor_tools_sections', [
			'charts'          => __( 'Charts', 'site-speed-monitor' ),
			'history'         => __( 'Test History', 'site-speed-monitor' ),
			/**
			 * @todo Setup the recommendations tool
			 */
			// 'recommendations' => __( 'Recommendations', 'site-speed-monitor' ),
		] );

		$this->chart_options = Helpers::option( 'chart_options' );

	}

	/**
	 * Enqueue scripts & styles.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {

		global $pagenow;

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( 'tools.php' !== $pagenow || 'site-speed-monitor-tools' !== $page ) {

			return;

		}

		$this->chart_data = $this->get_chart_data();

		if ( empty( $this->chart_data ) ) {

			return;

		}

		$rtl    = ! is_rtl() ? '' : '-rtl';
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'tipso',     \Site_Speed_Monitor::$assets_url . "/js/tipso{$suffix}.js", [ 'jquery' ], '1.0.8', true );
		wp_enqueue_script( 'chart-js',  \Site_Speed_Monitor::$assets_url . "/js/Chart{$suffix}.js", [ 'tipso' ], '2.6.0', true );
		wp_enqueue_script( 'chart-init', \Site_Speed_Monitor::$assets_url . "/js/chart-init{$suffix}.js", [ 'chart-js' ], \Site_Speed_Monitor::$version, true );

		wp_enqueue_style( 'site-speed-monitor-chart', \Site_Speed_Monitor::$assets_url . "/css/site-speed-monitor-chart{$rtl}{$suffix}.css", [], \Site_Speed_Monitor::$version, 'all' );
		wp_enqueue_style( 'tipso', \Site_Speed_Monitor::$assets_url . "/css/tipso{$rtl}{$suffix}.css", [], '1.0.8', 'all' );

		$labels = array_map( function( $date ) {

			/**
			 * Note: When the chart is set to one test per day, don't bother
			 *       displaying the time in the chart label.
			 *
			 * @var string
			 */
			$format = ( isset( $this->chart_options['one_per_day'] ) && $this->chart_options['one_per_day'] ) ? get_option( 'date_format' ) : get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

			return date_i18n( $format, $date );

		}, wp_list_pluck( $this->chart_data, 'date' ) );

		$colors = Helpers::get_chart_colors();

		/**
		 * Allow users to filter the chart settings.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		$chart_data = (array) apply_filters( 'site_speed_monitor_chart_settings', [
			'data'      => [
				'labels'         => $labels,
				'firstView'      => wp_list_pluck( $this->chart_data, 'first_view' ),
				'repeatView'     => wp_list_pluck( $this->chart_data, 'repeat_view' ),
				'toolTipFooters' => wp_list_pluck( $this->chart_data, 'site_details' ),
			],
			'yAxisLabel' => esc_html__( 'Site Load Time (seconds)', 'site-speed-monitor' ),
			'xAxisLabel' => esc_html__( 'Speed Test Date', 'site-speed-monitor' ),
			'firstView' => [
				'label' => esc_html__( 'First View', 'site-speed-monitor' ),
				'color' => isset( $colors[0] ) ? $colors[0] : 'rgba(54, 162, 235, 1)',
			],
			'repeatView' => [
				'label' => esc_html__( 'Repeat View', 'site-speed-monitor' ),
				'color' => isset( $colors[1] ) ? $colors[1] : 'rgba(255,99,132,1)',
			],
			'options' => [
				'chartTitle'   => esc_html__( 'Site Speed Monitor - Performance Graph', 'site-speed-monitor' ),
				'toolTipTitle' => ( isset( $this->chart_options['one_per_day'] ) && $this->chart_options['one_per_day'] ) ? esc_html__( 'Average Load Times', 'site-speed-monitor' ) : esc_html__( 'Load Times', 'site-speed-monitor' ),
				'secondsLabel' => esc_html__( 'seconds', 'site-speed-monitor' ),
			],
			'chartOptions' => [
				'error'         => esc_html__( 'There was an error updating the chart options.', 'site-speed-monitor' ),
				'success'       => esc_html__( 'Chart options updated. Regenerating the chart.', 'site-speed-monitor' ),
				'displayDiff'   => ( isset( $this->chart_options['display_diff'] ) && $this->chart_options['display_diff'] ),
				'onePerDay'     => ( isset( $this->chart_options['one_per_day'] ) && $this->chart_options['one_per_day'] ),
				'noDiffMessage' => esc_html__( 'No change from the previous test.', 'site-speed-monitor' ),
				'preloader'     => sprintf(
					'<img src="%s" class="preloader" />',
					admin_url( 'images/wpspin_light.gif' )
				),
			],
			'testDelete' => [
				'confirmation' => esc_html__( 'Are you sure you want to delete this speed test? This cannot be undone.', 'site-speed-monitor' ),
			],
		] );

		wp_localize_script( 'chart-init', 'chartSettings', $chart_data );

	}

	/**
	 * Register Site Speed Monitor settings page inside of 'Settings > Site Speed Monitor'
	 *
	 * @since 1.0.0
	 */
	public function tools_page() {

		add_submenu_page(
			'tools.php',
			__( 'Site Speed Monitor - Tools', 'site-speed-monitor' ),
			__( 'Site Speed Monitor', 'site-speed-monitor' ),
			'manage_options',
			'site-speed-monitor-tools',
			[ $this, 'tools' ]
		);

	}

	/**
	 * Tools page markup
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the tools page.
	 */
	public function tools() {

		if ( empty( Helpers::option( 'api_key', '' ) ) ) {

			Helpers::site_speed_monitor_notice( 'error', sprintf(
				/* translators: 1. 'settings page' wrapped in an anchor tag. */
				__( 'Enter your WebPageTest.org API key on the Site Speed Monitor %s.', 'site-speed-monitor' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					admin_url( 'options-general.php?page=site-speed-monitor' ),
					esc_html( 'settings page', 'site-speed-monitor' )
				)
			), false );

			return;

		}

		$header_parrot = [
			'url' => sprintf(
				\Site_Speed_Monitor::$assets_url . '/images/%sheader-parrot.png',
				Helpers::is_developer_mode() ? 'developer-' : ''
			),
			'alt' => Helpers::is_developer_mode() ? __( 'Developer Mode', 'site-speed-monitor' ) : __( 'Code Parrots Mascot', 'site-speed-monitor' ),
		];

		?>

		<div class="wrap">

			<div class="header">

				<!-- Speedometer icon by Icons8 -->
				<div class="icon site-speed-monitor-icon"></div>

				<!-- Site Speed Monitor text logo -->
				<div class="logo site-speed-monitor-text"><span class="version">v<?php echo esc_html( \Site_Speed_Monitor::$version ); ?></span></div>

				<!-- Parrot Mascot -->
				<div class="parrot-mascot"><img src="<?php echo esc_url( $header_parrot['url'] ); ?>" class="parrot" alt="<?php echo esc_attr( $header_parrot['alt'] ); ?>" title="<?php echo esc_attr( $header_parrot['alt'] ); ?>" /></div>

			</div>

			<?php

			if ( empty( $this->chart_data ) ) {

				$rtl    = ! is_rtl() ? '' : '-rtl';
				$suffix = SCRIPT_DEBUG ? '' : '.min';

				printf(
					'<p class="description">%s</p>',
					esc_html__( 'You have not run any speed tests yet, or they have not yet completed. Check back here when your speed tests have completed.', 'site-speed-monitor' )
				);

				wp_enqueue_style( 'site-speed-monitor-chart', \Site_Speed_Monitor::$assets_url . "/css/site-speed-monitor-chart{$rtl}{$suffix}.css", [], \Site_Speed_Monitor::$version, 'all' );

				return;

			}

			?>

			<p class="description">
				<?php esc_html_e( 'View additional Site Speed Monitor tools below.', 'site-speed-monitor' ); ?>
			</p>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-1">

					<div id="post-body-content">

						<?php $this->generate_sections(); ?>

					</div>

				</div>

				<br class="clear">

			</div>

		</div>

		<?php

	}

	/**
	 * Generate the sections
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Tab content markup.
	 */
	private function generate_sections() {

		if ( empty( $this->sections ) ) {

			return;

		}

		foreach ( $this->sections as $section_slug => $section ) {

			if ( empty( $this->chart_data ) && 'recommendations' === $section_slug ) {

				continue;

			}

			printf(
				'<div class="postbox site-speed-monitor-%1$s" id="%1$s">
					<div class="meta-box-sortables ui-sortable">
						<div class="inside">%2$s</div>
					</div>
				</div>',
				esc_attr( $section_slug ),
				$this->generate_content( $section_slug )
			);

		}

	}

	/**
	 * Generate the section content.
	 *
	 * @param  string $section The content to render.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed          Markup for the section content.
	 */
	private function generate_content( $section = '' ) {

		switch ( $section ) {

			case 'charts':
				return sprintf(
					/* translators: 1. Chart. 2. Chart options title. 3. Chart Options. */
					'%1$s
					<div class="chartjs-tooltip" id="site-speed-monitor-diff-tooltip"></div>
					<div class="chart-options">
						<h4>%2$s</h4>
						%3$s
					</div>',
					( ! empty( $this->chart_data ) ) ? '<div class="chart" style="height: 400px;"><canvas id="speed-test-chart" width="100%" height="400"></canvas></div>' : 'empty',
					esc_html__( 'Chart Options', 'site-speed-monitor' ),
					$this->get_chart_options()
				);

				break;

			case 'history':

				ob_start();

				printf(
					'<h3>%1$s</h3>
					<p class="description">%2$s</p>',
					esc_html__( 'Site Speed Test History', 'site-speed-monitor' ),
					esc_html__( 'Below is a complete list of all speed tests run on this site.', 'site-speed-monitor' )
				);

				$table = new Tests_Table();

				$table->prepare_items();

				$length = (int) $table->_pagination_args['total_items'];
				$class  = ( 0 >= $length ) ? 'no-tests' : 'tests';

				if ( 0 < $length ) {

					$suffix = SCRIPT_DEBUG ? '' : '.min';

					wp_enqueue_script( 'site-speed-monitor-widget', \Site_Speed_Monitor::$assets_url . "/js/widget{$suffix}.js", [ 'tipso', 'thickbox' ], \Site_Speed_Monitor::$version, true );

					wp_localize_script( 'site-speed-monitor-widget', 'site_speed_monitor', [
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
					] );

				}

				?>

				<div class="site-speed-monitor-list <?php echo esc_attr( $class ); ?>">

					<?php $table->display(); ?>

				</div>

				<br class="clear" />

				<?php

				if ( (int) $length >= 1 ) {

					printf(
						'<a href="#" class="button clear-test-log">%1$s</a>',
						esc_html__( 'Clear Tests', 'site-speed-monitor' )
					);

				}

				/**
				 * Action hook to add content after the speed test widget.
				 *
				 * @since 1.0.0
				 */
				do_action( 'site_speed_monitor_tools_history_bottom' );

				add_thickbox();

				$contents = ob_get_contents();

				ob_get_clean();

				return $contents;

				break;

			case 'recommendations':
				return sprintf(
					/* translators: Recommendations section title. */
					'<h2>%1$s</h2>',
					__( 'Recommendations', 'site-speed-monitor' )
				);

				break;

			default:
				/**
				 * Output custom section tab content.
				 *
				 * @param string The section name.
				 */
				do_action( 'site_speed_monitor_tools_section_content', $section );

				break;

		} // End switch().

	}

	/**
	 * Get the chart data to pass to the javascript file.
	 *
	 * @return array Chart data.
	 */
	public function get_chart_data() {

		$completed_tests = Helpers::option( 'completed_tests', [] );

		if ( empty( $completed_tests ) ) {

			return false;

		}

		/**
		 * Filter the higher max completed tests length.
		 *
		 * If a user has an excessive number of tests run (say 1000+), this will
		 * only display chart data for the latest 50 test runs.
		 *
		 * Note: It's important to note, if multiple test were run on a single day,
		 *       only one test will be returned. This ensure only the last test run
		 *       on any single day is displayed.
		 *
		 * @var integer
		 */
		$completed_tests_max = (int) apply_filters( 'site_speed_monitor_chart_data_max', 50 );

		$completed_tests = array_slice( $completed_tests, 0, $completed_tests_max );

		$chart_data = [];

		$previous_details = false;

		foreach ( $completed_tests as $test ) {

			if ( ! isset( $test['data'] ) || ! isset( $test['data']['testData'] ) || ! isset( $test['data']['testData']['data'] ) ) {

				continue;

			}

			$current_details = Helpers::get_test_site_details( $test['data']['testId'] );

			$site_details = [
				'current'  => $current_details,
				'previous' => $previous_details,
			];

			$previous_details = $current_details;

			// Setup chart data.
			$chart_data[] = [
				'date'         => $test['start_date'],
				'site_details' => $site_details,
				'first_view'   => Helpers::get_load_time( $test['data']['testData']['data'], 'first-view' ),
				'repeat_view'  => Helpers::get_load_time( $test['data']['testData']['data'], 'repeat-view' ),
			];

		}

		$chart_data = Helpers::sort_tests( $chart_data, true, 'date' );

		if ( isset( $this->chart_options['newest_first'] ) && $this->chart_options['newest_first'] ) {

			$chart_data = array_reverse( $chart_data );

		}

		if ( isset( $this->chart_options['one_per_day'] ) && $this->chart_options['one_per_day'] ) {

			$chart_data = $this->one_test_per_day( $chart_data );

		}

		/**
		 * Site details not working when one test per day is run,
		 * because the current site details array is empty.
		 */

		$chart_data = $this->append_test_differences( $chart_data );

		return $chart_data;

	}

	/**
	 * Loop through tests and calculate the differences between current and previous.
	 *
	 * @param array $chart_data Test data array.
	 *
	 * @since 1.0.0
	 *
	 * @return Array            Test data differences array.
	 */
	public function append_test_differences( $chart_data ) {

		if ( empty( $chart_data ) ) {

			return $chart_data;

		}

		foreach ( $chart_data as $index => $data ) {

			$site_details = $data['site_details'];
			$date         = $data['date'];
			$current      = isset( $site_details['current'] ) ? $site_details['current'] : false;
			$previous     = isset( $site_details['previous'] ) ? $site_details['previous'] : false;

			$chart_data[ $index ]['site_details'] = $this->diff_markup( $date, $current, $previous );

		}

		return $chart_data;

	}

	/**
	 * Generate the tooltip difference markup.
	 *
	 * @param integer $date             The date the test ran, used for transients.
	 * @param array   $site_details     Current test site details.
	 * @param array   $previous_details Previous test site details.
	 *
	 * @since 1.0.0
	 *
	 * @return string                 Markup for the difference tooltip.
	 */
	public function diff_markup( $date, $site_details, $previous_details ) {

		$transient_name = "site_speed_monitor_test_{$date}_diff_markup";
		$transient      = get_transient( $transient_name );

		// Check for an existing markup transient, so we don't recalculate each page load.
		if ( ! Helpers::is_developer_mode() && false !== $transient ) {

			return $transient;

		}

		if ( empty( $site_details ) ) {

			return false;

		}

		$differences = [
			'added'   => Helpers::get_added_details( $site_details, $previous_details ),
			'removed' => Helpers::get_removed_details( $site_details, $previous_details ),
		];

		if ( '08/09/2017' === date( 'm/d/Y', $date ) ) {
			// wp_die( print_r( $previous_details ) );
		}

		if ( empty( $differences['added'] ) && empty( $differences['removed'] ) ) {

			return;

		}

		$merged = [
			'theme'   => null,
			'plugins' => null,
			'site'    => null,
		];

		$markup = null;

		foreach ( $differences as $type => $diff ) {

			if ( isset( $diff['theme'] ) && ! empty( $diff['theme'] ) ) {

				$merged['theme'] .= sprintf(
					'<div class="%1$s">%2$s %3$s%4$s</div>',
					esc_attr( $type ),
					( 'added' === $type ) ? '+' : '-',
					empty( $diff['theme']['name'] ) ? '' : esc_html( $diff['theme']['name'] ),
					empty( $diff['theme']['version'] ) ? '' : ' ' . sprintf(
						/* translators: Theme version. */
						esc_html__( 'version %s', 'site-speed-monitor' ),
						esc_html( $diff['theme']['version'] )
					)
				);

			}

			if ( isset( $diff['plugins'] ) && ! empty( $diff['plugins'] ) ) {

				foreach ( $diff['plugins'] as $index => $plugin ) {

					$merged['plugins'] .= sprintf(
						'<div class="%1$s">%2$s %3$s%4$s</div>',
						esc_attr( $type ),
						( 'added' === $type ) ? '+' : '-',
						empty( $plugin['name'] ) ? '' : esc_html( $plugin['name'] ),
						empty( $plugin['version'] ) ? '' : ' ' . sprintf(
							/* translators: Theme version. */
							esc_html__( 'version %s', 'site-speed-monitor' ),
							esc_html( $plugin['version'] )
						)
					);

				} // @codingStandardsIgnoreLine

			}

			if ( isset( $diff['site'] ) && ! empty( $diff['site'] ) ) {

				foreach ( $diff['site'] as $key => $value ) {

					$merged['site'] .= sprintf(
						'<div class="%1$s">%2$s %3$s: %4$s</div>',
						esc_attr( $type ),
						( 'added' === $type ) ? '+' : '-',
						esc_html( $key ),
						wp_kses_post( $value )
					);

				} // @codingStandardsIgnoreLine

			} // @codingStandardsIgnoreLine

		}

		foreach ( $merged as $type => $data ) {

			switch ( $type ) {

				default:
				case 'theme':

					$type = '<h4>' . esc_html__( 'Theme', 'site-speed-monitor' ) . '</h4>';

					break;

				case 'plugins':

					$type = '<h4>' . esc_html__( 'Plugins', 'site-speed-monitor' ) . '</h4>';

					break;

				case 'site':

					$type = '<h4>' . esc_html__( 'Site Details', 'site-speed-monitor' ) . '</h4>';

					break;

			}

			if ( empty( $data ) ) {

				continue;

			}

			$markup .= $type . $data;

		}

		set_transient( $transient_name, $markup );

		return $markup;

	}

	/**
	 * Get the chart options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of chart options.
	 */
	public function get_chart_options() {

		/**
		 * Filter the chart options.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		$options = (array) apply_filters( 'site_speed_monitor_chart_options', [
			'one_per_day'  => [
				'label'   => esc_html__( 'Unify Tests', 'site-speed-monitor' ),
				'tooltip' => esc_html__( 'Unify multiple tests run on a single day and average the first and repeat load times.', 'site-speed-monitor' ),
			],
			'newest_first' => [
				'label'   => esc_html__( 'Flip Chart', 'site-speed-monitor' ),
				'tooltip' => esc_html__( 'Reverse the order that the tests are displayed on the chart.', 'site-speed-monitor' ),
			],
			'display_diff' => [
				'label'    => esc_html__( 'Display Differences', 'site-speed-monitor' ),
				'tooltip'  => esc_html__( 'Display site changes between the current and previous tests. This option can be used to help trackdown plugins impacting page load speeds.', 'site-speed-monitor' ),
				'disabled' => Helpers::is_local( false ),
				'message'  => __( 'The difference check tool cannot be used on a localhost environment. Migrate to a live site to enable this option.', 'site-speed-monitor' ),
			],
		] );

		$chart_option_array = Helpers::option( 'chart_options', [] );
		$chart_options      = '';

		foreach ( $options as $key => $data ) {

			$disabled = ( isset( $data['disabled'] ) && $data['disabled'] ) ? 'disabled="disabled"' : '';
			$message  = $disabled ? ( isset( $data['message'] ) ? sprintf(
				/* translators: Reason the tool is disabled. */
				'<p>' . esc_html__( 'Disabled: %s', 'site-speed-monitor' ) . '<p>',
				$data['message']
			) : '' ) : '';

			$chart_options .= sprintf(
				'<label class="chart-option-label">
					<input type="checkbox" class="site-speed-monitor-chart-one-per-day js-chart-option" value="1" name="%1$s" %2$s %3$s />
					%4$s
					%5$s
				</label>',
				esc_attr( $key ),
				isset( $chart_option_array[ $key ] ) ? checked( $chart_option_array[ $key ], 1, false ) : '',
				$disabled,
				esc_html( $data['label'] ),
				isset( $data['tooltip'] ) ? sprintf(
					'<span class="dashicons dashicons-editor-help js-option-tooltip" data-tooltip="%1$s" data-tooltip-title="%2$s"></span>',
					wp_kses_post( $data['tooltip'] . $message ),
					esc_attr( $data['label'] )
				) : ''
			);

		}

		return $chart_options;

	}

	/**
	 * Only allow a single test per day in the test array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of chart data.
	 */
	public function one_test_per_day( $chart_data ) {

		if ( empty( $chart_data ) ) {

			return $chart_data;

		}

		$previous_day = false;
		$iteration    = 0;
		$match_key    = 0;
		$new_data     = [];

		foreach ( $chart_data as $test_data ) {

			// If the current day and previous day match, unset it.
			if ( $this->days_match( $previous_day['date'], $test_data['date'], $iteration ) ) {

				if ( '08/09/2017' === date( 'm/d/Y', $test_data['date'] ) ) {

					// wp_die( print_r( $test_data ) );

				}

				$new_data[ $match_key ]['first_view'][]  = $test_data['first_view'];
				$new_data[ $match_key ]['repeat_view'][] = $test_data['repeat_view'];

				$new_data[ $match_key ]['site_details'] = isset( $new_data[ $match_key ]['site_details'] ) ? array_merge( $new_data[ $match_key ]['site_details'], $test_data['site_details'] ) : $test_data['site_details'];

				unset( $chart_data[ $iteration ] );

			} else {

				if ( isset( $previous_day['date'] ) ) {

					$match_key++;

				} // @codingStandardsIgnoreLine

			}

			$previous_day = $test_data;

			$iteration++;

		}

		// Reset the keys.
		$chart_data = array_values( $chart_data );

		// Map the load times to the correct positions.
		$chart_data = array_map( function( $key, $value ) use ( $new_data ) {

			if ( ! isset( $new_data[ $key ] ) ) {

				return $value;

			}

			$new_data[ $key ]['first_view']  = (float) round( ( array_sum( $new_data[ $key ]['first_view'] ) / count( $new_data[ $key ]['first_view'] ) ), 2 );
			$new_data[ $key ]['repeat_view'] = (float) round( ( array_sum( $new_data[ $key ]['repeat_view'] ) / count( $new_data[ $key ]['repeat_view'] ) ), 2 );

			return array_merge( $value, $new_data[ $key ] );

		}, array_keys( $chart_data ), $chart_data );

		return $chart_data;

	}

	/**
	 * Check if two strtotime() values days match. (d)
	 *
	 * @param  integer $date1 strtotime() timestamp for the first date.
	 * @param  integer $date2 strtotime() timestamp for the second date.
	 *
	 * @return boolean        True if the days match, else false.
	 */
	public function days_match( $date1, $date2, $iter ) {

		// On the first iteration, $date1 is false.
		if ( ! $date1 ) {

			return false;

		}

		$day1 = date_i18n( 'm/d/Y', $date1 );
		$day2 = date_i18n( 'm/d/Y', $date2 );

		return ( $day1 === $day2 );

	}

	/**
	 * Sort completed tests by the start date.
	 *
	 * @param  integer $a Test 1 start date.
	 * @param  integer $b Test 2 start date.
	 *
	 * @return new order based on test date.
	 */
	public function order_chart_data( $a, $b ) {

		$result = strcmp( $a['date'], $b['date'] );

		return $result;

	}

}
