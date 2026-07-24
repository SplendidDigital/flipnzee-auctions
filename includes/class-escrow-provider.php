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
/**
 * Create an escrow transaction.
 *
 * Currently delegates transaction creation to the Escrow API Client,
 * which operates in simulation mode. Future lessons will replace the
 * simulated responses with live Escrow.com API communication.
 *
 * @param int $transaction_id Transaction ID.
 * @return string|false Escrow reference on success, false on failure.
 */
/**
 * Create an escrow transaction.
 *
 * Currently delegates transaction creation to the Escrow API Client,
 * which operates in simulation mode. Future lessons will replace the
 * simulated responses with live Escrow.com API communication.
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
	| Create API client.
	|--------------------------------------------------------------------------
	*/
error_log( 'STEP 1: create_transaction entered' );
	$client = new Flipnzee_Escrow_API_Client();
	error_log(
    'STEP 4 RESPONSE: ' .
    print_r( $response, true )
);
	error_log( 'STEP 3: API client created' );

	/*
	|--------------------------------------------------------------------------
	| Ask API client to create transaction.
	|--------------------------------------------------------------------------
	*/

	$response = $client->create_transaction(
		array(
			'transaction_id' => absint( $transaction_id ),
		)
	);

	if (
		empty( $response['success'] ) ||
		empty( $response['reference'] )
	) {

		error_log(
			sprintf(
				'FLIPNZEE ESCROW: API client failed for transaction #%d.',
				$transaction_id
			)
		);

		return false;

	}

	$reference = $response['reference'];

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

				'status'             => $response['status'],

				'started_at'         => current_time( 'mysql' ),

				'completed_at'       => null,

				'notes'              => 'Transaction created through Escrow API Client.',

				'created_at'         => current_time( 'mysql' ),

				'updated_at'         => current_time( 'mysql' ),

			)
		);

	if ( false === $provider_id ) {

		error_log(
			sprintf(
				'FLIPNZEE ESCROW: Failed to create provider record for transaction #%d.',
				$transaction_id
			)
		);

		return false;

	}

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