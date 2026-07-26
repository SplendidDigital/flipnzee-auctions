<?php
/**
 * Escrow Settings Administration.
 *
 * @package Flipnzee_Auctions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Escrow Settings Page.
 */
class Flipnzee_Admin_Escrow_Settings {

/**
 * WordPress option name used to store Escrow settings.
 */
private const OPTION_NAME = 'flipnzee_escrow_settings';

/**
 * Get current environment label.
 *
 * @param string $environment Environment.
 *
 * @return string
 */
private static function get_environment_label( $environment ) {

	switch ( $environment ) {

		case 'production':
			return 'Production';

		case 'sandbox':
			return 'Sandbox';

		default:
			return 'Simulation';
	}
}

/**
 * Determine whether credentials exist.
 *
 * @param array  $settings Settings.
 * @param string $environment Environment.
 *
 * @return bool
 */
private static function has_credentials(
	array $settings,
	$environment
) {

	switch ( $environment ) {

		case 'production':

			return ! empty(
				$settings['production_email']
			) &&
			! empty(
				$settings['production_api_key']
			);

		case 'sandbox':

			return ! empty(
				$settings['sandbox_email']
			) &&
			! empty(
				$settings['sandbox_api_key']
			);

		case 'simulation':

			return true;

		default:

			return false;
	}
}

/**
 * Validate settings before saving.
 *
 * @param array $settings Settings.
 *
 * @return bool
 */
private static function validate_settings(
	array $settings
) {

	switch ( $settings['environment'] ) {

		case 'sandbox':

			if ( empty(
				$settings['sandbox_email']
			) ) {

				add_settings_error(
					'flipnzee_escrow',
					'sandbox_email',
					'Sandbox email is required.'
				);

				return false;
			}

			if ( empty(
				$settings['sandbox_api_key']
			) ) {

				add_settings_error(
					'flipnzee_escrow',
					'sandbox_key',
					'Sandbox API Key is required.'
				);

				return false;
			}

			break;

		case 'production':

			if ( empty(
				$settings['production_email']
			) ) {

				add_settings_error(
					'flipnzee_escrow',
					'production_email',
					'Production email is required.'
				);

				return false;
			}

			if ( empty(
				$settings['production_api_key']
			) ) {

				add_settings_error(
					'flipnzee_escrow',
					'production_key',
					'Production API Key is required.'
				);

				return false;
			}

			break;
	}

	return true;
}

/**
 * Print environment notice.
 *
 * @param string $environment Environment.
 *
 * @return void
 */
private static function render_environment_notice(
	$environment
) {

	switch ( $environment ) {

		case 'simulation':

			echo '<div class="notice notice-info inline">';
			echo '<p><strong>Simulation Mode</strong><br>';
			echo 'No communication with Escrow.com occurs.</p>';
			echo '</div>';

			break;

		case 'sandbox':

			echo '<div class="notice notice-warning inline">';
			echo '<p><strong>Sandbox Mode</strong><br>';
			echo 'Requests will be sent to the Escrow Sandbox.</p>';
			echo '</div>';

			break;

		case 'production':

			echo '<div class="notice notice-success inline">';
			echo '<p><strong>Production Mode</strong><br>';
			echo 'Live Escrow transactions will be created.</p>';
			echo '</div>';

			break;
	}
}

	public static function render_page() {

		self::save_settings();

		settings_errors( 'flipnzee_escrow' );

		$settings = self::get_settings();
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Escrow Settings', 'flipnzee-auctions' ); ?></h1>
			<p><?php esc_html_e( 'Configure the Escrow.com connection used to create and manage auction transactions.', 'flipnzee-auctions' ); ?></p>

			<h2><?php esc_html_e( 'Environment Notice', 'flipnzee-auctions' ); ?></h2>
			<?php self::render_environment_notice( $settings['environment'] ); ?>

			<form method="post">
				<?php wp_nonce_field( 'flipnzee_save_escrow_settings', 'flipnzee_escrow_nonce' ); ?>

				<hr>
				<h2><?php esc_html_e( 'General Settings', 'flipnzee-auctions' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Choose the Escrow.com environment for new auction transactions.', 'flipnzee-auctions' ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><label for="flipnzee-escrow-environment"><?php esc_html_e( 'Environment', 'flipnzee-auctions' ); ?></label></th>
						<td>
							<select id="flipnzee-escrow-environment" name="environment">
								<option value="simulation" <?php selected( $settings['environment'], 'simulation' ); ?>><?php esc_html_e( 'Simulation', 'flipnzee-auctions' ); ?></option>
								<option value="sandbox" <?php selected( $settings['environment'], 'sandbox' ); ?>><?php esc_html_e( 'Sandbox', 'flipnzee-auctions' ); ?></option>
								<option value="production" <?php selected( $settings['environment'], 'production' ); ?>><?php esc_html_e( 'Production', 'flipnzee-auctions' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Simulation does not contact Escrow.com. Use Sandbox for testing and Production for live transactions.', 'flipnzee-auctions' ); ?></p>
						</td>
					</tr>
				</tbody></table>

				<hr>
				<h2><?php esc_html_e( 'Sandbox Credentials', 'flipnzee-auctions' ); ?></h2>
				<p class="description"><?php esc_html_e( 'These credentials are used only when the Sandbox environment is selected.', 'flipnzee-auctions' ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><label for="flipnzee-sandbox-email"><?php esc_html_e( 'Sandbox Email', 'flipnzee-auctions' ); ?></label></th>
						<td><input type="email" id="flipnzee-sandbox-email" name="sandbox_email" class="regular-text" value="<?php echo esc_attr( $settings['sandbox_email'] ); ?>">
							<p class="description"><?php esc_html_e( 'Email address associated with your Escrow.com Sandbox account.', 'flipnzee-auctions' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="flipnzee-sandbox-api-key"><?php esc_html_e( 'Sandbox API Key', 'flipnzee-auctions' ); ?></label></th>
						<td><input type="password" id="flipnzee-sandbox-api-key" name="sandbox_api_key" class="regular-text" value="<?php echo esc_attr( $settings['sandbox_api_key'] ); ?>">
							<p class="description"><?php esc_html_e( 'API key generated for your Escrow.com Sandbox account.', 'flipnzee-auctions' ); ?></p></td>
					</tr>
				</tbody></table>

				<hr>
				<h2><?php esc_html_e( 'Production Credentials', 'flipnzee-auctions' ); ?></h2>
				<p class="description"><?php esc_html_e( 'These credentials are used only when the Production environment is selected.', 'flipnzee-auctions' ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><label for="flipnzee-production-email"><?php esc_html_e( 'Production Email', 'flipnzee-auctions' ); ?></label></th>
						<td><input type="email" id="flipnzee-production-email" name="production_email" class="regular-text" value="<?php echo esc_attr( isset( $settings['production_email'] ) ? $settings['production_email'] : '' ); ?>">
							<p class="description"><?php esc_html_e( 'Email address associated with your live Escrow.com account.', 'flipnzee-auctions' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="flipnzee-production-api-key"><?php esc_html_e( 'Production API Key', 'flipnzee-auctions' ); ?></label></th>
						<td><input type="password" id="flipnzee-production-api-key" name="production_api_key" class="regular-text" value="<?php echo esc_attr( isset( $settings['production_api_key'] ) ? $settings['production_api_key'] : '' ); ?>">
							<p class="description"><?php esc_html_e( 'API key generated for your live Escrow.com account.', 'flipnzee-auctions' ); ?></p></td>
					</tr>
				</tbody></table>

				<hr>
				<h2><?php esc_html_e( 'Debug Settings', 'flipnzee-auctions' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Use diagnostic logging when troubleshooting Escrow.com connection or transaction issues.', 'flipnzee-auctions' ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Debug Logging', 'flipnzee-auctions' ); ?></th>
						<td><label for="flipnzee-escrow-debug"><input type="checkbox" id="flipnzee-escrow-debug" name="debug" value="1" <?php checked( $settings['debug'], 1 ); ?>> <?php esc_html_e( 'Enable debug logging', 'flipnzee-auctions' ); ?></label>
							<p class="description"><?php esc_html_e( 'Logs additional diagnostic information. Disable it after troubleshooting is complete.', 'flipnzee-auctions' ); ?></p></td>
					</tr>
				</tbody></table>

				<?php
				self::render_status_card( $settings );

				$can_test = self::has_credentials(
					$settings,
					$settings['environment']
				);
				?>
				<p class="submit">
					<button type="submit" name="flipnzee_save_escrow" class="button button-primary"><?php esc_html_e( 'Save Settings', 'flipnzee-auctions' ); ?></button>
					<button type="submit" name="flipnzee_test_connection" class="button button-secondary" <?php disabled( ! $can_test ); ?>><?php esc_html_e( 'Test Escrow Connection', 'flipnzee-auctions' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

/**
	 * Save settings.
	 *
	 * @return void
	 */
	private static function save_settings() {
if (
	! isset( $_POST['flipnzee_save_escrow'] ) &&
	! isset( $_POST['flipnzee_test_connection'] )
) {
	return;
}

check_admin_referer(
	'flipnzee_save_escrow_settings',
	'flipnzee_escrow_nonce'
);

if ( isset( $_POST['flipnzee_test_connection'] ) ) {

	self::test_connection();

	return;

}
		$existing = self::get_settings();
		$settings = array(

			'environment' => sanitize_text_field(
				wp_unslash(
					$_POST['environment']
				)
			),

			'sandbox_email' => sanitize_email(
				wp_unslash(
					$_POST['sandbox_email']
				)
			),

			'sandbox_api_key' => sanitize_text_field(
				wp_unslash(
					$_POST['sandbox_api_key']
				)
			),

		'production_email' => isset( $_POST['production_email'] )
	? sanitize_email(
		wp_unslash( $_POST['production_email'] )
	)
	: $existing['production_email'],

'production_api_key' => isset( $_POST['production_api_key'] )
	? sanitize_text_field(
		wp_unslash( $_POST['production_api_key'] )
	)
	: $existing['production_api_key'],
	

			'debug' => isset(
				$_POST['debug']
			)
				? 1
				: 0,

		);

		if ( ! self::validate_settings( $settings ) ) {
    return;
}

		update_option(
			self::OPTION_NAME,
			$settings
		);

		?>

		<div class="notice notice-success is-dismissible">

			<p>

				Escrow settings saved successfully.

			</p>

		</div>

		<?php
	}

		/**
 * Test the Escrow API connection.
 *
 * @return void
 */
private static function test_connection() {
	error_log(
	'FLIPNZEE ESCROW SETTINGS: Test button clicked.'
);

	$client = new Flipnzee_Escrow_API_Client();

	$result = $client->test_connection();

	if ( ! empty( $result['success'] ) ) {

		add_settings_error(
			'flipnzee_escrow',
			'connection_success',
			esc_html( $result['message'] ),
			'success'
		);

	} else {

		add_settings_error(
			'flipnzee_escrow',
			'connection_error',
			esc_html( $result['message'] ),
			'error'
		);

	}

}


	/**
	 * Default settings.
	 *
	 * @return array
	 */
	private static function get_settings() {

		return wp_parse_args(

			get_option(
				self::OPTION_NAME,
				array()
			),

			array(

    'environment'       => 'simulation',

    'sandbox_email'     => '',

    'sandbox_api_key'   => '',

    'production_email'  => '',

    'production_api_key'=> '',

    'debug'             => 1,

)

		);

	}

	/**
	 * Render status card.
	 *
	 * @param array $settings Settings.
	 *
	 * @return void
	 */
	/**
 * Render configuration status card.
 *
 * @param array $settings Escrow settings.
 *
 * @return void
 */
private static function render_status_card(
	array $settings
) {

	$has_sandbox = ! empty(
		$settings['sandbox_email']
	) && ! empty(
		$settings['sandbox_api_key']
	);

	$has_production = ! empty(
		$settings['production_email']
	) && ! empty(
		$settings['production_api_key']
	);

	$ready = self::has_credentials(
		$settings,
		$settings['environment']
	);

	?>

	<div
		style="
			background:#fff;
			padding:20px;
			margin:30px 0;
			border-left:4px solid #2271b1;
			box-shadow:0 1px 3px rgba(0,0,0,.08);
		"
	>

		<h2>

			<?php esc_html_e(
				'Current Configuration',
				'flipnzee-auctions'
			); ?>

		</h2>

		<p>

			<?php esc_html_e(
				'Review the current Escrow configuration before testing or creating live transactions.',
				'flipnzee-auctions'
			); ?>

		</p>

		<table class="widefat striped">

			<tbody>

				<tr>

					<td width="35%">

						<strong>

							<?php esc_html_e(
								'Environment',
								'flipnzee-auctions'
							); ?>

						</strong>

					</td>

					<td>

						<?php

						echo esc_html(
							self::get_environment_label(
								$settings['environment']
							)
						);

						?>

					</td>

				</tr>

				<tr>

					<td>

						<strong>

							<?php esc_html_e(
								'Sandbox Credentials',
								'flipnzee-auctions'
							); ?>

						</strong>

					</td>

					<td>

						<?php

						echo $has_sandbox
							? '✅ Configured'
							: '❌ Missing';

						?>

					</td>

				</tr>

				<tr>

					<td>

						<strong>

							<?php esc_html_e(
								'Production Credentials',
								'flipnzee-auctions'
							); ?>

						</strong>

					</td>

					<td>

						<?php

						echo $has_production
							? '✅ Configured'
							: '❌ Missing';

						?>

					</td>

				</tr>

				<tr>

					<td>

						<strong>

							<?php esc_html_e(
								'Connection Status',
								'flipnzee-auctions'
							); ?>

						</strong>

					</td>

					<td>

						<?php

						echo $ready
							? '🟢 Ready'
							: '🟠 Configuration Required';

						?>

					</td>

				</tr>

				<tr>

					<td>

						<strong>

							<?php esc_html_e(
								'Debug Logging',
								'flipnzee-auctions'
							); ?>

						</strong>

					</td>

					<td>

						<?php

						echo $settings['debug']
							? 'Enabled'
							: 'Disabled';

						?>

					</td>

				</tr>

			</tbody>

		</table>

	</div>

	<?php
}
}
