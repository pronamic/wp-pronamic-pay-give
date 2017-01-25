<?php

/**
 * Title: Give extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.5
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'give';

	//////////////////////////////////////////////////

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
		add_filter( 'pronamic_payment_source_text_' . self::SLUG,   array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG,   array( $this, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG,   array( $this, 'source_url' ), 10, 2 );
	}

	//////////////////////////////////////////////////

	/**
	 * Give payments gateways.
	 *
	 * @see https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L37
	 * @param array $gateways
	 * @retrun array
	 */
	public function give_payment_gateways( $gateways ) {
		if ( ! isset( $this->gateways ) ) {
			$classes = array(
				'Pronamic_WP_Pay_Extensions_Give_Gateway',
				'Pronamic_WP_Pay_Extensions_Give_BankTransferGateway',
				'Pronamic_WP_Pay_Extensions_Give_CreditCardGateway',
				'Pronamic_WP_Pay_Extensions_Give_DirectDebitGateway',
				'Pronamic_WP_Pay_Extensions_Give_IDealGateway',
				'Pronamic_WP_Pay_Extensions_Give_MisterCashGateway',
				'Pronamic_WP_Pay_Extensions_Give_SofortGateway',
			);

			foreach ( $classes as $class ) {
				$gateway = new $class;

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
	 * @param string                  $url
	 * @param Pronamic_WP_Pay_Payment $payment
	 * @return string
	 */
	public static function redirect_url( $url, $payment ) {
		switch ( $payment->get_status() ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED :
				$url = give_get_failed_transaction_uri();

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE :
				$url = give_get_failed_transaction_uri();

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS :
				$url = give_get_success_page_uri();

				break;
		}

		return $url;
	}

	/**
	 * Update lead status of the specified payment
	 *
	 * @see https://github.com/Charitable/Charitable/blob/1.1.4/includes/gateways/class-charitable-gateway-paypal.php#L229-L357
	 * @param Pronamic_Pay_Payment $payment
	 */
	public static function status_update( Pronamic_Pay_Payment $payment ) {
		$donation_id = $payment->get_source_id();

		switch ( $payment->get_status() ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED :
				give_update_payment_status( $donation_id, 'cancelled' );

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED :
				give_update_payment_status( $donation_id, 'abandoned' );

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE :
				give_update_payment_status( $donation_id, 'failed' );

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS :
				give_update_payment_status( $donation_id, 'publish' );

				break;
			case Pronamic_WP_Pay_Statuses::OPEN :
			default:
				give_update_payment_status( $donation_id, 'pending' );

				break;
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Source column
	 */
	public static function source_text( $text, Pronamic_WP_Pay_Payment $payment ) {
		$text  = '';

		$text .= __( 'Give', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			get_edit_post_link( $payment->source_id ),
			sprintf( __( 'Donation %s', 'pronamic_ideal' ), $payment->source_id )
		);

		return $text;
	}

	/**
	 * Source description.
	 */
	public function source_description( $description, Pronamic_Pay_Payment $payment ) {
		$description = __( 'Give Donation', 'pronamic_ideal' );

		return $description;
	}

	/**
	 * Source URL.
	 */
	public function source_url( $url, Pronamic_Pay_Payment $payment ) {
		$url = get_edit_post_link( $payment->source_id );

		return $url;
	}
}
