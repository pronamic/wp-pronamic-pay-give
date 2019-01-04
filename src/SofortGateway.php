<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Sofort gateway
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   1.0.0
 */
class SofortGateway extends Gateway {
	/**
	 * Constructs and initialize Sofort gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_sofort',
			__( 'SOFORT Banking', 'pronamic_ideal' ),
			PaymentMethods::SOFORT
		);
	}
}
