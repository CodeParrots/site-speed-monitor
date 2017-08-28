<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Plugin {

	/**
	 * Plugin options
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public static $options;

	/**
	 * Class constructor
	 */
	public static function init() {

		/**
		 * Filter the Site Speed Monitor options array.
		 *
		 * @param array Site Speed Monitor options array.
		 *
		 * @since 1.0.0
		 */
		self::$options = (array) apply_filters( 'site_speed_monitor_options', get_option( 'site_speed_monitor_options', [
			// General Options.
			'api_key'             => '',
			'email_results'       => true,
			'private_tests'       => false,
			'test_location'       => false,
			'test_browser'        => 'Chrome',
			'test_speed'          => 'Cable',
			'cron_tests'          => true,
			'cron_frequency'      => 'weekly',
			'activation_checks'   => true,
			'admin_bar_notice'    => true,
			// Debug Options.
			'debug'               => false,
			'logging'             => false,
			// Others.
			'stripped_parameters' => [],
			'test_run_count'      => 1,
			'test_parameters'     => [],
			'pending_tests'       => [],
			'completed_tests'     => [],
			'chart_options'       => [
				'one_per_day'  => 1,
				'newest_first' => 0,
				'display_diff' => 0,
			],
		] ) );

		new Log;
		new Settings;
		new Tools;
		new Dashboard_Widget;
		new Admin_Notices;
		new Admin_Bar;
		new WPT_API;
		new Listener;
		new Actions;
		new AJAX;
		new Cron;

		if ( self::is_wp_cli() ) {

			\WP_CLI::add_command( 'site-speed-monitor', __NAMESPACE__ . '\cli\Commands' );

		}

		add_filter( 'plugin_action_links_' . \Site_Speed_Monitor::$plugin_file, [ get_called_class(), 'plugin_action_links' ], 10, 4 );

	}

	/**
	 * Render custom action links for Site Speed Monitor.
	 *
	 * @param  array  $actions     The initial plugin action links array.
	 * @param  string $plugin_file Plugin file.
	 * @param  array  $plugin_data Plugin data array.
	 * @param  string $context     null.
	 *
	 * @since 1.0.0
	 *
	 * @return array               Action links array.
	 *
	 */
	public static function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		$actions['site-speed-monitor-options'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'options-general.php?page=site-speed-monitor' ),
			esc_html__( 'Settings', 'site-speed-monitor' )
		);

		$actions['site-speed-monitor-tools'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'tools.php?page=site-speed-monitor-tools' ),
			esc_html__( 'Tools', 'site-speed-monitor' )
		);

		return $actions;

	}

	/**
	 * Check if WP-CLI is running.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean True when WP-CLI is running, else false.
	 */
	public static function is_wp_cli() {

		return ( defined( 'WP_CLI' ) && WP_CLI );

	}

}
