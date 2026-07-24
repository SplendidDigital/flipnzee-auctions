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
	/**
 * Create a simulated escrow transaction.
 *
 * @param int $transaction_id Transaction ID.
 * @return string|false Escrow reference on success, false on failure.
 */
public static function create_transaction( $transaction_id ) {

	error_log(
		sprintf(
			'FLIPNZEE ESCROW: Starting escrow transaction for transaction #%d.',
			$transaction_id
		)
	);

	/*
	|--------------------------------------------------------------------------
	| Generate simulated escrow reference.
	|--------------------------------------------------------------------------
	*/

	$reference = sprintf(
		'ESCROW-%s-%d',
		gmdate( 'YmdHis' ),
		absint( $transaction_id )
	);

	/*
	|--------------------------------------------------------------------------
	| Persist provider record.
	|--------------------------------------------------------------------------
	*/

	$provider_id =
		Flipnzee_External_Provider_Manager::create_provider(
			array(

				'transaction_id'     => absint( $transaction_id ),

				'provider'           => 'Escrow.com',

				'provider_reference' => $reference,

				'provider_url'       => '',

				'status'             => 'created',

				'started_at'         => current_time( 'mysql' ),

				'completed_at'       => null,

				'notes'              => 'Simulated escrow transaction created.',

				'created_at'         => current_time( 'mysql' ),

				'updated_at'         => current_time( 'mysql' ),

			)
		);

	/*
	|--------------------------------------------------------------------------
	| Check insert result.
	|--------------------------------------------------------------------------
	*/

	if ( false === $provider_id ) {

		error_log(
			sprintf(
				'FLIPNZEE ESCROW: Failed to create provider record for transaction #%d.',
				$transaction_id
			)
		);

		return false;

	}

	/*
	|--------------------------------------------------------------------------
	| Success logs.
	|--------------------------------------------------------------------------
	*/

	error_log(
		sprintf(
			'FLIPNZEE ESCROW: Provider record #%d created.',
			$provider_id
		)
	);

	error_log(
		sprintf(
			'FLIPNZEE ESCROW: Escrow reference %s created.',
			$reference
		)
	);

	return $reference;

}

}