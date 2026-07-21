<?php
/**
 * Buyer Payment Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Payment_Page {

	public static function render() {

		if ( ! is_user_logged_in() ) {
			return '<p>Please log in to continue.</p>';
		}

		ob_start();

$transaction_id = isset( $_GET['transaction_id'] )
	? absint( $_GET['transaction_id'] )
	: 0;

if ( ! $transaction_id ) {
	?>
	<p>Invalid transaction.</p>
	<?php
	return ob_get_clean();
}

$transaction = Flipnzee_Payment_Manager::get_transaction(
    $transaction_id
);

error_log(
	'PAYMENT PAGE TRANSACTION: ' .
	print_r( $transaction, true )
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
$payment_proof_attachment_id = 0;
$payment_proof_already_uploaded =
    (
        $transaction->payment_status === 'submitted'
        &&
        ! empty( $transaction->payment_proof_id )
    );

if ( isset( $_POST['flipnzee_payment_completed'] ) ) {

    $payment_completed = true;

}

if (
    isset( $_POST['flipnzee_upload_payment_proof'] ) &&
    isset( $_FILES['flipnzee_payment_proof'] ) &&
    ! empty( $_FILES['flipnzee_payment_proof']['name'] )
) {
	if (
    ! isset( $_POST['flipnzee_upload_nonce'] ) ||
    ! wp_verify_nonce(
        sanitize_text_field(
            wp_unslash( $_POST['flipnzee_upload_nonce'] )
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

    $payment_proof_uploaded      = true;
    $payment_proof_attachment_id = (int) $attachment_id;

    Flipnzee_Payment_Manager::save_payment_proof(
        $transaction->id,
        $payment_proof_attachment_id
    );
	$transaction = Flipnzee_Payment_Manager::get_transaction(
    $transaction->id
);

}
}
if ( isset( $_POST['flipnzee_continue_payment'] ) ) {

    if (
        ! isset( $_POST['flipnzee_payment_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field(
                wp_unslash( $_POST['flipnzee_payment_nonce'] )
            ),
            'flipnzee_payment_action'
        )
    ) {
        return '<p>Security check failed.</p>';
    }

    $selected_gateway = '';

    if ( isset( $_POST['payment_gateway'] ) ) {

        $selected_gateway = sanitize_text_field(
            wp_unslash( $_POST['payment_gateway'] )
        );

        if ( ! isset( $gateways[ $selected_gateway ] ) ) {
            return '<p>Invalid payment gateway selected.</p>';
        }

        switch ( $selected_gateway ) {

           case 'manual':

    self::render_manual_payment( $transaction );

    break;

            case 'escrow':
                ?>
                <div class="notice notice-info">
                    <p>
                        Escrow.com integration will be available in a future release.
                    </p>
                </div>
                <?php
                break;

            case 'stripe':
            case 'paypal':
            case 'razorpay':
            case 'crypto':
                ?>
                <div class="notice notice-warning">
                    <p>
                        This payment gateway is not yet available.
                    </p>
                </div>
                <?php
                break;

            default:
                ?>
                <div class="notice notice-error">
                    <p>Unknown payment gateway.</p>
                </div>
                <?php
                break;
        }
    }
}
?>
<?php if ( $payment_completed ) : ?>

<div class="notice notice-success">

    <p>

        <strong>Payment Submitted.</strong>

        Your payment has been recorded and is awaiting verification.

    </p>

</div>

<?php endif; ?>

<?php if ( $payment_proof_uploaded ) : ?>

<div class="notice notice-success">

    <p>

        <strong>Payment proof detected.</strong>

        The uploaded file has been received and will be processed in the next step.

    </p>

</div>

<?php endif; ?>

<?php
self::render_transaction_summary( $transaction );
self::render_gateway_selector( $gateways );

return ob_get_clean();
    }

    private static function render_transaction_summary( $transaction ) {
?>

<h2>Payment</h2>

<table class="widefat striped">

<tr>
    <th>Transaction ID</th>
    <td><?php echo esc_html( $transaction->id ); ?></td>
</tr>

<tr>
    <th>Winning Bid</th>
    <td><?php echo esc_html( number_format_i18n( $transaction->winning_bid, 2 ) ); ?></td>
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
    <td><?php echo esc_html( ucfirst( $transaction->payment_status ) ); ?></td>
</tr>

<tr>
    <th>Payment Gateway</th>
    <td>
        <?php
        echo esc_html(
            Flipnzee_Payment_Manager::get_gateway_name( $transaction )
        );
        ?>
    </td>
</tr>

</table>

<?php
    }
	private static function render_gateway_selector( $gateways ) {
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
    <?php checked( $gateway['enabled'] ); ?>
    <?php disabled( ! $gateway['enabled'] ); ?>
>

<?php echo esc_html( $gateway['label'] ); ?>

<?php if ( ! $gateway['enabled'] ) : ?>

<em>(Coming Soon)</em>

<?php endif; ?>

</label>

</p>

<?php endforeach; ?>

</div>

<p class="flipnzee-payment-actions">

<button
    type="submit"
    name="flipnzee_payment_completed"
    class="button"
>
I've Completed Payment
</button>

<button
    type="submit"
    name="flipnzee_continue_payment"
    class="button button-primary"
>
Continue to Payment
</button>

</p>

</form>

<?php
}

private static function render_manual_payment( $transaction ) {
?>

<div class="notice notice-success">

    <p><strong>Manual Payment Selected</strong></p>

    <p>Please complete your payment using the instructions below.</p>

</div>

<div class="flipnzee-manual-payment">

<h3>Payment Instructions</h3>

<p>Thank you for choosing Manual Payment.</p>

<p>Please use the transaction reference below when sending your payment.</p>

<table class="widefat striped">

<tr>
    <th>Reference Number</th>
    <td>
        <?php
        echo esc_html(
            'FLIP-' . str_pad(
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

<tr>
    <th>Status</th>
    <td>Awaiting Payment</td>
</tr>

</table>

<h4>Important</h4>

<ul>

<li>Include the reference number with your payment.</li>

<li>Keep proof of payment for verification.</li>

<li>Your transaction will be reviewed before ownership transfer.</li>

</ul>
<?php if ( empty( $transaction->payment_proof_id ) ) : ?>
<h3>Upload Payment Proof</h3>

<p>
After completing your payment, upload your receipt or screenshot below.
</p>

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

<p class="description">

Supported formats:
JPG, JPEG, PNG and PDF.

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
<?php else : ?>

<div class="notice notice-success">

    <p>

        <strong>Payment Proof Already Submitted.</strong>

        Your payment proof has already been uploaded and is awaiting verification by the Flipnzee team.

    </p>

</div>

<?php endif; ?>

</div>
<?php
}
}