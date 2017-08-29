<?php
/*
 * Plugin Name: Site Speed Monitor
 * Plugin URI:  https://wordpress.org/plugins/site-speed-monitor/
 * Description: Site Speed Monitor allows you to monitor your website load times using the WebPageTest.org API.
 * Author:      Code Parrots
 * Author URI:  https://www.codeparrots.com
 * Version:     1.0.0
 * Text Domain: site-speed-monitor
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

/**
 * Site Speed Monitor lets you test and view your website speed using https://www.webpagetest.org/
 *
 * LICENSE
 * This file is part of Site Speed Monitor.
 *
 * Site Speed Monitor is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package    Site Speed Monitor
 * @author     Code Parrots <info@codeparrots.com> & Evan Herman <evan.m.herman@gmail.com>
 * @copyright  Copyright 2013-2017 Code Parrots, 2013-2017 Evan Herman
 * @license    http://www.gnu.org/licenses/gpl.txt GPL 2.0
 * @link       https://wordpress.org/plugins/site-speed-monitor/
 * @since      1.0.0
 */


if ( ! class_exists( 'Site_Speed_Monitor' ) ) {

	final class Site_Speed_Monitor {

		/**
		 * Plugin version
		 *
		 * @var string
		 *
		 * @since 1.0.0
		 */
		public static $version = '1.0.0';

		/**
		 * Minimum PHP version
		 *
		 * @var string
		 */
		private $php_min_version = '5.6';

		/**
		 * Plugin assets URL
		 *
		 * @var string
		 */
		public static $assets_url;

		/**
		 * Plugin file
		 *
		 * @var string
		 */
		public static $plugin_file;

		/**
		 * Class constructor
		 *
		 * @param string $cur_php_version
		 *
		 * @since 1.0.0
		 */
		public function __construct( $php_version = PHP_VERSION ) {

			add_action( 'plugins_loaded', [ $this, 'i18n' ] );

			if ( version_compare( $php_version, $this->php_min_version, '<' ) ) {

				add_action( 'shutdown', array( $this, 'notice' ) );

				return;

			}

			static::$assets_url = plugin_dir_url( __FILE__ ) . 'assets';

			static::$plugin_file = plugin_basename( __FILE__ );

			require_once __DIR__ . '/includes/autoload.php';

		}

		/**
		 * Run using the 'init' action.
		 */
		public function i18n() {

			load_plugin_textdomain(
				'site-speed-monitor',
				false,
				dirname( self::$plugin_file ) . '/languages'
			);

		}

		/**
		 * Display minimum PHP version notice.
		 *
		 * @action shutdown
		 *
		 * @since 1.0.0
		 */
		public function notice() {

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					/* translators: Minimum PHP version for speed test to run. */
					esc_html__( 'Site Speed Monitor requires PHP version %s or higher. Please deactivate the plugin and contact your system administrator to upgrade.', 'contact-widgets' ),
					esc_html( $this->php_min_version )
				)
			);

		}

	}

	new Site_Speed_Monitor;

} // End if().
