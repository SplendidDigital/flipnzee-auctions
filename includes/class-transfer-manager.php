<?php
class Flipnzee_Transfer_Manager {
    /**
 * Transfer table name.
 *
 * @return string
 */
private static function get_table_name() {

    global $wpdb;

    return $wpdb->prefix .
        'flipnzee_transfer_status';

}

/**
 * Create transfer record.
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return bool
 */
public static function create_transfer(
    $transaction_id
) {

    error_log(
        'FLIPNZEE: create_transfer() started. Transaction ID: ' .
        $transaction_id
    );

    global $wpdb;

    $table = self::get_table_name();

    error_log(
        'FLIPNZEE: Transfer table: ' .
        $table
    );    error_log(
    'FLIPNZEE: Transfer table: ' .
    $table
);

    $exists = $wpdb->get_var(

        $wpdb->prepare(

            "
            SELECT id
            FROM {$table}
            WHERE transaction_id = %d
            LIMIT 1
            ",

            absint(
                $transaction_id
            )

        )

    );

    if ( $exists ) {

        return true;

    }

    $result = $wpdb->insert(

        $table,

        array(

            'transaction_id' => absint(
                $transaction_id
            ),

            'payment_status' => 'Completed',

            'files_status'    => 'Pending',

            'database_status' => 'Pending',

            'domain_status'   => 'Pending',

            'buyer_status'    => 'Pending',

            'notes'           => '',

        ),

        array(

            '%d',

            '%s',

            '%s',

            '%s',

            '%s',

            '%s',

            '%s',

        )

    );

    error_log(
    'FLIPNZEE: insert result = ' .
    var_export( $result, true )
);

error_log(
    'FLIPNZEE: last error = ' .
    $wpdb->last_error
);

error_log(
    'FLIPNZEE: last query = ' .
    $wpdb->last_query
);

    return false !== $result;

}
/**
 * Get transfer record.
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return array|null
 */
public static function get_transfer(
    $transaction_id
) {

    global $wpdb;

    $table = self::get_table_name();

    return $wpdb->get_row(

        $wpdb->prepare(

            "
            SELECT *
            FROM {$table}
            WHERE transaction_id = %d
            LIMIT 1
            ",

            absint( $transaction_id )

        ),

        ARRAY_A

    );

}
/**
 * Update transfer status.
 *
 * @param int    $transaction_id Transaction ID.
 * @param string $field          Status field.
 * @param string $value          Status value.
 *
 * @return bool
 */
/**
 * Update a single transfer status.
 *
 * @param int    $transaction_id Transaction ID.
 * @param string $field          Status field.
 * @param string $value          New value.
 *
 * @return bool
 */
public static function update_status(
	$transaction_id,
	$field,
	$value
) {

	global $wpdb;

	$allowed = array(
		'payment_status',
		'files_status',
		'database_status',
		'domain_status',
		'buyer_status',
	);

	if ( ! in_array( $field, $allowed, true ) ) {
		return false;
	}

	$table = self::get_table_name();

	$result = $wpdb->update(

		$table,

		array(
			$field => sanitize_text_field( $value ),
		),

		array(
			'transaction_id' => absint( $transaction_id ),
		),

		array(
			'%s',
		),

		array(
			'%d',
		)

	);

	return false !== $result;

}

/**
 * Update transfer notes.
 *
 * @param int    $transaction_id Transaction ID.
 * @param string $notes          Notes.
 *
 * @return bool
 */
public static function update_notes(
    $transaction_id,
    $notes
) {

    global $wpdb;

    $table = self::get_table_name();

    $result = $wpdb->update(

        $table,

        array(
            'notes' => sanitize_textarea_field(
                $notes
            ),
        ),

        array(
            'transaction_id' => absint(
                $transaction_id
            ),
        ),

        array(
            '%s',
        ),

        array(
            '%d',
        )

    );

    return false !== $result;
}

    /**
 * Update an entire transfer.
 *
 * @param int   $transaction_id Transaction ID.
 * @param array $data           Transfer data.
 *
 * @return bool
 */
public static function update_transfer(
    $transaction_id,
    $data
) {

    global $wpdb;

    $table = self::get_table_name();

    $result = $wpdb->update(

        $table,

        array(

            'payment_status' => sanitize_text_field(
                $data['payment_status']
            ),

            'files_status' => sanitize_text_field(
                $data['files_status']
            ),

            'database_status' => sanitize_text_field(
                $data['database_status']
            ),

            'domain_status' => sanitize_text_field(
                $data['domain_status']
            ),

            'buyer_status' => sanitize_text_field(
                $data['buyer_status']
            ),

            'notes' => sanitize_textarea_field(
                $data['notes']
            ),

        ),

        array(
            'transaction_id' => absint(
                $transaction_id
            ),
        ),

        array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        ),

        array(
            '%d',
        )

    );

    return false !== $result;
}

	/**
	 * Default transfer steps.
	 *
	 * @return array
	 */
    /**
 * Get all transfers.
 *
 * @return array
 */
public static function get_all_transfers() {

	global $wpdb;

	$table = self::get_table_name();

	$results = $wpdb->get_results(
		"
		SELECT *
		FROM {$table}
		ORDER BY id DESC
		",
		ARRAY_A
	);

	if ( ! $results ) {
		return array();
	}

	return $results;

}
	public static function get_default_steps() {

		return array(

			array(
				'label'     => 'Payment Confirmed',
				'completed' => true,
			),

			array(
				'label'     => 'Website Files Delivered',
				'completed' => false,
			),

			array(
				'label'     => 'Database Delivered',
				'completed' => false,
			),

			array(
				'label'     => 'Domain Transfer Completed',
				'completed' => false,
			),

			array(
				'label'     => 'Buyer Verification',
				'completed' => false,
			),

			array(
				'label'     => 'Purchase Completed',
				'completed' => false,
			),

		);

	}
/**
 * Check whether transfer is completed.
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return bool
 */
/**
 * Build transfer steps from database status.
 *
 * @param array $transfer Transfer row.
 *
 * @return array
 */
public static function build_steps(
    $transfer
) {

    return array(

        array(
            'label'     => 'Payment Confirmed',
            'completed' => (
                'Completed' === $transfer['payment']
            ),
        ),

        array(
            'label'     => 'Website Files Delivered',
            'completed' => (
                'Completed' === $transfer['files']
            ),
        ),

        array(
            'label'     => 'Database Delivered',
            'completed' => (
                'Completed' === $transfer['database']
            ),
        ),

        array(
            'label'     => 'Domain Transfer Completed',
            'completed' => (
                'Completed' === $transfer['domain']
            ),
        ),

        array(
            'label'     => 'Buyer Verification',
            'completed' => (
                'Completed' === $transfer['buyer']
            ),
        ),

        array(
            'label'     => 'Purchase Completed',
            'completed' => self::is_completed(
                $transfer['transaction_id']
            ),
        ),

    );

}
public static function is_completed(
    $transaction_id
) {

    $transfer = self::get_transfer(
        $transaction_id
    );

    if ( empty( $transfer ) ) {
        return false;
    }

    return (
	'Completed' === $transfer['payment_status'] &&
	'Completed' === $transfer['files_status'] &&
	'Completed' === $transfer['database_status'] &&
	'Completed' === $transfer['domain_status'] &&
	'Completed' === $transfer['buyer_status']
);

}
	/**
	 * Default transfer status.
	 *
	 * @return array
	 */
	public static function get_default_status() {

		return array(

			'payment' => 'Completed',

			'files'    => 'Pending',

			'database' => 'Pending',

			'domain'   => 'Pending',

			'buyer'    => 'Pending',

		);

	}

	/**
	 * Status badge classes.
	 *
	 * @return array
	 */
	public static function get_status_badges() {

		return array(

			'Completed'   => 'flipnzee-success',

			'Pending'     => 'flipnzee-pending',

			'In Progress' => 'flipnzee-progress',

		);

	}

    /**
 * Get transfer progress.
 *
 * @param int $transaction_id Transaction ID.
 *
 * @return array
 */

public static function get_progress( $transaction_id ) {

	$transfer = self::get_transfer( $transaction_id );

	if ( empty( $transfer ) ) {

		return array(
			'completed'  => 0,
			'total'      => 5,
			'percentage' => 0,
		);

	}

	$fields = array(
		'payment_status',
		'files_status',
		'database_status',
		'domain_status',
		'buyer_status',
	);

	$completed = 0;

	foreach ( $fields as $field ) {

		if (
			isset( $transfer[ $field ] ) &&
			'Completed' === $transfer[ $field ]
		) {
			$completed++;
		}
	}

	return array(
		'completed'  => $completed,
		'total'      => 5,
		'percentage' => (int) round( ( $completed / 5 ) * 100 ),
	);

}

public static function get_overall_status( $transaction_id ) {

	$progress = self::get_progress( $transaction_id );

	if ( 5 === $progress['completed'] ) {
		return 'Completed';
	}

	if ( $progress['completed'] > 0 ) {
		return 'In Progress';
	}

	return 'Pending';

}

public static function maybe_complete_transfer( $transaction_id ) {

	global $wpdb;

	if ( ! self::is_completed( $transaction_id ) ) {
		return;
	}

	$wpdb->update(
		$wpdb->prefix . 'flipnzee_transactions',
		array(
			'status' => 'completed',
		),
		array(
			'id' => absint( $transaction_id ),
		),
		array( '%s' ),
		array( '%d' )
	);

	Flipnzee_Activity_Log::log(
		sprintf(
			'Transfer completed for transaction #%d.',
			$transaction_id
		)
	);

}

public static function complete_stage(
	$transaction_id,
	$stage
) {

	$result = self::update_status(
		$transaction_id,
		$stage . '_status',
		'Completed'
	);

	if ( $result ) {
		self::maybe_complete_transfer( $transaction_id );
	}

	return $result;

}

}