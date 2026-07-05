<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Flipnzee_Auctions_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'auction',
				'plural'   => 'auctions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Define table columns.
	 *
	 * @return array
	 */
	public function get_columns() {

	return array(
		'cb'             => '<input type="checkbox" />',
		'id'             => 'ID',
		'listing_id'     => 'Listing ID',
		'start_price'    => 'Start Price',
		'reserve_price'  => 'Reserve Price',
		'buy_now_price'  => 'Buy Now Price',
		'auction_start'  => 'Auction Start',
        'auction_end'    => 'Auction End',
		'status'         => 'Status',
		'created_at'     => 'Created At',
	);
}

	/**
 * Define sortable columns.
 *
 * @return array
 */
public function get_sortable_columns() {

	return array(
		'id'            => array( 'id', true ),
		'listing_id'    => array( 'listing_id', false ),
		'start_price'   => array( 'start_price', false ),
		'reserve_price' => array( 'reserve_price', false ),
		'buy_now_price' => array( 'buy_now_price', false ),
		'status'        => array( 'status', false ),
		'created_at'    => array( 'created_at', false ),
	);
}

	/**
	 * Default column output.
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

	switch ( $column_name ) {

		case 'auction_start':

			$timestamp = strtotime( $item->auction_start );

			if (
				empty( $item->auction_start ) ||
				'0000-00-00 00:00:00' === $item->auction_start ||
				false === $timestamp
			) {
				return '—';
			}

			return wp_date(
				'd M Y H:i',
				$timestamp
			);

		case 'auction_end':

			$timestamp = strtotime( $item->auction_end );

			if (
				empty( $item->auction_end ) ||
				'0000-00-00 00:00:00' === $item->auction_end ||
				false === $timestamp
			) {
				return '—';
			}

			return wp_date(
				'd M Y H:i',
				$timestamp
			);

		case 'id':
		case 'listing_id':
		case 'start_price':
		case 'reserve_price':
		case 'buy_now_price':
		case 'status':
		case 'created_at':
			return $item->$column_name;

		default:
			return '';
	}
}
/**
 * Register bulk actions.
 *
 * @return array
 */
public function get_bulk_actions() {

	return array(
		'delete' => 'Delete',
	);
}
/**
 * Checkbox column.
 *
 * @param object $item Auction record.
 *
 * @return string
 */
public function column_cb( $item ) {

	return sprintf(
		'<input type="checkbox" name="auction_ids[]" value="%d" />',
		absint( $item->id )
	);
}

	/**
 * Display the ID column with row actions.
 *
 * @param object $item Auction record.
 *
 * @return string
 */
public function column_id( $item ) {

	$actions = array(

		

		'edit' => sprintf(
	'<a href="%s">Edit</a>',
	admin_url(
		'admin.php?page=flipnzee-edit-auction&auction_id=' . absint( $item->id )
	)
),

'delete' => sprintf(
    '<a href="%s" onclick="return confirm(\'Are you sure you want to delete this auction?\');">Delete</a>',
    wp_nonce_url(
        admin_url(
            'admin-post.php?action=flipnzee_delete_auction&auction_id=' . absint( $item->id )
        ),
        'flipnzee_delete_auction'
    )
),

	);

	return sprintf(
		'%1$s %2$s',
		esc_html( $item->id ),
		$this->row_actions( $actions )
	);
}

	/**
	 * Prepare table items.
	 */
	
public function prepare_items() {

	$per_page = 20;

	$search = isset( $_REQUEST['s'] )
	? sanitize_text_field(
		wp_unslash( $_REQUEST['s'] )
	)
	: '';
	$status = isset( $_REQUEST['status'] )
	? sanitize_text_field(
		wp_unslash( $_REQUEST['status'] )
	)
	: '';
		$orderby = isset( $_GET['orderby'] )
		? sanitize_key(
			wp_unslash( $_GET['orderby'] )
		)
		: 'created_at';

		$order = isset( $_GET['order'] )
		? strtoupper(
			sanitize_text_field(
				wp_unslash( $_GET['order'] )
			)
		)
		: 'DESC';

	$current_page = $this->get_pagenum();

	$total_items = Flipnzee_Auction_Manager::count_auctions(
	$search,
	$status
);

	$offset = ( $current_page - 1 ) * $per_page;

	$this->_column_headers = array(
	$this->get_columns(),
	array(),
	$this->get_sortable_columns(),
);

	$this->items = Flipnzee_Auction_Manager::get_all_auctions(
	$per_page,
	$offset,
	$search,
	$status,
	$orderby,
	$order
);

	$this->set_pagination_args(
		array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		)
	);
}
}