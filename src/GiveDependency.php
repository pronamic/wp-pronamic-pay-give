<?php
/**
 * Give Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * Give Dependency
 *
 * @author  Reüel van der Steege
 * @version 2.1.1
 * @since   2.1.1
 */
class GiveDependency extends Dependency {
	/**
	 * Is met.
	 *
	 * @link https://github.com/impress-org/givewp/blob/2.6.0/give.php#L52
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		if ( ! \class_exists( '\Give' ) ) {
			return false;
		}

		return true;
	}
}
