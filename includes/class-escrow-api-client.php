<?php
/**
 * Escrow API Client.
 *
 * Provides a reusable interface for communicating with Escrow.com.
 * Currently operates in simulation mode. Future lessons will replace
 * the simulated responses with live REST API requests.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Escrow_API_Client {

	/**
	 * Escrow Sandbox endpoint.
	 */
	const SANDBOX_URL = 'https://api.escrow-sandbox.com/2017-09-01';

	/**
	 * Escrow Live endpoint.
	 */
	const LIVE_URL = 'https://api.escrow.com/2017-09-01';

	/**
	 * API Version.
	 */
	const API_VERSION = '2017-09-01';

	/**
	 * Whether sandbox mode is enabled.
	 *
	 * @var bool
	 */
	/**
 * Plugin settings.
 *
 * @var array
 */
protected $settings = array();

	/**
	 * Constructor.
	 */
/**
 * Constructor.
 */
public function __construct() {

/**
 * Write a debug log entry.
 *
 * @param string $message Log message.
 *
 * @return void
 */
private function log( $message ) {

	if ( empty( $this->settings['debug'] ) ) {
		return;
	}

	error_log(
		'FLIPNZEE ESCROW API: ' . $message
	);

}

/**
 * Get the configured environment.
 *
 * @return string
 */
public function get_environment() {

	return ! empty(
		$this->settings['environment']
	)
		? $this->settings['environment']
		: 'simulation';

}

/**
 * Get the API endpoint.
 *
 * @return string
 */
public function get_endpoint() {

	switch ( $this->get_environment() ) {

		case 'production':
			return self::LIVE_URL;

		case 'sandbox':
			return self::SANDBOX_URL;

		default:
			return '';

	}

}

/**
 * Build HTTP headers.
 *
 * @return array
 */
private function get_headers() {

/**
 * Send a request to the Escrow API.
 *
 * @param string $method HTTP method.
 * @param string $path   API path.
 * @param array  $body   Request body.
 *
 * @return array
 */
private function send_request(
	$method,
	$path,
	$body = array()
) {

	if ( 'simulation' === $this->get_environment() ) {

		$this->log(
			'Simulation request intercepted.'
		);

		return array(

			'success' => true,

			'status' => 200,

			'endpoint' => 'simulation',

			'message' => 'Simulation mode active.',

			'data' => array(),

		);

	}

	$url = trailingslashit(
		$this->get_endpoint()
	) . ltrim(
		$path,
		'/'
	);

	$args = array(

		'method' => strtoupper(
			$method
		),

		'headers' => $this->get_headers(),

		'timeout' => 20,

	);

	if ( ! empty( $body ) ) {

		$args['body'] = wp_json_encode(
			$body
		);

	}

	$this->log(
		sprintf(
			'%s %s',
			strtoupper( $method ),
			$url
		)
	);

	$response = wp_remote_request(
		$url,
		$args
	);

	if ( is_wp_error( $response ) ) {

		$this->log(
			$response->get_error_message()
		);

		return array(

			'success' => false,

			'status' => 0,

			'message' => $response->get_error_message(),

			'data' => array(),

		);

	}

	$status = wp_remote_retrieve_response_code(
		$response
	);

	$data = json_decode(

		wp_remote_retrieve_body(
			$response
		),

		true

	);

	$this->log(
		'HTTP Status ' . $status
	);

	return array(

		'success' => (
			$status >= 200 &&
			$status < 300
		),

		'status' => $status,

		'message' => (
			$status >= 200 &&
			$status < 300
		)
			? 'Connection successful.'
			: 'Escrow API returned an error.',

		'data' => is_array( $data )
			? $data
			: array(),

	);

}

	if ( 'production' === $this->get_environment() ) {

		$email = $this->settings['production_email'] ?? '';

		$api_key = $this->settings['production_api_key'] ?? '';

	} else {

		$email = $this->settings['sandbox_email'] ?? '';

		$api_key = $this->settings['sandbox_api_key'] ?? '';

	}

	return array(

		'Authorization' => 'Basic ' . base64_encode(
			$email . ':' . $api_key
		),

		'Content-Type' => 'application/json',

		'Accept' => 'application/json',

		'User-Agent' => sprintf(
			'Flipnzee Auctions/%s',
			defined( 'FLIPNZEE_AUCTIONS_VERSION' )
				? FLIPNZEE_AUCTIONS_VERSION
				: 'development'
		),

	);

}

	$this->settings = get_option(
		'flipnzee_escrow_settings',
		array()
	);

	$this->log(
		'Client initialized.'
	);

}

	/**
	 * Get current endpoint.
	 *
	 * @return string
	 */


	/**
	 * Enable sandbox mode.
	 *
	 * @return void
	 */
	

	/**
	 * Enable live mode.
	 *
	 * @return void
	 */


	/**
	 * Test connection.
	 *
	 * @return array
	 */
	/**
 * Test the Escrow API connection.
 *
 * @return array
 */
public function test_connection() {

	$this->log(
		'Testing Escrow connection.'
	);

	/*
	 * Simulation mode never performs
	 * external HTTP requests.
	 */
	if ( 'simulation' === $this->get_environment() ) {

		return $this->send_request(
			'GET',
			'/'
		);

	}

	/*
	 * A simple GET request against the API root
	 * is sufficient to verify connectivity and
	 * authentication.
	 */
	$result = $this->send_request(
		'GET',
		'/'
	);

	if ( ! $result['success'] ) {

		return $result;

	}

	$result['message'] = sprintf(
		'%s connection established successfully.',
		ucfirst(
			$this->get_environment()
		)
	);

	return $result;

}

	/**
	 * Create transaction.
	 *
	 * @param array $transaction_data Transaction data.
	 * @return array
	 */
	/**
 * Create an Escrow transaction.
 *
 * @param array $transaction_data Transaction payload.
 *
 * @return array
 */
public function create_transaction(
	$transaction_data = array()
) {

	$this->log(
		'Creating Escrow transaction.'
	);

	if ( 'simulation' === $this->get_environment() ) {

		return array(

			'success' => true,

			'reference' => sprintf(
				'ESCROW-%s',
				gmdate( 'YmdHis' )
			),

			'status' => 'created',

			'message' => 'Simulation transaction created.',

			'endpoint' => 'simulation',

			'data' => $transaction_data,

		);

	}

	return $this->send_request(

		'POST',

		'transaction',

		$transaction_data

	);

}
	/**
	 * Retrieve transaction.
	 *
	 * @param string $reference Provider reference.
	 * @return array
	 */
	/**
 * Retrieve an Escrow transaction.
 *
 * @param string $reference Provider reference.
 *
 * @return array
 */
public function get_transaction(
	$reference
) {

	$this->log(
		'Retrieving transaction ' . $reference
	);

	if ( 'simulation' === $this->get_environment() ) {

		return array(

			'success' => true,

			'reference' => $reference,

			'status' => 'created',

			'message' => 'Simulation response.',

		);

	}

	return $this->send_request(

		'GET',

		'transaction/' . rawurlencode(
			$reference
		)

	);

}
	/**
	 * Update transaction.
	 *
	 * @param string $reference Provider reference.
	 * @param array  $data Updated values.
	 * @return array
	 */
	/**
 * Update an Escrow transaction.
 *
 * @param string $reference Provider reference.
 * @param array  $data      Updated values.
 *
 * @return array
 */
public function update_transaction(
	$reference,
	$data = array()
) {

	$this->log(
		'Updating transaction ' . $reference
	);

	if ( 'simulation' === $this->get_environment() ) {

		return array(

			'success' => true,

			'updated' => true,

			'reference' => $reference,

			'data' => $data,

		);

	}

	return $this->send_request(

		'PATCH',

		'transaction/' . rawurlencode(
			$reference
		),

		$data

	);

}
	/**
	 * Cancel transaction.
	 *
	 * @param string $reference Provider reference.
	 * @return array
	 */
	/**
 * Cancel an Escrow transaction.
 *
 * @param string $reference Provider reference.
 *
 * @return array
 */
public function cancel_transaction(
	$reference
) {

	$this->log(
		'Cancelling transaction ' . $reference
	);

	if ( 'simulation' === $this->get_environment() ) {

		return array(

			'success' => true,

			'reference' => $reference,

			'status' => 'cancelled',

		);

	}

	return $this->send_request(

		'POST',

		'transaction/' .
		rawurlencode(
			$reference
		) .
		'/cancel'

	);

}
	/**
	 * Synchronize provider status.
	 *
	 * @param string $reference Provider reference.
	 * @return array
	 */
	/**
 * Retrieve transaction status.
 *
 * @param string $reference Provider reference.
 *
 * @return array
 */
public function get_status(
	$reference
) {

	$this->log(
		'Synchronizing transaction ' . $reference
	);

	if ( 'simulation' === $this->get_environment() ) {

		return array(

			'success' => true,

			'status' => 'created',

			'reference' => $reference,

			'updated_at' => current_time(
				'mysql'
			),

		);

	}

	return $this->send_request(

		'GET',

		'transaction/' .
		rawurlencode(
			$reference
		)

	);

}
}