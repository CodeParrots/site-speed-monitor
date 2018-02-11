=== Site Speed Monitor ===
Contributors: codeparrots, eherman24
Tags: website, speed, test, webpagetest, report, monitor, tester, check, chart, graph
Requires at least: 4.0
Requires PHP: 5.6
Tested up to: 4.9
Stable tag: 1.0.0

Site Speed Monitor allows you to monitor your website load times automatically while tracking it's performance.

== Description ==

> <strong>Note:</strong>

> Site Speed Monitor requires PHP 5.6 or later. Please ensure that you are running version 5.6 or later before installing Site Speed Monitor.

Using Site Speed Monitor, users can manually check their website load times using the WebPageTest.org API. View load times and additional information about your site which can be used to improve load times across your website.

Bring your website load times down, improve search rankings and please your site visitors!

Special thanks to <a href="https://icons8.com">Icons8</a> for the use of their icons within Site Speed Monitor.

> <strong>Features</strong>

> - Run speed tests manually.
> - Automatic speed tests run weekly, bi-weekly, daily, monthly and more!
> - View speed test results and grades.
> - View speed test results in an easy to read graph.
> - Diff checker to help narrow down what caused the change in results between two tests.
> - Site details tracked.
> - Receive email reports with speed test results.

== Installation ==

1. Download the plugin .zip file, and keep in mind where on your computer you saved it.
2. Log in to your website (ie: yourdomain.com/wp-admin), and head into <strong>Plugins > Add New</strong>
3. On the following screen, click the 'Upload Plugin' link at the top of the page.
4. Browse your computer to the location you downloaded the .zip file in step one, select it and click the 'Install Now' button.
4. After the plugin has successfully installed, click "Activate Plugin" and enjoy!
6. Before you can run speed tests and monitor your site, you need to retrieve a <a href="https://www.webpagetest.org/getkey.php" target="_blank">WebPageTest.org</a> API key. Head over to <a href="https://www.webpagetest.org/getkey.php" target="_blank">https://www.webpagetest.org/getkey.php</a> and fill out the form.
7. Once you receive your WebPageTest.org API key, you can enter that on the settings page and begin using the plugin.


== Frequently Asked Questions ==

= Will this plugin monitor my website load times? =

Yes! This plugin utilizes the WebPageTest.org API to run speed tests against your site. This plugin will display the results of the speed tests, including the first view and repeat view load times as well as the respective grades for Time to First Byte, Keep Alive, GZip Compression, Compress Images, Cache Static and CDN values.

= Will this plugin give me advice on how to speed up my site? =

No. While this plugin provides a means of communication between WebPageTest.org and your website, there are no recommendations provided directly in the plugin. However, you can use the results of the speed tests to make alterations to your site to improve load times. These can be minor things such as adding cache or no-expiry headers, to compressing/minifying javascript and CSS files and deferring them to the footer.

Site Speed Monitor was developed to provide you with solid data that can be used to make changes to your site.

= Can I change the WebPageTest.org API parameters? =

Yes! On the Site Speed Monitor settings page, you will want to head into the 'Test Parameters' tab. From here, you can add any of the available <a href="https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis#TOC-Parameters" target="_blank">test parameters</a> that you see fit.

Additionally, there are a number of actions and filters built into Site Speed Monitor to allow you to customize things as you need. For a full list, please see the "Actions & Filters" sub-section on the "Documentation" tab on the settings page.

= Are any WP-CLI commands available? =

Yes, we have setup a number of WP-CLI commands to make your life easier.

Run a speed check on the current site.

`$ wp site-speed-monitor`

Run a speed check on the current site and send the results to an email address.

`$ wp site-speed-monitor --notify=info@codeparrots.com`

Run a speed check test on a separate URL and send the results to an email address.

`$ wp site-speed-monitor --site_url=https://www.codeparrots.com --notify=info@codeparrots.com`

== Screenshots ==

1. Site Speed Monitor - Settings Page
2. Site Speed Monitor - Admin Dashboard Widget (test in progress)
3. Site Speed Monitor - Admin Dashboard Widget (test complete)
4. Site Speed Monitor - Past Website Performance Graph
5. Site Speed Monitor - Test Results - Site Details Modal
6. Site Speed Monitor - Test History Table & Grade Results
7. Site Speed Monitor - Test Results - Admin Bar Notice

== Changelog ==

= 1.0.0 - August, 2017 =

== Upgrade Notice ==

= 1.0.0 - August, 2017 =
