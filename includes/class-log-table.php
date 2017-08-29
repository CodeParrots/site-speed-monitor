<?php

namespace CPSSM;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}

final class Log_Table extends \WP_List_Table {

	use Helpers;

	private $data = [];

	private $per_page;

	function __construct() {

		$this->per_page = 10;

		parent::__construct( [
			'singular' => 'Site Speed Monitor Log',
			'plural'   => 'Site Speed Monitor Log',
			'ajax'     => false,
		] );

		$this->data = $this->get_table_data();

	}

	public function get_table_data() {

		$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

		$args = [
			'post_type'      => 'sc_log',
			'posts_per_page' => $this->per_page,
			'paged'          => $paged ? $paged : 1,
		];

		$log_query = new \WP_Query( $args );

		return $log_query;

	}

	function no_items() {

		esc_html_e( 'Nothing has been logged, yet.', 'site-speed-monitor' );

	}

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'type':

				return sprintf(
					'%1$s %2$s',
					esc_html( $item->post_title ),
					$this->row_actions( [] )
				);

			case 'time':

				return sprintf(
					'%1$s',
					esc_html( date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $item->post_date ) ) )
				);

			default:

				return print_r( $item, true );

		}

	}

	/**
	 * Generates the table navigation above or bellow the table and removes the
	 * _wp_http_referrer and _wpnonce because it generates a error about URL too large
	 *
	 * @param string $which
	 * @return void
	 */
	function display_tablenav( $which ) {

		if ( 'top' === $which ) {

			printf(
				'<div class="tablenav %1$s%2$s">%3$s</div>',
				esc_attr( $which ),
				( 0 >= $this->data->found_posts ) ? ' no-logs' : ' logs',
				$this->clear_log_button()
			);

			return;

		}

		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
			?>

			<br class="clear" />

		</div>

		<?php

	}

	function clear_log_button() {

		if ( ! $this->data->found_posts || 0 >= $this->data->found_posts ) {

			return;

		}

		return sprintf(
			'<a href="#" data-nonce="%1$s" class="button button-secondary clear-site-speed-monitor-log">%2$s</a>',
			wp_create_nonce( 'clear-site-speed-monitor-log' ),
			esc_html__( 'Clear Logs', 'site-speed-monitor' )
		);

	}

	function get_sortable_columns() {

		$sortable_columns = [
			// 'start_date' => [ 'start_date', false ],
		];

		return $sortable_columns;

	}

	function get_columns() {

		$columns = [
			'type' => esc_html__( 'Message', 'site-speed-monitor' ),
			'time' => esc_html__( 'Date', 'site-speed-monitor' ),
		];

		return $columns;

	}

	function prepare_items() {

		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		$columns  = $this->get_columns();

		$hidden   = [];

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$current_page = $this->get_pagenum();

		$this->set_pagination_args( [
			'total_items' => (int) $this->data->found_posts,
			'per_page'    => $this->per_page,
		] );

		$this->items = (array) $this->data->posts;

	}

}
