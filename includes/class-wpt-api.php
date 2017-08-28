<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class WPT_API {

	use Helpers;

	/**
	 * WebPageTest.org API endpoint base
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public static $endpoints;

	/**
	 * API request parameters
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public static $test_parameters;

	public function __construct() {

		$user = wp_get_current_user();

		self::$endpoints['start_test']     = 'http://www.webpagetest.org/runtest.php';
		self::$endpoints['test_results']   = 'http://www.webpagetest.org/xmlResult/%s/';
		self::$endpoints['test_locations'] = 'http://www.webpagetest.org/getLocations.php?f=json&k=%s';
		self::$endpoints['cancel_test']    = 'http://www.webpagetest.org/cancelTest.php?test=%1$s&k=%2$s';

		/**
		 * Combine the test parameter options with the defaults.
		 *
		 * @var array
		 */
		$test_parameters = wp_parse_args( Helpers::get_test_parameters(), [
			'url'      => site_url(),
			'f'        => 'json',
			'k'        => Helpers::option( 'api_key', '' ),
			'pingback' => add_query_arg( 'complete', 1, site_url( '/site-speed-monitor/' ) ),
		] );

		self::$test_parameters = (array) apply_filters( 'site_speed_monitor_test_parameters', $test_parameters );

		if ( empty( self::$test_parameters['k'] ) ) {

			add_action( 'admin_notices', [ $this, 'admin_notice' ] );

			return;

		}

	}

	/**
	 * Start the speed test.
	 *
	 * @return array JSON encoded array of response data.
	 *
	 * @since 1.0.0
	 */
	public static function start_test() {

		/**
		 * Tests are not run on localhost because they are not accessible
		 * by the WebPageTest.org web crawler.
		 *
		 * You can test external sites by specifying a 'url' => https://www.example.com
		 * key value pair in the test parameters tab on the settings page.
		 */
		if ( Helpers::is_local() ) {

			return;

		}

		/**
		 * @todo Setup debug/logging data
		 */
		Log::entry( Log::log_type( 'started' ) );

		$response = wp_remote_post( self::$endpoints['start_test'], [
			'body' => self::$test_parameters,
		] );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $body['statusCode'] ) {

			if ( Plugin::is_wp_cli() ) {

				return false;

			}

			wp_safe_redirect( add_query_arg( [
				'site-speed-monitor-started' => false,
				'response'                   => urlencode( $body['statusText'] ),
			], admin_url() ) );

			exit;

		}

		unset( $body['statusCode'], $body['statusText'] );

		$user = wp_get_current_user();

		$body['user']         = ( defined( 'WP_CLI' ) && WP_CLI ) ? 'wp-cli' : ( ( 0 !== $user ) ? $user->user_login : 'wp-cron'  );
		$body['status']       = 'pending';
		$body['start_date']   = (int) current_time( 'timestamp' );
		$body['site_details'] = Helpers::additional_test_data();

		/**
		 * Filter the data being stored after a test starts, before it's stored.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		$body = apply_filters( 'site_speed_monitor_test_results', $body );

		Helpers::update_option( 'pending_tests', $body );

		if ( Plugin::is_wp_cli() ) {

			return $body;

		}

		wp_safe_redirect( add_query_arg( 'site-speed-monitor-started', 1, admin_url() . '#site-speed-monitor-top' ) );

		exit;

	}

	/**
	 * Cancel a WebPageTest.org site speed test that has already started.
	 *
	 * @param  string $test_id The test ID to cancel.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean True on success, else false.
	 */
	public static function cancel_test( $test_id ) {

		$response = wp_remote_post( sprintf(
			self::$endpoints['cancel_test'],
			$test_id,
			Helpers::option( 'api_key', '' )
		) );

		$body = wp_remote_retrieve_body( $response );

		if ( false !== strpos( $body, 'Sorry, the test could not be cancelled' ) ) {

			wp_safe_redirect( add_query_arg( [
				'site-speed-monitor-started' => false,
				'response'                   => urlencode( wp_strip_all_tags( $body ) ),
			], admin_url() ) );

			exit;

		}

		wp_safe_redirect( add_query_arg( [
			'site-speed-monitor-cancelled' => true,
		], admin_url() ) );

		exit;

	}

	/**
	 * Retreive specific test data.
	 *
	 * @param  string $test_id The test ID to retreive data for.
	 *
	 * @since  1.0.0
	 *
	 * @return array           PHP array of test data.
	 */
	public static function get_test_data( $test_id ) {

		/**
		 * @todo Setup debug/logging when tests start
		 */
		if ( Plugin::$options['debug'] ) {

		}

		$response = wp_remote_post( sprintf( self::$endpoints['test_results'], $test_id ) );

		$body = Helpers::xml_to_php_array( wp_remote_retrieve_body( $response ) );

		if ( 200 !== (int) $body['statusCode'] ) {

			return [];

		}

		if ( ! (bool) $body['data']['successfulFVRuns'] ) {

			return false;

		}

		$averages = $body['data']['average']['firstView'];

		$scores = [
			$averages['score_keep-alive'],
			$averages['score_gzip'],
			$averages['score_compress'],
			$averages['score_cache'],
			$averages['score_cdn'],
		];

		$average_score = array_sum( $scores ) / count( $scores );

		$body['grade'] = Helpers::calculate_grade( $averages['TTFB'], $average_score );

		unset( $body['statusCode'], $body['statusText'] );

		return $body;

	}

	/**
	 * Get location information.
	 *
	 * @since  1.0.0
	 *
	 * @return array Possible test location data.
	 */
	public static function get_locations() {

		/**
		 * @todo Setup debug/logging when tests start
		 */
		if ( Plugin::$options['debug'] ) {

		}

		$response = wp_remote_post( sprintf( self::$endpoints['test_locations'], Helpers::option( 'api_key', '' ) ) );

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $body['statusCode'] || empty( $body['data'] ) ) {

			$body['statusText'] = $body['statusText'] . '.<br />' . __( 'The default test location will be used.', 'site-speed-monitor' );

			if ( 200 === $body['statusCode'] ) {

				$body['statusText'] = __( 'We encountered an error. Is your API key correct?', 'site-speed-monitor' );

			}

			return [
				'error'    => true,
				'response' => $body['statusText'],
			];

		}

		$locations = [];

		// Update the 'Mobile Devices - XXX' group to be 'Mobile Devices'
		$body['data'] = array_map( function( $data ) {

			$data['group'] = ( strpos( $data['group'], 'Mobile Devices' ) !== false ) ? esc_html__( 'Mobile Devices', 'site-speed-monitor' ) : $data['group'];

			return $data;

		}, $body['data'] );

		foreach ( $body['data'] as $location => $data ) {

			$locations[ $data['group'] ][] = $data;

		}

		return $locations;

	}

	/**
	 * Display the admin notice
	 *
	 * @return mixed Markup for the admin notice.
	 *
	 * @since 1.0.0
	 */
	public function admin_notice() {

		printf(
			'<div class="notice notice-error"><p>%1$s</p></div>',
			sprintf(
				/* translators: 1. Link to the settings page. */
				wp_kses_post( 'Your website speed is not being monitored. Enter your WebPageTest.org API key on the Site Speed Monitor %s to begin.', 'site-speed-monitor' ),
				sprintf(
					'<a href="%s">' . __( 'settings page', 'site-speed-monitor' ) . '</a>',
					admin_url( 'options-general.php?page=site-speed-monitor' )
				)
			)
		);

	}

}
