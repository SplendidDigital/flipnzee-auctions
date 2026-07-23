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
	/**
 * Render page.
 */
public function render_page() {

	echo '<div class="wrap">';

	echo '<h1 class="wp-heading-inline">';
	echo esc_html__( 'Transfer Management', 'flipnzee-auctions' );
	echo '</h1>';

	echo '<p>';
	echo esc_html__(
		'Monitor the ownership transfer progress for completed auction transactions.',
		'flipnzee-auctions'
	);
	echo '</p>';

	$transfers = Flipnzee_Transfer_Manager::get_all_transfers();

	if ( empty( $transfers ) ) {

		echo '<div class="notice notice-info inline">';
		echo '<p>';
		echo esc_html__(
			'No transfer records found.',
			'flipnzee-auctions'
		);
		echo '</p>';
		echo '</div>';

		echo '</div>';

		return;

	}

	echo '<table class="widefat striped">';

	echo '<thead>';

	echo '<tr>';

	echo '<th>';
	echo esc_html__( 'ID', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Transaction', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Overall', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Progress', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Payment', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Files', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Database', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Domain', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Buyer', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Notes', 'flipnzee-auctions' );
	echo '</th>';

	echo '<th>';
	echo esc_html__( 'Actions', 'flipnzee-auctions' );
	echo '</th>';

	echo '</tr>';

	echo '</thead>';

	echo '<tbody>';

	foreach ( $transfers as $transfer ) {

		$progress = Flipnzee_Transfer_Manager::get_progress(
			$transfer['transaction_id']
		);

		$overall = Flipnzee_Transfer_Manager::get_overall_status(
			$transfer['transaction_id']
		);

		switch ( $overall ) {

			case 'Completed':
				$badge = '#46b450';
				break;

			case 'In Progress':
				$badge = '#ffb900';
				break;

			default:
				$badge = '#999999';
				break;

		}

		echo '<tr>';

		echo '<td>';
		echo esc_html( $transfer['id'] );
		echo '</td>';

		echo '<td>';
		echo '#' . esc_html( $transfer['transaction_id'] );
		echo '</td>';

		echo '<td>';

		echo '<span style="
			background:' . esc_attr( $badge ) . ';
			color:#fff;
			padding:4px 10px;
			border-radius:12px;
			font-size:12px;
			font-weight:600;
			display:inline-block;
		">';

		echo esc_html( $overall );

		echo '</span>';

		echo '</td>';

		echo '<td>';

		echo '<strong>';

		echo esc_html(
			$progress['completed']
		);

		echo ' / ';

		echo esc_html(
			$progress['total']
		);

		echo '</strong>';

		echo '<br>';

		echo '<progress
			value="' .
			esc_attr(
				$progress['percentage']
			) .
			'"
			max="100"
			style="width:140px;">
		</progress>';

		echo '<br>';

		echo esc_html(
			$progress['percentage']
		);

		echo '%';

		echo '</td>';

		echo '<td>';
		echo esc_html(
			$transfer['payment_status']
		);
		echo '</td>';

		echo '<td>';
		echo esc_html(
			$transfer['files_status']
		);
		echo '</td>';

		echo '<td>';
		echo esc_html(
			$transfer['database_status']
		);
		echo '</td>';

		echo '<td>';
		echo esc_html(
			$transfer['domain_status']
		);
		echo '</td>';

		echo '<td>';
		echo esc_html(
			$transfer['buyer_status']
		);
		echo '</td>';

		echo '<td>';

		if ( empty( $transfer['notes'] ) ) {

			echo '&mdash;';

		} else {

			echo esc_html(
				wp_trim_words(
					$transfer['notes'],
					8
				)
			);

		}

		echo '</td>';

		echo '<td>';

		$url = admin_url(
			'admin.php?page=flipnzee-transaction-details&transaction_id=' .
			absint(
				$transfer['transaction_id']
			)
		);

		echo '<a class="button button-primary" href="' .
			esc_url( $url ) .
			'">';

		echo esc_html__(
			'View Transaction',
			'flipnzee-auctions'
		);

		echo '</a>';

		echo '</td>';

		echo '</tr>';

	}

	echo '</tbody>';

	echo '</table>';

echo '</div>';

} // End render_page()

} // End Flipnzee_Admin_Transfer class

new Flipnzee_Admin_Transfer();