<?php
use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Direct Debit gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.1
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_DirectDebitGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize Direct Debit gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_direct_debit',
			__( 'Direct Debit', 'pronamic_ideal' ),
			PaymentMethods::DIRECT_DEBIT
		);
	}
}
