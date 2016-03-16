<?php

/**
 * Title: Give Sofort gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_SofortGateway extends Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * Constructs and initialize a Sofort gateway
	 */
	public function __construct() {
		$this->id = 'pronamic_pay_sofort';

		$this->name = __( 'SOFORT Banking', 'pronamic_ideal' );

		$this->payment_method = Pronamic_WP_Pay_PaymentMethods::SOFORT;

		parent::__construct();
	}
}
