<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Direct Debit gateway
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class DirectDebitGateway extends Gateway {
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
