<?php
/**
 * Give Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * Give Dependency class
 */
class GiveDependency extends Dependency {
	/**
	 * Is met.
	 *
	 * Requires GiveWP with the 3.0 payment gateway API (GiveWP 3.0+, tested up
	 * to 4.16).
	 *
	 * @link https://github.com/impress-org/givewp/blob/2.6.0/give.php#L52
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		if ( ! \class_exists( '\Give' ) ) {
			return false;
		}

		if ( ! \class_exists( '\Give\Framework\PaymentGateways\PaymentGateway' ) ) {
			return false;
		}

		return true;
	}
}
