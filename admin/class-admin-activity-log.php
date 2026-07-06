<?php
/**
 * Activity Log Admin Page
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Admin_Activity_Log {

	/**
	 * Render Activity Log page.
	 *
	 * @return void
	 */
	public static function render() {

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Flipnzee Activity Log', 'flipnzee-auctions' ); ?></h1>

			<p>
				<?php esc_html_e(
					'Recent auction activity recorded by the plugin.',
					'flipnzee-auctions'
				); ?>
			</p>

			<?php

			$upload_dir = wp_upload_dir();

			$log_file = trailingslashit(
				$upload_dir['basedir']
			) . 'flipnzee-logs/activity.log';

			if ( ! file_exists( $log_file ) ) {

				echo '<div class="notice notice-info"><p>';
				esc_html_e(
					'No activity has been recorded yet.',
					'flipnzee-auctions'
				);
				echo '</p></div>';

				return;
			}

			$lines = file(
	$log_file,
	FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
);

if ( empty( $lines ) ) {

	echo '<div class="notice notice-info"><p>';
	esc_html_e(
		'No activity has been recorded yet.',
		'flipnzee-auctions'
	);
	echo '</p></div>';

} else {

	echo '<table class="widefat striped">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Date &amp; Time</th>';
	echo '<th>Event</th>';
	echo '<th>Auction</th>';
	echo '<th>User</th>';
	echo '<th>Details</th>';
	echo '</tr>';
	echo '</thead>';

	echo '<tbody>';

	foreach ( $lines as $line ) {

    if ( empty( trim( $line ) ) ) {
        continue;
    }

    preg_match(
        '/^\[(.*?)\]\s*Event:\s*(.*?)\s*\|\s*Auction:\s*(.*?)\s*\|\s*User:\s*(.*?)\s*\|\s*Details:\s*(.*)$/',
        $line,
        $matches
    );

    if ( ! empty( $matches ) ) {

        echo '<tr>';

        echo '<td>' . esc_html( $matches[1] ) . '</td>';
        echo '<td>' . esc_html( $matches[2] ) . '</td>';
        echo '<td>' . esc_html( $matches[3] ) . '</td>';
        echo '<td>' . esc_html( $matches[4] ) . '</td>';
        echo '<td>' . esc_html( $matches[5] ) . '</td>';

        echo '</tr>';

    } else {

        echo '<tr>';
        echo '<td colspan="5"><code>' .
            esc_html( $line ) .
            '</code></td>';
        echo '</tr>';

    }
}
	echo '</tbody>';
	echo '</table>';
}
echo '</div>';
	}
}