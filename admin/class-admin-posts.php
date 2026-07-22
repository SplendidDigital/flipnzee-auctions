<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Auction_Admin_Posts {

	/**
	 * Constructor.
	 */
	

	public function __construct() {

	add_action(
		'admin_post_flipnzee_create_auction',
		array( $this, 'handle_create_auction' )
	);

	add_action(
		'admin_post_flipnzee_update_auction',
		array( $this, 'handle_update_auction' )
	);
	add_action(
    'admin_post_flipnzee_place_bid',
    array( $this, 'handle_place_bid' )
);

add_action(
    'admin_post_nopriv_flipnzee_place_bid',
    array( $this, 'handle_place_bid' )
);
	
	add_action(
	'admin_post_flipnzee_delete_auction',
	array( $this, 'handle_delete_auction' )
);
}

	/**
	 * Handle the Add Auction form submission.
	 */
	public function handle_create_auction() {

		// Verify the nonce.
		check_admin_referer(
			'flipnzee_create_auction',
			'flipnzee_nonce'
		);

		// Sanitize and validate form data.
		$listing_id    = absint( $_POST['listing_id'] );

		$start_price   = (float) $_POST['start_price'];

		$reserve_price = (float) $_POST['reserve_price'];

		$buy_now_price = (float) $_POST['buy_now_price'];

		$auction_start = isset( $_POST['auction_start'] )
	? sanitize_text_field(
		wp_unslash( $_POST['auction_start'] )
	)
	: '';

$auction_end = isset( $_POST['auction_end'] )
	? sanitize_text_field(
		wp_unslash( $_POST['auction_end'] )
	)
	: '';

		// Create the auction.
		$auction_id = Flipnzee_Auction_Manager::create_auction(
	$listing_id,
	$start_price,
	$reserve_price,
	$buy_now_price,
	$auction_start,
	$auction_end
);

		// Redirect with a status message.
		if ( $auction_id ) {

			wp_safe_redirect(
				admin_url(
					'admin.php?page=flipnzee-add-auction&message=success'
				)
			);

		} else {

			wp_safe_redirect(
				admin_url(
					'admin.php?page=flipnzee-add-auction&message=error'
				)
			);

		}

		exit;
}

/**
 * Handle the Edit Auction form submission.
 */
public function handle_update_auction() {

		check_admin_referer(
			'flipnzee_update_auction',
			'flipnzee_nonce'
		);

		$auction_id = isset( $_POST['auction_id'] )
			? absint( $_POST['auction_id'] )
			: 0;

		$listing_id = isset( $_POST['listing_id'] )
			? absint( $_POST['listing_id'] )
			: 0;

		$start_price = isset( $_POST['start_price'] )
			? floatval( $_POST['start_price'] )
			: 0;

		$reserve_price = isset( $_POST['reserve_price'] )
			? floatval( $_POST['reserve_price'] )
			: 0;

		$buy_now_price = isset( $_POST['buy_now_price'] )
			? floatval( $_POST['buy_now_price'] )
			: 0;

		$status = isset( $_POST['status'] )
			? sanitize_text_field(
				wp_unslash( $_POST['status'] )
			)
			: 'draft';

		$auction_start = isset( $_POST['auction_start'] )
    ? str_replace(
        'T',
        ' ',
        sanitize_text_field(
            wp_unslash( $_POST['auction_start'] )
        )
    ) . ':00'
    : '';

$auction_end = isset( $_POST['auction_end'] )
    ? str_replace(
        'T',
        ' ',
        sanitize_text_field(
            wp_unslash( $_POST['auction_end'] )
        )
    ) . ':00'
    : '';
		$updated = Flipnzee_Auction_Manager::update_auction(
    $auction_id,
    $listing_id,
    $start_price,
    $reserve_price,
    $buy_now_price,
    $status,
    $auction_start,
    $auction_end
);

error_log(
    'FLIPNZEE: updated = ' .
    var_export( $updated, true )
);

error_log(
    'FLIPNZEE: status = ' .
    $status
);

if ( $updated && 'closed' === $status ) {

    error_log(
        'FLIPNZEE: Auction was closed. Looking for winner.'
    );

    $winner = Flipnzee_Bid_Manager::determine_winner(
    $auction_id
);

    error_log(
        'FLIPNZEE: Winner = ' .
        var_export( $winner, true )
    );

}

		$message = $updated
			? 'updated'
			: 'error';

		wp_safe_redirect(
			admin_url(
				'admin.php?page=flipnzee-edit-auction&auction_id=' .
				$auction_id .
				'&message=' .
				$message
			)
		);

		exit;
	
	    
    }

    
	/**
 * Handle the Delete Auction request.
 */
/**
 * Handle Place Bid.
 */
public function handle_place_bid() {

    check_admin_referer(
        'flipnzee_place_bid',
        'flipnzee_nonce'
    );

    if ( ! is_user_logged_in() ) {
        wp_die( 'Please login.' );
    }

    $auction_id = isset( $_POST['auction_id'] )
        ? absint( $_POST['auction_id'] )
        : 0;

    $bid_amount = isset( $_POST['bid_amount'] )
        ? (float) $_POST['bid_amount']
        : 0;

    $result = Flipnzee_Bid_Manager::place_bid(
    $auction_id,
    get_current_user_id(),
    $bid_amount
);

error_log( 'HANDLE: place_bid() finished' );

$is_buy_now = Flipnzee_Bid_Manager::is_buy_now_bid(
    $auction_id,
    $bid_amount
);

error_log(
    'HANDLE: is_buy_now = ' .
    ( $is_buy_now ? 'TRUE' : 'FALSE' )
);

if ( $is_buy_now ) {

	error_log(
		'BUY NOW: Calling close_auction()'
	);

	Flipnzee_Auction_Manager::close_auction(
		$auction_id
	);

	error_log(
		'BUY NOW: Calling determine_winner()'
	);

	Flipnzee_Bid_Manager::determine_winner(
		$auction_id
	);

}


$url = wp_get_referer();

if ( is_wp_error( $result ) ) {

    $url = add_query_arg(
        'bid',
        'already_highest',
        $url
    );

} elseif ( $result ) {

    $url = add_query_arg(
        'bid',
        'success',
        $url
    );

} else {

    $url = add_query_arg(
        'bid',
        'failed',
        $url
    );

}
wp_safe_redirect( $url );

exit;
}
public function handle_delete_auction() {

	check_admin_referer(
		'flipnzee_delete_auction'
	);

	$auction_id = isset( $_GET['auction_id'] )
		? absint( $_GET['auction_id'] )
		: 0;

	if ( $auction_id ) {

		$deleted = Flipnzee_Auction_Manager::delete_auction(
			$auction_id
		);

		$message = $deleted
			? 'deleted'
			: 'error';

	} else {

		$message = 'error';
	}

	wp_safe_redirect(
		admin_url(
			'admin.php?page=flipnzee-all-auctions&message=' .
			$message
		)
	);

	exit;
}
}

new Flipnzee_Auction_Admin_Posts();