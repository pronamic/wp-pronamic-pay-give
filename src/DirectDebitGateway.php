<?php

/**
 * Title: Charitable iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.2
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Charitable_DirectDebitGateway extends Pronamic_WP_Pay_Extensions_Charitable_Gateway {
	/**
	 * The unique ID of this payment gateway
	 *
	 * @var string
	 */
	const ID = 'pronamic_pay_direct_debit';

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Direct Debit', 'pronamic_ideal' );

		$this->defaults = array(
			'label' => __( 'Direct Debit', 'pronamic_ideal' ),
		);

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT;
	}

	/**
	 * Process donation.
	 *
	 * @since   1.0.2
	 */
	public static function process_donation( $donation_id, $processor, $gateway = null ) {
		parent::process_donation( $donation_id, $processor, get_class() );
	}

	/**
	 * Returns the current gateway's ID.
	 *
	 * @return  string
	 * @access  public
	 * @static
	 * @since   1.0.1
	 */
	public static function get_gateway_id() {
		return self::ID;
	}
}
