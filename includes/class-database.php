<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Auction_Database {

	/**
	 * Create the plugin database tables.
	 */
	public static function create_tables() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'flipnzee_auctions';

		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table_name} (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
listing_id BIGINT UNSIGNED NOT NULL,
status VARCHAR(30) DEFAULT 'draft',
start_price DECIMAL(12,2) DEFAULT 0,
reserve_price DECIMAL(12,2) DEFAULT 0,
buy_now_price DECIMAL(12,2) DEFAULT 0,
current_bid DECIMAL(12,2) DEFAULT 0,
auction_start DATETIME NULL,
auction_end DATETIME NULL,
winner_user_id BIGINT UNSIGNED DEFAULT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
KEY listing_id (listing_id),
KEY status (status)
) {$charset_collate};";
		dbDelta( $sql );

/*
 * Create bids table.
 */
$bid_table = $wpdb->prefix . 'flipnzee_bids';

$sql = "CREATE TABLE {$bid_table} (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
auction_id BIGINT UNSIGNED NOT NULL,
bidder_id BIGINT UNSIGNED NOT NULL,
bid_amount DECIMAL(12,2) NOT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
KEY auction_id (auction_id)
) {$charset_collate};";
dbDelta( $sql );

/*
 * Create transactions table.
 */
$transaction_table = $wpdb->prefix . 'flipnzee_transactions';

$sql = "CREATE TABLE {$transaction_table} (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
auction_id BIGINT UNSIGNED NOT NULL,
listing_id BIGINT UNSIGNED NOT NULL,
seller_id BIGINT UNSIGNED NOT NULL,
buyer_id BIGINT UNSIGNED NOT NULL,
winning_bid DECIMAL(12,2) NOT NULL,
status VARCHAR(30) DEFAULT 'pending',
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
KEY auction_id (auction_id),
KEY buyer_id (buyer_id),
KEY seller_id (seller_id),
KEY status (status)
) {$charset_collate};";
dbDelta( $sql );
	}
}