<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Pronamic\WordPress\Pay\AbstractPluginIntegration;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Extension class
 */
class Extension extends AbstractPluginIntegration {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'give';

	/**
	 * Construct Give plugin integration.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			[
				'name' => 'Give',
			]
		);

		// Dependencies.
		$dependencies = $this->get_dependencies();

		$dependencies->add( new GiveDependency() );
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_filter( 'pronamic_payment_source_description_' . self::SLUG, $this->source_description( ... ), 10, 2 );
		\add_filter( 'pronamic_payment_source_text_' . self::SLUG, $this->source_text( ... ), 10, 2 );
		\add_filter( 'pronamic_payment_source_url_' . self::SLUG, $this->source_url( ... ), 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		\add_action( 'pronamic_payment_status_update_' . self::SLUG, $this->status_update( ... ), 10, 1 );
		\add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, $this->redirect_url( ... ), 10, 2 );

		\add_action( 'givewp_register_payment_gateway', $this->register_gateways( ... ) );

		( new GatewaySettings( $this->get_gateway_classes() ) )->setup();
	}

	/**
	 * Get the gateway classes to register (generic + active payment methods).
	 *
	 * @return array<int, class-string<Gateway>>
	 */
	private function get_gateway_classes(): array {
		$classes = [ Gateways\PronamicPayGateway::class ];

		$map = GatewayRepository::get_map();

		foreach ( PaymentMethods::get_active_payment_methods() as $payment_method ) {
			if ( isset( $map[ $payment_method ] ) ) {
				$classes[] = $map[ $payment_method ];
			}
		}

		return $classes;
	}

	/**
	 * Register the Pronamic Pay gateways with GiveWP.
	 *
	 * @param PaymentGatewayRegister $registrar Gateway registrar.
	 * @return void
	 */
	public function register_gateways( PaymentGatewayRegister $registrar ): void {
		foreach ( $this->get_gateway_classes() as $gateway_class ) {
			if ( $registrar->hasPaymentGateway( $gateway_class::id() ) ) {
				continue;
			}

			$registrar->registerGateway( $gateway_class );
		}
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 * @return string
	 */
	public function redirect_url( $url, $payment ) {
		return match ( $payment->get_status() ) {
			PaymentStatus::CANCELLED, PaymentStatus::FAILURE => \give_get_failed_transaction_uri(),
			PaymentStatus::SUCCESS => \give_get_success_page_uri(),
			default => $url,
		};
	}

	/**
	 * Update the donation status for the specified payment.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function status_update( Payment $payment ): void {
		$donation = Donation::find( (int) $payment->get_source_id() );

		if ( null === $donation ) {
			return;
		}

		$donation->status = match ( $payment->get_status() ) {
			PaymentStatus::CANCELLED => DonationStatus::CANCELLED(),
			PaymentStatus::EXPIRED => DonationStatus::ABANDONED(),
			PaymentStatus::FAILURE => DonationStatus::FAILED(),
			PaymentStatus::SUCCESS => DonationStatus::COMPLETE(),
			default => DonationStatus::PENDING(),
		};

		$donation->save();
	}

	/**
	 * Source column text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		$source_id = (int) $payment->get_source_id();

		$text = \__( 'Give', 'pronamic_ideal' ) . '<br />';

		$text .= \sprintf(
			'<a href="%s">%s</a>',
			\esc_url( $this->get_donation_edit_url( $source_id ) ),
			/* translators: %s: source id */
			\sprintf( \__( 'Donation %s', 'pronamic_ideal' ), $source_id )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Payment.
	 * @return string
	 */
	public function source_description( $description, Payment $payment ) {
		return \__( 'Give Donation', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Payment.
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		return $this->get_donation_edit_url( (int) $payment->get_source_id() );
	}

	/**
	 * Get the admin edit URL for a donation.
	 *
	 * @param int $donation_id Donation ID.
	 * @return string
	 */
	private function get_donation_edit_url( int $donation_id ): string {
		return \add_query_arg(
			[
				'post_type' => 'give_forms',
				'page'      => 'give-payment-history',
				'view'      => 'view-payment-details',
				'id'        => $donation_id,
			],
			\admin_url( 'edit.php' )
		);
	}
}
