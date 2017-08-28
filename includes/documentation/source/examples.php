<?php
/**
 * Examples for our documentation.
 *
 * @since 1.0.0
 *
 * @var array
 */
return [
	'site_speed_monitor_plugin_theme_activation_delay' => [
		'code'  => '
/**
 * Delay the site speed test to 5 minutes after a plugin or theme
 * is activated or switched.
 *
 * @param  integer $delay The offset time before running a site speed test.
 *
 * @return integer        The offset time before running a site speed test.
 */
add_filter( \'site_speed_monitor_plugin_theme_activation_delay\', function( $delay ) {
	return 300;
} );
',
	],
	'site_speed_monitor_pingback_timeout' => [
		'code'  => '
/**
 * Display the \'Force Complete\' link appears on the test table after 180
 * seconds (3 minutes).
 *
 * @param  integer $delay Delay (in seconds) our site will wait.
 *
 * @return integer        Delay (in seconds).
 */
add_filter( \'site_speed_monitor_pingback_timeout\', function( $delay ) {
	return 180;
} );
',
],
	'site_speed_monitor_developer_mode' => [
		'code'  => '
/**
 * Enable Site Speed Monitor developer mode.
 *
 * @return boolean True to enable developer mode, else false.
 */
add_filter( \'site_speed_monitor_developer_mode\', \'__return_true\' );
',
	],
	'site_speed_monitor_test_results' => [
		'code'  => '
/**
 * When a test runs, set the start date to sometime random in the past.
 *
 * @var array
 */
add_filter( \'site_speed_monitor_test_results\', function( $data ) {

	if ( ! CPSSM\Helpers::is_developer_mode() ) {

		return $data;

	}

	$number = array_rand( range( 1, 4 ) );

	$data[\'start_date\'] = strtotime( "-{$number} day" );

	return $data;

} );
',
	],
	'site_speed_monitor_chart_options' => [
		'code'  => '
/**
 * Alter the \'Flip Chart\' label to read \'Reverse Chart\';
 *
 * @param  array $options The chart options array.
 *
 * @return array          The filtered chart options array.
 */
add_filter( \'site_speed_monitor_chart_options\', function( $options ) {

	$options[\'newest_first\'][\'label\'] = \'Reverse Chart\';

	return $options;

} );
',
	],
	'site_speed_monitor_chart_data_max' => [
		'code'  => '
/**
 * Expand the total number of items on the chart to the last 100 tests.
 *
 * @param  integer $count The number of items the chart should display.
 *
 * @return integer        The number of chart items.
 */
add_filter( \'site_speed_monitor_chart_data_max\', function( $count ) {

	return 100;

} );
',
	],
	'site_speed_monitor_chart_settings' => [
		'code'  => '
/**
 * Change the \'First View\' and \'Repeat View\' colors on the chart.
 *
 * @param  array $chart_options The chart options array.
 *
 * @return array                Filtered chart options.
 */
add_filter( \'site_speed_monitor_chart_settings\', function( $chart_options ) {

	$chart_options[\'firstView\'][\'color\']  = \'rgba(255,255,0,0.3)\';
	$chart_options[\'repeatView\'][\'color\'] = \'rgba(255,0,255,0.3)\';

	return $chart_options;

} );
',
	],
	'site_speed_monitor_tools_sections' => [
		'code'  => '
/**
 * Add a new section on the speed test tools page.
 *
 * @param  array $sections Tools sections.
 *
 * @return array           Array of tools page sections.
 */
add_filter( \'site_speed_monitor_tools_sections\', function( $sections ) {

	$sections[\'custom\'] = \'Custom\';

	return $sections;

} );
',
'notes' => sprintf(
	/* translators: 1. 'Note' wrapped in strong tags. 2. The message. */
	'<strong>%1$s:</strong> %2$s',
	'Note',
	sprintf(
		/* translators: The associated filter. */
		'This filter will also need to hook into the %s action to generate the associated content.',
		'<code>site_speed_monitor_tools_section_content</code>'
	)
),
	],
	'site_speed_monitor_options' => [
		'code'  => '
/**
 * Disable all emails with test results.
 *
 * @param  array $options Site Speed Monitor plugin options.
 *
 * @return array          Filtered options array.
 */
add_filter( \'site_speed_monitor_options\', function( $sections ) {

	$options[\'email_results\'] = false;

	return $options;

} );
',
	],
	'site_speed_monitor_test_parameters' => [
		'code'  => '
/**
 * Override the test parameters options and check "http://example.org" on all speed tests.
 *
 * @param  array $parameters WebPageTest.org API test parameters.
 *
 * @return array          Filtered options array.
 */
add_filter( \'site_speed_monitor_test_parameters\', function( $parameters ) {

	$parameters[\'url\'] = \'http://example.org\';

	return $parameters;

} );
',
'notes' => sprintf(
	'%1$s %2$s.',
	sprintf(
		'<strong>%s</strong>',
		'Note:'
	),
	'Overriding the WebPageTest.org API parameters using this filter will negate all options set on the test parameters settings tag'
),
	],
	'site_speed_monitor_settings_tabs' => [
		'code'  => '
/**
 * Hide the \'Test Parameters\' tab from non-admin users.
 *
 * @param  array $option_tabs Site Speed Monitor option tabs.
 *
 * @return array              Filtered options tab array.
 */
add_filter( \'site_speed_monitor_settings_tabs\', function( $option_tabs ) {

	if ( ! current_user_can( \'manage_options\' ) ) {

		unset( $option_tabs[\'test-parameters\'] );

	}

	return $option_tabs;

} );
',
	],
	'site_speed_monitor_activation_test_args' => [
		'code'  => '
/**
 * When the theme is switched, run the speed test tests twice and return the test speed average.
 *
 * @param  array  $parameters WebPageTest.org API parameters.
 * @param  string $type       The activation type (possible: plugin|theme).
 *
 * @return array              WebPageTest.org API parameters.
 */
add_filter( \'site_speed_monitor_activation_test_args\', function( $parameters, $type ) {

	if ( \'theme\' === $type ) {

		$parameters[\'runs\'] = 2;

	}

	return $parameters;

}, 10, 2 );
',
'notes' => sprintf(
	'%1$s %2$s.',
	sprintf(
		'<strong>%s</strong>',
		'Note:'
	),
	'To see a complete list of WebPageTest.org API parameters, please see their <a href="https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters" target="_blank">documentation</a>'
),
	],
	'site_speed_monitor_log_data' => [
		'code'  => '
/**
 * Append custom data onto the test log when a speed test starts.
 *
 * @param  array  $data The data to append to the log.
 * @param  string $type The log type (Possible: )
 *
 * @return array        Log data.
 */
add_filter( \'site_speed_monitor_log_data\', function( $data, $type ) {

	if ( \'completed\' === $type ) {

		$data[\'custom\'] = \'Some custom data\';

	}

	return $data;

}, 10, 2 );
',
],
	'site_speed_monitor_warning_max_speed' => [
		'code'  => '
/**
 * Increase the slow site warning time limit to 8 seconds.
 * Note: If a site speed test returns 7 seconds or less, the warning will not display.
 *
 * @param  integer  $max The limit (in seconds) before a warning is displayed.
 *
 * @return integer       8 seconds.
 */
add_filter( \'site_speed_monitor_warning_max_speed\', function( $max ) {

	return 8;

} );
',
'notes' => sprintf(
	'%1$s %2$s.',
	sprintf(
		'<strong>%s</strong>',
		'Note:'
	),
	'The Site Speed Monitor logs are standards post types with the `post_type` of `sc_log`. Each log entry can be accessed using the `WP_Query` class.'
),
	],
	'site_speed_monitor_warning_max_speed' => [
		'code'  => '
/**
 * Increase the slow site warning time limit to 8 seconds.
 * Note: If a site speed test returns 7 seconds or less, the warning will not display.
 *
 * @param  integer  $max The limit (in seconds) before a warning is displayed.
 *
 * @return integer       8 seconds.
 */
add_filter( \'site_speed_monitor_warning_max_speed\', function( $max ) {

	return 8;

} );
',
'notes' => sprintf(
	'%1$s %2$s.',
	sprintf(
		'<strong>%s</strong>',
		'Note:'
	),
	'The Site Speed Monitor logs are standards post types with the `post_type` of `sc_log`. Each log entry can be accessed using the `WP_Query` class.'
),
	],
	'site_speed_monitor_widget_averages' => [
		'code'  => '
/**
 * Display the speed test averages from displaying on the admin widget.
 *
 * @param  boolean $enabled Whether the speed test averages is enabled or not.
 *
 * @return boolean          False to disable the averages text, else true.
 */
add_filter( \'site_speed_monitor_widget_averages\', \'__return_false\' );
',
	],
	'site_speed_monitor_cron_args' => [
		'code'  => '
/**
 * Email \'some_user\' when the cron tasks run.
 *
 * @param  array $args WebPageTest.org API arguments when cron jobs run.
 *
 * @return array       Filtered arguments for speed test crons.
 */
add_filter( \'site_speed_monitor_cron_args\', function( $args ) {

	$args[\'notify\'] = \'some_user@gmail.com\';

	return $args;

} );
',
	],
	'site_speed_monitor_site_details_plugin_table_titles' => [
		'code'  => '
/**
 * Add an additional column to the speed test table.
 *
 * @param  array $columns WebPageTest.org API test parameters.
 *
 * @return array          Final speed test table columns.
 */
add_filter( \'site_speed_monitor_site_details_plugin_table_titles\', function( $columns ) {

	$columns[\'custom\'] = \'Custom Column\';

	return $columns;

} );
',
	],
	'site_speed_monitor_site_details' => [
		'code'  => '
/**
 * Append additional information onto the Site Details.
 *
 * @param  array $data Site Details data array.
 *
 * @return array       Filtered site details data array.
 */
add_filter( \'site_speed_monitor_site_details\', function( $data ) {

	$data[\'theme\'][\'custom\'] = \'Custom Theme Data\';
	$data[\'plugins\'][\'custom\'] = \'Custom Plugin Data\';
	$data[\'site\'][\'custom\'] = \'Custom Site Data\';

	return $data;

} );
',
	],
];
