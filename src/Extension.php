<?php

/**
 * Title: Give extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
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
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'give_payment_gateways', array( $this, 'give_payment_gateways' ) );

		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'status_update' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . self::SLUG,   array( __CLASS__, 'source_text' ), 10, 2 );

		// @todo: remove, for local development environment only (prevents using server name in URL)
		add_filter( 'give_get_current_page_url', array( __CLASS__, 'give_current_page_url' ) );
	}

	//////////////////////////////////////////////////

	public static function give_current_page_url( $page_url ) {
		global $wp;

		return home_url( add_query_arg( array(), $wp->request ) );
	}

	/**
	 * Initialize
	 */
	public function init() {
	}

	/**
	 * Give payments gateways.
	 *
	 * @see https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L37
	 * @param array $gateways
	 * @retrun array
	 */
	public function give_payment_gateways( $gateways ) {
		$classes = array(
			'Pronamic_WP_Pay_Extensions_Give_Gateway',
			//'Pronamic_WP_Pay_Extensions_Give_BankTransferGateway',
			//'Pronamic_WP_Pay_Extensions_Give_CreditCardGateway',
			//'Pronamic_WP_Pay_Extensions_Give_DirectDebitGateway',
			//'Pronamic_WP_Pay_Extensions_Give_IDealGateway',
			//'Pronamic_WP_Pay_Extensions_Give_MisterCashGateway',
			//'Pronamic_WP_Pay_Extensions_Give_SofortGateway',
		);

		foreach ( $classes as $class ) {
			$gateway = new $class;

			$id = $gateway->get_gateway_id();

			$gateways[ $id ] = array(
				'admin_label'    => $gateway->name,
				'checkout_label' => $gateway->name,
			);
		}

		return $gateways;
	}

	//////////////////////////////////////////////////

	/**
	 * Update lead status of the specified payment
	 *
	 * @see https://github.com/Charitable/Charitable/blob/1.1.4/includes/gateways/class-charitable-gateway-paypal.php#L229-L357
	 * @param Pronamic_Pay_Payment $payment
	 */
	public static function status_update( Pronamic_Pay_Payment $payment, $can_redirect = false ) {
		$donation_id = $payment->get_source_id();

		switch ( $payment->get_status() ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED :
				give_update_payment_status( $donation_id, 'cancelled' );

				$url = home_url();

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED :
				give_update_payment_status( $donation_id, 'abandoned' );

				$url = home_url();

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE :
				give_update_payment_status( $donation_id, 'failed' );

				$url = give_get_failed_transaction_uri();

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS :
				give_update_payment_status( $donation_id, 'publish' );

				$url = give_get_success_page_uri();

				break;
			case Pronamic_WP_Pay_Statuses::OPEN :
			default:
				give_update_payment_status( $donation_id, 'pending' );

				$url = home_url();

				break;
		}

		if ( $can_redirect ) {
			wp_redirect( $url );

			exit;
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
}
