<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.1
 * @since   1.0.0
 */
class IDealGateway extends Gateway {
	/**
	 * Constructs and initialize iDEAL gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_ideal',
			__( 'iDEAL', 'pronamic_ideal' ),
			PaymentMethods::IDEAL
		);
	}
}
