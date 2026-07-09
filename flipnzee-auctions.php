<?php
/**
 * Plugin Name: Flipnzee Auctions
 * Plugin URI: https://flipnzee.com
 * Description: Professional website auction platform for Flipnzee.
 * Version: 1.0.0
 * Author: Splendid Digital Solutions
 * Author URI: https://flipnzee.com
 * License: GPL v2 or later
 * Text Domain: flipnzee-auctions
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.1
 */
/**
 * Current database schema version.
 */
define( 'FLIPNZEE_DB_VERSION', '1.1.0' );



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'FLIPNZEE_AUCTION_VERSION', '1.0.0' );
define( 'FLIPNZEE_AUCTION_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLIPNZEE_AUCTION_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load Plugin Classes
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-loader.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-loader.php';
}

/**
 * Load Database Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-database.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-database.php';
}

/**
 * Load Database Migration Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-database-migration.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-database-migration.php';
}

/**
 * Load Activity Log Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-activity-log.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-activity-log.php';
}
/**
 * Load Auction Manager
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-auction-manager.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-auction-manager.php';
	}
	/**
 * Load Bid Manager
 */
if ( file_exists(
    FLIPNZEE_AUCTION_PATH . 'includes/class-bid-manager.php'
) ) {

    require_once FLIPNZEE_AUCTION_PATH .
        'includes/class-bid-manager.php';
}
require_once FLIPNZEE_AUCTION_PATH .
    'includes/class-payment-manager.php';
require_once FLIPNZEE_AUCTION_PATH . 'includes/class-payment-page.php';

if ( file_exists( FLIPNZEE_AUCTION_PATH . 'admin/class-admin-payments.php' ) ) {

    require_once FLIPNZEE_AUCTION_PATH . 'admin/class-admin-payments.php';

}


/**
 *  * Load Shortcodes Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'includes/class-shortcodes.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'includes/class-shortcodes.php';
}

/**
 * Load Admin Activity Log
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'admin/class-admin-activity-log.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'admin/class-admin-activity-log.php';
}

/**
 * Load Admin Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'admin/class-admin.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'admin/class-admin.php';
}

/**
 * Load Admin Posts Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'admin/class-admin-posts.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'admin/class-admin-posts.php';
}

/**
 * Load Auctions Table Class
 */
if ( file_exists( FLIPNZEE_AUCTION_PATH . 'admin/class-auctions-table.php' ) ) {
	require_once FLIPNZEE_AUCTION_PATH . 'admin/class-auctions-table.php';
}

require_once FLIPNZEE_AUCTION_PATH .
	'includes/class-transaction-manager.php';

	/**
 * Plugin Activation
 */
/**
 * Plugin Activation
 */



require_once FLIPNZEE_AUCTION_PATH .
    'includes/class-payment-page.php';



require_once FLIPNZEE_AUCTION_PATH .
    'includes/class-payment-page.php';

require_once FLIPNZEE_AUCTION_PATH .
    'admin/class-transactions-table.php';

require_once FLIPNZEE_AUCTION_PATH .
    'admin/class-admin-transactions.php';

require_once FLIPNZEE_AUCTION_PATH .
    'admin/class-admin-transaction-details.php';

require_once FLIPNZEE_AUCTION_PATH .
    'includes/class-my-purchases.php';

require_once FLIPNZEE_AUCTION_PATH .
    'includes/class-my-purchase-details.php';


function flipnzee_auction_activate() {

	if ( false === get_option( 'flipnzee_db_version', false ) ) {

		Flipnzee_Auction_Database::create_tables();

	} else {

		Flipnzee_Database_Migration::run();
	}

	Flipnzee_Auction_Database::update_db_version();
}

/*
if ( ! wp_next_scheduled( 'flipnzee_auction_maintenance' ) ) {
    wp_schedule_event(
        time(),
        'hourly',
        'flipnzee_auction_maintenance'
    );
}
*/


/**
 * Plugin Deactivation
 */
function flipnzee_auction_deactivate() {

	$timestamp = wp_next_scheduled(
		'flipnzee_auction_maintenance'
	);

	if ( $timestamp ) {

		wp_unschedule_event(
			$timestamp,
			'flipnzee_auction_maintenance'
		);
	}
}

register_activation_hook(
	__FILE__,
	'flipnzee_auction_activate'
);

// register_activation_hook(
//     __FILE__,
//     'flipnzee_auction_activate'
// );

add_action(
	'flipnzee_auction_maintenance',
	array(
		'Flipnzee_Auction_Manager',
		'run_scheduled_maintenance',
	)
);
/**
 * Load frontend styles.
 */
function flipnzee_auction_enqueue_styles() {

	wp_enqueue_style(
		'flipnzee-auctions',
		FLIPNZEE_AUCTION_URL . 'assets/css/frontend.css',
		array(),
		FLIPNZEE_AUCTION_VERSION
	);

	wp_enqueue_script(
		'flipnzee-countdown',
		FLIPNZEE_AUCTION_URL . 'assets/js/countdown.js',
		array(),
		FLIPNZEE_AUCTION_VERSION,
		true
	);
}

add_action(
	'wp_enqueue_scripts',
	'flipnzee_auction_enqueue_styles'
);
/**
 * Handle transaction status updates.
 */
add_action(
	'admin_post_flipnzee_update_transaction_status',
	array(
		'Flipnzee_Transaction_Manager',
		'handle_status_update',
	)
);

add_action(
    'admin_post_flipnzee_update_payment_status',
    array(
        'Flipnzee_Admin_Transaction_Details',
        'update_payment_status'
    )
);
new Flipnzee_Shortcodes();
new Flipnzee_Transaction_Manager();
/**
 * Load Flipnzee admin styles.
 */
function flipnzee_admin_enqueue_styles( $hook ) {

    // Only load on Flipnzee admin pages.
    if ( false === strpos( $hook, 'flipnzee' ) ) {
        return;
    }

    wp_enqueue_style(
        'flipnzee-admin',
        plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
        array(),
        FLIPNZEE_AUCTION_VERSION
    );
}

add_action(
    'admin_enqueue_scripts',
    'flipnzee_admin_enqueue_styles'
);