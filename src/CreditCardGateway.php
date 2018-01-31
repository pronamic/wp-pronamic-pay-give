<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Credit Card gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.1
 * @since   1.0.0
 */
class CreditCardGateway extends Gateway {
	/**
	 * Constructs and initialize Credit Card gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_credit_card',
			__( 'Credit Card', 'pronamic_ideal' ),
			PaymentMethods::CREDIT_CARD
		);
	}
}
