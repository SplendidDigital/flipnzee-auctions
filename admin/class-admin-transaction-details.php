<?php
/**
 * Transaction Details Admin Page
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Admin_Transaction_Details {

	/**
	 * Render the transaction details page.
	 */
	public static function render_page() {

		global $wpdb;

$transaction_id = isset( $_GET['transaction_id'] )
	? absint( $_GET['transaction_id'] )
	: 0;

$table = $wpdb->prefix . 'flipnzee_transactions';

$transaction = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM {$table} WHERE id = %d",
		$transaction_id
	),
	ARRAY_A
);
if ( ! $transaction ) {
	?>

	<div class="wrap">

		<h1>Transaction Details</h1>

		<div class="notice notice-error">
			<p>Transaction not found.</p>
		</div>

	</div>

	<?php

	return;
}

?>

<div class="wrap">

	<h1>Transaction Details</h1>

	<table class="widefat striped" style="max-width:800px;">

		<tbody>

			<tr>
				<th>ID</th>
				<td><?php echo esc_html( $transaction['id'] ); ?></td>
			</tr>

			<tr>
				<th>Auction</th>
				<td><?php echo esc_html( $transaction['auction_id'] ); ?></td>
			</tr>

			<tr>
				<th>Listing</th>
				<td><?php echo esc_html( $transaction['listing_id'] ); ?></td>
			</tr>

			<tr>
				<th>Seller</th>
				<td><?php echo esc_html( $transaction['seller_id'] ); ?></td>
			</tr>

			<tr>
				<th>Buyer</th>
				<td><?php echo esc_html( $transaction['buyer_id'] ); ?></td>
			</tr>

			<tr>
				<th>Winning Bid</th>
				<td><?php echo esc_html( $transaction['winning_bid'] ); ?></td>
			</tr>

			<tr>
				<th>Status</th>
				<td><?php echo esc_html( ucfirst( $transaction['status'] ) ); ?></td>
			</tr>

			<tr>
				<th>Created</th>
				<td><?php echo esc_html( $transaction['created_at'] ); ?></td>
			</tr>

			<tr>
				<th>Updated</th>
				<td><?php echo esc_html( $transaction['updated_at'] ); ?></td>
			</tr>

		</tbody>

	</table>

</div>

<?php
	}
}
