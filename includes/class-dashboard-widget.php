<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Dashboard_Widget {

	use Helpers;

	public function __construct() {

		add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ] );

		add_action( 'site_speed_monitor_widget_top', [ $this, 'display_averages' ], 20 );

	}

	/**
	 * Register the Site Speed Monitor dashboard widget
	 *
	 * @since 1.0.0
	 */
	function dashboard_widget() {

		global $wp_meta_boxes;

		/**
		 * Warning icon when the last speed test returns slower than the max time allowed.
		 */
		$icon  = Helpers::is_load_speed_excessive() ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" x="0px" y="0px" viewBox="0 0 48 48" class="icon icons8-High-Priority" style="height:20px;width:20px;display:inline-block;fill:#false;vertical-align:middle;margin-top:-2px;"><g id="surface1"><path style=" fill:#F44336;" d="M 21.199219 44.800781 L 3.199219 26.800781 C 1.601563 25.199219 1.601563 22.699219 3.199219 21.101563 L 21.199219 3.101563 C 22.800781 1.5 25.300781 1.5 26.898438 3.101563 L 44.898438 21.101563 C 46.5 22.699219 46.5 25.199219 44.898438 26.800781 L 26.898438 44.800781 C 25.300781 46.398438 22.699219 46.398438 21.199219 44.800781 Z "></path><path style=" fill:#FFFFFF;" d="M 21.601563 32.699219 C 21.601563 32.398438 21.699219 32.101563 21.800781 31.800781 C 21.898438 31.5 22.101563 31.300781 22.300781 31.101563 C 22.5 30.898438 22.800781 30.699219 23.101563 30.601563 C 23.398438 30.5 23.699219 30.398438 24.101563 30.398438 C 24.5 30.398438 24.800781 30.5 25.101563 30.601563 C 25.398438 30.699219 25.699219 30.898438 25.898438 31.101563 C 26.101563 31.300781 26.300781 31.5 26.398438 31.800781 C 26.5 32.101563 26.601563 32.398438 26.601563 32.699219 C 26.601563 33 26.5 33.300781 26.398438 33.601563 C 26.300781 33.898438 26.101563 34.101563 25.898438 34.300781 C 25.699219 34.5 25.398438 34.699219 25.101563 34.800781 C 24.800781 34.898438 24.5 35 24.101563 35 C 23.699219 35 23.398438 34.898438 23.101563 34.800781 C 22.800781 34.699219 22.601563 34.5 22.300781 34.300781 C 22.101563 34.101563 21.898438 33.898438 21.800781 33.601563 C 21.699219 33.300781 21.601563 33.101563 21.601563 32.699219 Z M 25.800781 28.101563 L 22.199219 28.101563 L 21.699219 13 L 26.300781 13 Z "></path></g></svg>  ' : '';

		wp_add_dashboard_widget(
			'site_speed_monitor_widget',
			sprintf(
				/* translators: Warning icon when last speed test was excessive. */
				__( '%1$sSite Speed Monitor', 'site-speed-monitor' ),
				$icon
			),
			[ $this, 'dashboard_widget_content' ]
		);

	}

	/**
	 * Render the Site Speed Monitor dashboard widget content
	 *
	 * @return mixed Markup for the widget
	 *
	 * @since 1.0.0
	 */
	public function dashboard_widget_content() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'site-speed-monitor-widget', \Site_Speed_Monitor::$assets_url . "/css/widget{$suffix}.css", [], \Site_Speed_Monitor::$version, 'all' );

		if ( empty( Helpers::option( 'api_key', '' ) ) ) {

			Helpers::site_speed_monitor_notice( 'error', sprintf(
				/* translators: 1. 'settings page' wrapped in an anchor tag. */
				__( 'Enter your WebPageTest.org API key on the Site Speed Monitor %s.', 'site-speed-monitor' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					admin_url( 'options-general.php?page=site-speed-monitor' ),
					esc_html( 'settings page', 'site-speed-monitor' )
				)
			) );

			return;

		}

		/**
		 * Action hook to add content before the speed test widget.
		 *
		 * @since 1.0.0
		 */
		do_action( 'site_speed_monitor_widget_top' );

		?>

		<!-- Jump Link -->
		<a name="site-speed-monitor-top"></a>

		<!--
			Strip #site-speed-monitor-top from the URL
			Note: Taken from core: https://core.trac.wordpress.org/browser/tags/4.8/src/wp-admin/includes/misc.php#L916
		-->
		<script type="text/javascript">
		if ( window.history.replaceState ) {

			if ( location.href.match( /\#site-speed-monitor-top/ ) ) {

				setTimeout( function() {

					window.history.replaceState( null, null, document.getElementById( 'wp-admin-canonical' ).href );

				}, 1000 );

			}

		}
		</script>

		<form method="post" id="site-speed-monitor">

			<?php

			wp_nonce_field( 'start_site_speed_monitor', 'start_site_speed_monitor' );

			$other_atts = [];

			if ( Helpers::is_speed_test_pending() ) {

				$other_atts = [
					'disabled' => 'disabled',
				];

			}

			$completed_tests = Helpers::option( 'completed_tests', [] );

			printf(
				'<div class="actions">
					%1$s
					%2$s
					%3$s
				</div>',
				Helpers::is_local() ? '' : get_submit_button(
					esc_html__( 'Run Speed Test', 'site-speed-monitor' ),
					'primary',
					'site-speed-monitor',
					false,
					$other_atts
				),
				! empty( $completed_tests ) ? sprintf(
					'<a href="%1$s" class="button button-secondary view-test-history">%2$s</a>',
					admin_url( 'tools.php?page=site-speed-monitor-tools#history' ),
					esc_html__( 'Test History', 'site-speed-monitor' )
				) : '',
				! empty( $completed_tests ) ? sprintf(
					'<a href="%1$s" class="button button-secondary view-performance-chart">%2$s</a>',
					admin_url( 'tools.php?page=site-speed-monitor-tools#charts' ),
					esc_html__( 'Performance Chart', 'site-speed-monitor' )
				) : ''
			);

			Helpers::widget_notice( 'info' )

			?>

		</form>

		<?php

		/**
		 * Action hook to add content after the speed test widget.
		 *
		 * @since 1.0.0
		 */
		do_action( 'site_speed_monitor_widget_bottom' );

	}

	/**
	 * Generate the load time averages accross all tests.
	 *
	 * @return mixed Markup for the load time averages.
	 *
	 * @since  1.0.0
	 */
	public function display_averages() {

		/**
		 * Disable the widget average display at the top of the admin widget.
		 *
		 * @since 1.0.0
		 *
		 * @var boolean
		 */
		if ( ! (boolean) apply_filters( 'site_speed_monitor_widget_averages', true ) ) {

			return;

		}

		$completed_tests = Helpers::option( 'completed_tests', [] );
		$pending_tests   = Helpers::option( 'pending_tests', [] );

		$pending = false;

		if ( empty( $completed_tests ) && empty( $pending_tests ) ) {

			printf(
				'<div class="no-tests">
					<h3>%1$s</h3>
				</div>',
				esc_html__( "You haven't run any tests yet. Click 'Run Speed Test' below to get started.", 'site-speed-monitor' )
			);

			return;

		}

		if ( ! empty( $pending_tests ) ) {

			$pending = true;

			$preloader = sprintf(
				'<img src="%s" class="pending-preloader" />',
				admin_url( 'images/wpspin_light.gif' )
			);

			$average_container = sprintf(
				'<div class="average pending">
					<h4>%1$s</h4>
					%2$s
				</div>
				<div class="average pending">
					<h4>%3$s</h4>
					%4$s
				</div>
				<div class="average pending">
					<h4>%5$s</h4>
					%6$s
				</div>',
				esc_html( 'First View', 'site-speed-monitor' ),
				wp_kses_post( $preloader ),
				esc_html( 'Repeat View', 'site-speed-monitor' ),
				wp_kses_post( $preloader ),
				esc_html( 'Last Run', 'site-speed-monitor' ),
				wp_kses_post( $preloader )
			);

		}

		$first_view_averages  = [];
		$repeat_view_averages = [];

		// If the tests are completed.
		if ( ! $pending ) {

			foreach ( $completed_tests as $test ) {

				if ( ! isset( $test['data'] ) || ! isset( $test['data']['testData'] ) || ! isset( $test['data']['testData']['data'] ) ) {

					return;

				}

				$first_view_averages[]  = Helpers::get_load_time( $test['data']['testData']['data'], 'first-view' );
				$repeat_view_averages[] = Helpers::get_load_time( $test['data']['testData']['data'], 'repeat-view' );

			}

			$averages = [
				__( 'First View', 'site-speed-monitor' )  => ( empty( $first_view_averages ) ? '' : array_sum( $first_view_averages ) / count( $first_view_averages ) ),
				__( 'Repeat View', 'site-speed-monitor' ) => ( empty( $repeat_view_averages ) ? '' : array_sum( $repeat_view_averages ) / count( $repeat_view_averages ) ),
			];

			$average_container = '';

			foreach ( $averages as $title => $average ) {

				$average_container .= sprintf(
					'<div class="average %1$s-average %2$s">
						<h4>%3$s</h4>
						%4$s
					</div>',
					esc_attr( sanitize_title( $title ) ),
					esc_attr( Helpers::get_load_time_grade( $average ) ),
					esc_html( $title ),
					sprintf(
						'%1$s <span>%2$s</span>',
						(float) round( $average, 2 ),
						_n( 'second', 'seconds', $average, 'site-speed-monitor' )
					)
				);

			}

			$average_container .= sprintf(
				'<div class="average last-run">
					<h4>%1$s</h4>
					%2$s
				</div>',
				esc_html__( 'Last Run', 'site-speed-monitor' ),
				Helpers::get_last_test_start_date()
			);

		}

		printf(
			'<div class="load-time-averages">
				<p class="description">%1$s</p>
				<div class="averages-wrap">
					%2$s
				</div>
			</div>',
			esc_html( 'Below are the latest load times for your site.', 'site-speed-monitor' ),
			wp_kses_post( $average_container )
		);

	}

}
