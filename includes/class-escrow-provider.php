<?php
/**
 * Escrow Provider.
 *
 * Simulates Escrow.com integration.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Escrow_Provider {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {

		error_log(
			'FLIPNZEE ESCROW: Provider initialized.'
		);

	}

	/**
	 * Create a simulated escrow transaction.
	 *
	 * @param int $transaction_id Transaction ID.
	 * @return string
	 */
	public static function create_transaction( $transaction_id ) {

		$reference = sprintf(
			'ESCROW-%s-%d',
			gmdate( 'YmdHis' ),
			$transaction_id
		);

		error_log(
			sprintf(
				'FLIPNZEE ESCROW: Created simulated escrow %s for transaction #%d',
				$reference,
				$transaction_id
			)
		);

		return $reference;

	}

}