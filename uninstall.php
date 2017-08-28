<?php
/**
 * Site Speed Monitor Uninstall
 * Delete all log post types, clear transients.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	die;

}

delete_option( 'site_speed_monitor_options' );
delete_site_transient( 'site_speed_monitor_stripped_parameters' );
