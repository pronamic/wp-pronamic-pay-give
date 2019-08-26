<?php

namespace Pronamic\WordPress\Pay\Extensions\Give;

use PHPUnit_Framework_TestCase;

/**
 * Title: WordPress pay Give extension test
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class ExtensionTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test class.
	 */
	public function test_class() {
		$this->assertTrue( class_exists( __NAMESPACE__ . '\Extension' ) );
	}
}
