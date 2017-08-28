<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Admin_Notices {

	use Helpers;

	public function __construct() {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );

	}

	/**
	 * Display admin notice.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed HTML markup for the admin notice.
	 */
	public function admin_notice() {

		$options = Plugin::$options;

		$screen              = get_current_screen();
		$site_speed_monitor_started = filter_input( INPUT_GET, 'site-speed-monitor-started', FILTER_SANITIZE_NUMBER_INT );
		$response            = filter_input( INPUT_GET, 'response', FILTER_SANITIZE_STRING );

		if ( ! $site_speed_monitor_started && $response ) {

			self::generate_notice( 'error', $response . '.' );

		}

		$scheduled_test_cancelled = filter_input( INPUT_GET, 'site-speed-monitor-cancel-scheduled-test', FILTER_SANITIZE_NUMBER_INT );

		if ( $scheduled_test_cancelled ) {

			self::generate_notice( 'success', __( 'Scheduled speed test successfully cancelled.', 'site-speed-monitor' ) );

		}

		$stripped_parameters = get_site_transient( 'site_speed_monitor_stripped_parameters' );

		if (
			isset( $screen->base ) &&
			'settings_page_site-speed-monitor' === $screen->base &&
			false !== $stripped_parameters
		) {

			self::generate_notice( 'error', sprintf(
				/* translators: 1. Comma separated list of stripped test parameters. */
				__( 'You attempted to save test parameters that are reserved by Site Speed Monitor. The following parameters were not saved: %1$s', 'site-speed-monitor' ),
				implode( ', ', array_map( function( $value ) {
					return '<code>' . $value . '</code>';
				}, $stripped_parameters ) )
			) );

		}

	}

	/**
	 * Render the admin notice markup.
	 *
	 * @param  string $type    The notice type (success/warning/error).
	 * @param  string $message The message to display.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed           Markup for the admin notice.
	 */
	public static function generate_notice( $type, $message ) {

		printf(
			'<div class="notice notice-%1$s"><p><strong>Site Speed Monitor:</strong></p><p>%2$s</p></div>',
			esc_attr( $type ),
			wp_kses_post( $message )
		);

	}

}
