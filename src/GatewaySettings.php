<?php
/**
 * Gateway Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Gateway Settings class
 *
 * Registers the GiveWP gateway settings (configuration + transaction
 * description) for the Pronamic Pay gateways, reusing the legacy option keys so
 * existing settings migrate seamlessly.
 */
class GatewaySettings {
	/**
	 * Gateways.
	 *
	 * @var array<int, Gateway>
	 */
	private $gateways;

	/**
	 * Construct gateway settings.
	 *
	 * @param array<int, class-string<Gateway>> $gateway_classes Gateway classes.
	 */
	public function __construct( array $gateway_classes ) {
		$this->gateways = \array_map(
			static fn( $gateway_class ) => new $gateway_class(),
			$gateway_classes
		);
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup(): void {
		\add_filter( 'give_get_sections_gateways', $this->sections( ... ) );
		\add_filter( 'give_get_settings_gateways', $this->settings( ... ) );
	}

	/**
	 * Add gateway sections.
	 *
	 * @param array<string, string> $sections Sections.
	 * @return array<string, string>
	 */
	public function sections( array $sections ): array {
		foreach ( $this->gateways as $gateway ) {
			$sections[ $gateway::id() ] = $gateway->getName();
		}

		return $sections;
	}

	/**
	 * Add gateway settings for the current section.
	 *
	 * @param array<int, array<string, mixed>> $settings Settings.
	 * @return array<int, array<string, mixed>>
	 */
	public function settings( array $settings ): array {
		$current_section = \give_get_current_setting_section();

		foreach ( $this->gateways as $gateway ) {
			if ( $gateway::id() !== $current_section ) {
				continue;
			}

			return \array_merge( $settings, $this->get_gateway_settings( $gateway ) );
		}

		return $settings;
	}

	/**
	 * Get the settings fields for a gateway.
	 *
	 * @param Gateway $gateway Gateway.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_gateway_settings( Gateway $gateway ): array {
		$id = $gateway::id();

		$description = '';

		if ( 'pronamic_pay' === $id ) {
			$description = \__( "This payment method does not use a predefined payment method for the payment. Some payment providers list all activated payment methods for your account to choose from. Use payment method specific gateways (such as 'iDEAL') to let customers choose their desired payment method at checkout.", 'pronamic_ideal' );
		}

		return [
			[
				'desc' => $description,
				'id'   => \sprintf( 'give_title_%s', $id ),
				'type' => 'title',
			],
			[
				'name'    => \__( 'Configuration', 'pronamic_ideal' ),
				'desc'    => '',
				'id'      => \sprintf( 'give_%s_configuration', $id ),
				'type'    => 'select',
				'options' => Plugin::get_config_select_options( $gateway->get_payment_method() ),
				'default' => $gateway->get_config_id(),
			],
			[
				'name'    => \__( 'Transaction description', 'pronamic_ideal' ),
				'desc'    => \sprintf(
					/* translators: %s: <code>{donation_id}</code> */
					\__( 'Available tags: %s', 'pronamic_ideal' ),
					\sprintf( '<code>%s</code>', '{donation_id}' )
				),
				'id'      => \sprintf( 'give_%s_transaction_description', $id ),
				'type'    => 'text',
				'default' => \__( 'Give donation {donation_id}', 'pronamic_ideal' ),
			],
			[
				'id'   => \sprintf( 'give_title_gateway_settings_%s', $id ),
				'type' => 'sectionend',
			],
		];
	}
}
