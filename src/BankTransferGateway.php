<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Bank Transfer gateway
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
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
