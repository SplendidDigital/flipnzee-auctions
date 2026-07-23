<?php
/**
 * Transaction Lifecycle Manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Transaction_Lifecycle_Manager {

	/**
	 * Register lifecycle hooks.
	 */
	public static function init() {

		add_action(
			'flipnzee_payment_completed',
			array(
				__CLASS__,
				'payment_completed',
			),
			10,
			1
		);

	}

	/**
	 * Payment completed callback.
	 *
	 * @param int $transaction_id Transaction ID.
	 */
	public static function payment_completed(
		$transaction_id
	) {

		error_log(
			'FLIPNZEE LIFECYCLE: Payment completed for transaction #' .
			$transaction_id
		);

	}

}

Flipnzee_Transaction_Lifecycle_Manager::init();