<?php
/**
 * Buyer Payment Page
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Payment_Page {

	/**
	 * Render buyer payment page.
	 *
	 * @return string
	 */
	public static function render() {

		if ( ! is_user_logged_in() ) {
			return '<p>Please log in to continue.</p>';
		}

		ob_start();

		$transaction_id = isset( $_GET['transaction_id'] )
			? absint( $_GET['transaction_id'] )
			: 0;

		if ( ! $transaction_id ) {
			return '<p>Invalid transaction.</p>';
		}

		$transaction = Flipnzee_Payment_Manager::get_transaction(
			$transaction_id
		);

		if ( ! $transaction ) {
			return '<p>Transaction not found.</p>';
		}

		if ( ! Flipnzee_Payment_Manager::can_pay( $transaction ) ) {
			return '<p>This transaction is no longer available for payment.</p>';
		}

		$gateways = Flipnzee_Payment_Manager::get_available_gateways();

		$payment_completed = false;

		$payment_proof_uploaded = false;

		/*
		|--------------------------------------------------------------------------
		| Upload payment proof
		|--------------------------------------------------------------------------
		*/

		if (
			isset( $_POST['flipnzee_upload_payment_proof'] ) &&
			isset( $_FILES['flipnzee_payment_proof'] ) &&
			! empty( $_FILES['flipnzee_payment_proof']['name'] )
		) {

			if (
				! isset( $_POST['flipnzee_upload_nonce'] ) ||
				! wp_verify_nonce(
					sanitize_text_field(
						wp_unslash(
							$_POST['flipnzee_upload_nonce']
						)
					),
					'flipnzee_upload_proof'
				)
			) {
				return '<p>Security check failed.</p>';
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_id = media_handle_upload(
				'flipnzee_payment_proof',
				0
			);

			if ( ! is_wp_error( $attachment_id ) ) {

				Flipnzee_Payment_Manager::save_payment_proof(
					$transaction->id,
					(int) $attachment_id
				);

				$payment_proof_uploaded = true;

				$transaction = Flipnzee_Payment_Manager::get_transaction(
					$transaction->id
				);
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Handle payment method selection
		|--------------------------------------------------------------------------
		*/

		self::handle_payment_submission(
			$transaction,
			$gateways
		);

		if ( isset( $_POST['flipnzee_payment_completed'] ) ) {

			$payment_completed = true;

		}

		if ( $payment_completed ) :
			?>

			<div class="notice notice-success">

				<p>

					<strong>Payment Submitted.</strong>

					Your payment has been recorded.

				</p>

			</div>

			<?php
		endif;

		if ( $payment_proof_uploaded ) :
			?>

			<div class="notice notice-success">

				<p>

					<strong>Payment proof uploaded successfully.</strong>

				</p>

			</div>

			<?php
		endif;

		self::render_transaction_summary(
			$transaction
		);

		self::render_payment_state(
			$transaction,
			$gateways
		);

		return ob_get_clean();

	}

	/**
	 * Handle payment submission.
	 *
	 * @param object $transaction Transaction.
	 * @param array  $gateways Available gateways.
	 *
	 * @return void
	 */
	private static function handle_payment_submission(
		$transaction,
		$gateways
	) {

		if ( ! isset( $_POST['flipnzee_continue_payment'] ) ) {
			return;
		}

		if (
			! isset( $_POST['flipnzee_payment_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash(
						$_POST['flipnzee_payment_nonce']
					)
				),
				'flipnzee_payment_action'
			)
		) {

			echo '<p>Security check failed.</p>';

			return;

		}

		$gateway = '';

		if ( isset( $_POST['payment_gateway'] ) ) {

			$gateway = sanitize_text_field(
				wp_unslash(
					$_POST['payment_gateway']
				)
			);

		}

		if ( ! isset( $gateways[ $gateway ] ) ) {

			echo '<p>Invalid payment gateway selected.</p>';

			return;

		}

		switch ( $gateway ) {

			case 'manual':

				self::render_manual_payment(
					$transaction
				);

				break;

			case 'escrow':

				?>

				<div class="notice notice-info">

					<p>

						Escrow integration will be available in a future lesson.

					</p>

				</div>

				<?php

				break;

			default:

				?>

				<div class="notice notice-warning">

					<p>

						This payment gateway is not yet available.

					</p>

				</div>

				<?php

				break;

		}

	}

	/**
	 * Render payment workflow.
	 *
	 * @param object $transaction Transaction.
	 * @param array  $gateways Available gateways.
	 *
	 * @return void
	 */
	private static function render_payment_state(
		$transaction,
		$gateways
	) {

		switch ( strtolower( $transaction->payment_status ) ) {

			case 'submitted':

				self::render_submitted_state(
					$transaction
				);

				break;

			case 'verified':

				self::render_verified_state(
					$transaction
				);

				break;

			case 'completed':

				self::render_completed_state(
					$transaction
				);

				break;

			case 'pending':
			default:

				self::render_pending_state(
					$transaction,
					$gateways
				);

				break;

		}

	}

    	/**
	 * Render pending payment state.
	 *
	 * @param object $transaction Transaction.
	 * @param array  $gateways    Available gateways.
	 *
	 * @return void
	 */
	private static function render_pending_state(
		$transaction,
		$gateways
	) {

		self::render_gateway_selector(
			$gateways
		);

	}

	/**
	 * Render submitted state.
	 *
	 * @param object $transaction Transaction.
	 *
	 * @return void
	 */
	private static function render_submitted_state(
		$transaction
	) {
		?>

		<div class="notice notice-success">

			<h3>Payment Submitted</h3>

			<p>

				Your payment proof has been received.

			</p>

			<p>

				Our team is verifying your payment before
				starting the ownership transfer.

			</p>

		</div>

		<?php
	}

	/**
	 * Render verified state.
	 *
	 * @param object $transaction Transaction.
	 *
	 * @return void
	 */
	private static function render_verified_state(
		$transaction
	) {
		?>

		<div class="notice notice-info">

			<h3>Payment Verified</h3>

			<p>

				Your payment has been verified successfully.

			</p>

			<p>

				Ownership transfer has started.

			</p>

		</div>

		<?php
	}

	/**
	 * Render completed state.
	 *
	 * @param object $transaction Transaction.
	 *
	 * @return void
	 */
	private static function render_completed_state(
		$transaction
	) {
		?>

		<div class="notice notice-success">

			<h3>Transaction Completed</h3>

			<p>

				Ownership has been transferred successfully.

			</p>

		</div>

		<?php
	}

	/**
	 * Render transaction summary.
	 *
	 * @param object $transaction Transaction.
	 *
	 * @return void
	 */
	private static function render_transaction_summary(
		$transaction
	) {
		?>

		<h2>Payment</h2>

		<table class="widefat striped">

			<tr>
				<th>Transaction ID</th>
				<td><?php echo esc_html( $transaction->id ); ?></td>
			</tr>

			<tr>
				<th>Winning Bid</th>
				<td>
					<?php
					echo esc_html(
						number_format_i18n(
							$transaction->winning_bid,
							2
						)
					);
					?>
				</td>
			</tr>

			<tr>
				<th>Status</th>

				<td>

					<?php

					$status = strtolower(
						$transaction->status
					);

					$class = 'flipnzee-status-pending';

					if ( 'paid' === $status ) {

						$class = 'flipnzee-status-paid';

					} elseif ( 'completed' === $status ) {

						$class = 'flipnzee-status-completed';

					}

					?>

					<span class="<?php echo esc_attr( $class ); ?>">

						<?php
						echo esc_html(
							ucfirst( $status )
						);
						?>

					</span>

				</td>

			</tr>

			<tr>
				<th>Payment Status</th>

				<td>

					<?php
					echo esc_html(
						ucfirst(
							$transaction->payment_status
						)
					);
					?>

				</td>

			</tr>

			<tr>

				<th>Payment Gateway</th>

				<td>

					<?php
					echo esc_html(
						Flipnzee_Payment_Manager::get_gateway_name(
							$transaction
						)
					);
					?>

				</td>

			</tr>

		</table>

		<?php
	}

	/**
	 * Render gateway selector.
	 *
	 * @param array $gateways Gateways.
	 *
	 * @return void
	 */
	private static function render_gateway_selector(
		$gateways
	) {
		?>

		<form
			method="post"
			enctype="multipart/form-data"
		>

			<?php
			wp_nonce_field(
				'flipnzee_payment_action',
				'flipnzee_payment_nonce'
			);
			?>

			<h3>Select Payment Method</h3>

			<div class="flipnzee-payment-gateways">

				<?php foreach ( $gateways as $gateway_id => $gateway ) : ?>

					<p>

						<label>

							<input
								type="radio"
								name="payment_gateway"
								value="<?php echo esc_attr( $gateway_id ); ?>"
								<?php checked( 'manual' === $gateway_id ); ?>
								<?php disabled( 'manual' !== $gateway_id ); ?>
							>

							<?php echo esc_html( $gateway['label'] ); ?>

							<?php if ( 'manual' !== $gateway_id ) : ?>

								<em>(Coming Soon)</em>

							<?php endif; ?>

						</label>

					</p>

				<?php endforeach; ?>

			</div>

			<p class="flipnzee-payment-actions">

				<button
					type="submit"
					name="flipnzee_continue_payment"
					class="button button-primary"
				>

					View Payment Instructions

				</button>

			</p>

		</form>

		<?php
	}

	/**
	 * Render manual payment.
	 *
	 * @param object $transaction Transaction.
	 *
	 * @return void
	 */
	private static function render_manual_payment(
		$transaction
	) {

		if ( ! empty( $transaction->payment_proof_id ) ) {

			self::render_submitted_state(
				$transaction
			);

			return;

		}

		?>

		<div class="notice notice-success">

			<p>

				<strong>Manual Payment Selected</strong>

			</p>

			<p>

				Please complete your payment using the
				instructions below.

			</p>

		</div>

		<div class="flipnzee-manual-payment">

			<h3>Payment Instructions</h3>

			<table class="widefat striped">

				<tr>

					<th>Reference Number</th>

					<td>

						<?php
						echo esc_html(
							'FLIP-' .
							str_pad(
								$transaction->id,
								6,
								'0',
								STR_PAD_LEFT
							)
						);
						?>

					</td>

				</tr>

				<tr>

					<th>Amount</th>

					<td>

						<?php
						echo esc_html(
							number_format_i18n(
								$transaction->winning_bid,
								2
							)
						);
						?>

					</td>

				</tr>

			</table>

			<h3>Upload Payment Proof</h3>

			<form
				method="post"
				enctype="multipart/form-data"
			>

				<?php
				wp_nonce_field(
					'flipnzee_upload_proof',
					'flipnzee_upload_nonce'
				);
				?>

				<input
					type="hidden"
					name="transaction_id"
					value="<?php echo esc_attr( $transaction->id ); ?>"
				>

				<p>

					<input
						type="file"
						name="flipnzee_payment_proof"
						accept=".jpg,.jpeg,.png,.pdf"
						required
					>

				</p>

				<p>

					<button
						type="submit"
						name="flipnzee_upload_payment_proof"
						class="button button-primary"
					>

						Upload Payment Proof

					</button>

				</p>

			</form>

		</div>

		<?php
	}

}