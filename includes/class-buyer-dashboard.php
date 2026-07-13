<?php
/**
 * Buyer Dashboard
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Buyer_Dashboard {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_shortcode(
			'flipnzee_buyer_dashboard',
			array( $this, 'render_dashboard' )
		);
	}

	/**
	 * Render Buyer Dashboard.
	 *
	 * @return string
	 */
	public function render_dashboard() {

		if ( ! is_user_logged_in() ) {
			return '<p>Please log in to access your Buyer Dashboard.</p>';
		}

		ob_start();
		?>

		<div class="flipnzee-buyer-dashboard">

			<h2>Buyer Dashboard</h2>

			<p>
				Welcome,
				<?php
				echo esc_html(
					wp_get_current_user()->display_name
				);
				?>
			</p>

			<div class="flipnzee-dashboard-cards">

    <div class="flipnzee-dashboard-card">
        <h3>My Purchases</h3>
        <p>View websites you have won and purchased.</p>

        <a href="<?php echo esc_url( site_url( '/my-purchases/' ) ); ?>">
            View Purchases
        </a>
    </div>

    <div class="flipnzee-dashboard-card">
        <h3>My Watchlist</h3>
        <p>Track auctions you are interested in.</p>

        <a href="<?php echo esc_url( site_url( '/watchlist/' ) ); ?>">
            View Watchlist
        </a>
    </div>

    <div class="flipnzee-dashboard-card">
        <h3>Browse Auctions</h3>
        <p>Discover active website auctions.</p>

        <a href="<?php echo esc_url( site_url( '/listings/' ) ); ?>">
            Browse Auctions
        </a>
    </div>

    <div class="flipnzee-dashboard-card">
        <h3>Support</h3>
        <p>Need help with your purchase?</p>

        <a href="<?php echo esc_url( site_url( '/support/' ) ); ?>">
            Contact Support
        </a>
    </div>

</div>
		</div>

		<?php

		return ob_get_clean();
	}
}