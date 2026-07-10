<?php
/**
 * Watchlist Manager.
 *
 * Handles watchlist database operations.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Watchlist_Manager {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private static $wpdb;

    /**
     * Watchlist table name.
     *
     * @var string
     */
    private static $table;

    /**
     * Initialize the manager.
     *
     * @return void
     */
    public static function init() {

        global $wpdb;

        self::$wpdb = $wpdb;

        self::$table = $wpdb->prefix . 'flipnzee_watchlist';
    }

    /**
 * Add an auction to a user's watchlist.
 *
 * @param int $auction_id Auction ID.
 * @param int $user_id    User ID.
 *
 * @return bool
 */
public static function add_to_watchlist( $auction_id, $user_id ) {

    self::init();

    // Prevent duplicate entries.
    if ( self::is_in_watchlist( $auction_id, $user_id ) ) {
        return false;
    }

    $result = self::$wpdb->insert(
    self::$table,
    array(
        'auction_id' => absint( $auction_id ),
        'user_id'    => absint( $user_id ),
        'created_at' => current_time( 'mysql' ),
    ),
    array(
        '%d',
        '%d',
        '%s',
    )
);

error_log( 'TABLE: ' . self::$table );
error_log( 'USER: ' . $user_id );
error_log( 'AUCTION: ' . $auction_id );
error_log( 'INSERT RESULT: ' . var_export( $result, true ) );
error_log( 'LAST ERROR: ' . self::$wpdb->last_error );
error_log( 'LAST QUERY: ' . self::$wpdb->last_query );

return false !== $result;
}

/**
 * Check whether an auction is already in a user's watchlist.
 *
 * @param int $auction_id Auction ID.
 * @param int $user_id    User ID.
 *
 * @return bool
 */
public static function is_in_watchlist( $auction_id, $user_id ) {

    self::init();

    $watchlist_id = self::$wpdb->get_var(
        self::$wpdb->prepare(
            "SELECT id
            FROM " . self::$table . "
            WHERE auction_id = %d
            AND user_id = %d
            LIMIT 1",
            absint( $auction_id ),
            absint( $user_id )
        )
    );

    return ! empty( $watchlist_id );
}

/**
 * Remove an auction from a user's watchlist.
 *
 * @param int $auction_id Auction ID.
 * @param int $user_id    User ID.
 *
 * @return bool
 */
public static function remove_from_watchlist( $auction_id, $user_id ) {

    self::init();

    $result = self::$wpdb->delete(
        self::$table,
        array(
            'auction_id' => absint( $auction_id ),
            'user_id'    => absint( $user_id ),
        ),
        array(
            '%d',
            '%d',
        )
    );

    return false !== $result;
}

/**
 * Get all auctions in a user's watchlist.
 *
 * @param int $user_id User ID.
 *
 * @return array
 */
public static function get_user_watchlist( $user_id ) {

    self::init();

    $results = self::$wpdb->get_results(
        self::$wpdb->prepare(
            "SELECT *
            FROM " . self::$table . "
            WHERE user_id = %d
            ORDER BY created_at DESC",
            absint( $user_id )
        ),
        ARRAY_A
    );

    return $results;
}

/**
 * Count how many users are watching an auction.
 *
 * @param int $auction_id Auction ID.
 *
 * @return int
 */
public static function count_watchers( $auction_id ) {

    self::init();

    $count = self::$wpdb->get_var(
        self::$wpdb->prepare(
            "SELECT COUNT(*)
            FROM " . self::$table . "
            WHERE auction_id = %d",
            absint( $auction_id )
        )
    );

    return absint( $count );
}

/**
 * Display the Watchlist button.
 *
 * @param int $auction_id Auction ID.
 * @return void
 */
public static function render_button( $auction_id ) {
	?>
	<button
		type="button"
		class="flipnzee-watchlist-button"
		data-auction-id="<?php echo esc_attr( $auction_id ); ?>">
		❤ Add to Watchlist
	</button>
	<?php
}


}