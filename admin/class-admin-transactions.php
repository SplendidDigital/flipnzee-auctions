<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Admin_Transactions {

	/**
	 * Render Transactions page.
	 */
	/**
 * Render Transactions page.
 */
public static function render_page() {

	$table = new Flipnzee_Transactions_Table();

	$table->prepare_items();

	?>

	<div class="wrap">

		<h1>Transactions</h1>

		<p>
			Manage auction transactions generated after completed auctions.
		</p>

		<form method="post">

			<?php $table->display(); ?>

		</form>

	</div>

	<?php
}
}