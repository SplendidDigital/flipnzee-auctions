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
	 */public static function run() {

    $current_version = get_option( 'flipnzee_db_version', '1.0.0' );

    if ( version_compare( $current_version, '1.1.0', '<' ) ) {
        self::migrate_to_1_1_0();
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

	$result = $wpdb->query(
		"ALTER TABLE `{$table_name}` ADD COLUMN {$column_definition}"
	);

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

	return false !== $result;
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

}