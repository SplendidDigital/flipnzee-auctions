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
	id="payment_status">

	<option
    value="pending"
    <?php selected( $transaction['payment_status'], 'pending' ); ?>>
    <?php esc_html_e( 'Pending', 'flipnzee-auctions' ); ?>
</option>

	<option
		value="processing"
		<?php selected( $transaction['payment_status'], 'processing' ); ?>>
		<?php esc_html_e( 'Processing', 'flipnzee-auctions' ); ?>
	</option>

	<option
		value="paid"
		<?php selected( $transaction['payment_status'], 'paid' ); ?>>
		<?php esc_html_e( 'Paid', 'flipnzee-auctions' ); ?>
	</option>

	<option
		value="completed"
		<?php selected( $transaction['payment_status'], 'completed' ); ?>>
		<?php esc_html_e( 'Completed', 'flipnzee-auctions' ); ?>
	</option>

	<option
		value="cancelled"
		<?php selected( $transaction['payment_status'], 'cancelled' ); ?>>
		<?php esc_html_e( 'Cancelled', 'flipnzee-auctions' ); ?>
	</option>

	<option
		value="refunded"
		<?php selected( $transaction['payment_status'], 'refunded' ); ?>>
		<?php esc_html_e( 'Refunded', 'flipnzee-auctions' ); ?>
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

switch ( $payment_status ) {

    case 'paid':
        $status = 'paid';
        break;

    case 'completed':
        $status = 'completed';
        break;

    case 'cancelled':
        $status = 'cancelled';
        break;

    case 'refunded':
        $status = 'refunded';
        break;

    default:
        $status = 'pending';
        break;
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

    wp_safe_redirect(
        admin_url(
            'admin.php?page=flipnzee-transaction-details&transaction_id=' .
            $transaction_id .
            '&updated=1'
        )
    );

    exit;
}
}
