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
if ( isset( $_GET['transfer_updated'] ) ) {
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong>Transfer updated successfully.</strong></p>
	</div>
	<?php
}
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
		<?php
if ( isset( $_GET['updated'] ) ) {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <?php esc_html_e(
                'Payment status updated successfully.',
                'flipnzee-auctions'
            ); ?>
        </p>
    </div>
    <?php
}
?>

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
				<th>Created</th>
				<td><?php echo esc_html( $transaction['created_at'] ); ?></td>
			</tr>

			<tr>
				<th>Updated</th>
				<td><?php echo esc_html( $transaction['updated_at'] ); ?></td>
			</tr>
			<tr>
    <th>Payment Status</th>
    <td><?php echo esc_html( ucfirst( $transaction['payment_status'] ) ); ?></td>
</tr>

<tr>
    <th>Payment Gateway</th>
    <td><?php echo esc_html( $transaction['payment_gateway'] ); ?></td>
</tr>

<tr>
    <th>Payment Submitted</th>
    <td><?php echo esc_html( $transaction['payment_submitted_at'] ); ?></td>
</tr>

<tr>

	<th>Payment Proof</th>

	<td>

		<?php if ( ! empty( $transaction['payment_proof_id'] ) ) : ?>

			<?php
			echo wp_get_attachment_link(
				$transaction['payment_proof_id'],
				'thumbnail',
				false,
				true
			);
			?>

			<br><br>

			<a
				class="button"
				target="_blank"
				href="<?php echo esc_url(
					wp_get_attachment_url(
						$transaction['payment_proof_id']
					)
				); ?>"
			>

				View Original

			</a>

		<?php else : ?>

			No payment proof uploaded.

		<?php endif; ?>

	</td>

</tr>

		</tbody>

		</table>

	<hr>

	<h2><?php esc_html_e( 'Payment Management', 'flipnzee-auctions' ); ?></h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

		<?php
wp_nonce_field(
    'flipnzee_update_payment_status',
    'flipnzee_payment_nonce'
);
?>

<input
    type="hidden"
    name="action"
    value="flipnzee_update_payment_status">
		<input
			type="hidden"
			name="transaction_id"
			value="<?php echo esc_attr( $transaction['id'] ); ?>">

		<table class="form-table">

			<tr>

				<th scope="row">
					<label for="payment_status">
						<?php esc_html_e( 'Payment Status', 'flipnzee-auctions' ); ?>
					</label>
				</th>

				<td>
<select
	name="payment_status"
	id="payment_status"
>

	<option
		value="pending"
		<?php selected( $transaction['payment_status'], 'pending' ); ?>
	>
		Pending
	</option>

	<option
		value="submitted"
		<?php selected( $transaction['payment_status'], 'submitted' ); ?>
	>
		Submitted
	</option>

	<option
		value="verified"
		<?php selected( $transaction['payment_status'], 'verified' ); ?>
	>
		Verified
	</option>

	<option
		value="completed"
		<?php selected( $transaction['payment_status'], 'completed' ); ?>
	>
		Completed
	</option>

</select>

				</td>

			</tr>

		</table>

		<?php
		submit_button(
			__( 'Update Payment Status', 'flipnzee-auctions' )
		);
		?>

	</form>

	<?php

/*
|--------------------------------------------------------------------------
| External Provider Information
|--------------------------------------------------------------------------
*/

$provider =
	Flipnzee_External_Provider_Manager::get_provider_by_transaction(
		$transaction['id']
	);

?>

<hr>

<h2>

	<?php esc_html_e(
		'External Provider',
		'flipnzee-auctions'
	); ?>

</h2>

<?php if ( empty( $provider ) ) : ?>

	<div class="notice notice-warning inline">

		<p>

			<?php esc_html_e(
				'No external provider record exists for this transaction.',
				'flipnzee-auctions'
			); ?>

		</p>

	</div>

<?php else : ?>

<table
	class="widefat striped"
	style="max-width:900px;">

	<tbody>

	<tr>

		<th style="width:220px;">

			<?php esc_html_e(
				'Provider',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<?php
			echo esc_html(
				$provider['provider']
			);
			?>

		</td>

	</tr>

	<tr>

		<th>

			<?php esc_html_e(
				'Reference',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<code>

			<?php

			echo esc_html(
				$provider['provider_reference']
			);

			?>

			</code>

		</td>

	</tr>

	<tr>

		<th>

			<?php esc_html_e(
				'Status',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<?php

			$status =
				$provider['status'];

			$color = '#777';

			switch ( strtolower( $status ) ) {

				case 'created':

					$color = '#2271b1';

					break;

				case 'completed':

					$color = '#008a20';

					break;

				case 'failed':

					$color = '#d63638';

					break;

				case 'pending':

					$color = '#dba617';

					break;

			}

			?>

			<span
				style="
					background: <?php echo esc_attr( $color ); ?>;
					color:#fff;
					padding:5px 10px;
					border-radius:4px;
					font-weight:600;
				">

				<?php

				echo esc_html(
					ucfirst( $status )
				);

				?>

			</span>

		</td>

	</tr>

	<tr>

		<th>

			<?php esc_html_e(
				'Started',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<?php

			echo esc_html(
				$provider['started_at']
			);

			?>

		</td>

	</tr>

	<tr>

		<th>

			<?php esc_html_e(
				'Completed',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<?php

			echo ! empty(
				$provider['completed_at']
			)

			? esc_html(
				$provider['completed_at']
			)

			: '&mdash;';

			?>

		</td>

	</tr>

	<tr>

		<th>

			<?php esc_html_e(
				'Notes',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<?php

			echo ! empty(
				$provider['notes']
			)

			? nl2br(
				esc_html(
					$provider['notes']
				)
			)

			: '&mdash;';

			?>

		</td>

	</tr>

	</tbody>

</table>

<?php endif; ?>


	<?php

$transfer = Flipnzee_Transfer_Manager::get_transfer(
	$transaction['id']
);

if ( ! empty( $transfer ) ) :

	$progress = Flipnzee_Transfer_Manager::get_progress(
		$transaction['id']
	);

	$overall = Flipnzee_Transfer_Manager::get_overall_status(
		$transaction['id']
	);

?>

<hr>

<h2><?php esc_html_e( 'Ownership Transfer', 'flipnzee-auctions' ); ?></h2>

<table class="widefat striped" style="max-width:900px;">

	<tbody>

	<tr>

		<th style="width:220px;">

			<?php esc_html_e(
				'Overall Progress',
				'flipnzee-auctions'
			); ?>

		</th>

		<td>

			<strong>

				<?php

				echo esc_html(
					$progress['completed']
				);

				?>

				/

				<?php

				echo esc_html(
					$progress['total']
				);

				?>

			</strong>

			<br><br>

			<progress
				value="<?php echo esc_attr(
					$progress['percentage']
				); ?>"
				max="100"
				style="width:350px;height:20px;">
			</progress>

			<br><br>

			<strong>

				<?php

				echo esc_html(
					$progress['percentage']
				);

				?>

				%

			</strong>

			&nbsp;

			<span
				class="button"
				style="cursor:default;">

				<?php echo esc_html( $overall ); ?>

			</span>

		</td>

	</tr>

	</tbody>

</table>

<br>

<form
	method="post"
	action="<?php echo esc_url(
		admin_url(
			'admin-post.php'
		)
	); ?>">

	<?php

	wp_nonce_field(
		'flipnzee_update_transfer',
		'flipnzee_transfer_nonce'
	);

	?>

	<input
		type="hidden"
		name="action"
		value="flipnzee_update_transfer">

	<input
		type="hidden"
		name="transaction_id"
		value="<?php echo esc_attr(
			$transaction['id']
		); ?>">

	<table class="form-table">

		<tr>

			<th>

				<?php esc_html_e(
					'Payment',
					'flipnzee-auctions'
				); ?>

			</th>

			<td>

				<select name="payment_status">

					<option value="Pending"
						<?php selected(
							$transfer['payment_status'],
							'Pending'
						); ?>>

						Pending

					</option>

					<option value="In Progress"
						<?php selected(
							$transfer['payment_status'],
							'In Progress'
						); ?>>

						In Progress

					</option>

					<option value="Completed"
						<?php selected(
							$transfer['payment_status'],
							'Completed'
						); ?>>

						Completed

					</option>

				</select>

			</td>

		</tr>

		<tr>

			<th>Website Files</th>

			<td>

				<select name="files_status">

					<option value="Pending"
						<?php selected(
							$transfer['files_status'],
							'Pending'
						); ?>>

						Pending

					</option>

					<option value="In Progress"
						<?php selected(
							$transfer['files_status'],
							'In Progress'
						); ?>>

						In Progress

					</option>

					<option value="Completed"
						<?php selected(
							$transfer['files_status'],
							'Completed'
						); ?>>

						Completed

					</option>

				</select>

			</td>

		</tr>

		<tr>

			<th>Database</th>

			<td>

				<select name="database_status">

					<option value="Pending"
						<?php selected(
							$transfer['database_status'],
							'Pending'
						); ?>>

						Pending

					</option>

					<option value="In Progress"
						<?php selected(
							$transfer['database_status'],
							'In Progress'
						); ?>>

						In Progress

					</option>

					<option value="Completed"
						<?php selected(
							$transfer['database_status'],
							'Completed'
						); ?>>

						Completed

					</option>

				</select>

			</td>

		</tr>

		<tr>

			<th>Domain</th>

			<td>

				<select name="domain_status">

					<option value="Pending"
						<?php selected(
							$transfer['domain_status'],
							'Pending'
						); ?>>

						Pending

					</option>

					<option value="In Progress"
						<?php selected(
							$transfer['domain_status'],
							'In Progress'
						); ?>>

						In Progress

					</option>

					<option value="Completed"
						<?php selected(
							$transfer['domain_status'],
							'Completed'
						); ?>>

						Completed

					</option>

				</select>

			</td>

		</tr>

		<tr>

			<th>Buyer</th>

			<td>

				<select name="buyer_status">

					<option value="Pending"
						<?php selected(
							$transfer['buyer_status'],
							'Pending'
						); ?>>

						Pending

					</option>

					<option value="In Progress"
						<?php selected(
							$transfer['buyer_status'],
							'In Progress'
						); ?>>

						In Progress

					</option>

					<option value="Completed"
						<?php selected(
							$transfer['buyer_status'],
							'Completed'
						); ?>>

						Completed

					</option>

				</select>

			</td>

		</tr>

		<tr>

			<th>

				<?php esc_html_e(
					'Notes',
					'flipnzee-auctions'
				); ?>

			</th>

			<td>

				<textarea
					name="notes"
					rows="5"
					style="width:100%;"><?php

					echo esc_textarea(
						$transfer['notes']
					);

					?></textarea>

			</td>

		</tr>

	</table>

	<?php

	submit_button(
		__(
			'Save Transfer',
			'flipnzee-auctions'
		)
	);

	?>

</form>

<?php endif; ?>

</div>

<?php
	}
	public static function update_payment_status() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Permission denied.' );
    }

    check_admin_referer(
        'flipnzee_update_payment_status',
        'flipnzee_payment_nonce'
    );

    global $wpdb;

    $transaction_id = isset( $_POST['transaction_id'] )
        ? absint( $_POST['transaction_id'] )
        : 0;

    $payment_status = isset( $_POST['payment_status'] )
        ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) )
        : '';

	



    $status = 'pending';

if ( 'completed' === $payment_status ) {

	$status = 'completed';

}
		$wpdb->update(
        $wpdb->prefix . 'flipnzee_transactions',
        array(
    'payment_status' => $payment_status,
    'status'         => $status,
    'updated_at'     => current_time( 'mysql' ),
),
        array(
            'id' => $transaction_id,
        ),
        array(
            '%s',
            '%s',
			'%s',
        ),
        array(
            '%d',
        )
    );
Flipnzee_Activity_Log::log(
	sprintf(
		'Payment status for transaction #%d changed to %s.',
		$transaction_id,
		$payment_status
	)

);

error_log(
	'FLIPNZEE ADMIN: About to fire payment_completed action.'
);

if ( 'completed' === $payment_status ) {

	error_log(
		sprintf(
			'FLIPNZEE ADMIN: Firing payment_completed action for transaction #%d.',
			$transaction_id
		)
	);

	do_action(
		'flipnzee_payment_completed',
		$transaction_id
	);

}
    wp_safe_redirect(
        admin_url(
            'admin.php?page=flipnzee-transaction-details&transaction_id=' .
            $transaction_id .
            '&updated=1'
        )
    );

    exit;
}

/**
 * Update ownership transfer.
 */
public static function update_transfer() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__(
				'Permission denied.',
				'flipnzee-auctions'
			)
		);
	}

	check_admin_referer(
		'flipnzee_update_transfer',
		'flipnzee_transfer_nonce'
	);

	$transaction_id = isset( $_POST['transaction_id'] )
		? absint( wp_unslash( $_POST['transaction_id'] ) )
		: 0;

	$data = array(
		'payment_status' => isset( $_POST['payment_status'] )
			? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) )
			: 'Pending',

		'files_status' => isset( $_POST['files_status'] )
			? sanitize_text_field( wp_unslash( $_POST['files_status'] ) )
			: 'Pending',

		'database_status' => isset( $_POST['database_status'] )
			? sanitize_text_field( wp_unslash( $_POST['database_status'] ) )
			: 'Pending',

		'domain_status' => isset( $_POST['domain_status'] )
			? sanitize_text_field( wp_unslash( $_POST['domain_status'] ) )
			: 'Pending',

		'buyer_status' => isset( $_POST['buyer_status'] )
			? sanitize_text_field( wp_unslash( $_POST['buyer_status'] ) )
			: 'Pending',

		'notes' => isset( $_POST['notes'] )
			? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) )
			: '',
	);

	$result = Flipnzee_Transfer_Manager::update_transfer(
		$transaction_id,
		$data
	);

	Flipnzee_Activity_Log::log(
    'ownership_transfer_updated',
    0,
    get_current_user_id(),
    sprintf(
        'Ownership transfer updated for transaction #%d.',
        $transaction_id
    )
);

	if ( $result ) {

		Flipnzee_Transfer_Manager::maybe_complete_transfer(
			$transaction_id
		);

		if ( 'Completed' === Flipnzee_Transfer_Manager::get_overall_status( $transaction_id ) ) {

    Flipnzee_Activity_Log::log(
        'ownership_transfer_completed',
        0,
        get_current_user_id(),
        sprintf(
            'Ownership transfer completed for transaction #%d.',
            $transaction_id
        )
    );

}

		Flipnzee_Activity_Log::log(
			sprintf(
				'Ownership transfer updated for transaction #%d.',
				$transaction_id
			)
		);
	}

	wp_safe_redirect(
		admin_url(
			'admin.php?page=flipnzee-transaction-details&transaction_id=' .
			$transaction_id .
			'&transfer_updated=1'
		)
	);

	exit;
}

}
