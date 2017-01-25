<?php

/**
 * Title: Give Bank Transfer gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.1
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_BankTransferGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize Bank Transfer gateway
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_bank_transfer',
			__( 'Bank Transfer', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER
		);
	}
}
