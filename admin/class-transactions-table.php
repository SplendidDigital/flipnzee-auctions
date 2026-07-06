<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Flipnzee_Transactions_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'transaction',
				'plural'   => 'transactions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Table columns.
	 */
	public function get_columns() {

		return array(
			'id'          => 'ID',
			'auction_id'  => 'Auction',
			'listing_id'  => 'Listing',
			'seller_id'   => 'Seller',
			'buyer_id'    => 'Buyer',
			'winning_bid' => 'Winning Bid',
			'status'      => 'Status',
			'created_at'  => 'Created',
		);
	}

	/**
	 * Default column output.
	 */
	public function column_default( $item, $column_name ) {

		return $item[ $column_name ];
	}
    /**
 * Display the transaction ID with a View action.
 *
 * @param array $item Transaction row.
 * @return string
 */
public function column_id( $item ) {

	$url = admin_url(
		'admin.php?page=flipnzee-transaction-details'
		. '&transaction_id=' . $item['id']
	);

	$actions = array(
		'view' => sprintf(
			'<a href="%s">View</a>',
			esc_url( $url )
		),
	);

	return sprintf(
		'%1$s %2$s',
		$item['id'],
		$this->row_actions( $actions )
	);
}
    /**
 * Display the Status column with action links.
 *
 * @param array $item Transaction row.
 * @return string
 */
public function column_status( $item ) {

	$status = esc_html( ucfirst( $item['status'] ) );

	$actions = array();

	if ( 'pending' === $item['status'] ) {

		$url = wp_nonce_url(
			admin_url(
				'admin-post.php?action=flipnzee_update_transaction_status'
				. '&transaction_id=' . $item['id']
				. '&status=paid'
			),
			'flipnzee_update_transaction'
		);

		$actions['paid'] =
			'<a href="' . esc_url( $url ) . '">Mark Paid</a>';

	} elseif ( 'paid' === $item['status'] ) {

		$url = wp_nonce_url(
			admin_url(
				'admin-post.php?action=flipnzee_update_transaction_status'
				. '&transaction_id=' . $item['id']
				. '&status=completed'
			),
			'flipnzee_update_transaction'
		);

		$actions['completed'] =
			'<a href="' . esc_url( $url ) . '">Mark Completed</a>';
	}

	return sprintf(
		'%1$s %2$s',
		$status,
		$this->row_actions( $actions )
	);
}

	/**
	 * Prepare table items.
	 */
	public function prepare_items() {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_transactions';

	$columns  = $this->get_columns();
	$hidden   = array();
	$sortable = array();

	$this->_column_headers = array(
		$columns,
		$hidden,
		$sortable,
		'id',
	);

	$this->items = $wpdb->get_results(
		"SELECT * FROM {$table} ORDER BY id DESC",
		ARRAY_A
	);
}

/**
 * Table CSS classes.
 */
protected function get_table_classes() {

	return array(
		'widefat',
		'fixed',
		'striped',
		'table-view-list',
		'transactions',
	);
}
}
