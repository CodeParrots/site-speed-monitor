<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class AJAX {

	use Helpers;

	public function __construct() {

		add_action( 'wp_ajax_get_test_scores', [ $this, 'get_test_scores' ] );

		add_action( 'wp_ajax_get_test_site_details', [ $this, 'get_test_site_details' ] );

		add_action( 'wp_ajax_clear_site_speed_monitor_logs', [ $this, 'clear_site_speed_monitor_logs' ] );

		add_action( 'wp_ajax_update_chart_option', [ $this, 'update_chart_option' ] );

	}

	/**
	 * Return the test scores
	 *
	 * @return mixed Markup for the tooltip.
	 */
	public function get_test_scores() {

		$test_id = filter_input( INPUT_GET, 'test', FILTER_SANITIZE_STRING );

		if ( ! $test_id ) {

			print( __( 'Error: No test id specified.', 'site-speed-monitor' ) );

			exit;

		}

		$test_data = Helpers::get_test_data( $test_id );
		$test_data = isset( $test_data['average']['firstView'] ) ? $test_data['average']['firstView'] : false;

		if ( ! $test_data ) {

			print( __( 'Error', 'site-speed-monitor' ) );

			exit;

		}

		// ttfb
		// keep alive
		// compress transfer
		// compress images
		// cache
		// cdn
		$score_keys = [
			'TTFB'             => [
				'label'    => __( 'First Byte Time', 'site-speed-monitor' ),
				'url_base' => 'first_byte_time',
			],
			'score_keep-alive' => [
				'label' => __( 'Keep-Alive', 'site-speed-monitor' ),
				'url_base' => 'keep_alive_enabled',
			],
			'score_gzip'       => [
				'label' => __( 'GZIP Text', 'site-speed-monitor' ),
				'url_base' => 'compress_text',
			],
			'score_compress'   => [
				'label' => __( 'Compress Images', 'site-speed-monitor' ),
				'url_base' => 'compress_images',
			],
			'score_cache'      => [
				'label' => __( 'Cache Static', 'site-speed-monitor' ),
				'url_base' => 'cache_static_content',
			],
			'score_cdn'        => [
				'label' => __( 'CDN', 'site-speed-monitor' ),
				'url_base' => 'use_of_cdn',
			],
		];

		$scores = '';

		foreach ( $score_keys as $key => $data ) {

			$url_base = $data['url_base'];

			$scores .= sprintf(
				'<div class="score %1$s %2$s">
						<a href="%3$s" target="_blank">
							<div>%4$s</div>
						</a>
						<small>%5$s</small>
				</div>',
				esc_attr( $key ),
				esc_html( str_replace( 'N/A', 'none', Helpers::get_letter_grade( (int) $test_data[ $key ], $key ) ) ),
				esc_url( "http://www.webpagetest.org/performance_optimization.php?test={$test_id}&run=1#{$url_base}" ),
				esc_html( Helpers::get_letter_grade( (int) $test_data[ $key ], $key ) ),
				esc_html( $data['label'] )
			);

		}

		printf(
			'<div class="test-scores">
				<strong>%1$s</strong>
				%2$s
			</div>',
			esc_html( 'Test Grades', 'site-speed-monitor' ),
			wp_kses_post( $scores )
		);

		wp_die();

	}

	/**
	 * Return the test site details.
	 *
	 * @return mixed Markup for the site details when this test ran.
	 *         ie: The active theme and list of active plugins when the test ran.
	 */
	public function get_test_site_details() {

		$test_id = filter_input( INPUT_POST, 'test', FILTER_SANITIZE_STRING );

		if ( ! $test_id ) {

			print( __( 'Error: No test id specified.', 'site-speed-monitor' ) );

			exit;

		}

		$site_details = Helpers::get_test_site_details( $test_id );

		if ( ! $site_details ) {

			print( __( 'No data was stored for this test.', 'site-speed-monitor' ) );

			exit;

		}

		ob_start();

		/**
		 * Action before the site details list.
		 *
		 * @since 1.0.0
		 */
		do_action( 'site_speed_monitor_site_details_start', $test_id, $site_details );

		?>

		<h1><?php esc_html_e( 'Site Details', 'site-speed-monitor' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Below you will find details about your site at the time the speed test ran.', 'site-speed-monitor' ); ?></p>
		<hr />

		<h2><?php esc_html_e( 'Active Theme:', 'site-speed-monitor' ); ?></h2>
		<span>
			<?php
			$theme_table = [
				'titles' => [
					__( 'Theme Name', 'site-speed-monitor' ),
					__( 'Version', 'site-speed-monitor' ),
					__( 'Description', 'site-speed-monitor' ),
				],
				'data'   => [
					'name'        => $site_details['theme']['name'],
					'version'     => $site_details['theme']['version'],
					'description' => $site_details['theme']['description'],
					'url'         => $site_details['theme']['url'],
					'author'      => $site_details['theme']['author'],
					'author_url'  => $site_details['theme']['author_url'],
				],
			];
			/**
			 * Note: This is a custom table, so the helper function
			 * (Helpers::generate_table()) is not usable here.
			 */
			print( '<table class="wp-list-table widefat fixed striped site-details-theme-table">' );

				print( '<thead>' );

				foreach ( $theme_table['titles'] as $title ) {

					printf(
						'<th class="%1$s">%2$s</th>',
						esc_attr( sanitize_title( $title ) ),
						esc_html( $title )
					);

				} // @codingStandardsIgnoreLine

				print( '</thead>' );

				print( '<tbody>' );

					print( '<tr>' );

					foreach ( $theme_table['data'] as $key => $data ) {

						if ( in_array( $key, [ 'url', 'author', 'author_url' ], true ) ) {

							continue;

						}

						if ( 'name' === $key ) {

							$theme_link = ( isset( $theme_table['data']['url'] ) && ! empty( $theme_table['data']['url'] ) ) ? sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								esc_attr( $theme_table['data']['url'] ),
								esc_html( $data )
							) : esc_html( $data );

							$author_link = ( isset( $theme_table['data']['author_url'] ) && ! empty( $theme_table['data']['author_url'] ) ) ? sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								wp_kses_post( $theme_table['data']['author_url'] ),
								wp_kses_post( $theme_table['data']['author'] )
							) : $theme_table['data']['author'];

							$data = '<strong>' . $theme_link . '</strong>' . '<p class="description">' . sprintf(
								/* translators: Author name wrapped in anchor tag. */
								__( 'By: %s', 'site-speed-monitor' ),
								$author_link
							) . '</p>';

						}

						printf(
							'<td class="%1$s">%2$s</td>',
							esc_attr( sanitize_title( $key ) ),
							$data
						);

					}

					print( '</tr>' );

				print( '</tbody>' );

			print( '</table>' );

			// Helpers::generate_table( $theme_table['titles'], $site_details['theme'], 'site-details-theme-table' );
			?>
		</span>

		<?php

		if ( ! empty( $site_details['plugins'] ) ) {

			printf(
				'<h2>%s</h2>',
				esc_html( 'Active Plugins:', 'site-speed-monitor' )
			);

			/**
			 * Filter the speed test site details table titles.
			 *
			 * @param array Table titles.
			 *
			 * @since 1.0.0
			 */
			$titles = (array) apply_filters( 'site_speed_monitor_site_details_plugin_table_titles', [
				__( 'Plugin', 'site-speed-monitor' ),
				__( 'Version', 'site-speed-monitor' ),
				__( 'Description', 'site-speed-monitor' ),
			] );

			Helpers::generate_table( $titles, $site_details['plugins'], 'site-details-plugins-table' );

		}

		if ( empty( $site_details['plugins'] ) ) {

			esc_html_e( 'No plugins were active.', 'site-speed-monitor' );

		}

		printf(
			'<h2>%s</h2>',
			esc_html( 'Additional Site Info:', 'site-speed-monitor' )
		);

		$titles = [
			__( 'Detail', 'site-speed-monitor' ),
			__( 'Value', 'site-speed-monitor' ),
		];

		Helpers::generate_table( $titles, $site_details['site'], 'site-details-plugins-table' );

		/**
		 * Action after the site details list.
		 *
		 * @since 1.0.0
		 */
		do_action( 'site_speed_monitor_site_details_end', $test_id, $site_details );

		?>

		<a href="#" class="button button-secondary close-modal site-details"><?php esc_html_e( 'Close Site Details', 'site-speed-monitor' ); ?></a>

		<?php

		$contents = ob_get_contents();

		ob_get_clean();

		echo $contents; // xss ok.

		wp_die();

	}

	/**
	 * Clear speed test logs.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean Success when logs are deleted, else false.
	 */
	public function clear_site_speed_monitor_logs() {

		if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING ), 'clear-site-speed-monitor-log' ) ) {

			wp_send_json_error( [
				'error' => __( "The security check didn't pass. Please refresh the page and try again.", 'site-speed-monitor' ),
			] );

			return;

		}

		$query = new \WP_Query( [
			'post_type'      => 'sc_log',
			'posts_per_page' => -1,
		] );

		if ( ! $query->have_posts() ) {

			wp_send_json_error( [
				'error' => __( 'No logs found.', 'site-speed-monitor' ),
			] );

		}

		$ids = wp_list_pluck( $query->posts, 'ID' );

		foreach ( $ids as $log_id ) {

			wp_delete_post( $log_id, true );

		}

		wp_send_json_success();

	}

	/**
	 * Update chart options.
	 *
	 * @return bolean
	 */
	public function update_chart_option() {

		$option_name = filter_input( INPUT_POST, 'option', FILTER_SANITIZE_STRING );
		$is_checked  = filter_input( INPUT_POST, 'isChecked', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $option_name ) {

			return;

		}

		$chart_options = Helpers::option( 'chart_options', [] );

		// If the one test per day option is altered, refresh the diff cache.
		if ( 'one_per_day' === $option_name ) {

			Helpers::delete_site_speed_monitor_transients();

		}

		$chart_options[ $option_name ] = $is_checked;

		if ( Helpers::update_option( 'chart_options', $chart_options ) ) {

			wp_send_json_success();

		}

		wp_send_json_error();

	}

}
