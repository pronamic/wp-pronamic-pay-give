<?php
/**
 * Gulden gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Give Gulden gateway
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.3
 * @since   2.0.0
 */
class GuldenGateway extends Gateway {
	/**
	 * Constructs and initialize Gulden gateway.
	 */
	public function __construct() {
		parent::__construct(
			'pronamic_pay_gulden',
			PaymentMethods::get_name( PaymentMethods::GULDEN ),
			PaymentMethods::GULDEN
		);
	}
}
