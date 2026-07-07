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

?>

<h2>Payment</h2>

<p>Transaction ID:
	<strong><?php echo esc_html( $transaction_id ); ?></strong>
</p>

<p>This page will become the buyer payment page.</p>

<?php

return ob_get_clean();
	}
}