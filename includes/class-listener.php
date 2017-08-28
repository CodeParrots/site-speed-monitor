<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Listener {

	use Helpers;

	/**
	 * Test ID Instance.
	 *
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	private $test_id;

	/**
	 * Nonce instance.
	 *
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	private $nonce;

	public function __construct() {

		add_filter( 'query_vars',    [ $this, 'add_query_vars' ], 0 );

		add_action( 'parse_request', [ $this, 'sniff_requests' ], 0 );

		add_action( 'init',          [ $this, 'endpoint' ], 0 );
		add_action( 'init',          [ $this, 'start_speed_test' ], 0 );
		add_action( 'init',          [ $this, 'delete_speed_test' ], 0 );
		add_action( 'init',          [ $this, 'clear_test_log' ], 0 );

	}

	/**
	 * Add query variables to the request.
	 *
	 * @param  array  $vars Array of query variables
	 *
	 * @return array       Final array of query varaibles
	 *
	 * @since  1.0.0
	 */
	public function add_query_vars( $vars ) {

		$vars[] = 'complete';
		$vars[] = 'cancel';
		$vars[] = 'cancel-scheduled';
		$vars[] = 'id';
		$vars[] = '_wpnonce';

		return $vars;

	}

	/**
	 * Listen for the API requests.
	 *
	 * @since 1.0.0
	 */
	public function sniff_requests() {

		global $wp;

		if ( isset( $wp->query_vars['complete'] ) ) {

			/**
			 * @todo Setup debug/logging data
			 */
			Log::entry( Log::log_type( 'received' ) );

			$this->test_id = urldecode( $wp->query_vars['id'] );
			$this->nonce   = isset( $wp->query_vars['_wpnonce'] ) ? urldecode( $wp->query_vars['_wpnonce'] ) : false;

			if ( $this->nonce && ! wp_verify_nonce( $this->nonce, 'site-speed-monitor-force-complete' ) ) {

				wp_safe_redirect( add_query_arg( 'site-speed-monitor-force-complete', 'fail', admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

				exit;

			}

			$this->transition_pending_test( 'completed' );

		}

		if ( isset( $wp->query_vars['cancel'] ) ) {

			/**
			 * @todo Setup debug/logging data
			 */
			Log::entry( Log::log_type( 'cancelled' ) );

			$this->test_id = urldecode( $wp->query_vars['id'] );
			$this->nonce   = isset( $wp->query_vars['_wpnonce'] ) ? urldecode( $wp->query_vars['_wpnonce'] ) : false;

			if ( $this->nonce && ! wp_verify_nonce( $this->nonce, 'site-speed-monitor-cancel-test' ) ) {

				wp_safe_redirect( add_query_arg( 'site-speed-monitor-cancel-test', 'fail', admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

				exit;

			}

			$this->transition_pending_test( 'cancelled' );

		}

		if ( isset( $wp->query_vars['cancel-scheduled'] ) ) {

			/**
			 * @todo Setup debug/logging data
			 */
			Log::entry( Log::log_type( 'schedule_cancelled' ) );

			$this->nonce = isset( $wp->query_vars['_wpnonce'] ) ? urldecode( $wp->query_vars['_wpnonce'] ) : false;

			if ( $this->nonce && ! wp_verify_nonce( $this->nonce, 'site-speed-monitor-cancel-scheduled-test' ) ) {

				wp_safe_redirect( add_query_arg( 'site-speed-monitor-cancel-scheduled-test', false, admin_url() ) );

				exit;

			}

			wp_clear_scheduled_hook( 'delayed_speed_test_run' );

			wp_safe_redirect( add_query_arg( 'site-speed-monitor-cancel-scheduled-test', true, admin_url() ) );

			exit;

		}

	}

	/**
	 * Transition a pending speed test test to complete.
	 *
	 * @since 1.0.0
	 */
	public function transition_pending_test( $type = 'completed' ) {

		if ( ! $this->test_id || empty( $this->test_id ) ) {

			return;

		}

		if ( empty( Plugin::$options['pending_tests'] ) ) {

			return;

		}

		$pending_test_key = $this->locate_pending_test( Plugin::$options['pending_tests'] );

		if ( ! is_integer( $pending_test_key ) ) {

			return;

		}

		$test_to_transition = array_splice( Plugin::$options['pending_tests'], $pending_test_key, 1 );

		$test_to_transition[0]['status']        = $type;
		$test_to_transition[0]['complete_date'] = current_time( 'timestamp' );

		// Mark a site speed test as cancelled.
		if ( 'cancelled' === $type ) {

			$cancelled = WPT_API::cancel_test( $this->test_id );

			if ( ! $cancelled ) {

				wp_safe_redirect( add_query_arg( 'site-speed-monitor-test-cancelled', false, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

				exit;

			}

			/**
			 * @todo Setup debug/logging data
			 */
			Log::entry( Log::log_type( 'cancelled' ) );

			Plugin::$options['completed_tests'][] = current( $test_to_transition );

			update_option( 'site_speed_monitor_options', Plugin::$options );

			wp_safe_redirect( add_query_arg( 'site-speed-monitor-test-cancelled', true, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

			exit;

		}

		$test_to_transition[0]['data']['testData'] = WPT_API::get_test_data( $test_to_transition[0]['data']['testId'] );

		Plugin::$options['completed_tests'][] = current( $test_to_transition );

		update_option( 'site_speed_monitor_options', Plugin::$options );

		/**
		 * @todo Setup debug/logging data
		 */
		Log::entry( Log::log_type( 'completed' ) );

		wp_safe_redirect( add_query_arg( 'site-speed-monitor-force-complete', true, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

		exit;

	}

	/**
	 * Locate the pending test.
	 *
	 * @param  array $pending_tests Array of pending tests.
	 *
	 * @return integer              The location of the pending test in the 'pending_tests' option array.
	 */
	public function locate_pending_test( $pending_tests = [] ) {

		$iteration = 0;

		foreach ( $pending_tests as $pending_test ) {

			if ( $this->test_id !== $pending_test['data']['testId'] ) {

				$iteration++;

				continue;

			}

			return $iteration;

		}

		return false;

	}

	/**
	 * Expose custom endpoint URL.
	 * sample endpoint: http://example.com/site-speed-monitor/?complete=1&id=TEST_ID
	 *
	 * @since 1.0.0
	 */
	public function endpoint() {

		add_rewrite_rule(
			'^site-speed-monitor/?id=?([a-zA-Z0-9%+]+)?',
			'index.php?complete=1&id=$matches[1]',
			'top'
		);

	}

	/**
	 * Handles any POSTs made by the plugin.
	 *
	 * @action init
	 *
	 * @since  1.0.0
	 */
	public function start_speed_test() {

		$nonce = filter_input( INPUT_POST, 'start_site_speed_monitor', FILTER_SANITIZE_STRING );

		if ( ! $nonce ) {

			return;

		}

		if ( ! wp_verify_nonce( $nonce, 'start_site_speed_monitor' ) ) {

			wp_safe_redirect( admin_url( 'tools.php?page=site-speed-monitor-tools' ) );

			exit;

		}

		$api = new WPT_API();

		$api::start_test();

	}

	/**
	 * Delete a stored Site Speed Monitor test.
	 *
	 * @action init
	 *
	 * @since  1.0.0
	 */
	public function delete_speed_test() {

		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		$test_id = filter_input( INPUT_GET, 'testId', FILTER_SANITIZE_STRING );
		$action  = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

		if ( ! $nonce || ! $test_id || 'delete-site-speed-monitor-test' !== $action ) {

			return;

		}

		if ( ! wp_verify_nonce( $nonce, 'delete-site-speed-monitor-test' ) ) {

			wp_safe_redirect( admin_url( 'tools.php?page=site-speed-monitor-tools' ) );

			exit;

		}

		$options = Plugin::$options;

		$tests = [
			'pending_tests'   => Helpers::option( 'pending_tests', [] ),
			'completed_tests' => Helpers::option( 'completed_tests', [] ),
		];

		foreach ( $tests as $type => $array ) {

			if ( empty( $array ) ) {

				continue;

			}

			$data = wp_list_pluck( $array, 'data' );

			$test_ids = wp_list_pluck( $data, 'testId' );

			$key = array_search( $test_id, $test_ids );

			if ( false === $key ) {

				continue;

			}

			unset( $tests[ $type ][ $key ] );

			Plugin::$options[ $type ] = array_values( $tests[ $type ] );

			update_option( 'site_speed_monitor_options', Plugin::$options );

			wp_safe_redirect( add_query_arg( 'site-speed-monitor-test-deleted', true, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

		}

	}

	/**
	 * Clear the test log of all tests.
	 *
	 * @since 1.0.0
	 */
	public function clear_test_log() {

		$nonce  = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

		if ( ! isset( $nonce ) || 'clear-test-log' !== $action ) {

			return;

		}

		if ( ! wp_verify_nonce( $nonce, 'site_speed_monitor_clear_test_log' ) ) {

			wp_safe_redirect( add_query_arg( 'site-speed-monitor-clear-test-log', false, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

			exit;

		}

		Plugin::$options['pending_tests']   = [];
		Plugin::$options['completed_tests'] = [];

		update_option( 'site_speed_monitor_options', Plugin::$options );

		wp_safe_redirect( add_query_arg( 'site-speed-monitor-clear-test-log', true, admin_url( 'tools.php?page=site-speed-monitor-tools' ) ) );

		exit;

	}

}
