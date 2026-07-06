<?php
/**
 * Frontend Shortcodes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_shortcode(
			'flipnzee_auctions',
			array(
				$this,
				'auctions_shortcode',
			)
		);
	}

	/**
	 * Display auctions.
	 *
	 * @return string
	 */
	/**
 * Display active auctions.
 *
 * @return string
 */
public function auctions_shortcode() {
    
	$auctions = Flipnzee_Auction_Manager::get_active_auctions();

	if ( empty( $auctions ) ) {

		return '<p>No active auctions found.</p>';
	}

	ob_start();

	?>

	<div class="flipnzee-auctions">

		<?php foreach ( $auctions as $auction ) : ?>

			<?php
$listing = get_post( $auction['listing_id'] );

if ( ! $listing ) {
	continue;
}
$current_time = current_time( 'timestamp' );
$end_time = strtotime( $auction['auction_end'] );

if ( 'closed' === $auction['status'] ) {

    $status = 'ended';
    $status_label = 'Auction Ended';

} elseif ( $end_time <= $current_time ) {

    $status = 'ended';
    $status_label = 'Auction Ended';

} elseif ( ( $end_time - $current_time ) <= DAY_IN_SECONDS ) {

    $status = 'ending';
    $status_label = 'Ending Soon';

} else {

    $status = 'live';
    $status_label = 'Live Auction';

}
?>
<div class="flipnzee-auction-card">

	<div class="flipnzee-auction-thumbnail">

		<?php if ( has_post_thumbnail( $listing ) ) : ?>

			<?php echo get_the_post_thumbnail( $listing, 'medium' ); ?>

		<?php else : ?>

			<img
				src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/placeholder.png' ); ?>"
				alt="<?php esc_attr_e( 'Website Preview Unavailable', 'flipnzee-auctions' ); ?>"
				class="flipnzee-auction-image"
			/>

		<?php endif; ?>

	</div>

	<div class="flipnzee-auction-content">
		<div class="flipnzee-auction-status flipnzee-status-<?php echo esc_attr( $status ); ?>">
    <?php echo esc_html( $status_label ); ?>
</div>


				<h3>
	<?php echo esc_html( get_the_title( $listing ) ); ?>
</h3>
<?php

$listing_id = $listing->ID;


$bids = Flipnzee_Bid_Manager::get_bids(
    $auction['id']
);

$highest_bidder = Flipnzee_Bid_Manager::get_highest_bidder(
    $auction['id']
);


$winning_bid = Flipnzee_Bid_Manager::get_winning_bid(
    $auction['id']
);


/*
 * Check whether auction has expired.
 */
$auction_closed =
    (
        'closed' === $auction['status']
    )
    ||
    (
        current_time( 'timestamp' ) >=
        strtotime( $auction['auction_end'] )
    );



$main = get_transient( "flipnzee_main_{$listing_id}" );
$meta = get_transient( "flipnzee_meta_{$listing_id}" );

$main = get_transient( "flipnzee_main_{$listing_id}" );
$meta = get_transient( "flipnzee_meta_{$listing_id}" );

if ( is_array( $main ) || is_array( $meta ) ) :

?>

<div class="flipnzee-analytics-summary">

    <div class="flipnzee-verified">
        ✔ Google Verified Analytics
    </div>

    <div class="flipnzee-stat">
        👥 Monthly Users
        <strong><?php echo esc_html( is_array( $main ) ? ( $main['users'] ?? 0 ) : 0 ); ?></strong>
    </div>

    <div class="flipnzee-stat">
        📈 Monthly Sessions
        <strong><?php echo esc_html( is_array( $main ) ? ( $main['sessions'] ?? 0 ) : 0 ); ?></strong>
    </div>

    <div class="flipnzee-stat">
        🔍 Google Impressions
        <strong><?php echo esc_html( is_array( $meta ) ? ( $meta['organic_impressions'] ?? 0 ) : 0 ); ?></strong>
    </div>

</div>

<?php endif; ?>

<?php
if ( isset( $_GET['bid'] ) ) {

	if ( 'success' === sanitize_text_field( wp_unslash( $_GET['bid'] ) ) ) {
		?>

		<div class="flipnzee-notice flipnzee-success">
			✅ Your bid has been placed successfully.
		</div>

		<?php

	} elseif ( 'failed' === sanitize_text_field( wp_unslash( $_GET['bid'] ) ) ) {
		?>

		<div class="flipnzee-notice flipnzee-error">
			❌ Your bid could not be accepted.
		</div>

		<?php
	}
}
?>

<table class="flipnzee-auction-meta">

	<tr>
		<th>Start Price</th>
		<td><?php echo esc_html( '$' . number_format_i18n( $auction['start_price'], 0 ) ); ?></td>
	</tr>

	<tr>
		<th>Current Bid</th>
		<td><?php echo esc_html( '$' . number_format_i18n( $auction['current_bid'], 0 ) ); ?></td>
	</tr>

    <tr>
    <th>Highest Bidder</th>
    <td>
        <?php
        if ( $highest_bidder ) {
            echo esc_html(
                $highest_bidder->display_name
            );
        } else {
            echo esc_html__(
                'No bids yet',
                'flipnzee-auctions'
            );
        }
        ?>
    </td>
</tr>
    
</tr>

	
	<tr>
		<th>Buy Now</th>
		<td><?php echo esc_html( '$' . number_format_i18n( $auction['buy_now_price'], 0 ) ); ?></td>
	</tr>

	<?php if ( 'closed' === $auction['status'] ) : ?>

<tr>
    <th>Auction Status</th>
    <td>Auction Ended</td>
</tr>

<?php else : ?>

<tr>
    <th>Auction Ends In</th>
    <td
        class="flipnzee-countdown"
        data-end="<?php echo esc_attr( $auction['auction_end'] ); ?>"
    >
        Loading...
    </td>
</tr>

<?php endif; ?>

</table>


<?php if ( is_user_logged_in() && $highest_bidder ) : ?>

	<?php if ( get_current_user_id() === (int) $highest_bidder->bidder_id ) : ?>

		<div class="flipnzee-bid-status flipnzee-winning">
			🏆 You are currently the highest bidder.
		</div>

	<?php else : ?>

		<div class="flipnzee-bid-status flipnzee-outbid">
			⚠️ You have been outbid.
		</div>

	<?php endif; ?>

<?php endif; ?>


<h3>Bid History</h3>

<?php if ( empty( $bids ) ) : ?>

    <p>No bids have been placed yet.</p>

<?php else : ?>

<table class="flipnzee-bid-history">

    <thead>

        <tr>

            <th>Bidder</th>

            <th>Amount</th>

            <th>Time</th>

        </tr>

    </thead>

    <tbody>

    <?php foreach ( $bids as $bid ) : ?>

        <?php
        $user = get_userdata( $bid['bidder_id'] );
        ?>

        <tr>

            <td>

                <?php
                echo esc_html(
                    $user ? $user->display_name : 'Unknown'
                );
                ?>

            </td>

            <td>

                <?php
                echo '$' . number_format_i18n(
                    $bid['bid_amount'],
                    2
                );
                ?>

            </td>

            <td>

                <?php
                echo esc_html(
                    $bid['created_at']
                );
                ?>

            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>

<?php if ( $auction_closed ) : ?>

<div class="flipnzee-auction-closed">

    <?php if ( $winning_bid ) : ?>

    <p>
        🏆 <strong>Winner:</strong>
        <?php echo esc_html( $winning_bid->display_name ); ?>
    </p>
        <p>
            💰 <strong>Winning Bid:</strong>
            <?php
            echo esc_html(
                '$' . number_format_i18n(
                    $winning_bid->bid_amount,
                    2
                )
            );
            ?>
        </p>

    <?php else : ?>

        <p>No bids were placed.</p>

    <?php endif; ?>

    <p>
        🏁 <strong>Auction Closed</strong><br>
        This auction has ended. No further bids are accepted.
    </p>

</div>

<?php elseif ( is_user_logged_in() ) : ?>

<form
    method="post"
    action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
    class="flipnzee-bid-form"
>

    <input
        type="hidden"
        name="action"
        value="flipnzee_place_bid"
    >

    <input
        type="hidden"
        name="auction_id"
        value="<?php echo esc_attr( $auction['id'] ); ?>"
    >

    <?php
    wp_nonce_field(
        'flipnzee_place_bid',
        'flipnzee_nonce'
    );
    ?>

    <?php
$minimum_bid =
    (float) $auction['current_bid'] + 10;
?>

<input
    type="number"
    step="10"
    min="<?php echo esc_attr( $minimum_bid ); ?>"
    value="<?php echo esc_attr( $minimum_bid ); ?>"
    name="bid_amount"
    placeholder="Your Bid"
    required
>

    <button type="submit">
        Place Bid
    </button>

</form>

<?php else : ?>

<p>
    Please log in to place a bid.
</p>

<?php endif; ?>

<p class="flipnzee-auction-actions">

	<a
		href="<?php echo esc_url( get_permalink( $listing ) ); ?>"
		class="flipnzee-view-listing-button"
	>

		View Listing

	</a>

</p>

						</div><!-- .flipnzee-auction-content -->

		</div><!-- .flipnzee-auction-card -->

		<hr>

		<?php endforeach; ?>

	</div>

	<?php

	return ob_get_clean();
}
}