<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flipnzee_Auction_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {

	add_action(
		'admin_menu',
		array( $this, 'register_menu' )
	);

	add_action(
		'admin_init',
		array( $this, 'process_bulk_actions' )
	);
}

	/**
	 * Register the plugin admin menu.
	 */
	public function register_menu() {

		add_menu_page(
			'Flipnzee Auctions',
			'Flipnzee Auctions',
			'manage_options',
			'flipnzee-auctions',
			array( $this, 'dashboard_page' ),
			'dashicons-hammer',
			26
		);

add_submenu_page(
	'flipnzee-auctions',
	'Add Auction',
	'Add Auction',
	'manage_options',
	'flipnzee-add-auction',
	array( $this, 'add_auction_page' )
);

add_submenu_page(
	'flipnzee-auctions',
	'Activity Log',
	'Activity Log',
	'manage_options',
	'flipnzee-activity-log',
	array( $this, 'activity_log_page' )
);

add_submenu_page(
    'flipnzee-auctions',
    'Transactions',
    'Transactions',
    'manage_options',
    'flipnzee-transactions',
    array(
        'Flipnzee_Admin_Transactions',
        'render_page',
    )
);



add_submenu_page(
    'flipnzee-auctions',
    'Payments',
    'Payments',
    'manage_options',
    'flipnzee-payments',
    array(
        'Flipnzee_Admin_Payments',
        'render_page',
    )
);

add_submenu_page(
	'flipnzee-auctions',
	'Transaction Details',
	'Transaction Details',
	'manage_options',
	'flipnzee-transaction-details',
	array(
		'Flipnzee_Admin_Transaction_Details',
		'render_page',
	)
);
add_submenu_page(
	'flipnzee-auctions',
	'All Auctions',
	'All Auctions',
	'manage_options',
	'flipnzee-all-auctions',
	array( $this, 'all_auctions_page' )
);

add_submenu_page(
	'flipnzee-auctions',
	'Edit Auction',
	'Edit Auction',
	'manage_options',
	'flipnzee-edit-auction',
	array( $this, 'edit_auction_page' )
);

} 	/**
	 * Dashboard page.
	 */
	public function dashboard_page() {

		global $wpdb;

		$table = $wpdb->prefix . 'flipnzee_auctions';
		$transactions_table = $wpdb->prefix . 'flipnzee_transactions';

		$total_auctions = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table}"
		);

		$active_auctions = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table} WHERE status = 'active'"
);

$scheduled_auctions = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table} WHERE status = 'draft'"
);

$closed_auctions = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table} WHERE status = 'closed'"
);

$pending_transactions = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$transactions_table}
    WHERE payment_status = 'pending'"
);

$paid_transactions = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$transactions_table}
    WHERE payment_status = 'paid'"
);

$listings_with_active = (int) $wpdb->get_var(
    "SELECT COUNT(DISTINCT listing_id)
    FROM {$table}
    WHERE status = 'active'"
);

		

		?>

		<div class="wrap">

			<h1>Flipnzee Auctions Dashboard</h1>
			<?php
if ( isset( $_POST['flipnzee_activate_now'] ) ) {

	check_admin_referer(
		'flipnzee_activate_now',
		'flipnzee_activate_nonce'
	);

	Flipnzee_Auction_Manager::activate_scheduled_auctions();
	?>

	<div class="notice notice-success is-dismissible">
		<p>Scheduled auctions checked successfully.</p>
	</div>

	<?php
}

if ( isset( $_POST['flipnzee_close_now'] ) ) {

	check_admin_referer(
		'flipnzee_activate_now',
		'flipnzee_activate_nonce'
	);

	Flipnzee_Auction_Manager::close_expired_auctions();
	?>

	<div class="notice notice-success is-dismissible">
		<p>Expired auctions checked successfully.</p>
	</div>

	<?php
}

?>

<form method="post">

	<?php
	wp_nonce_field(
		'flipnzee_activate_now',
		'flipnzee_activate_nonce'
	);
	?>

	<?php
submit_button(
	'Activate Scheduled Auctions',
	'secondary',
	'flipnzee_activate_now',
	false
);

submit_button(
	'Close Expired Auctions',
	'secondary',
	'flipnzee_close_now',
	false
);
?>

</form>

			<p>Welcome to the Flipnzee Auctions administration panel.</p>
<div class="flipnzee-dashboard-grid">

<?php

$cards = array(

    'Total Auctions'               => $total_auctions,
    'Active Auctions'              => $active_auctions,
    'Scheduled Auctions'           => $scheduled_auctions,
    'Closed Auctions'              => $closed_auctions,
    'Listings With Active Auctions'=> $listings_with_active,
    'Pending Payments'             => $pending_transactions,
    'Paid Transactions'            => $paid_transactions,

);


foreach ( $cards as $title => $value ) {
    $this->render_dashboard_card( $title, $value );
}
?>


<?php
}

/**

 * Render a dashboard statistic card.
 *
 * @param string $title Card title.
 * @param mixed  $value Card value.
 */
private function render_dashboard_card( $title, $value ) {
    ?>
    <div class="flipnzee-dashboard-card">

        <h2><?php echo esc_html( $value ); ?></h2>

        <p><?php echo esc_html( $title ); ?></p>

    </div>
    <?php
}

/**
 * Render an admin notice.
 *
 * @param string $message Notice message.
 * @param string $type    Notice type.
 */
private function render_admin_notice( $message, $type = 'success' ) {
    ?>
    <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
        <p><?php echo esc_html( $message ); ?></p>
    </div>
    <?php
}

	/**
	 * Add Auction page.
	 */
	public function add_auction_page() {

		$message = isset( $_GET['message'] )
			? sanitize_text_field( wp_unslash( $_GET['message'] ) )
			: '';

		?>

		<div class="wrap">

			<h1>Add Auction</h1>

			<?php if ( 'success' === $message ) : ?>

				<div class="notice notice-success is-dismissible">
					<p>Auction created successfully.</p>
				</div>

			<?php elseif ( 'error' === $message ) : ?>

				<div class="notice notice-error is-dismissible">
					<p>Unable to create auction.</p>
				</div>

			<?php endif; ?>

			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			>

				<input
					type="hidden"
					name="action"
					value="flipnzee_create_auction"
				>

				<table class="form-table">

					<tr>
						<th scope="row">
							<label for="listing_id">Listing ID</label>
						</th>
						<td>
							<input
								type="number"
								id="listing_id"
								name="listing_id"
								min="1"
								required
								class="regular-text"
							>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="start_price">Start Price</label>
						</th>
						<td>
							<input
								type="number"
								id="start_price"
								name="start_price"
								step="0.01"
								required
								class="regular-text"
							>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="reserve_price">Reserve Price</label>
						</th>
						<td>
							<input
								type="number"
								id="reserve_price"
								name="reserve_price"
								step="0.01"
								class="regular-text"
							>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="buy_now_price">Buy Now Price</label>
						</th>
						<td>
							<input
								type="number"
								id="buy_now_price"
								name="buy_now_price"
								step="0.01"
								class="regular-text"
							>
						</td>
					</tr>

					<tr>
	<th scope="row">
		<label for="auction_start">Auction Start</label>
	</th>
	<td>
		<input
			type="datetime-local"
			id="auction_start"
			name="auction_start"
			class="regular-text"
		>
	</td>
</tr>

<tr>
	<th scope="row">
		<label for="auction_end">Auction End</label>
	</th>
	<td>
		<input
			type="datetime-local"
			id="auction_end"
			name="auction_end"
			class="regular-text"
		>
	</td>
</tr>

				</table>

				<?php
				wp_nonce_field(
					'flipnzee_create_auction',
					'flipnzee_nonce'
				);
				?>

				<?php submit_button( 'Create Auction' ); ?>

			</form>

		</div>

		<?php
	}

/**

 * All Auctions page.
 */
public function all_auctions_page() {

	$table = new Flipnzee_Auctions_Table();

/*
 * Read request parameters.
 */
$message = isset( $_GET['message'] )
	? sanitize_text_field(
		wp_unslash( $_GET['message'] )
	)
	: '';

/*
 * Prepare the table.
 */
$table->prepare_items();

	?>

	<div class="wrap">

		<h1>All Auctions</h1>

		<?php if ( 'deleted' === $message ) : ?>

			<div class="notice notice-success is-dismissible">
				<p>Auction deleted successfully.</p>
			</div>

		<?php elseif ( 'error' === $message ) : ?>

			<div class="notice notice-error is-dismissible">
				<p>Something went wrong.</p>
			</div>

		<?php endif; ?>

		<form method="post">

	<input
		type="hidden"
		name="page"
		value="flipnzee-all-auctions"
	>

	<?php
	wp_nonce_field(
		'flipnzee_bulk_delete',
		'flipnzee_bulk_nonce'
	);

	$status = isset( $_GET['status'] )
		? sanitize_text_field(
			wp_unslash( $_GET['status'] )
		)
		: '';
	?>

	<select name="status">

		<option value="">All Statuses</option>

		<option
			value="draft"
			<?php selected( $status, 'draft' ); ?>
		>
			Draft
		</option>

		<option
			value="active"
			<?php selected( $status, 'active' ); ?>
		>
			Active
		</option>

		<option
			value="closed"
			<?php selected( $status, 'closed' ); ?>
		>
			Closed
		</option>

	</select>

	<?php

	submit_button(
		'Filter',
		'secondary',
		'',
		false
	);

	$table->search_box(
		'Search Auctions',
		'flipnzee-search'
	);

	$table->display();

	?>

</form>

	<?php
}
	public function edit_auction_page() {
		$auction_id = isset( $_GET['auction_id'] )
			? absint( $_GET['auction_id'] )
			: 0;

		$auction = Flipnzee_Auction_Manager::get_auction(
	        $auction_id
        );



		?>

		<div class="wrap">

			<h1>Edit Auction</h1>
			<?php

$message = isset( $_GET['message'] )
	? sanitize_text_field(
		wp_unslash( $_GET['message'] )
	)
	: '';

if ( 'updated' === $message ) :
?>

	<?php
$this->render_admin_notice(
    'Auction updated successfully.'
);
?>

<?php elseif ( 'error' === $message ) : ?>

	<div class="notice notice-error is-dismissible">
		<p>Unable to update auction.</p>
	</div>

<?php endif; ?>

			<?php if ( ! $auction ) : ?>

				<div class="notice notice-error">
					<p>Auction not found.</p>
				</div>

			<?php else : ?>

						<form
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
>

	<input
		type="hidden"
		name="action"
		value="flipnzee_update_auction"
	>

				<table class="form-table">

					<tr>

						<th scope="row">
							<label for="listing_id">Listing ID</label>
						</th>

						<td>
							<input
								type="number"
								id="listing_id"
								name="listing_id"
								value="<?php echo esc_attr( $auction->listing_id ); ?>"
								required
								class="regular-text"
							>
						</td>

					</tr>
					<tr>

    <th scope="row">
        <label for="auction_start">
            Auction Start
        </label>
    </th>

    <td>

        <input
            type="datetime-local"
            id="auction_start"
            name="auction_start"
            value="<?php echo esc_attr( str_replace( ' ', 'T', $auction->auction_start ) ); ?>"
            class="regular-text"
        >

    </td>

</tr>
<tr>

    <th scope="row">
        <label for="auction_end">
            Auction End
        </label>
    </th>

    <td>

        <input
            type="datetime-local"
            id="auction_end"
            name="auction_end"
            value="<?php echo esc_attr( str_replace( ' ', 'T', $auction->auction_end ) ); ?>"
            class="regular-text"
        >

    </td>

</tr>

					<tr>

						<th scope="row">
							<label for="start_price">Start Price</label>
						</th>

						<td>
							<input
								type="number"
								step="0.01"
								id="start_price"
								name="start_price"
								value="<?php echo esc_attr( $auction->start_price ); ?>"
								class="regular-text"
							>
						</td>

					</tr>

					<tr>

						<th scope="row">
							<label for="reserve_price">Reserve Price</label>
						</th>

						<td>
							<input
								type="number"
								step="0.01"
								id="reserve_price"
								name="reserve_price"
								value="<?php echo esc_attr( $auction->reserve_price ); ?>"
								class="regular-text"
							>
						</td>

					</tr>

					<tr>

						<th scope="row">
							<label for="buy_now_price">Buy Now Price</label>
						</th>

						<td>
							<input
								type="number"
								step="0.01"
								id="buy_now_price"
								name="buy_now_price"
								value="<?php echo esc_attr( $auction->buy_now_price ); ?>"
								class="regular-text"
							>
						</td>

					</tr>

					<tr>

						<th scope="row">
							<label for="status">Status</label>
						</th>

						<td>

							<select
								name="status"
								id="status"
							>

								<option
									value="draft"
									<?php selected( $auction->status, 'draft' ); ?>
								>
									Draft
								</option>

								<option
									value="active"
									<?php selected( $auction->status, 'active' ); ?>
								>
									Active
								</option>

								<option
									value="closed"
									<?php selected( $auction->status, 'closed' ); ?>
								>
									Closed
								</option>

							</select>

						</td>

					</tr>

				</table>

				<?php
				wp_nonce_field(
					'flipnzee_update_auction',
					'flipnzee_nonce'
				);
				?>

				<input
					type="hidden"
					name="auction_id"
					value="<?php echo esc_attr( $auction->id ); ?>"
				>

				<?php submit_button( 'Save Changes' ); ?>

			</form>	

			<?php endif; ?>

		</div>

		<?php
	}
	/**
 * Process bulk actions.
 *
 * @return void
 */
public function process_bulk_actions() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if (
	! isset( $_POST['flipnzee_bulk_nonce'] ) ||
	! wp_verify_nonce(
		sanitize_text_field(
			wp_unslash( $_POST['flipnzee_bulk_nonce'] )
		),
		'flipnzee_bulk_delete'
	)
) {
	return;
}

	if ( ! isset( $_POST['action'] ) ) {
		return;
	}

	if ( 'delete' !== $_POST['action'] ) {
		return;
	}

	if ( empty( $_POST['auction_ids'] ) ) {
		return;
	}

	$auction_ids = array_map(
		'absint',
		wp_unslash( $_POST['auction_ids'] )
	);

	Flipnzee_Auction_Manager::delete_multiple_auctions(
		$auction_ids
	);

	wp_safe_redirect(
		admin_url(
			'admin.php?page=flipnzee-all-auctions&message=deleted'
		)
	);

	exit;
}
/**
 * Activity Log page.
 *
 * @return void
 */
public function activity_log_page() {

	Flipnzee_Admin_Activity_Log::render();
}
}

new Flipnzee_Auction_Admin();