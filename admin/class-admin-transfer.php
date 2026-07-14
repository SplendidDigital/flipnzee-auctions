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

		echo '<p>';

		echo 'Transfer administration interface will be implemented in Lesson 103.';

		echo '</p>';

		echo '</div>';

	}

}

new Flipnzee_Admin_Transfer();