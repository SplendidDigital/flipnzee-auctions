<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Auction_Manager {

	/**
	 * Create a new auction.
	 *
	 * @param int   $listing_id    The ID of the listing.
	 * @param float $start_price   Starting auction price.
	 * @param float $reserve_price Reserve price.
	 * @param float $buy_now_price Buy Now price.
	 *
	 * @return int|false Auction ID on success, false on failure.
	 */
	public static function create_auction(
	$listing_id,
	$start_price,
	$reserve_price,
	$buy_now_price,
	$auction_start,
	$auction_end
) {

	global $wpdb;

$table = $wpdb->prefix . 'flipnzee_auctions';


/*
 * Check whether this listing already has an auction.
 */
$existing_auction = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT id
        FROM {$table}
        WHERE listing_id = %d
        AND status IN ('active', 'scheduled')
        LIMIT 1",
        $listing_id
    )
);

/*
 * If it exists, update it instead of creating a new one.
 */

if ( $existing_auction ) {

	self::update_auction(
		$existing_auction,
		$listing_id,
		$start_price,
		$reserve_price,
		$buy_now_price,
		'active',
		$auction_start,
		$auction_end
	);
   
	return $existing_auction;
}
$result = $wpdb->insert(
			$table,
			array(
    'listing_id'    => $listing_id,
    'start_price'   => $start_price,
    'reserve_price' => $reserve_price,
    'buy_now_price' => $buy_now_price,
    'status' => 'active',
    'auction_start' => $auction_start,
    'auction_end'   => $auction_end,
),
			array(
    '%d',
    '%f',
    '%f',
    '%f',
    '%s',
    '%s',
    '%s',
),
		);

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}
	/**
 * Update an existing auction.
 *
 * @param int    $auction_id     Auction ID.
 * @param int    $listing_id     Listing ID.
 * @param float  $start_price    Start price.
 * @param float  $reserve_price  Reserve price.
 * @param float  $buy_now_price  Buy Now price.
 * @param string $status         Auction status.
 *
 * @return bool
 */
public static function update_auction(
    $auction_id,
    $listing_id,
    $start_price,
    $reserve_price,
    $buy_now_price,
    $status,
    $auction_start,
    $auction_end
) {

    global $wpdb;

    $table = $wpdb->prefix . 'flipnzee_auctions';
	

    error_log( 'auction_start = ' . $auction_start );
    error_log( 'auction_end = ' . $auction_end );

    error_log(
        print_r(
            array(
                'listing_id'    => $listing_id,
                'start_price'   => $start_price,
                'reserve_price' => $reserve_price,
                'buy_now_price' => $buy_now_price,
                'status'        => $status,
                'auction_start' => $auction_start,
                'auction_end'   => $auction_end,
            ),
            true
        )
    );

    $result = $wpdb->update(
        $table,
        array(
            'listing_id'    => $listing_id,
            'start_price'   => $start_price,
            'reserve_price' => $reserve_price,
            'buy_now_price' => $buy_now_price,
            'status'        => $status,
            'auction_start' => $auction_start,
            'auction_end'   => $auction_end,
        ),
        array(
            'id' => $auction_id,
        ),
        array(
            '%d',
            '%f',
            '%f',
            '%f',
            '%s',
            '%s',
            '%s',
        ),
        array(
            '%d',
        )
    );

    if ( false === $result ) {
        error_log( 'DB ERROR: ' . $wpdb->last_error );
        error_log( 'LAST QUERY: ' . $wpdb->last_query );
    }

    return false !== $result;
}

/**
 * Automatically close expired active auctions.
 *
 * Updates any auction that is still marked as active
 * but whose end date/time has already passed.
 *
 * @return int Number of auctions updated.
 */
public static function update_expired_auctions() {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';
	$expired_auctions = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT id
		FROM {$table}
		WHERE status = %s
		AND auction_end < %s",
		'active',
		current_time( 'mysql' )
	)
);

	$result = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table}
			SET status = %s
			WHERE status = %s
			AND auction_end < %s",
			'closed',
			'active',
			current_time( 'mysql' )
		)
	);

	$updated_count = ( false === $result ) ? 0 : (int) $result;
if ( $updated_count > 0 ) {

	Flipnzee_Activity_Log::log(
		'auction_auto_closed',
		0,
		0,
		sprintf(
			'%d auction(s) automatically closed.',
			$updated_count
		)
	);
	do_action(
	'flipnzee_auction_winner_determined',
	$auction,
	$winner
);
	foreach ( $expired_auctions as $auction_id ) {

	Flipnzee_Bid_Manager::determine_winner(
		(int) $auction_id
	);

}
}
/**
 * Fires after expired auctions have been processed.
 *
 * This action allows Flipnzee Analytics and other plugins
 * to respond after automatic auction lifecycle processing
 * has completed.
 *
 * Typical uses include:
 * - Refresh marketplace statistics.
 * - Send winner or seller notifications.
 * - Record activity logs.
 * - Clear caches.
 * - Trigger third-party integrations.
 *
 * @since 1.0.0
 *
 * @param int $updated_count Number of auctions automatically closed.
 */
do_action(
    'flipnzee_auctions_expired_processed',
    $updated_count
);

return $updated_count;
}
		public static function delete_auction( $auction_id ) {

		global $wpdb;

		$table = $wpdb->prefix . 'flipnzee_auctions';

		$result = $wpdb->delete(
			$table,
			array(
				'id' => $auction_id,
			),
			array(
				'%d',
			)
		);

		return false !== $result;
	}


	/**
	 * Retrieve one auction by ID.
	 *
	 * @param int $auction_id Auction ID.
	 *
	 * @return object|null
	 */
	public static function get_auction( $auction_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	

	$result = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE id = %d",
			$auction_id
		)
	);

	

	return $result;
}
/**
 * Retrieve one auction by Listing ID.
 *
 * @param int $listing_id Listing ID.
 *
 * @return object|null
 */
public static function get_auction_by_listing_id( $listing_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE listing_id = %d",
			$listing_id
		)
	);
}
	/**
	 * Retrieve all auctions.
	 *
	 * @return array
	 */
	/**
 * Retrieve auctions with pagination.
 *
 * @param int $per_page Number of records per page.
 * @param int $offset   SQL offset.
 *
 * @return array
 */
public static function get_all_auctions(
	$per_page = 20,
	$offset = 0,
	$search = '',
	$status = '',
	$orderby = 'created_at',
	$order = 'DESC'
) {

	global $wpdb;

$table = $wpdb->prefix . 'flipnzee_auctions';
$allowed_columns = array(
	'id',
	'listing_id',
	'start_price',
	'reserve_price',
	'buy_now_price',
	'status',
	'created_at',
);

if ( ! in_array( $orderby, $allowed_columns, true ) ) {
	$orderby = 'created_at';
}

$order = ( 'ASC' === strtoupper( $order ) )
	? 'ASC'
	: 'DESC';

if ( ! empty( $search ) ) {

	$like = '%' . $wpdb->esc_like( $search ) . '%';

	if ( ! empty( $status ) ) {

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				FROM {$table}
				WHERE
				(
					id LIKE %s
					OR listing_id LIKE %s
				)
				AND status = %s
				ORDER BY {$orderby} {$order}
				LIMIT %d OFFSET %d",
				$like,
				$like,
				$status,
				$per_page,
				$offset
			)
		);
	}

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE
				id LIKE %s
				OR listing_id LIKE %s
				OR status LIKE %s
			ORDER BY {$orderby} {$order}
			LIMIT %d OFFSET %d",
			$like,
			$like,
			$like,
			$per_page,
			$offset
		)
	);
}
if ( ! empty( $status ) ) {

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE status = %s
			ORDER BY {$orderby} {$order}
			LIMIT %d OFFSET %d",
			$status,
			$per_page,
			$offset
		)
	);
}



return $wpdb->get_results(
	$wpdb->prepare(
	"SELECT *
	FROM {$table}
	ORDER BY {$orderby} {$order}
	LIMIT %d OFFSET %d",
	$per_page,
	$offset
)
);
}
	/**
 * Count all auctions.
 *
 * @return int
 */
public static function count_auctions(
	$search = '',
	$status = ''
) {


	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	if ( ! empty( $search ) && ! empty( $status ) ) {

	$like = '%' . $wpdb->esc_like( $search ) . '%';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$table}
			WHERE
			(
				id LIKE %s
				OR listing_id LIKE %s
			)
			AND status = %s",
			$like,
			$like,
			$status
		)
	);
}

if ( ! empty( $search ) ) {

	$like = '%' . $wpdb->esc_like( $search ) . '%';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$table}
			WHERE
				id LIKE %s
				OR listing_id LIKE %s
				OR status LIKE %s",
			$like,
			$like,
			$like
		)
	);
}

if ( ! empty( $status ) ) {

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$table}
			WHERE status = %s",
			$status
		)
	);
}

return (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table}"
);

	
}
/**
 * Delete multiple auctions.
 *
 * @param array $auction_ids Auction IDs.
 *
 * @return void
 */
public static function delete_multiple_auctions( $auction_ids ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	foreach ( $auction_ids as $auction_id ) {

		$wpdb->delete(
			$table,
			array(
				'id' => absint( $auction_id ),
			),
			array( '%d' )
		);
	}
}
/**
 * Activate scheduled auctions.
 *
 * @return void
 */
public static function activate_scheduled_auctions() {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	$current_time = current_time( 'mysql' );
	

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table}
			SET status = %s
			WHERE status = %s
			AND auction_start IS NOT NULL
			AND auction_start <= %s",
			'active',
			'draft',
			$current_time
		)
	);
}

/**
 * Automatically close expired auctions.
 */
/**
 * Automatically close expired auctions.
 *
 * @return int Number of auctions closed.
 */
public static function close_expired_auctions() {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	$current_time = current_time( 'mysql' );

	$result = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table}
			SET status = %s
			WHERE status = %s
			AND auction_end IS NOT NULL
			AND auction_end <= %s",
			'closed',
			'active',
			$current_time
		)
	);
	return (int) $result;

	$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$table}
        SET status = %s
        WHERE status = %s
        AND auction_end IS NOT NULL
        AND auction_end <= %s",
        'closed',
        'active',
        $current_time
    )
);
}
/**
 * Run scheduled maintenance.
 *
 * @return void
 */
public static function run_scheduled_maintenance() {

	self::activate_scheduled_auctions();

	self::update_expired_auctions();
}
/**
 * Get active auctions.
 *
 * @return array
 */
public static function get_active_auctions() {
	self::update_expired_auctions();

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_auctions';

	return $wpdb->get_results(
		"SELECT *
		FROM {$table}
		WHERE status IN ('active', 'closed')
		ORDER BY auction_end ASC",
		ARRAY_A
	);
}
}

