<?php
/**
 * Transfer Management Page.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Admin_Transfer {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'admin_menu',
			array(
				$this,
				'register_page',
			)
		);

	}

	/**
	 * Register admin page.
	 */
	public function register_page() {

		add_submenu_page(

			'flipnzee-auctions',

			'Transfer Management',

			'Transfers',

			'manage_options',

			'flipnzee-transfers',

			array(
				$this,
				'render_page',
			)

		);

	}

	/**
	 * Render page.
	 */
	public function render_page() {

		echo '<div class="wrap">';

		echo '<h1>Transfer Management</h1>';

		$transfers = Flipnzee_Transfer_Manager::get_all_transfers();

echo '<table class="widefat striped">';

echo '<thead>';

echo '<tr>';

echo '<th>ID</th>';
echo '<th>Transaction</th>';
echo '<th>Payment</th>';
echo '<th>Files</th>';
echo '<th>Database</th>';
echo '<th>Domain</th>';
echo '<th>Buyer</th>';

echo '</tr>';

echo '</thead>';

echo '<tbody>';

if ( empty( $transfers ) ) {

	echo '<tr>';
	echo '<td colspan="7">No transfers found.</td>';
	echo '</tr>';

} else {

	foreach ( $transfers as $transfer ) {

		echo '<tr>';

		echo '<td>' . esc_html( $transfer['id'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['transaction_id'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['payment_status'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['files_status'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['database_status'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['domain_status'] ) . '</td>';

		echo '<td>' . esc_html( $transfer['buyer_status'] ) . '</td>';

		echo '</tr>';

	}

}

echo '</tbody>';

echo '</table>';

		echo '</div>';

	}

}

new Flipnzee_Admin_Transfer();