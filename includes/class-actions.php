<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Actions {

	/**
	 * Time to delay the single cron event.
	 *
	 * The offset time between plugin/theme activation and the speed test run.
	 *
	 * @var integer
	 */
	private $delay;

	/**
	 * Maximum load time (in seconds) before the admin is notified of slow site speed.
	 *
	 * The offset time between plugin/theme activation and the speed test run.
	 *
	 * @var integer
	 */
	public static $warning_max_speed;

	public function __construct() {

		/**
		 * Activation delay offset, in seconds.
		 *
		 * Tweak the length of time after a plugin/theme is activated that a site
		 * speed test will begin.
		 *
		 * @var integer
		 */
		$this->delay = (int) apply_filters( 'site_speed_monitor_plugin_theme_activation_delay', 120 );

		/**
		 * Maximum time that a speed test can return before an admin notice is displayed.
		 *
		 * @var integer
		 */
		self::$warning_max_speed = (int) apply_filters( 'site_speed_monitor_warning_max_speed', 5 );

		add_filter( 'site_speed_monitor_test_parameters', [ $this, 'filter_test_parameters' ], PHP_INT_MAX );

		add_filter( 'removable_query_args', [ $this, 'remove_query_args' ], PHP_INT_MAX );

		add_action( 'site_speed_monitor_widget_top', [ $this, 'display_notices' ], 10 );
		add_action( 'current_screen', [ $this, 'force_complete_test' ], 10 );

		add_action( 'site_speed_monitor_tools_history_bottom', [ $this, 'confirmation_modal' ] );
		add_action( 'site_speed_monitor_tools_history_bottom', [ $this, 'site_details_modal' ] );

		// Plugin & Theme activation callbacks
		add_action( 'activated_plugin', [ $this, 'plugin_activated' ], 10, 2 );
		add_action( 'switch_theme',     [ $this, 'theme_switched' ] );

		add_action( 'site_speed_monitor_site_details_start', [ $this, 'test_details' ], 10, 2 );

		add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ] );
		add_filter( 'update_footer',     [ $this, 'update_footer_text' ], 15 );

	}

	/**
	 * Filter the Site Speed Monitor test parameters.
	 *
	 * @param   array $parameters Array of default test parameters.
	 *
	 * @since  1.0.0
	 *
	 * @return array             Filtered array of test parameters.
	 */
	public function filter_test_parameters( $parameters ) {

		if ( Helpers::option( 'email_results', false ) ) {

			$user = wp_get_current_user();

			$parameters['notify'] = $user->user_email;

		}

		if ( Helpers::option( 'private_test', true ) ) {

			$parameters['private'] = true;

		}

		// Setup the testing location.
		if ( ! empty( Helpers::option( 'test_location', false ) ) ) {

			$location = Helpers::option( 'test_location', false );
			$browser  = Helpers::option( 'test_browser', 'Chrome' );
			$speed    = Helpers::option( 'test_speed', 'Cable' );

			$parameters['location'] = "$location:$browser.$speed";

		}

		return $parameters;

	}

	/**
	 * Display admin notice.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed HTML markup for the admin notice.
	 */
	public function remove_query_args( $args ) {

		if ( WP_DEBUG ) {

			return $args;

		}

		$args[] = 'site-speed-monitor-started';
		$args[] = 'site-speed-monitor-force-complete';
		$args[] = 'response';
		$args[] = 'site-speed-monitor-cancel-test';
		$args[] = 'site-speed-monitor-test-cancelled';
		$args[] = 'site-speed-monitor-cancel-scheduled-test';
		$args[] = 'site-speed-monitor-test-deleted';
		$args[] = 'site-speed-monitor-clear-test-log';

		return $args;

	}

	/**
	 * Display notices inside of the dashboard widget, when needed.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the Site Speed Monitor notice.
	 */
	public function display_notices() {

		/**
		 * Display the site speed load warning.
		 */
		if ( Helpers::is_load_speed_excessive() ) {

			$last_load_times = Helpers::get_last_test_load_speed();

			Helpers::site_speed_monitor_notice(
				'error',
				sprintf(
					'%1$s',
					sprintf(
						/* translators: Integer. The maximum speed allowed before a warning is displayed. */
						esc_html__( 'Your site appears to be loading slower than average. You may want to investigate what\'s causing your site to take longer than %1$s seconds to load. Your last test took %2$s seconds to load.', 'site-speed-monitor' ),
						esc_html( self::$warning_max_speed ),
						'<code>' . esc_html( $last_load_times['first-view'] ) . '</code>'
					)
				)
			);

		}

		/**
		 * Display site speed test started notice.
		 */
		$speed_test_started = filter_input( INPUT_GET, 'site-speed-monitor-started', FILTER_SANITIZE_NUMBER_INT );

		if ( $speed_test_started ) {

			Helpers::site_speed_monitor_notice(
				'success',
				sprintf(
					/* translators: 1. Maximum time before a test times out (minutes, integer) */
					__( 'Speed test started. Your test should be complete within the next %1$s.', 'site-speed-monitor' ),
					sprintf(
						/* translators: Maximum time before a test times out (minutes, integer). */
						_n( '%s minute', '%s minutes', Tests_Table::$test_timeout, 'site-speed-monitor' ),
						(int) apply_filters( 'site_speed_monitor_pingback_timeout', Helpers::is_local() ? 2 : 5 )
					)
				)
			);

		}

		/**
		 * Display speed test shceuled notice.
		 */
		if ( Helpers::is_scheduled_speed_test() ) {

			Helpers::site_speed_monitor_notice(
				'success',
				sprintf(
					/* translators: 1. The time that the next speed test will run. */
					esc_html__( 'A speed test is scheduled to run %1$s. %2$s', 'site-speed-monitor' ),
					Helpers::get_next_scheduled_speed_test(),
					sprintf(
						'<br /><small><a href="%1$s" class="cancel-scheduled-test">%2$s</a></small>',
						wp_nonce_url( add_query_arg( [
							'cancel-scheduled' => true,
						], site_url() ), 'site-speed-monitor-cancel-scheduled-test' ),
						esc_html__( 'Cancel Scheduled Test', 'site-speed-monitor' )
					)
				)
			);

		}

		/**
		 * Display developer mode notice.
		 */
		if ( Helpers::is_developer_mode() ) {

			Helpers::site_speed_monitor_notice(
				'info',
				sprintf(
					'%1$s %2$s',
					'<span class="dashicons dashicons-warning"></span>',
					esc_html__( 'Site Speed Monitor is currently running in developer mode.', 'site-speed-monitor' )
				)
			);

		}

	}

	/**
	 * Force complete the latest widget test if it is pending for too long.
	 *
	 * @since 1.0.0
	 */
	public function force_complete_test() {

		$screen = get_current_screen();

		if ( empty( $screen->base ) || 'dashboard' !== $screen->base ) {

			return;

		}

		$pending_tests = Helpers::option( 'pending_tests', [] );

		if ( empty( $pending_tests ) ) {

			return;

		}

		/**
		 * Minutes the test should wait to hear a pingback before allowing
		 * the user to manually fetch the data from WebPageTest.org
		 *
		 * @Default 2 minutes on local host, 5 minutes on a live site
		 *
		 * @var integer
		 */
		$test_timeout = (int) apply_filters( 'site_speed_monitor_pingback_timeout', Helpers::is_local() ? 2 : 5 );

		$test_ids = [];

		foreach ( $pending_tests as $pending_test ) {

			$time_diff = date_diff( date_create( date( 'm/d/Y H:i:s', $pending_test['start_date'] ) ), date_create( current_time( 'm/d/Y H:i:s' ) ) );

			$force_complete = ( isset( $time_diff->i ) && $test_timeout <= (int) $time_diff->i ) ? true : false;

			if ( ! $force_complete ) {

				continue;

			}

			$test_ids[] = $pending_test['data']['testId'];

		}

		if ( empty( $test_ids ) ) {

			return;

		}

		foreach ( $test_ids as $force_complete_test ) {

			$force_complete_url = add_query_arg( [
				'complete' => true,
				'id'       => $pending_test['data']['testId'],
				'auto'     => true,
			], site_url() );

			wp_remote_post( $force_complete_url );

		}

		wp_safe_redirect( admin_url() );

		exit;

	}

	/**
	 * Render the confirmation modal markup
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the confirmation modal.
	 */
	public function confirmation_modal() {

		Helpers::confirmation_modal(
			'clear-test-log',
			'delete',
			__( 'Are you sure you want to clear the test log? This cannot be undone.', 'site-speed-monitor' ),
			'site_speed_monitor_clear_test_log'
		);

	}

	/**
	 * Render the site details modal markup.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the site details modal.
	 */
	public function site_details_modal() {

		Helpers::confirmation_modal(
			'test-site-details',
			'custom',
			null, // Content populated via ajax request.
			'testing'
		);

	}

	/**
	 * Start our speed test when a plugin is activated.
	 *
	 * @todo Schedule a cron to run a few minutes after plugin activation.
	 *
	 * @since 1.0.0
	 */
	public function plugin_activated( $plugin, $network_activation ) {

		$plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin, false );
		$plugin = isset( $plugin['Name'] ) ? $plugin['Name'] : $plugin;

		Log::entry( Log::log_type( 'plugin_activated', $plugin ) );

		// Plugin activation tests should not run on localhost installs, unless in developer mode.
		if (
			! Helpers::is_developer_mode() &&
			( Helpers::is_local( false ) || ! Plugin::$options['activation_checks'] )
		) {

			return;

		}

		/**
		 * Clear the scheduled cron.
		 *
		 * This is done incase a user activates multiple plugins in a row.
		 */
		wp_clear_scheduled_hook( 'delayed_speed_test_run' );

		/**
		 * Plugin activation cron job arguments.
		 *
		 * Note: The arguments passed here will be used to specify arguments in
		 *       the WebPageTest.org API request.
		 *
		 * @var array
		 */
		$cron_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'plugin' );

		wp_schedule_single_event( strtotime( "+{$this->delay} seconds" ), 'delayed_speed_test_run', $cron_args );

	}

	/**
	 * Start the speed test when a theme is switched.
	 *
	 * @todo Schedule a cron to run a few minutes after theme switch.
	 *
	 * @since 1.0.0
	 */
	public function theme_switched() {

		$theme = wp_get_theme();

		Log::entry( Log::log_type( 'theme_switched', $theme->get( 'Name' ) ) );

		// Theme activation tests should not run on localhost installs.
		if (
			! Helpers::is_developer_mode() &&
			( Helpers::is_local( false ) || ! Plugin::$options['activation_checks'] )
		) {

			return;

		} // @codingStandardsIgnoreLine

		// Ensures the log entry is added before the test starts.
		sleep( 1 );

		/**
		 * Clear the scheduled cron.
		 *
		 * This is done incase a user activates multiple plugins in a row.
		 */
		wp_clear_scheduled_hook( 'delayed_speed_test_run' );

		/**
		 * Theme activation cron job arguments.
		 *
		 * Note: The arguments passed here will be used to specify arguments in
		 *       the WebPageTest.org API request.
		 *
		 * @var array
		 */
		$cron_args = (array) apply_filters( 'site_speed_monitor_activation_test_args', [], 'theme' );

		wp_schedule_single_event( strtotime( "+{$this->delay} seconds" ), 'delayed_speed_test_run', $cron_args );

	}

	/**
	 * Display sped test details in the site details container.
	 *
	 * @param  string $test_id      The test ID.
	 * @param  array  $site_details Site details array.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed                Markup for the additional details.
	 */
	public function test_details( $test_id, $site_details ) {

		$loads = [
			'first-view',
			'repeat-view',
		];

		$test_data = Helpers::get_test_data( $test_id );

		if ( ! $test_data ) {

			return;

		}

		$load_data = '';

		foreach ( $loads as $view ) {

			$load_time = Helpers::get_load_time( $test_data, $view );

			$load_data .= sprintf(
				'<div class="%1$s">
					<strong>%2$s:</strong>
					%3$s %4$s
				</div>',
				esc_attr( $view ),
				( 'first-view' === $view ) ? esc_html__( 'First View', 'site-speed-monitor' ) : esc_html__( 'Repeat View', 'site-speed-monitor' ),
				sprintf(
					/* translators: Load time. (integer) */
					__( '%s seconds', 'site-speed-monitor' ),
					$load_time
				),
				Helpers::get_load_time_grade( $load_time, true )
			);

		}

		if ( empty( $load_data ) ) {

			return;

		}

		printf(
			'<div class="load-data">
				<h4>%1$s</h4>%2$s
			</div>',
			esc_html__( 'Load Data:', 'site-speed-monitor' ),
			$load_data
		);

	}

	/**
	 * Customize the admin footer text on certain pages.
	 *
	 * @param  string $footer_text Admin footer text.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed               Admin footer text markup.
	 */
	public function admin_footer_text( $footer_text ) {

		$screen = get_current_screen();

		if ( ! isset( $screen->base ) || ! strpos( $screen->base, 'site-speed-monitor' ) ) {

			return $footer_text;

		}

		$scheme = get_user_option( 'admin_color' );

		$colors = ( 'midnight' !== $scheme ) ? Helpers::get_admin_colors() : Helpers::get_admin_colors( 1, 'goldenrod', 3 );

		$styles = [
			'font-size: 14px;',
			'height: 14px;',
			'width: 14px;',
			'vertical-align: middle;',
			'margin-top: -4px;',
			"color: {$colors[0]};",
		];

		return sprintf(
			/* translators: 1. Star dashicons. 2. Link to Site Speed Monitor wordpress.org plugin listing. */
			__( 'If you enjoy Site Speed Monitor, consider leaving us a %1$s review on %2$s.', 'site-speed-monitor' ),
			sprintf(
				wp_kses_post( '<span style="%1$s" class="dashicons dashicons-star-filled"></span><span style="%1$s" class="dashicons dashicons-star-filled"></span><span style="%1$s" class="dashicons dashicons-star-filled"></span><span style="%1$s" class="dashicons dashicons-star-filled"></span><span style="%1$s" class="dashicons dashicons-star-filled"></span>' ),
				esc_attr( implode( ' ', $styles ) )
			),
			sprintf(
				'<a href="wordpress.org/plugins/site-speed-monitor/#reviews" target="_blank">%1$s</a>',
				esc_html( 'WordPress.org' )
			)
		);

	}

	/**
	 * Display Site Speed Monitor version in the footer.
	 *
	 * @param  string $update_footer_text Update footer text.
	 *
	 * @since 1.0.0
	 *
	 * @return string                     Update footer text.
	 */
	public function update_footer_text( $update_footer_text ) {

		$screen = get_current_screen();

		if ( ! isset( $screen->base ) || ! strpos( $screen->base, 'site-speed-monitor' ) ) {

			return $update_footer_text;

		}

		return sprintf(
			/* translators: Site Speed Monitor version. */
			esc_html__( 'Site Speed Monitor Version %s', 'site-speed-monitor' ),
			esc_html( \Site_Speed_Monitor::$version )
		);

	}

}
