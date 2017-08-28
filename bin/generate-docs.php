<?php
/**
 * Generate documentation for hooks in Primer
 */
class Docs_Hook_Finder {

	/**
	 * Repository link to use for hook links.
	 *
	 * @var string
	 */
	private static $repo_link = 'https://github.com/CodeParrots/wp-site-speed-monitor/blob/master/includes/class-%1$s.php#L%2$s';

	/**
	 * Current file
	 *
	 * @var string
	 */
	private static $current_file = '';

	/**
	 * Files to scan
	 *
	 * @var array
	 */
	private static $files_to_scan = array();

	/**
	 * REGEX do_action pattern
	 *
	 * @var string
	 */
	private static $pattern_custom_actions = '/do_action(.*?);/i';

	/**
	 * REGEX apply_filters pattern
	 *
	 * @var string
	 */
	private static $pattern_custom_filters = '/apply_filters(.*?);/i';

	/**
	 * Found files
	 *
	 * @var array
	 */
	private static $found_files = array();

	/**
	 * Custom hook found
	 *
	 * @var string/array
	 */
	private static $custom_hooks_found = '';

	/**
	 * Scan directory and return files
	 *
	 * @param  string  $pattern The pattern to match
	 * @param  bool    $flags   Sort flags
	 * @param  string  $path    Path to file
	 *
	 * @return array
	 *
	 * @since NEXT
	 */
	private static function get_files( $pattern, $flags = 0, $path = '' ) {

		if ( ! $path && ( $dir = dirname( $pattern ) ) != '.' ) {

			if ( '\\' == $dir || '/' == $dir ) {

				$dir = '';

			}

			return self::get_files( basename( $pattern ), $flags, $dir . '/' );

		}

		$paths = glob( $path . '*', GLOB_ONLYDIR | GLOB_NOSORT );
		$files = glob( $path . $pattern, $flags );

		if ( is_array( $paths ) ) {

			foreach ( $paths as $p ) {

				$found_files    = array();
				$retrieved_files = (array) self::get_files( $pattern, $flags, $p . '/' );

				foreach ( $retrieved_files as $file ) {

					if ( in_array( $file, self::$found_files ) ) {

						continue;

					}

					$found_files[] = $file;

				}

				self::$found_files = array_merge( self::$found_files, $found_files );

				if ( is_array( $files ) && is_array( $found_files ) ) {

					$files = array_merge( $files, $found_files );

				}

			}

		}

		return $files;

	}

	/**
	 * Get specific hook link
	 *
	 * @param  string $hook     Name of hook
	 * @param  array  $details  Hook data array
	 *
	 * @return mixed
	 *
	 * @since NEXT
	 */
	private static function get_hook_link( $hook, $details = array() ) {

		if ( ! empty( $details['class'] ) ) {

			$link = sprintf(
				self::$repo_link,
				strtolower( $details['class'] ),
				$details['line']
			);

		} elseif ( ! empty( $details['function'] ) ) {

			$link = "source-function-{$details['function']}.html#{$details['line']}";

			$link = sprintf(
				self::$repo_link,
				$details['class'],
				$details['line']
			);

		} else {

			$link = "https://github.com/CodeParrots/wp-site-speed-monitor/search?utf8=%E2%9C%93&q={$hook}";

		}

		return '<a href="' . $link . '">' . $hook . '</a>';

	}

	/**
	 * Process files, pull hooks & render table
	 *
	 * @return mixed
	 */
	public static function process_hooks() {

		self::$files_to_scan = array(
			'Main' => self::get_files( '*.php', GLOB_MARK, dirname( dirname( __FILE__ ) ) . '/' ),
		);

		$scanned = array();

		ob_start();

		echo '<div id="content">';

		foreach ( self::$files_to_scan as $heading => $files ) {

			self::$custom_hooks_found = array();

			foreach ( $files as $f ) {

				self::$current_file = basename( $f );
				$tokens             = token_get_all( file_get_contents( $f ) );
				$token_type         = false;
				$current_class      = '';
				$current_function   = '';

				if ( in_array( self::$current_file, $scanned ) ) {

					continue;

				}

				$scanned[] = self::$current_file;

				foreach ( $tokens as $index => $token ) {

					if ( is_array( $token ) ) {

						$trimmed_token_1 = trim( $token[1] );

						if ( T_CLASS == $token[0] ) {

							$token_type = 'class';

						} elseif ( T_FUNCTION == $token[0] ) {

							$token_type = 'function';

						} elseif ( 'do_action' === $token[1] ) {

							$token_type = 'action';

						} elseif ( 'apply_filters' === $token[1] ) {

							$token_type = 'filter';

						} elseif ( $token_type && ! empty( $trimmed_token_1 ) ) {

							switch ( $token_type ) {

								case 'class' :

									$current_class = $token[1];

								break;

								case 'function' :

									$current_function = $token[1];

								break;

								case 'filter' :
								case 'action' :

									$hook = trim( $token[1], "'" );
									$loop = 0;

									if ( '_' === substr( $hook, '-1', 1 ) ) {

										$hook .= '{';

										$open = true;

										// Keep adding to hook until we find a comma or colon
										while ( 1 ) {

											$loop ++;

											$next_hook  = trim( trim( is_string( $tokens[ $index + $loop ] ) ? $tokens[ $index + $loop ] : $tokens[ $index + $loop ][1], '"' ), "'" );

											if ( in_array( $next_hook, array( '.', '{', '}', '"', "'", ' ' ) ) ) {

												continue;

											}

											$hook_first = substr( $next_hook, 0, 1 );
											$hook_last  = substr( $next_hook, -1, 1 );

											if ( in_array( $next_hook, array( ',', ';' ) ) ) {

												if ( $open ) {

													$hook .= '}';

													$open = false;

												}

												break;

											}

											if ( '_' === $hook_first ) {

												$next_hook = '}' . $next_hook;

												$open = false;

											}

											if ( '_' === $hook_last ) {

												$next_hook .= '{';

												$open = true;

											}

											$hook .= $next_hook;

										}

									}

									if ( isset( self::$custom_hooks_found[ $hook ] ) ) {

										self::$custom_hooks_found[ $hook ]['file'][] = self::$current_file;

									} else {

										self::$custom_hooks_found[ $hook ] = array(
											'line'     => $token[2],
											'class'    => $current_class,
											'function' => $current_function,
											'file'     => array( self::$current_file ),
											'type'     => $token_type,
										);

									}

								break;

							}

							$token_type = false;

						}

					}

				}

			}

			foreach ( self::$custom_hooks_found as $hook => $details ) {

				if ( ! strstr( $hook, 'site_speed_monitor_' ) ) {

					unset( self::$custom_hooks_found[ $hook ] );

				}

			}

			uasort( self::$custom_hooks_found, function( $a, $b ) {

				return strcmp( $a['type'], $b['type'] );

			} );

			if ( ! empty( self::$custom_hooks_found ) ) {

				echo '<table class="wp-list-table widefat fixed striped documentation"><thead><tr><th>Hook</th><th class="type">Action Type</th><th>File(s)</th></tr></thead><tbody>';

				$y = 1;

				$examples = include_once( dirname( dirname( __FILE__ ) ) . '/includes/documentation/source/examples.php' );

				foreach ( self::$custom_hooks_found as $hook => $details ) {

					$doc_comment = self::get_doc_comment( dirname( dirname( __FILE__ ) ) . '/includes/' . $details['file'][0], $details['type'], $hook );

					printf(
						'<tr class="%1$s">
							<td><a href="#" class="toggle-docs" style="color:#333;" onclick="jQuery(this).closest(\'tr\').next().fadeToggle(); event.preventDefault();"><span class="dashicons dashicons-visibility" style="font-size: 15px; vertical-align: middle; height: 15px; width: 15px;"></span></a> %2$s</td>
							<td>%3$s</td>
							<td>%4$s</td>
						</tr>' . "\n",
						( $y % 2 == 0 ) ? 'even' : 'odd',
						self::get_hook_link( $hook, $details ),
						$details['type'],
						implode( ', ', array_unique( $details['file'] ) )
					);

					if ( ! empty( $doc_comment ) ) {

						$comment = '';

						$previous_string = false;

						$final = [];

						$previous_param = false;

						foreach ( $doc_comment as $comment_data ) {

							$not_parameter = ( false === strpos( $comment_data, '@' ) );

							if ( $not_parameter && ! $previous_param ) {

								$final[0] = empty( $final ) ? $comment_data : ( $final[0] . ' ' . $comment_data );

								continue;

							}

							$previous_param = true;

							$final[] = $comment_data;

						}

						$x = 0;

						foreach ( $final as $com ) {

							$wrap = ( 0 === $x ) ? '<p class="description">%s<p>' : '<pre>%s</pre>';

							$comment .= sprintf(
								$wrap,
								$com
							);

							$x++;

						}

						if ( isset( $examples[ $hook ] ) ) {

							$comment .= sprintf(
								'<h3>Example:</h3><pre><code class="site-speed-monitor-example language-php">%1$s</code></pre>%2$s',
								htmlspecialchars( $examples[ $hook ]['code'] ),
								( isset( $examples[ $hook ]['notes'] ) && ! empty( $examples[ $hook ]['notes'] ) ) ? sprintf(
									'<p>%s</p>',
									$examples[ $hook ]['notes']
								): ''
							);

						}

						printf(
							'<tr class="doc-comment hidden %1$s"><td td colspan="3">%2$s</td></tr>',
							( $y % 2 == 0 ) ? 'even' : 'odd',
							$comment
						);

					}

					$y++;

				}

				echo '</tbody></table></div>';

			}
		}

		echo '</div><div id="footer">';

		if ( ! is_dir( dirname( dirname( __FILE__ ) ) . '/includes/documentation/' ) ) {

			// dir doesn't exist, make it
			mkdir( dirname( dirname( __FILE__ ) ) . '/includes/documentation/' );

		}

		file_put_contents( dirname( dirname( __FILE__ ) ) . '/includes/documentation/hook-docs.php', ob_get_clean() );

		echo "\x1b[32mDocumentation successfully generated!\x1b[32m\n";

	}

	/**
	 * Process our FAQ file into a usable php file.
	 * Generates a .php file included in the plugin.
	 */
	public static function process_faq() {

		$file = dirname( dirname( __FILE__ ) ) . '/includes/documentation/source/faq.php';

		if ( ! is_readable( $file ) ) {

			return;

		}

		ob_start();

		$contents = include_once( $file );

		if ( ! is_array( $contents ) ) {

			echo "\x1b[91mError during FAQ generation.\x1b[91m\n";

			ob_get_clean();

			return;

		}

		?>

		<div class="faqs">
			<dl>

				<?php

				foreach ( $contents as $key => $faq ) {

					?>
					<div class="faq">
						<dt class="question"><srong><?php echo $faq['question']; ?></dt>
						<dd class="answer hidden">Answer: <br />
							<p><em><?php echo $faq['answer']; ?></em></p>
						</dd>
					</div>
					<?php

				}

				?>

			</dl>
		</div>

		<?php

		$faqs = ob_get_contents();

		ob_get_clean();

		file_put_contents( dirname( dirname( __FILE__ ) ) . '/includes/documentation/faq-docs.php', $faqs );

		echo "\x1b[32mFAQs successfully generated!\x1b[32m\n";

	}

	/**
	 * Generate our Terminology documentation.
	 * Note: This is scraped from https://sites.google.com/a/webpagetest.org/docs/using-webpagetest/metrics
	 *
	 * @return mixed Markup for the terminology section.
	 */
	public static function process_terminology() {

		$file = dirname( dirname( __FILE__ ) ) . '/includes/documentation/term-docs.php';

		if ( file_exists( $file ) && 0 < filesize( $file ) ) {

			echo "\x1b[32mTerminology file already exists.\x1b[32m\n";

			return;

		}

		$html = file_get_contents( 'https://sites.google.com/a/webpagetest.org/docs/using-webpagetest/metrics' );

		ob_start();

		if ( ! empty( $html ) ) {

			$metrics_markup = new DOMDocument();

			// Disable errors.
			libxml_use_internal_errors( true );

			$metrics_markup->loadHTML( $html );

			libxml_clear_errors();

			$element = $metrics_markup->getElementById( 'sites-canvas-main-content' );

			$element = self::DOMinnerHTML( $element );

			echo $element;

		}

		$terms = ob_get_contents();

		ob_get_clean();

		file_put_contents( dirname( dirname( __FILE__ ) ) . '/includes/documentation/term-docs.php', $terms );

		echo "\x1b[32mTerminology successfully generated!\x1b[32m\n";

	}

	public static function get_doc_comment( $filepath, $type, $hook ) {

		$custom = false;

		if ( 'filter' === $type ) {

			$custom = 'Custom!';

		}

		$comments = [];

		switch ( $type ) {

			default:
			case 'action':

				$action  = 'do_action';
				$pattern = "/\s(\/\*(?:[^*]|\n|(?:\*(?:[^\/]|\n)))*\*\/)\n*\s+\s{$action}\( \'{$hook}\'/";

				break;

			case 'filter':

				$action  = 'apply_filters';
				$pattern = "/(\/\*(?:[^*]|\n|(?:\*(?:[^\/]|\n)))*\*\/)\n.+{$hook}/";

				break;

		}

		preg_match( $pattern, file_get_contents( $filepath ), $comments );

		if ( empty( $comments ) ) {

			return;

		}

		$split = explode( ' * ', $comments[1] );

		if ( empty( $split ) ) {

			return;

		}

		unset( $split[0] );

		$split = array_map( function( $value ) {
			return trim( str_replace( '/', '', str_replace( '*', '', $value ) ) );
		}, $split );

		return $split;

	}

	public static function DOMinnerHTML( $element ) {

		$innerHTML = '';
		$children  = $element->childNodes;

		foreach ( $children as $child ) {

			$tmp_dom = new DOMDocument();
			$tmp_dom->appendChild( $tmp_dom->importNode( $child, true ) );
			$innerHTML .= trim( $tmp_dom->saveHTML() );

		}

		return $innerHTML;

	}

}

Docs_Hook_Finder::process_hooks();
Docs_Hook_Finder::process_faq();
Docs_Hook_Finder::process_terminology();
