<?php
/**
 * Watchlist AJAX Handler.
 *
 * Handles AJAX requests for the watchlist.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Watchlist AJAX class.
 */class Flipnzee_Watchlist_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->init();

	}

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	private function init() {

		add_action(
			'wp_ajax_flipnzee_add_to_watchlist',
			array( $this, 'add_to_watchlist' )
		);

		add_action(
	         'wp_ajax_nopriv_flipnzee_add_to_watchlist',
	          array( $this, 'add_to_watchlist' )
    );

		add_action(
			'wp_ajax_flipnzee_remove_from_watchlist',
			array( $this, 'remove_from_watchlist' )
		);

        add_action(
    'wp_ajax_nopriv_flipnzee_remove_from_watchlist',
    array( $this, 'remove_from_watchlist' )
);

	}

	/**
	 * AJAX: Add auction to watchlist.
	 *
	 * @return void
	 */
	/**
 * AJAX: Add auction to watchlist.
 *
 * @return void
 */
public function add_to_watchlist() {

	// Verify nonce.
	check_ajax_referer(
		'flipnzee_watchlist_nonce',
		'nonce'
	);

	// User must be logged in.
	if ( ! is_user_logged_in() ) {

		wp_send_json_error(
			array(
				'message' => __( 'Please log in first.', 'flipnzee-auctions' ),
			)
		);

	}

	$user_id = get_current_user_id();

	$auction_id = isset( $_POST['auction_id'] )
		? absint( wp_unslash( $_POST['auction_id'] ) )
		: 0;

	if ( ! $auction_id ) {

		wp_send_json_error(
			array(
				'message' => __( 'Invalid auction.', 'flipnzee-auctions' ),
			)
		);

	}

	$result = Flipnzee_Watchlist_Manager::add_to_watchlist(
		$auction_id,
		$user_id
	);

	if ( $result ) {

		wp_send_json_success(
			array(
				'message' => __( 'Added to watchlist.', 'flipnzee-auctions' ),
			)
		);

	}

	wp_send_json_error(
		array(
			'message' => __( 'Unable to add to watchlist.', 'flipnzee-auctions' ),
		)
	);

}

	/**
	 * AJAX: Remove auction from watchlist.
	 *
	 * @return void
	 */
	/**
 * AJAX: Remove auction from watchlist.
 *
 * @return void
 */
public function remove_from_watchlist() {

	// Verify nonce.
	check_ajax_referer(
		'flipnzee_watchlist_nonce',
		'nonce'
	);

	// User must be logged in.
	if ( ! is_user_logged_in() ) {

		wp_send_json_error(
			array(
				'message' => __( 'Please log in first.', 'flipnzee-auctions' ),
			)
		);

	}

	$user_id = get_current_user_id();

	$auction_id = isset( $_POST['auction_id'] )
		? absint( wp_unslash( $_POST['auction_id'] ) )
		: 0;

	if ( ! $auction_id ) {

		wp_send_json_error(
			array(
				'message' => __( 'Invalid auction.', 'flipnzee-auctions' ),
			)
		);

	}

	$result = Flipnzee_Watchlist_Manager::remove_from_watchlist(
		$auction_id,
		$user_id
	);

	if ( $result ) {

		wp_send_json_success(
			array(
				'message' => __( 'Removed from watchlist.', 'flipnzee-auctions' ),
			)
		);

	}

	wp_send_json_error(
		array(
			'message' => __( 'Unable to remove from watchlist.', 'flipnzee-auctions' ),
		)
	);

}

}