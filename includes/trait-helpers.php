<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

trait Helpers {

	/**
	 * Retreive a specific Site Speed Monitor option.
	 *
	 * @param  string $option Name of option to return
	 *
	 * @return string         Value of the option, or empty if not found.
	 *
	 * @since  1.0.0
	 */
	static function option( $option, $default = '' ) {

		return isset( Plugin::$options[ $option ] ) ? Plugin::$options[ $option ] : ( ! empty( $default ) ? $default : '' );

	}

	/**
	 * Update plugin option.
	 *
	 * @param string $name  Option to update.
	 * @param array  $value New value.
	 *
	 * @since 1.0.0
	 *
	 * @todo
	 */
	static function update_option( $name = '', $value = '' ) {

		if ( empty( $name ) || ! isset( Plugin::$options[ $name ] ) ) {

			return false;

		}

		switch ( $name ) {

			case is_array( Plugin::$options[ $name ] ):

				switch ( $name ) {

					/**
					 * @todo
					 */
					case 'completed_tests':

						break;

					case 'pending_tests':

						Plugin::$options[ $name ][] = $value;

						break;

					default:

						Plugin::$options[ $name ] = $value;

						break;

				}

				break;

			default:

				Plugin::$options[ $name ] = $value;

				break;

		}

		return update_option( 'site_speed_monitor_options', Plugin::$options );

	}

	/**
	 * Detect a localhost install.
	 *
	 * @return boolean True if localhost, else false.
	 *         Note: This can be short circuited by specifying a 'url' parameter
	 *               in the Site Speed Monitor test options array.
	 *               eg: key:   url
	 *                   value: https://www.codeparrots.com
	 *
	 * @since 1.0.0
	 */
	static function is_local( $check_url_param = true ) {

		if (
			self::is_developer_mode() ||
			( $check_url_param && array_key_exists( 'url', self::get_test_parameters() ) )
		) {

			return false;

		}

		return in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] );

	}

	/**
	 * Check if there are any speed tests pending.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if test are pending completion, else false.
	 */
	static function is_speed_test_pending() {

		return ! empty( self::option( 'pending_tests', [] ) );

	}

	/**
	 * Get the last run test first view load time.
	 *
	 * @since 1.0.0
	 *
	 * @return string Load time.
	 */
	public static function get_last_test_load_speed() {

		$completed_tests = self::option( 'completed_tests', [] );

		if ( empty( $completed_tests ) ) {

			return;

		}

		$completed_tests = self::sort_tests( $completed_tests );

		$load_time = isset( $completed_tests[0]['data']['testData']['data'] ) ? $completed_tests[0]['data']['testData']['data'] : false;

		if ( ! $load_time ) {

			return;

		}

		return [
			'first-view'  => self::get_load_time( $load_time, 'first-view' ),
			'repeat-view' => self::get_load_time( $load_time, 'repeat-view' ),
		];

	}

	/**
	 * Get the last run test start date.
	 *
	 * @since 1.0.0
	 *
	 * @return string formatted Start date.
	 */
	public static function get_last_test_start_date() {

		$completed_tests = self::option( 'completed_tests', [] );

		if ( empty( $completed_tests ) ) {

			return;

		}

		$completed_tests = self::sort_tests( $completed_tests );

		$start_date = isset( $completed_tests[0]['start_date'] ) ? $completed_tests[0]['start_date'] : false;

		if ( ! $start_date ) {

			return;

		}

		return date_i18n( get_option( 'date_format' ), $start_date );

	}

	/**
	 * Sort the 'completed_tests' option, chronologically by 'start_date' (or another key)
	 *
	 * @param array   $data     The data to sort.
	 * @param boolean $reverse  Whether the array be reversed or not.
	 * @param string  $sort_key The key used for comparison.
	 *
	 * @since 1.0.0
	 *
	 * @return                  Return the sorted array.
	 */
	static function sort_tests( $data, $reverse = false, $sort_key = 'start_date' ) {

		usort( $data, function( $a, $b ) use ( $reverse, $sort_key ) {

			$results = strcmp( $a[ $sort_key ], $b[ $sort_key ] );

			return ! $reverse ? -$results : $results;

		} );

		return $data;

	}

	/**
	 * Display a warning notice to the admin that their site is loading slowly.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the admin notice.
	 */
	public static function is_load_speed_excessive() {

		$load_time = self::get_last_test_load_speed();

		if ( empty( $load_time ) ) {

			return false;

		}

		return ( $load_time['first-view'] > Actions::$warning_max_speed );

	}

	/**
	 * Check if Site Speed Monitor is running in developer mode.
	 *
	 * @return boolean True when developer mode is active, else false.
	 */
	static function is_developer_mode() {

		/**
		 * Enable developer mode for speped check.
		 *
		 * Enabling developer mode inside of Site Speed Monitor will all of the options
		 * disabled on localhost installs, including site_details inside of tests,
		 * plugins and theme activation runs, enable diff checks on the chart and
		 * more.
		 *
		 * @var boolean
		 */
		$developer_mode = (bool) apply_filters( 'site_speed_monitor_developer_mode', false );

		return $developer_mode;

	}

	/**
	 * Check if debug is enabled.
	 *
	 * @return boolean True if debug is turned on, else false.
	 *
	 * @since 1.0.0
	 */
	static function logging() {

		return self::option( 'logging', false );

	}

	/**
	 * Check if a speed test is scheduled to run.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if a speed test is scheduled, else false.
	 */
	static function is_scheduled_speed_test() {

		$activation_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'plugin' );
		$activation_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'theme' );

		return ( ! wp_next_scheduled( 'delayed_speed_test_run', $activation_args ) ) ? false : true;

	}

	/**
	 * Get the next speed test run time.
	 *
	 * @since 1.0.0
	 *
	 * @return array Data for the speed test schedule.
	 */
	static function get_next_scheduled_speed_test() {

		$activation_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'plugin' );
		$activation_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'theme' );

		$schedule = wp_next_scheduled( 'delayed_speed_test_run', $activation_args );

		$schedule_time = get_date_from_gmt( date( get_option( 'time_format' ), $schedule ), get_option( 'time_format' ) );

		$runs_today = ( date_i18n( 'm/d/Y' ) === date_i18n( 'm/d/Y', $schedule ) );

		$date = $runs_today ? sprintf(
			/* translators: Human readable time the event is scheduled for. */
			__( 'at %s', 'site-speed-monitor' ),
			esc_html( $schedule_time )
		) : sprintf(
			'on %1$s at %2$s',
			esc_html( date_i18n( 'm/d/Y', $schedule ) ),
			esc_html( $schedule_time )
		);

		return ! $schedule ? '<code>' . __( 'Error', 'site-speed-monitor' ) . '</code>' : $date;

	}

	/**
	 * Temporarily sets the test parameters before a test is run.
	 *
	 * @param  array $parameters Array of test parameters.
	 *
	 * @since  1.0.0
	 */
	static function set_temp_test_parameters( $parameters = [] ) {

		if ( empty( $parameters ) ) {

			return;

		}

		foreach ( $parameters as $key => $value ) {

			WPT_API::$test_parameters[ $key ] = $value;

		}

	}

	/**
	 * Display a custom notice.
	 *
	 * @param  string  $type    The notice type. sucess|warning|error
	 * @param  string  $message The text to display in the notice.
	 * @param  boolean $echo    Whether the notice should be echoed.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed            Markup for the notice.
	 */
	static function site_speed_monitor_notice( $type = 'success', $message = '', $echo = true ) {

		$func = $echo ? 'printf' : 'sprintf';

		return call_user_func_array( $func, [
			'<div class="site-speed-monitor-notice notice-%1$s"><p>%2$s</p></div>',
			esc_attr( $type ),
			wp_kses_post( $message ),
		] );

	}

	/**
	 * Display a small notice to the users about the widget behavior.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the dashboard widget.
	 */
	static function widget_notice( $type = 'info' ) {

		$notices = [];

		if ( self::is_local() ) {

			$notices[] = [
				'type' => 'warning',
				'text' => sprintf(
					/* translators: 1. 'url' string wrapped in <code> tags. 2. Link to the Site Speed Monitor settings page. */
					__( 'It looks like this is a localhost install. Site Speed Monitor tests can only be run on live sites. If you want to test external sites (not this one) you can specify a %1$s parameter on the %2$s settings tab.', 'site-speed-monitor' ),
					'<code>' . __( 'url' ) . '</code>', // Core i10n
					sprintf(
						'<a href="%1$s">' . __( 'Site Speed Monitor - Test Parameters' ) . '</a>',
						admin_url( 'options-general.php?page=site-speed-monitor&tab=test-parameters' )
					)
				),
			];

		}

		/**
		 * Display a logging notice when enabled.
		 */
		if ( self::logging() ) {

			$notices[] = [
				'type' => 'info',
				'text' => sprintf(
					/* translators: 1. 'settings page' wrapped in an anchor tag. */
					esc_html__( 'Logging is enabled. %s', 'site-speed-monitor' ),
					sprintf(
						'<small><a href="%1$s">%2$s</a></small>',
						admin_url( 'options-general.php?page=site-speed-monitor&tab=logging' ),
						esc_html__( 'View Log', 'site-speed-monitor' )
					)
				),
			];

		}

		$params = self::get_test_parameters();

		if ( array_key_exists( 'url', $params ) ) {

			$notices[] = [
				'type' => 'info',
				'text' => sprintf(
					/* translators: 1. 'url' string wrapped in <code> tags. 2. Website URL wrapped in <code> tags. 3. Link to the Site Speed Monitor settings page. */
					__( 'You have a %1$s parameter set. Running site speed tests will scan the site at %2$s. To scan this site, remove the %1$s parameter on the Site Speed Monitor %3$s.', 'site-speed-monitor' ),
					'<code>' . __( 'url' ) . '</code>', // Core i10n
					'<code>' . esc_url( $params['url'] ) . '</code>',
					sprintf(
						'<a href="%1$s">%2$s</a>',
						admin_url( 'options-general.php?page=site-speed-monitor&tab=test-parameters' ),
						esc_html__( 'settings page', 'site-speed-monitor' )
					)
				),
			];

		}

		if ( empty( $notices ) ) {

			return;

		}

		ob_start();

		?>

		<div class="widget-notices">

		<?php

		foreach ( $notices as $notice ) {

			echo self::site_speed_monitor_notice( $notice['type'], $notice['text'], false );

		}

		?>

		</div>

		<?php

	}

	/**
	 * Convert XML markup to JSON
	 *
	 * @return array JSON encoded array
	 *
	 * @since 1.0.0
	 */
	static function xml_to_php_array( $data ) {

		// Strip whitespace between xml tags
		$xml = preg_replace( '~\s*(<([^-->]*)>[^<]*<!--\2-->|<[^>]*>)\s*~', '$1', $data );

		// Convert CDATA into xml nodes.
		$xml = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );

		return json_decode( json_encode( $xml ), true );

	}

	/**
	 * Convert milliseconds to seconds.
	 *
	 * @param  integer $ms Milliseconds to convert.
	 *
	 * @return float       Seconds.
	 */
	static function convert_time( $ms = false, $append = 's' ) {

		if ( ! $ms ) {

			return;

		}

		return (float) round( ( $ms / 1000 ), 2 ) . ( $append ? $append : '' );

	}

	/**
	 * Return a text grade based on the load time given.
	 *
	 * @param integer $time The load time to grade.
	 * @param boolean $wrap Should the return be wrapped in a span element.
	 *
	 * @return string        The text grade. great|normal|poor
	 */
	static function get_load_time_grade( $time = 0, $wrap = false ) {

		switch ( true ) {

			case $time <= 2:

				$grade = __( 'great', 'site-speed-monitor' );

				break;

			case $time <= 4:

				$grade = __( 'average', 'site-speed-monitor' );

				break;

			case $time > 4:

				$grade = __( 'poor', 'site-speed-monitor' );

				break;

		} // End switch().

		return $wrap ? sprintf(
			'<span class="badge %1$s">%2$s</span>',
			esc_attr( $grade ),
			esc_html( ucwords( $grade ) )
		) : $grade;

	}

	 /**
	 * Delete site_speed_monitor_* transients.
	 *
	 * @param integer $date The test ID to delete transients for.
	 *
	 * @since 1.2
	 */
	function delete_site_speed_monitor_transients() {

		$transient_name = 'site_speed_monitor_test_';

		global $wpdb;

		$transients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * from `{$wpdb->prefix}options` WHERE option_name LIKE %s;", '%' . $wpdb->esc_like( $transient_name ) . '%'
			)
		);

		if ( ! $transients && ! empty( $transients ) ) {

			return;

		}

		foreach ( $transients as $transient ) {

			delete_transient( str_replace( '_transient_', '', $transient->option_name ) );

		}

	}

	/**
	 * Calculate the overall test score
	 *
	 * @param integer $ttfb          Time to first byte score.
	 * @param integer $average_score The average score among other grades.
	 * @param boolean $return_early  If the converted TTFB be returned.
	 *
	 * @return string                The final grade (A-F).
	 */
	static function calculate_grade( $ttfb, $average_score = 0, $return_early = false ) {

		if ( empty( $ttfb ) ) {

			return;

		}

		switch ( true ) {

			case $ttfb <= 1500:

				$ttfb = 100;

				break;

			case $ttfb <= 2500:

				$ttfb = 80;

				break;

			case $ttfb <= 3000:

				$ttfb = 70;

				break;

			case $ttfb <= 3500:

				$ttfb = 60;

				break;

			case $ttfb > 3500:

				$ttfb = 0;

				break;

		} // End switch().

		if ( $return_early ) {

			return $ttfb;

		}

		$grade = ( $average_score + $ttfb ) / 2;

		switch ( true ) {

			case $grade >= 90:

				return 'A';

				break;

			case $grade >= 80:

				return 'B';

				break;

			case $grade >= 70:

				return 'C';

				break;

			case $grade >= 60:

				return 'D';

				break;

			case $grade < 60:

				return 'F';

				break;

		} // End switch().

	}

	/**
	 * Convert the Average test scores (integer) into letter grades.
	 *
	 * @param  integer $score The score to test.
	 * @param  string  $key   The key of the score we are testing. (eg: TTFB)
	 *
	 * @return string         The final letter grade for the given score.
	 */
	static function get_letter_grade( $score = -1, $key = '' ) {

		/**
		 * Note: For TTFB we are returned a ms value, here we convert it to a integer grade
		 *
		 * @var integer
		 */
		if ( ! empty( $key ) && 'TTFB' === $key ) {

			$score = self::calculate_grade( $score, '', true );

		}

		switch ( true ) {

			case 90 <= $score:

				return 'A';

			case 80 <= $score:

				return 'B';

			case 70 <= $score:

				return 'C';

			case 60 <= $score:

				return 'D';

			case -1 === $score:

				return 'N/A';

			case 60 > $score:
			case 0 === $score:

				return 'F';

		}
	}

	/**
	 * Build an array of test parameters from our test_parameters options
	 *
	 * @since  1.0.0
	 *
	 * @return array Returns a key => value pair of test parameters
	 */
	static function get_test_data( $test_id = false ) {

		$completed_tests = self::option( 'completed_tests', [] );

		if ( empty( $completed_tests ) || ! $test_id ) {

			return false;

		}

		foreach ( $completed_tests as $key => $test ) {

			if ( $test_id !== $test['data']['testId'] ) {

				continue;

			}

			return $completed_tests[ $key ]['data']['testData']['data'];

		}

		return false;

	}

	/**
	 * Return the load time.
	 *
	 * @param array  $test_data The test data to return the value from.
	 * @param string $type      The load type to retrieve. Possible: first-view, repeat-view.
	 *
	 * @since 1.0.0
	 *
	 * @return float            The test speed load time.
	 */
	static function get_load_time( $test_data, $type = 'first-view' ) {

		$type = ( 'first-view' === $type ) ? 'firstView' : 'repeatView';

		if ( ! isset( $test_data['average'][ $type ]['loadTime'] ) ) {

			return;

		}

		return self::convert_time( $test_data['average'][ $type ]['loadTime'], false );

	}

	/**
	 * Return the site details (eg. Active theme & active plugins)
	 *
	 * @since  1.0.0
	 *
	 * @return array Array of test site details.
	 */
	static function get_test_site_details( $test_id = false ) {

		$completed_tests = self::option( 'completed_tests', [] );

		if ( empty( $completed_tests ) || ! $test_id ) {

			return false;

		}

		foreach ( $completed_tests as $key => $test ) {

			if ( $test_id !== $test['data']['testId'] ) {

				continue;

			}

			return isset( $completed_tests[ $key ]['site_details'] ) ? $completed_tests[ $key ]['site_details'] : false;

		}

		return false;

	}

	/**
	 * Build an array of test parameters from our test_parameters options
	 *
	 * @since  1.0.0
	 *
	 * @return array Returns a key => value pair of test parameters
	 */
	static function get_test_parameters() {

		$test_parameters = self::option( 'test_parameters', [] );

		if ( empty( $test_parameters ) ) {

			return [];

		}

		return array_combine( $test_parameters['keys'], $test_parameters['values'] );

	}

	/**
	 * Render the confirmation modal markup
	 *
	 * @param  string $id The ID of the modal, used in our javascript.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed      Markup for the confirmation modal.
	 */
	static function confirmation_modal( $id = '', $type = 'delete', $content = '', $action = '' ) {

		printf(
			'<div id="%1$s" class="cp-confirmation-hidden %2$s">
				<p class="icon">%3$s</p>
				<p>%4$s</p>
				<div class="actions">%5$s %6$s</div>
			</div>',
			esc_attr( $id ),
			esc_attr( $type ),
			'delete' === $type ? wp_kses_post( '<span class="dashicons dashicons-warning"></span>' ) : '',
			esc_html( $content ),
			sprintf(
				'<a href="%1$s" class="button button-primary"></a>',
				wp_nonce_url( add_query_arg( 'action', esc_attr( $id ), admin_url() ), $action )
			),
			sprintf(
				'<a href="#" class="button button-secondary close-modal">%1$s</a>',
				esc_html__( 'Cancel', 'site-speed-monitor' )
			)
		);

	}

	/**
	 * Get the activation offset time in a human readable format.
	 *
	 * @since 1.0.0
	 *
	 * @return The offset time.
	 */
	static function get_activation_offset_time() {

		$delay = (int) apply_filters( 'site_speed_monitor_plugin_theme_activation_delay', 120 );

		$seconds_in_minute = 60;
		$seconds_in_hour   = 60 * $seconds_in_minute;
		$seconds_in_day    = 24 * $seconds_in_hour;

		// Extract days
		$days = floor( $delay / $seconds_in_day );

		// Extract hours
		$hour_seconds = $delay % $seconds_in_day;
		$hours        = floor( $hour_seconds / $seconds_in_hour );

		// Extract minutes
		$minute_seconds = $hour_seconds % $seconds_in_hour;
		$minutes        = floor( $minute_seconds / $seconds_in_minute );

		// Extract the remaining seconds
		$remaining_seconds = $minute_seconds % $seconds_in_minute;
		$seconds           = ceil( $remaining_seconds );

		// Format and return
		$time_parts = [];
		$sections   = [
			'day'    => (int) $days,
			'hour'   => (int) $hours,
			'minute' => (int) $minutes,
			'second' => (int) $seconds,
		];

		foreach ( $sections as $name => $value ) {

			if ( $value <= 0 ) {

				continue;

			}

			$time_parts[] = $value . ' ' . $name . ( 1 === $value ? '' : 's' );

		}

		return implode( ', ', $time_parts );

	}

	/**
	 * Get current themes & plugins to append to test data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Active theme and active plugins.
	 */
	static function additional_test_data() {

		if ( ! self::is_developer_mode() && self::is_local( false ) ) {

			return false;

		}

		if ( ! function_exists( 'get_plugin_data' ) ) {

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		}

		$theme   = wp_get_theme();
		$plugins = get_option( 'active_plugins' );

		if ( $plugins && ! empty( $plugins ) ) {

			$plugins = array_map( function( $plugin_file ) {

				$plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file );

				$name         = ! $plugin ? $plugin_file : $plugin['Name'];
				$version      = ! $plugin ? __( 'undefined', 'site-speed-monitor' ) : $plugin['Version'];
				$description  = ! $plugin ? false : $plugin['Description'];
				$author       = ! $plugin ? false : $plugin['Author'];

				return [
					'name'        => $name,
					'version'     => $version,
					'description' => $description,
					'author'      => $author,
				];

			}, $plugins );

		}

		/**
		 * Filter the site details array
		 *
		 * This allows users to display additional data on the site details view.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		return (array) apply_filters( 'site_speed_monitor_site_details', [
			'theme'   => [
				'name'        => $theme->get( 'Name' ),
				'description' => $theme->get( 'Description' ),
				'url'         => $theme->get( 'ThemeURI' ),
				'version'     => $theme->get( 'Version' ),
				'author'      => $theme->get( 'Author' ),
				'author_url'  => $theme->get( 'AuthorURI' ),
			],
			'plugins' => $plugins,
			'site'    => [
				__( 'WordPress Version', 'site-speed-monitor' ) => get_bloginfo( 'version' ),
				__( 'PHP Version', 'site-speed-monitor' )       => phpversion(),
				__( 'SSL', 'site-speed-monitor' )               => is_ssl() ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no-alt"></span>',
				__( 'GZip Enabled', 'site-speed-monitor' )      => extension_loaded( 'zlib' ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no-alt"></span>',
			],
		], $theme, $plugins );

	}

	/**
	 * Generate a table for our data.
	 *
	 * @param array $titles The titles for the table. (Single value, title)
	 * @param array $data   The data array to use. (label => value array)
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the table.
	 */
	static function generate_table( $titles = [], $data = [], $class = '' ) {

		?>

		<table class="wp-list-table widefat fixed striped <?php echo $class; ?>">

		<?php

		if ( ! empty( $titles ) ) {

			?>

			<thead>
				<tr>

				<?php

				foreach ( $titles as $title ) {

					printf(
						'<th class="%1$s">%2$s</th>',
						esc_attr( sanitize_title( $title ) ),
						esc_html( $title )
					);

				}

				?>

				</tr>
			</thead>

			<?php

			foreach ( $data as $index => $data_array ) {

				print( '<tr>' );

				if ( is_array( $data_array ) ) {

					foreach ( $data_array as $key => $value ) {

						if ( 'author' === $key ) {

							continue;

						}

						if ( 'name' === $key ) {

							$value = '<strong>' . esc_html( $value ) . '</strong>' . '<p class="description">' . sprintf(
								/* translators: Author name wrapped in anchor tag. */
								__( 'By: %s', 'site-speed-monitor' ),
								wp_kses_post( $data_array['author'] )
							) . '</p>';

						}

						printf(
							'<td class="%1$s">%2$s</td>',
							esc_attr( $key ),
							( 'name' === $key ) ? $value : wp_kses_post( $value )
						);

					} // @codingStandardsIgnoreLine.

				} else {

					printf(
						'<td>%1$s</td><td>%2$s</td>',
						'<strong>' . esc_html( $index ) . '</strong>',
						wp_kses_post( $data_array )
					);

				}

				print( '</tr>' );

			} // @codingStandardsIgnoreLine.

		}

		?>

		</table>

		<?php

	}

	/**
	 * Get the added plugins/themes/site details between two arrays.
	 *
	 * @param array $a1 First array to compare.
	 * @param array $a2 Second array to compare.
	 *
	 * @since 1.0.0
	 *
	 * @return array    Items added to $a2.
	 */
	static function get_added_details( $a1, $a2 ) {

		$r = [];

		if ( empty( $a1 ) ) {

			return false;

		}

		if ( empty( $a2 ) ) {

			return $a1;

		}

		foreach ( $a1 as $type => $data ) {

			foreach ( $data as $key => $value ) {

				if ( is_array( $value ) ) {

					$found = in_array( $value['name'], array_column( $a2[ $type ], 'name' ) );

					if ( $found ) {

						continue;

					}
					$r[ $type ][] = $value;

					continue;

				}

				if ( isset( $a2[ $type ][ $key ] ) && $a2[ $type ][ $key ] === $value ) {

					continue;

				}

				$r[ $type ][ $key ] = $value;

			} // @codingStandardsIgnoreLine

		}

		return $r;

	}

	/**
	 * Get the removed plugins/themes/site details between two arrays.
	 *
	 * @param array $a1 First array to compare.
	 * @param array $a2 Second array to compare.
	 *
	 * @since 1.0.0
	 *
	 * @return array    Items added to $a2.
	 */
	static function get_removed_details( $a1, $a2 ) {

		$r = [];

		if ( empty( $a1 ) ) {

			return $a2;

		}

		if ( empty( $a2 ) ) {

			return [];

		}

		foreach ( $a2 as $type => $data ) {

			foreach ( $data as $key => $value ) {

				if ( is_array( $value ) ) {

					$found = in_array( $value['name'], array_column( $a1[ $type ], 'name' ) );

					if ( $found ) {

						continue;

					}

					$r[ $type ][] = $value;

					continue;

				}

				if ( isset( $a1[ $type ][ $key ] ) && $a1[ $type ][ $key ] === $value ) {

					continue;

				}

				$r[ $type ][ $key ] = $value;

			} // @codingStandardsIgnoreLine

		}

		return $r;

	}

	/**
	 * Get colors to use on the speed chart.
	 *
	 * @since 1.0.0
	 *
	 * @return array Colors array to be used on the chart.
	 */
	public static function get_chart_colors() {

		$scheme = get_user_option( 'admin_color' );

		switch ( $scheme ) {

			default:
			case 'light':
			case 'evergreen':
			case 'coffee':
			case 'ocean':
			case 'sunrise':

				return self::get_admin_colors( 2 );

				break;

			case 'fresh':

				return [
					'rgba(54, 162, 235, 1)',
					'rgba(255,99,132,1)',
				];

				break;

			case 'bbp-mint':

				return self::get_admin_colors( 2, '', [ 1, 3 ] );

				break;

			case 'blue':

				return self::get_admin_colors( 2, '', [ 0, 3 ] );

				break;

			case 'ectoplasm':

				return self::get_admin_colors( 2, '', [ 0, 3 ] );

				break;

			case 'midnight':

				return self::get_admin_colors( 2, '', [ 1, 3 ] );

				break;

		}

	}

	/**
	 * Retrieve the admin colors to use based on the selected color scheme.
	 *
	 * @param  integer $number Number of colors to return.
	 *                         Note: 1 will will return color #3, 2 will return #2 & #3,
	 *                               3 or more will begin at position 0 in the array.
	 *                               eg: 3 - [ $color[0], $color[1], $color[2] ]
	 * @return array          Array of colors to be used.
	 */
	static function get_admin_colors( $number = 1, $default = 'goldenrod', $indexes = [] ) {

		global $_wp_admin_css_colors;

		$scheme = get_user_option( 'admin_color' );
		$colors = isset( $_wp_admin_css_colors[ $scheme ] ) ? $_wp_admin_css_colors[ $scheme ] : false;

		if ( ! $colors ) {

			return (array) $default;

		}

		// Short circuit the colors, and return specific color indexes.
		if ( ! empty( $indexes ) ) {

			$color = [];

			// Non-array, single index override.
			if ( ! is_array( $indexes ) ) {

				return (array) $colors->colors[ $indexes ];

			}

			foreach ( $indexes as $color_index ) {

				$color[] = $colors->colors[ $color_index ];

			}

			return $color;

		}

		switch ( $number ) {

			case 1:

				$color = ( $colors && ! empty( $colors ) ) ? $colors->colors[2] : $default;

				break;

			case 2:

				$color = [
					$colors->colors[1],
					$colors->colors[2],
				];

				break;

			// 3 or more.
			default:

				$color  = [];
				$x      = 0;

				while ( $x <= $number ) {

					$color[] = $colors->colors[ $x ];

				}

				break;

		}

		return is_array( $color ) ? $color : (array) $color;

	}

}
