<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Cron {

	public function __construct() {

		add_filter( 'cron_schedules', [ $this, 'custom_cron_schedules' ] );

		/**
		 * Standard cron.
		 *
		 * @since 1.0.0
		 */
		add_action( 'speed_test_run', [ $this, 'run_test' ] );

		/**
		 * Delayed, single time, cron job.
		 *
		 * @since 1.0.0
		 */
		add_action( 'delayed_speed_test_run', [ $this, 'run_test' ] );

		/**
		 * Default speed test cron arguments.
		 *
		 * Note: The arguments passed here will be used to specify arguments in
		 *       the WebPageTest.org API request.
		 *
		 * @var array
		 */
		$cron_args = (array) apply_filters( 'site_speed_monitor_cron_args', [
			'notify' => get_option( 'admin_email' ),
		] );

		// Cron tests are disabled.
		if ( ! Plugin::$options['cron_tests'] ) {

			wp_clear_scheduled_hook( 'speed_test_run', $cron_args );

			return;

		}

		if ( wp_next_scheduled( 'speed_test_run', $cron_args ) ) {

			return;

		}

		wp_schedule_event( time(), Plugin::$options['cron_frequency'], 'speed_test_run', $cron_args );

	}

	/**
	 * Run the speed test.
	 *
	 * @param  array $args Cron arguments.
	 *
	 * @since 1.0.0
	 */
	public function run_test( $args = [] ) {

		$api = new WPT_API();

		Helpers::set_temp_test_parameters( $args );

		$api::start_test();

	}

	/**
	 * Define custom cron schedules.
	 *
	 * @param  array $schedules Array of possible cron schedules.
	 *
	 * @since  1.0.0
	 *
	 * @return array            Final array of cron schedules.
	 */
	public function custom_cron_schedules( $schedules ) {

		$schedules['weekly'] = [
			'interval' => 604800,
			'display'  => __( 'Weekly', 'site-speed-monitor' ),
		];

		$schedules['twiceweekly'] = [
			'interval' => 302400,
			'display'  => __( 'Twice Per Week', 'site-speed-monitor' ),
		];

		$schedules['thirtydays'] = [
			'interval' => 2592000,
			'display'  => __( 'Every 30 Days', 'site-speed-monitor' ),
		];

		$schedules['fifteendays'] = [
			'interval' => 1296000,
			'display'  => __( 'Every 15 Days', 'site-speed-monitor' ),
		];

		return $schedules;

	}

}
