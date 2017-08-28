<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Log {

	use Helpers;

	public static $types;

	public function __construct() {

		self::$types = [
			'started'            => __( 'Site Speed Monitor test started.', 'site-speed-monitor' ),
			'received'           => __( 'Site Speed Monitor test data received.', 'site-speed-monitor' ),
			'completed'          => __( 'Site Speed Monitor test marked complete.', 'site-speed-monitor' ),
			'timeout'            => __( 'Site Speed Monitor test timedout.', 'site-speed-monitor' ),
			'cancelled'          => __( 'Site Speed Monitor test cancelled.', 'site-speed-monitor' ),
			'schedule_cancelled' => __( 'Scheduled Site Speed Monitor test cancelled.', 'site-speed-monitor' ),
			'settings_updated'   => __( 'Site Speed Monitor settings updated.', 'site-speed-monitor' ),
			'plugin_activated'   => /* translators: Activated plugin name. */ __( '%s plugin activated.', 'site-speed-monitor' ),
			'theme_switched'     => /* translators: Activated theme name. */ __( '%s theme activated.', 'site-speed-monitor' ),
		];

		add_action( 'init', [ $this, 'log_cpt' ] );

	}

	/**
	 * Add an entry to the speed test log.
	 *
	 * @param  string $type The log type to add.
	 * @param  array  $data Additional data for the log.
	 *
	 * @since 1.0.0
	 */
	public static function entry( $type, $data = [] ) {

		if ( ! Helpers::logging() ) {

			return;

		}

		$my_post = [
			'post_title'  => $type,
			'post_status' => 'publish',
			'post_type'   => 'sc_log',
		];

		$entry_id = wp_insert_post( $my_post );

		if ( 0 === $entry_id ) {

			return;

		}

		/**
		 * Filter the log data.
		 *
		 * @param array  The log data array.
		 * @param string The log type. Possible:
		 *                            <span>plugin_activated</span>   - A plugin is activated.
		 *                            <span>theme_switched</span>     - A theme is activated.
		 *                            <span>received</span>           - WebPageTest.org payload is received.
		 *                            <span>cancelled</span>          - A speed test is cancelled.
		 *                            <span>completed</span>          - A speed test transitions from pending to complete.
		 *                            <span>timeout</span>            - No response was received from WebPageTest.org
		 *                            <span>schedule_cancelled</span> - A scheduled speed test was cancelled.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		$data = (array) apply_filters( 'site_speed_monitor_log_data', $data, $type );

		foreach ( $data as $k => $v ) {

			update_post_meta( $entry_id, $k, $v );

		}

	}

	/**
	 * Retreive the log type title.
	 *
	 * @param  string $type The log text to retreive.
	 *
	 * @since  1.0.0
	 *
	 * @return string       Return the log type title text.
	 */
	public static function log_type( $type = 'started', $replacement = '' ) {

		if ( ! empty( $replacement ) ) {

			return sprintf(
				self::$types[ $type ],
				$replacement
			);

		}

		return self::$types[ $type ];

	}

	/**
	 * Register the log post type.
	 *
	 * @action init
	 *
	 * @link http://codex.WordPress.org/Function_Reference/register_post_type
	 */
	public function log_cpt() {

		$args = [
			'labels'             => [
				'name'               => esc_html__( 'Site Speed Monitor Log', 'site-speed-monitor' ),
				'singular_name'      => esc_html__( 'Log', 'site-speed-monitor' ),
				'menu_name'          => esc_html__( 'Log', 'site-speed-monitor' ),
				'name_admin_bar'     => esc_html__( 'Log', 'site-speed-monitor' ),
			],
			'description'        => esc_html__( 'Site Speed Monitor log.', 'site-speed-monitor' ),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => [
				'slug' => 'sc_log',
			],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title', 'editor' ],
		];

		register_post_type( 'sc_log', $args );

	}

}
