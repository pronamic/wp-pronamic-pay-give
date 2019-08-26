<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Credit Card gateway
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
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
