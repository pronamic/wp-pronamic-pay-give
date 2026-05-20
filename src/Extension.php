<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Pronamic\WordPress\Pay\AbstractPluginIntegration;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Give extension
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.1.1
 * @since   1.0.0
 */
class Extension extends AbstractPluginIntegration {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'give';

	/**
	 * Payment gateway class names by gateway key.
	 *
	 * @var array<string, string>
	 */
	private $payment_gateway_classes = [];

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
		\add_filter( 'pronamic_payment_source_description_' . self::SLUG, [ $this, 'source_description' ], 10, 2 );
		\add_filter( 'pronamic_payment_source_text_' . self::SLUG, [ $this, 'source_text' ], 10, 2 );
		\add_filter( 'pronamic_payment_source_url_' . self::SLUG, [ $this, 'source_url' ], 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		\add_action( 'pronamic_payment_status_update_' . self::SLUG, [ $this, 'status_update' ], 10, 1 );
		\add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, [ $this, 'redirect_url' ], 10, 2 );

		\add_filter( 'give_payment_gateways', $this->give_payment_gateways( ... ) );
		\add_filter( 'give_enabled_payment_gateways', $this->give_enabled_payment_gateways( ... ) );

		if ( \class_exists( PaymentGatewayRegister::class ) ) {
			\add_action( 'givewp_register_payment_gateway', $this->register_payment_gateways( ... ) );
		}
	}

	/**
	 * Get gateways.
	 *
	 * @return array
	 */
	private function get_gateways() {
		$gateways = [];

		// PaymentMethods::update_active_payment_methods();

		// Get active payment methods.
		$payment_methods = array_merge( [ null ], PaymentMethods::get_active_payment_methods() );

		// Create gateways for payment methods.
		foreach ( $payment_methods as $payment_method ) {
			// Gateway identifier.
			$id = 'pronamic_pay';

			if ( ! empty( $payment_method ) ) {
				$id = \sprintf( 'pronamic_pay_%s', $payment_method );

				// Use `mister_cash` instead of `bancontact` for backwards compatibility.
				if ( PaymentMethods::BANCONTACT === $payment_method ) {
					$id = 'pronamic_pay_mister_cash';
				}
			}

			// New gateway.
			$gateway = new Gateway( $id, $payment_method );

			$name = PaymentMethods::get_name( $payment_method, __( 'Pronamic', 'pronamic_ideal' ) );

			// Admin label.
			$admin_label = \__( 'Pronamic', 'pronamic_ideal' );

			if ( null !== $payment_method ) {
				$admin_label = sprintf( '%s - %s', \__( 'Pronamic', 'pronamic_ideal' ), $name );
			}

			$gateways[ $id ] = [
				'instance'       => $gateway,
				'payment_method' => $payment_method,
				'admin_label'    => $admin_label,
				'checkout_label' => $name,
			];
		}

		// Sort gateways alphabetically.
		uasort(
			$gateways,
			function ( $a, $b ) {
				return strnatcasecmp( $a['admin_label'], $b['admin_label'] );
			}
		);

		return $gateways;
	}

	/**
	 * Classic Give payment gateways.
	 *
	 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L37
	 *
	 * @param array $gateways Gateways.
	 *
	 * @return array
	 */
	private function give_payment_gateways( $gateways ) {
		$legacy_gateways = [];

		foreach ( $this->get_gateways() as $gateway_id => $gateway_data ) {
			$legacy_gateways[ $gateway_id ] = [
				'admin_label'    => $gateway_data['admin_label'],
				'checkout_label' => $gateway_data['checkout_label'],
			];
		}

		return array_merge( $gateways, $legacy_gateways );
	}

	/**
	 * Register payment gateways for visual form builder.
	 *
	 * @param PaymentGatewayRegister $payment_gateway_register Payment gateway register.
	 * @return void
	 */
	private function register_payment_gateways( PaymentGatewayRegister $payment_gateway_register ): void {
		foreach ( $this->get_gateways() as $gateway_id => $gateway_data ) {
			if ( $payment_gateway_register->hasPaymentGateway( $gateway_id ) ) {
				continue;
			}

			$payment_gateway_register->registerGateway(
				$this->get_payment_gateway_class( $gateway_id, $gateway_data['payment_method'] )
			);
		}
	}

	/**
	 * Get payment gateway class name for the given gateway ID.
	 *
	 * @param string      $gateway_id      Gateway ID.
	 * @param string|null $payment_method Payment method.
	 * @return string
	 */
	private function get_payment_gateway_class( string $gateway_id, ?string $payment_method ): string {
		$gateway_key = $gateway_id . '::' . (string) $payment_method;

		if ( \array_key_exists( $gateway_key, $this->payment_gateway_classes ) ) {
			return $this->payment_gateway_classes[ $gateway_key ];
		}

		if ( null === $payment_method && 'pronamic_pay' === $gateway_id ) {
			$this->payment_gateway_classes[ $gateway_key ] = Gateway::class;

			return Gateway::class;
		}

		$class_name = __NAMESPACE__ . '\\GeneratedGateway_' . md5( $gateway_key );

		if ( ! \class_exists( $class_name, false ) ) {
			$gateway_id_literal     = var_export( $gateway_id, true );
			$payment_method_literal = var_export( $payment_method, true );

			$code = sprintf(
				'namespace %1$s; class %2$s extends Gateway { public static function id(): string { return %3$s; } public function __construct( $subscription_module = null ) { parent::__construct( $subscription_module, %4$s ); } }',
				__NAMESPACE__,
				substr( $class_name, strrpos( $class_name, '\\' ) + 1 ),
				$gateway_id_literal,
				$payment_method_literal
			);

			eval( $code );
		}

		$this->payment_gateway_classes[ $gateway_key ] = $class_name;

		return $class_name;
	}

	/**
	 * Give enabled payment gateways.
	 *
	 * @param array $gateways Gateways.
	 * @return array
	 */
	public function give_enabled_payment_gateways( $gateways ) {
		foreach ( $gateways as $key => $gateway ) {
			// Check if gateway is ours.
			if ( 'pronamic_pay' !== \substr( $key, 0, 12 ) ) {
				continue;
			}

			// Get configuration ID.
			$config_id = \give_get_option( \sprintf( 'give_%s_configuration', $key ) );

			if ( empty( $config_id ) ) {
				$config_id = \get_option( 'pronamic_pay_config_id' );
			}

			// Check if gateway exists for given configuration ID.
			if ( null === Plugin::get_gateway( $config_id ) ) {
				unset( $gateways[ $key ] );
			}
		}

		return $gateways;
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function redirect_url( $url, $payment ) {
		switch ( $payment->get_status() ) {
			case PaymentStatus::CANCELLED:
			case PaymentStatus::FAILURE:
				$url = give_get_failed_transaction_uri();

				break;
			case PaymentStatus::SUCCESS:
				$url = give_get_success_page_uri();

				break;
		}

		return $url;
	}

	/**
	 * Update lead status of the specified payment
	 *
	 * @link https://github.com/Charitable/Charitable/blob/1.1.4/includes/gateways/class-charitable-gateway-paypal.php#L229-L357
	 *
	 * @param Payment $payment Payment.
	 */
	public function status_update( Payment $payment ) {
		$donation_id = (int) $payment->get_source_id();

		switch ( $payment->get_status() ) {
			case PaymentStatus::CANCELLED:
				give_update_payment_status( $donation_id, 'cancelled' );

				break;
			case PaymentStatus::EXPIRED:
				give_update_payment_status( $donation_id, 'abandoned' );

				break;
			case PaymentStatus::FAILURE:
				give_update_payment_status( $donation_id, 'failed' );

				break;
			case PaymentStatus::SUCCESS:
				give_update_payment_status( $donation_id, 'publish' );

				break;
			case PaymentStatus::OPEN:
			default:
				give_update_payment_status( $donation_id, 'pending' );

				break;
		}
	}

	/**
	 * Source column
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		$source_id = (int) $payment->source_id;

		$text = __( 'Give', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			get_edit_post_link( $source_id ),
			/* translators: %s: source id */
			sprintf( __( 'Donation %s', 'pronamic_ideal' ), $source_id )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Payment.
	 *
	 * @return string
	 */
	public function source_description( $description, Payment $payment ) {
		return __( 'Give Donation', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment payment.
	 *
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		return get_edit_post_link( (int) $payment->source_id );
	}
}
