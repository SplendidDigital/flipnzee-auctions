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

    return '<p>No purchase selected.</p>';
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

$listing_id = absint( $transaction['listing_id'] );

$thumbnail = get_the_post_thumbnail(
	$listing_id,
	'medium'
);

$listing_url = get_permalink( $listing_id );
$status_class =
	'flipnzee-status-' .
	sanitize_html_class(
		strtolower( $transaction['status'] )
	);
	$listing_title = get_the_title( $transaction['listing_id'] );

$listing_url = get_permalink( $transaction['listing_id'] );

$listing_thumbnail = get_the_post_thumbnail(
	$transaction['listing_id'],
	'medium'
);

$reference = sprintf(
	'FLIP-%s-%06d',
	gmdate( 'Y' ),
	$transaction['id']
);
$purchase_date = date_i18n(
	get_option( 'date_format' ),
	strtotime( $transaction['created_at'] )
);

$purchase_time = date_i18n(
	get_option( 'time_format' ),
	strtotime( $transaction['created_at'] )
);

$payment_method = 'Manual';

$transfer_steps = array(

	array(
		'label'     => 'Payment Confirmed',
		'completed' => true,
	),

	array(
		'label'     => 'Website Files Delivered',
		'completed' => false,
	),

	array(
		'label'     => 'Database Delivered',
		'completed' => false,
	),

	array(
		'label'     => 'Domain Transfer Completed',
		'completed' => false,
	),

	array(
		'label'     => 'Buyer Verification',
		'completed' => false,
	),

	array(
		'label'     => 'Purchase Completed',
		'completed' => false,
	),
);

if ( ! empty( $transaction['payment_method'] ) ) {

	$payment_method = $transaction['payment_method'];
}
ob_start();
?>

<h2>Purchase Details</h2>

<div class="flipnzee-purchase-header">

	<?php if ( ! empty( $thumbnail ) ) : ?>

	<div class="flipnzee-purchase-image">

		<?php echo $thumbnail; ?>

	</div>

<?php endif; ?>

	<div class="flipnzee-purchase-summary">

		<h3>

			<?php
			echo esc_html(
				get_the_title( $listing_id )
			);
			?>

		</h3>

		<p>

			<span class="<?php echo esc_attr( $status_class ); ?>">

				<?php
				echo esc_html(
					ucfirst( $transaction['status'] )
				);
				?>

			</span>

		</p>

		<p>

			<strong>Winning Bid:</strong>

			₹<?php
			echo number_format(
				$transaction['winning_bid'],
				2
			);
			?>

		</p>

		<p>

			<strong>Purchased:</strong>

			<?php
			echo esc_html(
				$transaction['created_at']
			);
			?>

		</p>

		<p>

			<a
				class="flipnzee-view-listing-button"
				href="<?php echo esc_url( $listing_url ); ?>"
			>

				View Listing

			</a>

		</p>

	</div>

</div>



<h3>Purchase Timeline</h3>

<ul class="flipnzee-purchase-timeline">

	<li class="completed">
		Auction Won
	</li>

	<li class="completed">
		Payment Received
	</li>

	<?php if ( 'completed' === strtolower( $transaction['status'] ) ) : ?>

		<li class="completed">
			Website Transfer Completed
		</li>

		<li class="completed">
			Purchase Completed
		</li>

	<?php else : ?>

		<li>
			Website Transfer Pending
		</li>

		<li>
			Purchase Pending
		</li>

	<?php endif; ?>

</ul>

<table class="widefat striped" style="max-width:800px;">

	<tbody>

		<tr>
			<th>Transaction ID</th>
			<td><?php echo esc_html( $transaction['id'] ); ?></td>
		</tr>

		<tr>

	<th>Reference</th>

	<td>

		<?php echo esc_html( $reference ); ?>

	</td>

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

	<th>Purchase Date</th>

	<td>

		<?php echo esc_html( $purchase_date ); ?>

	</td>

</tr>

<tr>

	<th>Purchase Time</th>

	<td>

		<?php echo esc_html( $purchase_time ); ?>

	</td>

</tr>

<tr>

	<th>Payment Method</th>

	<td>

		<?php echo esc_html( $payment_method ); ?>

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

        <span class="<?php echo esc_attr( $status_class ); ?>">

            <?php
            echo esc_html(
                ucfirst( $transaction['status'] )
            );
            ?>

        </span>

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

<div class="flipnzee-purchase-info">

	<h3>Purchase Information</h3>

	<div class="flipnzee-info-box">

		<h4>Buyer Protection</h4>

		<p>

			Your payment has been securely recorded.
			Our team verifies the website transfer
			before marking the transaction complete.

		</p>

	</div>

	<div class="flipnzee-info-box">

		<h4>Ownership Transfer</h4>

		<p>

			The seller will provide the necessary
			website files, database and domain
			transfer instructions.

		</p>

	</div>

	<div class="flipnzee-info-box">

		<h4>Need Help?</h4>

		<p>

			If you experience any issue during
			the transfer process,
			please contact our support team.

		</p>

	</div>

</div>

<div class="flipnzee-next-steps">

	<h3>Next Steps</h3>

	<ul class="flipnzee-transfer-progress">

<?php foreach ( $transfer_steps as $step ) : ?>

	<li
		class="<?php echo $step['completed']
			? 'completed'
			: 'pending'; ?>">

		<?php
		echo $step['completed']
			? '✓ '
			: '○ ';

		echo esc_html(
			$step['label']
		);
		?>

	</li>

<?php endforeach; ?>

</ul>
</div>

<div class="flipnzee-purchase-notes">

    <h3>Purchase Notes</h3>

    <div class="flipnzee-note-box">

        <p>

            No notes available.

        </p>

    </div>

</div>


<div class="flipnzee-purchase-actions">

	<a
		class="flipnzee-dashboard-button"
		href="<?php echo esc_url( site_url( '/my-purchases/' ) ); ?>"
	>

		← Back to My Purchases

	</a>

	<a
		class="flipnzee-dashboard-button"
		href="<?php echo esc_url( site_url( '/listings/' ) ); ?>"
	>

		Browse Auctions

	</a>

	<a
		class="flipnzee-dashboard-button"
		href="<?php echo esc_url( site_url( '/support/' ) ); ?>"
	>

		Contact Support

	</a>

</div>

<?php

return ob_get_clean();
	}
}