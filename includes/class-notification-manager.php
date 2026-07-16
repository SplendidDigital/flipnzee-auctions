<?php
/**
 * Notification Manager
 *
 * Handles auction notifications.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Notification_Manager {
    

	/**
	 * Notify the auction winner.
	 *
	 * @param int    $auction_id Auction ID.
	 * @param object $winner     Winner object.
	 *
	 * @return void
	 */
    /**
 * Register notification hooks.
 *
 * @return void
 */
public static function init() {

	add_action(
		'flipnzee_auction_winner_determined',
		array(
			__CLASS__,
			'notify_winner',
		),
		10,
		2
	);

	add_action(
		'flipnzee_auction_winner_determined',
		array(
			__CLASS__,
			'notify_seller',
		),
		10,
		2
	);

	add_action(
		'flipnzee_auction_winner_determined',
		array(
			__CLASS__,
			'notify_admin',
		),
		10,
		2
	);
}
	public static function notify_winner(
		$auction_id,
		$winner
	) {

		Flipnzee_Activity_Log::log(
			'winner_notification',
			$auction_id,
			$winner->bidder_id,
			sprintf(
				'Winner notification prepared. Winning bid: %s',
				$winner->bid_amount
			)
		);

		error_log(
			'FLIPNZEE: Winner notification logged.'
		);
	}

    /**
 * Notify the seller.
 *
 * @param int    $auction_id Auction ID.
 * @param object $winner Winner object.
 *
 * @return void
 */
public static function notify_seller(
	$auction_id,
	$winner
) {

	Flipnzee_Activity_Log::log(
		'seller_notification',
		$auction_id,
		0,
		sprintf(
			'Seller notification prepared. Winner ID: %d',
			$winner->bidder_id
		)
	);

	error_log(
		'FLIPNZEE: Seller notification logged.'
	);
}

/**
 * Notify the administrator.
 *
 * @param int    $auction_id Auction ID.
 * @param object $winner Winner object.
 *
 * @return void
 */
public static function notify_admin(
	$auction_id,
	$winner
) {

	Flipnzee_Activity_Log::log(
		'admin_notification',
		$auction_id,
		0,
		sprintf(
			'Administrator notification prepared. Winning bid: %s',
			$winner->bid_amount
		)
	);

	error_log(
		'FLIPNZEE: Admin notification logged.'
	);
}
}

