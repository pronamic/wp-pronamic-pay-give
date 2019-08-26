<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Give extension
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'give';

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		new self();
	}

	/**
	 * Construct and initializes an Charitable extension object.
	 */
	public function __construct() {
		add_filter( 'give_payment_gateways', array( $this, 'give_payment_gateways' ) );

		add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, array( __CLASS__, 'redirect_url' ), 10, 2 );
		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'status_update' ), 10, 1 );
		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( $this, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG, array( $this, 'source_url' ), 10, 2 );

		add_filter( 'give_currencies', array( __CLASS__, 'currencies' ), 10, 1 );
	}

	/**
	 * Give payments gateways.
	 *
	 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L37
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public function give_payment_gateways( $gateways ) {
		if ( ! isset( $this->gateways ) ) {
			$classes = array(
				'Gateway',
				'BancontactGateway',
				'BankTransferGateway',
				'CreditCardGateway',
				'DirectDebitGateway',
				'IDealGateway',
				'SofortGateway',
			);

			if ( PaymentMethods::is_active( PaymentMethods::GULDEN ) ) {
				$classes[] = 'GuldenGateway';
			}

			foreach ( $classes as $class ) {
				$class = __NAMESPACE__ . '\\' . $class;

				$gateway = new $class();

				$this->gateways[ $gateway->id ] = array(
					'admin_label'    => $gateway->name,
					'checkout_label' => $gateway->name,
				);
			}
		}

		return array_merge( $gateways, $this->gateways );
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @param string  $url
	 * @param Payment $payment
	 *
	 * @return string
	 */
	public static function redirect_url( $url, $payment ) {
		switch ( $payment->get_status() ) {
			case Statuses::CANCELLED:
				$url = give_get_failed_transaction_uri();

				break;
			case Statuses::FAILURE:
				$url = give_get_failed_transaction_uri();

				break;
			case Statuses::SUCCESS:
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
	 * @param Payment $payment
	 */
	public static function status_update( Payment $payment ) {
		$donation_id = $payment->get_source_id();

		switch ( $payment->get_status() ) {
			case Statuses::CANCELLED:
				give_update_payment_status( $donation_id, 'cancelled' );

				break;
			case Statuses::EXPIRED:
				give_update_payment_status( $donation_id, 'abandoned' );

				break;
			case Statuses::FAILURE:
				give_update_payment_status( $donation_id, 'failed' );

				break;
			case Statuses::SUCCESS:
				give_update_payment_status( $donation_id, 'publish' );

				break;
			case Statuses::OPEN:
			default:
				give_update_payment_status( $donation_id, 'pending' );

				break;
		}
	}

	/**
	 * Filter currencies.
	 *
	 * @param array $currencies Available currencies.
	 *
	 * @return mixed
	 */
	public static function currencies( $currencies ) {
		if ( PaymentMethods::is_active( PaymentMethods::GULDEN ) ) {
			$currencies['NLG'] = array(
				'admin_label' => PaymentMethods::get_name( PaymentMethods::GULDEN ) . ' (G)',
				'symbol'      => 'G',
				'setting'     => array(
					'currency_position'   => 'before',
					'thousands_separator' => '',
					'decimal_separator'   => '.',
					'number_decimals'     => 4,
				),
			);
		}

		return $currencies;
	}

	/**
	 * Source column
	 *
	 * @param         $text
	 * @param Payment $payment
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		$text = __( 'Give', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			get_edit_post_link( $payment->source_id ),
			/* translators: %s: source id */
			sprintf( __( 'Donation %s', 'pronamic_ideal' ), $payment->source_id )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param         $description
	 * @param Payment $payment
	 *
	 * @return string
	 */
	public function source_description( $description, Payment $payment ) {
		return __( 'Give Donation', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param         $url
	 * @param Payment $payment
	 *
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		return get_edit_post_link( $payment->source_id );
	}
}
