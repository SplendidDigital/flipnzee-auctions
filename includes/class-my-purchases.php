<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buyer Purchases Shortcode
 */
class Flipnzee_My_Purchases {

	/**
	 * Render My Purchases.
	 */
	public static function render() {

		if ( ! is_user_logged_in() ) {

			return '<p>Please log in to view your purchases.</p>';
		}

		global $wpdb;

$table = $wpdb->prefix . 'flipnzee_transactions';

$purchases = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT *
		FROM {$table}
		WHERE buyer_id = %d
		ORDER BY created_at DESC",
		get_current_user_id()
	),
	ARRAY_A
);

if ( empty( $purchases ) ) {

	return '<p>You have not purchased any auctions yet.</p>';
}

ob_start();
?>

<h2>My Purchases</h2>

<table class="flipnzee-my-purchases">

	<thead>

		<tr>

			<th>Auction</th>
<th>Winning Bid</th>
<th>Status</th>
<th>Purchased</th>
<th>Details</th>

		</tr>

	</thead>

	<tbody>

		<?php foreach ( $purchases as $purchase ) : ?>

			<tr>

				<td>

<?php

$title = get_the_title( $purchase['listing_id'] );

$url = get_permalink( $purchase['listing_id'] );

$details_url = add_query_arg(
    'transaction_id',
    $purchase['id'],
    get_permalink()
);

$payment_url = add_query_arg(
    'transaction_id',
    $purchase['id'],
    site_url( '/payment/' )
);

?>

<a href="<?php echo esc_url( $url ); ?>">
    <?php echo esc_html( $title ); ?>
</a>

</td>

				<td>

	₹<?php

	echo esc_html(
		number_format(
			(float) $purchase['winning_bid'],
			2
		)
	);

	?>

</td>

				<td>
					<?php echo esc_html(
						ucfirst( $purchase['status'] )
					); ?>
				</td>

				<td>
					<?php echo esc_html( $purchase['created_at'] ); ?>
				</td>
               <td>

<?php

$details_url = add_query_arg(
    'transaction_id',
    $purchase['id'],
    site_url( '/testing/' )
);

if ( 'pending' === strtolower( $purchase['status'] ) ) :

    $payment_url = add_query_arg(
        'transaction_id',
        $purchase['id'],
        site_url( '/payment/' )
    );
?>

    <a href="<?php echo esc_url( $payment_url ); ?>">
    Pay Now
</a>

<br>

<a href="<?php echo esc_url( $details_url ); ?>">
    View Details
</a>
<?php else : ?>

    <a href="<?php echo esc_url( $details_url ); ?>">
        View Details
    </a>

<?php endif; ?>

</td>

			</tr>

		<?php endforeach; ?>

	</tbody>

</table>

<?php

return ob_get_clean();
	}
}
