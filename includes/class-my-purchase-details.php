<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buyer Purchase Details Shortcode.
 */
class Flipnzee_My_Purchase_Details {

	/**
	 * Render purchase details.
	 *
	 * @return string
	 */
	public static function render() {

		if ( ! is_user_logged_in() ) {

			return '<p>Please log in to view this purchase.</p>';
		}
        $transaction_id = isset( $_GET['transaction_id'] )
	? absint( $_GET['transaction_id'] )
	: 0;

if ( ! $transaction_id ) {

    return '';
}

		global $wpdb;

$table = $wpdb->prefix . 'flipnzee_transactions';

$transaction = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM {$table}
		WHERE id = %d
		AND buyer_id = %d",
		$transaction_id,
		get_current_user_id()
	),
	ARRAY_A
);

if ( ! $transaction ) {

	return '<p>Transaction not found.</p>';
}

ob_start();
?>

<h2>Purchase Details</h2>

<table class="widefat striped" style="max-width:800px;">

	<tbody>

		<tr>
			<th>Transaction ID</th>
			<td><?php echo esc_html( $transaction['id'] ); ?></td>
		</tr>

		<tr>
			<th>Auction</th>
			<td>
				<?php
				echo esc_html(
					get_the_title( $transaction['listing_id'] )
				);
				?>
			</td>
		</tr>

		<tr>
			<th>Winning Bid</th>
			<td>
				₹<?php echo number_format( $transaction['winning_bid'], 2 ); ?>
			</td>
		</tr>

		<tr>
			<th>Status</th>
			<td>
				<?php echo esc_html( ucfirst( $transaction['status'] ) ); ?>
			</td>
		</tr>

		<tr>
			<th>Purchased</th>
			<td>
				<?php echo esc_html( $transaction['created_at'] ); ?>
			</td>
		</tr>

	</tbody>

</table>

<?php

return ob_get_clean();
	}
}