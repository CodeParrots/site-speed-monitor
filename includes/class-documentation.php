<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Documentation {

	private $sections;

	private $active_section;

	public function __construct() {

		$this->sections = [
			'faq'     => [
				'title' => __( 'FAQ', 'site-speed-monitor' ),
				'file'  => __DIR__ . '/documentation/faq-docs.php',
			],
			'terms'   => [
				'title' => __( 'Terminology', 'site-speed-monitor' ),
				'file'  => __DIR__ . '/documentation/term-docs.php',
			],
			'actions' => [
				'title' => __( 'Action and Filters', 'site-speed-monitor' ),
				'file'  => __DIR__ . '/documentation/hook-docs.php',
			],
		];

		$section_query_string = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
		$this->active_section = $section_query_string ? ( array_key_exists( $section_query_string, $this->sections ) ? $section_query_string : key( $this->sections ) ) : key( $this->sections );

		$this->render_documentation();

	}

	/**
	 * Generate the documentation markup.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the documentation.
	 */
	public function render_documentation() {

		?>

		<div class="documentation-wrap">

			<div class="inside flex">

				<?php

				$this->render_sidemenu();

				$this->render();

				?>

			</div>

		</div>

		<?php

	}

	/**
	 * Render the submenu sidebar.
	 *
	 * @param  string $section The current section slug.
	 * @param  array  $data    The section data array.
	 *
	 * @return mixed Markup for the documentation.
	 */
	public function render_sidemenu() {

		?>

		<div class="documentation-submenu">

			<ul>

				<?php

				foreach ( $this->sections as $section_key => $data ) {

					printf(
						'<li class="link%1$s">
							<a class="section" data-section="%2$s" href="">%3$s</a>
						</li>',
						( $section_key === $this->active_section ) ? ' active' : '',
						esc_attr( $section_key ),
						esc_attr( $data['title'] )
					);

				}

				?>

			</ul>

		</div>

		<?php

	}

	/**
	 * Render the section content.
	 *
	 * @param  string $section The current section slug.
	 * @param  array  $data    The section data array.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Markup for the documentation.
	 */
	private function render() {

		foreach ( $this->sections as $section_key => $data ) {

			$hidden = ( $section_key === $this->active_section ) ? '' : 'hidden';

			?>

			<div class="postbox doc-subsection <?php echo esc_attr( $section_key . ' ' . $hidden ); ?>">

				<div class="inside">

					<?php

					printf( '<h2 class="section-title">%s</h2>', esc_html( $data['title'] ) );

					if ( isset( $data['file'] ) && is_readable( $data['file'] ) ) {

						include_once( $data['file'] );

					}

					?>

				</div>

			</div>

			<?php

		}

	}

}
