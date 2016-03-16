<?php

/**
 * Title: Give Bank Transfer gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_BankTransferGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize a Bank Transfer gateway
	 */
	public function __construct() {
		$this->id = 'pronamic_pay_bank_transfer';

		$this->name = __( 'Bank Transfer', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER;

		parent::__construct();
	}
}
