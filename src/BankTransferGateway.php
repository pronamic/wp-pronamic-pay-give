<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Bank Transfer gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.1
 * @since   1.0.0
 */
class BankTransferGateway extends Gateway {
	/**
	 * Constructs and initialize Bank Transfer gateway
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_bank_transfer',
			__( 'Bank Transfer', 'pronamic_ideal' ),
			PaymentMethods::BANK_TRANSFER
		);
	}
}
