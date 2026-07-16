<?php
/**
 * Payment Manager
 *
 * Handles payment preparation before sending the buyer
 * to an actual payment gateway.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Flipnzee_Payment_Manager {

    /**
     * Get transaction details.
     *
     * @param int $transaction_id
     * @return object|null
     */
    public static function get_transaction( $transaction_id ) {

        global $wpdb;

        $table = $wpdb->prefix . 'flipnzee_transactions';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d",
                $transaction_id
            )
        );
    }

    /**
     * Check whether a transaction can be paid.
     *
     * @param object $transaction
     * @return bool
     */
    public static function can_pay( $transaction ) {

        if ( ! $transaction ) {
            return false;
        }

       if ( strtolower( trim( $transaction->status ) ) !== 'pending' ) {
             return false;
        }

        return true;
    }

    /**
     * Placeholder for future gateway integration.
     *
     * @param object $transaction
     * @return string
     */
    public static function get_gateway_name( $transaction ) {

        return 'Manual Payment (Coming Soon)';
    }

    /**
 * Get available payment gateways.
 *
 * @return array
 */
public static function get_available_gateways() {

	return array(

    'escrow' => array(
        'label'   => 'Escrow.com (Recommended)',
        'enabled' => false,
    ),

    'manual' => array(
        'label'   => 'Manual Payment',
        'enabled' => true,
    ),

    'stripe' => array(
        'label'   => 'Stripe',
        'enabled' => false,
    ),

    'paypal' => array(
        'label'   => 'PayPal',
        'enabled' => false,
    ),

    'razorpay' => array(
        'label'   => 'Razorpay',
        'enabled' => false,
    ),

    'crypto' => array(
        'label'   => 'USDT Cryptocurrency',
        'enabled' => false,
    ),

);
}
/**
 * Save uploaded payment proof.
 *
 * @param int $transaction_id
 * @param int $attachment_id
 * @return bool
 */
public static function save_payment_proof(
    $transaction_id,
    $attachment_id
) {

    global $wpdb;

    $table = $wpdb->prefix . 'flipnzee_transactions';

    return false !== $wpdb->update(

        $table,

        array(

            'payment_proof_id'      => (int) $attachment_id,
            'payment_status'        => 'submitted',
            'payment_submitted_at'  => current_time( 'mysql' ),

        ),

        array(

            'id' => (int) $transaction_id,

        ),

        array(

            '%d',
            '%s',
            '%s',

        ),

        array(

            '%d',

        )

    );

}

/**
 * Format a price for display.
 *
 * @param float $amount Price.
 * @return string
 */
public static function format_price( $amount ) {

    return '$' . number_format(
        (float) $amount,
        2
    );
}

}