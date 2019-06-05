<?php
/**
 * Class BasicTest
 *
 * Tests for the existence of Titles to Tags.
 *
 * @package Title_To_Tags
 */

/**
 * Sample test case.
 */
class BasicTest extends WP_UnitTestCase {

	/**
	 * Class exists. So.. that's pretty good.
	 */
	public function test_namespace() {
		$this->assertTrue( class_exists( '\Title_To_Terms\Core' ) );
	}

	/**
	 * Instantiating the class doesn't blow shit up. That's good.
	 */
	public function test_instantiate() {
		$instantiate = new \Title_To_Terms\Core;
		$this->assertTrue( $instantiate );
	}
}
