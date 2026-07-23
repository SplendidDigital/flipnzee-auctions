<?php
/**
 * Database Migration Manager.
 *
 * Handles version-based database migrations.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Migration Manager.
 */
class Flipnzee_Database_Migration {

	/**
	 * Run pending database migrations.
	 *
	 * @return void
	 */
	public static function run() {

	$current_version = get_option(
		'flipnzee_db_version',
		'1.0.0'
	);

	if ( version_compare( $current_version, '1.1.0', '<' ) ) {

		error_log( 'FLIPNZEE RUN: migrate_to_1_1_0()' );

		self::migrate_to_1_1_0();
	}

	if ( version_compare( $current_version, '1.2.0', '<' ) ) {

		error_log( 'FLIPNZEE RUN: migrate_to_1_2_0()' );

		self::migrate_to_1_2_0();
	}

	if ( version_compare( $current_version, '1.3.0', '<' ) ) {

	error_log( 'FLIPNZEE RUN: migrate_to_1_3_0()' );

	self::migrate_to_1_3_0();

}

if ( version_compare( $current_version, '1.4.0', '<' ) ) {

	error_log( 'FLIPNZEE RUN: migrate_to_1_4_0()' );

	self::migrate_to_1_4_0();

}
	}
    /**
 * Check whether a database table exists.
 *
 * @since 1.1.0
 *
 * @param string $table_name Full database table name.
 * @return bool True if the table exists, otherwise false.
 */
public static function table_exists( $table_name ) {
	global $wpdb;

	$table = $wpdb->get_var(
		$wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$table_name
		)
	);

	return $table === $table_name;
}
/**
 * Check whether a column exists in a database table.
 *
 * @since 1.1.0
 *
 * @param string $table_name  Full database table name.
 * @param string $column_name Column name.
 * @return bool True if the column exists, otherwise false.
 */

public static function column_exists( $table_name, $column_name ) {
	global $wpdb;

	$column = $wpdb->get_var(
		$wpdb->prepare(
			"SHOW COLUMNS FROM `{$table_name}` LIKE %s",
			$column_name
		)
	);

	return ! empty( $column );
}

/**
 * Check whether an index exists on a database table.
 *
 * @since 1.1.0
 *
 * @param string $table_name Full database table name.
 * @param string $index_name Index name.
 * @return bool True if the index exists, otherwise false.
 */

public static function index_exists( $table_name, $index_name ) {
	global $wpdb;

	$index = $wpdb->get_var(
		$wpdb->prepare(
			"SHOW INDEX FROM `{$table_name}` WHERE Key_name = %s",
			$index_name
		)
	);

	return ! empty( $index );
}

/**
 * Add a column to a database table if it does not already exist.
 *
 * @since 1.1.0
 *
 * @param string $table_name        Full database table name.
 * @param string $column_name       Column name.
 * @param string $column_definition Full SQL column definition.
 * @return bool True on success, false otherwise.
 */

public static function add_column( $table_name, $column_name, $column_definition ) {
	global $wpdb;

	// Ensure the table exists.
	if ( ! self::table_exists( $table_name ) ) {
		return false;
	}

	// Don't add the column if it already exists.
	if ( self::column_exists( $table_name, $column_name ) ) {
		return true;
	}

$sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` {$column_definition}";

error_log( 'FLIPNZEE SQL: ' . $sql );

$result = $wpdb->query( $sql );

if ( false === $result ) {
	error_log( 'FLIPNZEE SQL ERROR: ' . $wpdb->last_error );
}

	return false !== $result;
}

/**
 * Add an index to a database table if it does not already exist.
 *
 * @since 1.1.0
 *
 * @param string $table_name  Full database table name.
 * @param string $index_name  Index name.
 * @param string $index_sql   Index SQL definition.
 * @return bool True on success, false otherwise.
 */

public static function add_index( $table_name, $index_name, $index_sql ) {
	global $wpdb;

	// Ensure the table exists.
	if ( ! self::table_exists( $table_name ) ) {
		return false;
	}

	// Don't add the index if it already exists.
	if ( self::index_exists( $table_name, $index_name ) ) {
		return true;
	}

	$result = $wpdb->query(
		"ALTER TABLE `{$table_name}` ADD {$index_sql}"
	);
if ( false === $result ) {

	error_log(
		'FLIPNZEE MIGRATION ERROR: ' . $wpdb->last_error
	);

	return false;
}

error_log(
	'FLIPNZEE MIGRATION: Added payment_reference column.'
);

return true;
}

/**
 * Migrate database schema to version 1.1.0.
 *
 * @return void
 */
/**
 * Migrate database schema to version 1.1.0.
 *
 * @return void
 */
private static function migrate_to_1_1_0() {

    error_log( 'FLIPNZEE MIGRATION: Running database migration to version 1.1.0' );

    // Future schema changes for version 1.1.0
    // will be added here.

    update_option( 'flipnzee_db_version', '1.1.0' );

    error_log( 'FLIPNZEE MIGRATION: Database version updated to 1.1.0' );
}
private static function migrate_to_1_2_0() {

    error_log( 'FLIPNZEE MIGRATION: Running database migration to version 1.2.0' );
    error_log( 'FLIPNZEE MIGRATION: Checking payment_reference column...' );

   global $wpdb;

$table = $wpdb->prefix . 'flipnzee_transactions';

if ( ! self::column_exists( $table, 'payment_reference' ) ) {

    self::add_column(
        $table,
        'payment_reference',
        'VARCHAR(100) NULL'
    );

   error_log(
	"FLIPNZEE MIGRATION: Added {$index_name} index."
);
}

    update_option( 'flipnzee_db_version', '1.2.0' );

    error_log( 'FLIPNZEE MIGRATION: Database version updated to 1.2.0' );
}
/**
 * Database migration to version 1.3.0.
 *
 * Creates the watchlist table.
 *
 * @return void
 */
/**
 * Database migration to version 1.3.0.
 *
 * Creates the External Providers table.
 *
 * @return void
 */
private static function migrate_to_1_3_0() {

	error_log(
		'FLIPNZEE MIGRATION: Running database migration to version 1.3.0'
	);

	// Create the External Providers table.
	self::create_external_providers_table();

	// Update the stored database version.
	update_option(
		'flipnzee_db_version',
		'1.3.0'
	);

	error_log(
		'FLIPNZEE MIGRATION: Database version updated to 1.3.0'
	);
}

/**
 * Create the External Providers table.
 *
 * @since 1.3.0
 *
 * @return void
 */
private static function create_external_providers_table() {

	global $wpdb;

	$table_name = $wpdb->prefix . 'flipnzee_external_providers';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table_name} (

		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,

		transaction_id BIGINT(20) UNSIGNED NOT NULL,

		provider VARCHAR(50) NOT NULL,

		provider_reference VARCHAR(255) DEFAULT '',

		provider_url TEXT NULL,

		status VARCHAR(30) NOT NULL DEFAULT 'pending',

		started_at DATETIME NULL,

		completed_at DATETIME NULL,

		notes LONGTEXT NULL,

		created_at DATETIME NOT NULL,

		updated_at DATETIME NOT NULL,

		PRIMARY KEY (id),

		KEY transaction_id (transaction_id),

		KEY provider (provider),

		KEY status (status)

	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta( $sql );

	error_log(
		'FLIPNZEE MIGRATION: External providers table checked/created.'
	);

}
/**
 * Database migration to version 1.4.0.
 *
 * Adds the transaction state column.
 *
 * @return void
 */
private static function migrate_to_1_4_0() {

	error_log(
		'FLIPNZEE MIGRATION: Running database migration to version 1.4.0'
	);

	global $wpdb;

	$table = $wpdb->prefix . 'flipnzee_transactions';

	if ( ! self::column_exists( $table, 'state' ) ) {

		self::add_column(
			$table,
			'state',
			"VARCHAR(50) NOT NULL DEFAULT 'payment_pending'"
		);

		error_log(
			'FLIPNZEE MIGRATION: Added state column.'
		);

	}

	update_option(
		'flipnzee_db_version',
		'1.4.0'
	);

	error_log(
		'FLIPNZEE MIGRATION: Database version updated to 1.4.0'
	);

}

}