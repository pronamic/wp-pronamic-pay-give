<?php

/**
 * Title: Give Mister Cash gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.3
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_MisterCashGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize Mister Cash gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_mister_cash',
			__( 'Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT
		);
	}
}
