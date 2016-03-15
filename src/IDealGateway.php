<?php

/**
 * Title: Give iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_IDealGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * The unique ID of this payment gateway
	 *
	 * @var string
	 */
	const ID = 'pronamic_pay_ideal';

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'iDEAL', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::IDEAL;
	}

	///**
	// * Process donation.
	// *
	// * @since   1.0.2
	// */
	//public static function process_donation( $donation_id, $processor, $gateway = null ) {
	//	parent::process_donation( $donation_id, $processor, get_class() );
	//}
	//
	///**
	// * Form gateway fields.
	// *
	// * @since   1.0.2
	// */
	//public static function form_gateway_fields( $fields, $gateway ) {
	//	if ( get_class() === get_class( $gateway ) ) {
	//		$payment_method = $gateway->payment_method;
	//
	//		$config_id = $gateway->get_value( 'config_id' );
	//
	//		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );
	//
	//		if ( $gateway ) {
	//			$gateway->set_payment_method( $payment_method );
	//
	//			$fields['pronamic-pay-input-html'] = array(
	//				'type'    => '',
	//				'gateway' => $gateway,
	//			);
	//		}
	//	}
	//
	//	return $fields;
	//}
	//
	///**
	// * Form gateway field template.
	// *
	// * @since   1.0.2
	// */
	//public static function form_field_template( $arg, $field, $form, $index ) {
	//	if ( 'pronamic-pay-input-html' === $field['key'] ) {
	//		echo $field['gateway']->get_input_html();
	//	}
	//}

	/**
	 * Returns the current gateway's ID.
	 *
	 * @return  string
	 * @access  public
	 * @static
	 * @since   1.0.3
	 */
	public static function get_gateway_id() {
		return self::ID;
	}
}
