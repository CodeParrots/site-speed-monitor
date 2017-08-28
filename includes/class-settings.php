<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Settings {

	use Helpers;

	private $tab;

	private $browsers;

	public function __construct() {

		$this->tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$this->tab = $this->tab ? $this->tab : 'general-settings';

		add_action( 'admin_menu', [ $this, 'settings_page' ] );

		add_action( 'admin_init', [ $this, 'register_settings' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_scripts' ] );

	}

	public function admin_notice() {

		$stripped_parameters = Helpers::option( 'stripped_parameters', [] );

		if ( empty( $stripped_parameters ) ) {

			return;

		}

		Admin_Notices::generate_notice( 'error', sprintf(
			/* translators: 1. Comma separated list of test parameters that were stripped. */
			__( 'You attempted to save test parameters that are reserved by Site Speed Monitor. The following test parameters were not saved: %1$s', 'site-speed-monitor' ),
			'<code>' . implode( ', ', $stripped_parameters ) . '</code>'
		) );

	}

	/**
	 * Register Site Speed Monitor settings page inside of 'Settings > Site Speed Monitor'
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {

		add_options_page(
			esc_html__( 'Site Speed Monitor - Settings', 'site-speed-monitor' ),
			esc_html__( 'Site Speed Monitor', 'site-speed-monitor' ),
			'manage_options',
			'site-speed-monitor',
			[ $this, 'settings' ]
		);

	}

	/**
	 * Enqueue the settings scripts & styles.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $hook The admin page basename.
	 */
	public function enqueue_settings_scripts( $hook ) {

		if ( 'settings_page_site-speed-monitor' !== $hook ) {

			return;

		}

		$rtl    = ! is_rtl() ? '' : '-rtl';
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'site-speed-monitor-settings', \Site_Speed_Monitor::$assets_url . "/css/settings{$rtl}{$suffix}.css", array(), \Site_Speed_Monitor::$version, 'all' );
		wp_enqueue_script( 'site-speed-monitor-settings', \Site_Speed_Monitor::$assets_url . "/js/settings{$suffix}.js", array( 'jquery' ), \Site_Speed_Monitor::$version, true );

		wp_enqueue_style( 'prism.js', \Site_Speed_Monitor::$assets_url . "/css/prism{$suffix}.css", array(), \Site_Speed_Monitor::$version, 'all' );
		wp_enqueue_script( 'prism.js', \Site_Speed_Monitor::$assets_url . "/js/prism{$suffix}.js", array( 'jquery' ), \Site_Speed_Monitor::$version, true );

		wp_localize_script( 'site-speed-monitor-settings', 'speedCheckSettings', [
			'removeLogConfirmation' => esc_html__( 'Are you sure you want to remove the Site Speed Monitor logs?', 'site-speed-monitor' ),
			'NoLogsMessage'         => esc_html__( 'Nothing has been logged, yet.', 'site-speed-monitor' ),
		] );

	}

	/**
	 * Settings page markup
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the settings page
	 */
	public function settings() {

		$header_parrot = [
			'url' => sprintf(
				\Site_Speed_Monitor::$assets_url . '/images/%sheader-parrot.png',
				Helpers::is_developer_mode() ? 'developer-' : ''
			),
			'alt' => Helpers::is_developer_mode() ? __( 'Developer Mode', 'site-speed-monitor' ) : __( 'Code Parrots Mascot', 'site-speed-monitor' ),
		];

		?>

		<div class="wrap">

			<div class="header">

				<!-- Speedometer icon by Icons8 -->
				<div class="icon site-speed-monitor-icon"></div>

				<!-- Site Speed Monitor text logo -->
				<div class="logo site-speed-monitor-text"><span class="version">v<?php echo esc_html( \Site_Speed_Monitor::$version ); ?></span></div>

				<!-- Parrot Mascot -->
				<div class="parrot-mascot"><img src="<?php echo esc_url( $header_parrot['url'] ); ?>" class="parrot" alt="<?php echo esc_attr( $header_parrot['alt'] ); ?>" title="<?php echo esc_attr( $header_parrot['alt'] ); ?>" /></div>

			</div>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-1">

					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">

							<?php $this->settings_tabs(); ?>

							<?php $this->settings_section(); ?>

						</div>
					</div>

					<!-- Sidebar Here -->

				</div>

				<br class="clear">

			</div>

		</div>

		<?php

	}

	/**
	 * Settings tabs markup.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the settings tabs.
	 */
	public function settings_tabs() {

		/**
		 * Filter the settings page tabs.
		 *
		 * @param array Key value array of settings tabs.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		$tabs = (array) apply_filters( 'site_speed_monitor_settings_tabs', [
			'general-settings' => __( 'General', 'site-speed-monitor' ),
			'test-parameters'  => __( 'Test Parameters', 'site-speed-monitor' ),
			'logging'          => __( 'Logging', 'site-speed-monitor' ),
			'documentation'    => __( 'Documentation', 'site-speed-monitor' ),
		] );

		if ( Helpers::is_developer_mode() ) {

			$tabs['developer_mode'] = __( 'Developer Mode', 'site-speed-monitor' );

		}

		$tab_markup = '';

		if ( empty( $tabs ) ) {

			return;

		}

		foreach ( $tabs as $slug => $text ) {

			$tab_markup .= sprintf(
				'<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
				esc_url( add_query_arg( 'tab', $slug, admin_url( 'options-general.php?page=site-speed-monitor' ) ) ),
				( ( $this->tab === $slug ) ? 'nav-tab-active' : '' ) . ( ( 'developer_mode' === $slug ) ? 'developer-mode' : '' ),
				esc_html( $text )
			);

		}

		printf(
			'<h2 class="nav-tab-wrapper">
				%1$s
			</h2>',
			wp_kses_post( $tab_markup )
		);

	}

	/**
	 * Generate the tab content.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Tab content markup.
	 */
	public function settings_section() {

		if ( 'documentation' === $this->tab ) {

			new Documentation;

			return;

		}

		print( '<div class="postbox ' . $this->tab . '"><div class="inside"><div class="inside-wrap">' );

		?>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'cpsc_options' );
				do_settings_sections( "site-speed-monitor-{$this->tab}" );
				submit_button( esc_html__( 'Save Changes', 'site-speed-monitor' ) );
			?>
			<input type="hidden" name="wpsc_action" value="update_site_speed_monitor_settings" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $this->tab ); ?>" />
		</form>

		<?php

		print( '</div></div></div>' );

	}

	/**
	 * Register the plugin settings
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {

		$user = wp_get_current_user();

		register_setting(
			'cpsc_options',
			'site_speed_monitor_options',
			[ $this, 'sanitize_options' ]
		);

		add_settings_section(
			'general-settings',
			null,
			false,
			'site-speed-monitor-general-settings'
		);

		$additional_help = '';

		if ( empty( Helpers::option( 'api_key', '' ) ) ) {

			$additional_help = '</p>' . sprintf(
				'<p class="description">%1$s</p>',
				sprintf(
					/* translators: 1. Link to the FAQ settings section. */
					esc_html__( 'For help retreiving your WebPageTest.org API key, please see our FAQ for "%1$s".', 'site-speed-monitor' ),
					'<a href="#">' . __( 'How do I get a WebPageTest.org API key?', 'site-speed-monitor' ) . '</a>'
				)
			);

		}

		add_settings_field(
			'api_key',
			esc_html__( 'WebPageTest.org API Key', 'site-speed-monitor' ),
			[ $this, 'text_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'api_key',
				'description' => sprintf(
					/* translators: Link to WebPageTest.org. */
					esc_html__( 'Enter your %1$s API key to get started.', 'site-speed-monitor' ),
					'<a href="https://www.webpagetest.org/getkey.php" target="_blank">WebPageTest.org</a>'
				) . $additional_help,
			]
		);

		add_settings_field(
			'email_results',
			esc_html__( 'Email Test Results', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'email_results',
				'description' => sprintf(
					/* translators: The user email wrapped in <code> tags. */
					esc_html__( 'Email the results to %s when the test is marked complete?', 'site-speed-monitor' ),
					'<code>' . sanitize_email( $user->user_email ) . '</code>'
				),
				'default'     => true,
				'test_key'    => 'notify',
			]
		);

		add_settings_field(
			'private_tests',
			esc_html__( 'Private Tests', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'private_tests',
				'description' => esc_html__( 'When checked, the test results will be hidden from the WebPageTest.org test log.', 'site-speed-monitor' ),
				'default'     => true,
				'test_key'    => 'private',
			]
		);

		add_settings_field(
			'test_location',
			esc_html__( 'Test Location', 'site-speed-monitor' ),
			[ $this, 'location_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'test_location',
				'description' => esc_html__( 'Select the location to run your site speed tests from.', 'site-speed-monitor' ),
				'default'     => false,
			]
		);

		if ( ! empty( Helpers::option( 'api_key', '' ) ) ) {

			add_settings_field(
				'test_browser',
				esc_html__( 'Test Browser', 'site-speed-monitor' ),
				[ $this, 'browser_callback' ],
				'site-speed-monitor-general-settings',
				'general-settings',
				[
					'id'          => 'test_browser',
					'description' => esc_html__( 'Select the browser to test your site on.', 'site-speed-monitor' ),
					'default'     => 'Chrome',
				]
			);

			add_settings_field(
				'test_speed',
				esc_html__( 'Test Speed', 'site-speed-monitor' ),
				[ $this, 'speed_callback' ],
				'site-speed-monitor-general-settings',
				'general-settings',
				[
					'id'          => 'test_speed',
					'description' => esc_html__( 'Select the speed you would like to run the site speed test at.', 'site-speed-monitor' ),
					'default'     => 'Cable',
				]
			);

		}

		add_settings_field(
			'cron_tests',
			esc_html__( 'Regular Site Speed Tests', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'cron_tests',
				'description' => esc_html__( 'Run site speed tests on a regular basis.', 'site-speed-monitor' ),
				'default'     => 1,
			]
		);

		add_settings_field(
			'cron_frequency',
			esc_html__( 'Test Run Frequency', 'site-speed-monitor' ),
			[ $this, 'select_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'cron_frequency',
				'description' => esc_html__( 'Select how often the site speed tests should run.', 'site-speed-monitor' ),
				'default'     => 'weekly',
				'options'     => [
					'thirtydays'  => __( 'Every 30 Days', 'site-speed-monitor' ),
					'fifteendays' => __( 'Every 15 Days', 'site-speed-monitor' ),
					'twiceweekly' => __( 'Twice Per week', 'site-speed-monitor' ),
					'weekly'      => __( 'Weekly', 'site-speed-monitor' ),
					'twicedaily'  => __( 'Twice Per Day', 'site-speed-monitor' ),
					'daily'       => __( 'Daily', 'site-speed-monitor' ),
				],
			]
		);

		$local_install      = Helpers::is_local( false );
		$admin_bar_message  = '';

		if ( $local_install ) {

			ob_start();

			Helpers::site_speed_monitor_notice( 'warning', __( 'The "Plugin/Theme Activation Checks" option is disabled on local installs. This setting will be enabled on live sites that are accessible to the WebPageTest.org API.', 'site-speed-monitor' ) );

			$notice = ob_get_contents();

			ob_get_clean();

			$admin_bar_message  = '</p>' . $notice;

		}

		add_settings_field(
			'activation_checks',
			esc_html__( 'Plugin/Theme Activation Checks', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'activation_checks',
				'default'     => ! $local_install,
				'disabled'    => $local_install,
				'description' => sprintf(
					/* translators: Additional description details wrapped in <code> tags. */
					esc_html__( 'Run site speed tests after you switch themes or activate plugins. %s', 'site-speed-monitor' ),
					/* translators: The offset time for when test start. */
					'<br />' . sprintf(
						'Speed tests will start %s after the theme is switched or a plugin is activated.',
						'<code>' . esc_html( Helpers::get_activation_offset_time() ) . '</code>'
					)
				) . wp_kses_post( $admin_bar_message ),
			]
		);

		add_settings_field(
			'admin_bar_notice',
			esc_html__( 'Admin Bar Notice', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-general-settings',
			'general-settings',
			[
				'id'          => 'admin_bar_notice',
				'default'     => 1,
				'description' => esc_html__( 'Display the latest site speed test load time in the admin bar, along with a grade badge for the load time.', 'site-speed-monitor' ),
			]
		);

		add_settings_section(
			'test-parameters',
			null,
			false,
			'site-speed-monitor-test-parameters'
		);

		add_settings_field(
			'test_parameters',
			esc_html__( 'Test Parameters', 'site-speed-monitor' ),
			[ $this, 'parameters_callback' ],
			'site-speed-monitor-test-parameters',
			'test-parameters',
			[
				'id'          => 'test_parameters',
				'description' => sprintf(
					/* translators: 1. Link to WebPageTest.org 2. Link to WebPageTest.org parameter documentation. */
					__( 'Tweak the %1$s test parameters using the fields below. For a full list of test parameters, see the test %2$s.', 'site-speed-monitor' ),
					'<a href="http://WebPageTest.org" target="_blank" title="WebPageTest.org">WebPageTest.org</a>',
					sprintf(
						'<a href="https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters" target="_blank" title="%1$s">%2$s</a>',
						esc_html__( 'WebPageTest.org API Documentation', 'site-speed-monitor' ),
						esc_html__( 'documentation', 'site-speed-monitor' )
					)
				),
			]
		);

		add_settings_section(
			'logging',
			null,
			false,
			'site-speed-monitor-logging'
		);

		add_settings_field(
			'logging',
			esc_html__( 'Enable Logging', 'site-speed-monitor' ),
			[ $this, 'checkbox_callback' ],
			'site-speed-monitor-logging',
			'logging',
			[
				'id'          => 'logging',
				'description' => esc_html__( 'Enable logging for all Site Speed Monitor activity.', 'site-speed-monitor' ),
				'default'     => false,
				'after'       => [ $this, 'log_container' ], // Callback function.
			]
		);

	}

	/**
	 * Sanitize the options
	 *
	 * @param  array $input The settings to save.
	 *
	 * @since  1.0.0
	 *
	 * @return array        The final array of options.
	 */
	public function sanitize_options( $input ) {

		$action = filter_input( INPUT_POST, 'wpsc_action', FILTER_SANITIZE_STRING );

		if ( 'update_site_speed_monitor_settings' !== $action ) {

			return $input;

		}

		$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cpsc_options-options' ) ) {

			wp_die( esc_html__( 'The security check did not pass. Please refresh the page and try again.', 'site-speed-monitor' ) );

		}

		$tab = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );

		$whitelist = [
			// General Settings.
			'api_key'           => 'string',
			'email_results'     => 'boolean',
			'private_tests'     => 'boolean',
			'test_location'     => 'string',
			'test_browser'      => 'string',
			'test_speed'        => 'string',
			'cron_tests'        => 'boolean',
			'cron_frequency'    => 'string',
			'activation_checks' => 'boolean',
			'admin_bar_notice'  => 'boolean',
			// Test Parameter Settings.
			'test_parameters'   => 'array',
			// Debug Settings.
			'debug'             => 'boolean',
			'logging'           => 'boolean',
			'test_run_count'    => 'integer',
		];

		$options     = Plugin::$options;
		$new_options = filter_input( INPUT_POST, 'site_speed_monitor_options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		// Clear the cron if the cron frequency was updated.
		if ( isset( $options['cron_frequency'] ) && $options['cron_frequency'] !== $new_options['cron_frequency'] ) {

			wp_clear_scheduled_hook( 'speed_test_run', (array) apply_filters( 'site_speed_monitor_cron_args', [
				'notify' => get_option( 'admin_email' ),
			] ) );

		}

		// Checkbox save fix
		switch ( $tab ) {

			case 'general-settings':

				$new_options['email_results']     = isset( $new_options['email_results'] ) ? true : false;
				$new_options['private_tests']     = isset( $new_options['private_tests'] ) ? true : false;
				$new_options['activation_checks'] = isset( $new_options['activation_checks'] ) ? true : false;
				$new_options['admin_bar_notice']  = isset( $new_options['admin_bar_notice'] ) ? true : false;
				$new_options['cron_tests']        = isset( $new_options['cron_tests'] ) ? true : false;

				break;

			case 'logging':

				$new_options['logging'] = isset( $new_options['logging'] ) ? true : false;

				break;

		}

		foreach ( $options as $name => $value ) {

			if ( ! array_key_exists( $name, $whitelist ) || ! isset( $new_options[ $name ] ) ) {

				continue;

			}

			switch ( $whitelist[ $name ] ) {

				case 'string':

					$value = (string) isset( $input[ $name ] ) ? sanitize_text_field( $input[ $name ] ) : '';

					break;

				case 'boolean':

					$value = (bool) isset( $input[ $name ] ) && ! empty( $input[ $name ] ) ? true : false;

					break;

				case 'integer':

					$value = (int) isset( $input[ $name ] ) ? intval( $input[ $name ] ) : 1;

					break;

				case 'array':

					$value = (array) isset( $input[ $name ] ) ? $input[ $name ] : [];

					// strip empty values in test parameters
					if ( 'test_parameters' !== $name ) {

						break;

					}

					$value = $this->sanitize_test_parameters( $value );

					break;

				default:

					$value = $input[ $name ];

					break;

			} // End switch().

			$options[ $name ] = $value;

		} // End foreach().

		if ( Plugin::$options != $options ) {

			/**
			 * @todo Setup debug/logging data
			 */
			Log::entry( Log::log_type( 'settings_updated' ) );

		}

		return $options;

	}

	/**
	 * Text input callback
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the text input field.
	 */
	public function text_callback( $args ) {

		printf(
			'<input id="%1$s" name="site_speed_monitor_options[%1$s]" placeholder="%2$s" type="%3$s" value="%4$s" class="widefat" />%5$s',
			esc_attr( $args['id'] ),
			esc_attr__( 'Enter your API key.', 'site-speed-monitor' ),
			empty( Helpers::option( 'api_key', '' ) ) ? 'text' : 'password',
			esc_attr( Helpers::option( 'api_key', '' ) ),
			isset( $args['description'] ) ? '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' : ''
		);

	}

	/**
	 * Checkbox callback
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the checkbox field.
	 */
	public function checkbox_callback( $args ) {

		$test_key = isset( $args['test_key'] ) ? $args['test_key'] : '';

		$disabled_by_test_key = array_key_exists( $test_key, self::get_test_parameters() );
		$disabled_class       = ( $disabled_by_test_key || ( isset( $args['disabled'] ) && $args['disabled'] ) ) ? 'option-disabled' : '';

		$disabled_by_arg = ( isset( $args['disabled'] ) && $args['disabled'] );

		printf(
			'<input id="%1$s" name="site_speed_monitor_options[%1$s]" type="checkbox" value="1" %2$s class="%3$s" %4$s />%5$s %6$s',
			esc_attr( $args['id'] ),
			checked( true, ( Helpers::option( $args['id'], $args['default'] ) || $disabled_by_test_key ), false ),
			esc_attr( $disabled_class ),
			( $disabled_by_test_key || $disabled_by_arg ) ? 'disabled="disabled"' : '',
			isset( $args['description'] ) ? '<p class="description ' . $disabled_class . '">' . wp_kses_post( $args['description'] ) . '</p>' : '',
			$disabled_by_test_key ? Helpers::site_speed_monitor_notice( 'info', sprintf(
				/* translators: 1. Testing. 2. Testing. */
				__( '<span class="dashicons dashicons-editor-help"></span> This option is being overridden in the test parameters setting. Remove %1$s from the test parameters to re-enable this option.', 'site-speed-monitor' ),
				'<code>' . esc_html( $test_key ) . '</code>'
			), false ) : ''
		);

		if ( isset( $args['after'] ) && is_callable( $args['after'] ) ) {

			call_user_func_array( $args['after'], $args );

		}

	}

	/**
	 * Generate the markup for the log container.
	 *
	 * @since  1.0.0
	 *
	 * @return [type]       [description]
	 */
	public function log_container() {

		if ( ! Helpers::logging() ) {

			return;

		}

		$table = new Log_Table();

		$table->prepare_items();

		$table->display();

	}

	/**
	 * Display a warning about invalid test parameters.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the invalid parameters notice.
	 */
	public function invalid_parameter_warning() {

		$parameters = Helpers::get_test_parameters();

		if ( empty( $parameters ) ) {

			return;

		}

		$checks = [
			'url'    => [
				'filter'  => FILTER_VALIDATE_URL,
				/* translators: 1. Test parameter key (eg: url) */
				'message' => __( 'Invalid URL. The %1$s test parameter requires a valid website URL to test.', 'site-speed-monitor' ),
			],
			'notify' => [
				'filter'  => FILTER_VALIDATE_EMAIL,
				/* translators: 1. Test parameter key (eg: notify) */
				'message' => __( 'Invalid Email. The %1$s test parameter requires a valid email address (eg: <code>email@example.com</code>) to notify when tests are complete.', 'site-speed-monitor' ),
			],
		];

		foreach ( $checks as $parameter => $data ) {

			if ( ! isset( $parameters[ $parameter ] ) || filter_var( $parameters[ $parameter ], $data['filter'] ) ) {

				unset( $checks[ $parameter ] );

			}

			continue;

		}

		$message = '';

		foreach ( $checks as $parameter => $data ) {

			$message .= sprintf(
				'<p>%1$s</p>',
				wp_kses_post( sprintf(
					$data['message'],
					'<code>' . $parameter . '</code>'
				) )
			);

		}

		if ( empty( $message ) ) {

			return;

		}

		Helpers::site_speed_monitor_notice( 'error', $message );

	}

	/**
	 * Test location dropdown callback.
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the test location dropdown fields.
	 */
	public function location_callback( $args ) {

		if ( ! Helpers::option( 'api_key', '' ) ) {

			return printf(
				'<em class="location-invalid-api">%s</em>',
				esc_html__( 'Enter a valid API key to select your testing location.', 'site-speed-monitor' )
			);

		}

		$response = WPT_API::get_locations();

		if ( isset( $response['error'] ) && $response['error'] ) {

			return printf(
				'<p class="description">%s</p>',
				sprintf(
					/* translators: 1. API response error text. */
					wp_kses_post( 'Error: %1$s', 'site-speed-monitor' ),
					wp_kses_post( $response['response'] )
				)
			);

		}

		$browsers = [];
		$selected = Helpers::option( $args['id'], $args['default'] );

		printf(
			'<select class="widefat" name="site_speed_monitor_options[%1$s]">',
			esc_attr( $args['id'] )
		);

		foreach ( $response as $group_name => $group_data ) {

			printf(
				'<optgroup label="%1$s">',
				$group_name
			);

			foreach ( $group_data as $location_name => $data ) {

				$browsers[ $data['location'] ] = $data['Browsers'];

				$label = $data['labelShort'] . ' (' . str_replace( ',', ', ', $data['Browsers'] ) . ')';

				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_html( $data['location'] ),
					selected( $selected, $data['location'], false ),
					esc_html( $label )
				);

			}

			print( '</optgroup>' );

		}

		print( '</select>' );

		$this->browsers = $browsers;

	}

	/**
	 * Render the browsers select field.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $args Field arguments for the 'test_browser' field.
	 *
	 * @return mixed        Markup for the browsers select field(s).
	 */
	public function browser_callback( $args ) {

		if ( empty( $this->browsers ) ) {

			return;

		}

		$browsers = array_map( function( $value ) {

			$array = explode( ',', $value );

			$array = array_map( function( $browser ) {

				$split = explode( '-', $browser );

				if ( 2 <= count( $split ) ) {

					// Return the second half. eg: Chrome from: Moto G - Chrome
					return $split[1];

				}

				return $browser;

			}, $array );

			return $array;

		}, $this->browsers );

		$current_location = Helpers::option( 'test_location' );
		$selected         = Helpers::option( $args['id'], $args['default'] );

		printf(
			'<select class="widefat" name="site_speed_monitor_options[%1$s]">',
			esc_attr( $args['id'] )
		);

		foreach ( $browsers as $location => $browser_arr ) {

			$hidden = ( $current_location !== $location ) ? 'hidden' : '';

			foreach ( $browser_arr as $browser ) {

				printf(
					'<option class="%1$s %2$s" value="%3$s" %4$s>%5$s</option>',
					esc_html( $location . '-browser browser-option' ),
					esc_attr( $hidden ),
					trim( esc_html( $browser ) ),
					selected( $selected, $browser, false ),
					esc_html( $browser )
				);

			} // @codingStandardsIgnoreLine

		}

		print( '</select>' );

	}

	/**
	 * Render the test speed field.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $args Field arguments for the 'test_speed' field.
	 *
	 * @return mixed        Markup for the test speed field.
	 */
	public function speed_callback( $args ) {

		$speeds = [
			'FIOS'   => __( '20 Mbps down, 5 Mbps up, 4 ms first-hop RTT, 0% packet loss (not all locations will get the full bandwidth).', 'site-speed-monitor' ),
			'Cable'  => __( '5 Mbps down, 1 Mbps up, 28ms first-hop RTT, 0% packet loss.', 'site-speed-monitor' ),
			'DSL'    => __( '1.5 Mbps down, 384 Kbps up, 50 ms first-hop RTT, 0% packet loss.', 'site-speed-monitor' ),
			'3GFast' => __( '1.6 Mbps down, 768 Kbps up, 150 ms first-hop RTT, 0% packet loss.', 'site-speed-monitor' ),
			'3G'     => __( '1.6 Mbps down, 768 Kbps up, 300 ms first-hop RTT, 0% packet loss.', 'site-speed-monitor' ),
			'Dial'   => __( '49 Kbps down, 30 Kbps up, 120 ms first-hop RTT, 0% packet loss.', 'site-speed-monitor' ),
			'Native' => __( 'No synthetic traffic shaping applied.', 'site-speed-monitor' ),
		];

		$selected = Helpers::option( $args['id'], $args['default'] );

		foreach ( $speeds as $value => $label ) {

			printf(
				'<label style="margin: 3px 0;">
					<input type="radio" name="site_speed_monitor_options[%1$s]" value="%2$s" %3$s />
					%4$s
				</label><br />',
				esc_attr( $args['id'] ),
				esc_attr( $value ),
				checked( $selected, $value, false ),
				sprintf(
					/* translators: 1. Speed, wrapped in strong tabs eg. Cable. 2. Speed description. */
					'%1$s - %2$s',
					sprintf(
						'<strong>%1$s</strong>',
						esc_html( $value )
					),
					esc_html( $label )
				)
			);

		}

	}

	/**
	 * Checkbox callback
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the checkbox field.
	 */
	public function select_callback( $args ) {

		if ( ! isset( $args['options'] ) || empty( $args['options'] ) ) {

			print( 'Error: You forgot to specify options for this field.' );

			return;

		}

		printf(
			'<select name="site_speed_monitor_options[%1$s]" class="widefat">',
			esc_attr( $args['id'] )
		);

		foreach ( $args['options'] as $option_value => $option_text ) {

			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option_value ),
				selected( Helpers::option( $args['id'], $args['default'] ), $option_value ),
				esc_html( $option_text )
			);

		}

		print( '</select>' );

		if ( isset( $args['description'] ) ) {

			printf(
				'<p class="description">%s</p>',
				wp_kses_post( $args['description'] )
			);

		}

		if ( isset( $args['after'] ) && is_callable( $args['after'] ) ) {

			call_user_func_array( $args['after'], $args );

		}

	}

	/**
	 * Test Parameter callback
	 *
	 * @since  1.0.0
	 *
	 * @return mixed Markup for the repeating parameter field.
	 */
	public function parameters_callback( $args ) {

		printf(
			'<p class="description">%1$s</p><br />',
			wp_kses_post( $args['description'] )
		);

		/**
		 * Filter the speed test test parameters.
		 *
		 * These values map to the WebPageTest.org API parameters.
		 * For a full list of possible parameters, see: https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters
		 *
		 * @param array speed test test parameters, from the settings page 'Parameters' tab.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		$parameters = (array) apply_filters( 'site_speed_monitor_test_parameters', Helpers::option( 'test_parameters', [] ) );

		// Blank field for new parameters
		$parameters['keys'][]   = '';
		$parameters['values'][] = '';

		$iteration  = 0;
		$length     = (int) isset( $parameters['keys'] ) ? count( $parameters['keys'] ) : 0;

		$this->invalid_parameter_warning();

		print( '<div class="parameter-list">' );

		while ( $iteration < $length ) {

			printf(
				'<section class="param">
					<input placeholder="key" name="site_speed_monitor_options[%1$s][keys][%2$s]" type="text" value="%3$s" class="test-param" />
					<input placeholder="value" name="site_speed_monitor_options[%1$s][values][%2$s]" type="text" value="%4$s" class="test-param" />
					%5$s
				</section>',
				esc_attr( $args['id'] ),
				esc_attr( $iteration ),
				esc_attr( $parameters['keys'][ $iteration ] ),
				esc_attr( $parameters['values'][ $iteration ] ),
				wp_kses_post( ( ( $iteration + 1 ) === $length ) ? sprintf(
					'<a href="#" class="button button-primary add-parameter" title="%1$s">+</a>',
					esc_attr( 'Add Additional Test Parameter', 'site-speed-monitor' )
				) : '' )
			);

			$iteration++;

		}

		print( '</div>' );

	}

	/**
	 * Strip blacklisted and empty values from our test_parameters options array.
	 *
	 * @param  array $value test_parameters array.
	 *
	 * @since  1.0.0
	 *
	 * @return array        test_parameters array clean of blacklisted options and any empty values.
	 */
	public function sanitize_test_parameters( $value ) {

		/**
		 * Blacklisted API parameters that are reserved by this plugin.
		 *
		 * @var   array
		 *
		 * @since 1.0.0
		 */
		$blacklist = array_intersect( $value['keys'], [ 'f', 'location', 'k' ] );

		if ( ! empty( $blacklist ) ) {

			$stripped_parameters = [];

			foreach ( $blacklist as $k => $v ) {

				$stripped_parameters[] = $v;

				unset( $value['keys'][ $k ], $value['values'][ $k ] );

			} // @codingStandardsIgnoreLine

			set_site_transient( 'site_speed_monitor_stripped_parameters', $stripped_parameters, 1 );

		}

		foreach ( $value as $key => $value_array ) {

			if ( empty( $value_array ) ) {

				continue;

			}

			$opposite_array = ( 'keys' === $key ) ? 'values' : 'keys';

			foreach ( $value_array as $k => $v ) {

				if ( ! empty( $v ) ) {

					if ( 'url' === $v ) {

						$value[ $opposite_array ][ $k ] = esc_url( $value[ $opposite_array ][ $k ] );

					}

					continue;

				}

				unset( $value[ $key ][ $k ] );

				if ( ! isset( $value[ $opposite_array ][ $k ] ) ) {

					continue;

				}

				unset( $value[ $opposite_array ][ $k ] );

			} // @codingStandardsIgnoreLine

		} // End foreach().

		return array_map( 'array_values', $value );

	}

}
