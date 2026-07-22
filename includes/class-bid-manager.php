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
	error_log( 'FLIPNZEE: place_bid() START' );

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
	error_log(
    'FLIPNZEE: About to insert bid ' . $bid_amount
);
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

	error_log(
    'FLIPNZEE: Bid inserted successfully'
);

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
		'%d',	)
);

/*
 * Buy Now:
 * Close the auction immediately if the
 * bid meets or exceeds the Buy Now price.
 */
if (
	! empty( $auction->buy_now_price ) &&
	(float) $auction->buy_now_price > 0 &&
	(float) $bid_amount >= (float) $auction->buy_now_price
) {

	error_log(
		'FLIPNZEE: Buy Now triggered for auction ' .
		$auction_id
	);

}

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
	if ( ! self::reserve_price_met( $auction_id, $winner ) ) {
    return false;
}

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
	error_log(
    'FLIPNZEE DEBUG: About to fire flipnzee_auction_winner_determined'
);
	do_action(
	'flipnzee_auction_winner_determined',
	$auction_id,
	$winner
);

error_log(
    'FLIPNZEE DEBUG: Finished flipnzee_auction_winner_determined'
);

	return $winner;
}
/**
 * Check whether reserve price has been met.
 *
 * @param object $auction Auction object.
 * @param object $winner Highest bid object.
 *
 * @return bool
 */
public static function reserve_price_met(
    $auction,
    $winner
) {

    if ( empty( $auction->reserve_price ) ) {
        return true;
    }

	if ( ! self::reserve_price_met( $auction_id, $winner ) ) {

    if ( class_exists( 'Flipnzee_Activity_Log' ) ) {

        Flipnzee_Activity_Log::log(
            'reserve_not_met',
            $auction_id,
            0,
            sprintf(
                'Highest bid %s did not reach reserve price.',
                $winner->bid_amount
            )
        );
    }

    error_log(
        'FLIPNZEE: Reserve price not met. No winner declared.'
    );

    return false;
}

    if ( ! $winner ) {
        return false;
    }

	$auction = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT auction_end,
		        buy_now_price
		FROM {$auction_table}
		WHERE id = %d",
		$auction_id
	)
);

if (
    $auction &&
    (float) $auction->reserve_price > 0 &&
    (float) $winner->bid_amount < (float) $auction->reserve_price
) {

    $wpdb->update(
        $auction_table,
        array(
            'winner_user_id' => null,
        ),
        array(
            'id' => $auction_id,
        ),
        array(
            null,
        ),
        array(
            '%d',
        )
    );

    if ( class_exists( 'Flipnzee_Activity_Log' ) ) {

        Flipnzee_Activity_Log::log(
            'reserve_not_met',
            $auction_id,
            $winner->bidder_id,
            sprintf(
                'Highest bid %s did not meet reserve price %s',
                $winner->bid_amount,
                $auction->reserve_price
            )
        );
    }

    return false;
}
          
    

	$auction = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT reserve_price
        FROM {$auction_table}
        WHERE id = %d",
        $auction_id
    )
);

if (
    ! self::reserve_price_met(
        $auction,
        $winner
    )
) {

    if ( class_exists( 'Flipnzee_Activity_Log' ) ) {

        Flipnzee_Activity_Log::log(
            'reserve_not_met',
            $auction_id,
            0,
            sprintf(
                'Highest bid %s did not meet reserve price %s.',
                $winner->bid_amount,
                $auction->reserve_price
            )
        );
    }

    return false;
}

    return (float) $winner->bid_amount >=
        (float) $auction->reserve_price;
}

/**
 * Check whether a bid reached the Buy Now price.
 *
 * @param int   $auction_id Auction ID.
 * @param float $bid_amount Bid amount.
 *
 * @return bool
 */
public static function is_buy_now_bid(
    $auction_id,
    $bid_amount
) {

    global $wpdb;

    $table = $wpdb->prefix . 'flipnzee_auctions';

    $buy_now_price = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT buy_now_price
             FROM {$table}
             WHERE id = %d",
            $auction_id
        )
    );

    error_log(
        'BUY NOW DEBUG: auction_id=' . $auction_id
    );

    error_log(
        'BUY NOW DEBUG: buy_now_price=' .
        print_r( $buy_now_price, true )
    );

    error_log(
        'BUY NOW DEBUG: bid_amount=' .
        print_r( $bid_amount, true )
    );

    if ( empty( $buy_now_price ) ) {

        error_log( 'BUY NOW DEBUG: Empty Buy Now Price' );

        return false;
    }

    $result =
        (float) $bid_amount >=
        (float) $buy_now_price;

    error_log(
        'BUY NOW DEBUG: result=' .
        ( $result ? 'TRUE' : 'FALSE' )
    );

    return $result;
}}
