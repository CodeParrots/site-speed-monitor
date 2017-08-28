<?php
/**
 * WP CLI Commands
 *
 * @since 1.0.0
 *
 * @todo Setup the remaining commands.
 */
namespace CPSSM\cli;

use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Commands extends \WP_CLI_Command {

	/**
	 * Run a Site Speed Monitor test.
	 *
	 * Errors when a speed test test was not successfully run.
	 *
	 * ## OPTIONS
	 *
	 * [--site_url=<url>]
	 * : The URL to speed test.
	 * ---
	 * default: site_url()
	 * ---
	 *
	 * [--runs=<integer>]
	 * : The number of tests to run. ("1" will actually run 2 tests: 1 initial view and 1 repeat view)
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--notify=<email>]
	 * : Email address to send the test results to.
	 * ---
	 * default:
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *      # Run Site Speed Monitor on current site URL
	 *      $ wp site-speed-monitor
	 *      Success: The speed test was successfully run.
	 *
	 *      # Run Site Speed Monitor on another site URL
	 *      $ wp site-speed-monitor --site_url=https://www.codeparrots.com
	 *      Success: The speed test was successfully run.
	 *
	 *      # Run Site Speed Monitor and send the results to an email address
	 *      $ wp site-speed-monitor --site_url=https://www.codeparrots.com --notify=support@codeparrots.com
	 *      Success: The speed test was successfully run.
	 *
	 *      # Run two Site Speed Monitor tests on the current site
	 *      $ wp site-speed-monitor --runs=2
	 *      Success: The speed test was successfully run.
	 *
	 */
	function run( $args, $assoc_args ) {

		$url = WP_CLI\Utils\get_flag_value( $assoc_args, 'site_url' );
		$url = ( 'site_url()' === $url ) ? site_url() : esc_url_raw( $url );

		\CPSSM\WPT_API::$test_parameters['url'] = $url; // @codingStandardsIgnoreLine

		$runs = (int) WP_CLI\Utils\get_flag_value( $assoc_args, 'runs' );

		if ( 1 < $runs ) {

			\CPSSM\WPT_API::$test_parameters['runs'] = $runs; // @codingStandardsIgnoreLine

		}

		$notify = WP_CLI\Utils\get_flag_value( $assoc_args, 'notify' );
		$notify = empty( $notify ) ? false : sanitize_email( $notify );

		if ( $notify ) {

			\CPSSM\WPT_API::$test_parameters['notify'] = $notify; // @codingStandardsIgnoreLine

		}

		if ( \CPSSM\WPT_API::$test_parameters['pingback'] !== $url ) {

			unset( \CPSSM\WPT_API::$test_parameters['pingback'] );

		}

		$test_data = \CPSSM\WPT_API::start_test();

		if ( ! $test_data ) {

			WP_CLI::error( __( 'Test failed to start.', 'site-speed-monitor' ) );

		}

		WP_CLI::success( sprintf(
			/* translators: 1. Site URL 2. Returned test ID. */
			__( 'Test successfully started for %1$s. Test ID: %2$s.', 'site-speed-monitor' ),
			$url,
			$test_data['data']['testId']
		) );

	}

}
