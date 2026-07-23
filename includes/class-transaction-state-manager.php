<?php
/**
 * Transaction State Manager
 *
 * Centralized transaction state definitions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Transaction_State_Manager {

	/**
	 * Waiting for payment.
	 */
	const PAYMENT_PENDING = 'payment_pending';

	/**
	 * Payment submitted.
	 */
	const PAYMENT_SUBMITTED = 'payment_submitted';

	/**
	 * Payment verified.
	 */
	const PAYMENT_COMPLETED = 'payment_completed';

	/**
	 * Website files transfer.
	 */
	const FILES_TRANSFER = 'files_transfer';

	/**
	 * Database transfer.
	 */
	const DATABASE_TRANSFER = 'database_transfer';

	/**
	 * Domain transfer.
	 */
	const DOMAIN_TRANSFER = 'domain_transfer';

	/**
	 * Buyer verification.
	 */
	const BUYER_VERIFICATION = 'buyer_verification';

	/**
	 * Transaction completed.
	 */
	const COMPLETED = 'completed';

/**
 * Get human-readable label.
 *
 * @param string $state Transaction state.
 *
 * @return string
 */
public static function get_label( $state ) {

	$labels = array(

		self::PAYMENT_PENDING    => 'Payment Pending',

		self::PAYMENT_SUBMITTED  => 'Payment Submitted',

		self::PAYMENT_COMPLETED  => 'Payment Verified',

		self::FILES_TRANSFER     => 'Website Files Transfer',

		self::DATABASE_TRANSFER  => 'Database Transfer',

		self::DOMAIN_TRANSFER    => 'Domain Transfer',

		self::BUYER_VERIFICATION => 'Buyer Verification',

		self::COMPLETED          => 'Completed',

	);

	return isset( $labels[ $state ] )
		? $labels[ $state ]
		: ucfirst( str_replace( '_', ' ', $state ) );

}

/**
 * Get all transaction states in order.
 *
 * @return array
 */
public static function get_all_states() {

	return array(

		self::PAYMENT_PENDING,

		self::PAYMENT_SUBMITTED,

		self::PAYMENT_COMPLETED,

		self::FILES_TRANSFER,

		self::DATABASE_TRANSFER,

		self::DOMAIN_TRANSFER,

		self::BUYER_VERIFICATION,

		self::COMPLETED,

	);

}

/**
 * Check whether a state is terminal.
 *
 * @param string $state Transaction state.
 *
 * @return bool
 */
public static function is_terminal( $state ) {

	return self::COMPLETED === $state;

}

/**
 * Check whether a state is active.
 *
 * @param string $state Transaction state.
 *
 * @return bool
 */
public static function is_active( $state ) {

	return ! self::is_terminal( $state );

}

}