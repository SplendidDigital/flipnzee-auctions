<?php
/**
 * Escrow API client.
 *
 * Provides a single, reusable HTTP client for Escrow.com. The client supports
 * simulation, sandbox, and production environments while exposing a consistent
 * response format to the rest of the Flipnzee Auctions plugin.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client for the Escrow.com REST API.
 */
class Flipnzee_Escrow_API_Client {

	/**
	 * Escrow.com sandbox API base URL.
	 *
	 * @var string
	 */
	const SANDBOX_URL = 'https://api.escrow-sandbox.com/2017-09-01';

	/**
	 * Escrow.com production API base URL.
	 *
	 * @var string
	 */
	const LIVE_URL = 'https://api.escrow.com/2017-09-01';

	/**
	 * Escrow.com API version used by this client.
	 *
	 * @var string
	 */
	const API_VERSION = '2017-09-01';

	/**
	 * Settings loaded from the Flipnzee Escrow settings option.
	 *
	 * @var array<string, mixed>
	 */
	private $settings = array();

	/**
	 * Initialize the client with the saved Escrow settings.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->settings = wp_parse_args(
			get_option( 'flipnzee_escrow_settings', array() ),
			array(
				'environment'        => 'simulation',
				'sandbox_email'      => '',
				'sandbox_api_key'    => '',
				'production_email'   => '',
				'production_api_key' => '',
				'debug'              => false,
			)
		);

		$this->log( 'Escrow API client initialized.' );
	}

	/**
	 * Test API connectivity and authentication.
	 *
	 * @return array<string, mixed> Normalized API response.
	 */
	public function test_connection() {
		$result = $this->send_request( 'GET', '/' );

		if ( $result['success'] && 'simulation' !== $this->get_environment() ) {
			$result['message'] = sprintf(
				'%s connection established successfully.',
				ucfirst( $this->get_environment() )
			);
		}

		return $result;
	}

	/**
	 * Create an Escrow transaction.
	 *
	 * @param array<string, mixed> $transaction_data Transaction payload.
	 * @return array<string, mixed> Normalized API response.
	 */
	/**
 * Create an Escrow transaction.
 *
 * @param array<string, mixed> $transaction_data Transaction payload.
 * @return array<string, mixed> Normalized API response.
 */
public function create_transaction( $transaction_data = array() ) {

	if ( empty( $transaction_data['amount'] ) ) {
		return $this->build_response(
			false,
			'Transaction amount is required.',
			array(),
			'',
			'',
			'transaction',
			0
		);
	}

	if ( empty( $transaction_data['currency'] ) ) {
		return $this->build_response(
			false,
			'Transaction currency is required.',
			array(),
			'',
			'',
			'transaction',
			0
		);
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
	 * Retrieve an Escrow transaction.
	 *
	 * @param string $reference Provider transaction reference.
	 * @return array<string, mixed> Normalized API response.
	 */
	public function get_transaction( $reference ) {
		return $this->send_request(
			'GET',
			'transaction/' . rawurlencode( (string) $reference )
		);
	}

	/**
	 * Update an Escrow transaction.
	 *
	 * @param string               $reference Provider transaction reference.
	 * @param array<string, mixed> $data      Values to update.
	 * @return array<string, mixed> Normalized API response.
	 */
	public function update_transaction( $reference, $data = array() ) {
		return $this->send_request(
			'PATCH',
			'transaction/' . rawurlencode( (string) $reference ),
			$data
		);
	}

	/**
	 * Cancel an Escrow transaction.
	 *
	 * @param string $reference Provider transaction reference.
	 * @return array<string, mixed> Normalized API response.
	 */
	public function cancel_transaction( $reference ) {
		return $this->send_request(
			'POST',
			'transaction/' . rawurlencode( (string) $reference ) . '/cancel'
		);
	}

	/**
	 * Retrieve the current status of an Escrow transaction.
	 *
	 * @param string $reference Provider transaction reference.
	 * @return array<string, mixed> Normalized API response.
	 */
	public function get_status( $reference ) {
		return $this->send_request(
			'GET',
			'transaction/' . rawurlencode( (string) $reference )
		);
	}

	/**
	 * Write a diagnostic message when Escrow debugging is enabled.
	 *
	 * Messages must never include credentials or request bodies.
	 *
	 * @param string $message Message to write to the PHP error log.
	 * @return void
	 */
	private function log( $message ) {
		if ( empty( $this->settings['debug'] ) ) {
			return;
		}

		error_log( 'FLIPNZEE ESCROW API: ' . $message );
	}

	/**
	 * Get the configured Escrow environment.
	 *
	 * Invalid or missing values safely fall back to simulation.
	 *
	 * @return string One of simulation, sandbox, or production.
	 */
	private function get_environment() {
		$environment = isset( $this->settings['environment'] )
			? sanitize_key( (string) $this->settings['environment'] )
			: 'simulation';

		return in_array( $environment, array( 'simulation', 'sandbox', 'production' ), true )
			? $environment
			: 'simulation';
	}

	/**
	 * Get the API base URL for the configured environment.
	 *
	 * @return string API base URL, or an empty string in simulation mode.
	 */
	private function get_endpoint() {
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
	 * Build the headers for an authenticated Escrow API request.
	 *
	 * @return array<string, string> Request headers.
	 */
	private function get_headers() {
		$environment = $this->get_environment();
		$email       = (string) $this->settings[ $environment . '_email' ];
		$api_key     = (string) $this->settings[ $environment . '_api_key' ];
		$version     = defined( 'FLIPNZEE_AUCTION_VERSION' ) ? FLIPNZEE_AUCTION_VERSION : 'development';

		return array(
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $email . ':' . $api_key ),
			'User-Agent'    => 'Flipnzee Auctions/' . $version,
		);
	}

	/**
	 * Send a request to Escrow.com and normalize its response.
	 *
	 * This is the only method in this class that makes HTTP requests.
	 *
	 * @param string               $method HTTP request method.
	 * @param string               $path   API path relative to the base URL.
	 * @param array<string, mixed> $body   Optional request payload.
	 * @return array<string, mixed> Normalized API response.
	 */
	private function send_request( $method, $path, $body = array() ) {
		if ( 'simulation' === $this->get_environment() ) {
			return $this->simulate_request( $method, $path, $body );
		}

		$endpoint = trailingslashit( $this->get_endpoint() ) . ltrim( $path, '/' );
		$headers  = $this->get_headers();

		if ( empty( $this->settings[ $this->get_environment() . '_email' ] ) || empty( $this->settings[ $this->get_environment() . '_api_key' ] ) ) {
			return $this->build_response( false, 'Escrow API credentials are not configured.', array(), '', '', $endpoint, 0 );
		}

		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => $headers,
			'timeout' => 20,
		);

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );

			if ( false === $args['body'] ) {
				return $this->build_response( false, 'The request data could not be encoded as JSON.', array(), '', '', $endpoint, 0 );
			}
		}

		$this->log( sprintf( '%s %s', $args['method'], $endpoint ) );
		$response = wp_remote_request( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$this->log( $response->get_error_message() );
			return $this->build_response( false, $response->get_error_message(), array(), '', '', $endpoint, 0 );
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = array();

		if ( '' !== trim( $response_body ) ) {
			$data = json_decode( $response_body, true );

			if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
				$this->log( 'The Escrow API returned invalid JSON.' );
				return $this->build_response( false, 'The Escrow API returned an invalid JSON response.', array(), '', '', $endpoint, $response_code );
			}
		}

		$success = $response_code >= 200 && $response_code < 300;
		$message = $success ? 'Escrow API request completed successfully.' : $this->get_error_message( $response_code, $data );

		$this->log( 'HTTP response code: ' . $response_code );
		return $this->build_response( $success, $message, $data, $this->extract_reference( $data ), $this->extract_status( $data ), $endpoint, $response_code );
	}

	/**
	 * Return a realistic response without making an external HTTP request.
	 *
	 * @param string               $method Simulated HTTP request method.
	 * @param string               $path   Simulated API path.
	 * @param array<string, mixed> $body   Simulated request payload.
	 * @return array<string, mixed> Normalized simulated response.
	 */
	private function simulate_request( $method, $path, $body ) {
		$reference   = $this->get_reference_from_path( $path );
		$timestamp   = current_time( 'mysql', true );
		$transaction = array(
			'id'             => $reference ? $reference : 'ESCROW-' . gmdate( 'YmdHis' ) . '-' . wp_rand( 100, 999 ),
			'status'         => 'created',
			'created_at'     => $timestamp,
			'updated_at'     => $timestamp,
			'transaction_id' => isset( $body['transaction_id'] ) ? absint( $body['transaction_id'] ) : 0,
		);

		if ( 'POST' === strtoupper( $method ) && false !== strpos( $path, '/cancel' ) ) {
			$transaction['status'] = 'cancelled';
		} elseif ( 'PATCH' === strtoupper( $method ) ) {
			$transaction = array_merge( $transaction, $body );
		}

		if ( '/' === $path ) {
			return $this->build_response( true, 'Simulation mode is active. No external request was made.', array( 'timestamp' => $timestamp ), '', 'connected', 'simulation', 200 );
		}

		$this->log( 'Simulation request intercepted: ' . strtoupper( $method ) . ' ' . $path );
		return $this->build_response( true, 'Simulation mode is active. No external request was made.', $transaction, $transaction['id'], $transaction['status'], 'simulation', 200 );
	}

	/**
	 * Create the standard response returned by every public client method.
	 *
	 * @param bool                 $success       Whether the request succeeded.
	 * @param string               $message       Human-readable result message.
	 * @param array<string, mixed> $data          Response data.
	 * @param string               $reference     Provider transaction reference.
	 * @param string               $status        Provider transaction status.
	 * @param string               $endpoint      Endpoint that handled the request.
	 * @param int                  $response_code HTTP response code.
	 * @return array<string, mixed> Normalized response.
	 */
	private function build_response( $success, $message, $data, $reference, $status, $endpoint, $response_code ) {
		return array(
			'success'       => (bool) $success,
			'message'       => (string) $message,
			'data'          => is_array( $data ) ? $data : array(),
			'reference'     => (string) $reference,
			'status'        => (string) $status,
			'endpoint'      => (string) $endpoint,
			'response_code' => (int) $response_code,
		);
	}

	/**
	 * Get a useful error message for a failed HTTP response.
	 *
	 * @param int                  $response_code HTTP response code.
	 * @param array<string, mixed> $data          Decoded response data.
	 * @return string Error message.
	 */
	private function get_error_message( $response_code, $data ) {
		if ( ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
			return $data['message'];
		}

		if ( ! empty( $data['error'] ) && is_string( $data['error'] ) ) {
			return $data['error'];
		}

		if ( 401 === (int) $response_code || 403 === (int) $response_code ) {
			return 'Escrow API authentication failed. Please verify the configured credentials.';
		}

		return sprintf( 'Escrow API request failed with HTTP response code %d.', (int) $response_code );
	}

	/**
	 * Extract a provider transaction reference from an API response.
	 *
	 * @param array<string, mixed> $data Decoded API response.
	 * @return string Provider transaction reference.
	 */
	private function extract_reference( $data ) {
		foreach ( array( 'id', 'reference', 'transaction_id' ) as $key ) {
			if ( isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ) {
				return (string) $data[ $key ];
			}
		}

		return '';
	}

	/**
	 * Extract a provider transaction status from an API response.
	 *
	 * @param array<string, mixed> $data Decoded API response.
	 * @return string Provider transaction status.
	 */
	private function extract_status( $data ) {
		return isset( $data['status'] ) && is_scalar( $data['status'] ) ? (string) $data['status'] : '';
	}

	/**
	 * Extract a transaction reference from a transaction endpoint path.
	 *
	 * @param string $path API path.
	 * @return string Transaction reference, if present.
	 */
	private function get_reference_from_path( $path ) {
		if ( preg_match( '#^transaction/([^/]+)#', $path, $matches ) ) {
			return rawurldecode( $matches[1] );
		}

		return '';
	}
}
