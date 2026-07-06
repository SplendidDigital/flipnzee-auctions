<?php
/**
 * Transaction Manager.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Transaction_Manager {
    	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'flipnzee_auction_winner_determined',
			array( $this, 'create_transaction_from_auction' ),
			10,
			2
		);
	}


    	
/**
 * Create transaction when an auction winner is determined.
 *
 * @param int    $auction_id Auction ID.
 * @param object $winner     Winning bid.
 * @return void
 */
public function create_transaction_from_auction(
	$auction_id,
	$winner
) {

	global $wpdb;
    if ( class_exists( 'Flipnzee_Activity_Log' ) ) {

	
}

	$auction = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}flipnzee_auctions
			WHERE id = %d",
			$auction_id
		)
	);

	if ( ! $auction ) {
		return;
	}

	$listing_author = (int) get_post_field(
		'post_author',
		$auction->listing_id
	);

	$transaction_id = self::create_transaction(
		array(
			'auction_id'  => $auction->id,
			'listing_id'  => $auction->listing_id,
			'seller_id'   => $listing_author,
			'buyer_id'    => $winner->bidder_id,
			'winning_bid' => $winner->bid_amount,
		)
	);

	
if ( $transaction_id ) {

	Flipnzee_Activity_Log::log(
		'transaction_created',
		$auction->id,
		$winner->bidder_id,
		'Transaction ID: ' . $transaction_id
	);
}
}
	public static function create_transaction( $data ) {

		global $wpdb;

		$table = $wpdb->prefix . 'flipnzee_transactions';

		$result = $wpdb->insert(
			$table,
			array(
				'auction_id'  => $data['auction_id'],
				'listing_id'  => $data['listing_id'],
				'seller_id'   => $data['seller_id'],
				'buyer_id'    => $data['buyer_id'],
				'winning_bid' => $data['winning_bid'],
				'status'      => 'pending',
			),
			array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%f',
				'%s',
			)
		);

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get a transaction.
	 *
	 * @param int $transaction_id Transaction ID.
	 * @return object|null
	 */
	public static function get_transaction( $transaction_id ) {

		global $wpdb;

		$table = $wpdb->prefix . 'flipnzee_transactions';

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$transaction_id
			)
		);
	} 
	/**
	 * Update transaction status.
	 *
	 * @param int    $transaction_id Transaction ID.
	 * @param string $status         New status.
	 * @return bool
	 */
	public static function update_status(
		$transaction_id,
		$status
	) {

		global $wpdb;

		$table = $wpdb->prefix . 'flipnzee_transactions';

		$result = $wpdb->update(
			$table,
			array(
				'status' => sanitize_text_field( $status ),
			),
			array(
				'id' => absint( $transaction_id ),
			),
			array(
				'%s',
			),
			array(
				'%d',
			)
		);

		return false !== $result;
	}
    /**
 * Update a transaction status.
 *
 * @param int    $transaction_id Transaction ID.
 * @param string $status         New status.
 * @return bool
 */
/**
 * Handle transaction status updates.
 *
 * @return void
 */
public static function handle_status_update() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Permission denied.' );
	}

	check_admin_referer(
		'flipnzee_update_transaction'
	);

	$transaction_id = isset( $_GET['transaction_id'] )
		? absint( $_GET['transaction_id'] )
		: 0;

	$status = isset( $_GET['status'] )
		? sanitize_text_field( wp_unslash( $_GET['status'] ) )
		: '';

	if ( $transaction_id && $status ) {

		self::update_status(
			$transaction_id,
			$status
		);
	}

	wp_safe_redirect(
		admin_url(
			'admin.php?page=flipnzee-transactions'
		)
	);

	exit;
}

}