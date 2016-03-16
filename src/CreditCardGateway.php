<?php

/**
 * Title: Give Credit Card gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_CreditCardGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		$this->id = 'pronamic_pay_credit_card';

		$this->name = __( 'Credit Card', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD;

		parent::__construct();
	}
}
