<?php
/**
 * Pronamic Pay gateway.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give\Gateways;

use Pronamic\WordPress\Pay\Extensions\Give\Gateway;

/**
 * Pronamic Pay gateway class
 *
 * Generic gateway without a predefined payment method. The payment provider
 * decides which activated payment methods to offer at checkout.
 */
final class PronamicPayGateway extends Gateway {
	/**
	 * Gateway ID.
	 *
	 * @return string
	 */
	public static function id(): string {
		return 'pronamic_pay';
	}

	/**
	 * Payment method.
	 *
	 * @return string|null
	 */
	public function get_payment_method(): ?string {
		return null;
	}
}
