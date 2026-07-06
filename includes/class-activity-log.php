<?php
/**
 * Activity Log.
 *
 * Records important auction events.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity Log class.
 */
class Flipnzee_Activity_Log {

	/**
	 * Record an activity.
	 *
	 * Currently writes to the PHP error log.
	 * Future lessons will store records in a database table.
	 *
	 * @param string $event      Event name.
	 * @param int    $auction_id Auction ID.
	 * @param int    $user_id    User ID.
	 * @param string $details    Optional details.
	 *
	 * @return void
	 */
	public static function log( $event, $auction_id = 0, $user_id = 0, $details = '' ) {

	$upload_dir = wp_upload_dir();

	$log_dir = trailingslashit( $upload_dir['basedir'] ) . 'flipnzee-logs';

	if ( ! file_exists( $log_dir ) ) {
		wp_mkdir_p( $log_dir );
	}

	$log_file = trailingslashit( $log_dir ) . 'activity.log';

	$entry = sprintf(
		"[%s] Event: %s | Auction: %d | User: %d | Details: %s\n",
		current_time( 'mysql' ),
		$event,
		$auction_id,
		$user_id,
		$details
	);

	file_put_contents(
		$log_file,
		$entry,
		FILE_APPEND | LOCK_EX
	);
}
}