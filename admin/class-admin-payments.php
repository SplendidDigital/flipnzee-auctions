<?php
/**
 * Admin Payments Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Admin_Payments {

	/**
	 * Render Payments page.
	 *
	 * @return void
	 */
	public static function render_page() {

		?>

		<div class="wrap">

			<h1>Buyer Payments</h1>

<p>
    Review buyer payment submissions before approving
    the transfer of ownership.
</p>

<?php

global $wpdb;

$table = $wpdb->prefix . 'flipnzee_transactions';

$payments = $wpdb->get_results(
    "
    SELECT *
    FROM {$table}
    WHERE payment_status = 'submitted'
    ORDER BY updated_at DESC
    "
);

if ( empty( $payments ) ) {

    echo '<p>No payment submissions found.</p>';

} else {

?>

<table class="widefat striped">

    <thead>

        <tr>

            <th>ID</th>

            <th>Listing</th>

            <th>Buyer</th>

            <th>Amount</th>

            <th>Gateway</th>

            <th>Status</th>

            <th>Submitted</th>

            <th>Actions</th>

        </tr>

    </thead>

    <tbody>

    <?php foreach ( $payments as $payment ) : ?>

        <tr>

            <td><?php echo esc_html( $payment->id ); ?></td>

            <td><?php echo esc_html( $payment->listing_id ); ?></td>

            <td><?php echo esc_html( $payment->buyer_id ); ?></td>

            <td><?php echo esc_html( number_format_i18n( $payment->winning_bid, 2 ) ); ?></td>

            <td><?php echo esc_html( $payment->payment_gateway ); ?></td>

            <td><?php echo esc_html( ucfirst( $payment->payment_status ) ); ?></td>

            <td><?php echo esc_html( $payment->updated_at ); ?></td>

            <td>

    <a
        class="button button-primary"
        href="<?php echo esc_url(
            admin_url(
                'admin.php?page=flipnzee-transaction-details&transaction_id=' .
                $payment->id
            )
        ); ?>">

        View Details

    </a>

</td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php

}


?>
		</div>

		<?php
	}
}