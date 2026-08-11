<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Gateway class
 *
 * Abstract base for the Pronamic Pay GiveWP 3.0 payment gateways. Each Pronamic
 * payment method is registered as a concrete subclass with its own static `id()`.
 */
abstract class Gateway extends PaymentGateway {
	/**
	 * Gateway ID.
	 *
	 * @return string
	 */
	abstract public static function id(): string;

	/**
	 * Pronamic payment method (`null` for the generic gateway).
	 *
	 * @return string|null
	 */
	abstract public function get_payment_method(): ?string;

	/**
	 * Legacy gateway ID.
	 *
	 * @return string
	 */
	public function getId(): string {
		return static::id();
	}

	/**
	 * Human-readable gateway name for the admin.
	 *
	 * @return string
	 */
	public function getName(): string {
		$payment_method = $this->get_payment_method();

		if ( null === $payment_method ) {
			return \__( 'Pronamic', 'pronamic_ideal' );
		}

		return \sprintf(
			/* translators: %s: payment method name */
			\__( 'Pronamic - %s', 'pronamic_ideal' ),
			PaymentMethods::get_name( $payment_method, \__( 'Pronamic', 'pronamic_ideal' ) )
		);
	}

	/**
	 * Donor-facing payment method label.
	 *
	 * @return string
	 */
	public function getPaymentMethodLabel(): string {
		$fallback = \__( 'Pronamic', 'pronamic_ideal' );

		return PaymentMethods::get_name( $this->get_payment_method(), $fallback ) ?? $fallback;
	}

	/**
	 * Supported form versions: v2 (legacy) and v3 (visual builder).
	 *
	 * @return array<int, int>
	 */
	public function supportsFormVersions(): array {
		return [ 2, 3 ];
	}

	/**
	 * Enqueue the gateway script for the visual donation form builder (v3).
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 * @throws \RuntimeException When the built gateway asset file is missing.
	 */
	public function enqueueScript( int $form_id ) {
		$asset_file = __DIR__ . '/../js/dist/gateway/index.asset.php';

		if ( ! \is_readable( $asset_file ) ) {
			throw new \RuntimeException(
				\sprintf(
					/* translators: %s: asset file path */
					\__( 'Missing GiveWP gateway asset file: %s. Run `npm run build`.', 'pronamic_ideal' ),
					$asset_file
				)
			);
		}

		$asset = require $asset_file;

		\wp_enqueue_script(
			'pronamic-pay-give-gateway',
			(string) \get_block_asset_url( __DIR__ . '/../js/dist/gateway/index.js' ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		\wp_add_inline_script(
			'pronamic-pay-give-gateway',
			\sprintf(
				'window.pronamicPayGive = window.pronamicPayGive || { ids: [] }; window.pronamicPayGive.ids.push( %s );',
				(string) \wp_json_encode( static::id() )
			),
			'before'
		);
	}

	/**
	 * Settings sent to the JavaScript gateway counterpart.
	 *
	 * @param int $form_id Form ID.
	 * @return array<string, mixed>
	 */
	public function formSettings( int $form_id ): array {
		return [
			'message' => $this->get_checkout_message(),
		];
	}

	/**
	 * Field markup for legacy option-based donation forms (v2).
	 *
	 * @param int                  $form_id Form ID.
	 * @param array<string, mixed> $args    Arguments.
	 * @return string
	 */
	public function getLegacyFormFieldMarkup( int $form_id, array $args ): string {
		$help_text = \sprintf(
			'<div class="pronamic-pay-give-help-text"><p>%s</p></div>',
			\esc_html( $this->get_checkout_message() )
		);

		$config_id      = $this->get_config_id();
		$gateway        = Plugin::get_gateway( $config_id );
		$payment_method = $this->get_payment_method();

		if ( null === $gateway || null === $payment_method ) {
			return $help_text;
		}

		$method = $gateway->get_payment_method( $payment_method );

		if ( null === $method ) {
			return $help_text;
		}

		$output = '';

		try {
			foreach ( $method->get_fields() as $field ) {
				$required_indicator = $field->is_required()
					? '<span class="give-required-indicator">*</span>'
					: '';

				$field_html = $field->render();

				$output .= \sprintf(
					'<p class="form-row form-row-wide"><label class="give-label">%s%s</label>%s</p>',
					\esc_html( $field->get_label() ),
					$required_indicator,
					$field_html
				);
			}
		} catch ( \Exception $e ) {
			return $output . \sprintf(
				'<div class="give_error">%s<br /><br />%s</div>',
				\esc_html( Plugin::get_default_error_message() ),
				\esc_html( \sprintf( '%s: %s', $e->getCode(), $e->getMessage() ) )
			);
		}

		if ( '' === $output ) {
			return $help_text;
		}

		return $output;
	}

	/**
	 * Create a payment with the gateway.
	 *
	 * @param Donation             $donation     Donation.
	 * @param array<string, mixed> $gateway_data Gateway data.
	 * @return RedirectOffsite
	 * @throws PaymentGatewayException When the payment could not be started.
	 */
	public function createPayment( Donation $donation, $gateway_data ) {
		$config_id = $this->get_config_id();

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			throw new PaymentGatewayException( \esc_html( Plugin::get_default_error_message() ) );
		}

		$payment = new Payment();

		$payment->source    = 'give';
		$payment->source_id = $donation->id;
		$payment->order_id  = (string) $donation->id;

		$payment->set_description( GiveHelper::get_description( $this, $donation ) );

		$payment->title = GiveHelper::get_title( $donation->id );

		$payment->set_customer( GiveHelper::get_customer_from_donation( $donation ) );
		$payment->set_billing_address( GiveHelper::get_address_from_donation( $donation ) );

		$currency = Currency::get_instance( \give_get_payment_currency_code( $donation->id ) );

		$payment->set_total_amount( new Money( $donation->amount->formatToDecimal(), $currency ) );

		$payment->set_payment_method( $this->get_payment_method() );

		$payment->config_id = $config_id;

		try {
			$payment = Plugin::start_payment( $payment );
		} catch ( \Exception $e ) {
			DonationNote::create(
				[
					'donationId' => $donation->id,
					'content'    => \sprintf(
						/* translators: %s: error message */
						\__( 'Pronamic Pay payment could not be created: %s', 'pronamic_ideal' ),
						$e->getMessage()
					),
				]
			);

			throw new PaymentGatewayException( \esc_html( $e->getMessage() ) );
		}

		return new RedirectOffsite( $payment->get_pay_redirect_url() );
	}

	/**
	 * Get the configured Pronamic gateway configuration ID.
	 *
	 * @return mixed
	 */
	public function get_config_id() {
		$config_id = \give_get_option( \sprintf( 'give_%s_configuration', static::id() ) );

		if ( empty( $config_id ) ) {
			$config_id = \get_option( 'pronamic_pay_config_id' );
		}

		return $config_id;
	}

	/**
	 * Get the transaction description setting.
	 *
	 * @return string
	 */
	public function get_transaction_description(): string {
		return (string) \give_get_option( \sprintf( 'give_%s_transaction_description', static::id() ), '' );
	}

	/**
	 * Message shown to the donor before the offsite redirect.
	 *
	 * @return string
	 */
	protected function get_checkout_message(): string {
		return \__( 'After submitting your donation you will be redirected to securely complete the payment.', 'pronamic_ideal' );
	}
}
