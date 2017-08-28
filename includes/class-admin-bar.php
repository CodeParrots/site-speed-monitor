<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Admin_Bar {

	public function __construct() {

		add_action( 'admin_bar_menu', [ $this, 'site_speed_monitor_admin_bar' ], PHP_INT_MAX, 1 );

	}

	/**
	 * Display admin bar when active.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	function site_speed_monitor_admin_bar( $wp_admin_bar ) {

		if ( ! Helpers::option( 'admin_bar_notice', true ) ) {

			return;

		}

		$last_speed_test = Helpers::get_last_test_load_speed();

		if ( empty( $last_speed_test ) ) {

			return;

		}

		$grade = Helpers::get_load_time_grade( $last_speed_test['first-view'], false );
		$badge = Helpers::get_load_time_grade( $last_speed_test['first-view'], true );

		$rtl    = ! is_rtl() ? '' : '-rtl';
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'site-speed-monitor-admin-bar', \Site_Speed_Monitor::$assets_url . "/css/admin-bar{$rtl}{$suffix}.css", array(), \Site_Speed_Monitor::$version, 'all' );

		// Add the main siteadmin menu item.
		$wp_admin_bar->add_menu( array(
			'id'     => 'site-speed-monitor-admin-bar',
			'href'   => admin_url( 'tools.php?page=site-speed-monitor-tools#history' ),
			'parent' => 'top-secondary',
			'title'  => sprintf(
				/* translators */
				'%1$s %2$s',
				sprintf(
					/* translators: Last test result load time. */
					_n( '%s second', '%s seconds', $last_speed_test['first-view'], 'site-speed-monitor' ),
					esc_html( $last_speed_test['first-view'] )
				),
				wp_kses_post( $badge )
			),
			'meta'   => [
				'class' => 'site-speed-monitor',
				'title' => esc_html__( 'Site Speed Monitor - Website Load Time', 'site-speed-monitor' ),
			],
		) );

	}

}
