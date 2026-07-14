<?php
/**
 * Buyer Purchase Details.
 *
 * @package Flipnzee_Auctions
 */

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

				"SELECT *
				FROM {$table}
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

		$listing_id = absint(
			$transaction['listing_id']
		);

		$listing_title = get_the_title(
			$listing_id
		);

		$listing_url = get_permalink(
			$listing_id
		);

		$thumbnail = get_the_post_thumbnail(
			$listing_id,
			'medium'
		);

		$status_class =
			'flipnzee-status-' .
			sanitize_html_class(
				strtolower(
					$transaction['status']
				)
			);

		$reference = sprintf(

			'FLIP-%s-%06d',

			gmdate( 'Y' ),

			$transaction['id']

		);

		$purchase_date = date_i18n(

			get_option( 'date_format' ),

			strtotime(
				$transaction['created_at']
			)

		);

		$purchase_time = date_i18n(

			get_option( 'time_format' ),

			strtotime(
				$transaction['created_at']
			)

		);

		$payment_method = ! empty(
			$transaction['payment_method']
		)
			? $transaction['payment_method']
			: 'Manual';

		/*
		 * Transfer Manager.
		 */
		$transfer =
    Flipnzee_Transfer_Manager::get_transfer(
        $transaction['id']
    );

$transfer_status =
    Flipnzee_Transfer_Manager::get_default_status();

$transfer_steps =
    Flipnzee_Transfer_Manager::get_default_steps();

if ( ! empty( $transfer ) ) {

    $transfer_status = array(

        'payment' => $transfer['payment'],

        'files' => $transfer['files'],

        'database' => $transfer['database'],

        'domain' => $transfer['domain'],

        'buyer' => $transfer['buyer'],

    );

    $transfer_steps =
        Flipnzee_Transfer_Manager::build_steps(
            $transfer
        );

}
$status_badges =
    Flipnzee_Transfer_Manager::get_status_badges();
		ob_start();
?>

<h2>Purchase Details</h2>

<div class="flipnzee-purchase-header">

	<?php if ( ! empty( $thumbnail ) ) : ?>

		<div class="flipnzee-purchase-image">

			<?php echo wp_kses_post( $thumbnail ); ?>

		</div>

	<?php endif; ?>

	<div class="flipnzee-purchase-summary">

		<h3><?php echo esc_html( $listing_title ); ?></h3>

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
echo esc_html(
    number_format(
        $transaction['winning_bid'],
        2
    )
);
?>
		</p>

		<p>

			<strong>Purchased:</strong>

			<?php echo esc_html( $purchase_date ); ?>

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

	<?php foreach ( $transfer_steps as $step ) : ?>

		<li
			class="<?php
			echo $step['completed']
				? 'completed'
				: '';
			?>">

			<?php
			echo esc_html(
				$step['label']
			);
			?>

		</li>

	<?php endforeach; ?>

</ul>

<table
	class="widefat striped"
	style="max-width:800px;"
>

	<tbody>

		<tr>

			<th>Transaction ID</th>

			<td>

				<?php
				echo esc_html(
					$transaction['id']
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Reference</th>

			<td>

				<?php
				echo esc_html(
					$reference
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Auction</th>

			<td>

				<?php
				echo esc_html(
					$listing_title
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Purchase Date</th>

			<td>

				<?php
				echo esc_html(
					$purchase_date
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Purchase Time</th>

			<td>

				<?php
				echo esc_html(
					$purchase_time
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Payment Method</th>

			<td>

				<?php
				echo esc_html(
					$payment_method
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Winning Bid</th>

			<td>

				₹<?php
				echo number_format(
					$transaction['winning_bid'],
					2
				);
				?>

			</td>

		</tr>

		<tr>

			<th>Status</th>

			<td>

				<span class="<?php echo esc_attr( $status_class ); ?>">

					<?php
					echo esc_html(
						ucfirst(
							$transaction['status']
						)
					);
					?>

				</span>

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

			The website owner will provide the
			website files, database and
			domain transfer instructions.

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

<div class="flipnzee-transfer-status">

	<h3>Transfer Status</h3>

	<div class="flipnzee-transfer-card">

		<p>

			The progress below reflects the latest
			transfer information.

		</p>

		<table
			class="widefat striped"
			style="max-width:700px;"
		>

			<tbody>

			<?php foreach ( $transfer_status as $key => $value ) : ?>

				<tr>

					<th>

						<?php

						$labels = array(

							'payment' => 'Payment',

							'files' => 'Website Files',

							'database' => 'Database',

							'domain' => 'Domain Transfer',

							'buyer' => 'Buyer Verification',

						);

						echo esc_html(
							$labels[ $key ]
						);

						?>

					</th>

					<td>

						<span
							class="<?php
							echo esc_attr(
								$status_badges[
									$value
								]
							);
							?>">

							<?php
							echo esc_html(
								$value
							);
							?>

						</span>

					</td>

				</tr>

			<?php endforeach; ?>

			</tbody>

		</table>

	</div>

</div>

<div class="flipnzee-next-steps">

	<h3>Next Steps</h3>

	<ul class="flipnzee-transfer-progress">

		<?php foreach ( $transfer_steps as $step ) : ?>

			<li
				class="<?php
				echo $step['completed']
					? 'completed'
					: 'pending';
				?>">

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

			Thank you for purchasing through
			Flipnzee Auctions.

		</p>

		<ul>

			<li>

				Verify the website after receiving
				all files.

			</li>

			<li>

				Change all passwords immediately
				after the transfer is completed.

			</li>

			<li>

				Confirm that domain ownership has
				been transferred successfully.

			</li>

			<li>

				Contact support if you require
				assistance during the transfer.

			</li>

		</ul>

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