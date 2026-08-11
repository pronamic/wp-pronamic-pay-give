<?php
/**
 * Gateway repository test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use PHPUnit_Framework_TestCase;
use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Gateway repository test
 */
class GatewayRepositoryTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test map is not empty.
	 */
	public function test_map_not_empty() {
		$this->assertNotEmpty( GatewayRepository::get_map() );
	}

	/**
	 * Test the iDEAL mapping.
	 */
	public function test_ideal_mapping() {
		$map = GatewayRepository::get_map();

		$this->assertArrayHasKey( PaymentMethods::IDEAL, $map );
		$this->assertSame( Gateways\IDealGateway::class, $map[ PaymentMethods::IDEAL ] );
	}

	/**
	 * Test the Bancontact mapping keeps the legacy `mister_cash` identifier.
	 */
	public function test_bancontact_mapping() {
		$map = GatewayRepository::get_map();

		$this->assertArrayHasKey( PaymentMethods::BANCONTACT, $map );
		$this->assertSame( Gateways\BancontactGateway::class, $map[ PaymentMethods::BANCONTACT ] );
	}
}
