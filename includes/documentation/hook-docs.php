<div id="content"><table class="wp-list-table widefat fixed striped documentation"><thead><tr><th>Hook</th><th class="type">Action Type</th><th>File(s)</th></tr></thead><tbody><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-ajax.php#L305">site_speed_monitor_site_details_end</a></td>
							<td>action</td>
							<td>class-ajax.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Action after the site details list.<p><pre>@since 1.0.0</pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-ajax.php#L158">site_speed_monitor_site_details_start</a></td>
							<td>action</td>
							<td>class-ajax.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Action before the site details list.<p><pre>@since 1.0.0</pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-dashboard_widget.php#L83">site_speed_monitor_widget_top</a></td>
							<td>action</td>
							<td>class-dashboard-widget.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Action hook to add content before the speed test widget.<p><pre>@since 1.0.0</pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-dashboard_widget.php#L166">site_speed_monitor_widget_bottom</a></td>
							<td>action</td>
							<td>class-dashboard-widget.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Action hook to add content after the speed test widget.<p><pre>@since 1.0.0</pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L416">site_speed_monitor_tools_section_content</a></td>
							<td>action</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Output custom section tab content.<p><pre>@param string The section name.</pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L389">site_speed_monitor_tools_history_bottom</a></td>
							<td>action</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Action hook to add content after the speed test widget.<p><pre>@since 1.0.0</pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-actions.php#L41">site_speed_monitor_plugin_theme_activation_delay</a></td>
							<td>filter</td>
							<td>class-actions.php, trait-helpers.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Activation delay offset, in seconds. Tweak the length of time after a plugintheme is activated that a site speed test will begin.<p><pre>@var integer</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Delay the site speed test to 5 minutes after a plugin or theme
 * is activated or switched.
 *
 * @param  integer $delay The offset time before running a site speed test.
 *
 * @return integer        The offset time before running a site speed test.
 */
add_filter( 'site_speed_monitor_plugin_theme_activation_delay', function( $delay ) {
	return 300;
} );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L40">site_speed_monitor_tools_sections</a></td>
							<td>filter</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the tool sections.<p><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Add a new section on the speed test tools page.
 *
 * @param  array $sections Tools sections.
 *
 * @return array           Array of tools page sections.
 */
add_filter( 'site_speed_monitor_tools_sections', function( $sections ) {

	$sections['custom'] = 'Custom';

	return $sections;

} );
</code></pre><p><strong>Note:</strong> This filter will also need to hook into the <code>site_speed_monitor_tools_section_content</code> action to generate the associated content.</p></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-.php#L254">site_speed_monitor_developer_mode</a></td>
							<td>filter</td>
							<td>trait-helpers.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Enable developer mode for speped check. Enabling developer mode inside of Site Speed Monitor will all of the options disabled on localhost installs, including site_details inside of tests, plugins and theme activation runs, enable diff checks on the chart and more.<p><pre>@var boolean</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Enable Site Speed Monitor developer mode.
 *
 * @return boolean True to enable developer mode, else false.
 */
add_filter( 'site_speed_monitor_developer_mode', '__return_true' );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-wpt_api.php#L132">site_speed_monitor_test_results</a></td>
							<td>filter</td>
							<td>class-wpt-api.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the data being stored after a test starts, before it's stored.<p><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * When a test runs, set the start date to sometime random in the past.
 *
 * @var array
 */
add_filter( 'site_speed_monitor_test_results', function( $data ) {

	if ( ! CPSSM\Helpers::is_developer_mode() ) {

		return $data;

	}

	$number = array_rand( range( 1, 4 ) );

	$data['start_date'] = strtotime( &quot;-{$number} day&quot; );

	return $data;

} );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L708">site_speed_monitor_chart_options</a></td>
							<td>filter</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Filter the chart options.<p><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Alter the 'Flip Chart' label to read 'Reverse Chart';
 *
 * @param  array $options The chart options array.
 *
 * @return array          The filtered chart options array.
 */
add_filter( 'site_speed_monitor_chart_options', function( $options ) {

	$options['newest_first']['label'] = 'Reverse Chart';

	return $options;

} );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L451">site_speed_monitor_chart_data_max</a></td>
							<td>filter</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the higher max completed tests length. If a user has an excessive number of tests run (say 1000+), this will only display chart data for the latest 50 test runs. Note: It's important to note, if multiple test were run on a single day, only one test will be returned. This ensure only the last test run on any single day is displayed.<p><pre>@var integer</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Expand the total number of items on the chart to the last 100 tests.
 *
 * @param  integer $count The number of items the chart should display.
 *
 * @return integer        The number of chart items.
 */
add_filter( 'site_speed_monitor_chart_data_max', function( $count ) {

	return 100;

} );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-tools.php#L111">site_speed_monitor_chart_settings</a></td>
							<td>filter</td>
							<td>class-tools.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Allow users to filter the chart settings.<p><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Change the 'First View' and 'Repeat View' colors on the chart.
 *
 * @param  array $chart_options The chart options array.
 *
 * @return array                Filtered chart options.
 */
add_filter( 'site_speed_monitor_chart_settings', function( $chart_options ) {

	$chart_options['firstView']['color']  = 'rgba(255,255,0,0.3)';
	$chart_options['repeatView']['color'] = 'rgba(255,0,255,0.3)';

	return $chart_options;

} );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-plugin.php#L34">site_speed_monitor_options</a></td>
							<td>filter</td>
							<td>class-plugin.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the Site Speed Monitor options array.<p><pre>@param array Site Speed Monitor options array.</pre><pre>@since 1.0.0</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Disable all emails with test results.
 *
 * @param  array $options Site Speed Monitor plugin options.
 *
 * @return array          Filtered options array.
 */
add_filter( 'site_speed_monitor_options', function( $sections ) {

	$options['email_results'] = false;

	return $options;

} );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-settings.php#L1088">site_speed_monitor_test_parameters</a></td>
							<td>filter</td>
							<td>class-settings.php, class-wpt-api.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Filter the speed test test parameters. These values map to the WebPageTest.org API parameters. For a full list of possible parameters, see: https:sites.google.comawebpagetest.orgdocsadvanced-featureswebpagetest-restful-apis#TOC-Parameters<p><pre>@param array speed test test parameters, from the settings page 'Parameters' tab.</pre><pre>@return array</pre><pre>@since 1.0.0</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Override the test parameters options and check &quot;http://example.org&quot; on all speed tests.
 *
 * @param  array $parameters WebPageTest.org API test parameters.
 *
 * @return array          Filtered options array.
 */
add_filter( 'site_speed_monitor_test_parameters', function( $parameters ) {

	$parameters['url'] = 'http://example.org';

	return $parameters;

} );
</code></pre><p><strong>Note:</strong> Overriding the WebPageTest.org API parameters using this filter will negate all options set on the test parameters settings tag.</p></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-settings.php#L178">site_speed_monitor_settings_tabs</a></td>
							<td>filter</td>
							<td>class-settings.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the settings page tabs.<p><pre>@param array Key value array of settings tabs.</pre><pre>@return array</pre><pre>@since 1.0.0</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Hide the 'Test Parameters' tab from non-admin users.
 *
 * @param  array $option_tabs Site Speed Monitor option tabs.
 *
 * @return array              Filtered options tab array.
 */
add_filter( 'site_speed_monitor_settings_tabs', function( $option_tabs ) {

	if ( ! current_user_can( 'manage_options' ) ) {

		unset( $option_tabs['test-parameters'] );

	}

	return $option_tabs;

} );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-actions.php#L48">site_speed_monitor_warning_max_speed</a></td>
							<td>filter</td>
							<td>class-actions.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Maximum time that a speed test can return before an admin notice is displayed.<p><pre>@var integer</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Increase the slow site warning time limit to 8 seconds.
 * Note: If a site speed test returns 7 seconds or less, the warning will not display.
 *
 * @param  integer  $max The limit (in seconds) before a warning is displayed.
 *
 * @return integer       8 seconds.
 */
add_filter( 'site_speed_monitor_warning_max_speed', function( $max ) {

	return 8;

} );
</code></pre><p><strong>Note:</strong> The Site Speed Monitor logs are standards post types with the `post_type` of `sc_log`. Each log entry can be accessed using the `WP_Query` class..</p></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-log.php#L82">site_speed_monitor_log_data</a></td>
							<td>filter</td>
							<td>class-log.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the log data.<p><pre>@param array  The log data array.</pre><pre>@param string The log type. Possible:</pre><pre><span>plugin_activated<span>   - A plugin is activated.</pre><pre><span>theme_switched<span>     - A theme is activated.</pre><pre><span>received<span>           - WebPageTest.org payload is received.</pre><pre><span>cancelled<span>          - A speed test is cancelled.</pre><pre><span>completed<span>          - A speed test transitions from pending to complete.</pre><pre><span>timeout<span>            - No response was received from WebPageTest.org</pre><pre><span>schedule_cancelled<span> - A scheduled speed test was cancelled.</pre><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Append custom data onto the test log when a speed test starts.
 *
 * @param  array  $data The data to append to the log.
 * @param  string $type The log type (Possible: )
 *
 * @return array        Log data.
 */
add_filter( 'site_speed_monitor_log_data', function( $data, $type ) {

	if ( 'completed' === $type ) {

		$data['custom'] = 'Some custom data';

	}

	return $data;

}, 10, 2 );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-dashboard_widget.php#L186">site_speed_monitor_widget_averages</a></td>
							<td>filter</td>
							<td>class-dashboard-widget.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Disable the widget average display at the top of the admin widget.<p><pre>@since 1.0.0</pre><pre>@var boolean</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Display the speed test averages from displaying on the admin widget.
 *
 * @param  boolean $enabled Whether the speed test averages is enabled or not.
 *
 * @return boolean          False to disable the averages text, else true.
 */
add_filter( 'site_speed_monitor_widget_averages', '__return_false' );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-cron.php#L39">site_speed_monitor_cron_args</a></td>
							<td>filter</td>
							<td>class-cron.php, class-settings.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Default speed test cron arguments. Note: The arguments passed here will be used to specify arguments in the WebPageTest.org API request.<p><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Email 'some_user' when the cron tasks run.
 *
 * @param  array $args WebPageTest.org API arguments when cron jobs run.
 *
 * @return array       Filtered arguments for speed test crons.
 */
add_filter( 'site_speed_monitor_cron_args', function( $args ) {

	$args['notify'] = 'some_user@gmail.com';

	return $args;

} );
</code></pre></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-ajax.php#L272">site_speed_monitor_site_details_plugin_table_titles</a></td>
							<td>filter</td>
							<td>class-ajax.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Filter the speed test site details table titles.<p><pre>@param array Table titles.</pre><pre>@since 1.0.0</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Add an additional column to the speed test table.
 *
 * @param  array $columns WebPageTest.org API test parameters.
 *
 * @return array          Final speed test table columns.
 */
add_filter( 'site_speed_monitor_site_details_plugin_table_titles', function( $columns ) {

	$columns['custom'] = 'Custom Column';

	return $columns;

} );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-actions.php#L383">site_speed_monitor_activation_test_args</a></td>
							<td>filter</td>
							<td>class-actions.php, trait-helpers.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Plugin activation cron job arguments. Note: The arguments passed here will be used to specify arguments in the WebPageTest.org API request.<p><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * When the theme is switched, run the speed test tests twice and return the test speed average.
 *
 * @param  array  $parameters WebPageTest.org API parameters.
 * @param  string $type       The activation type (possible: plugin|theme).
 *
 * @return array              WebPageTest.org API parameters.
 */
add_filter( 'site_speed_monitor_activation_test_args', function( $parameters, $type ) {

	if ( 'theme' === $type ) {

		$parameters['runs'] = 2;

	}

	return $parameters;

}, 10, 2 );
</code></pre><p><strong>Note:</strong> To see a complete list of WebPageTest.org API parameters, please see their <a href="https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters" target="_blank">documentation</a>.</p></td></tr><tr class="odd">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-actions.php#L185">site_speed_monitor_pingback_timeout</a></td>
							<td>filter</td>
							<td>class-actions.php, class-tests-table.php</td>
						</tr>
<tr class="doc-comment hidden odd"><td td colspan="3"><p class="description">Minutes the test should wait to hear a pingback before allowing the user to manually fetch the data from WebPageTest.org<p><pre>@Default 2 minutes on local host, 5 minutes on a live site</pre><pre>@var integer</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Display the 'Force Complete' link appears on the test table after 180
 * seconds (3 minutes).
 *
 * @param  integer $delay Delay (in seconds) our site will wait.
 *
 * @return integer        Delay (in seconds).
 */
add_filter( 'site_speed_monitor_pingback_timeout', function( $delay ) {
	return 180;
} );
</code></pre></td></tr><tr class="even">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest('tr').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> <a href="https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-.php#L975">site_speed_monitor_site_details</a></td>
							<td>filter</td>
							<td>trait-helpers.php</td>
						</tr>
<tr class="doc-comment hidden even"><td td colspan="3"><p class="description">Filter the site details array This allows users to display additional data on the site details view.<p><pre>@since 1.0.0</pre><pre>@var array</pre><h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">
/**
 * Append additional information onto the Site Details.
 *
 * @param  array $data Site Details data array.
 *
 * @return array       Filtered site details data array.
 */
add_filter( 'site_speed_monitor_site_details', function( $data ) {

	$data['theme']['custom'] = 'Custom Theme Data';
	$data['plugins']['custom'] = 'Custom Plugin Data';
	$data['site']['custom'] = 'Custom Site Data';

	return $data;

} );
</code></pre></td></tr></tbody></table></div></div><div id="footer">