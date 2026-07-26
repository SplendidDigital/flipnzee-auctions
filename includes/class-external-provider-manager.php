<?php
/**
 * External Provider Manager.
 *
 * Handles integrations with external transaction providers
 * such as Escrow.com.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External Provider Manager.
 */
class Flipnzee_External_Provider_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Reserved for future hooks.
	}

	/**
 * Create a provider transaction.
 *
 * Creates an external transaction using the configured
 * payment provider and returns a standardized response.
 *
 * @param string $provider Provider slug.
 * @param array  $transaction Transaction data.
 *
 * @return array
 */
public function create_transaction(
	$transaction_data = array()
) {

	$required = array(

		'transaction_id',
		'auction_id',
		'listing_id',
		'title',
		'description',
		'amount',
		'currency',
		'buyer_email',
		'seller_email',

	);

	foreach ( $required as $field ) {

		if (
			empty(
				$transaction_data[ $field ]
			)
		) {

			return $this->build_response(

				false,

				sprintf(
					'Missing required field: %s',
					$field
				),

				array(),

				'',

				'',

				'transaction',

				0

			);

		}

	}

	$this->log(
		'Creating Escrow transaction.'
	);

	return $this->send_request(

		'POST',

		'transaction',

		$transaction_data

	);

}
/**
 * Build an Escrow transaction payload.
 *
 * Converts Flipnzee transaction data into the format
 * expected by the Escrow API client.
 *
 * @param array $transaction Local transaction.
 *
 * @return array
 */
private function build_escrow_payload(
	array $transaction
) {

	$required = array(

		'transaction_id',
		'auction_id',
		'listing_id',
		'amount',
		'currency',
		'buyer_email',
		'seller_email',
		'title',
		'description',

	);

	foreach ( $required as $field ) {

		if (
			! isset(
				$transaction[ $field ]
			)
		) {

			throw new Exception(
				sprintf(
					'Missing transaction payload field: %s',
					$field
				)
			);

		}
	}

	return array(

		'transaction_id' =>
			absint(
				$transaction['transaction_id']
			),

		'auction_id' =>
			absint(
				$transaction['auction_id']
			),

		'listing_id' =>
			absint(
				$transaction['listing_id']
			),

		'title' =>
			sanitize_text_field(
				$transaction['title']
			),

		'description' =>
			sanitize_textarea_field(
				$transaction['description']
			),

		'currency' =>
			strtoupper(
				$transaction['currency']
			),

		'amount' =>
			(float)
			$transaction['amount'],

		'buyer_email' =>
			sanitize_email(
				$transaction['buyer_email']
			),

		'seller_email' =>
			sanitize_email(
				$transaction['seller_email']
			),

	);

}
	/**
	 * Create an external provider record.
	 *
	 * @param array $data Provider data.
	 * @return int|false
	 */

public static function create_provider( $data ) {

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: create_provider() called.'
	);

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_external_providers';

	$defaults = array(
		'transaction_id'     => 0,
		'provider'           => '',
		'provider_reference' => '',
		'provider_url'       => '',
		'status'             => 'pending',
		'started_at'         => current_time( 'mysql' ),
		'completed_at'       => null,
		'notes'              => '',
		'created_at'         => current_time( 'mysql' ),
		'updated_at'         => current_time( 'mysql' ),
	);

	$data = wp_parse_args( $data, $defaults );
	

	$result = $wpdb->insert(
		$table,
		$data,
		array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	if ( false === $result ) {

		error_log(
			'FLIPNZEE EXTERNAL PROVIDER INSERT FAILED: ' .
			$wpdb->last_error
		);

		return false;

	}

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER INSERT SUCCEEDED'
	);

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: Created provider record #' .
		$wpdb->insert_id
	);

	return (int) $wpdb->insert_id;

}
/**
 * Get an external provider record by ID.
 *
 * @param int $provider_id Provider ID.
 * @return array|null
 */
public static function get_provider( $provider_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_external_providers';

	$provider = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE id = %d",
			$provider_id
		),
		ARRAY_A
	);

	if ( empty( $provider ) ) {

		error_log(
			'FLIPNZEE EXTERNAL PROVIDER: Provider not found. ID: ' .
			$provider_id
		);

		return null;
	}

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: Loaded provider #' .
		$provider_id
	);

	return $provider;

}

/**
 * Get an external provider record by transaction ID.
 *
 * @param int $transaction_id Transaction ID.
 * @return array|null
 */
public static function get_provider_by_transaction( $transaction_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_external_providers';

	$provider = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE transaction_id = %d
			LIMIT 1",
			$transaction_id
		),
		ARRAY_A
	);

	if ( empty( $provider ) ) {

		error_log(
			'FLIPNZEE EXTERNAL PROVIDER: No provider found for transaction #' .
			$transaction_id
		);

		return null;
	}

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: Loaded provider for transaction #' .
		$transaction_id
	);

	return $provider;

}

	/**
	 * Update provider status.
	 *
	 * @param int    $provider_id Provider ID.
	 * @param string $status      New status.
	 * @return bool
	 */
	/**
 * Update the status of an external provider record.
 *
 * @param int    $provider_id Provider ID.
 * @param string $status      New provider status.
 * @return bool
 */
public static function update_provider_status(
	$provider_id,
	$status
) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_external_providers';

	$result = $wpdb->update(
		$table,
		array(
			'status'     => sanitize_text_field( $status ),
			'updated_at' => current_time( 'mysql' ),
		),
		array(
			'id' => absint( $provider_id ),
		),
		array(
			'%s',
			'%s',
		),
		array(
			'%d',
		)
	);

	if ( false === $result ) {

		error_log(
			'FLIPNZEE EXTERNAL PROVIDER: Failed to update provider #' .
			$provider_id .
			'. ' .
			$wpdb->last_error
		);

		return false;

	}

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: Updated provider #' .
		$provider_id .
		' to status "' .
		$status .
		'".'
	);

	return true;

}

	/**
	 * Delete provider.
	 *
	 * @param int $provider_id Provider ID.
	 * @return bool
	 */
	/**
 * Delete an external provider record.
 *
 * @param int $provider_id Provider ID.
 * @return bool
 */
public static function delete_provider( $provider_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_external_providers';

	$result = $wpdb->delete(
		$table,
		array(
			'id' => absint( $provider_id ),
		),
		array(
			'%d',
		)
	);

	if ( false === $result ) {

		error_log(
			'FLIPNZEE EXTERNAL PROVIDER: Failed to delete provider #' .
			$provider_id .
			'. ' .
			$wpdb->last_error
		);

		return false;

	}

	error_log(
		'FLIPNZEE EXTERNAL PROVIDER: Deleted provider #' .
		$provider_id
	);

	return true;

}

}