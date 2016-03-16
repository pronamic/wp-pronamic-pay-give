<?php

/**
 * Title: Give Mister Cash gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_MisterCashGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize a Mister Cash gateway
	 */
	public function __construct() {
		$this->id = 'pronamic_pay_mister_cash';

		$this->name = __( 'Bancontact/Mister Cash', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::MISTER_CASH;

		parent::__construct();
	}
}
