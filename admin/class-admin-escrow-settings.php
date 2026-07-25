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

	public static function render_page() {

	self::save_settings();

    $settings = self::get_settings();

	?>

	<div class="wrap">

		<h1>Escrow Settings</h1>

		<p>

			Configure the connection between
			Flipnzee Auctions and Escrow.com.

		</p>

		<form method="post">

			<?php

			wp_nonce_field(
				'flipnzee_save_escrow_settings',
				'flipnzee_escrow_nonce'
			);

			?>

			<table class="form-table">

				<tr>

					<th>Environment</th>

					<td>

						<select
							name="environment"
						>

							<option
								value="simulation"
								<?php selected(
									$settings['environment'],
									'simulation'
								); ?>
							>

								Simulation

							</option>

							<option
								value="sandbox"
								<?php selected(
									$settings['environment'],
									'sandbox'
								); ?>
							>

								Sandbox

							</option>

							<option
								value="production"
								<?php selected(
									$settings['environment'],
									'production'
								); ?>
							>

								Production

							</option>

						</select>

					</td>

				</tr>

				<tr>

					<th>Sandbox Email</th>

					<td>

						<input
							type="email"
							name="sandbox_email"
							class="regular-text"
							value="<?php echo esc_attr(
								$settings['sandbox_email']
							); ?>">

					</td>

				</tr>

				<tr>

					<th>Sandbox API Key</th>

					<td>

						<input
							type="password"
							name="sandbox_api_key"
							class="regular-text"
							value="<?php echo esc_attr(
								$settings['sandbox_api_key']
							); ?>">

					</td>

				</tr>

				<tr>

					<th>Debug Logging</th>

					<td>

						<label>

							<input
								type="checkbox"
								name="debug"
								value="1"
								<?php checked(
									$settings['debug'],
									1
								); ?>>

							Enable debug logging

						</label>

					</td>

				</tr>

			</table>

			<?php

			submit_button(
				'Save Settings',
				'primary',
				'flipnzee_save_escrow'
			);

			?>

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
			! isset(
				$_POST['flipnzee_save_escrow']
			)
		) {
			return;
		}

		check_admin_referer(
			'flipnzee_save_escrow_settings',
			'flipnzee_escrow_nonce'
		);

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

			'production_email' => sanitize_email(
				wp_unslash(
					$_POST['production_email']
				)
			),

			'production_api_key' => sanitize_text_field(
				wp_unslash(
					$_POST['production_api_key']
				)
			),

			'debug' => isset(
				$_POST['debug']
			)
				? 1
				: 0,

		);

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

				'environment' => 'simulation',

				'sandbox_email' => '',

				'sandbox_api_key' => '',

				'production_email' => '',

				'production_api_key' => '',

				'debug' => 1,

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
	private static function render_status_card(
		$settings
	) {

		?>

		<div
			style="
				background:#fff;
				padding:20px;
				margin:20px 0;
				border-left:4px solid #2271b1;
				box-shadow:0 1px 3px rgba(0,0,0,.1);
			"
		>

			<h2>Current Configuration</h2>

			<table class="widefat striped">

				<tbody>

					<tr>

						<td><strong>Environment</strong></td>

						<td>
							<?php
							echo esc_html(
								ucfirst(
									$settings['environment']
								)
							);
							?>
						</td>

					</tr>

					<tr>

						<td><strong>Debug Logging</strong></td>

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