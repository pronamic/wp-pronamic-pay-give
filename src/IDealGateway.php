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
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		// Unique ID for this payment gateway.
		$this->id = 'pronamic_pay_ideal';

		$this->name = __( 'iDEAL', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::IDEAL;

		parent::__construct();
	}
}
