<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Bancontact gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.3
 * @since   1.0.0
 */
class BancontactGateway extends Gateway {
	/**
	 * Constructs and initialize Bancontact gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_mister_cash',
			__( 'Bancontact', 'pronamic_ideal' ),
			PaymentMethods::BANCONTACT
		);
	}
}
