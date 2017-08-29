<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}

final class Tests_Table extends \WP_List_Table {

	use Helpers;

	private $data = [];

	/**
	 * The maximum time a test will wait for a response before it times out
	 * and a user can click 'Force Complete'.
	 *
	 * @var integer
	 */
	public static $test_timeout;

	function __construct() {

		parent::__construct( [
			'singular' => __( 'Site Speed Monitor Test', 'site-speed-monitor' ),
			'plural'   => __( 'Site Speed Monitor Tests', 'site-speed-monitor' ),
			'ajax'     => false,
		] );

		$this->data = $this->get_table_data();

		/**
		 * Minutes the test should wait to hear a pingback before allowing
		 * the user to manually fetch the data from WebPageTest.org
		 *
		 * @Default 2 minutes on local host, 5 minutes on a live site
		 *
		 * @var integer
		 */
		self::$test_timeout = (int) apply_filters( 'site_speed_monitor_pingback_timeout', Helpers::is_local() ? 2 : 5 );

	}

	public function get_table_data() {

		return array_merge( Helpers::option( 'pending_tests', [] ), Helpers::option( 'completed_tests', [] ) );

	}

	function no_items() {

		esc_html_e( "You haven't run any speed tests yet. To run a speed test, click the \"Start Site Speed Monitor\" button below.", 'site-speed-monitor' );

	}

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'start_date':

				return $item[ $column_name ];

			case 'first_view num':

				return isset( $item['data']['testData']['data']['average']['firstView']['loadTime'] ) ? sprintf(
					'<div class="test">%1$s</div>',
					esc_html( Helpers::convert_time( $item['data']['testData']['data']['average']['firstView']['loadTime'] ) )
				) : '-';

			case 'repeat_view num':

				return isset( $item['data']['testData']['data']['average']['repeatView']['loadTime'] ) ? sprintf(
					'<div class="test">%1$s</div>',
					esc_html( Helpers::convert_time( $item['data']['testData']['data']['average']['repeatView']['loadTime'] ) )
				) : '-';

			case 'grade num':

				$this->grade_markup( $item );

				break;

			default:

				return print_r( $item, true );

		}

	}

	function get_sortable_columns() {

		$sortable_columns = [
			// 'start_date' => [ 'start_date', false ],
		];

		return $sortable_columns;

	}

	function get_columns() {

		$columns = [
			'start_date'  => esc_html__( 'Test Date', 'site-speed-monitor' ),
			'first_view num'  => esc_html__( 'First View', 'site-speed-monitor' ),
			'repeat_view num' => esc_html__( 'Repeat View', 'site-speed-monitor' ),
			'grade num'   => esc_html__( 'Grade', 'site-speed-monitor' ),
		];

		return $columns;

	}

	function column_start_date( $item ) {

		$actions = ( 'pending' === $item['status'] ) ? [] : [
			'view' => sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( $item['data']['userUrl'] ),
				esc_html__( 'View', 'site-speed-monitor' )
			),
			// 'summary' => sprintf(
			// 	'<a href="%1$s" target="_blank">%2$s</a>',
			// 	esc_url( $item['data']['userUrl'] ),
			// 	esc_html__( 'Download', 'site-speed-monitor' )
			// ),
			'delete' => sprintf(
				'<a href="%1$s">%2$s</a>',
				wp_nonce_url( add_query_arg( [
					'action' => 'delete-site-speed-monitor-test',
					'testId' => $item['data']['testId'],
				], admin_url() ), 'delete-site-speed-monitor-test' ),
				esc_html__( 'Delete', 'site-speed-monitor' )
			),
		];

		if ( 'pending' !== $item['status'] && ( isset( $item['site_details'] ) && ! empty( $item['site_details'] ) ) ) {

			array_splice( $actions, 1, 0, [
				'site_details' => sprintf(
					'<a class="test-site-details" href="#" data-test-id="%1$s" target="_blank">%2$s</a>',
					esc_attr( $item['data']['testId'] ),
					esc_html__( 'Site Details', 'site-speed-monitor' )
				),
			] );

		}

		printf(
			'%1$s %2$s %3$s',
			date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), (int) $item['start_date'], false ),
			"<input type='hidden' class='test-id' value='{$item["data"]["testId"]}' />",
			$this->row_actions( $actions )
		);

	}

	function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$this->data = Helpers::sort_tests( $this->data );

		$per_page = 8;

		$current_page = $this->get_pagenum();

		$total_items = count( $this->data );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		] );

		$this->items = array_slice( $this->data, ( ( $current_page - 1 ) * $per_page ), $per_page );

	}

	/**
	 * Get the Speed test grade.
	 *
	 * @param  array $item The test data array.
	 *
	 * @return mixed       Markup for the grade markup.
	 *
	 * @since  1.0.0
	 */
	public function grade_markup( $item ) {

		switch ( $item['status'] ) {

			default:
			case 'pending':

				$time_diff = date_diff( date_create( date( 'm/d/Y H:i:s', $item['start_date'] ) ), date_create( current_time( 'm/d/Y H:i:s' ) ) );

				$force_complete = ( isset( $time_diff->i ) && self::$test_timeout <= (int) $time_diff->i ) ? sprintf(
					'<a href="%1$s" class="force-complete">%2$s</a>',
					wp_nonce_url( add_query_arg( [
						'complete' => true,
						'id'       => $item['data']['testId'],
					], site_url() ), 'site-speed-monitor-force-complete' ),
					esc_html__( 'Force Complete', 'site-speed-monitor' )
				) : false;

				$cancel_test = ! $force_complete ? sprintf(
					'<a href="%1$s" class="cancel-test">%2$s</a>',
					wp_nonce_url( add_query_arg( [
						'cancel' => true,
						'id'     => $item['data']['testId'],
					], site_url() ), 'site-speed-monitor-cancel-test' ),
					esc_html__( 'Cancel Test', 'site-speed-monitor' )
				) : false;

				if ( $force_complete ) {

					/**
					 * @todo Setup debug/logging data
					 *
					 * In this state, logs would be created for each timeout on every page load.
					 * This should only log one time.
					 */
					// Log::entry( Log::log_type( 'timeout' ) );

				}

				return printf(
					'<img src="%1$s" alt="preloader" height="15" width="15" />%2$s',
					esc_url( admin_url( 'images/wpspin_light.gif' ) ),
					wp_kses_post( $force_complete . $cancel_test )
				);

			case 'cancelled':

				return printf(
					'<span class="test-failed">%1$s</span>',
					esc_html__( 'Test Canceled', 'site-speed-monitor' )
				);

			case 'completed':

				if ( ! $item['data']['testData'] ) {

					return printf(
						'<span class="test-failed">%1$s</span>',
						esc_html__( 'Test Failed', 'site-speed-monitor' )
					);

				}

				return isset( $item['data']['testData']['grade'] ) ? printf(
					'<div class="grade grade-%1$s">%2$s</div>',
					esc_attr( strtolower( $item['data']['testData']['grade'] ) ),
					esc_html( $item['data']['testData']['grade'] )
				) : '';

		} // End switch().

	}

}
