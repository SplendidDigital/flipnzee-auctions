<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Bid_Manager {

	/**
	 * Place a bid.
	 *
	 * @param int   $auction_id Auction ID.
	 * @param int   $bidder_id  User ID.
	 * @param float $bid_amount Bid amount.
	 *
	 * @return bool
	 */
	public static function place_bid(
	$auction_id,
	$bidder_id,
	$bid_amount
) {

	global $wpdb;

	$bid_table = $wpdb->prefix . 'flipnzee_bids';
	$auction_table = $wpdb->prefix . 'flipnzee_auctions';

	/*
 * Do not allow bidding on expired auctions.
 */

$auction = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT auction_end
		FROM {$auction_table}
		WHERE id = %d",
		$auction_id
	)
);

if ( $auction ) {

	$end_time = strtotime(
		$auction->auction_end
	);

	if ( current_time( 'timestamp' ) >= $end_time ) {

		return false;

	}
}

	$highest_bidder = self::get_highest_bidder(
    $auction_id
);

	/*
	 * Get current highest bid.
	 */
	$current_bid = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT current_bid
			FROM {$auction_table}
			WHERE id = %d",
			$auction_id
		)
	);

	if (
    $highest_bidder &&
    (int) $highest_bidder->bidder_id === (int) $bidder_id
) {
    return new WP_Error(
        'already_highest_bidder',
        __(
            'You are already the highest bidder.',
            'flipnzee-auctions'
        )
    );
}
	/*
 * Minimum bid increment.
 */
$minimum_increment = 10;

$minimum_allowed_bid =
    (float) $current_bid +
    $minimum_increment;

	/*
 * Validate minimum bid.
 */
if ( $bid_amount < $minimum_allowed_bid ) {
    return false;
}

	/*
	 * Save bid.
	 */
	$result = $wpdb->insert(
		$bid_table,
		array(
			'auction_id' => $auction_id,
			'bidder_id'  => $bidder_id,
			'bid_amount' => $bid_amount,
		),
		array(
			'%d',
			'%d',
			'%f',
		)
	);

	if ( false === $result ) {
		return false;
	}

	/*
	 * Update current bid.
	 */
	$wpdb->update(

		$auction_table,
		array(
			'current_bid' => $bid_amount,
		),
		array(
			'id' => $auction_id,
		),
		array(
			'%f',
		),
		array(
			'%d',
		)
	);
$wpdb->update(
	$auction_table,
	array(
		'current_bid' => $bid_amount,
	),
	array(
		'id' => $auction_id,
	),
	array(
		'%f',
	),
	array(
		'%d',
	)
);

/*
 * Anti-sniping:
 * Extend auction by 5 minutes if
 * less than 60 seconds remain.
 */
$auction = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT auction_end
		FROM {$auction_table}
		WHERE id = %d",
		$auction_id
	)
);

if ( $auction ) {

	$end_time = strtotime( $auction->auction_end );

	$current_time = current_time( 'timestamp' );

	$seconds_left = $end_time - $current_time;

	if ( $seconds_left <= 60 ) {

		$new_end = date(
			'Y-m-d H:i:s',
			$end_time + ( 5 * 60 )
		);

		$wpdb->update(
			$auction_table,
			array(
				'auction_end' => $new_end,
			),
			array(
				'id' => $auction_id,
			),
			array(
				'%s',
			),
			array(
				'%d',
			)
		);
	}
}

return true;
	return true;
}
/**
 * Get all bids for an auction.
 *
 * @param int $auction_id Auction ID.
 *
 * @return array
 */
public static function get_bids( $auction_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_bids';

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE auction_id = %d
			ORDER BY bid_amount DESC, created_at DESC",
			$auction_id
		),
		ARRAY_A
	);
}
/**
 * Get highest bidder.
 *
 * @param int $auction_id Auction ID.
 *
 * @return object|null
 */
public static function get_highest_bidder( $auction_id ) {

	global $wpdb;

	$bid_table = $wpdb->prefix . 'flipnzee_bids';
	$users     = $wpdb->users;

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				b.bid_amount,
				b.bidder_id,
				u.display_name
			FROM {$bid_table} b
			LEFT JOIN {$users} u
				ON b.bidder_id = u.ID
			WHERE b.auction_id = %d
			ORDER BY b.bid_amount DESC
			LIMIT 1",
			$auction_id
		)
	);
}

/**
 * Get the winning bid.
 *
 * @param int $auction_id Auction ID.
 * @return object|null
 */
public static function get_winning_bid( $auction_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_bids';
	$users = $wpdb->users;

	return $wpdb->get_row(
		$wpdb->prepare(
			"
			SELECT
				b.bid_amount,
				b.bidder_id,
				u.display_name
			FROM {$table} b
			LEFT JOIN {$users} u
				ON b.bidder_id = u.ID
			WHERE b.auction_id = %d
			ORDER BY b.bid_amount DESC
			LIMIT 1
			",
			$auction_id
		)
	);
}
/**
 * Determine the winning bidder for an auction.
 *
 * @param int $auction_id Auction ID.
 * @return bool
 */
public static function determine_winner( $auction_id ) {

	global $wpdb;

	$bid_table = $wpdb->prefix . 'flipnzee_bids';
	$auction_table = $wpdb->prefix . 'flipnzee_auctions';

	$winner = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT bidder_id, bid_amount
			FROM {$bid_table}
			WHERE auction_id = %d
			ORDER BY bid_amount DESC, created_at ASC
			LIMIT 1",
			$auction_id
		)
	);

	if ( ! $winner ) {
		return false;
	}

	$wpdb->update(
		$auction_table,
		array(
			'winner_user_id' => $winner->bidder_id,
			'current_bid'    => $winner->bid_amount,
		),
		array(
			'id' => $auction_id,
		),
		array(
			'%d',
			'%f',
		),
		array(
			'%d',
		)
	);

	if ( class_exists( 'Flipnzee_Activity_Log' ) ) {

		Flipnzee_Activity_Log::log(
			'winner_determined',
			$auction_id,
			$winner->bidder_id,
			sprintf(
				'Winning bid: %s',
				$winner->bid_amount
			)
		);
	}
	do_action(
	'flipnzee_auction_winner_determined',
	$auction_id,
	$winner
);

	return true;
}

}
